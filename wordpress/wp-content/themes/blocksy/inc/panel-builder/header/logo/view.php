<?php

if (! isset($device)) {
	$device = 'desktop';
}

$default_logo = blocksy_expand_responsive_value(
	blocksy_default_akg('custom_logo', $atts, blocksy_get_theme_mod('custom_logo', ''))
);

$transparent_logo = blocksy_expand_responsive_value(
	blocksy_default_akg('transparent_logo', $atts, '')
);

$logo_type_classes = apply_filters("blocksy:{$panel_type}:logo:img:class", [
	'default_logo' => '',
	'transparent_state_logo' => '',
	'sticky_state_logo' => '',
	'dark_mode_logo' => '',
	'offcanvas_logo' => ''
]);

$sticky_logo = blocksy_expand_responsive_value(
	blocksy_default_akg('sticky_logo', $atts, '')
);

$custom_logo_id = '';
$additional_logos = [];

if (
	isset($has_transparent_header)
	&&
	$has_transparent_header
	&&
	is_array($has_transparent_header)
	&&
	in_array($device, $has_transparent_header)
	&&
	! empty($transparent_logo[$device])
) {
	$custom_logo_id = $transparent_logo[$device];
} else {
	if (! empty($default_logo[$device])) {
		$custom_logo_id = $default_logo[$device];
	}
}

$will_use_transparent_logo = false;

if (
	isset($has_transparent_header)
	&&
	$has_transparent_header
	&&
	is_array($has_transparent_header)
	&&
	in_array($device, $has_transparent_header)
	&&
	! empty($transparent_logo[$device])
) {
	$custom_logo_id = $transparent_logo[$device];
	$will_use_transparent_logo = true;
} else {
	if (! empty($default_logo[$device])) {
		$custom_logo_id = $default_logo[$device];
	}
}

if (
	isset($has_sticky_header)
	&&
	is_array($has_sticky_header)
	&&
	is_array($has_sticky_header['devices'])
	&&
	in_array($device, $has_sticky_header['devices'])
	&&
	! empty($sticky_logo[$device])
    &&
	(
		$has_sticky_header['behaviour'] === 'entire_header'
		||
		strpos(
			$has_sticky_header['behaviour'],
			str_replace('-row', '', $row_id)
		) !== false
	)
) {
	$additional_logos[] = [
		'class' => trim(
			implode(' ', [
				'sticky-logo',
				$logo_type_classes['sticky_state_logo']
			])
		),
		'id' => $sticky_logo[$device]
	];
}

/**
 * Filters the additional logo images rendered next to the main logo.
 *
 * @since 2.0.1
 *
 * @param array  $additional_logos  List of additional logos, each one an array with a `class`
 *                                  string and an `id` attachment ID.
 * @param array  $atts              Options of the logo panel builder item.
 * @param string $device            Device the logo is rendered for. Either 'desktop', 'tablet' or 'mobile'.
 * @param string $panel_type        Panel builder the logo belongs to. Either 'header' or 'footer'.
 * @param array  $logo_type_classes Map of logo state to the class name applied to its image.
 */
$additional_logos = apply_filters(
	'blocksy:panel-builder:logo:additional-logos',
	$additional_logos,
	$atts,
	$device,
	$panel_type,
	$logo_type_classes
);

/**
 * Filters the attachment ID of the logo image.
 *
 * The dynamic portion of the hook name, `$panel_type`, refers to the panel
 * builder the logo is rendered in (e.g. `header`, `footer`).
 *
 * @since 1.8.97
 *
 * @param string|int $custom_logo_id Attachment ID of the resolved logo image. Default empty string.
 */
$custom_logo_id = apply_filters(
	'blocksy:' . $panel_type . ':logo:image-id',
	$custom_logo_id
);

if ($custom_logo_id) {
	$custom_logo_attr = [
		'class' => trim(
			implode(' ', [
				'default-logo',
				($will_use_transparent_logo ? $logo_type_classes['transparent_state_logo'] : $logo_type_classes['default_logo'])
			])
		),
		'fetchpriority' => false,
		// 'itemprop' => 'logo'
	];

	if ($panel_type === 'header') {
		$custom_logo_attr['loading'] = false;
	}

	/**
	 * If the logo alt attribute is empty, get the site title and explicitly
	 * pass it to the attributes used by wp_get_attachment_image().
	 */
	$custom_logo_attr['alt'] = get_post_meta(
		$custom_logo_id,
		'_wp_attachment_image_alt',
		true
	);

	if (empty($custom_logo_attr['alt'])) {
		$custom_logo_attr['alt'] = get_bloginfo('name', 'display');
	}

	$has_custom_mobile_logo = $panel_type === 'footer' && isset($default_logo['mobile']) && isset($default_logo['desktop']) &&  $default_logo['mobile'] !== $default_logo['desktop'];
	$footer_mobile_logo_html = '';

	blocksy_lazy_zone_start();

	$image_logo_html = wp_get_attachment_image(
		$custom_logo_id,
		'full',
		false,
		array_merge(
			$custom_logo_attr,
			$has_custom_mobile_logo ? [
				'class' => trim(
					implode(' ', [
						$custom_logo_attr['class'],
						'ct-hidden-sm',
						'ct-hidden-md'
					])
				)
			] : []
		)
	);

	$inline_svg_logos = blocksy_akg('inline_svg_logos', $atts, 'no');

	if ($inline_svg_logos === 'yes') {
		$svg = blocksy_get_sanitized_inline_svg(
			get_attached_file($custom_logo_id)
		);

		if ($svg !== null) {
			$parser = new Blocksy_Attributes_Parser();

			$svg_logo_attr = $custom_logo_attr;

			unset($svg_logo_attr['loading'], $svg_logo_attr['fetchpriority']);
			$svg_logo_attr['aria-label'] = $svg_logo_attr['alt'];
			$svg_logo_attr['role'] = 'img';
			unset($svg_logo_attr['alt']);

			foreach ($svg_logo_attr as $svg_attr => $svg_attr_value) {
				$svg = $parser->add_attribute_to_images_with_tag(
					$svg,
					$svg_attr,
					$svg_attr_value,
					'svg',
					false
				);
			}

			/**
			 * Filters the inline SVG markup of a logo image.
			 *
			 * The dynamic portion of the hook name, `$panel_type`, refers to the panel
			 * builder the logo is rendered in (e.g. `header`, `footer`).
			 *
			 * @since 2.1.26
			 *
			 * @param string $svg           Sanitized inline SVG markup of the logo, with the image attributes applied.
			 * @param int    $attachment_id Attachment ID the SVG file was read from.
			 */
			$svg = apply_filters(
				'blocksy:' . $panel_type . ':logo:svg-content',
				$svg,
				$custom_logo_id
			);

			$image_logo_html = $svg;
		}
	}

	foreach ($additional_logos as $additional_logo) {
		$custom_logo_attr['class'] = $additional_logo['class'];

		$additional_logo_html = wp_get_attachment_image(
			$additional_logo['id'],
			'full',
			false,
			$custom_logo_attr
		);

		if ($inline_svg_logos === 'yes') {
			$svg = blocksy_get_sanitized_inline_svg(
				get_attached_file($additional_logo['id'])
			);

			if ($svg !== null) {
				$parser = new Blocksy_Attributes_Parser();

				$svg_logo_attr = $custom_logo_attr;

				unset($svg_logo_attr['loading'], $svg_logo_attr['fetchpriority']);
				$svg_logo_attr['aria-label'] = $svg_logo_attr['alt'];
				$svg_logo_attr['role'] = 'img';
				unset($svg_logo_attr['alt']);

				foreach ($svg_logo_attr as $svg_attr => $svg_attr_value) {
					$svg = $parser->add_attribute_to_images_with_tag(
						$svg,
						$svg_attr,
						$svg_attr_value,
						'svg',
						false
					);
				}

				/**
				 * Filters the inline SVG markup of a logo image.
				 *
				 * The dynamic portion of the hook name, `$panel_type`, refers to the panel
				 * builder the logo is rendered in (e.g. `header`, `footer`).
				 *
				 * @since 2.1.26
				 *
				 * @param string $svg           Sanitized inline SVG markup of the logo, with the image attributes applied.
				 * @param int    $attachment_id Attachment ID the SVG file was read from.
				 */
				$svg = apply_filters(
					'blocksy:' . $panel_type . ':logo:svg-content',
					$svg,
					$additional_logo['id']
				);

				$additional_logo_html = $svg;
			}
		}

		$image_logo_html = $additional_logo_html . $image_logo_html;
	}

	$aria_label = blocksy_akg('header_logo_aria_label', $atts, '');

	if (! empty($aria_label)) {
		$aria_label = 'aria-label="' . esc_attr($aria_label) . '"';
	}

	if ($has_custom_mobile_logo) {
		$footer_mobile_logo_html = wp_get_attachment_image(
			$default_logo['mobile'],
			'full',
			false,
			array_merge(
				$custom_logo_attr,
				[
					'class' => trim(
						implode(' ', [
							$custom_logo_attr['class'],
							'ct-hidden-lg'
						])
					)
				]
			)
		);
	}

	blocksy_lazy_zone_end();

	/**
	 * If the alt attribute is not empty, there's no need to explicitly pass
	 * it because wp_get_attachment_image() already adds the alt attribute.
	 */
	$logo_html = blocksy_safe_sprintf(
		'<a href="%1$s" class="site-logo-container" rel="home" itemprop="url" %2$s>%3$s</a>',
		esc_url(
			/**
			 * Filters the URL the logo links to.
			 *
			 * The dynamic portion of the hook name, `$panel_type`, refers to the panel
			 * builder the logo is rendered in (e.g. `header`, `footer`).
			 *
			 * @since 1.7.48
			 *
			 * @param string $url URL the logo links to. Default the home page URL.
			 */
			apply_filters('blocksy:' . $panel_type . ':logo:url', home_url('/'))
		),
		$aria_label,
		$image_logo_html . $footer_mobile_logo_html,
	);

	if (blocksy_akg('has_logo_image_link', $atts, 'yes') !== 'yes') {
		$logo_html = blocksy_safe_sprintf(
			'<span class="site-logo-container" %1$s>%2$s</span>',
			$aria_label,
			$image_logo_html . $footer_mobile_logo_html,
		);
	}
}

/**
 * Filters the HTML tag used for the logo wrapper element.
 *
 * The dynamic portion of the hook name, `$panel_type`, refers to the panel
 * builder the logo is rendered in (e.g. `header`, `footer`).
 *
 * @since 1.8.0
 *
 * @param string $wrapper_tag Tag name of the logo wrapper element. Default 'div'.
 */
$wrapper_tag = apply_filters('blocksy:' . $panel_type . ':logo:wrapper-tag', 'div');

$logo_position = '';

$wrapper_class = 'site-branding';

$wrapper_class = trim($wrapper_class . ' ' . blocksy_default_akg(
	'header_logo_class',
	$atts,
	''
));

$wrapper_class = trim($wrapper_class . ' ' . blocksy_visibility_classes(
	blocksy_akg('visibility', $atts, [
		'desktop' => true,
		'tablet' => true,
		'mobile' => true,
	])
));

$is_desktop = $device === 'desktop';

$blogname_html = '';
$tagline_html = '';

$has_site_title = blocksy_akg('has_site_title', $atts, 'yes') === 'yes';
$has_tagline = blocksy_akg('has_tagline', $atts, 'no') === 'yes';

if (
	$custom_logo_id
	&&
	(
		$has_site_title
		||
		$has_tagline
	)
) {
	$logo_position_v = blocksy_expand_responsive_value(
		blocksy_default_akg('logo_position', $atts, 'top')
	);

	$logo_position = 'data-logo="' . esc_attr($logo_position_v[$device]) . '"';
}

if ($has_site_title) {
	$blog_name = blocksy_sanitize_html_for_display([
		'html' => blocksy_translate_dynamic(
			blocksy_default_akg(
				'blogname',
				$atts,
				get_bloginfo('name')
			),
			$panel_type . ':' . $section_id . ':logo:blogname'
		)
	]);

	/**
	 * Filters the HTML tag used for the site title.
	 *
	 * The dynamic portion of the hook name, `$panel_type`, refers to the panel
	 * builder the logo is rendered in (e.g. `header`, `footer`).
	 *
	 * @since 1.8.3.4
	 *
	 * @param string $tag Tag name of the site title element. Default 'span'.
	 */
	$tag = apply_filters('blocksy:' . $panel_type . ':logo:tag', 'span');

	$site_title_class = 'site-title ' . blocksy_visibility_classes(
		blocksy_default_akg('blogname_visibility', $atts, [
			'desktop' => true,
			'tablet' => true,
			'mobile' => true,
		])
	);

	$has_site_title_link = blocksy_akg('has_site_title_link', $atts, 'yes') === 'yes';

	$blogname_html = blocksy_html_tag(
		$tag,
		array_merge(
			[
				'class' => $site_title_class
			],
			blocksy_schema_org_definitions(
				'name',
				[
					'condition' => $is_desktop,
					'array' => true
				]
			)
		),
		$has_site_title_link ? blocksy_safe_sprintf(
			'<a href="%1$s" rel="home" %2$s>%3$s</a>',
			esc_url(
				/**
				 * Filters the URL the logo links to.
				 *
				 * The dynamic portion of the hook name, `$panel_type`, refers to the panel
				 * builder the logo is rendered in (e.g. `header`, `footer`).
				 *
				 * @since 1.7.48
				 *
				 * @param string $url URL the logo links to. Default the home page URL.
				 */
				apply_filters('blocksy:' . $panel_type . ':logo:url', home_url('/'))
			),
			blocksy_schema_org_definitions(
				'url',
				[
					'condition' => $is_desktop
				]
			),
			$blog_name
		) : $blog_name
	);
}

if ($has_tagline) {
	$tagline_class = 'site-description ' . blocksy_visibility_classes(
		blocksy_default_akg('blogdescription_visibility', $atts, [
			'desktop' => true,
			'tablet' => true,
			'mobile' => true,
		])
	);

	$tagline_html = blocksy_html_tag(
		'p',
		array_merge(
			[
				'class' => $tagline_class
			],
			blocksy_schema_org_definitions(
				'description',
				[
					'condition' => $device === 'desktop',
					'array' => true
				]
			)
		),
		blocksy_sanitize_html_for_display([
			'html' => blocksy_translate_dynamic(
				blocksy_default_akg(
					'blogdescription',
					$atts,
					get_bloginfo('description')
				),
				$panel_type . ':' . $section_id . ':logo:blogdescription'
			)
		])
	);
}


?>

<<?php echo $wrapper_tag ?>
	class="<?php echo esc_attr($wrapper_class) ?>"
	<?php echo blocksy_attr_to_html($attr) ?>
	<?php echo $logo_position ?>
	<?php echo blocksy_schema_org_definitions('logo', ['condition' => $is_desktop]) ?>>

	<?php if ($custom_logo_id) { ?>
		<?php echo $logo_html; ?>
	<?php } ?>

	<?php if ($has_site_title || $has_tagline) { ?>
		<div class="site-title-container">
			<?php echo $blogname_html; ?>
			<?php echo $tagline_html; ?>
		</div>
	  <?php } ?>
</<?php echo $wrapper_tag ?>>

