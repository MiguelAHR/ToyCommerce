<?php
/*
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Blocksy
 */

if (have_posts()) {
	the_post();
}

/**
 * Filters the rendered output for the single post canvas.
 *
 * Returning a non-empty string short-circuits the entire default single
 * template, including the hero section (e.g. a content block in canvas mode).
 *
 * @since 2.1.47
 *
 * @param string $content Rendered output. Default empty string.
 */
$content = apply_filters(
	'blocksy:single:canvas:custom-output',
	''
);

if (! empty($content)) {
	echo $content;
	have_posts();
	wp_reset_query();
	return;
}

/**
 * Note to code reviewers: This line doesn't need to be escaped.
 * Function blocksy_output_hero_section() used here escapes the value properly.
 */
/**
 * Filters whether the default hero section renders on a single post.
 *
 * @since 1.7.60
 *
 * @param bool $has_default_hero Whether the default hero is shown.
 */
if (apply_filters('blocksy:single:has-default-hero', true)) {
	echo blocksy_output_hero_section([
		'type' => 'type-2'
	]);
}

$page_structure = blocksy_get_page_structure();

$container_class = 'ct-container-full';
$data_container_output = '';

if ($page_structure === 'none' || blocksy_post_uses_vc()) {
	$container_class = 'ct-container';

	if ($page_structure === 'narrow') {
		$container_class = 'ct-container-narrow';
	}
} else {
	$data_container_output = 'data-content="' . $page_structure . '"';
}


?>

	<div
		class="<?php echo trim($container_class) ?>"
		<?php echo wp_kses_post(blocksy_sidebar_position_attr()); ?>
		<?php echo $data_container_output; ?>
		<?php echo blocksy_get_v_spacing() ?>>

		<?php do_action('blocksy:single:container:top'); ?>

		<?php
			/**
			 * Note to code reviewers: This line doesn't need to be escaped.
			 * Function blocksy_single_content() used here escapes the value properly.
			 */
			echo blocksy_single_content();
		?>

		<?php get_sidebar(); ?>

		<?php do_action('blocksy:single:container:bottom'); ?>
	</div>

<?php

blocksy_display_page_elements('separated');

have_posts();
wp_reset_query();
