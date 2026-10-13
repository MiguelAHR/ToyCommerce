<?php

$page_title_options = blocksy_get_options('general/page-title', [
    'prefix' => 'categories',
	'is_archive' => true,
	'location' => __('Categories', 'blocksy'),
]);

$posts_listing_options = blocksy_get_options('general/posts-listing', [
	'prefix' => 'categories',
	'title' => __('Categories', 'blocksy')
]);

$inner_options = [
	blocksy_manager()->get_prefix_title_actions([
		'prefix' => 'categories',
		'areas' => [
			[
				'title' => __('Page Title', 'blocksy'),
				'options' => $page_title_options,
				'sources' => array_merge(
					blocksy_manager()
						->screen
						->get_archive_prefixes_with_human_labels([
							'has_categories' => true,
							'has_author' => true,
							'has_search' => true,
							'has_woocommerce' => true
						]),

					blocksy_manager()
						->screen
						->get_single_prefixes_with_human_labels([
							'has_woocommerce' => true
						])
				)
			],

			[
				'id' => 'posts_listing',
				'title' => __('Posts Listing', 'blocksy'),
				'options' => $posts_listing_options,
				'sources' => blocksy_manager()
					->screen
					->get_archive_prefixes_with_human_labels([
						'has_categories' => true,
						'has_author' => true,
						'has_search' => true
					]),
			],

			[
				'title' => __('Pagination', 'blocksy'),
				'options' => [],
				'sources' => blocksy_manager()
					->screen
					->get_archive_prefixes_with_human_labels([
						'has_categories' => true,
						'has_author' => true,
						'has_search' => true
					]),
			]
		]
	]),

	$page_title_options,
	$posts_listing_options,

	[
		blocksy_rand_md5() => [
			'type'  => 'ct-title',
			'label' => __( 'Categories Elements', 'blocksy' ),
		],
	],

	blocksy_get_options('general/sidebar-particular', [
		'prefix' => 'categories',
	])
];

/**
 * Filters the inner options for the category archives customizer section.
 *
 * @since 2.1.47
 *
 * @param array $inner_options List of option definitions for the section.
 */
$inner_options = apply_filters(
	'blocksy:options:categories',
	$inner_options
);

$options = [
	'single_categories_section_options' => [
		'type' => 'ct-options',
		'setting' => [ 'transport' => 'postMessage' ],
		'inner-options' => $inner_options
	],
];
