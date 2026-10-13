// WooCommerce reports cart changes through two unrelated flows:
//
// - legacy — jQuery `added_to_cart`, `removed_from_cart`, `updated_wc_div`
//   on document.body (classic AJAX add to cart, mini cart, classic cart).
// - blocks — native events (Product Collection, Cart, Checkout, ...):
//   - `wc-blocks_added_to_cart`, also re-dispatched by WooCommerce from the
//     jQuery `added_to_cart`. Only the ones the blocks fire themselves carry
//     `preserveCartData`.
//   - `wc-blocks_store_sync_required` on window, once per mutation of either
//     block store (`from_iAPI`, `from_@wordpress/data`).
//
// cart-sync.js and cart-events.js build on these to handle both. This is
// the part that decides which events count.

// Blocks backed by one of the two block cart stores.
export const BLOCKS_CART_SELECTOR = [
	// @wordpress/data store
	'.wp-block-woocommerce-cart',
	'.wp-block-woocommerce-checkout',
	'.wp-block-woocommerce-all-products',
	'.wp-block-woocommerce-mini-cart',

	// Interactivity API store — Product Collection buttons, Add to Cart +
	// Options, the iAPI Mini-Cart
	'[data-wp-interactive*="woocommerce/product-button"]',
	'[data-wp-interactive*="woocommerce/add-to-cart"]',
	'[data-wp-interactive*="woocommerce/mini-cart"]'
].join(', ')

export const LEGACY_CART_CHANGE_EVENTS = [
	'added_to_cart',
	'removed_from_cart',
	'updated_wc_div'
]

// Returns `added`, `changed` or null for a native blocks event.
export const classifyBlocksCartEvent = (event) => {
	if (!event || !event.detail) {
		return null
	}

	if (event.type === 'wc-blocks_added_to_cart') {
		if (!event.detail.preserveCartData) {
			return null
		}

		return 'added'
	}

	if (event.type !== 'wc-blocks_store_sync_required') {
		return null
	}

	if (event.detail.blocksy) {
		return null
	}

	if (!['from_iAPI', 'from_@wordpress/data'].includes(event.detail.type)) {
		return null
	}

	return 'changed'
}

// The iAPI store (Product Collection "N in cart" buttons, ...) refetches the
// cart when it receives a `from_@wordpress/data` sync event. It's WooCommerce's
// internal cross-store event, not a public API — if it ever changes, those
// buttons go stale until the next page load. Marked as ours so
// classifyBlocksCartEvent() ignores it.
export const requestInteractivityCartRefresh = (target = window) =>
	target.dispatchEvent(
		new CustomEvent('wc-blocks_store_sync_required', {
			detail: { type: 'from_@wordpress/data', blocksy: true }
		})
	)
