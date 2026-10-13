import $ from 'jquery'
import {
	classifyBlocksCartEvent,
	LEGACY_CART_CHANGE_EVENTS,
	requestInteractivityCartRefresh
} from './blocks-cart-events'

// Each flow only updates its own cart views — bring the other one's up to
// date. Mounted on page load when blocks are there (handle-events.js): the
// header count must follow a Cart block change even if the header cart was
// never touched.

export const mount = () => {
	window.addEventListener('wc-blocks_store_sync_required', (event) => {
		if (classifyBlocksCartEvent(event) === 'changed') {
			$(document.body).trigger('wc_fragment_refresh')
		}
	})

	$(document.body).on(LEGACY_CART_CHANGE_EVENTS.join(' '), () => {
		window.wp?.data
			?.dispatch('wc/store/cart')
			?.invalidateResolutionForStoreSelector('getCartData')

		requestInteractivityCartRefresh()
	})
}
