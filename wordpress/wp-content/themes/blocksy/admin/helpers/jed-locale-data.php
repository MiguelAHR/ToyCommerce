<?php

if (! function_exists('blocksy_get_json_translation_files')) {
	function blocksy_get_json_translation_files($domain) {
		$locale = determine_locale();

		$locations = [
			WP_LANG_DIR . '/themes',
			WP_LANG_DIR . '/plugins'
		];

		$result = [];

		foreach ($locations as $location) {
			$files = glob(
				$location . '/' . $domain . '-' . $locale . '-*.json'
			);

			if (! $files) {
				continue;
			}

			$result = array_merge($result, $files);
		}

		return $result;
	}
}

if (! function_exists('blocksy_get_jed_locale_data')) {
	function blocksy_get_jed_locale_data($domain) {
		static $locale = [];

		if (isset($locale[$domain])) {
			return $locale[$domain];
		}

		$translations = get_translations_for_domain($domain);

		$locale[$domain] = [
			'' => [
				'domain' => $domain,
				'lang' => get_user_locale(),
			]
		];

		if (! empty($translations->headers['Plural-Forms'])) {
			$locale[$domain]['']['plural_forms'] = $translations->headers['Plural-Forms'];
		}

		foreach (blocksy_get_json_translation_files($domain) as $file_path) {
			$parsed_json = json_decode(
				call_user_func(
					'file' . '_get_contents',
					$file_path
				),
				true
			);

			if (
				! $parsed_json
				||
				! isset($parsed_json['locale_data']['messages'])
			) {
				continue;
			}

			foreach ($parsed_json['locale_data']['messages'] as $msgid => $entry) {
				if (empty($msgid)) {
					continue;
				}

				$locale[$domain][$msgid] = $entry;
			}
		}

		foreach ($translations->entries as $msgid => $entry) {
			$locale[$domain][$entry->key()] = $entry->translations;
		}

		return $locale[$domain];
	}
}
