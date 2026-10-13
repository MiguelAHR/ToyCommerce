<?php
Namespace Fullculqi\Syncs;

use Fullculqi\Traits\Singleton;

/**
 * Orders Class
 * @since  1.0.0
 * @package Includes / Sync / Orders
 */
class Orders extends Client {
	use Singleton;

	/**
	 * PostType
	 * @var string
	 */
	protected string $postType = 'culqi_orders';


	/**
	 * Endpoint to sync
	 * @var string
	 */
	protected string $endpoint = 'orders/';
	

	/**
	 * Create Order
	 * @param  array $args_order
	 * @return array
	 */
	public function create( array $args ): \stdClass {

		$args  = \apply_filters( \sprintf( 'fullculqi/%s/create/args', $this->postType ), $args );
		$order = $this->requestPost( $args );

		if ( ! $order->success ) {
			return $order;
		}

		\do_action( \sprintf( 'fullculqi/%s/create', $this->postType ), $order->data->body );

		return (object) \apply_filters( \sprintf( 'fullculqi/%s/create/success', $this->postType ), [
			'success' => true,
			'data'	  => (object) [ 'culqiOrderID' => $order->data->body->id ]
		] );
	}

	/**
	 * Save metadata in Order
	 * @param  array  $args
	 * @return mixed
	 */
	public function afterConfirm( string $culqiOrderID ): \stdClass {

		//$order = $this->requestPost(
		//	[ 'metadata' => $metadata ],
		//	\sprintf( '%s/confirm', $culqiOrderID )
		//);
		
		$order = $this->requestGet( $culqiOrderID );

		if ( ! $order->success ) {
			return $order;
		}

		// Update post
		$postID = $this->createWPPost( $order->data->body );

		\do_action(
			\sprintf( 'fullculqi/%s/after_confirm', $this->postType ), $postID, $order->data->body
		);

		return (object) \apply_filters(
			\sprintf( 'fullculqi/%s/after_confirm/success', $this->postType ), [
				'success' => true,
				'data'    => (object)[
					'culqiOrderID' => $order->data->body->id,
					'postOrderID'  => $postID
				]
			]
		);
	}

	/**
	 * Update Order from webhook
	 * @param  object $culqi_order
	 * @return mixed
	 */
	public function update( \stdClass $order ) {
		
		if ( ! isset( $order->id ) ) {
			return;
		}

		$postID = fullculqi_post_from_meta( 'culqi_id', $order->id );

		if ( empty( $postID ) ) {
			return;
		}

		$postID = $this->createWPPost( $order, $postID );

		\do_action( \sprintf( 'fullculqi/%s/update', $this->postType ), $order, $postID );
	}

	/**
	 * Create Order Post
	 * @param  integer $post_id
	 * @param  objt $culqi_order
	 * @param  integer $post_customer_id
	 * @return integer
	 */
	public function createWPPost( \stdClass $order, ?\integer $postID = null ) {

		if ( empty( $postID ) ) {

			// Create Post
			$args = [
				'post_title'	=> $order->id,
				'post_type'		=> 'culqi_orders',
				'post_status'	=> 'publish',
			];

			$postID = \wp_insert_post( $args );
		}

		$amount = \round( $order->amount/100, 2 );

		\update_post_meta( $postID, 'culqi_id', $order->id );
		\update_post_meta( $postID, 'culqi_qr', $order->qr ?? '' );
		\update_post_meta( $postID, 'culqi_data', $order );
		\update_post_meta( $postID, 'culqi_status', $order->state );
		\update_post_meta( $postID, 'culqi_status_date', \date( 'Y-m-d H:i:s' ) );
		\update_post_meta( $postID, 'culqi_cip', $order->payment_code ?? '' );
		\update_post_meta( $postID, 'culqi_creation_date', \fullculqi_convertToDate( $order->creation_date ) );

		$basic = [
			'culqi_expiration'		=> \fullculqi_convertToDate( $order->expiration_date ),
			'culqi_amount'			=> $amount,
			'culqi_currency'		=> $order->currency_code,
		];

		\update_post_meta( $postID, 'culqi_basic', $basic );

		// Metavalues
		if ( ! empty( $order->metadata ) ) {
			\update_post_meta( $postID, 'culqi_metadata', $order->metadata );
		}

		// Customers
		$customer = [
			'post_id'          => $order->metadata->post_customer_id ?? 0,
			'culqi_email'      => $order->metadata->wc_order_email ?? '',
			'culqi_first_name' => $order->metadata->wc_order_firstname ?? '',
			'culqi_last_name'  => $order->metadata->wc_order_lastname ?? '',
			'culqi_city'       => $order->metadata->wc_order_city ?? '',
			'culqi_country'    => $order->metadata->wc_order_country ?? '',
			'culqi_phone'      => $order->metadata->wc_order_phone ?? '',
		];

		// Customer
		\update_post_meta( $postID, 'culqi_customer', $customer );


		\do_action( \sprintf( 'fullculqi/%s/wppost', $this->postType ), $order, $postID );

		return $postID;
	}
}