const positionArrow = (target, tooltip) => {
	const targetRect = target.getBoundingClientRect()
	const tooltipRect = tooltip.getBoundingClientRect()

	const targetCenter = targetRect.left + targetRect.width / 2

	// Arrow is 10px wide (border-inline: 5px) — offset by half its width so
	// its tip points at the target center even when the tooltip is shifted to
	// stay in the viewport.
	const arrowPosition = Math.round(targetCenter - tooltipRect.left - 5)

	tooltip.style.setProperty(
		'--ct-tooltip-arrow-position',
		`${arrowPosition}px`
	)
}

const showTooltip = (target, tooltip) => {
	positionArrow(target, tooltip)
	tooltip.classList.add('ct-active')
}

export const mount = (target) => {
	// The hovered target usually holds the tooltip directly (e.g. a variation
	// swatch), and is also what the arrow points at.
	let anchor = target
	let tooltip = target.querySelector(':scope > .ct-tooltip')

	// When it doesn't (e.g. a price-filter range input, which can't hold
	// children), the tooltip lives in the next sibling — the hover stays on the
	// target, but the tooltip and its arrow anchor to that sibling.
	if (!tooltip) {
		anchor = target.nextElementSibling
		tooltip = anchor && anchor.querySelector(':scope > .ct-tooltip')
	}

	if (!tooltip) {
		return
	}

	// The `hover` trigger mounts once, so show on this initial hover and wire
	// up enter/leave to keep toggling on subsequent hovers.
	showTooltip(anchor, tooltip)

	target.addEventListener('mouseenter', () => showTooltip(anchor, tooltip))
	target.addEventListener('mouseleave', () => tooltip.classList.remove('ct-active'))

	// Keep the arrow aligned as the price label width changes while dragging.
	if (target.tagName === 'INPUT') {
		target.addEventListener('input', () => positionArrow(anchor, tooltip))
	}
}
