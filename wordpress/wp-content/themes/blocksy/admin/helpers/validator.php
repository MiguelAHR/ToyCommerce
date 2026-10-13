<?php
/**
 * Sanitization helpers for admin inputs.
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package   Blocksy
 */

class Blocksy_Meta_Sanitizer {
	/**
	 * Sanitize post meta options by recursively checking all string values.
	 *
	 * Any string containing suspicious characters (< >) will be replaced
	 * with an empty string to prevent XSS attacks. Structural CSS breakout
	 * (extra declarations / rule blocks) is prevented at the CSS injector,
	 * which is the single boundary every dynamic style passes through.
	 *
	 * @param mixed $value The meta options to sanitize.
	 * @return mixed Sanitized meta options.
	 */
	public function sanitize_post_meta_options($value) {
		// The styles descriptor holds pre-rendered CSS and is owned by the
		// server. It is dropped here and regenerated on save/serve so it can
		// never carry attacker supplied CSS.
		if (is_array($value)) {
			unset($value['styles_descriptor']);
		}

		$unfiltered_keys = [];

		if (current_user_can('unfiltered_html')) {
			/**
			 * Filters the post meta option keys preserved verbatim (skipping
			 * sanitization) for users who have the `unfiltered_html`
			 * capability.
			 *
			 * Only consulted for trusted users (`unfiltered_html`); untrusted
			 * roles never reach this filter, so their values are always
			 * sanitized.
			 *
			 * @since 2.1.56
			 *
			 * @param string[] $unfiltered_keys Post meta option keys to leave unsanitized. Default empty array.
			 */
			$unfiltered_keys = apply_filters(
				'blocksy:post-meta:unfiltered-keys',
				[]
			);
		}

		if (is_array($value) && ! empty($unfiltered_keys)) {
			$preserved = [];

			foreach ($unfiltered_keys as $key) {
				if (array_key_exists($key, $value)) {
					$preserved[$key] = $value[$key];
				}
			}

			$value = $this->sanitize_value_recursive($value);

			foreach ($preserved as $key => $val) {
				$value[$key] = $val;
			}

			return $value;
		}

		return $this->sanitize_value_recursive($value);
	}

	/**
	 * Validate that a CSS value is a gradient (and nothing else). Every
	 * top-level comma segment must literally be a gradient function, so no
	 * URL fetching function can appear as a value; a url() nested inside a
	 * matched gradient is inert (invalid gradient, no fetch).
	 *
	 * @param string $value The candidate gradient value.
	 * @return bool True if the value is a safe gradient, false otherwise.
	 */
	public function is_safe_gradient($value) {
		if (! is_string($value)) {
			return false;
		}

		$value = trim($value);

		if ($value === '') {
			return true;
		}

		$segments = $this->split_top_level_commas($value);

		foreach ($segments as $segment) {
			$segment = trim($segment);

			if ($segment === '') {
				return false;
			}

			if (! preg_match(
				'/^(repeating-)?(linear|radial|conic)-gradient\s*\(.*\)$/is',
				$segment
			)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Split a CSS value on top-level commas, ignoring commas nested
	 * inside parentheses.
	 *
	 * @param string $value The CSS value to split.
	 * @return array List of segments.
	 */
	private function split_top_level_commas($value) {
		$segments = [];
		$current = '';
		$depth = 0;
		$length = strlen($value);

		for ($i = 0; $i < $length; $i++) {
			$char = $value[$i];

			if ($char === '(') {
				$depth++;
			}

			if ($char === ')' && $depth > 0) {
				$depth--;
			}

			if ($char === ',' && $depth === 0) {
				$segments[] = $current;
				$current = '';
				continue;
			}

			$current .= $char;
		}

		$segments[] = $current;

		return $segments;
	}

	/**
	 * Recursively sanitize all string values in an array.
	 *
	 * @param mixed  $value The value to sanitize.
	 * @param string $key   The key the value is stored under.
	 * @return mixed Sanitized value.
	 */
	private function sanitize_value_recursive($value, $key = '') {
		if (is_string($value)) {
			if ($this->is_value_suspicious($value)) {
				return '';
			}

			return $value;
		}

		if (is_array($value)) {
			foreach ($value as $child_key => $item) {
				$value[$child_key] = $this->sanitize_value_recursive($item);
			}
		}

		return $value;
	}

	/**
	 * Check if a string value contains suspicious patterns.
	 *
	 * @param string $value The value to check.
	 * @return bool True if suspicious, false otherwise.
	 */
	private function is_value_suspicious($value) {
		if (! is_string($value)) {
			return false;
		}

		$value = trim($value);

		// Null bytes can be used to bypass security checks
		if (strpos($value, "\0") !== false) {
			return true;
		}

		// Characters that could enable XSS or CSS injection
		$dangerous = ['<', '>'];

		foreach ($dangerous as $char) {
			if (strpos($value, $char) !== false) {
				return true;
			}
		}

		// Block serialized PHP object strings to prevent Object Injection
		if (is_serialized($value)) {
			return true;
		}

		return false;
	}
}

if (! function_exists('blocksy_sanitize_post_meta_options')) {
	function blocksy_sanitize_post_meta_options($value) {
		return (new Blocksy_Meta_Sanitizer())->sanitize_post_meta_options($value);
	}
}
