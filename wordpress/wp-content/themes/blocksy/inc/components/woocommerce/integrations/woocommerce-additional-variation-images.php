<?php

// https://woocommerce.com/products/woocommerce-additional-variation-images/

add_action('init', function () {
	if (! blocksy_woocommerce_has_flexy_view()) {
		return;
	}

	add_action(
		'wp_enqueue_scripts',
		function () {
			wp_dequeue_script('wc_additional_variation_images_script');
		},
		500
	);
});

if (! function_exists('blocksy_get_variation_video_payload')) {
	function blocksy_get_variation_video_payload($attachment_id) {
		$video_data = blocksy_get_video_data($attachment_id);

		if (empty($video_data['url'])) {
			return null;
		}

		return array_intersect_key(
			$video_data,
			array_flip([
				'media_video_event',
				'media_video_autoplay',
				'media_video_hover_revert',
				'media_video_player',
			])
		);
	}
}

add_filter(
	'woocommerce_available_variation',
	function ($result, $product, $variation) {
		if (! blocksy_woocommerce_has_flexy_view()) {
			return $result;
		}

		$original_image = wc_get_product_attachment_props(
			$product->get_image_id()
		);

		$original_image['id'] = $product->get_image_id();

		if (
			! isset($original_image['url'])
			||
			empty($original_image['url'])
		) {
			$original_image['src'] = wc_placeholder_img_src('full');
		}

		$original_video = blocksy_get_variation_video_payload(
			$product->get_image_id()
		);

		if ($original_video !== null) {
			$original_image['blocksy_video'] = $original_video;
		}

		$result['blocksy_original_image'] = $original_image;

		unset($result['blocksy_original_image']['url']);

		if (isset($result['image_id'])) {
			$variation_video = blocksy_get_variation_video_payload(
				$result['image_id']
			);

			if ($variation_video !== null) {
				$result['image']['blocksy_video'] = $variation_video;
			}
		}

		if (wp_doing_ajax()) {
			$gallery_args = [
				'product' => $product,
				'forced_single' => true,
			];

			remove_action(
				'woocommerce_product_thumbnails',
				'woocommerce_show_product_thumbnails',
				20
			);

			global $blocksy_current_variation;

			if ($variation) {
				$blocksy_current_variation = $variation;
			}

			$result['blocksy_gallery_html'] = blocksy_render_view(
				dirname(__FILE__) . '/../single/woo-gallery-template.php',
				$gallery_args
			);

			$blocksy_current_variation = null;
		}

		return $result;
	},
	10, 3
);

add_action(
	'wp_ajax_blocksy_get_product_view_for_variation',
	'blocksy_get_product_view_for_variation'
);

add_action(
	'wp_ajax_nopriv_blocksy_get_product_view_for_variation',
	'blocksy_get_product_view_for_variation'
);

function blocksy_get_product_view_for_variation() {
	if (! isset($_GET['product_id'])) {
		wp_send_json_error();
	}

	$product = wc_get_product(absint($_GET['product_id']));

	if (! $product) {
		wp_send_json_error();
	}

	if (
		$product->get_status() !== 'publish'
		||
		post_password_required($product->get_id())
	) {
		wp_send_json_error();
	}

	$gallery_args = [
		'product' => $product,
		'forced_single' => true,
		'skip_default_variation' => true
	];

	if (
		isset($_GET['retrieve_json'])
		&&
		$_GET['retrieve_json'] === 'yes'
	) {
		if (isset($_GET['variation_id'])) {
			$product = wc_get_product(absint($_GET['variation_id']));
		}

		if (! $product) {
			wp_send_json_error();
		}

		$parent_product = $product;

		if ($product->get_parent_id()) {
			$parent_product = wc_get_product($product->get_parent_id());
		}

		if (
			! $parent_product
			||
			$parent_product->get_status() !== 'publish'
			||
			post_password_required($parent_product->get_id())
		) {
			wp_send_json_error();
		}

		$images_ids = blocksy_product_get_gallery_images(
			$product,
			[
				'enforce_first_image_replace' => true
			]
		);

		$images_ids = array_slice($images_ids, 0, 2);

		$images = [];

		foreach ($images_ids as $image_id) {
			$image_data = wc_get_product_attachment_props($image_id);
			$image_data['id'] = $image_id;

			$image_video = blocksy_get_variation_video_payload($image_id);

			if ($image_video !== null) {
				$image_data['blocksy_video'] = $image_video;
			}

			$images[] = $image_data;
		}

		wp_send_json_success([
			'images' => $images
		]);
	}

	if (
		isset($_GET['is_quick_view'])
		&&
		$_GET['is_quick_view'] === 'yes'
	) {
		global $blocksy_is_quick_view;
		$blocksy_is_quick_view = true;

		$gallery_args['forced_single'] = false;
	}

	remove_action(
		'woocommerce_product_thumbnails',
		'woocommerce_show_product_thumbnails',
		20
	);

	if (isset($_GET['variation_id'])) {
		$variation_id = false;

		if (isset($_GET['variation_id'])) {
			$variation_id = absint($_GET['variation_id']);
		}

		if ($variation_id) {
			$variation = wc_get_product($variation_id);

			global $blocksy_current_variation;

			if ($variation) {
				$blocksy_current_variation = $variation;
			}
		}
	}

	wp_send_json_success([
		'html' => blocksy_render_view(
			dirname(__FILE__) . '/../single/woo-gallery-template.php',
			$gallery_args
		)
	]);
}
