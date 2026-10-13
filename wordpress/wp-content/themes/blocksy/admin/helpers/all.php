<?php

require get_template_directory() . '/admin/helpers/validator.php';
require get_template_directory() . '/admin/helpers/options.php';
require get_template_directory() . '/admin/helpers/meta-boxes.php';
require get_template_directory() . '/admin/helpers/options-logic.php';
require get_template_directory() . '/admin/helpers/inline-svgs.php';

function blocksy_get_block_editor_data($args = []) {
	$args = wp_parse_args($args, [
		'is_block_editor' => 'NEEDS_DETECTION'
	]);

	if ($args['is_block_editor'] === 'NEEDS_DETECTION') {
		$current_screen = get_current_screen();

		if ($current_screen) {
			$args['is_block_editor'] = $current_screen->is_block_editor();
		}
	}

	if (! $args['is_block_editor']) {
		return [];
	}

	/**
	 * Filters the data localized for Blocksy blocks in the block editor.
	 *
	 * Only applied on block editor screens.
	 *
	 * @since 2.0.93
	 *
	 * @param array $data Block editor data, keyed by feature. Default empty array.
	 */
	return apply_filters('blocksy:block-editor:localized_data', []);
}

function blocksy_backend_dynamic_styles_urls() {
	return [
		'flexy' => get_template_directory_uri() . '/static/bundle/flexy.min.css'
	];
}

// Temporary work-around until this issue is fixed:
// https://github.com/WordPress/gutenberg/issues/53509
function blocksy_add_early_inline_style_in_gutenberg($cb) {
	add_action(
		'block_editor_settings_all',
		function ($settings) use ($cb) {
			$css = wp_strip_all_tags($cb());

			if (empty($css)) {
				return $settings;
			}

			$settings['__unstableResolvedAssets']['styles'] .= blocksy_html_tag(
				'style',
				[],
				$css
			);

			return $settings;
		}
	);

	add_action(
		'admin_print_styles',
		function () use ($cb) {
			if (
				! get_current_screen()
				||
				! get_current_screen()->is_block_editor
			) {
				return;
			}

			$css = wp_strip_all_tags($cb());

			if (empty($css)) {
				return;
			}

			echo blocksy_html_tag('style', [], $css);
		},
		25
	);
}
