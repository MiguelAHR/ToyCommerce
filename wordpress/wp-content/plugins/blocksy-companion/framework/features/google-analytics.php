<?php

namespace Blocksy;

if (! defined('ABSPATH')) {
	exit;
}

class GoogleAnalytics {
	public function __construct() {
		add_filter(
			'blocksy_engagement_general_start_customizer_options',
			[$this, 'generate_google_analytics_opts']
		);

		add_filter('blocksy:cookies-consent:scripts-to-load', function ($data) {
			$ga_4_code = $this->get_ga_4_code();

			if (! empty($ga_4_code)) {
				$data[] = $ga_4_code;
			}

			return $data;
		});

		if (is_admin()) return;

		add_action(
			'init',
			function () {
				add_action('wp_print_scripts', function () {
					if (is_admin()) return;

					if (class_exists('BlocksyExtensionCookiesConsent')) {
						if (! \BlocksyExtensionCookiesConsent::has_consent()) {
							return;
						}
					}

					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $this->get_ga_4_code();
				});
			}
		);
	}

	public function sanitize_ga_4_id($id) {
		if (! is_string($id)) {
			return '';
		}

		$id = trim($id);

		if (! preg_match('/^[A-Z]{1,3}-[A-Z0-9-]+$/i', $id)) {
			return '';
		}

		return $id;
	}

	private function get_ga_4_code() {
		$analytics_v4_id = $this->sanitize_ga_4_id(
			blocksy_companion_theme_functions()->blocksy_get_theme_mod('analytics_v4_id', '')
		);

		if (empty($analytics_v4_id)) {
			return '';
		}

		$src = 'https://www.googletagmanager.com/gtag/js?id=' . rawurlencode(
			$analytics_v4_id
		);

		$config_id = wp_json_encode(
			$analytics_v4_id,
			JSON_HEX_TAG | JSON_UNESCAPED_SLASHES
		);

		return wp_get_script_tag([
			'async' => true,
			'src' => esc_url_raw($src),
		]) . wp_get_inline_script_tag(
			"window.dataLayer = window.dataLayer || [];\n" .
			"function gtag(){dataLayer.push(arguments);}\n" .
			"gtag('js', new Date());\n" .
			"gtag('config', " . $config_id . ");"
		);
	}

	public function generate_google_analytics_opts($options) {
		$options[] = [
			'analytics_v4_id' => [
				'label' => __( 'Google Analytics v4', 'blocksy-companion' ),
				'type' => 'text',
				'design' => 'block',
				'value' => '',
				'desc' => blocksy_companion_safe_sprintf(
					// translators: %1$s and %2$s are HTML tags for a link.
					__(
						'Link your Google Analytics 4 tracking ID. More info and instructions can be found %1$shere%2$s.',
						'blocksy-companion'
					),
					'<a href="https://support.google.com/analytics/answer/9744165?hl=en" target="_blank">',
					'</a>'
				),
				'disableRevertButton' => true,
				'setting' => [
					'transport' => 'postMessage',
					'sanitize_callback' => [$this, 'sanitize_ga_4_id'],
				],
			]
		];

		return $options;
	}
}
