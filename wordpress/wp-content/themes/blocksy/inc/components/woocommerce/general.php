<?php

add_action(
	'blocksy:content:top',
	function () {
		if (
			! is_shop()
			&&
			! is_woocommerce()
			&&
			! is_cart()
			&&
			! is_checkout()
			&&
			! is_account_page()
		) {
			global $blocksy_messages_content;
			ob_start();
			echo '<div class="blocksy-woo-messages-default woocommerce-notices-wrapper">';
			echo do_shortcode('[woocommerce_messages]');
			echo '</div>';
			$blocksy_messages_content = ob_get_clean();
		}
	}
);

add_action(
	'blocksy:single:top',
	function () {
		global $blocksy_messages_content;

		if (! empty($blocksy_messages_content)) {
			echo $blocksy_messages_content;
		}
	}
);

add_filter(
	'woocommerce_format_sale_price',
	function ($price, $regular_price, $sale_price) {
		return '<span class="sale-price">' . $price . '</span>';
	},
	10,
	3
);

add_filter('woocommerce_quantity_input_args', function ($args, $product) {
	global $blocksy_quantity_args;
	$blocksy_quantity_args = $args;

	if (! isset($blocksy_quantity_args['min_value'])) {
		$blocksy_quantity_args['min_value'] = 0;
	}

	if (! isset($blocksy_quantity_args['max_value'])) {
		$blocksy_quantity_args['max_value'] = $product->get_max_purchase_quantity();
	}

	$blocksy_quantity_args['min_value'] = max($blocksy_quantity_args['min_value'], 0);
	$blocksy_quantity_args['max_value'] = 0 < $blocksy_quantity_args['max_value'] ? $blocksy_quantity_args['max_value'] : '';

	if ('' !== $blocksy_quantity_args['max_value'] && $blocksy_quantity_args['max_value'] < $blocksy_quantity_args['min_value']) {
		$blocksy_quantity_args['max_value'] = $blocksy_quantity_args['min_value'];
	}

	return $args;
}, 10, 2);

add_action(
	'woocommerce_before_quantity_input_field',
	function () {
		if (blocksy_get_theme_mod('has_custom_quantity', 'yes') !== 'yes') {
			return;
		}

		global $blocksy_detect_woo_block_render;

		if (
			isset($blocksy_detect_woo_block_render)
			&&
			$blocksy_detect_woo_block_render
		) {
			return;
		}

		$arrow_icon = '<path class="ct-quantity-type-1" d="M1004 778Q1004 778 997.5 783Q991 788 983 788H942Q934 788 931.5 783Q929 778 922 778L512 358L82 778Q82 778 75.5 783Q69 788 61 788H20Q20 788 14 783Q8 778 0 778V748Q0 740 2.5 727Q5 714 20 707L481 246Q489 238 496.5 231.5Q504 225 512 225Q520 225 527.5 227.5Q535 230 543 246L1004 707Q1019 714 1021.5 722Q1024 730 1024 737Q1024 753 1021.5 762Q1019 771 1004 778Z"/>';
		$quantity_icons = [
			'ct-increase' => '<path class="ct-quantity-type-2" d="M1024 512Q1024 527 1021.5 537.5Q1019 548 1004 563Q996 571 987 572Q978 573 963 573H573V963Q573 978 570.5 988Q568 998 553 1014Q545 1021 536 1022.5Q527 1024 512 1024Q497 1024 486.5 1021.5Q476 1019 461 1004Q453 996 452 987Q451 978 451 963V573H61Q46 573 36 570.5Q26 568 10 553Q3 545 1.5 536Q0 527 0 512Q0 497 2.5 486.5Q5 476 20 461Q36 445 42.5 442.5Q49 440 72 440H461V61Q461 46 463.5 36Q466 26 481 10Q481 3 489 1.5Q497 0 512 0Q527 0 537.5 2.5Q548 5 563 20Q579 36 581.5 42.5Q584 49 584 72V461H973Q988 461 998.5 463.5Q1009 466 1024 481Z"/>',
			'ct-decrease' => '<path class="ct-quantity-type-2" d="M963 573H61Q38 573 19 554Q0 535 0 512Q0 489 19 470Q38 451 61 451H963Q986 451 1005 470Q1024 489 1024 512Q1024 535 1005 554Q986 573 963 573Z"/>',
		];

		foreach ($quantity_icons as $class => $icon) {
			blocksy_html_tag_e(
				'span',
				['class' => $class],
				blocksy_html_tag('svg', [
					'width' => 10,
					'height' => 10,
					'viewBox' => '0 0 1024 1024',
					'fill' => 'currentColor',
					'aria-hidden' => 'true',
					'focusable' => 'false',
				], $arrow_icon . $icon)
			);
		}
	}
);

add_action(
	'woocommerce_before_main_content',
	function () {
		$prefix = blocksy_manager()->screen->get_prefix();

		if (
			is_search()
			&&
			! have_posts()
		) {
			/**
			 * Filters the rendered output for the WooCommerce "nothing found" state.
			 *
			 * Returning a non-empty string is echoed in place of the default
			 * no-results content on an empty product search results page.
			 *
			 * @since 2.1.47
			 *
			 * @param string $content Rendered output. Default empty string.
			 */
			$content = apply_filters(
				'blocksy:woocommerce:nothing-found:custom-output',
				''
			);

			if (! empty($content)) {
				// TODO: refactor into a class so this becomes instance state, not a global.
				global $blocksy_woo_has_nothing_found;
				$blocksy_woo_has_nothing_found = true;

				echo $content;
				ob_start();
				return;
			}
		}

		if ($prefix === 'woo_categories' || $prefix === 'search') {
			/**
			 * Note to code reviewers: This line doesn't need to be escaped.
			 * Function blocksy_output_hero_section() used here escapes the value properly.
			 */
			echo blocksy_output_hero_section([
				'type' => 'type-2'
			]);
		}

		$attr = [
			'class' => 'ct-container'
		];

		if (blocksy_get_page_structure() === 'narrow') {
			$attr['class'] = 'ct-container-narrow';
		}

		if ($prefix === 'product') {
			if (blocksy_sidebar_position() === 'none') {
				$attr['class'] = 'ct-container-full';

				$attr['data-content'] = 'normal';

				if (blocksy_get_page_structure() === 'narrow') {
					$attr['data-content'] = 'narrow';
				}
			}

			echo blocksy_output_hero_section([
				'type' => 'type-2'
			]);
		}


		echo '<div ' . blocksy_attr_to_html($attr) . ' ' . wp_kses(blocksy_sidebar_position_attr(), []) . ' ' . blocksy_get_v_spacing() . '>';

		if (blocksy_manager()->screen->is_product()) {
			echo '<article class="post-' . get_the_ID() . '">';
		} else {
			echo '<section>';
		}

		if (
			$prefix === 'woo_categories'
			||
			$prefix === 'search'
			||
			$prefix === 'product'
		) {
			/**
			 * Note to code reviewers: This line doesn't need to be escaped.
			 * Function blocksy_output_hero_section() used here escapes the value properly.
			 */
			echo blocksy_output_hero_section([
				'type' => 'type-1'
			]);
		}
	}
);

add_action(
	'woocommerce_after_main_content',
	function () {
		global $blocksy_woo_has_nothing_found;

		if ($blocksy_woo_has_nothing_found) {
			$blocksy_woo_has_nothing_found = false;
			ob_get_clean();
			return;
		}

		if (blocksy_manager()->screen->is_product()) {
			echo '</article>';
		} else {
			echo '</section>';
		}

		get_sidebar();
		echo '</div>';
	}
);

add_action(
	'woocommerce_before_template_part',
	function ($template_name, $template_path, $located, $args) {
		global $blocksy_is_offcanvas_cart;

		if ($template_name === 'global/quantity-input.php') {
			ob_start();
		}

		if ($template_name === 'single-product/up-sells.php') {
			ob_start();
		}

		if ($template_name === 'single-product/related.php') {
			ob_start();
		}
	},
	10,
	4
);

add_action(
	'woocommerce_after_template_part',
	function ($template_name, $template_path, $located, $args) {
		global $blocksy_is_offcanvas_cart;

		if ($template_name === 'global/quantity-input.php') {
			$quantity = ob_get_clean();

			$final_quantity_look = 'class="quantity"';

			global $blocksy_quantity_args;

			$args = $blocksy_quantity_args;

			if ($args['max_value'] && $args['min_value'] === $args['max_value']) {
				$final_quantity_look = 'class="quantity hidden"';
			} else {
				if (blocksy_get_theme_mod('has_custom_quantity', 'yes') === 'yes') {
					$final_quantity_look .= ' data-type="' . blocksy_get_theme_mod('quantity_type', 'type-2') . '"';
				}
			}

			echo str_replace(
				'class="quantity"',
				$final_quantity_look,
				$quantity
			);
		}

		if ($template_name === 'single-product/up-sells.php') {
			$upsells = ob_get_clean();

			$woocommerce_related_products_slideshow = blocksy_get_theme_mod(
				'woocommerce_related_products_slideshow',
				'default'
			);

			$other_attr = [];

			if (is_customize_preview()) {
				$other_attr['data-shortcut'] = 'border:outside';
				$other_attr['data-shortcut-location'] = blocksy_first_level_deep_link('woo_categories');

				if (is_single()) {
					$prefix = blocksy_manager()->screen->get_prefix();

					$other_attr['data-shortcut-location'] = blocksy_first_level_deep_link($prefix) . ':woo_has_related_upsells';
				}
			}

			$constrained_class = '';
			$visibility_classes = '';

			$upsells_class = [
				'up-sells',
				'upsells',
				'products'
			];

			if ($woocommerce_related_products_slideshow === 'slider') {
				$upsells_class[] = 'is-layout-slider';
			}

			if (blocksy_manager()->screen->uses_woo_default_template()) {
				$upsells_class[] = 'is-width-constrained';

				$upsells_class = array_merge(
					$upsells_class,
					blocksy_visibility_classes(
						blocksy_get_theme_mod(
							'upsell_products_visibility',
							[
								'desktop' => true,
								'tablet' => false,
								'mobile' => false,
							]
						),

						[
							'output' => 'array'
						]
					)
				);
			}

			$other_attr['class'] = implode(' ', $upsells_class);

			$woo_product_related_label_tag = blocksy_get_theme_mod('woo_product_related_label_tag', 'h2');

			$upsells = preg_replace(
				'/<h2>(.*?)<\/h2>/',
				'<' . $woo_product_related_label_tag . ' class="ct-module-title">$1</' . $woo_product_related_label_tag . '>',
				$upsells
			);

			echo str_replace(
				'class="up-sells upsells products"',
				blocksy_attr_to_html($other_attr),
				$upsells
			);
		}

		if ($template_name === 'single-product/related.php') {
			$related = ob_get_clean();

			$woocommerce_related_products_slideshow = blocksy_get_theme_mod(
				'woocommerce_related_products_slideshow',
				'default'
			);

			$other_attr = [];

			if (is_customize_preview()) {
				$other_attr['data-shortcut'] = 'border:outside';
				$other_attr['data-shortcut-location'] = blocksy_first_level_deep_link('woo_categories');

				if (is_single()) {
					$prefix = blocksy_manager()->screen->get_prefix();

					$other_attr['data-shortcut-location'] = blocksy_first_level_deep_link($prefix) . ':woo_has_related_upsells';
				}
			}

			$constrained_class = '';
			$visibility_classes = '';

			$related_class = [
				'related',
				'products'
			];

			if ($woocommerce_related_products_slideshow === 'slider') {
				$related_class[] = 'is-layout-slider';
			}

			if (blocksy_manager()->screen->uses_woo_default_template()) {
				$related_class[] = 'is-width-constrained';

				$related_class = array_merge(
					$related_class,
					blocksy_visibility_classes(
						blocksy_get_theme_mod(
							'related_products_visibility',
							[
								'desktop' => true,
								'tablet' => false,
								'mobile' => false,
							]
						),

						[
							'output' => 'array'
						]
					)
				);
			}

			$other_attr['class'] = implode(' ', $related_class);

			$woo_product_related_label_tag = blocksy_get_theme_mod(
				'woo_product_related_label_tag',
				'h2'
			);

			$related = preg_replace(
				'/<h2>(.*?)<\/h2>/',
				'<' . $woo_product_related_label_tag . ' class="ct-module-title">$1</' . $woo_product_related_label_tag . '>',
				$related
			);

			echo str_replace(
				'class="related products"',
				blocksy_attr_to_html($other_attr),
				$related
			);
		}
	},
	4,
	4
);

if (! function_exists('blocksy_get_product_default_variation')) {
	/**
	 * Retrieve the variation a variable product's default attributes resolve
	 * to, if any.
	 *
	 * Public wrapper for the WooCommerce integration internals, so the
	 * companion can consume it through the ThemeFunctions proxy instead of
	 * reaching into blocksy_manager().
	 *
	 * @since 2.1.58
	 *
	 * @param WC_Product $product The variable product.
	 *
	 * @return WC_Product_Variation|null
	 */
	function blocksy_get_product_default_variation($product) {
		$variation = blocksy_manager()->woocommerce->retrieve_product_default_variation(
			$product
		);

		if (! $variation) {
			return null;
		}

		return $variation;
	}
}

add_filter(
	'woocommerce_product_variation_get_gallery_image_ids',
	function ($value, $variation) {
		$variation_id = $variation->get_id();

		$source_id = blocksy_translate_post_id(
			$variation_id,
			[
				'use_wpml_default_language_woo' => true
			]
		);

		$legacy = get_post_meta($source_id, 'blocksy_post_meta_options', true);

		if (! is_array($legacy)) {
			$legacy = [];
		}

		$is_unmigrated_custom = (
			blocksy_akg('gallery_source', $legacy, 'default') === 'custom'
			&& empty($legacy['gallery_migrated'])
		);

		if ($is_unmigrated_custom) {
			$images = array_values(array_filter(wp_parse_id_list(
				array_column(blocksy_akg('images', $legacy, []), 'attachment_id')
			)));

			foreach (array_unique([$source_id, $variation_id]) as $id) {
				if (! empty(get_post_meta($id, '_product_image_gallery', true))) {
					continue;
				}

				$gallery = array_diff($images, [(int) get_post_thumbnail_id($id)]);

				if (empty($gallery)) {
					continue;
				}

				update_post_meta($id, '_product_image_gallery', implode(',', $gallery));
			}

			$legacy['gallery_migrated'] = true;
			update_post_meta($source_id, 'blocksy_post_meta_options', $legacy);
		}

		if (empty($value)) {
			$value = get_post_meta($variation_id, '_product_image_gallery', true);
		}

		return array_values(array_filter(wp_parse_id_list($value)));
	},
	10, 2
);

if (! function_exists('blocksy_product_get_gallery_images')) {
    function blocksy_product_get_gallery_images($product, $args = []) {
		$args = wp_parse_args(
			$args,
			[
				'enforce_first_image_replace' => false
			]
		);

		$root_product = $product;

		if ($product->post_type === 'product_variation') {
			$root_product = wc_get_product($product->get_parent_id());
		}

		$thumb_id = apply_filters(
			'woocommerce_product_get_image_id',
			get_post_thumbnail_id($root_product->get_id()),
			$root_product
		);

		$gallery_images = $root_product->get_gallery_image_ids();

		if ($thumb_id) {
			array_unshift($gallery_images, intval($thumb_id));
		} else {
			$gallery_images = [null];
		}

		if ($product->post_type === 'product_variation') {
			$variation_main_image = $product->get_image_id();

			$variation_gallery_images = $product->get_gallery_image_ids();

			if (empty($variation_gallery_images)) {
				if (
					! in_array($variation_main_image, $gallery_images)
				) {
					$gallery_images[0] = $variation_main_image;
				} else {
					if ($args['enforce_first_image_replace']) {
						array_unshift($gallery_images, $variation_main_image);
						$gallery_images = array_unique($gallery_images);
					}
				}
			} else {
				$gallery_images = [$variation_main_image];

				foreach ($variation_gallery_images as $variation_gallery_image) {
					$gallery_images[] = $variation_gallery_image;
				}
			}
		}

		return array_values(array_unique($gallery_images));
	}
}
