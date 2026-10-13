<?php

// Note: `wc_get_gallery_image_html` was added in WC 3.3.2 and did not exist prior. This check protects against theme overrides being used on older versions of WC.
if (! function_exists('wc_get_gallery_image_html')) {
	return;
}

if (! isset($skip_default_variation)) {
	$skip_default_variation = false;
}

global $blocksy_current_variation;

if (! isset($product)) {
	global $product;
} else {
	$temp_product = $product;
	global $product;
	$product = $temp_product;
}

if (
	$product->get_type() === 'variable'
	&&
	! $blocksy_current_variation
	&&
	! $skip_default_variation
) {
	$maybe_current_variation = blocksy_manager()
		->woocommerce
		->retrieve_product_default_variation($product);

	if ($maybe_current_variation) {
		$blocksy_current_variation = $maybe_current_variation;
	}
}

$is_single = is_single();

if (isset($forced_single) && $forced_single) {
	$is_single = true;
}

$product_view_attr = [
	'class' => 'woocommerce-product-gallery'
];

$active_index = 1;

if (! isset($gallery_images)) {
	$gallery_product = $product;

	if ($blocksy_current_variation) {
		$gallery_product = $blocksy_current_variation;
	}

	$gallery_images = blocksy_product_get_gallery_images($gallery_product);
}

if ($blocksy_current_variation) {
	$variation_main_image = $blocksy_current_variation->get_image_id();
	$index = array_search($variation_main_image, $gallery_images);

	if ($index !== false) {
		$active_index = $index + 1;
	}

	$product_view_attr['data-current-variation'] = $blocksy_current_variation->get_id();
}

/**
 * Filters the list of product gallery image attachment IDs.
 *
 * @since 1.8.0
 *
 * @param array $gallery_images List of gallery image attachment IDs.
 */
$gallery_images = apply_filters(
	'blocksy:woocommerce:product-view:product_gallery_images',
	$gallery_images
);

$product_view_attr['data-gallery'] = 'default';

if (count($gallery_images) > 1) {
	$product_view_attr['data-thumbs'] = blocksy_get_product_gallery_thumbs();
}

$ratio = '3/4';
$single_ratio = blocksy_get_theme_mod('product_gallery_ratio', '3/4');
$single_thumbs_ratio = blocksy_get_theme_mod(
	'product_gallery_thumbs_ratio',
	'1/1'
);

/**
 * Filters the HTML attributes of the product gallery wrapper element.
 *
 * @since 1.7.54
 *
 * @param array $product_view_attr Wrapper HTML attributes.
 */
$product_view_attr = apply_filters(
	'blocksy:woocommerce:product-view:attr',
	$product_view_attr
);

ob_start();

global $blocksy_is_quick_view;

$badges = [];
$location_key = $blocksy_is_quick_view ? 'archive' : 'single';

if ($product->is_in_stock()) {
	$has_sale_badge = blocksy_get_theme_mod('has_sale_badge', [
		'single' => true,
		'archive' => true
	]);

	if ($has_sale_badge[$location_key]) {
		ob_start();
		woocommerce_show_product_sale_flash();
		$badges[] = ob_get_clean();
	}
} else {
	$maybe_stock_badge = blocksy_get_woo_out_of_stock_badge([
		'location' => $location_key
	]);

	if ($maybe_stock_badge) {
		$badges[] = $maybe_stock_badge;
	}
}

/**
 * Fires before the single product gallery is rendered.
 *
 * @since 2.0.1
 */
do_action('blocksy:woocommerce:product-gallery:before');

/**
 * Filters the single product gallery container CSS classes.
 *
 * @since 2.1.49
 *
 * @param string[] $gallery_container_class Gallery container CSS classes.
 */
$gallery_container_class = apply_filters(
	'blocksy:woocommerce:product-gallery-container:class',
	['ct-product-gallery-container']
);

echo '<div class="' . esc_attr(implode(' ', $gallery_container_class)) . '">';

ob_start();

/**
 * Filters the list of badge markup strings shown over the product gallery.
 *
 * @since 2.0.1
 *
 * @param string[] $badges List of rendered badge markup strings.
 */
echo implode('', apply_filters('blocksy:woocommerce:single:after-sale-badge', $badges));

$maybe_custom_content = null;

if (! $blocksy_is_quick_view) {
	/**
	 * Filters the custom content rendered in place of the default product
	 * gallery.
	 *
	 * Returning a non-null value short-circuits the default gallery markup.
	 *
	 * @since 1.7.54
	 *
	 * @param string|null $content        Custom gallery content. Default null.
	 * @param WC_Product  $product        The current product.
	 * @param array       $gallery_images List of gallery image attachment IDs.
	 * @param bool        $is_single      Whether this renders on a single product page.
	 */
	$maybe_custom_content = apply_filters(
		'blocksy:woocommerce:product-view:content',
		null,
		$product,
		$gallery_images,
		$is_single
	);
}

/**
 * Fires at the start of the product gallery inner content.
 *
 * @since 1.7.21
 *
 * @param array $gallery_images List of gallery image attachment IDs.
 */
do_action('blocksy:woocommerce:product-view:start', $gallery_images);

$gallery_actions = [];

if (
	blocksy_get_theme_mod('has_product_single_lightbox', 'no') === 'yes'
	&&
	current_theme_supports('wc-product-gallery-lightbox')
	&&
	! isset($blocksy_is_quick_view)
	&&
	! $blocksy_is_quick_view
	&&
	isset($gallery_images[0])
	&&
	$gallery_images[0]
	&&
	! $maybe_custom_content
	&&
	/**
	 * Filters whether the product gallery lightbox/zoom trigger is rendered.
	 *
	 * @since 1.8.0
	 *
	 * @param bool $has_trigger Whether the zoom trigger is shown. Default true.
	 */
	apply_filters('blocksy:woocommerce:product-review:has-gallery-zoom-trigger', true)
) {
	$gallery_actions[] = '<a href="#" class="woocommerce-product-gallery__trigger">🔍</a>';
}

if (! empty($gallery_actions)) {
	// echo '<div class="ct-gallery-actions">';
	echo implode(' ', $gallery_actions);
	// echo '</div>';
}

/**
 * Filters the default image ratio used for the product gallery when not on a
 * single product page.
 *
 * @since 1.7.8
 *
 * @param string $default_ratio Image ratio (e.g. '3/4'). Default '3/4'.
 */
$default_ratio = apply_filters('blocksy:woocommerce:default_product_ratio', '3/4');

if (! $maybe_custom_content && count($gallery_images) === 1) {
	$attachment_id = $gallery_images[0];

	$image_href = wp_get_attachment_image_src(
		$attachment_id,
		'full'
	);

	$width = null;
	$height = null;

	if ($image_href) {
		$width = $image_href[1];
		$height = $image_href[2];

		$image_href = $image_href[0];
	}

	echo blocksy_media(
		/**
		 * Filters the arguments used to render the single product gallery image
		 * when there is a single image.
		 *
		 * @since 2.0.1
		 *
		 * @param array $attributes Arguments passed to blocksy_media().
		 */
		apply_filters(
			'blocksy:woocommerce:image_additional_attributes',
			[
				'no_image_type' => 'woo',
				'attachment_id' => $gallery_images[0],
				'post_id' => $product->get_id(),
				'size' => 'woocommerce_single',
				'ratio' => $is_single ? $single_ratio : $default_ratio,
				'tag_name' => 'figure',
				'size' => 'woocommerce_single',
				'html_atts' => array_merge([
					'data-src' => $image_href
				], $width ? [
					'data-width' => $width,
					'data-height' => $height
				] : []),
				'display_video' => true
			]
		)
	);
}

if (! $maybe_custom_content && count($gallery_images) > 1) {
	/**
	 * Filters the arguments passed to the Blocksy flexy slider for the single
	 * product gallery.
	 *
	 * @since 1.8.0
	 *
	 * @param array $flexy_args Flexy slider arguments.
	 */
	$flexy_args = apply_filters(
		'blocksy:woocommerce:single_product:flexy-args',
		[
			'active_index' => $active_index,
			'images' => $gallery_images,
			'size' => 'woocommerce_single',
			'pills_images' => $is_single ? $gallery_images : null,
			'pills_images_ratio' => $is_single ? $single_thumbs_ratio : '1/1',
			'images_ratio' => $is_single ? $single_ratio : $default_ratio,
		]
	);

	if (
		empty($flexy_args['slide_image_args'])
		&&
		($flexy_args['enable'] ?? true)
	) {
		$visible_items = 1;

		if (! $blocksy_is_quick_view) {
			$visible_items = max(blocksy_get_product_gallery_columns());
		}

		$flexy_args['visible_items'] = intval($visible_items);
	}

	echo blocksy_flexy($flexy_args);
}

if ($maybe_custom_content) {
	echo $maybe_custom_content;
}

echo apply_filters(
	'woocommerce_single_product_image_thumbnail_html',
	ob_get_clean(),
	$gallery_images[0]
);

do_action('woocommerce_product_thumbnails');

echo '</div>';

/**
 * Fires after the single product gallery is rendered.
 *
 * @since 2.0.1
 */
do_action('blocksy:woocommerce:product-gallery:after');

$result_html = ob_get_clean();

$prefix = blocksy_manager()->screen->get_prefix();

if (is_customize_preview()) {
	$product_view_attr['data-shortcut'] = 'border:outside';
	$product_view_attr['data-shortcut-location'] = blocksy_first_level_deep_link($prefix) . ':woo_product_gallery';
}

if (! empty($result_html)) {
	echo blocksy_html_tag(
		'div',
		$product_view_attr,
		$result_html
	);
}
