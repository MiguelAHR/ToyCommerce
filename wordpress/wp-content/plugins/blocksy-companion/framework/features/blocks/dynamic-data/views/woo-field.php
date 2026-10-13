<?php

if (! defined('ABSPATH')) {
	exit;
}

if (! class_exists('WooCommerce')) {
	return;
}

$value_fallback = blocksy_companion_akg('fallback', $attributes, '');

$value = '';

$has_fallback = false;

$product = wc_get_product();

if (! $product) {
	return;
}

if ($field === 'woo:price') {
	$value = $product->get_price_html();
}

if ($field === 'woo:stock_status') {
	$value = $product->get_stock_status() === 'instock' ?
			__('In Stock', 'blocksy-companion') :
			__('Out of Stock', 'blocksy-companion');
}

if ($field === 'woo:sku') {
	if ( $product->get_sku() ) {
		$value = $product->get_sku();
	}
}

if ($field === 'woo:rating') {
	ob_start();
	woocommerce_template_loop_rating();
	$value = ob_get_clean();
}

if ($field === 'woo:brands') {
	$value = blocksy_companion_render_view(
		dirname(__FILE__) . '/brands-grid.php',
		[
			'attributes' => $attributes
		]
	);
}

if ($field === 'woo:attributes') {
	$attribute = blocksy_companion_akg('attribute', $attributes, '');

	if (empty($attribute)) {
		$choices = \Blocksy\Editor\Blocks\DynamicData::get_product_attribute_choices();

		if (count($choices) > 0) {
			$attribute = array_keys($choices)[0];
		}
	}

	$taxonomy_name = wc_attribute_taxonomy_name($attribute);

	$attributes_tax = $product->get_attributes();

	if (isset($attributes_tax[sanitize_title($taxonomy_name)])) {
		$product_attribute = $attributes_tax[sanitize_title($taxonomy_name)];

		$values = [];

		if ($product_attribute->is_taxonomy()) {
			$attribute_values = wc_get_product_terms(
				$product->get_id(),
				$product_attribute->get_name(),
				['fields' => 'all']
			);

			foreach ($attribute_values as $attribute_value) {
				$values[] = esc_html($attribute_value->name);
			}
		} else {
			foreach ($product_attribute->get_options() as $option) {
				$values[] = make_clickable(esc_html($option));
			}
		}

		if (! empty($values)) {
			$value = implode(
				preg_replace('/ /', "\u{00A0}", blocksy_companion_akg('separator', $attributes, ', ')),
				$values
			);
		}
	}
}

if (empty(trim($value))) {
	return;
}

$value_before = blocksy_companion_akg('before', $attributes, '');
$value_after = blocksy_companion_akg('after', $attributes, '');

if (
	! $has_fallback
	&&
	(! empty($value_before) || ! empty($value_after))
) {
	$value = blocksy_companion_sanitize_html_for_display(
		$value_before . $value . $value_after
	);
}

$tagName = blocksy_companion_akg('tagName', $attributes, 'div');

$classes = ['ct-dynamic-data'];

if (! empty($attributes['align'])) {
	$classes[] = 'has-text-align-' . $attributes['align'];
}

$wrapper_attr['class'] = implode(' ', $classes);

$border_result = get_block_core_post_featured_image_border_attributes(
	$attributes
);

if (! empty($border_result['class'])) {
	$wrapper_attr['class'] .= ' ' . $border_result['class'];
}

if (! empty($border_result['style'])) {
	$wrapper_attr['style'] .= $border_result['style'];
}

$block_type = WP_Block_Type_Registry::get_instance()->get_registered('blocksy/dynamic-data');
$block_type->supports['color'] = true;
wp_apply_colors_support($block_type, $attributes);

$wrapper_attr = get_block_wrapper_attributes($wrapper_attr);

blocksy_companion_html_tag_e($tagName, $wrapper_attr, $value);

