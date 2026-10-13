<?php

namespace Blocksy\DbVersioning;

class V211 {
	public function migrate() {
		if (
			! function_exists('wc_get_attribute_taxonomies')
			||
			! class_exists('\Blocksy\Plugin')
			||
			! in_array(
				'woocommerce-extra',
				get_option('blocksy_active_extensions', [])
			)
		) {
			return;
		}

		$settings = get_option('blocksy_ext_woocommerce_extra_settings', []);

		if (! is_array($settings)) {
			return;
		}

		if (
			! isset($settings['features']['variation-swatches'])
			||
			! $settings['features']['variation-swatches']
		) {
			return;
		}

		if (! function_exists('blocksy_get_woo_archive_layout_defaults')) {
			return;
		}

		$woo_card_layout = get_theme_mod(
			'woo_card_layout',
			blocksy_get_woo_archive_layout_defaults()
		);

		$touched = false;

		foreach ($woo_card_layout as $index => $layer) {
			if ($layer['id'] !== 'product_swatches') {
				continue;
			}

			if (
				! isset($layer['options']['limit_number_of_swatches'])
				||
				$layer['options']['limit_number_of_swatches'] === 'no'
			) {
				continue;
			}

			$limit = blocksy_akg('limit', $layer['options'], 10);

			set_theme_mod('limit_number_of_swatches', 'yes');
			set_theme_mod('archive_limit_number_of_swatches_number', $limit);
		}
	}
}


