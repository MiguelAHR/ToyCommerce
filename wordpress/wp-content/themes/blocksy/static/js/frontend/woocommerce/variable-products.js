import $ from 'jquery'
import ctEvents from 'ct-events'

import cachedFetch from '@creative-themes/wordpress-helpers/cached-fetch'

import { syncVideoDecorations } from './video-decorations'

let originalImageUpdate = null

function isTouchDevice() {
	try {
		document.createEvent('TouchEvent')
		return true
	} catch (e) {
		return false
	}
}

const makeUrlFor = ({ variation, productId, isQuickView }) => {
	let url = new URL(ct_localizations.ajax_url)
	let params = new URLSearchParams(url.search.slice(1))

	params.append('action', 'blocksy_get_product_view_for_variation')

	if (variation) {
		params.append('variation_id', variation.variation_id)
	}

	params.append('product_id', productId)
	params.append('is_quick_view', isQuickView)

	url.search = `?${params.toString()}`

	return url.toString()
}

const replaceFirstImage = ({ container, image }) => {
	if (!image) {
		return
	}

	const containersToReplace = []

	const selectorsToTry = [
		'.woocommerce-product-gallery .ct-product-gallery-container > .ct-media-container',
		'.woocommerce-product-gallery .ct-stacked-gallery-container > .ct-media-container:first-child',
		'.woocommerce-product-gallery .flexy-items > *:first-child > *',
		'.woocommerce-product-gallery .flexy-pills > ol > *:first-child > *'
	]

	selectorsToTry.map((selector) => {
		if (container.parentNode.querySelector(selector)) {
			containersToReplace.push(
				container.parentNode.querySelector(selector)
			)
		}
	})

	syncVideoDecorations({ containers: containersToReplace, image })

	containersToReplace.map((imgContainer) => {
		if (imgContainer.href) {
			imgContainer.href = image.full_src
		}

		if (imgContainer.dataset.src) {
			imgContainer.dataset.src = image.full_src
		}

		if (imgContainer.dataset.height) {
			imgContainer.dataset.height = image.full_src_h
		}

		if (imgContainer.dataset.width) {
			imgContainer.dataset.width = image.full_src_w
		}

		;[...imgContainer.querySelectorAll('.zoomImg')].map((img) => {
			img.remove()
		})
		;[...imgContainer.querySelectorAll('img, source')].map((img) => {
			if (img.matches('.zoomImg')) {
				return
			}

			if (img.getAttribute('width')) {
				img.width =
					image.width ||
					(img.closest('.flexy-pills')
						? image.gallery_thumbnail_src_w
						: image.src_w)
			}

			if (img.getAttribute('height')) {
				img.height =
					image.height ||
					(img.closest('.flexy-pills')
						? image.gallery_thumbnail_src_h
						: image.src_h)
			}

			img.src = img.closest('.flexy-pills')
				? image.gallery_thumbnail_src
				: image.src

			// if (image.srcset && img.srcset && image.srcset !== 'false') {
			if (image.srcset && image.srcset !== 'false') {
				img.srcset = image.srcset
			} else {
				img.removeAttribute('srcset')
			}
		})

		setTimeout(() => {
			if ($.fn.zoom) {
				if (
					(window.wp &&
						wp.customize &&
						wp.customize('has_product_single_zoom') &&
						wp.customize('has_product_single_zoom')() === 'yes') ||
					!window.wp ||
					!wp.customize
				) {
					const rect = imgContainer.getBoundingClientRect()

					if (
						parseFloat(imgContainer.getAttribute('data-width')) >
						imgContainer
							.closest('.woocommerce-product-gallery')
							.getBoundingClientRect().width
					) {
						$(imgContainer).zoom({
							url: imgContainer.href || imgContainer.dataset.src,
							touch: false,
							duration: 50,

							...(rect.width >
								parseFloat(imgContainer.dataset.width) ||
							rect.height >
								parseFloat(imgContainer.dataset.height)
								? {
										magnify: 2
									}
								: {}),

							...(isTouchDevice()
								? {
										on: 'toggle'
									}
								: {})
						})
					}
				}
			}
		}, 5)
	})
}

// Fire a window resize to match what WooCommerce does after a gallery/image
// update. WC core and the gallery integrations layered around it (zoom,
// flexslider, and plugins like YITH badge positioning, whose
// force-badge-positioning.js re-clones badges on resize) only recompute their
// layout/positions on a resize event. Whenever we change the gallery DOM —
// either in place or by swapping it for a variation — we nudge them so they
// reflow against the new markup, exactly as they would on a native WC variation
// image update. The small delay lets the new DOM settle first.
const triggerGalleryReflow = () => {
	setTimeout(() => {
		$(window).trigger('resize')
	}, 20)
}

const performInPlaceUpdate = ({
	container,
	currentVariationObj,
	nextVariationObj
}) => {
	const currentImage = currentVariationObj
		? {
				id: currentVariationObj.image_id,
				...(currentVariationObj.image?.src
					? { ...currentVariationObj.image }
					: {
							...currentVariationObj.blocksy_original_image
						})
			}
		: (nextVariationObj || {}).blocksy_original_image

	const nextImage = nextVariationObj
		? {
				id: nextVariationObj.image_id,
				...(nextVariationObj.image?.src
					? { ...nextVariationObj.image }
					: {
							...nextVariationObj.blocksy_original_image
						})
			}
		: (currentVariationObj || {}).blocksy_original_image

	if (!nextImage) {
		return
	}

	if (
		currentImage &&
		parseFloat(nextImage.id) === parseFloat(currentImage.id)
	) {
		return
	}

	// Attempt slide to image

	if (container.querySelector(`.flexy-pills > *`)) {
		let maybePillImage =
			container.querySelector(
				`.flexy-items [srcset*="${nextImage.src}"]`
			) ||
			container.querySelector(`.flexy-items [src*="${nextImage.src}"]`)

		if (maybePillImage) {
			let pillIndex = [
				...container.querySelector(`.flexy-items`).children
			].indexOf(maybePillImage.closest('div'))

			const pill =
				container.querySelector(`.flexy-pills > *`).children[pillIndex]

			if (pill) {
				if (
					container
						.querySelector('[data-flexy]')
						.dataset.flexy.indexOf('no') > -1
				) {
					if (container.querySelector('[data-flexy]').forcedMount) {
						container.querySelector('[data-flexy]').forcedMount()
					}

					setTimeout(() => {
						if (nextVariationObj) {
							replaceFirstImage({
								container,
								image: nextVariationObj.blocksy_original_image
							})
						}
						pill.click()
					}, 500)

					return
				} else {
					if (nextVariationObj) {
						replaceFirstImage({
							container,
							image: nextVariationObj.blocksy_original_image
						})
					}

					pill.click()
					return
				}
			}
		}
	}

	// Replace 1st image

	replaceFirstImage({ container, image: nextImage })

	if (container.querySelector(`.flexy-pills > *`)) {
		const pill = container.querySelector(`.flexy-pills > *`).children[0]

		if (pill) {
			pill.click()
		}
	}

	triggerGalleryReflow()
}

export const mount = (el) => {
	if (!$ || !$.fn || !$.fn.wc_variations_image_update) {
		return
	}

	originalImageUpdate = $.fn.wc_variations_image_update

	// TODO: Attempt to rely only on found_variation event that woo triggers
	$.fn.wc_variations_image_update = function (variation) {
		const currentElement = this[0]

		if (
			currentElement.closest('.woobt-products') ||
			currentElement.closest('.upsells') ||
			currentElement.closest('.related') ||
			!el.closest('.product') ||
			!el
				.closest('.product')
				.querySelector('.woocommerce-product-gallery')
		) {
			return
		}

		const currentVariation = el
			.closest('.product')
			.querySelector('.woocommerce-product-gallery')

		let productContainer = currentVariation.closest('.type-product')

		let isQuickView = 'no'

		let productId = productContainer.id.replace('product-', '')

		if (!productId) {
			productId = currentVariation
				.closest('[class*="ct-quick-view"]')
				.querySelector('[data-product_id]').dataset.product_id

			if (productId) {
				isQuickView = 'yes'
			}
		}

		const allVariations = JSON.parse(el.dataset.product_variations)

		let nextVariationObj = false
		let currentVariationObj = false

		if (allVariations) {
			if (variation.variation_id) {
				nextVariationObj = allVariations.find(
					({ variation_id }) =>
						parseInt(variation_id) ===
						parseInt(variation.variation_id)
				)
			}

			if (currentVariation.dataset.currentVariation) {
				currentVariationObj = allVariations.find(
					({ variation_id }) =>
						parseInt(variation_id) ===
						parseInt(currentVariation.dataset.currentVariation)
				)
			}
		}

		let defaultCanDoInPlaceUpdate = '__DEFAULT__'

		if (
			variation &&
			!variation.variation_id &&
			currentElement.querySelector('.wcpa_form_outer')
		) {
			defaultCanDoInPlaceUpdate = true
			nextVariationObj = variation
		}

		if (
			defaultCanDoInPlaceUpdate === '__DEFAULT__' &&
			!variation.variation_id &&
			!currentVariation.dataset.currentVariation
		) {
			return
		}

		if (
			defaultCanDoInPlaceUpdate === '__DEFAULT__' &&
			parseInt(variation.variation_id) ===
				parseInt(currentVariation.dataset.currentVariation)
		) {
			return
		}

		if (
			variation.variation_id ||
			defaultCanDoInPlaceUpdate === '__DEFAULT__'
		) {
			currentVariation.dataset.currentVariation =
				variation.variation_id || '0'
		} else {
			currentVariation.removeAttribute('data-current-variation')
		}

		const canDoInPlaceUpdate =
			defaultCanDoInPlaceUpdate === '__DEFAULT__'
				? allVariations &&
					[nextVariationObj, currentVariationObj].every(
						(variation) => {
							if (!variation) {
								return true
							}

							return (
								!variation.gallery_image_ids ||
								variation.gallery_image_ids.length === 0
							)
						}
					)
				: defaultCanDoInPlaceUpdate

		// TODO: add better check for in place update
		if (canDoInPlaceUpdate) {
			performInPlaceUpdate({
				container: currentVariation,
				nextVariationObj,
				currentVariationObj
			})

			return
		}

		const acceptHtml = (html) => {
			// Build the fresh gallery markup as real nodes in a detached buffer,
			// so we can move the real (plugin-mutated) foreign nodes across
			// instead of recreating everything from an HTML string and orphaning
			// references other scripts hold onto (e.g. YITH badge positioning
			// caches its badge nodes once and never re-queries them).
			const buffer = document.createElement('div')
			buffer.innerHTML = html

			const oldContainer = currentVariation.querySelector(
				'.ct-product-gallery-container, .flexy-container'
			)
			const newContainer = buffer.querySelector(
				'.ct-product-gallery-container, .flexy-container'
			)

			if (oldContainer && newContainer) {
				const ownedGalleryContainers =
					'.flexy-container, .container-image-and-badge, .ct-stacked-gallery-container'

				// Foreign = nodes Blocksy didn't render — markup other plugins
				// inject into the gallery. The concrete case this solves is YITH
				// WooCommerce Badge Management: it adds `.yith-wcbm-badge` nodes
				// (via the woocommerce_single_product_image_thumbnail_html filter)
				// as siblings after `.flexy-container`, toggles their per-variation
				// visibility client-side, and `force-badge-positioning.js` caches
				// those exact nodes once at init. If we recreate them on every
				// variation swap its cache points at detached nodes and the badge
				// positioning breaks — so we must keep the *real* badge nodes.
				//
				// Blocksy's own gallery nodes are: `.flexy-container` and its
				// `flexy-*` children (`.flexy`, `.flexy-pills`, `.flexy-arrow-*`),
				// the stacked-gallery wrapper, the `.woocommerce-product-gallery__trigger`
				// lightbox link, and the single-image `<figure>`. Everything else
				// is foreign. YITH may also wrap the image+badges in
				// `.container-image-and-badge`, so collectForeign() recurses into
				// Blocksy-owned gallery containers to reach foreign nodes wherever
				// they sit.
				const isForeign = (el) =>
					!el.matches(
						`${ownedGalleryContainers}, .woocommerce-product-gallery__trigger, figure`
					) &&
					![...el.classList].some((c) => c.indexOf('flexy') === 0)

				const collectForeign = (root) => {
					const foreign = []

					const scan = (node) => {
						;[...node.children].forEach((child) => {
							if (child.matches(ownedGalleryContainers)) {
								scan(child)
								return
							}

							if (isForeign(child)) {
								foreign.push(child)
							}
						})
					}

					scan(root)

					return foreign
				}

				// Drop the freshly-rendered duplicates from the new markup, then
				// move the REAL foreign nodes over — preserving their identity and
				// any client-side state already applied to them.
				collectForeign(newContainer).forEach((el) => el.remove())
				collectForeign(oldContainer).forEach((el) =>
					newContainer.appendChild(el)
				)

				oldContainer.replaceWith(newContainer)
			}

			currentVariation.removeAttribute('data-thumbs')

			const incomingGallery = buffer.querySelector(
				'.woocommerce-product-gallery'
			)

			if (
				currentVariation.dataset.gallery === 'default' &&
				incomingGallery &&
				incomingGallery.dataset.thumbs
			) {
				currentVariation.dataset.thumbs = incomingGallery.dataset.thumbs
			}

			currentVariation.hasLazyLoadClickHoverListener = false

			setTimeout(() => {
				ctEvents.trigger('blocksy:frontend:init')

				currentVariation.removeAttribute('data-state')

				triggerGalleryReflow()
			}, 20)
		}

		if (variation.blocksy_gallery_html) {
			acceptHtml(variation.blocksy_gallery_html)

			return
		}

		currentVariation.removeAttribute('style')
		requestAnimationFrame(() => {
			currentVariation.dataset.state = 'loading'
		})

		cachedFetch(
			makeUrlFor({
				variation,
				productId,
				isQuickView
			})
		)
			.then((response) => response.json())
			.then(({ success, data }) => {
				if (!success) {
					return
				}

				acceptHtml(data.html)
			})
	}
}
