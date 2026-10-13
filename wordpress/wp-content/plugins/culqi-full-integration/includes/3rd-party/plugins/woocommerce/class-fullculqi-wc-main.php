<?php

use Automattic\WooCommerce\Utilities\FeaturesUtil;

/**
 * WooCommerce Class
 * @since  1.0.0
 * @package Includes / 3rd-party / plugins / WooCommerce
 */
class FullCulqi_WC {

	public $log;

	public function __construct() {
		// Load the method payment
		add_action( 'after_setup_theme', [ $this, 'include_file' ], 50 );

		// Include Class
		add_filter( 'woocommerce_payment_gateways', [ $this, 'include_class' ] );

		// Actions
		add_action( 'fullculqi/api/wc-actions', [ $this, 'actions' ] );

		// Webhook Update order
		add_action( 'fullculqi/culqi_orders/update', [ $this, 'updateCulqiOrder' ], 10, 2 );

		// Lets add module type to script
		add_action( 'script_loader_tag', [ $this, 'addAttrToScript' ], 10, 3 );

		// HPOST compatibility
		add_action( 'before_woocommerce_init', [ $this, 'declareCompatibilty' ] );

		// Order Details
		add_action( 'woocommerce_after_order_details', [ $this, 'culqiOrderContent' ] );
	}


	/**
	 * Include the method payment
	 * @return mixed
	 */
	public function include_file() {

		// Check if WC is installed
		if ( ! class_exists( 'WC_Payment_Gateway' ) )
			return;

		// Check if WC has the supported currency activated
		$supported_currencies = array_keys( fullculqi_currencies() );
		if ( ! in_array( get_woocommerce_currency(), $supported_currencies ) ) {
			add_action( 'admin_notices', [ $this, 'notice_currency'] );
			return;
		}

		require_once FULLCULQI_WC_DIR . 'class-fullculqi-wc-method.php';
	}

	/**
	 * Include the gateway class
	 * @param  array $methods
	 * @return array
	 */
	public function include_class( array $methods = [] ): array {

		// Check if FullCulqi Class is included
		if ( ! class_exists( 'WC_Gateway_FullCulqi' ) ) {
			return $methods;
		}

		$methods[] = 'WC_Gateway_FullCulqi';
		
		return $methods;
	}

	/**
	 * Actions
	 * @return mixed
	 */
	public function actions() {
		
		if ( ! isset( $_POST['action'] ) ) {
			return;
		}

		// Run a security check.
		\check_ajax_referer( 'fullculqi', 'wpnonce' );

		$return   = false;
		$postData = fullculqi_esc_html( $_POST );

		// Check if the order is already being processed
		$orderID      = \absint( $postData['order_id'] );
		$blockedKey   = \sprintf( 'fullculqi_blocked_process_%d', $orderID );
		$blockedValue = \get_transient( $blockedKey ) ?: '';

		if ( $blockedValue === 'yes' ) {
			return;
		}

		\set_transient( $blockedKey, 'yes', 10 );

		switch( $postData['action'] ) {
			case 'order' :  $return = FullCulqi_WC_Process::order( $postData ); break;
			case 'charge' : $return = FullCulqi_WC_Process::charge( $postData ); break;
		}

		$return = \apply_filters( 'fullculqi/wc-actions', $return, $postData );
		

		if ( $return ) {

			$order = \wc_get_order( $orderID );

			if ( $order instanceof \WC_Order ) {
				wp_send_json_success( [
					'needs3Ds' => $order->meta_exists( '_culqi_needs3Ds' )
				] );

			} else {
				\wp_send_json_success( [ 'needs3Ds' => false ] );
			}
			
		} else {
			\wp_send_json_error();
		}
	}



	/**
	 * Update Order
	 * @param  OBJECT $culqi_order
	 * @return mixed
	 */
	public function updateCulqiOrder( \stdClass $culqiOrder, int $postOrderID ) {

		if ( ! isset( $culqiOrder->id ) ) {
			return;
		}

		$orderID = get_post_meta( $postOrderID, 'culqi_wc_order_id', true );
		$order   = new WC_Order( $orderID );

		if ( ! $order instanceof WC_Order ) {
			return;
		}

		// Log
		$log = new FullCulqi_Logs( $order->get_id() );

		// Payment Settings
		$method = get_option( 'woocommerce_fullculqi_settings', [] );


		switch( $culqiOrder->state ) {
			case 'paid' :

				$notice = sprintf(
					/* translators: %s: CIP Code */
					esc_html__( 'The CIP %s was paid', 'fullculqi' ),
					$cip_code
				);

				$order->add_order_note( $notice );
				$log->set_notice( $notice );

				// Status
				if( $method['status_success'] == 'wc-completed') {
					$order->payment_complete();
				} else {

					$order->update_status( $method['status_success'],
						sprintf(
							/* translators: %s: new WC Status */
							esc_html__( 'Status changed by FullCulqi (to %s)', 'fullculqi' ),
							$method['status_success']
						)
					);
				}

				break;

			case 'expired' :

				$error = sprintf(
					/* translators: %s: CIP expired */
					esc_html__( 'The CIP %s expired', 'fullculqi' ),
					$cip_code
				);

				$log->set_error( $error );
				$order->update_status( 'cancelled', $error );

				break;

			case 'deleted' :

				$error = sprintf(
					/* translators: %s: CIP Code to Delete */
					esc_html__( 'The CIP %s was deleted', 'fullculqi' ),
					$cip_code
				);
				
				$log->set_error( $error );
				$order->update_status( 'cancelled', $error );

				break;
		}

		return true;
	}


	/**
	 * Notice Currency
	 * @return html
	 */
	public function notice_currency() {
		fullculqi_get_template( 'layouts/notice_currency.php', [], FULLCULQI_WC_DIR );
	}


	/**
	 * Add Attr to Script
	 * @param string $tag    [description]
	 * @param string $handle [description]
	 * @param string $src    [description]
	 */
	public function addAttrToScript( string $tag, string $handle, string $src ) {
		if ( 'fullculqi-js' !== $handle ) {
        	return $tag;
		}

		$tag = sprintf(
			'<script type="module" src="%s" id="%s-js"></script>',
			esc_url( $src ), esc_attr( $handle )
		);

		return $tag;
	}


	/**
	 * Declare compatibility
	 * @return void
	 */
	public function declareCompatibilty(): void {
		if ( \class_exists( FeaturesUtil::class ) ) {
			FeaturesUtil::declare_compatibility( 'custom_order_tables', FULLCULQI_FILE, true );
		}
	}


	/**
	 * Checkout Thanks page
	 * @param  WC_Order     $order
	 * @return void
	 */
	public function culqiOrderContent( \WC_Order $order ) {
		$postOrderID = $order->get_meta( '_post_order_id' );

		if ( empty( $postOrderID ) ) {
			return;
		}

		$args = [
			'qr'	=> get_post_meta( $postOrderID, 'culqi_qr', true ),
			'cip'	=> get_post_meta( $postOrderID, 'culqi_cip', true ),
		];

		wc_get_template(
			'layouts/checkout-order.php',
			$args, false, FULLCULQI_WC_DIR
		);
	}
}

new FullCulqi_WC();