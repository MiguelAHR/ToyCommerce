<?php

namespace Blocksy;

class DemoInstallRegisterDemo {
	public function register() {
		if (! current_user_can('edit_theme_options')) {
			wp_send_json_error();
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$raw_demo_name = isset($_REQUEST['demo_name']) ? sanitize_text_field(wp_unslash($_REQUEST['demo_name'])) : '';

		if ($raw_demo_name === '') {
			wp_send_json_error([
				'message' => __("No demo name provided.", 'blocksy-companion')
			]);
		}

		$demo_name = explode(':', $raw_demo_name);

		if (! isset($demo_name[1])) {
			$demo_name[1] = '';
		}

		$demo = $demo_name[0];
		$builder = $demo_name[1];

		$this->set_current_demo($demo . ':' . $builder);

		wp_send_json_success();
	}

	public function deregister() {
		if (! current_user_can('edit_theme_options')) {
			wp_send_json_error();
		}

		update_option('blocksy_ext_demos_current_demo', null);

		/**
		 * Fires when the dynamic CSS caches should be invalidated.
		 *
		 * Listeners drop their generated CSS files/transients so the
		 * next request regenerates them.
		 *
		 * @since 1.6.2
		 * @since 1.8.0 Renamed from `blocksy:dynamic-css:regenere_css_files`.
		 */
		do_action('blocksy:dynamic-css:refresh-caches');
		/**
		 * Fires when all the caches managed by Blocksy need to be purged.
		 *
		 * @since 2.0.27
		 */
		do_action('blocksy:cache-manager:purge-all');

		wp_send_json_success();
	}

	public function set_current_demo($demo) {
		update_option('blocksy_ext_demos_current_demo', [
			'demo' => $demo
		]);
	}
}
