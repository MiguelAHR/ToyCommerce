<?php

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Generate attributes string for html tag
 *
 * Companion-owned copy of the theme's blocksy_attr_to_html(), so companion code
 * never depends on the theme being present or on a specific theme version. Keep
 * it in sync with inc/helpers/html.php in the theme.
 *
 * @param array $attr_array array('href' => '/', 'title' => 'Test').
 *
 * @return string 'href="/" title="Test"'
 */
function blocksy_companion_attr_to_html(array $attr_array) {
	$html_attr = '';

	foreach ($attr_array as $attr_name => $attr_val) {
		if (false === $attr_val) {
			continue;
		}

		$html_attr .= $attr_name . '="' . esc_attr($attr_val) . '" ';
	}

	return trim($html_attr);
}

function blocksy_companion_attr_to_html_e(array $attr_array) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo blocksy_companion_attr_to_html($attr_array);
}

/**
 * Generate html tag
 *
 * Companion-owned copy of the theme's blocksy_html_tag(). Keep it in sync with
 * inc/helpers/html.php in the theme.
 *
 * @param string      $tag Tag name.
 * @param array       $attr Tag attributes.
 * @param bool|string $end Append closing tag. Also accepts body content.
 *
 * @return string The tag's html
 */
function blocksy_companion_html_tag($tag, $attr = [], $end = false) {
	if (! is_string($attr)) {
		$attr = blocksy_companion_attr_to_html($attr);
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

function blocksy_companion_html_tag_e($tag, $attr = [], $end = false) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo blocksy_companion_html_tag($tag, $attr, $end);
}
