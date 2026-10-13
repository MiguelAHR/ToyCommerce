import ctEvents from 'ct-events'
import { select, useSelect } from '@wordpress/data'
import { updateVariableInStyleTags } from '@creative-themes/customizer-sync-helpers'
import { getValueFromInput } from 'blocksy-options'
import { gutenbergVariables } from './variables'

import { dropIframeBodyTransition, revertIframeBodyTransition } from '../editor'

export const handleMetaboxValueChange = (optionId, optionValue) => {
	const atts = {
		...getValueFromInput(
			ct_editor_localizations.post_options,
			wp.data
				.select('core/editor')
				.getEditedPostAttribute('blocksy_meta') || {}
		),
		[optionId]: optionValue
	}

	if (!gutenbergVariables[optionId]) {
		return
	}

	const variableDescriptor = Array.isArray(gutenbergVariables[optionId])
		? gutenbergVariables[optionId]
		: [gutenbergVariables[optionId]]

	dropIframeBodyTransition()

	updateVariableInStyleTags({
		variableDescriptor,

		value: optionValue,
		fullValue: atts,
		tabletMQ: '(max-width: 999.98px)',
		mobileMQ: '(max-width: 689.98px)',

		cacheId: 'editor-styles',
		initialStyleTagsDescriptor: [
			{
				readStyles: () => {
					const settings = window.wp.data
						.select('core/editor')
						.getEditorSettings()
					const maybeBlocksyStyle = settings.styles.find(
						(s) => s.source === 'blocksy'
					)

					if (!maybeBlocksyStyle || !maybeBlocksyStyle.css) {
						return ''
					}

					return maybeBlocksyStyle.css
				},

				persistStyles: (newCss) => {
					const settings = window.wp.data
						.select('core/editor')
						.getEditorSettings()

					wp.data.dispatch('core/editor').updateEditorSettings({
						...settings,
						styles: settings.styles.map((s) => {
							if (s.source !== 'blocksy') {
								return s
							}

							return {
								...s,
								css: newCss
							}
						})
					})
				}
			}
		]
	})

	revertIframeBodyTransition()
}
