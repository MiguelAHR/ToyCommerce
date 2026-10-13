<?php

add_action(
	'woocommerce_before_template_part',
	function ($template_name, $template_path, $located, $args) {
		if ($template_name !== 'single-product/product-image.php') {
			return;
		}

		wp_enqueue_style('ct-flexy-styles');

		if (blocksy_woocommerce_has_flexy_view()) {
			echo blocksy_render_view(dirname(__FILE__) . '/woo-gallery-template.php');

			ob_start();
		}
	},
	4, 4
);

add_action(
	'woocommerce_after_template_part',
	function ($template_name, $template_path, $located, $args) {
		if ($template_name !== 'single-product/product-image.php') {
			return;
		}

		if (blocksy_woocommerce_has_flexy_view()) {
			ob_end_clean();
		}
	},
	4, 4
);

add_filter(
	'blocksy:woocommerce:single-product:post-class',
	'blocksy_woo_single_post_class'
);

function blocksy_woo_single_post_class($classes) {
	// Not redundant with the filter's own scoping — the filter also fires
	// for AJAX renders (quick view), where these gallery classes must not
	// be applied.
	if (! blocksy_manager()->screen->is_product()) {
		return $classes;
	}

	$product_view_type = blocksy_get_product_view_type();

	if (
		$product_view_type === 'default-gallery'
		||
		$product_view_type === 'stacked-gallery'
	) {
		if (blocksy_get_theme_mod('has_product_sticky_gallery', 'no') === 'yes') {
			$classes[] = 'sticky-gallery';
		}

		if (blocksy_get_theme_mod('has_product_sticky_summary', 'no') === 'yes') {
			$classes[] = 'sticky-summary';
		}
	}

	return $classes;
}

function blocksy_get_product_view_type() {
	/**
	 * Filters the view type used for the main single product gallery layout.
	 *
	 * @since 2.0.1
	 *
	 * @param string $view_type Product gallery view type. Default 'default-gallery'.
	 */
	return apply_filters(
		'blocksy:woocommerce:product-single:view-type',
		'default-gallery'
	);
}
