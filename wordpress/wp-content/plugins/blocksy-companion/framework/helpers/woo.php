<?php

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Get product IDs that are on sale.
 *
 * Uses WooCommerce's wc_get_product_ids_on_sale() but allows filtering
 * for compatibility with dynamic pricing plugins like Advanced Dynamic Pricing.
 */
function blocksy_companion_get_product_ids_on_sale() {
	/**
	 * Filters the product IDs that are on sale.
	 *
	 * @since 2.1.27
	 *
	 * @param array $product_ids Product IDs, as returned by `wc_get_product_ids_on_sale()`.
	 */
	return apply_filters(
		'blocksy:helpers:woo:on-sale-product-ids',
		wc_get_product_ids_on_sale()
	);
}
