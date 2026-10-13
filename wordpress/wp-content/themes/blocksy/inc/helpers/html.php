<?php

function blocksy_safe_antispambot($string_with_email) {
	$mail_parts = wp_parse_url($string_with_email);

	// No reason in trying to obfuscate if there is no email passed in the
	// mailto: link.
	//
	// This is a valid mailto: link without email. Sometimes, the user wants
	// to only prefill the subject or body of the email via the link.
	//
	// Example: mailto:?subject=Hello%20world
	if (! isset($mail_parts['path'])) {
		return $string_with_email;
	}

	$mail_parts['path'] = antispambot($mail_parts['path']);

	$result = [];

	if (! empty($mail_parts['scheme'])) {
		$result[] = $mail_parts['scheme'] . ':';
	}

	$result[] = $mail_parts['path'];

	if (! empty($mail_parts['query'])) {
		$result[] = '?' . $mail_parts['query'];
	}

	return implode('', $result);
}

/**
 * Generate attributes string for html tag
 *
 * @param array $attr_array array('href' => '/', 'title' => 'Test').
 *
 * @return string 'href="/" title="Test"'
 */
if (! function_exists('blocksy_attr_to_html')) {
	function blocksy_attr_to_html(array $attr_array) {
		$html_attr = '';

		foreach ($attr_array as $attr_name => $attr_val) {
			if (false === $attr_val) {
				continue;
			}

			$html_attr .= $attr_name . '="' . esc_attr($attr_val) . '" ';
		}

		return trim($html_attr);
	}
}

function blocksy_attr_to_html_e(array $attr_array) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo blocksy_attr_to_html($attr_array);
}

/**
 * Generate html tag
 *
 * @param string      $tag Tag name.
 * @param array       $attr Tag attributes.
 * @param bool|string $end Append closing tag. Also accepts body content.
 *
 * @return string The tag's html
 */
if (! function_exists('blocksy_html_tag')) {
	function blocksy_html_tag($tag, $attr = [], $end = false) {
		if (! is_string($attr)) {
			$attr = blocksy_attr_to_html($attr);
		}

		if (strpos($tag, ' ') !== false) {
			$tag = explode(' ', $tag)[0];
		}

		$html = '<' . $tag;

		if (! empty($attr)) {
			$html .= ' ' . $attr;
		}

		if (true === $end) {
			// <script></script>
			$html .= '></' . $tag . '>';
		} elseif (false === $end) {
			// <br>
			$html .= '>';
		} else {
			// <div>content</div>
			$html .= '>' . $end . '</' . $tag . '>';
		}

		return $html;
	}
}

function blocksy_html_tag_e($tag, $attr = [], $end = false) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo blocksy_html_tag($tag, $attr, $end);
}

// Keep in parity with blocksy_companion_parse_attributes_string() in the
// companion (framework/helpers/helpers.php) and its matching test file.
function blocksy_parse_html_attributes($input) {
	$result = [];

	preg_match_all(
		'/([a-zA-Z0-9_-]+)=([\'"])(.*?)\2/',
		$input,
		$matches,
		PREG_SET_ORDER
	);

	foreach ($matches as $match) {
		$result[$match[1]] = $match[3];
	}

	$stripped = preg_replace('/([a-zA-Z0-9_-]+)=([\'"])(.*?)\2/', '', $input);

	$stripped = preg_replace('/([a-zA-Z0-9_-]+)=([^\s]+)/', '', $stripped);

	preg_match_all('/\b([a-zA-Z0-9_-]+)\b/', $stripped, $bool_matches);

	foreach ($bool_matches[1] as $key) {
		if (! array_key_exists($key, $result)) {
			$result[$key] = '';
		}
	}

	foreach ($result as $name => $value) {
		if (stripos($name, 'on') === 0) {
			unset($result[$name]);
		}
	}

	return $result;
}

function blocksy_sanitize_html_for_display($args = []) {
	$args = wp_parse_args($args, [
		'html' => '',
		'context' => 'block',

		// The value already went through blocksy_sanitize_user_html() on save.
		'trusted' => false,
	]);

	if (
		! is_string($args['html'])
		||
		$args['html'] === ''
		||
		$args['trusted']
	) {
		return $args['html'];
	}

	/**
	 * Filters the tags and attributes wp_kses() keeps in blocksy_sanitize_html_for_display().
	 *
	 * Starts from the core `post` context, the set WordPress allows in post
	 * content for users without unfiltered_html.
	 *
	 * @since 2.1.57
	 *
	 * @param array  $tags    Allowed tags in wp_kses() format. Keys must be lowercase.
	 * @param string $context Where the HTML renders. Default 'block'.
	 */
	$allowed_tags = apply_filters(
		'blocksy:display-html:allowed-tags',
		wp_kses_allowed_html('post'),
		$args['context']
	);

	return wp_kses($args['html'], $allowed_tags);
}

function blocksy_get_sanitized_inline_svg($file) {
	if (! $file || ! file_exists($file)) {
		return null;
	}

	$check = wp_check_filetype($file, ['svg' => 'image/svg+xml']);

	if ($check['ext'] !== 'svg') {
		return null;
	}

	$svg = file_get_contents($file);

	if (! is_string($svg) || $svg === '') {
		return null;
	}

	/**
	 * Filters the sanitized version of SVG markup that is about to be inlined.
	 *
	 * Nothing is inlined unless a callback returns the markup, the caller then
	 * renders a regular image. The callback is responsible for sanitizing it.
	 *
	 * @since 2.1.58
	 *
	 * @param string|null $sanitized Sanitized SVG markup. Default null.
	 * @param array       $args {
	 *     Arguments of the SVG being inlined.
	 *
	 *     @type string $svg Raw SVG markup.
	 * }
	 */
	$svg = apply_filters('blocksy:svg:sanitize-inline', null, [
		'svg' => $svg,
	]);

	if (! is_string($svg) || strpos(ltrim($svg), '<svg') !== 0) {
		return null;
	}

	return $svg;
}
