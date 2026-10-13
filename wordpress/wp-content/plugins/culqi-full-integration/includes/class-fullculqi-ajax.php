<?php

use Fullculqi\Syncs\Charges;
use Fullculqi\Syncs\Orders;
use Fullculqi\Syncs\Customers;
use Fullculqi\Syncs\Refunds;

/**
 * Ajax Class
 * @since  1.0.0
 * @package Includes / Ajax
 */
class FullCulqi_Ajax {

	public function __construct() {

		// Create a refund
		add_action( 'wp_ajax_create_culqi_refund', [ $this, 'create_refund' ] );

		// Delete All Charges
		add_action( 'wp_ajax_delete_culqi_charges', [ $this, 'deleteCharges' ] );

		// Delete All Orders
		add_action( 'wp_ajax_delete_culqi_orders', [ $this, 'deleteOrders' ] );

		// Delete All Customers
		add_action( 'wp_ajax_delete_culqi_customers', [ $this, 'deleteCustomers' ] );

		// Sync Charges from the admin
		add_action( 'wp_ajax_sync_culqi_charges', [ $this, 'syncCharges' ] );

		// Sync Orders from the admin
		add_action( 'wp_ajax_sync_culqi_orders', [ $this, 'syncOrders' ] );

		// Sync Customers from the admin
		add_action( 'wp_ajax_sync_culqi_customers', [ $this, 'syncCustomers' ] );

	}

	/**
	 * Sync Charges from Admin
	 * @return json
	 */
	public function syncCharges(): mixed {

		// Run a security check.
		\check_ajax_referer( 'fullculqi-wpnonce', 'wpnonce' );

		// Check the permissions
		if ( ! \current_user_can( 'manage_options' ) ) {
			\wp_send_json_error( \esc_html__( 'You do not have permission.', 'fullculqi' ) );
		}

		$record  = \intval( \wp_unslash( $_POST['record'] ?? 50 ) );
		$afterID = \esc_html( \wp_unslash( $_POST['after_id'] ?? '' ) );

		$charges = Charges::getInstance()->sync( $record, $afterID );

		if( ! $charges->success ) {
			\wp_send_json_error( $charges->data );
		}

		\wp_send_json_success( $charges->data );
	}

	/**
	 * Sync Charges from Admin
	 * @return json
	 */
	public function syncOrders(): mixed {

		// Run a security check.
		\check_ajax_referer( 'fullculqi-wpnonce', 'wpnonce' );

		// Check the permissions
		if( ! \current_user_can( 'manage_options' ) ) {
			\wp_send_json_error( \esc_html__( 'You do not have permission.', 'fullculqi' ) );
		}

		$record  = \intval( \wp_unslash( $_POST['record'] ?? 50 ) );
		$afterID = \esc_html( \wp_unslash( $_POST['after_id'] ?? '' ) );

		$orders = Orders::getInstance()->sync( $record, $afterID );

		if ( ! $orders->success ) {
			\wp_send_json_error( $orders->data );
		}

		\wp_send_json_success( $orders->data );
	}


	/**
	 * Sync Customer from Admin
	 * @return json
	 */
	public function syncCustomers(): mixed {
		
		// Run a security check.
		\check_ajax_referer( 'fullculqi-wpnonce', 'wpnonce' );

		// Check the permissions
		if( ! \current_user_can( 'manage_options' ) ) {
			\wp_send_json_error( \esc_html__( 'You do not have permission.', 'fullculqi' ) );
		}

		$record  = \intval( \wp_unslash( $_POST['record'] ?? 50 ) );
		$afterID = \esc_html( \wp_unslash( $_POST['after_id'] ?? '' ) );

		$customers = Customers::getInstance()->sync( $record, $afterID );

		if ( ! $customers->success ) {
			\wp_send_json_error( $customers->data );
		}
			
		\wp_send_json_success( $customers->data );
	}

	/**
	 * Delete all the charges posts
	 * @return mixed
	 */
	public function deleteCharges(): mixed {

		// Run a security check.
		\check_ajax_referer( 'fullculqi-wpnonce', 'wpnonce' );

		// Check the permissions
		if( ! \current_user_can( 'manage_options' ) ) {
			\wp_send_json_error( \esc_html__( 'You do not have permission.', 'fullculqi' ) );
		}

		$isDeleted = Charges::getInstance()->deleteWPPosts();
		
		if( ! $isDeleted ) {
			\wp_send_json_error();
		}

		\wp_send_json_success();	
	}

	/**
	 * Delete all the orders posts
	 * @return mixed
	 */
	public function deleteOrders(): mixed {

		// Run a security check.
		\check_ajax_referer( 'fullculqi-wpnonce', 'wpnonce' );

		// Check the permissions
		if( ! \current_user_can( 'manage_options' ) ) {
			\wp_send_json_error( \esc_html__( 'You do not have permission.', 'fullculqi' ) );
		}

		$isDeleted = Orders::getInstance()->deleteWPPosts();
		
		if( ! $isDeleted ) {
			\wp_send_json_error();
		}

		\wp_send_json_success();	
	}

	/**
	 * Delete all the customers posts
	 * @return mixed
	 */
	public function deleteCustomers(): mixed {

		// Run a security check.
		\check_ajax_referer( 'fullculqi-wpnonce', 'wpnonce' );

		// Check the permissions
		if( ! \current_user_can( 'manage_options' ) ) {
			\wp_send_json_error( \esc_html__( 'You do not have permission.', 'fullculqi' ) );
		}

		$isDeleted = Customers::getInstance()->deleteWPPosts();
		
		if( ! $isDeleted ) {
			\wp_send_json_error();
		}

		\wp_send_json_success();	
	}


	/**
	 * Create Refund from CPT
	 * @return mixed
	 */
	public function create_refund() {

		// Run a security check.
		\check_ajax_referer( 'fullculqi-wpnonce', 'wpnonce' );

		// Check the permissions
		if ( ! \current_user_can( 'manage_options' ) ) {
			\wp_send_json_error([
				'message' => \esc_html__( 'You do not have permission.', 'fullculqi' )
			]);
		}

		// Check if the post exists
		if ( empty( $_POST['post_id'] ) ) {
			\wp_send_json_error([
				'message' => \esc_html__( 'There is no post ID', 'fullculqi' )
			]);
		}

		// Charge Post ID
		$postChargeID = \absint( $_POST['post_id'] );


		// If external process. For example: WooCommerce
		if ( \has_filter( 'fullculqi/ajax/refund/is_external', false ) ) {

			$refundStatus = apply_filters( 'fullculqi/ajax/refund/process_external', $postChargeID );

			if ( ! $refundStatus ) {
				\wp_send_json_error([
					'message' => \esc_html__( 'Process external failed.', 'fullculqi' )
				]);
			}

			\wp_send_json_success();
		}

		// Meta Basic from Charges
		$chargeBasic = \get_post_meta( $postChargeID, 'culqi_basic', true );
		$refundAmount = $chargeBasic['culqi_current_amount'] ?? 0;

		if ( $refundAmount == 0 ) {
			\wp_send_json_error([
				'message' => \esc_html__( 'The amount to be refunded must be greater than 0.', 'fullculqi' )
			]);
		}

		// Culqi Charge ID
		$culqiChargeID = \get_post_meta( $postChargeID, 'culqi_id', true );

		$args = \apply_filters( 'fullculqi/ajax/refund/args', [
			'amount'	=> \round( $refundAmount*100, 0 ),
			'charge_id'	=> $culqiChargeID,
			'reason'	=> 'solicitud_comprador',
			'metadata'	=> [
				'post_charge_id' => $postChargeID,
			],
		], $postChargeID );

		$refund = Refunds::getInstance()->create( $args );

		if ( ! $refund->success ) {
			\wp_send_json_error( [ 'message' => $refund->data->message ] );
		}

		$charge = Charges::getInstance()->processRefund( $postChargeID, $refund->data );

		\do_action( 'fullculqi/ajax/refund/create', $refund->data, $postChargeID );

		\wp_send_json_success();
	}
}

new FullCulqi_Ajax();
?>