<?php

namespace Blocksy;

class DemoInstallPluginsUninstaller {
	public function import() {
		$plugins_installer = new DemoInstallPluginsInstaller();

		if (! $plugins_installer->can('activate_plugins')) {
			wp_send_json_error([
				'message' => __("Sorry, you don't have permission to uninstall plugins.", 'blocksy-companion')
			]);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if (empty($_REQUEST['plugins'])) {
			wp_send_json_success();
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$plugins = explode(':', sanitize_text_field(wp_unslash($_REQUEST['plugins'])));

		$plugins = array_intersect(
			$plugins,
			$this->get_current_demo_plugins()
		);

		$plugins_manager = Plugin::instance()->demo->get_plugins_manager();

		foreach ($plugins as $single_plugin) {
			$full_name = $plugins_manager->is_plugin_installed($single_plugin);

			if (
				$full_name
				&&
				is_multisite()
				&&
				is_plugin_active_for_network($full_name)
			) {
				continue;
			}

			$plugins_manager->plugin_deactivation($single_plugin);
		}

		wp_send_json_success();
	}

	public function get_current_demo_plugins() {
		$current_demo = Plugin::instance()->demo->get_current_demo();

		if (empty($current_demo['demo'])) {
			return [];
		}

		$demo_name = explode(':', $current_demo['demo'])[0];

		$demos = Plugin::instance()->demo->fetch_all_demos();

		if (! is_array($demos)) {
			return [];
		}

		$plugins = [];

		foreach ($demos as $demo) {
			if (
				isset($demo['name'])
				&&
				$demo['name'] === $demo_name
				&&
				isset($demo['plugins'])
				&&
				is_array($demo['plugins'])
			) {
				$plugins = array_merge($plugins, $demo['plugins']);
			}
		}

		return array_values(array_unique($plugins));
	}
}


