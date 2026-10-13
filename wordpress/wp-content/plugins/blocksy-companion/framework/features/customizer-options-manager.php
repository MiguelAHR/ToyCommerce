<?php

namespace Blocksy;

if (! defined('ABSPATH')) {
	exit;
}

class CustomizerOptionsManager {
	public function __construct() {
		add_filter('blocksy:options:manage-options:top', function ($options) {
			$options['importer'] = [
				'type' => 'blocksy-customizer-options-manager',
				'design' => 'none',
			];

			return $options;
		});

		add_action('wp_ajax_blocksy_customizer_export', function () {
			if (! isset($_REQUEST['nonce'])) {
				wp_send_json_error();
			}

			if (! wp_verify_nonce(sanitize_key($_REQUEST['nonce']), 'ct-customizer-reset')) {
				wp_send_json_error();
			}

			if (! current_user_can('manage_options')) {
				wp_send_json_error();
			}

			if (! isset($_POST['strategy'])) {
				wp_send_json_error();
			}

			$strategy = sanitize_text_field(wp_unslash($_POST['strategy']));

			wp_send_json_success([
				'data' => serialize($this->get_data(null, $strategy)),
				'site_url' => get_site_url()
			]);
		});

		add_action('wp_ajax_blocksy_customizer_import', function () {
			if (! current_user_can('manage_options')) {
				wp_send_json_error();
			}

			if (! isset($_REQUEST['nonce'])) {
				wp_send_json_error();
			}

			if (! wp_verify_nonce(sanitize_key($_REQUEST['nonce']), 'ct-customizer-reset')) {
				wp_send_json_error();
			}

			$data = json_decode(
				file_get_contents('php://input'),
				true
			);

			global $wp_customize;

			if (! is_array($data) || ! $wp_customize) {
				wp_send_json_error();
			}

			$data = $this->sanitize_import_data($data);

			// The above code will only import stuff from $data['mods']
			// that is actually a customizer control. Everything else will be ignored
			if (isset($data['mods'])) {
				$importer = new DemoInstallOptionsInstaller();

				$demo_data = null;

				if (isset($data['site_url']) && is_string($data['site_url'])) {
					$demo_data = [
						'url' => $data['site_url']
					];
				}

				$importer->import_options($data, $demo_data);
			}

			// Will only import $data['blocksy_widgets'] that has an actual
			// widget. Everything else is going to be ignored from being
			// processed.
			if (isset($data['blocksy_widgets'])) {
				$importer = new DemoInstallWidgetsInstaller();

				$importer->import_data($data['blocksy_widgets']);
			}

			wp_send_json_success([]);
		});

		add_action('wp_ajax_blocksy_customizer_copy_options', function () {
			if (! current_user_can('manage_options')) {
				wp_send_json_error();
			}

			if (! isset($_REQUEST['nonce'])) {
				wp_send_json_error();
			}

			if (! wp_verify_nonce(sanitize_key($_REQUEST['nonce']), 'ct-customizer-reset')) {
				wp_send_json_error();
			}

			if (! isset($_POST['strategy'])) {
				wp_send_json_error();
			}

			$theme_for_data = get_option('stylesheet');
			$strategy = sanitize_text_field(wp_unslash($_POST['strategy']));

			if ($strategy === 'parent') {
				foreach (wp_get_themes() as $id => $theme) {
					if (! $theme->parent()) {
						continue;
					}

					if ($theme->parent()->get_stylesheet() === 'blocksy') {
						$theme_for_data = $theme->parent()->get_stylesheet();
					}
				}
			}

			if ($strategy === 'child') {
				foreach (wp_get_themes() as $id => $theme) {
					if (! $theme->parent()) {
						continue;
					}

					if ($theme->parent()->get_stylesheet() === 'blocksy') {
						$theme_for_data = $theme->get_stylesheet();
					}
				}
			}

			$data = $this->get_data($theme_for_data);

			$importer = new DemoInstallOptionsInstaller();

			$importer->import_options($data);

			do_action('customize_save_after');
			/**
			 * Fires when the dynamic CSS caches should be invalidated.
			 *
			 * Listeners drop their generated CSS files/transients so the
			 * next request regenerates them.
			 *
			 * @since 1.6.2
			 * @since 1.8.0 Renamed from `blocksy:dynamic-css:regenere_css_files`.
			 */
			do_action('blocksy:dynamic-css:refresh-caches');
			/**
			 * Fires when all the caches managed by Blocksy need to be purged.
			 *
			 * @since 2.0.27
			 */
			do_action('blocksy:cache-manager:purge-all');

			wp_send_json_success([]);
		});

		add_action('wp_ajax_blocksy_customizer_wipe_caches', function () {
			if (! current_user_can('manage_options')) {
				wp_send_json_error();
			}

			do_action('customize_save_after');
			/**
			 * Fires when the dynamic CSS caches should be invalidated.
			 *
			 * Listeners drop their generated CSS files/transients so the
			 * next request regenerates them.
			 *
			 * @since 1.6.2
			 * @since 1.8.0 Renamed from `blocksy:dynamic-css:regenere_css_files`.
			 */
			do_action('blocksy:dynamic-css:refresh-caches');
			/**
			 * Fires when all the caches managed by Blocksy need to be purged.
			 *
			 * @since 2.0.27
			 */
			do_action('blocksy:cache-manager:purge-all');

			wp_send_json_success([]);
		});
	}

	public function sanitize_import_data($data) {
		global $wp_customize;

		$allowed_options = (new DemoInstallOptionsExport())->get_exported_options_keys();

		foreach ($wp_customize->settings() as $key => $setting) {
			if (
				'option' !== $setting->type
				||
				'widget_' === substr(strtolower($key), 0, 7)
				||
				'sidebars_' === substr(strtolower($key), 0, 9)
				||
				! $setting->check_capabilities()
			) {
				continue;
			}

			$allowed_options[] = $key;
		}

		$options = [];

		if (isset($data['options']) && is_array($data['options'])) {
			$options = array_intersect_key(
				$data['options'],
				array_flip($allowed_options)
			);
		}

		$data['options'] = $options;

		if (current_user_can(
			blocksy_companion_get_capabilities()->get_wp_capability_by('ext_code_snippets_fields')
		)) {
			return $data;
		}

		if (isset($data['mods']) && is_array($data['mods'])) {
			unset(
				$data['mods']['header_scripts'],
				$data['mods']['header_after_body_scripts'],
				$data['mods']['footer_scripts']
			);
		}

		unset($data['fluent_snippets'], $data['wp_css']);

		if (isset($data['blocksy_widgets']) && is_array($data['blocksy_widgets'])) {
			global $wp_widget_factory;

			foreach ($data['blocksy_widgets'] as $sidebar_id => $widgets) {
				if (! is_array($widgets)) {
					unset($data['blocksy_widgets'][$sidebar_id]);
					continue;
				}

				foreach ($widgets as $widget_instance_id => $widget) {
					$widget_object = $wp_widget_factory->get_widget_object(
						preg_replace('/-[0-9]+$/', '', $widget_instance_id)
					);

					$sanitized = false;

					if ($widget_object && is_array($widget)) {
						$sanitized = $widget_object->update($widget, []);
					}

					if (! is_array($sanitized)) {
						unset($data['blocksy_widgets'][$sidebar_id][$widget_instance_id]);
						continue;
					}

					$data['blocksy_widgets'][$sidebar_id][$widget_instance_id] = $sanitized;
				}
			}
		}

		return $data;
	}

	private function get_data($theme_slug = null, $strategy = 'options') {
		$data = [];

		if (strpos($strategy, 'options') !== false) {
			if (! $theme_slug) {
				$theme_slug = get_option('stylesheet');
			}

			global $wp_customize;

			$mods = $this->get_theme_mods($theme_slug);
			$data = [
				'template' => $theme_slug,
				'site_url' => get_site_url(),
				'mods' => $mods ? $mods : array(),
				'options' => array(),
				'custom_palettes' => get_option('blocksy_custom_palettes', []),
			];

			$core_options = [
				'blogname',
				'blogdescription',
				'show_on_front',
				'page_on_front',
				'page_for_posts',
			];

			$settings = $wp_customize->settings();

			foreach ($settings as $key => $setting) {
				if ('option' === $setting->type) {
					// Don't save widget data.
					if ('widget_' === substr(strtolower($key), 0, 7)) {
						continue;
					}

					// Don't save sidebar data.
					if ('sidebars_' === substr(strtolower($key), 0, 9)) {
						continue;
					}

					// Don't save core options.
					if (in_array($key, $core_options)) {
						continue;
					}

					$data['options'][$key] = $setting->value();
				}
			}

			if (function_exists('wp_get_custom_css_post')) {
				$data['wp_css'] = wp_get_custom_css($theme_slug);
			}
		}

		if (strpos($strategy, 'widgets') !== false) {
			$widgets = new DemoInstallWidgetsExport();
			$data['blocksy_widgets'] = json_decode($widgets->export());
		}

		return $data;
	}

	private function get_theme_mods($theme_slug = null) {
		if (! $theme_slug) {
			$theme_slug = get_option('stylesheet');
		}

		$mods = get_option("theme_mods_$theme_slug");

		if (false === $mods) {
			$theme_name = wp_get_theme($theme_slug)->get( 'Name' );

			$mods = get_option( "mods_$theme_name" ); // Deprecated location.

			if ( is_admin() && false !== $mods ) {
				update_option( "theme_mods_$theme_slug", $mods );
				delete_option( "mods_$theme_name" );
			}
		}

		return $mods;
	}
}
