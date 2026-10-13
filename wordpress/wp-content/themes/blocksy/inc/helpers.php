<?php
/**
 * General purpose helpers
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package Blocksy
 */

// Usage:
//
// $raii = blocksy_raii(function() { /* destruct code */ });
//
// When the $raii object goes out of scope, the callback will be called
// automatically.
function blocksy_raii($callback) {
	return new \Blocksy\RaiiPattern($callback);
}

function blocksy_assert_args($args, $fields = []) {
	foreach ($fields as $single_field) {
		if (
			! isset($args[$single_field])
			||
			! $args[$single_field]
		) {
			throw new Error($single_field . ' missing in args!');
		}
	}
}

function blocksy_sync_whole_page($args = []) {
	$args = wp_parse_args(
		$args,
		[
			'prefix_custom' => ''
		]
	);

	$selector = 'main#main';

	/**
	 * Filters the selective refresh partial arguments used to re-render the whole page.
	 *
	 * @since 2.1.18
	 *
	 * @param array $args {
	 *     Partial arguments passed to the customizer selective refresh API.
	 *
	 *     @type string   $selector            CSS selector of the refreshed container. Default 'main#main'.
	 *     @type bool     $container_inclusive Whether the container itself is replaced. Default true.
	 *     @type callable $render              Callback echoing the refreshed markup.
	 * }
	 */
	return apply_filters(
		'blocksy:customizer:sync:whole-page',
		array_merge(
			[
				'selector' => $selector,
				'container_inclusive' => true,
				'render' => function () {
					echo blocksy_replace_current_template();
				}
			],
			$args
		)
	);
}

function blocksy_get_with_percentage($id, $value) {
	$val = blocksy_get_theme_mod($id, $value);

	if (strpos($value, '%') !== false && is_numeric($val)) {
		$val .= '%';
	}

	return str_replace('%%', '%', $val);
}

/**
 * Link to menus editor for every empty menu.
 *
 * @param array  $args Menu args.
 */
if (! function_exists('blocksy_link_to_menu_editor')) {
	function blocksy_link_to_menu_editor($args) {
		if (! current_user_can('manage_options')) {
			return;
		}

		// see wp-includes/nav-menu-template.php for available arguments
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract($args);

		$output = '<a class="ct-create-menu" href="' . admin_url('nav-menus.php') . '" target="_blank">' . $before . __('You don\'t have a menu yet, please create one here &rarr;', 'blocksy') . $after . '</a>';

		if (! empty($container)) {
			$output = "<$container>$output</$container>";
		}

		if ($echo) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo wp_kses_post($output);
		}

		return $output;
	}
}

/**
 * Extract variable from a file.
 *
 * PARITY: mirrored by the companion's blocksy_companion_get_variables_from_file()
 * (framework/helpers/helpers.php) — keep both in sync.
 *
 * @param string $file_path path to file.
 * @param array  $_extract_variables variables to return.
 * @param array  $_set_variables variables to pass into the file.
 */
function blocksy_get_variables_from_file(
	$file_path,
	array $_extract_variables,
	array $_set_variables = array()
) {
	// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
	extract($_set_variables, EXTR_REFS);
	unset($_set_variables);

	if (is_file($file_path)) {
		$blocksy_fatal_containment = blocksy_start_fatal_containment();

		try {
			require $file_path;
		} catch (\Throwable $e) {
			blocksy_handle_contained_fatal($e, $file_path, $blocksy_fatal_containment);
		}
	}

	foreach ($_extract_variables as $variable_name => $default_value) {
		if (isset($$variable_name) ) {
			$_extract_variables[$variable_name] = $$variable_name;
		}
	}

	return $_extract_variables;
}

/**
 * Safe render a view and return html
 * In view will be accessible only passed variables
 * Use this function to not include files directly and to not give access to current context variables (like $this)
 *
 * PARITY: mirrored by the companion's blocksy_companion_render_view()
 * (framework/helpers/helpers.php) — keep both in sync.
 *
 * @param string $file_path File path.
 * @param array  $view_variables Variables to pass into the view.
 *
 * @return string HTML.
 */
if (! function_exists('blocksy_render_view')) {
	function blocksy_render_view(
		$file_path,
		$view_variables = [],
		$default_value = ''
	) {
		if (! is_file($file_path)) {
			return $default_value;
		}

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract($view_variables, EXTR_REFS);
		unset($view_variables);

		ob_start();

		$blocksy_fatal_containment = blocksy_start_fatal_containment();

		try {
			require $file_path;
		} catch (\Throwable $e) {
			ob_end_clean();
			blocksy_handle_contained_fatal($e, $file_path, $blocksy_fatal_containment);
			return $default_value;
		}

		return ob_get_clean();
	}
}

/**
 * Echo the result of blocksy_render_view().
 *
 * PARITY: mirrored by the companion's blocksy_companion_render_view_e()
 * (framework/helpers/helpers.php) — keep both in sync.
 *
 * @param string $file_path File path.
 * @param array  $view_variables Variables to pass into the view.
 * @param string $default_value Echoed when the file is missing.
 *
 * @return void
 */
function blocksy_render_view_e($file_path, $view_variables = [], $default_value = '') {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo blocksy_render_view($file_path, $view_variables, $default_value);
}

/**
 * Record the rendering state a contained fatal must restore. Take it right
 * before the guarded `require` and pass it to blocksy_handle_contained_fatal().
 * The companion's fatal containment uses it through its theme-functions proxy.
 *
 * @return array Containment state.
 */
function blocksy_start_fatal_containment() {
	if (! Blocksy_Manager::$instance) {
		return [];
	}

	return [
		'lazy_zone_depth' => blocksy_manager()->lazy_loading->zone_depth(),
	];
}

/**
 * Put back the rendering state recorded by blocksy_start_fatal_containment().
 * The companion's fatal containment uses it through its theme-functions proxy.
 *
 * @param array $containment Containment state.
 */
function blocksy_restore_fatal_containment($containment) {
	if (isset($containment['lazy_zone_depth'])) {
		blocksy_manager()->lazy_loading->restore_zone_depth($containment['lazy_zone_depth']);
	}
}

/**
 * Contain a fatal thrown while a view/options file is being `require`d. A missing
 * function/class throws \Error (a \Throwable) since PHP 7, so the require can be
 * wrapped to keep one broken file from white-screening the whole request — the
 * #5212 "theme/companion mid-swap" failure mode.
 *
 * Logs through blocksy_debug_log(), passing the \Throwable itself as the object so
 * the full detail — message, file:line and the backtrace — is captured: print_r'd
 * into the error_log, and handed live to the `blocksy:theme:debug-log` action for
 * any listener. In development it re-throws so the bug isn't silently swallowed;
 * the re-throw is filterable via `blocksy:theme:contained-fatal:rethrow`.
 *
 * PARITY: mirrored by the companion's blocksy_companion_handle_contained_fatal()
 * (framework/helpers/helpers.php) — keep both in sync.
 *
 * @param \Throwable $e           The contained error (carries the backtrace).
 * @param string     $context     The file being loaded when it threw.
 * @param array      $containment State from blocksy_start_fatal_containment(),
 *                                taken before the file was loaded.
 */
if (! function_exists('blocksy_handle_contained_fatal')) {
	function blocksy_handle_contained_fatal(\Throwable $e, $context = '', $containment = []) {
		blocksy_restore_fatal_containment($containment);

		blocksy_debug_log(
			sprintf(
				'[Blocksy] Contained fatal while loading %s: %s in %s:%d',
				$context,
				$e->getMessage(),
				$e->getFile(),
				$e->getLine()
			),
			$e
		);

		/**
		 * Filters whether a contained fatal is re-thrown after being logged.
		 *
		 * Defaults to true under WP_DEBUG so bugs surface in development, and
		 * false otherwise so production stays contained. Return true to always
		 * re-throw, or false to always swallow.
		 *
		 * @since 2.1.47
		 *
		 * @param bool $should_rethrow Whether to re-throw the contained error.
		 */
		$should_rethrow = apply_filters(
			'blocksy:theme:contained-fatal:rethrow',
			defined('WP_DEBUG') && WP_DEBUG
		);

		if ($should_rethrow) {
			throw $e;
		}
	}
}

function blocksy_get_wp_theme() {
	/**
	 * Filters the theme object Blocksy reports as its own.
	 *
	 * Used to white label the theme name and metadata shown across the
	 * dashboard and the customizer.
	 *
	 * @since 1.6.8
	 *
	 * @param WP_Theme $theme The resolved theme object.
	 */
	return apply_filters('blocksy_get_wp_theme', wp_get_theme());
}

if (! function_exists('blocksy_get_wp_parent_theme')) {
	function blocksy_get_wp_parent_theme() {
		/**
		 * Filters the theme object Blocksy reports as its own.
		 *
		 * Used to white label the theme name and metadata shown across the
		 * dashboard and the customizer.
		 *
		 * @since 1.6.8
		 *
		 * @param WP_Theme $theme The resolved theme object.
		 */
		return apply_filters('blocksy_get_wp_theme', wp_get_theme(get_template()));
	}
}

function blocksy_current_url() {
	static $url = null;

	if ($url === null) {
		if (is_multisite() && !(defined('SUBDOMAIN_INSTALL') && SUBDOMAIN_INSTALL)) {
			switch_to_blog(1);
			$url = home_url();
			restore_current_blog();
		} else {
			$url = home_url();
		}

		//Remove the "//" before the domain name
		$url = ltrim(preg_replace('/^[^:]+:\/\//', '//', $url), '/');

		//Remove the ulr subdirectory in case it has one
		$split = explode('/', $url);

		//Remove end slash
		$url = rtrim($split[0], '/');

		$request_uri = blocksy_akg('REQUEST_URI', $_SERVER, '');

		/**
		 * Filters the request URI used to build the current URL.
		 *
		 * @since 2.0.77
		 *
		 * @param string $request_uri The current request URI. Default the value of $_SERVER['REQUEST_URI'].
		 */
		$request_uri = apply_filters(
			'blocksy:current-url:request-uri',
			$request_uri
		);

		$url .= '/' . ltrim($request_uri, '/');

		$url = set_url_scheme('//' . $url); // https fix
	}

	return $url;
}

if (! function_exists('blocksy_get_all_image_sizes')) {
	function blocksy_get_all_image_sizes() {
		$titles = [
			'thumbnail' => __('Thumbnail', 'blocksy'),
			'medium' => __('Medium', 'blocksy'),
			'medium_large' => __('Medium Large', 'blocksy'),
			'large' => __('Large', 'blocksy'),
			'full' => __('Full Size', 'blocksy'),
			'woocommerce_thumbnail' => __('WooCommerce Thumbnail', 'blocksy'),
			'woocommerce_single' => __('WooCommerce Single', 'blocksy'),
			'woocommerce_gallery_thumbnail' => __(
				'WooCommerce Gallery Thumbnail',
				'blocksy'
			),
			'woocommerce_archive_thumbnail' => __(
				'WooCommerce Archive Thumbnail',
				'blocksy'
			)
		];

		$all_sizes = get_intermediate_image_sizes();

		$result = [
			'full' => __('Full Size', 'blocksy')
		];

		foreach ($all_sizes as $single_size) {
			if (isset($titles[$single_size])) {
				$result[$single_size] = $titles[$single_size];
			} else {
				$result[$single_size] = $single_size;
			}
		}

		return $result;
	}
}

/**
 * Log a debug message (always — not gated by WP_DEBUG) and fire the
 * `blocksy:theme:debug-log` action so anything can observe the theme's debug logs
 * (e.g. a companion feature).
 *
 * PARITY: mirrored by the companion's blocksy_companion_debug_log() (which fires
 * blocksy:companion:debug-log) — keep both in sync.
 *
 * @param string $message The log message.
 * @param mixed  $object  Optional context appended via print_r (e.g. a \Throwable).
 *
 * @return void
 */
function blocksy_debug_log($message, $object = null) {
	/**
	 * Fires for every theme debug log message.
	 *
	 * Lets anything (e.g. a companion feature) observe the theme's debug logs
	 * without the theme persisting them itself.
	 *
	 * @since 2.1.47
	 *
	 * @param string $message The log message.
	 * @param mixed  $object  Optional context (e.g. a \Throwable). Default null.
	 */
	do_action('blocksy:theme:debug-log', $message, $object);

	if (is_null($object)) {
		error_log($message);
	} else {
		error_log($message . ': ' . print_r($object, true));
	}
}

// Save-time only. Never call on stored content at render, use
// blocksy_sanitize_html_for_display() there.
function blocksy_sanitize_user_html($html, $args = []) {
	$args = wp_parse_args($args, [
		'context' => 'block',
	]);

	if (current_user_can('unfiltered_html')) {
		return $html;
	}

	if (! is_string($html)) {
		return '';
	}

	return blocksy_sanitize_html_for_display([
		'html' => $html,
		'context' => $args['context'],
	]);
}

if (! function_exists('blocksy_sanitize_builder_value')) {
	function blocksy_sanitize_builder_value($option, $input) {
		if (! isset($input['sections'])) {
			return $input;
		}

		foreach ($input['sections'] as $key => $section) {
			if (! isset($section['items'])) {
				continue;
			}

			$html_fields = [
				'text' => ['header_text'],
				'copyright' => ['copyright_text'],
				'trigger' => ['trigger_label'],
				'search' => ['search_label'],
				'offcanvas' => ['offcanvas_heading'],
				'button' => [
					'header_button_text',
					'header_button_secondary_text'
				],
				'cart' => ['cart_total_label'],
				'logo' => ['blogname', 'blogdescription']
			];

			foreach ($section['items'] as $item_key => $item) {
				if (! isset($item['id'])) {
					continue;
				}

				$original_id = explode('~', $item['id'])[0];

				if (
					! isset($html_fields[$original_id])
					||
					! isset($item['values'])
				) {
					continue;
				}

				foreach ($html_fields[$original_id] as $html_field) {
					if (! isset($item['values'][$html_field])) {
						continue;
					}

					if (is_array($item['values'][$html_field])) {
						$item['values'][$html_field] = array_map(
							'blocksy_sanitize_user_html',
							$item['values'][$html_field]
						);

						continue;
					}

					$item['values'][$html_field] = blocksy_sanitize_user_html(
						$item['values'][$html_field]
					);
				}

				$input['sections'][$key]['items'][$item_key] = $item;
			}
		}

		return $input;
	}
}

function blocksy_output_html_safely($html) {
	return do_shortcode($html);

	// Dont use wp_filter_post_kses() as it is very unstable as far as slashes go.
	// Just call wp_kses() directly.
	//
	// Context:
	//
	// https://github.com/WP-API/WP-API/issues/2848
	// https://github.com/WP-API/WP-API/issues/2788
	// https://core.trac.wordpress.org/ticket/38609
	// return wp_kses($html, 'post');
}

function blocksy_get_pricing_links() {
	/**
	 * Filters the pricing links used by the upsell modals.
	 *
	 * @since 2.0.62
	 *
	 * @param array $links {
	 *     Pricing related URLs.
	 *
	 *     @type string $pricing       URL of the pricing page.
	 *     @type string $premium       URL of the premium page.
	 *     @type string $compare-plans URL of the free versus pro comparison table.
	 * }
	 */
	return apply_filters('blocksy:modal:pricing-links', [
		'pricing' => 'https://creativethemes.com/blocksy/pricing/',
		'premium' => 'https://creativethemes.com/blocksy/premium/',
		'compare-plans' => 'https://creativethemes.com/blocksy/pricing/#comparison-free-vs-pro'
	]);
}
