<?php

namespace Blocksy\Extensions\NewsletterSubscribe;

class KlaviyoProvider extends Provider {
	public function __construct() {
	}

	private function request($method, $path, $api_key, $body = null) {
		$args = [
			'timeout' => 30,
			'method' => $method,
			'headers' => [
				'Authorization' => 'Klaviyo-API-Key ' . $api_key,
				'accept' => 'application/vnd.api+json',
				'content-type' => 'application/vnd.api+json',
				'revision' => '2025-10-15',
			],
		];

		if (! is_null($body)) {
			$args['body'] = wp_json_encode($body);
		}

		$response = wp_remote_request(
			'https://a.klaviyo.com/api/' . ltrim($path, '/'),
			$args
		);

		if (is_wp_error($response)) {
			return [
				'error' => $response->get_error_message(),
				'code' => 0,
				'body' => [],
			];
		}

		$decoded_body = json_decode(wp_remote_retrieve_body($response), true);

		if (! is_array($decoded_body)) {
			$decoded_body = [];
		}

		return [
			'error' => null,
			'code' => wp_remote_retrieve_response_code($response),
			'body' => $decoded_body,
		];
	}

	public function fetch_lists($api_key, $api_url = '') {
		$response = $this->request('GET', 'lists', $api_key);

		if ($response['error']) {
			return 'api_key_invalid';
		}

		if (200 !== $response['code']) {
			return 'api_key_invalid';
		}

		if (! isset($response['body']['data']) || ! is_array($response['body']['data'])) {
			return 'api_key_invalid';
		}

		return array_map(function ($list) {
			$attributes = isset($list['attributes']) ? $list['attributes'] : [];
			$opt_in_process = isset($attributes['opt_in_process']) ? $attributes['opt_in_process'] : '';

			return [
				'name' => $attributes['name'],
				'id' => $list['id'],
				'double_optin' => $opt_in_process === 'double_opt_in',
			];
		}, $response['body']['data']);
	}

	public function subscribe_form($args = []) {
		$args = wp_parse_args($args, [
			'email' => '',
			'name' => '',
			'group' => '',
			'double_optin' => false,
		]);

		$settings = $this->get_settings();

		$name_parts = $this->maybe_split_name($args['name']);
		$fname = $name_parts['first_name'];
		$lname = $name_parts['last_name'];

		$subscriber = [
			'email' => $args['email'],
		];

		if (! empty($fname)) {
			$subscriber['first_name'] = $fname;
		}

		if (! empty($lname)) {
			$subscriber['last_name'] = $lname;
		}

		if ($args['double_optin']) {
			$doi_profile = [
				'type' => 'profile',
				'attributes' => [
					'email' => $args['email'],
					'subscriptions' => [
						'email' => [
							'marketing' => [
								'consent' => 'SUBSCRIBED',
							],
						],
					],
				],
			];

			$response = $this->request(
				'POST',
				'profile-subscription-bulk-create-jobs',
				$settings['api_key'],
				[
					'data' => [
						'type' => 'profile-subscription-bulk-create-job',
						'attributes' => [
							'custom_source' => 'Marketing Event',
							'profiles' => [
								'data' => [
									$doi_profile,
								],
							],
						],
						'relationships' => [
							'list' => [
								'data' => [
									'type' => 'list',
									'id' => $args['group'],
								],
							],
						],
					],
				]
			);

			if ($response['error'] || ! in_array($response['code'], [200, 201, 202], true)) {
				return [
					'result' => 'no',
					'message' => NewsletterMessages::unable_to_subscribe(),
					'error' => $response['error'],
				];
			}

			// Best-effort profile sync for first/last name after DOI job was accepted.
			// This is intentionally non-blocking for subscription flow.
			if (! empty($fname) || ! empty($lname)) {
				$this->request(
					'POST',
					'profile-import',
					$settings['api_key'],
					[
						'data' => [
							'type' => 'profile',
							'attributes' => $subscriber,
						],
					]
				);
			}

			return [
				'result' => 'yes',
				'message' => NewsletterMessages::confirm_subscription(),
			];
		}

		$import_response = $this->request(
			'POST',
			'profile-import',
			$settings['api_key'],
			[
				'data' => [
					'type' => 'profile',
					'attributes' => $subscriber,
				],
			]
		);

		if ($import_response['error'] || ! in_array($import_response['code'], [200, 201, 202], true)) {
			return [
				'result' => 'no',
				'message' => NewsletterMessages::unable_to_subscribe(),
				'error' => $import_response['error'],
			];
		}

		$profile_id = $import_response['body']['data']['id'] ?? '';

		if (! $profile_id) {
			return [
				'result' => 'no',
				'message' => NewsletterMessages::unable_to_subscribe(),
			];
		}

		$list_response = $this->request(
			'POST',
			'lists/' . rawurlencode($args['group']) . '/relationships/profiles',
			$settings['api_key'],
			[
				'data' => [
					[
						'type' => 'profile',
						'id' => $profile_id,
					],
				],
			]
		);

		if ($list_response['error'] || ! in_array($list_response['code'], [200, 201, 202, 204], true)) {
			return [
				'result' => 'no',
				'message' => NewsletterMessages::unable_to_subscribe(),
				'error' => $list_response['error'],
			];
		}

		return [
			'result' => 'yes',
			'message' => NewsletterMessages::subscribed_successfully()
		];
	}
}
