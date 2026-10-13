<?php

namespace Blocksy;

if (! defined('ABSPATH')) {
	exit;
}

class SvgHandling {
	public function __construct() {
		add_filter(
			'blocksy:display-html:allowed-tags',
			[$this, 'add_svg_allowed_tags'],
			10, 2
		);

		add_filter(
			'wp_kses_uri_attributes',
			function ($attributes) {
				$attributes[] = 'xlink:href';
				return $attributes;
			}
		);

		add_filter(
			'blocksy:svg:sanitize-inline',
			function ($svg, $args) {
				if ($svg !== null) {
					return $svg;
				}

				$args = wp_parse_args($args, [
					'svg' => '',
				]);

				return self::sanitize_inline_svg([
					'svg' => $args['svg'],
				]);
			},
			10, 2
		);

		$sanitize_svg_upload = function ($file) {
			$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

			if ('svg' !== $extension) {
				return $file;
			}

			$error = self::sanitize_svg_file($file['tmp_name']);

			if ($error) {
				$file['error'] = $error;
			}

			return $file;
		};

		add_filter('wp_handle_upload_prefilter', $sanitize_svg_upload);
		add_filter('wp_handle_sideload_prefilter', $sanitize_svg_upload);

		add_filter(
			'wp_handle_upload',
			function ($upload) {
				if (
					! is_array($upload)
					||
					! empty($upload['error'])
					||
					empty($upload['file'])
				) {
					return $upload;
				}

				$extension = strtolower(pathinfo($upload['file'], PATHINFO_EXTENSION));

				if ('svg' !== $extension) {
					return $upload;
				}

				// wp_upload_bits() with empty bits creates a placeholder that
				// importers stream the remote file into afterwards.
				if ('' === file_get_contents($upload['file'])) {
					return $upload;
				}

				$error = self::sanitize_svg_file($upload['file']);

				if ($error) {
					wp_delete_file($upload['file']);

					return [
						'error' => $error
					];
				}

				return $upload;
			}
		);

		add_filter(
			'wp_get_attachment_metadata',
			[$this, 'filter_get_attachment_metadata'],
			10, 2
		);

		add_filter(
			'wp_update_attachment_metadata',
			[$this, 'filter_get_attachment_metadata'],
			10, 2
		);

		add_filter(
			'wp_get_attachment_image_src',
			function ($image, $attachment_id, $size, $icon) {
				if (! isset($attachment_id)) {
					return $image;
				}

				$mime = get_post_mime_type($attachment_id);

				if (
					'image/svg+xml' === $mime
					&&
					$image[1] === 1
					&&
					$image[2] === 1
				) {
					$dimensions = $this->get_dimensions_for($attachment_id);

					$image[2] = $dimensions['height'];
					$image[1] = $dimensions['width'];
				}

				return $image;
			},
			10, 4
		);

		$should_add_filter = true;

		// Avoid adding the filter during image cropping to prevent issues with SVGs.
		// WP can't locate editor for SVGs so it throws an error.
		if (
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			isset($_REQUEST['action'])
			&&
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$_REQUEST['action'] === 'crop-image'
		) {
			$should_add_filter = false;
		}

		if ($should_add_filter) {
			add_filter('upload_mimes', [$this, 'upload_mimes']);
			add_filter('wp_check_filetype_and_ext', [$this, 'wp_check_filetype_and_ext'], 75, 4);
		}
	}

	public function wp_check_filetype_and_ext($data = null, $file = null, $filename = null, $mimes = null) {
		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

		// Only accept files with .svg as the final extension
		// Reject files like test.svg.php, test.svg.jpg, etc.
		if ($extension === 'svg') {
			$data['type'] = 'image/svg+xml';
			$data['ext'] = 'svg';
		}

		return $data;
	}

	public function upload_mimes($mimes) {
		$mimes['svg'] = 'image/svg+xml';
		return $mimes;
	}

	public function filter_get_attachment_metadata($data, $attachment_id) {
		$mime = get_post_mime_type($attachment_id);

		if (
			'image/svg+xml' === $mime
			&&
			is_array($data)
			&&
			(
				! isset($data['width'])
				||
				! isset($data['height'])
			)
		) {
			$dimensions = $this->get_dimensions_for($attachment_id);

			$data['width'] = $dimensions['width'];
			$data['height'] = $dimensions['height'];
		}

		return $data;
	}

	public function get_dimensions_for($attachment_id) {
		$height = 100;
		$width = 100;

		$maybe_file = get_attached_file($attachment_id);

		if ($maybe_file) {
			$dimensions = $this->svg_dimensions($maybe_file);

			if ($dimensions) {
				$height = round($dimensions['height']);
				$width = round($dimensions['width']);
			}
		}

		return [
			'height' => $height,
			'width' => $width
		];
	}

	public function svg_dimensions($svg) {
		if (
			! preg_match('/.svg$/', $svg)
			||
			! file_exists($svg)
		) {
			return null;
		}

		$svg = file_get_contents($svg);

		$attributes = new \stdClass();

		if ($svg && function_exists('simplexml_load_string')) {
			$svg = @simplexml_load_string($svg);

			if ($svg) {
				foreach ($svg->attributes() as $key => $value) {
					$attributes->{$key} = strval($value);
				}
			}
		}

		if (
			! isset($attributes->width)
			&&
			$svg
			&&
			function_exists('xml_parser_create')
		) {
			$xml = xml_parser_create('UTF-8');

			$svgData = new \stdClass();

			xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, false);
			xml_set_element_handler(
				$xml,
				function ($parser, $name, $attrs) use (&$svgData) {
					if ($name === 'SVG') {
						if (isset($attrs['WIDTH'])) {
							$attrs['width'] = $attrs['WIDTH'];
						}

						if (isset($attrs['HEIGHT'])) {
							$attrs['height'] = $attrs['HEIGHT'];
						}

						if (isset($attrs['VIEWBOX'])) {
							$attrs['viewBox'] = $attrs['VIEWBOX'];
						}

						foreach ($attrs as $key => $value) {
							$svgData->{$key} = $value;
						}
					}
				},
				function ($parser, $tag) {
				}
			);

			if (xml_parse($xml, $svg, true)) {
				$attributes = $svgData;
			}

			xml_parser_free($xml);
		}

		$width = 0;
		$height = 0;

		if (empty($attributes)) {
			return false;
		}

		if (
			isset($attributes->width, $attributes->height)
			&&
			is_numeric($attributes->width)
			&&
			is_numeric($attributes->height)
		) {
			$width = floatval($attributes->width);
			$height = floatval($attributes->height);
		} elseif (isset($attributes->viewBox)) {
			$sizes = explode(' ', $attributes->viewBox);

			if (isset($sizes[2], $sizes[3])) {
				$width = floatval($sizes[2]);
				$height = floatval($sizes[3]);
			}
		} else {
			return false;
		}

		return [
			'width' => $width,
			'height' => $height,
			'orientation' => ($width > $height) ? 'landscape' : 'portrait'
		];
	}

	public function add_svg_allowed_tags($tags, $context) {
		static $svg_tags = [];

		if (! isset($svg_tags[$context])) {
			$svg_tags[$context] = $this->get_svg_allowed_tags($context);
		}

		foreach ($svg_tags[$context] as $tag => $attributes) {
			if (isset($tags[$tag])) {
				$tags[$tag] = array_merge($tags[$tag], $attributes);
				continue;
			}

			$tags[$tag] = $attributes;
		}

		return $tags;
	}

	private function get_svg_allowed_tags($context) {
		$base_path = BLOCKSY_PATH . 'vendor/svg-sanitizer/src';

		require_once($base_path . '/data/TagInterface.php');
		require_once($base_path . '/data/AllowedTags.php');
		require_once($base_path . '/data/AttributeInterface.php');
		require_once($base_path . '/data/AllowedAttributes.php');

		// SMIL can write javascript: into href, which wp_kses() does not
		// protocol-filter there. A <style> inside inline SVG applies to the
		// whole document.
		$excluded_tags = [
			'#text',
			'animate',
			'set',
			'animatecolor',
			'animatemotion',
			'animatetransform',
		];

		if ($context !== 'logo') {
			$excluded_tags[] = 'style';
		}

		$attributes = [];

		foreach (\blocksy\enshrined\svgSanitize\data\AllowedAttributes::getAttributes() as $attribute) {
			$attributes[strtolower($attribute)] = true;
		}

		$tags = [];

		foreach (\blocksy\enshrined\svgSanitize\data\AllowedTags::getTags() as $tag) {
			$tag = strtolower($tag);

			if (in_array($tag, $excluded_tags)) {
				continue;
			}

			$tags[$tag] = $attributes;
		}

		return $tags;
	}

	private static function sanitize_svg_file($path) {
		/**
		 * Filters whether the uploaded SVG files should be sanitized.
		 *
		 * Returning false skips the sanitization and lets the file through untouched.
		 *
		 * @since 2.1.3
		 *
		 * @param bool $should_sanitize Whether to sanitize the SVG. Default true.
		 */
		if (! apply_filters('blocksy:svg:should_sanitize', true)) {
			return '';
		}

		$svg_content = file_get_contents($path);

		$trimmed_content = trim($svg_content);

		if (
			strpos($trimmed_content, '<?xml') !== 0
			&&
			strpos($trimmed_content, '<svg') !== 0
		) {
			return __('This file does not appear to be a valid SVG file.', 'blocksy-companion');
		}

		if (
			stripos($svg_content, '<?php') !== false
			||
			stripos($svg_content, '<?=') !== false
		) {
			return __('SVG files cannot contain PHP code.', 'blocksy-companion');
		}

		$sanitized_content = self::cleanup_svg($svg_content);

		if (! is_string($sanitized_content) || $sanitized_content === '') {
			return __('This SVG file could not be sanitized.', 'blocksy-companion');
		}

		if (file_put_contents($path, $sanitized_content) === false) {
			return __('This SVG file could not be sanitized.', 'blocksy-companion');
		}

		return '';
	}

	public static function cleanup_svg($content) {
		$sanitizer = self::get_sanitizer();

		try {
			return $sanitizer->sanitize($content);
		} catch (\Throwable $e) {
			return false;
		}
	}

	public static function sanitize_inline_svg($args = []) {
		$args = wp_parse_args($args, [
			'svg' => null,
			'file' => '',
		]);

		$svg = $args['svg'];

		if ($svg === null) {
			$file = $args['file'];

			if (! is_string($file) || strpos($file, '://') !== false) {
				return '';
			}

			if (! is_file($file)) {
				return '';
			}

			$filetype = wp_check_filetype($file, ['svg' => 'image/svg+xml']);

			if ($filetype['ext'] !== 'svg') {
				return '';
			}

			if (filesize($file) > 5 * MB_IN_BYTES) {
				return '';
			}

			$svg = file_get_contents($file);
		}

		if (! is_string($svg) || $svg === '' || strlen($svg) > 5 * MB_IN_BYTES) {
			return '';
		}

		static $cache = [];

		$key = md5($svg);

		if (array_key_exists($key, $cache)) {
			return $cache[$key];
		}

		$sanitizer = self::get_sanitizer();
		$sanitizer->removeRemoteReferences(true);

		try {
			$sanitized = $sanitizer->sanitize($svg);
		} catch (\Throwable $e) {
			$sanitized = false;
		}

		if (! is_string($sanitized)) {
			$sanitized = '';
		}

		$cache[$key] = $sanitized;

		return $sanitized;
	}

	private static function get_sanitizer() {
		$base_path = BLOCKSY_PATH . 'vendor/svg-sanitizer/src';

		require_once($base_path . '/data/AttributeInterface.php');
		require_once($base_path . '/data/TagInterface.php');
		require_once($base_path . '/data/AllowedAttributes.php');
		require_once($base_path . '/data/AllowedTags.php');
		require_once($base_path . '/data/XPath.php');
		require_once($base_path . '/ElementReference/Resolver.php');
		require_once($base_path . '/ElementReference/Subject.php');
		require_once($base_path . '/ElementReference/Usage.php');
		require_once($base_path . '/Exceptions/NestingException.php');
		require_once($base_path . '/Helper.php');
		require_once($base_path . '/Sanitizer.php');

		$sanitizer = new \blocksy\enshrined\svgSanitize\Sanitizer();

		// Remove <?xml tag from the SVG content to avoid html5
		// validation issues when embedding SVGs inline.
		$sanitizer->removeXMLTag(true);

		return $sanitizer;
	}
}

