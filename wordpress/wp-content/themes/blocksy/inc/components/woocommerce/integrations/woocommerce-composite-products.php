<?php

if (! function_exists('wc_cp_add_to_cart_after_summary')) {
    return;
}

remove_action('woocommerce_after_single_product_summary', 'wc_cp_add_to_cart_after_summary', -1000);
add_action('woocommerce_after_single_product_summary', 'wc_cp_add_to_cart_after_summary', 2);

add_filter(
	'woocommerce_composite_form_wrapper_classes',
	function($classes, $product) {
		$classes[] = 'is-width-constrained';
		return $classes;
	},
	10, 2
);

add_action(
	'woocommerce_before_template_part',
	function ($template_name, $template_path, $located, $args) {
		if ($template_name !== 'single-product/composite-add-to-cart.php') {
			return;
		}

		if (! blocksy_woo_has_ajax_add_to_cart()) {
			return;
		}

		ob_start();
	},
	4, 4
);

add_action(
	'woocommerce_after_template_part',
	function ($template_name, $template_path, $located, $args) {
		if ($template_name !== 'single-product/composite-add-to-cart.php') {
			return;
		}

		if (! blocksy_woo_has_ajax_add_to_cart()) {
			return;
		}

		echo preg_replace(
			'/class="([^"]*\bcomposite_data\b[^"]*)"/',
			'data-add-to-cart="ajax" class="$1"',
			ob_get_clean(),
			1
		);
	},
	4, 4
);
