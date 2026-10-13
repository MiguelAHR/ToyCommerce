import { getColumnsLayout } from './columns-layout'

// Shared with the PHP test (tests/phpunit/footer-columns-layout-test.php) so
// both implementations are verified against the same cases.
import fixtures from '../../../../tests/fixtures/footer-columns-layout.json'

const valuesFor = ({ count, layout }) => {
	const values = { items_per_row: count }

	if (layout !== null) {
		values[`${count}_columns_layout`] = layout
	}

	return values
}

describe('getColumnsLayout', () => {
	fixtures.forEach((fixture) => {
		test(fixture.name, () => {
			const columns = getColumnsLayout(valuesFor(fixture))

			expect({
				desktop: columns.desktop,
				tablet: columns.tablet,
				mobile: columns.mobile,
			}).toStrictEqual(fixture.expected)
		})
	})
})
