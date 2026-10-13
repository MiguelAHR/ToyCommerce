<?php

namespace Blocksy\DbVersioning;

class V2145 {
	/**
	 * Companion PRO versions without CompanionUpdateTransientFix lose their
	 * entry in the `update_plugins` transient when any other update (e.g.
	 * this theme) completes first — the Freemius SDK skips re-injecting it
	 * during the post-upgrade transient rebuild. Re-save the transient once
	 * so the SDK filter runs again in a clean context and restores the entry.
	 */
	public function migrate() {
		if (! defined('BLOCKSY_PLUGIN_BASE')) {
			return;
		}

		if (! function_exists('blocksy_companion_fs')) {
			// PRO loads the Freemius SDK only in admin/cron/CLI requests.
			// In those contexts a missing SDK means the free build, which
			// updates from wp.org and is not affected. On the frontend we
			// can't tell yet — retry until a request where PRO would have
			// loaded it.
			if ($this->sdk_would_be_loaded()) {
				return;
			}

			return 'RETRY';
		}

		$transient = get_site_transient('update_plugins');

		if (! is_object($transient)) {
			return;
		}

		if (isset($transient->response[BLOCKSY_PLUGIN_BASE])) {
			return;
		}

		if (isset($transient->no_update[BLOCKSY_PLUGIN_BASE])) {
			return;
		}

		set_site_transient('update_plugins', $transient);
	}

	private function sdk_would_be_loaded() {
		if (is_admin()) {
			return true;
		}

		if (wp_doing_cron()) {
			return true;
		}

		if (defined('WP_CLI') && WP_CLI) {
			return true;
		}

		return false;
	}
}
