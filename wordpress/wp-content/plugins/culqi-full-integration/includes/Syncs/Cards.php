<?php
Namespace Fullculqi\Syncs;

use Fullculqi\Traits\Singleton;

/**
 * Customers Class
 * @since  1.0.0
 * @package Includes / Sync / Customers
 */
class Cards extends Client {
	use Singleton;

	/**
	 * Endpoint to sync
	 * @var string
	 */
	protected string $endpoint = 'cards/';
	
	/**
	 * Create Card
	 * @param  array  $args
	 * @return array
	 */
	public function create( array $args ): \stdClass {

		$args = \apply_filters( 'fullculqi/culqi_cards/create/args', $args );
		$card = $this->requestPost( $args );

		if ( ! $card->success ) {
			return $card;
		}

		// If it need a review. Apply 3Ds
		if( ! empty( $card->data->action_code ) && $card->data->action_code === 'REVIEW' ) {
			return (object)[
				'success' => true,
				'data'    => (object)[ 'needs3Ds' => true ]
			];
		}

		\do_action( 'fullculqi/culqi_cards/create', $card->data->body );

		return (object) \apply_filters( \sprintf( 'fullculqi/culqi_cards/create/success', $this->postType ), [
			'success' => true,
			'data'    => (object) [
				'culqiCardID'    => $card->data->body->id,
				'culqiCardLast4' => $card->data->body->source->last_four ?? '',
				'culqiCardBrand' => $card->data->body->source->iin->card_brand ?? '',
				'culqiCardBin'   => $card->data->body->source->iin->bin ?? '',
				'culqiCardType'  => $card->data->body->source->iin->card_type ?? '',
				'needs3Ds'       => false,
			]
		] );
	}


	/**
	 * Get Culqi Card ID
	 * @param  string $card_id
	 * @return array
	 */
	public function get( string $culqiCardID ): \stdClass {
		global $culqi;

		$culqiCardID = \apply_filters(
			'fullculqi/culqi_cards/get/id', $culqiCardID
		);

		$card = $this->requestGet( $culqiCardID );

		if ( ! $card->success ) {
			return $card;
		}

		\do_action( 'fullculqi/culqi_cards/get/after', $card->data->body );

		return (object) \apply_filters( 'fullculqi/culqi_cards/get/success', [
			'success' => true,
			'data'    => (object)[
				'culqiCardID'    => $card->data->body->id,
				'culqiCardLast4' => $card->data->body->source->last_four ?? '',
				'culqiCardBrand' => $card->data->body->source->iin->card_brand ?? '',
				'culqiCardType'  => $card->data->body->source->iin->card_type ?? '',
				'culqiCardBin'   => $card->data->body->source->iin->bin ?? '',
			]
		] );
	}
}