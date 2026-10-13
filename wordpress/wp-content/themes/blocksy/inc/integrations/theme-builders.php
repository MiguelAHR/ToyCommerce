<?php

if (! function_exists('blocksy_output_header')) {
	function blocksy_output_header() {
		global $blocksy_has_default_header;

		$show_header = apply_filters('blocksy:builder:header:enabled', true);

		if (! $show_header) {
			return;
		}

		/**
		 * Filters the rendered output for the site header.
		 *
		 * Returning a non-empty string short-circuits the default header builder
		 * and third-party header integrations (e.g. a custom header content block).
		 *
		 * @since 2.1.47
		 *
		 * @param string $maybe_custom_header_content Rendered header HTML. Default empty string.
		 */
		$maybe_custom_header_content = apply_filters(
			'blocksy:builder:header:custom-output',
			''
		);

		if (! empty($maybe_custom_header_content)) {
			echo blocksy_html_tag(
				'header',
				array_merge(
					[
						'id' => 'header',
					],
					blocksy_schema_org_definitions('header', ['array' => true])
				),
				$maybe_custom_header_content
			);

			return;
		}

		if (
			function_exists('boostify_header_active')
			&&
			boostify_header_active()
		) {
			boostify_get_header_template();
			return;
		}

		if (function_exists('hfe_render_header') && hfe_header_enabled()) {
			hfe_render_header();
			return;
		}

		if (
			function_exists('elementor_theme_do_location')
			&&
			elementor_theme_do_location('header')
		) {
			return;
		}

		if (class_exists('FLThemeBuilderLayoutData')) {
			$header_ids = FLThemeBuilderLayoutData::get_current_page_header_ids();

			if (! empty($header_ids)) {
				FLThemeBuilderLayoutRenderer::render_header();
			}
		}

		$header_result = Blocksy_Manager::instance()->header_builder->render();

		if (! empty($header_result)) {
			$blocksy_has_default_header = true;
			echo $header_result;
		}
	}
}

if (! function_exists('blocksy_output_footer')) {
	function blocksy_output_footer() {
		$show_footer = apply_filters('blocksy:builder:footer:enabled', true);

		if (! $show_footer) {
			return;
		}

		/**
		 * Filters the rendered output for the site footer.
		 *
		 * Returning a non-empty string short-circuits the default footer builder
		 * and third-party footer integrations (e.g. a custom footer content block).
		 *
		 * @since 2.1.47
		 *
		 * @param string $maybe_custom_footer_content Rendered footer HTML. Default empty string.
		 */
		$maybe_custom_footer_content = apply_filters(
			'blocksy:builder:footer:custom-output',
			''
		);

		if (! empty($maybe_custom_footer_content)) {
			echo blocksy_html_tag(
				'footer',
				array_merge(
					[
						'id' => 'footer'
					],
					blocksy_schema_org_definitions('footer', [
						'array' => true
					])
				),
				$maybe_custom_footer_content
			);
			return;
		}

		if (
			function_exists('boostify_footer_active')
			&&
			boostify_footer_active()
		) {
			boostify_get_footer_template();
			return;
		}

		if (function_exists('hfe_footer_enabled') && hfe_footer_enabled()) {
			hfe_render_footer();
			return;
		}

		if (
			function_exists('elementor_theme_do_location')
			&&
			elementor_theme_do_location('footer')
		) {
			return;
		}

		if (class_exists('FLThemeBuilderLayoutData')) {
			$footer_ids = FLThemeBuilderLayoutData::get_current_page_footer_ids();

			if (! empty($footer_ids)) {
				FLThemeBuilderLayoutRenderer::render_footer();
			}
		}

		echo blocksy_manager()->footer_builder->render();
	}
}
