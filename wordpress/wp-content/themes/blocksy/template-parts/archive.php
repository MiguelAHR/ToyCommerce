<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Blocksy
 */

/**
 * Filters the rendered output for the posts listing canvas.
 *
 * Returning a non-empty value short-circuits the entire default archive
 * template, including the hero section (e.g. a content block in canvas mode).
 *
 * @since 1.8.29
 *
 * @param string|null $maybe_custom_output Rendered output. Default null.
 */
$maybe_custom_output = apply_filters(
	'blocksy:posts-listing:canvas:custom-output',
	null
);

if ($maybe_custom_output) {
	echo $maybe_custom_output;
	return;
}

$container_class = 'ct-container';


/**
 * Note to code reviewers: This line doesn't need to be escaped.
 * Function blocksy_output_hero_section() used here escapes the value properly.
 */
echo blocksy_output_hero_section([
	'type' => 'type-2'
]);

$section_class = '';

if (! have_posts()) {
	$section_class = 'class="ct-no-results"';
}

?>

<div class="<?php echo $container_class ?>" <?php echo wp_kses_post(blocksy_sidebar_position_attr()); ?> <?php echo blocksy_get_v_spacing() ?>>
	<?php
		/**
		 * Fires at the top of the archive container, before the posts section.
		 *
		 * @since 2.1.54
		 */
		do_action('blocksy:archive:container:top');
	?>

	<section <?php echo $section_class ?>>
		<?php
			/**
			 * Fires at the top of the archive posts section, before the
			 * type-1 hero section and the archive cards.
			 *
			 * @since 2.1.54
			 */
			do_action('blocksy:archive:top');

			/**
			 * Note to code reviewers: This line doesn't need to be escaped.
			 * Function blocksy_output_hero_section() used here
			 * escapes the value properly.
			 */
			echo blocksy_output_hero_section([
				'type' => 'type-1'
			]);

			echo blocksy_render_archive_cards();

			/**
			 * Fires at the bottom of the archive posts section, after the
			 * archive cards.
			 *
			 * @since 2.1.54
			 */
			do_action('blocksy:archive:bottom');
		?>
	</section>

	<?php get_sidebar(); ?>

	<?php
		/**
		 * Fires at the bottom of the archive container, after the sidebar.
		 *
		 * @since 2.1.54
		 */
		do_action('blocksy:archive:container:bottom');
	?>
</div>
