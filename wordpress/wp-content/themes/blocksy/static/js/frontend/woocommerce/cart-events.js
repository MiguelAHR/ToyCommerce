import $ from 'jquery'
import { classifyBlocksCartEvent } from './blocks-cart-events'

// Product added to the cart, by either flow — `cb` gets
// { source, fragments, button }, once per add. Blocks bring no fragments
// along, they arrive with the refresh started by cart-sync.js.
export const onCartAdded = (cb) => {
	$(document.body).on('added_to_cart', (_, fragments, __, button) => {
		cb({
			source: 'legacy',
			fragments: fragments || {},
			button: button?.[0] || null
		})
	})

	document.body.addEventListener('wc-blocks_added_to_cart', (event) => {
		if (classifyBlocksCartEvent(event) === 'added') {
			cb({ source: 'blocks', fragments: {}, button: null })
		}
	})
}
