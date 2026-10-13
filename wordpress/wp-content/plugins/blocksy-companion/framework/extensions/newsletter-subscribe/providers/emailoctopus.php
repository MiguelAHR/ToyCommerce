<?php

namespace Blocksy\Extensions\NewsletterSubscribe;

class EmailOctopusProvider extends Provider {
	public function fetch_lists($api_key, $api_url = '') {
		if (! $api_key) {
			return 'api_key_invalid';
		}

		$response = wp_remote_get(
			"https://api.emailoctopus.com/lists",
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $api_key,
					'accept' => 'application/json',
				]
			]
		);

		if (! is_wp_error($response)) {
			if (200 !== wp_remote_retrieve_response_code($response)) {
				return 'api_key_invalid';
			}

			$body = json_decode(wp_remote_retrieve_body($response), true);

			if (! $body || ! isset($body['data'])) {
				return 'api_key_invalid';
			}

			return array_map(function($list) {
				return [
					'name' => $list['name'],
					'id' => $list['id'],

					'double_optin' => $list['double_opt_in']
				];
			}, $body['data']);
		} else {
			return 'api_key_invalid';
		}
	}

	public function subscribe_form($args = []) {
		$args = wp_parse_args($args, [
			'email' => '',
			'name' => '',
			'group' => '',
			'double_optin' => false,
		]);

		$settings = $this->get_settings();
		$target_status = $args['double_optin'] ? 'pending' : 'subscribed';

		// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_init
		$curl = curl_init();

		// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt_array
		curl_setopt_array($curl, [
		CURLOPT_URL => 'https://api.emailoctopus.com/lists/' . rawurlencode($args['group']) . '/contacts',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => json_encode([
			'email_address' => $args['email'],
			'fields' => [
				'FirstName' => $args['name']
			],
			'tags' => [],
			'status' => $target_status,
		]),
		CURLOPT_HTTPHEADER => [
			"Authorization: Bearer {$settings['api_key']}",
			"content-type: application/json"
		],
		]);

		// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_exec
		$response = curl_exec($curl);
		// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_error
		$err = curl_error($curl);

		// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_close
		curl_close($curl);

		if ($err) {
			return [
				'result' => 'no',
				'error' => $err,
			];
		} else {
			return [
				'result' => 'yes',
				'message' => NewsletterMessages::subscribed_successfully(),
			];
		}
	}
}
