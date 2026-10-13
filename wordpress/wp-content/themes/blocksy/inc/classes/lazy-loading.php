<?php

namespace Blocksy;

class LazyLoading {
	private $zone_depth = 0;

	public function __construct() {
		add_filter('wp_lazy_loading_enabled', [$this, 'filter_enabled']);
	}

	public function filter_enabled($enabled) {
		return $enabled && blocksy_get_theme_mod('has_lazy_load', 'yes') === 'yes';
	}

	public function is_enabled() {
		return wp_lazy_loading_enabled('img', 'wp_get_attachment_image');
	}

	public function start_zone() {
		if ($this->zone_depth === 0) {
			add_filter(
				'pre_wp_get_loading_optimization_attributes',
				[$this, 'zone_loading_attributes'],
				100,
				4
			);
		}

		$this->zone_depth++;
	}

	public function end_zone() {
		if ($this->zone_depth === 0) {
			return;
		}

		$this->zone_depth--;

		if ($this->zone_depth === 0) {
			remove_filter(
				'pre_wp_get_loading_optimization_attributes',
				[$this, 'zone_loading_attributes'],
				100
			);
		}
	}

	public function zone_depth() {
		return $this->zone_depth;
	}

	public function restore_zone_depth($depth) {
		while ($this->zone_depth > $depth) {
			$this->end_zone();
		}
	}

	public function zone_loading_attributes($value, $tag_name, $attr, $context) {
		if (is_array($value)) {
			return $value;
		}

		$result = [];

		if ($tag_name === 'img') {
			$result = [
				'decoding' => 'async',
				'fetchpriority' => 'low',
			];
		}

		if (
			wp_lazy_loading_enabled($tag_name, $context)
			&&
			($attr['fetchpriority'] ?? null) !== 'high'
		) {
			$result['loading'] = 'lazy';
		}

		foreach (array_keys($result) as $key) {
			if (isset($attr[$key])) {
				unset($result[$key]);
			}
		}

		return $result;
	}
}
