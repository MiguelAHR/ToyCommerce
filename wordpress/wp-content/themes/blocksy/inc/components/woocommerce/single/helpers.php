<?php

function blocksy_get_product_specific_layer($layer_id = '', $args = []) {
	$args = wp_parse_args($args, [
		'respect_post_type' => true
	]);

	if (empty($layer_id)) {
		return null;
	}

	if ($args['respect_post_type']) {
		$post_type = get_post_type();

		if ($post_type !== 'product') {
			return null;
		}
	}

	$default_product_layout = blocksy_get_woo_single_layout_defaults();

	$layout = blocksy_get_theme_mod(
		'woo_single_layout',
		$default_product_layout
	);

	$layout = blocksy_normalize_layout($layout, $default_product_layout);

	$product_view_type = blocksy_get_product_view_type();

	if (
		$product_view_type === 'top-gallery'
		||
		$product_view_type === 'columns-top-gallery'
	) {
		$woo_single_split_layout = blocksy_get_theme_mod(
			'woo_single_split_layout',
			[
				'left' => blocksy_get_woo_single_layout_defaults('left'),
				'right' => blocksy_get_woo_single_layout_defaults('right')
			]
		);

		$layout = array_merge(
			$woo_single_split_layout['left'],
			$woo_single_split_layout['right']
		);
	}

	$layer_to_find = array_values(array_filter($layout, function($k) use ($layer_id) {
		return $k['id'] === $layer_id;
	}));

	if (empty($layer_to_find)) {
		return null;
	}

	return $layer_to_find[0];
}

function blocksy_has_product_specific_layer($layer_id = '', $args = []) {
	$layer = blocksy_get_product_specific_layer($layer_id, $args);

	if (! $layer) {
		return false;
	}

	if (
		isset($layer['enabled'])
		&&
		$layer['enabled']
	) {
		return true;
	}

	return false;
}

function blocksy_woo_has_ajax_add_to_cart() {
	return blocksy_manager()->woocommerce->single->has_ajax_add_to_cart();
}
