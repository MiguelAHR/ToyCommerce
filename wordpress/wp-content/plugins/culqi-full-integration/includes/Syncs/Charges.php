<?php
Namespace Fullculqi\Syncs;

use Fullculqi\Traits\Singleton;

/**
 * Charges Class
 * @since  1.0.0
 * @package Includes / Sync / Charges
 */
class Charges extends Client {
	use Singleton;

	/**
	 * PostType
	 * @var string
	 */
	protected string $postType = 'culqi_charges';

	/**
	 * Endpoint to sync
	 * @var string
	 */
	protected string $endpoint = 'charges/';


	/**
	 * Update Charge
	 * @param  OBJ $charge
	 * @return mixed
	 */
	public function update( $charge ) {

		$post_id = fullculqi_post_from_meta( 'culqi_id', $charge->id );

		if( ! empty( $post_id ) ) {
			$post_id = self::create_wppost( $charge, $post_id );
		}

		\do_action(  \sprintf( 'fullculqi/%s/update', $this->postType ), $charge );
	}


	/**
	 * Create a charge
	 * @param  array  $post_data
	 * @return bool
	 */
	public function create( array $args ): \stdClass {

		$args   = \apply_filters( \sprintf( 'fullculqi/%s/create/args', $this->postType ), $args );
		$charge = $this->requestPost( $args );

		if ( ! $charge->success ) {
			return $charge;
		}

		// If it need a review. Apply 3Ds
		if( ! empty( $charge->data->body->action_code ) && $charge->data->body->action_code === 'REVIEW' ) {
			return (object)[
				'success' => true,
				'data'    => (object)[ 'needs3Ds' => true ]
			];
		}

		// Create wppost
		$postID = $this->createWPPost( $charge->data->body );

		\do_action(
			\sprintf( 'fullculqi/%s/create', $this->postType ), $postID, $charge->data->body
		);
		
		return (object) \apply_filters( \sprintf( 'fullculqi/%s/create/success', $this->postType ), [
			'success' => true,
			'data'    => (object)[
				'needs3Ds'      => false,
				'culqiChargeID' => $charge->data->body->id,
				'postChargeID'  => $postID
			]
		] );
	}


	/**
	 * Create WPPosts
	 * @param  object  $charge  
	 * @param  integer $post_id 
	 * @return mixed
	 */
	public function createWPPost( \stdClass $charge, ?int $postID = null ): int {

		if ( empty( $postID ) ) {
			
			$args = [
				'post_title'  => $charge->id,
				'post_type'   => 'culqi_charges',
				'post_status' => 'publish'
			];

			$postID = \wp_insert_post( $args );
		}

		\update_post_meta( $postID, 'culqi_data', $charge );
		\update_post_meta( $postID, 'culqi_id', $charge->id );
		\update_post_meta( $postID, 'culqi_authorization', $charge->authorization_code );
		\update_post_meta( $postID, 'culqi_capture', $charge->capture );
		\update_post_meta( $postID, 'culqi_capture_date',
			fullculqi_convertToDate( $charge->capture_date )
		);

		// If it use customer process
		if( isset( $charge->source->object ) && $charge->source->object == 'card' ) {
			\update_post_meta( $postID, 'culqi_ip', $charge->source->source->client->ip );

			\update_post_meta( $postID, 'culqi_card', [
				'card_number' => $charge->source->source->card_number ?? '',
				'card_last4'  => $charge->source->source->last_four  ?? '',
				'card_brand'  => $charge->source->source->iin->card_brand  ?? '',
				'card_bin'    => $charge->source->source->iin->bin ?? '',
				'card_type'   => $charge->source->source->iin->card_type  ?? '',
			] );
		} else {
			\update_post_meta( $postID, 'culqi_ip', $charge->source->client->ip );

			\update_post_meta( $postID, 'culqi_card', [
				'card_number' => $charge->source->card_number ?? '',
				'card_last4'  => $charge->source->last_four  ?? '',
				'card_brand'  => $charge->source->iin->card_brand  ?? '',
				'card_bin'    => $charge->source->iin->bin ?? '',
				'card_type'   => $charge->source->iin->card_type  ?? '',
			] );
		}

		// Token type ( card or yape )
		$isYape = $charge->source->object == 'token' && \fullculqi_is_token_yape( $charge->source->id );
		\update_post_meta( $postID, 'culqi_charge_type', $isYape ? 'yape' : 'charge' );

		// Status
		\update_post_meta(
			$postID,
			'culqi_status',
			$charge->current_amount == 0 ? 'refunded' : ( $charge->capture ? 'captured' : 'authorized' )
		);

		// Creation Date
		\update_post_meta( $postID, 'culqi_creation_date',
			fullculqi_convertToDate( $charge->creation_date )
		);

		// Meta Values
		if ( ! empty( $charge->metadata ) ) {
			\update_post_meta( $postID, 'culqi_metadata', $charge->metadata );

			if ( ! empty( $charge->metadata->culqi_customer_id ) ) {
				\update_post_meta(
					$postID, 'culqi_customer_id', $charge->metadata->culqi_customer_id
				);
			}
			
			if ( ! empty( $charge->metadata->post_customer_id ) ) {
				\update_post_meta(
					$postID, 'post_customer_id', $charge->metadata->post_customer_id
				);
			}
		}

		$basic = [
			'culqi_amount'			=> \round( $charge->amount/100, 2 ),
			'culqi_current_amount'	=> \round( $charge->current_amount/100, 2 ),
			'culqi_amount_refunded'	=> \round( $charge->amount_refunded/100, 2 ),
			'culqi_currency'		=> $charge->currency_code,
			'culqi_installments'    => $charge->installments,
		];

		\update_post_meta( $postID, 'culqi_basic', \array_map( 'esc_html', $basic ) );

		$antifraud = [ 'culqi_email' => $charge->email ];

		if ( ! empty( $charge->antifraud_details ) ) {

			$antifraud = \array_merge( $antifraud, [
				'culqi_first_name' => $charge->antifraud_details->first_name ?? '',
				'culqi_last_name'  => $charge->antifraud_details->last_name ?? '',
				'culqi_city'       => $charge->antifraud_details->address_city ?? '',
				'culqi_country'    => $charge->antifraud_details->country_code ?? '',
				'culqi_phone'      => $charge->antifraud_details->phone ?? '',
			] );
		}

		\update_post_meta( $postID, 'culqi_customer', \array_map( 'esc_html', $antifraud ) );

		\do_action( \sprintf( 'fullculqi/%s/wppost_create', $this->postType ), $charge, $postID );

		return $postID;
	}


	/**
	 * Process Refund
	 * @param  int       $postChargeID
	 * @param  \stdClass $refund
	 * @return void
	 */
	public function processRefund( int $postChargeID, \stdClass $refund ):void {
		
		// save refunded Amount
		$basic = get_post_meta( $postChargeID, 'culqi_basic', true );

		$refundedAmount = \round( $refund->culqiRefundAmount / 100, 2 );
		
		$basic['culqi_current_amount'] -= $refundedAmount;
		$basic['culqi_amount_refunded'] += $refundedAmount;

		\update_post_meta( $postChargeID, 'culqi_basic', $basic );

		if ( $basic['culqi_current_amount'] == 0 ) {
			\update_post_meta( $postChargeID, 'culqi_status', 'refunded' );
		}

		// Save refund IDs
		$refundsIDs = \get_post_meta( $postChargeID, 'culqi_refunded_ids', true ) ?: [];
		
		$refundsIDs[ $refund->culqiRefundID ] = $refundedAmount;

		\update_post_meta( $postChargeID, 'culqi_refunded_ids', $refundsIDs );
	}

}