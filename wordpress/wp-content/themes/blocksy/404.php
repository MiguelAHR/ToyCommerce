<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package Blocksy
 */

get_header();

/**
 * Filters the rendered output displayed instead of the default 404 template.
 *
 * Returning a non-empty string short-circuits the `template-parts/404` markup
 * (e.g. a content block assigned to the 404 location).
 *
 * @since 2.1.47
 *
 * @param string $content Rendered output. Default empty string.
 */
$content = apply_filters(
	'blocksy:404:custom-output',
	''
);

if (
	(
		! function_exists('elementor_theme_do_location')
		||
		! elementor_theme_do_location('single')
	)
	&&
	empty($content)
) {
	ob_start();
	get_template_part('template-parts/404');
	$content = ob_get_clean();
}

echo $content;

get_footer();
