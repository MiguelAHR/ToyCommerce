import { handleBackgroundOptionFor } from '../../../../static/js/customizer/sync/variables/background'
import ctEvents from 'ct-events'
import { typographyOption } from '../../../../static/js/customizer/sync/variables/typography'
import {
	getRootSelectorFor,
	assembleSelector,
	mutateSelector,
	responsiveClassesFor,
	withKeys,
} from '../../../../static/js/customizer/sync/helpers'
import { getColumnsLayout } from './columns-layout'

export const handleRowVariables = ({ itemId }) => ({
	rowTopBottomSpacing: {
		selector: assembleSelector(
			mutateSelector({
				selector: getRootSelectorFor({ itemId, panelType: 'footer' }),
				operation: 'suffix',
				to_add: '> div',
			})
		),
		variable: 'container-spacing',
		responsive: true,
		unit: '',
	},

	footerItemsGap: {
		selector: assembleSelector(
			mutateSelector({
				selector: getRootSelectorFor({ itemId, panelType: 'footer' }),
				operation: 'suffix',
				to_add: '> div',
			})
		),
		variable: 'columns-gap',
		responsive: true,
		unit: 'px',
	},

	footerWidgetsGap: {
		selector: assembleSelector(
			mutateSelector({
				selector: getRootSelectorFor({ itemId, panelType: 'footer' }),
				operation: 'suffix',
				to_add: '> div',
			})
		),
		variable: 'widgets-gap',
		responsive: true,
		unit: 'px',
	},

	footer_row_vertical_alignment: {
		selector: assembleSelector(
			mutateSelector({
				selector: getRootSelectorFor({ itemId, panelType: 'footer' }),
				operation: 'suffix',
				to_add: '> div',
			})
		),
		variable: 'vertical-alignment',
		responsive: true,
		unit: '',
	},

	...typographyOption({
		id: 'footerWidgetsTitleFont',

		selector: assembleSelector(
			mutateSelector({
				selector: getRootSelectorFor({ itemId, panelType: 'footer' }),
				operation: 'suffix',
				to_add: '.widget-title',
			})
		),
	}),

	...withKeys(
		['footerRowTopDivider', 'footerRowTopBorderFullWidth'],
		[
			{
				selector: assembleSelector(
					getRootSelectorFor({ itemId, panelType: 'footer' })
				),
				variable: 'theme-border-top',
				type: 'border',
				responsive: true,

				fullValue: true,

				extractValue: ({
					footerRowTopDivider,
					footerRowTopBorderFullWidth,
				}) =>
					footerRowTopBorderFullWidth === 'yes'
						? footerRowTopDivider
						: {
								desktop: { style: 'none' },
								tablet: { style: 'none' },
								mobile: { style: 'none' },
						  },
			},

			{
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({
							itemId,
							panelType: 'footer',
						}),
						operation: 'suffix',
						to_add: '> div',
					})
				),
				variable: 'theme-border-top',
				type: 'border',
				responsive: true,
				fullValue: true,

				extractValue: ({
					footerRowTopDivider,
					footerRowTopBorderFullWidth,
				}) =>
					footerRowTopBorderFullWidth !== 'yes'
						? footerRowTopDivider
						: {
								desktop: { style: 'none' },
								tablet: { style: 'none' },
								mobile: { style: 'none' },
						  },
			},
		]
	),

	...withKeys(
		['footerRowBottomDivider', 'footerRowBottomBorderFullWidth'],
		[
			{
				selector: assembleSelector(
					getRootSelectorFor({ itemId, panelType: 'footer' })
				),
				variable: 'theme-border-bottom',
				type: 'border',
				responsive: true,

				fullValue: true,

				extractValue: ({
					footerRowBottomDivider,
					footerRowBottomBorderFullWidth,
				}) =>
					footerRowBottomBorderFullWidth === 'yes'
						? footerRowBottomDivider
						: {
								desktop: { style: 'none' },
								tablet: { style: 'none' },
								mobile: { style: 'none' },
						  },
			},

			{
				selector: assembleSelector(
					mutateSelector({
						selector: getRootSelectorFor({
							itemId,
							panelType: 'footer',
						}),
						operation: 'suffix',
						to_add: '> div',
					})
				),
				variable: 'theme-border-bottom',
				type: 'border',
				responsive: true,
				fullValue: true,

				extractValue: ({
					footerRowBottomDivider,
					footerRowBottomBorderFullWidth,
				}) =>
					footerRowBottomBorderFullWidth !== 'yes'
						? footerRowBottomDivider
						: {
								desktop: { style: 'none' },
								tablet: { style: 'none' },
								mobile: { style: 'none' },
						  },
			},
		]
	),

	footerWidgetsTitleColor: {
		selector: assembleSelector(
			mutateSelector({
				selector: getRootSelectorFor({ itemId, panelType: 'footer' }),
				operation: 'suffix',
				to_add: '.widget-title',
			})
		),
		variable: 'theme-heading-color',
		type: 'color',
		responsive: true,
	},

	...typographyOption({
		id: 'footerWidgetsFont',

		selector: assembleSelector(
			mutateSelector({
				selector: getRootSelectorFor({ itemId, panelType: 'footer' }),
				operation: 'suffix',
				to_add: '.ct-widget > *:not(.widget-title)',
			})
		),
	}),

	rowFontColor: [
		{
			selector: assembleSelector(
				mutateSelector({
					selector: getRootSelectorFor({
						itemId,
						panelType: 'footer',
					}),
					operation: 'suffix',
					// to_add: '.ct-widget > *:not(.widget-title)',
					to_add: '.ct-widget',
				})
			),
			variable: 'theme-text-color',
			type: 'color:default',
			responsive: true,
		},

		{
			selector: assembleSelector(
				mutateSelector({
					selector: getRootSelectorFor({
						itemId,
						panelType: 'footer',
					}),
					operation: 'suffix',
					to_add: '.ct-widget',
				})
			),
			variable: 'theme-link-initial-color',
			type: 'color:link_initial',
			responsive: true,
		},

		{
			selector: assembleSelector(
				mutateSelector({
					selector: getRootSelectorFor({
						itemId,
						panelType: 'footer',
					}),
					operation: 'suffix',
					to_add: '.ct-widget',
				})
			),
			variable: 'theme-link-hover-color',
			type: 'color:link_hover',
			responsive: true,
		},
	],

	footerColumnsDivider: {
		selector: assembleSelector(
			mutateSelector({
				selector: getRootSelectorFor({ itemId, panelType: 'footer' }),
				operation: 'suffix',
				to_add: '> div',
			})
		),
		variable: 'theme-border',
		type: 'border',
	},

	...handleBackgroundOptionFor({
		id: 'footerRowBackground',
		selector: assembleSelector(
			getRootSelectorFor({ itemId, panelType: 'footer' })
		),
		responsive: true,
	}),

	...withKeys(
		[
			'items_per_row',
			'2_columns_layout',
			'3_columns_layout',
			'4_columns_layout',
			'5_columns_layout',
			'6_columns_layout',
		],
		{
			selector: assembleSelector(
				mutateSelector({
					selector: getRootSelectorFor({
						itemId,
						panelType: 'footer',
					}),
					operation: 'suffix',
					to_add: '> div',
				})
			),
			variable: 'grid-template-columns',
			responsive: true,
			fullValue: true,
			extractValue: (values) => {
				const row = document.querySelector(
					assembleSelector(
						getRootSelectorFor({ itemId, panelType: 'footer' })
					)
				)

				if (
					row &&
					parseInt(values.items_per_row, 10) !==
						row.firstElementChild.children.length
				) {
					;[...row.querySelectorAll('span[data-column]')].map((el) =>
						el.remove()
					)

					if (
						row.querySelectorAll('[data-column]').length >
						parseInt(values.items_per_row, 10)
					) {
						;[
							...Array(
								row.querySelectorAll('[data-column]').length -
									parseInt(values.items_per_row, 10)
							),
						].map(() =>
							row
								.querySelector('[data-column]')
								.parentNode.lastElementChild.remove()
						)
					}

					if (
						row.querySelectorAll('[data-column]').length <
						parseInt(values.items_per_row, 10)
					) {
						;[
							...Array(
								parseInt(values.items_per_row, 10) -
									row.querySelectorAll('[data-column]').length
							),
						].map(() =>
							row
								.querySelector('[class*="ct-container"]')
								.insertAdjacentHTML(
									'beforeend',
									'<span data-column></span>'
								)
						)
					}
				}

				return getColumnsLayout(values)
			},
		}
	),
})

export const handleRowOptions = ({
	selector,
	changeDescriptor: { optionId, optionValue, values },
}) => {
	const el = document.querySelector(selector)

	if (optionId === 'footerRowWidth') {
		el.firstElementChild.classList.remove(
			'ct-container',
			'ct-container-fluid'
		)

		el.firstElementChild.classList.add(
			optionValue !== 'fixed' ? 'ct-container-fluid' : 'ct-container'
		)
	}

	if (optionId === 'footerRowVisibility') {
		responsiveClassesFor(optionValue, el)
	}

	if (!el) {
		return
	}

	if (!el.firstElementChild) {
		return
	}

	el.firstElementChild.removeAttribute('data-columns-divider')

	const count = parseInt(values.items_per_row, 10)
	const stack = []
	const columns = getColumnsLayout(values)

	if (count >= 2 && count <= 6) {
		if (columns['tablet'] === 'initial') {
			stack.push('tablet')
		}

		if (columns['mobile'] === 'initial') {
			stack.push('mobile')
		}
	}

	let dataGrid = []

	if (count > 1) {
		if (stack.indexOf('tablet') === -1) {
			dataGrid.push('md')
		}

		if (stack.indexOf('mobile') === -1) {
			dataGrid.push('sm')
		}

		if (dataGrid.length > 0) {
			el.firstElementChild.dataset.columnsDivider = dataGrid.join(':')
		}
	}
}

ctEvents.on(
	'ct:footer:sync:collect-variable-descriptors',
	(variableDescriptors) => {
		variableDescriptors['middle-row'] = handleRowVariables
	}
)

ctEvents.on('ct:footer:sync:item:middle-row', (changeDescriptor) =>
	handleRowOptions({
		selector: '.ct-footer [data-row="middle"]',
		changeDescriptor,
	})
)
