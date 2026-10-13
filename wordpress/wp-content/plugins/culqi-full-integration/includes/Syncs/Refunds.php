<?php
Namespace Fullculqi\Syncs;

use Fullculqi\Traits\Singleton;

/**
 * Refunds Class
 * @since  1.0.0
 * @package Includes / Sync / Refunds
 */
class Refunds extends Client {
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
	protected string $endpoint = 'refunds/';

	
	/**
	 * Create Refund
	 * @param  integer $post_id
	 * @param  float  $amount
	 * @return bool
	 */
	public function create( array $args ): \stdClass {

		$args   = \apply_filters( 'fullculqi/culqi_refunds/create/args', $args );
		$refund = $this->requestPost( $args );

		if ( ! $refund->success ) {
			return $refund;
		}

		\do_action( 'fullculqi/culqi_refunds/create', $refund->data->body );

		return (object) \apply_filters( 'fullculqi/culqi_refunds/create/success', [
			'success' => true,
			'data'    => (object)[
				'culqiRefundID'     => $refund->data->body->id,
				'culqiChargeID'     => $refund->data->body->charge_id,
				'culqiRefundAmount' => $refund->data->body->amount,
				'culqiRefundReason' => $refund->data->body->reason,
			]
		] );
	}

}