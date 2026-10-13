import $ from 'jquery'
import ctEvents from 'ct-events'

const sendLocation = () => {
	wp.customize.selectiveRefresh.bind('partial-content-rendered', (e) => {
		if (!e.container) {
			return
		}

		if ($) {
			$('.wc-tabs-wrapper, .woocommerce-tabs').trigger('init')

			$('#rating').each((_, el) => {
				const rating = $(el)
				const ratingWrapper = rating.closest('.comment-form-rating')

				if (ratingWrapper.find('p.stars').length) {
					return
				}

				rating.trigger('init')
			})
		}

		window.ctEvents.trigger('blocksy:frontend:init')
	})
}

wp.customize.bind('ready', () => sendLocation())
wp.customize.bind('preview-ready', () => sendLocation())
