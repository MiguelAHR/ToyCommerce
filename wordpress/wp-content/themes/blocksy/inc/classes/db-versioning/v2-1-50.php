<?php

namespace Blocksy\DbVersioning;

class V2150 {
	/**
	 * `has_ajax_add_to_cart` moved from a top-level theme mod into the
	 * `product_add_to_cart` single product layer option. Only a non-default
	 * (`no`) value needs carrying over — the layer option defaults to `yes`
	 * like the old top-level one did.
	 */
	public function migrate() {
		$old_value = get_theme_mod('has_ajax_add_to_cart', '__not_set__');

		if ($old_value === '__not_set__' || $old_value === 'yes') {
			return;
		}

		if (! function_exists('blocksy_get_woo_single_layout_defaults')) {
			return;
		}

		$this->apply_to_mod(
			'woo_single_layout',
			blocksy_get_woo_single_layout_defaults(),
			$old_value
		);

		$split_defaults = [
			'left' => blocksy_get_woo_single_layout_defaults('left'),
			'right' => blocksy_get_woo_single_layout_defaults('right'),
		];

		$split_layout = get_theme_mod('woo_single_split_layout', $split_defaults);

		if (! is_array($split_layout)) {
			$split_layout = $split_defaults;
		}

		foreach (['left', 'right'] as $side) {
			if (! isset($split_layout[$side]) || ! is_array($split_layout[$side])) {
				continue;
			}

			$split_layout[$side] = $this->apply_to_layers(
				$split_layout[$side],
				$old_value
			);
		}

		set_theme_mod('woo_single_split_layout', $split_layout);

		remove_theme_mod('has_ajax_add_to_cart');
	}

	private function apply_to_mod($mod_name, $default_layout, $value) {
		$layout = get_theme_mod($mod_name, $default_layout);

		if (! is_array($layout)) {
			$layout = $default_layout;
		}

		set_theme_mod($mod_name, $this->apply_to_layers($layout, $value));
	}

	private function apply_to_layers($layout, $value) {
		foreach ($layout as $index => $layer) {
			if (! isset($layer['id']) || $layer['id'] !== 'product_add_to_cart') {
				continue;
			}

			$layout[$index]['has_ajax_add_to_cart'] = $value;
		}

		return $layout;
	}
}
