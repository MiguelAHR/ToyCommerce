<?php
/**
 * CSS Injector
 * Helper object for including dynamic styles into head of the document,
 * with possibilities of extending it.
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package Blocksy
 */

class Blocksy_Css_Injector {
	/**
	 * Temporary CSS attributes.
	 *
	 * @var array $attr Attributes.
	 */
	private $attr = [];
	private $additional_symbols = [];
	private $selector_prefix = null;
	private $fonts_manager = null;

	/**
	 * Keyword that allows skiping a certain CSS rule from getting in the output.
	 */
	public static function get_skip_rule_keyword($suffix = '') {
		return 'CT_CSS_SKIP_RULE' . $suffix;
	}

	public static function get_inline_keyword($suffix = '') {
		return 'CT_CSS_INLINE_CSS' . $suffix;
	}

	/**
	 * Injector constructor.
	 */
	public function __construct($args = []) {
		$args = wp_parse_args(
			$args,
			[
				'selector_prefix' => '',
				'fonts_manager' => null
			]
		);

		if (! empty($args['selector_prefix'])) {
			$this->selector_prefix = $args['selector_prefix'];
		}

		$this->additional_symbols = ['-', '%', 'px', 's'];

		if ($args['fonts_manager']) {
			$this->fonts_manager = $args['fonts_manager'];
		}
	}

	public function process_matching_typography($value) {
		if (! $this->fonts_manager) {
			return;
		}

		$this->fonts_manager->process_matching_typography($value);
	}

	/**
	 * Parse each temporary structure and transform it into actual CSS.
	 */
	public function build_css_structure() {
		$content = '';

		if (isset($this->attr[Blocksy_Css_Injector::get_inline_keyword()])) {
			$content .= implode('', $this->attr[Blocksy_Css_Injector::get_inline_keyword()]);
			unset($this->attr[Blocksy_Css_Injector::get_inline_keyword()]);
		}

		if (count($this->attr)) {
			$content .= "\n" . $this->convert_to_css();
		}

		$content = $this->css_minify($content);

		return $content;
	}

	public function get_wp_style_engine_rules($args = []) {
		$args = wp_parse_args(
			$args,
			[
				'device' => 'desktop'
			]
		);

		$media_queries = [
			'tablet' => '@media (max-width: 999.98px)',
			'mobile' => '@media (max-width: 689.98px)'
		];

		$rules = [];

		foreach ($this->attr as $selector => $lines) {
			$declarations = [];

			foreach ($lines as $line) {
				$line = trim($line);

				if (! $line) {
					continue;
				}

				$parts = explode(':', $line);

				if (count($parts) <= 1) {
					continue;
				}

				$declarations[trim($parts[0])] = trim($parts[1]);
			}

			$rule = [
				'selector' => $selector,
				'declarations' => $declarations
			];

			if (isset($media_queries[$args['device']])) {
				$rule['rules_group'] = $media_queries[$args['device']];
			}

			$rules[] = $rule;
		}

		return $rules;
	}

	/**
	 * Add new line in CSS structure.
	 *
	 * @param string|array $selector CSS class, id, tag.
	 * @param string|array $rules CSS syntax.
	 */
	public function put($selector, $rules) {
		$normalized = $this->normalize_inputs($selector, $rules);

		if (! $normalized) {
			return;
		}

		// Rules stored under the inline keyword are entire CSS chunks
		// (@font-face, @media), not single declarations.
		$is_inline = $selector === Blocksy_Css_Injector::get_inline_keyword();

		$selector = $normalized['selector'];
		$rules = $normalized['rules'];

		if (! isset($this->attr[$selector])) {
			$this->attr[$selector] = [];
		}

		foreach ($rules as $line) {
			$line = trim($line);

			if (
				! $line
				||
				in_array($line, $this->attr[$selector], true)
			) {
				continue;
			}

			if (strpos($line, self::get_skip_rule_keyword()) !== false) {
				continue;
			}

			if (! $is_inline && $this->declaration_breaks_out($line)) {
				continue;
			}

			$this->attr[$selector][] = $line;
		}
	}

	// A single CSS declaration (`property: value`) can never legitimately
	// contain `{`, `}` or `@`. Their presence means the value is trying to
	// close its declaration block and open a new selector or at-rule — a
	// stylesheet breakout from an attacker-controlled value. Inline-keyword
	// rules are exempt (handled by the caller) because they are whole CSS
	// chunks like @font-face / @media, not single declarations.
	private function declaration_breaks_out($declaration) {
		return preg_match('/[{}@]/', $declaration) === 1;
	}

	/**
	 * Reduce a single rule element to its one declaration: everything before
	 * the first `;`. A `;` that embeds a second declaration is dropped, and a
	 * value that starts with `;` (empty property position) is rejected whole —
	 * so the element is neutralized regardless of the caller, not trusting that
	 * the caller fixed the property.
	 *
	 * @param mixed $element Single rule element.
	 * @return string The single declaration, or empty string.
	 */
	private function take_first_declaration($element) {
		if (! is_string($element)) {
			return '';
		}

		$parts = explode(';', $element);

		return trim($parts[0]) !== '' ? $parts[0] : '';
	}

	private function normalize_inputs($selector, $preliminary_rules) {
		if (is_string($preliminary_rules) && trim($preliminary_rules) === '') {
			return false;
		}

		if (is_array($selector)) {
			$selector = implode(",\n", $selector);
		}

		$rules = [];

		if ($selector === Blocksy_Css_Injector::get_inline_keyword()) {
			$rules = is_array($preliminary_rules) ? $preliminary_rules : [
				$preliminary_rules
			];
		} else {
			$elements = is_array($preliminary_rules)
				? $preliminary_rules
				: [$preliminary_rules];

			// Every legitimate caller passes a single `property: value` per
			// element. A `;` that introduces a second declaration is always
			// injection, so each element is reduced to its first declaration.
			foreach ($elements as $element) {
				$rules[] = $this->take_first_declaration($element);
			}
		}

		$prefix = '';

		if (! empty($this->selector_prefix)) {
			$prefix = $this->selector_prefix . ' ';
		}

		return [
			'selector' => $prefix . $selector,
			'rules' => $rules
		];
	}

	/**
	 * Merge selectors that have the same CSS. This has the effect of increasing
	 * the weight of the selectors.
	 */
	private function merge_class_with_the_same_css() {
		return;

		$new_names = [];
		$used = [];

		foreach ($this->attr as $key => $values) {
			if (isset($used[$key])) {
				continue;
			}

			foreach ($this->attr as $sub_key => $sub_values) {
				if ($sub_key !== $key && $values === $sub_values) {
					$used[$sub_key] = 1;
					$new_names[$key][] = $sub_key;
					$used[$key] = 1;
				}
			}
		}

		// Merge classes.
		foreach ($new_names as $parent => $childs) {
			$class_name = $parent . ",\n" . join(",\n", $childs);
			$this->attr[$class_name] = $this->attr[$parent];

			// Remove CSS from main structure.
			if (isset($this->attr[$parent])) {
				unset($this->attr[$parent]);
			}

			// Remove all childs css.
			foreach ($childs as $child_class) {
				if (isset($this->attr[$child_class])) {
					unset($this->attr[$child_class]);
				}
			}
		}
	}

	/**
	 * Convert this->attr to a CSS string.
	 */
	private function convert_to_css() {
		$css = '';

		$this->merge_class_with_the_same_css();

		foreach ($this->attr as $key => $values) {
			$section = '';

			$section .= $key . " {\n";

			$content = '';

			foreach ($values as $line) {
				$line = trim($line);

				if (! $this->is_empty_style($line)) {
					if (strpos($key, '@media') === false) {
						$line = str_replace(';', '', $line);
					}

					$content .= "    {$line}";

					if (strpos($key, '@media') === false) {
						$content .= ";\n";
					}
				}
			}

			// CSS is not empty.
			if ($content) {
				$section .= $content;
			} else {
				continue;
			}

			$section .= "}\n\n";
			$css .= $section;
		}

		// Erase structure.
		$this->attr = [];

		return $css;
	}

	/**
	 * Check if a CSS rule is empty.
	 *
	 * @param string $line Single rule.
	 */
	private function is_empty_style($line) {
		$parts = explode(':', $line);

		if (count($parts) <= 1) {
			return false;
		}

		if (! isset($parts[1])) {
			return true;
		}

		$parts[1] = str_replace($this->additional_symbols, '', $parts[1]);

		return strlen(trim($parts[1])) === 0;
	}

	/**
	 * Very rudimentary CSS minifier.
	 *
	 * @param string $minify CSS to be minified.
	 */
	private function css_minify($minify) {
		if (defined('WP_DEBUG') && WP_DEBUG) {
			// return $minify;
		}

		// return $minify;

		/* remove comments */
		$minify = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $minify );

		/* remove tabs, spaces, newlines, etc. */
		$minify = str_replace( array( "\r\n", "\r", "\n", "\t", '  ', '    ', '    ' ), '', $minify );
		/* remove space after colons */
		$minify = str_replace( ': ', ':', $minify );
		$minify = str_replace( '}[', '} [', $minify );

		return $minify;
	}
}

