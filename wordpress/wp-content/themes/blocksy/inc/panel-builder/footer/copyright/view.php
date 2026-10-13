<?php

$class = 'ct-footer-copyright';

$class = trim($class . ' ' . blocksy_visibility_classes(blocksy_default_akg(
	'footer_copyright_visibility',
	$atts,
	[
		'desktop' => true,
		'tablet' => true,
		'mobile' => true,
	]
)));

$theme = blocksy_get_wp_theme();

$copyright_text = blocksy_default_akg(
	'copyright_text',
	$atts,
	/**
	 * Filters the default footer copyright text used when no
	 * custom copyright value is set.
	 *
	 * @since 1.8.65
	 *
	 * @param string $default The default copyright text.
	 */
	apply_filters(
		'blocksy:footer:copyright:default-value',
		__(
			'Copyright &copy; {current_year} - WordPress Theme by {theme_author}',
			'blocksy'
		)
	)
);

$translated_copyright_text = blocksy_translate_dynamic(
	$copyright_text,
	'footer:' . $section_id . ':copyright:copyright_text'
);

$text = str_replace(
	'{current_year}',
	date("Y"),
	/**
	 * Filters the footer copyright text before the `{current_year}` and
	 * `{site_title}` tokens are replaced.
	 *
	 * @since 1.8.99
	 *
	 * @param string $copyright_text The resolved copyright text.
	 */
	apply_filters(
		'blocksy:footer:copyright:value',
		$translated_copyright_text
	)
);

$text = str_replace(
	'{site_title}',
	get_bloginfo('name'),
	$text
);

$text = do_shortcode(blocksy_sanitize_html_for_display([
	'html' => str_replace(
		'{theme_author}',
		blocksy_html_tag(
			'a',
			[
				'href' => $theme->get('AuthorURI')
			],
			$theme->get('Author')
		),
		$text
	),
	'context' => 'block',
	'trusted' => $translated_copyright_text === $copyright_text,
]));

?>

<div
	class="<?php echo esc_attr($class) ?>"
	<?php echo blocksy_attr_to_html($attr) ?>>

	<?php echo $text ?>
</div>
