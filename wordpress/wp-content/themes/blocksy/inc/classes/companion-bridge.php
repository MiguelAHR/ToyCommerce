<?php

namespace Blocksy;

class CompanionBridge {
	private $bridges = [
		'custom_icons' => [
			'method' => 'get_icon',
			'bridgeFallback' => ''
		],
		'content_blocks' => [
			'method' => 'get_content_block_that_matches',
			'bridgeFallback' => null
		],
		'content_blocks_eligibility' => [
			'method' => 'is_hook_eligible_for_display',
			'bridgeFallback' => false
		],
		'extensions' => [
			'method' => 'activate_extension',
			'bridgeFallback' => null
		]
	];

	public function has($feature) {
		/**
		 * Filters whether the companion provides a given feature.
		 *
		 * The companion (or any plugin) answers `true` for features it handles;
		 * the theme uses the result to decide whether to defer to the companion.
		 *
		 * @since 2.1.47
		 *
		 * @param bool   $has     Whether the feature is available. Default false.
		 * @param string $feature The feature key being queried.
		 */
		return apply_filters(
			'blocksy:companion:has',
			false,
			$feature
		);
	}

	public function __call($method, $args) {
		$bridge = null;

		foreach ($this->bridges as $feature => $config) {
			if ($config['method'] === $method) {
				$bridge = $config;
				break;
			}
		}

		if (! $bridge) {
			return '';
		}

		/**
		 * Filters the result of a bridged companion method call.
		 *
		 * The dynamic portion of the hook name, `$method`, refers to the bridged
		 * method invoked on `blocksy_manager()->companion` (e.g. `get_icon`,
		 * `get_content_block_that_matches`). The companion registers a handler per
		 * method; the first argument is the fallback returned when no companion is
		 * present, followed by the call arguments.
		 *
		 * @since 2.1.47
		 *
		 * @param mixed $fallback The value returned when the method is unhandled.
		 * @param mixed ...$args  The arguments passed to the bridged method.
		 */
		return apply_filters_ref_array(
			'blocksy:companion:' . $method,
			array_merge([$bridge['bridgeFallback']], $args)
		);
	}
}
