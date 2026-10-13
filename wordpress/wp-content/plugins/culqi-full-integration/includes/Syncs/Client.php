<?php
namespace Fullculqi\Syncs;

/**
 * Sync Class
 * @since  1.0.0
 * @package Includes / Sync / Client
 */
abstract class Client {

	/**
	 * Post Type
	 * @var string
	 */
	protected string $postType = '';

	/**
	 * Base URL
	 * @var string
	 */
	protected string $baseURL = 'https://api.culqi.com/v2/';

	/**
	 * Endpoint
	 * @var string
	 */
	protected string $endpoint = '';


	/**
	 * Current Item IDs
	 * @var array
	 */
	protected array $currentItemIDs;


	/**
	 * Get item from Culqi when it creates a post type
	 * @var boolean
	 */
	protected bool $getItemToWPCreate = false;


	/**
	 * Header to rquest
	 * @var array
	 */
	protected array $headers = [
		'Content-Type'		=> 'application/json',
		'Accept'			=> 'application/json',
		'Accept-Encoding'	=> '*',
	];


	/**
	 * Timeout in seconds
	 * @var integer
	 */
	protected int $timeout = 120;


	/**
	 * Get All ( listing )
	 * @return array
	 */
	protected function requestGetAll( array $params = [] ): \stdClass {
		global $wp_version;

		$args = [
			'method'     => 'GET',
			'headers'    => $this->getHeaders(),
			'timeout'    => $this->getTimeout(),
			'user-agent' => \sprintf( 'WordPress/%s; %s', $wp_version, \get_bloginfo( 'url' ) ),
		];

		try {
			$response = \wp_remote_get( $this->getURLByParams( $params ), $args );
		} catch( Exception $e ) {
			return (object)[
				'success' => false,
				'data'    => (object)[ 'message' => $e->getMessage() ]
			];
		}

		$responseCode = \wp_remote_retrieve_response_code( $response );
		$responseBody = \wp_remote_retrieve_body( $response );

		$body = \json_decode( $responseBody );

		if ( \json_last_error() !== JSON_ERROR_NONE ) {
			return (object)[
				'success' => false,
				'data'    => (object)[
					'message' => \esc_html__('Body json decode error', 'fullculqi')
				]
			];
		}

		$isSuccessCode = $responseCode >= 200 && $responseCode <= 206;
		
		if ( ! $isSuccessCode && ! empty( $body->merchant_message ) ) {
			return (object)[
				'success' => false,
				'data'    => (object)[ 'message' => $body->merchant_message ]
			];
		}

		return (object)[
			'success' => true,
			'data'    => (object)[ 'list' => $body->data, 'paging' => $body->paging ],
		];
	}

	/**
	 * Get All ( listing )
	 * @return array
	 */
	protected function requestGet( string $itemID ): \stdClass {
		global $wp_version;

		$args = [
			'method'     => 'GET',
			'headers'    => $this->getHeaders(),
			'timeout'    => $this->getTimeout(),
			'user-agent' => \sprintf( 'WordPress/%s; %s', $wp_version, \get_bloginfo( 'url' ) ),
		];

		try {
			$response = \wp_remote_get( $this->getURL( $itemID ), $args );
		} catch( Exception $e ) {
			return (object)[
				'success' => false,
				'data'    => (object)[ 'message' => $e->getMessage() ]
			];
		}

		$responseCode = \wp_remote_retrieve_response_code( $response );
		$responseBody = \wp_remote_retrieve_body( $response );

		$body = \json_decode( $responseBody );

		if ( \json_last_error() !== JSON_ERROR_NONE ) {
			return (object)[
				'success' => false,
				'data'    => (object)[
					'message' => \esc_html__('Body json decode error', 'fullculqi')
				]
			];
		}

		$isSuccessCode = \absint( $responseCode ) === 200;
		
		if ( ! $isSuccessCode && ! empty( $body->merchant_message ) ) {
			return (object)[
				'success' => false,
				'data'    => (object)[ 'message' => $body->merchant_message ]
			];
		}

		return (object)[
			'success' => true,
			'data'    => (object)[ 'body' => $body ],
		];
	}


	/**
	 * Request POST
	 * @param  array  $payload
	 * @return bool
	 */
	protected function requestPost( array $payload, string $itemID = '' ): \stdClass {
		global $wp_version;

		$args = [
			'method'     => 'POST',
			'body'       => \wp_json_encode( $payload ),
			'headers'    => $this->getHeaders(),
			'timeout'    => $this->getTimeout(),
			'user-agent' => \sprintf( 'WordPress/%s; %s', $wp_version, \get_bloginfo( 'url' ) ),
		];
		
		try {
			$response = \wp_remote_post( $this->getURL( $itemID ), $args );
		} catch( Exception $e ) {
			return (object)[
				'success' => false,
				'data'    => (object)[ 'message' => $e->getMessage() ]
			];
		}

		$responseCode = \wp_remote_retrieve_response_code( $response );
		$responseBody = \wp_remote_retrieve_body( $response );

		$body = \json_decode( $responseBody );

		if ( \json_last_error() !== JSON_ERROR_NONE ) {
			return (object)[
				'success' => false,
				'data'    => (object)[
					'message' => \esc_html__( 'Body json decode error', 'fullculqi' )
				]
			];
		}

		$isSuccessCode = $responseCode >= 200 && $responseCode <= 206;
		
		if ( ! $isSuccessCode && ! empty( $body->merchant_message ) ) {
			return (object)[
				'success' => false,
				'data'    => (object)[ 'message' => $body->merchant_message ]
			];
		}

		return (object)[
			'success' => true,
			'data'    => (object)[ 'body' => $body ],
		];
	}


	/**
	 * Request POST
	 * @param  array  $payload
	 * @return bool
	 */
	protected function requestPatch( string $itemID, array $payload ): \stdClass {
		global $wp_version;

		$args = [
			'method'     => 'PATCH',
			'body'       => \wp_json_encode( $payload ),
			'headers'    => $this->getHeaders(),
			'timeout'    => $this->getTimeout(),
			'user-agent' => \sprintf( 'WordPress/%s; %s', $wp_version, \get_bloginfo( 'url' ) ),
		];

		try {
			$response = \wp_remote_request(
				$this->getURL( \sprintf( '%s/confirm/', $itemID ) ),
				$args
			);
		} catch( Exception $e ) {
			return (object)[
				'success' => false,
				'data'    => (object)[ 'message' => $e->getMessage() ]
			];
		}

		$responseCode = \wp_remote_retrieve_response_code( $response );
		$responseBody = \wp_remote_retrieve_body( $response );

		$body = \json_decode( $responseBody );

		if ( \json_last_error() !== JSON_ERROR_NONE ) {
			return (object)[
				'success' => false,
				'data'    => (object)[
					'message' => \esc_html__('Body json decode error', 'fullculqi')
				]
			];
		}

		$isSuccessCode = \absint( $responseCode ) === 200;
		
		if ( ! $isSuccessCode && ! empty( $body->merchant_message ) ) {
			return (object)[
				'success' => false,
				'data'    => (object)[ 'message' => $body->merchant_message ]
			];
		}

		return (object)[
			'success' => true,
			'data'    => (object)[ 'body' => $body ],
		];
	}

	/**
	 * Request POST
	 * @param  array  $payload
	 * @return bool
	 */
	protected function requestDelete( string $itemID ): \stdClass {
		global $wp_version;

		$args = [
			'method'     => 'DELETE',
			'headers'    => $this->getHeaders(),
			'timeout'    => $this->getTimeout(),
			'user-agent' => \sprintf( 'WordPress/%s; %s', $wp_version, \get_bloginfo( 'url' ) ),
		];

		try {
			$response = \wp_remote_request(
				$this->getURL( $itemID ),
				$args
			);
		} catch( Exception $e ) {
			return (object)[
				'success' => false,
				'data'    => (object)[ 'message' => $e->getMessage() ]
			];
		}

		$responseCode = \wp_remote_retrieve_response_code( $response );
		$responseBody = \wp_remote_retrieve_body( $response );

		$body = \json_decode( $responseBody );

		if ( \json_last_error() !== JSON_ERROR_NONE ) {
			return (object)[
				'success' => false,
				'data'    => (object)[
					'message' => \esc_html__('Body json decode error', 'fullculqi')
				]
			];
		}

		$isSuccessCode = \absint( $responseCode ) === 200;
		
		if ( ! $isSuccessCode && ! empty( $body->merchant_message ) ) {
			return (object)[
				'success' => false,
				'data'    => (object)[ 'message' => $body->merchant_message ]
			];
		}

		return (object)[ 'success' => true ];
	}


	/**
	 * Get URL with params
	 * @param  array  $params
	 * @return string
	 */
	protected function getURLByParams( array $params = [] ): string {
		return \sprintf( '%s?%s', $this->getURL(), \http_build_query( $params ) );
	}


	/**
	 * GeT URL by Item ID
	 * @return string
	 */
	protected function getURL( string $itemID = '' ): string {
		return $this->baseURL . $this->endpoint . $itemID ;
	}

	/**
	 * Get Timeout
	 * @return int
	 */
	protected function getTimeout(): int {
		return $this->timeout;
	}

	/**
	 * get Headers to connnection
	 * @return array
	 */
	protected function getHeaders(): array {

		$settings = \fullculqi_get_settings();

		$this->headers[ 'Authorization' ] = \sprintf( 'Bearer %s', $settings['secret_key'] );

		return $this->headers;
	}




	/**
	 * Sync from Culqi
	 * @param  integer $record
	 * @return mixed
	 */
	public function sync( int $record = 50, string $afterID = '' ): \stdClass {

		$params = [ 'limit' => $record ];

		if( ! empty( $afterID ) ) {
			$params[ 'after' ] = $afterID;
		}

		// Connect to the API Culqi
		$items = $this->requestGetAll( $params );

		if ( ! $items->success ) {
			return $items;
		}

		// Empty data
		if( empty( $items->data->list ) ) {
			return (object)[
				'success'	=> true,
				'data'		=> (object)[
					'after_id'	=> null,
				]
			];
		}

		global $wpdb;

		$query = \sprintf(
			'SELECT
				p.ID AS post_id,
				m.meta_value AS culqi_id
			FROM
				%s AS p
			INNER JOIN
				%s AS m
			ON
				p.ID = m.post_id
			WHERE
				p.post_type = "%s" AND
				m.meta_key = "culqi_id" AND
				m.meta_value <> ""',
			$wpdb->posts,
			$wpdb->postmeta,
			$this->postType
		);

		$results = $wpdb->get_results( $query );
		$keys    = [];

		// Keys Post Type 
		foreach ( $results as $result ) {
			$keys[ $result->culqi_id ] = $result->post_id;
		}

		// Culqi items
		foreach ( $items->data->list as $itemList ) {

			$postID = null;
			$item   = $itemList;

			// Check if is update
			if ( isset( $keys[ $item->id ] ) ) {
				$postID = $keys[ $item->id ];
			}

			// If it needs Single Get Sync from Culqi
			if ( $this->getItemToWPCreate ) {
				$itemSingle = $this->requestGet( $item->id );

				if ( ! $itemSingle->success ) {
					return $itemSingle;
				}

				$item = $itemSingle->data->body;
			}

			// Create item Post
			$postID = $this->createWPPost( $item, $postID );

			$this->afterCreateWPPost( $item, $postID );

			\do_action( \sprintf( 'fullculqi/%s/sync/loop', $this->postType ), $item, $postID );
		}

		\do_action( \sprintf( 'fullculqi/%s/sync/after', $this->postType ), $items );

		return (object)[
			'success'	=> true,
			'data'		=> (object)[
				'after_id'	      => $items->data->paging->cursors->after ?? null,
				'remaining_items' => $items->data->paging->remaining_items ?? null,
			]
		];
	}
	
	/**
	 * Get By Item ID
	 * @param string $itemID
	 * @param int|null $itemPostID
	 * @return \stdClass
	 */
	public function syncByItem( string $itemID, ?int $itemPostID = null ): \stdClass {
		$itemSingle = $this->requestGet( $itemID );

		if ( ! $itemSingle->success ) {
			return $itemSingle;
		}

		$postID = $this->createWPPost( $itemSingle->data->body, $itemPostID );
		
		$this->afterCreateWPPost( $itemSingle, $postID );

		\do_action(
			\sprintf( 'fullculqi/%s/sync_by_item', $this->postType ),
			$itemSingle->data->body, $itemPostID
		);

		return (object)[
			'success'	=> true,
			'data'		=> (object)[
				'postID' => $postID,
				'itemID' => $itemID,
			]
		];
	}


	/**
	 * Set Current Item IDs
	 * @param array $currentItemIDs
	 */
	public function setCurrentItemIDs( array $currentItemIDs ): void {
		$this->currentItemIDs = $currentItemIDs;
	}


	/**
	 * Get Current Item IDs
	 * @return array
	 */
	public function getCurrentItemIDs(): array {
		return $this->currentItemIDs ?: [];
	}

	/**
	 * Get Current Item ID by Key
	 * @param  string $key
	 * @return string
	 */
	public function getCurrentItemID( string $key ): string {
		return $this->currentItemIDs[ $key ] ?? '';
	}


	/**
	 * Check if it have current items
	 * @return boolean
	 */
	public function haveCurrentItemIDs(): bool {
		return ! empty( $this->currentItemIDs );
	}


	/**
	 * Delete WPPost
	 * @return void
	 */
	public function deleteWPPosts(): bool {
		global $wpdb;

		$query = \sprintf(
			'DELETE
				a, b, c
			FROM
				%s a
			LEFT JOIN
				%s b
			ON
				(a.ID = b.object_id)
			LEFT JOIN
				%s c
			ON
				(a.ID = c.post_id)
			WHERE
				a.post_type = "%s"',
			$wpdb->posts,
			$wpdb->term_relationships,
			$wpdb->postmeta,
			$this->postType
		);

		$wpdb->query( $query );

		\do_action( \sprintf( 'fullculqi/%s/wpdelete', $this->postType ) );

		return empty( $wpdb->last_error );
	}
	
	
	/**
	 * Afte create Post
	 * @param  \stdClass $item
	 * @param  int|null  $postID 
	 * @return void
	 */
	public function afterCreateWPPost( \stdClass $item, ?int $postID = null ): void {}
}