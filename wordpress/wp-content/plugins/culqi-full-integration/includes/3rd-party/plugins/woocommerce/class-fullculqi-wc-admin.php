<?php

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * WooCommerce Class
 * @since  1.0.0
 * @package Includes / 3rd-party / plugins / WooCommerce
 */
class FullCulqi_WC_Admin {

	public function __construct() {
		// Metaboxes to Shop Order CPT
		add_action( 'add_meta_boxes', [ $this, 'metaboxes'], 10, 1 );

		// Metaboxes Charges columns
		add_filter( 'fullculqi/charges/column_name', [ $this, 'columnName' ] );
		add_filter( 'fullculqi/charges/column_value', [ $this, 'columnValue' ], 10, 3 );
		add_filter( 'fullculqi/orders/column_name', [ $this, 'columnName' ] );
		add_filter( 'fullculqi/orders/column_value', [ $this, 'columnValue' ], 10, 3 );

		// Metaboxes Charges Edit
		add_action(  'fullculqi/charges/basic/print_data', [ $this, 'basicPrintWCOrder' ] );
		add_action(  'fullculqi/orders/basic/print_data', [ $this, 'basicPrintWCOrder' ] );

		// Ajax Refund
		add_filter( 'fullculqi/ajax/refund/is_external', '__return_true' );
		add_filter( 'fullculqi/ajax/refund/process_external', [ $this, 'createRefundProcess' ] );
	}

	/**
	 * Add Meta Boxes to Shop Order CPT
	 * @param  WP_POST $post
	 * @return mixed
	 */
	public function metaboxes( $post ) {

		if ( ! class_exists('WooCommerce') ) {
			return;
		}

		$orderScreen = \wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
			? \wc_get_page_screen_id( 'shop-order' )
			: 'shop_order';

		add_meta_box(
			'fullculqi_payment_log',
			esc_html__( 'FullCulqi Logs', 'fullculqi' ),
			[ $this, 'metaboxLog' ],
			$orderScreen,
			'normal',
			'core'
		);

		add_meta_box(
			'fullculqi_payment_resume',
			esc_html__( 'FullCulqi Resume', 'fullculqi' ),
			[ $this, 'metaboxResume' ],
			$orderScreen,
			'side',
			'high'
		);
	}


	/**
	 * Metaboxes Log
	 * @param  WP_POST $post
	 * @return mixed
	 */
	public function metaboxLog( $queriedObject ) {

		$order = ( $queriedObject instanceof \WP_Post ) ? \wc_get_order( $queriedObject->ID ) : $queriedObject;

		$args = [
			'logs' => $order->get_meta('culqi_log'),
		];

		fullculqi_get_template( 'layouts/order_log.php', $args, FULLCULQI_WC_DIR );
	}


	/**
	 * Resumen
	 * @param  [type] $queriedObject
	 * @return [type]                [description]
	 */
	public function metaboxResume( $queriedObject ) {
		$order = ( $queriedObject instanceof \WP_Post ) ? \wc_get_order( $queriedObject->ID ) : $queriedObject;

		$paymentType = $order->get_meta( '_culqi_payment_type' );

		if ( $paymentType === 'order' ) {

			$culqiOrderID = $order->get_meta( '_culqi_order_id' );
			$postOrderID  = fullculqi_post_from_meta( 'culqi_id', $culqiOrderID );

			$args = [
				'qr'	=> get_post_meta( $postOrderID, 'culqi_qr', true ),
				'cip'	=> get_post_meta( $postOrderID, 'culqi_cip', true ),
			];

			fullculqi_get_template( 'layouts/order_resume.php', $args, FULLCULQI_WC_DIR );
		}


		if ( $paymentType === 'charge' ) {

			$culqiChargeID = $order->get_meta( '_culqi_charge_id' );
			$postChargeID  = fullculqi_post_from_meta( 'culqi_id', $culqiChargeID );

			$args = [
				'culqi_charge_id'     => get_post_meta( $postChargeID, 'culqi_id', true ),
				'culqi_charge_type'   => get_post_meta( $postChargeID, 'culqi_charge_type', true ),
				'culqi_charge_auth'   => get_post_meta( $postChargeID, 'culqi_authorization', true ),
				'culqi_charge_status' => get_post_meta( $postChargeID, 'culqi_status', true ),
			];

			fullculqi_get_template( 'layouts/charge_resume.php', $args, FULLCULQI_WC_DIR );
		}

		do_action( 'fullculqi/wc/order_resume', $order );
	}


	/**
	 * Charges Column Name
	 * @param  array $newCols]
	 * @param  [type] $cols
	 * @return array
	 */
	public function columnName( array $newCols ): array {

		if ( ! class_exists( 'WooCommerce' ) ) {
			return $newCols;
		}

		$newCols['culqi_wc_order_id'] = esc_html__( 'WC Order', 'fullculqi' );

		return $newCols;
	}


	/**
	 * Charge Column Value
	 * @param  string  $value
	 * @param  string  $col
	 * @param  integer $post_id
	 * @return mixed
	 */
	public function columnValue( ?string $value, string $col, int $postID ) {
		
		if ( $col !== 'culqi_wc_order_id' ) {
			return $value;
		}

		$value   = '';
		$orderID = get_post_meta( $postID, 'culqi_wc_order_id', true );

		if ( ! empty( $orderID ) ) {
			$value = sprintf(
				'<a target="_blank" href="%s">%s</a>',
				get_edit_post_link( $orderID ), $orderID
			);
		}

		return $value;
	}


	/**
	 * Print WC Order in Metaboxes Basic
	 * @param  integer $post_id
	 * @return html
	 */
	public function basicPrintWCOrder( int $postID ) {

		$args = [
			'order_id' => get_post_meta( $postID, 'culqi_wc_order_id', true ) ?? '',
		];
		
		fullculqi_get_template( 'layouts/charge_basic.php', $args, FULLCULQI_WC_DIR );
	}


	/**
	 * Create Refund to WC
	 * @param  integer $post_charge_id
	 * @return mixed
	 */
	public function createRefundProcess( int $postChargeID ): bool {

		// WC Order ID
		$orderID = get_post_meta( $postChargeID, 'culqi_wc_order_id', true );
		$order 	 = wc_get_order( $orderID );

		if ( ! $order instanceof WC_Order ) {
			return false;
		}

		// WC Refund
		$basic = get_post_meta( $postChargeID, 'culqi_basic', true );
	
		$wcRefund = wc_create_refund( [
			'amount'         => wc_format_decimal( $basic['culqi_current_amount'] ),
			'reason'         => \esc_html__( 'Refund from Charge CPT', 'fullculqi' ),
			'order_id'       => $orderID,
			'line_items'     => [],
			'refund_payment' => true,
			'restock_items'  => true,
		] );

		return true;
	}
}

new FullCulqi_WC_Admin();