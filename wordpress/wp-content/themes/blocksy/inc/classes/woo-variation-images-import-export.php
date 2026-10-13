<?php

namespace Blocksy;

class WooVariationImagesImportExport {
	private $column_id = 'blocksy_variation_images';

	public function __construct() {
		add_filter(
			'woocommerce_csv_product_import_mapping_options',
			[$this, 'import_column_name']
		);

		add_filter(
			'woocommerce_csv_product_import_mapping_default_columns',
			[$this, 'default_import_column_name']
		);

		add_action(
			'woocommerce_product_import_inserted_product_object',
			[$this, 'process_wc_import'],
			10, 2
		);
	}

	public function import_column_name($columns) {
		$columns[$this->column_id] = __('Blocksy Variation Images', 'blocksy');

		return $columns;
	}

	public function default_import_column_name($columns) {
		$columns[__('Blocksy Variation Images', 'blocksy')] = $this->column_id;

		return $columns;
	}

	public function process_wc_import($product, $data) {
		$product_id = $product->get_id();

		if (! isset($data[$this->column_id])) {
			return;
		}

		if (empty($data[$this->column_id])) {
			return;
		}

		$raw_data = json_decode($data[$this->column_id], true);

		if (! is_array($raw_data)) {
			return;
		}

		$raw_data = array_intersect_key(
			$raw_data,
			array_flip(['gallery_source', 'images'])
		);

		if (empty($raw_data)) {
			return;
		}

		if (isset($raw_data['images'])) {
			$gallery_images = [];

			foreach ((array) $raw_data['images'] as $image) {
				$url = blocksy_akg('url', $image, '');

				if (! is_string($url) || empty($url)) {
					continue;
				}

				$attachment_id = \Blocksy\WooImportExport::get_attachment_id_from_url($url, $product_id);

				if (is_wp_error($attachment_id)) {
					continue;
				}

				$gallery_images[] = [
					'attachment_id' => $attachment_id,
					'url' => wp_get_attachment_url($attachment_id)
				];
			}

			$raw_data['images'] = $gallery_images;
		}

		update_post_meta(
			$product_id,
			'blocksy_post_meta_options',
			blocksy_sanitize_post_meta_options($raw_data)
		);
	}
}

