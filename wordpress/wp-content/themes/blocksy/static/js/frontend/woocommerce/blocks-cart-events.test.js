import {
	classifyBlocksCartEvent,
	requestInteractivityCartRefresh
} from './blocks-cart-events'

const ev = (type, detail) => ({ type, detail })

test('store sync from either block store is a change', () => {
	for (const type of ['from_iAPI', 'from_@wordpress/data']) {
		expect(
			classifyBlocksCartEvent(ev('wc-blocks_store_sync_required', { type }))
		).toBe('changed')
	}
})

test('other sync events and unrelated events are ignored', () => {
	expect(
		classifyBlocksCartEvent(
			ev('wc-blocks_store_sync_required', {
				type: 'shopper-list-item-added'
			})
		)
	).toBe(null)
	expect(classifyBlocksCartEvent(ev('click', { type: 'from_iAPI' }))).toBe(
		null
	)
	expect(classifyBlocksCartEvent(ev('wc-blocks_store_sync_required'))).toBe(
		null
	)
	expect(classifyBlocksCartEvent(null)).toBe(null)
})

test('block-originated add to cart is detected, jQuery re-dispatch is not', () => {
	expect(
		classifyBlocksCartEvent(
			ev('wc-blocks_added_to_cart', { preserveCartData: true })
		)
	).toBe('added')

	for (const detail of [{}, null, undefined, { preserveCartData: false }]) {
		expect(
			classifyBlocksCartEvent(ev('wc-blocks_added_to_cart', detail))
		).toBe(null)
	}
})

test('asking the iAPI store to refresh is not reported as a change', () => {
	const target = new EventTarget()
	const received = []

	target.addEventListener('wc-blocks_store_sync_required', (e) =>
		received.push(e)
	)

	requestInteractivityCartRefresh(target)

	expect(received).toHaveLength(1)
	// What WooCommerce's iAPI store listens for.
	expect(received[0].detail.type).toBe('from_@wordpress/data')
	expect(classifyBlocksCartEvent(received[0])).toBe(null)
})
