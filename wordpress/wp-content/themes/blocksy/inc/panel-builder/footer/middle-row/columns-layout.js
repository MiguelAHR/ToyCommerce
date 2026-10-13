import { maybePromoteScalarValueIntoResponsive } from '@creative-themes/customizer-sync-helpers/dist/promote-into-responsive'

// Small screens can only stack or split into two columns, so any other
// layout collapses to a stacked layout. Mirrors the clamp in
// middle-row/dynamic-styles.php and the PHP renderer so the divider gate
// stays aligned with the rendered grid.
const SMALL_SCREEN_CHOICES = ['initial', 'repeat(2, 1fr)']

// Normalizes a footer row's stored `*_columns_layout` value (which may be a
// full responsive object, a partial object, a bare scalar, or absent) into a
// complete { desktop, tablet, mobile } shape with stacked small screens.
export const getColumnsLayout = (values) => {
	const count = parseInt(values.items_per_row, 10)

	const defaults = {
		desktop: count >= 2 && count <= 6 ? `repeat(${count}, 1fr)` : 'initial',
		tablet: 'initial',
		mobile: 'initial'
	}

	if (count < 2 || count > 6) {
		return defaults
	}

	const stored = values[`${count}_columns_layout`]

	if (stored === undefined || stored === null) {
		return defaults
	}

	const columns = {
		...defaults,
		...maybePromoteScalarValueIntoResponsive(stored)
	}

	if (SMALL_SCREEN_CHOICES.indexOf(columns.tablet) === -1) {
		columns.tablet = 'initial'
	}

	if (SMALL_SCREEN_CHOICES.indexOf(columns.mobile) === -1) {
		columns.mobile = 'initial'
	}

	return columns
}
