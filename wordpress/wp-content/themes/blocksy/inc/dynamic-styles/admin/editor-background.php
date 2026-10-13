<?php

$post_type = get_current_screen()->post_type;

$post_id = null;

if (isset($_GET['post']) && $_GET['post']) {
	$post_id = $_GET['post'];
}

$prefix = blocksy_manager()->screen->get_admin_prefix($post_type);

$post_atts = blocksy_get_post_options($post_id);

$background_source = blocksy_default_akg(
	'background',
	$post_atts,
	blocksy_background_default_value([
		'backgroundColor' => [
			'default' => [
				'color' => Blocksy_Css_Injector::get_skip_rule_keyword()
			],
		],
	])
);

if ($post_type === 'ct_content_block') {
	$template_type = get_post_meta($post_id, 'template_type', true);
	$template_subtype = blocksy_akg('template_subtype', $post_atts, 'card');

	if ($template_type === 'popup') {
		$background_source = blocksy_default_akg(
			'popup_background',
			$post_atts,
			blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => '#ffffff'
					],
				],
			])
		);
	}

	if ($template_type !== 'popup') {
		$default_content_block_structure = 'yes';

		if ($template_type === 'hook') {
			$default_content_block_structure = 'no';
		}

		$has_content_block_structure = blocksy_akg(
			'has_content_block_structure',
			$post_atts,
			$default_content_block_structure
		);

		if (
			$has_content_block_structure !== 'yes'
			||
			$template_type === 'archive' && $template_subtype === 'card'
		) {
			$background_source = blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => Blocksy_Css_Injector::get_skip_rule_keyword()
					],
				],
			]);
		}
	}
}

$strategy = [
	'strategy' => 'customizer',
	'prefix' => $prefix
];

$maybe_matching_template = blocksy_manager()->companion->get_content_block_that_matches([
	'template_type' => 'single',
	'template_subtype' => 'canvas',
	'match_conditions_strategy' => $prefix
]);

if ($maybe_matching_template) {
	$strategy = [
		'strategy' => blocksy_get_post_options($maybe_matching_template)
	];
}

if (
	isset($background_source['background_type'])
	&&
	$background_source['background_type'] === 'color'
	&&
	isset($background_source['backgroundColor']['default']['color'])
	&&
	$background_source['backgroundColor']['default']['color'] === Blocksy_Css_Injector::get_skip_rule_keyword()
) {
	$background_source = blocksy_akg_or_customizer(
		'background',
		$strategy,
		blocksy_background_default_value([
			'backgroundColor' => [
				'default' => [
					'color' => Blocksy_Css_Injector::get_skip_rule_keyword()
				],
			],
		])
	);

	if (
		isset($background_source['background_type'])
		&&
		$background_source['background_type'] === 'color'
		&&
		isset($background_source['backgroundColor']['default']['color'])
		&&
		$background_source['backgroundColor']['default']['color'] === Blocksy_Css_Injector::get_skip_rule_keyword()
	) {
		$background_source = blocksy_get_theme_mod(
			'site_background',
			blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => 'var(--theme-palette-color-7)'
					],
				],
			])
		);
	}
}

blocksy_output_background_css([
	'selector' => '.block-editor-iframe__html',
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'value' => $background_source,
	'responsive' => true,
	'important' => true,
	'forced_background_image' => true
]);
