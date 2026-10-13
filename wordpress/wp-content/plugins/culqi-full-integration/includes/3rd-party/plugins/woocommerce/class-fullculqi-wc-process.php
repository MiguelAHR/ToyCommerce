<?php

use Fullculqi\Syncs\Charges;
use Fullculqi\Syncs\Orders;
use Fullculqi\Syncs\Customers;
use Fullculqi\Syncs\Cards;

/**
 * WooCommerce Process Class
 * @since  1.0.0
 * @package Includes / 3rd-party / plugins / WooCommerce / Process
 */
class FullCulqi_WC_Process {

	public static $log;

	/**
	 * Create Order
	 * @param  array  $postData
	 * @return mixed
	 */
	public static function order( array $postData ): bool {

		if ( ! isset( $postData['id'] ) ||
			! isset( $postData['cip_code'] ) ||
			! isset( $postData['order_id'] ) ) {
			return false;
		}
			
		// Settings Gateway
		$method = \get_option( 'woocommerce_fullculqi_settings', [] );

		if ( empty( $method ) ) {
			return false;
		}

		// Variables
		$order = \wc_get_order( \absint( $postData['order_id'] ) );

		if( ! $order instanceof \WC_Order ) {
			return false;
		}

		// Log
		self::$log = new FullCulqi_Logs( $order->get_id() );

		$notice = \sprintf(
			/* translators: %s: Multipayment ID */
			\esc_html__( 'Culqi Multipayment ID: %s', 'fullculqi' ), $postData['id']
		);

		self::$log->set_notice( $notice );

		$notice = \sprintf(
			/* translators: %s: Multipayment CIP */
			\esc_html__( 'Culqi Multipayment CIP: %s', 'fullculqi' ), $postData['cip_code']
		);

		self::$log->set_notice( $notice );
		$order->add_order_note( $notice );

		
		$order->update_status( $method['multi_status'],
			\sprintf(
				/* translators: %s: new WC status */
				\esc_html__( 'Status changed by FullCulqi (to %s)', 'fullculqi' ),
				$method['multi_status']
			)
		);

		// Is order payment type
		$order->update_meta_data( '_culqi_payment_type', 'order' );

		// Update CIP CODE in WC Order
		$order->update_meta_data( '_culqi_cip', $postData['cip_code'] );

		// From Culqi
		$culqiOrder = Orders::getInstance()->afterConfirm( $postData['id'] );

		if ( ! $culqiOrder->success ) {

			$error = \sprintf(
				/* translators: %s: Multipayment Error */
				\esc_html__( 'Culqi Multipayment Error: %s', 'fullculqi' ), $culqiOrder->data->message
			);

			self::$log->set_notice( $error );

			return false;
		}

		$culqiOrderID = $culqiOrder->data->culqiOrderID;
		$postOrderID  = $culqiOrder->data->postOrderID;

		
		$notice = \sprintf(
			/* translators: %s: Multipayment Post */
			\esc_html__( 'Post Multipayment Created: %s', 'fullculqi' ), $postOrderID
		);
		self::$log->set_notice( $notice );

		// Update WC Order IN in Culqi Orders
		\update_post_meta( $postOrderID, 'culqi_wc_order_id', $order->get_id() );

		// Update meta post in wc order
		$order->update_meta_data( '_post_order_id', $postOrderID );
		$order->update_meta_data( '_culqi_order_id', $culqiOrderID );
		$order->save_meta_data();

		return true;
	}

	/**
	 * Create Charge
	 * @param  array  $postData
	 * @return bool
	 */
	public static function charge( array $postData ): bool {

		// Settings WC
		$method = \get_option( 'woocommerce_fullculqi_settings' );

		if ( empty( $method ) ) {
			return false;
		}		

		// Get WC Order
		$order        = \wc_get_order( \absint( $postData['order_id'] ) );
		$installments = \sanitize_text_field( $postData['installments'] );
		$countryCode  = \sanitize_text_field( $postData['country_code'] );
		$token        = \sanitize_text_field( $postData['token_id'] ?? '' );

		if ( ! $order instanceof \WC_Order ) {
			return false;
		}

		// If the order is already in the success status, return true
		if ( $method['status_success'] === $order->get_status() ) {
			return true;
		}

		// Instance Logs
		self::$log = new FullCulqi_Logs( $order->get_id() );

		// Process Customer
		self::customer( $order );

		// If the user is logged
		if ( Customers::getInstance()->haveCurrentItemIDs() ) {

			$culqiCustomerID = Customers::getInstance()->getCurrentItemID( 'culqi_customer_id' );
			$postCustomerID  = Customers::getInstance()->getCurrentItemID( 'post_customer_id' );

			// Create Card
			if ( ! empty( $culqiCustomerID ) && ! \fullculqi_is_token_yape( $token ) ) {

				if ( isset( $postData['card_id'] ) ) {

					$token = \sanitize_text_field( $postData['card_id'] );

					$card = Cards::getInstance()->get( $token );

					\do_action( 'fullculqi/wc/charge/card', $card->data, $order );
				
				} else {

					$args = [
						'customer_id' => $culqiCustomerID,
						'token_id'    => $token,
						'validate'    => true,
						'metadata'    => [
							'culqi_customer_id' => $culqiCustomerID,
							'post_customer_id'  => $postCustomerID,
						]
					];


					if ( ! empty( $postData['device'] ) ) {
						$args['device_finger_print_id'] = $postData['device'];
					}

					// params 3Ds
					if ( ! empty( $postData['params3DS'] ) ) {
						$args['authentication_3DS'] = $postData['params3DS'];
					}

					$card = Cards::getInstance()->create( $args );

					\do_action( 'fullculqi/wc/charge/card', $card->data, $order );

					if ( ! $card->success ) {

						$error = \sprintf(
							/* translators: %s: Culqi Card Error */
							\esc_html__( 'Culqi Card Error: %s', 'fullculqi' ),
							$card->data->message
						);
						self::$log->set_error( $error );

						return false;
					}


					// If it needs 3Ds
					if ( $card->data->needs3Ds ) {
						$order->update_meta_data( '_culqi_needs3Ds', true );
						$order->save_meta_data();
						
						self::$log->set_notice(
							\esc_html__( 'The card needs 3Ds validation', 'fullculqi' )
						);
						
						return true;
					}

					// Remove needs3Ds
					$order->delete_meta_data( '_culqi_needs3Ds' );
					$order->save_meta_data();

					// Set CardID
					$token = $card->data->culqiCardID;
				}
			}
		}

		if ( ! isset( $token ) ) {
			return false;
		}

		if ( \apply_filters( 'fullculqi/wc/charge/is_new', false, $order ) ) {
			
			return \apply_filters( 'fullculqi/wc/charge/create', false, $order );
		
		} else {

			// Is order payment type
			$order->update_meta_data( '_culqi_payment_type', 'charge' );

			// Charges
			$pnames = [];

			foreach ( $order->get_items() as $item ) {
				$product = $item->get_product();

				if ( $product && method_exists( $product, 'get_name' ) ) {
					$pnames[] = $product->get_name();
				}
			}

			$desc = \count( $pnames ) == 0 ? 'Product' : \implode( ', ', $pnames );
			
			// Antifraud Customer Data
			$antifraud = [ 'email' => $order->get_billing_email() ];

			$billing_first_name = $order->get_billing_first_name();
			$billing_last_name  = $order->get_billing_last_name();
			$billing_address_1  = $order->get_billing_address_1();
			$billing_phone      = $order->get_billing_phone();
			$billing_city       = $order->get_billing_city();
			$billing_country    = $order->get_billing_country();

			if( ! empty( $billing_first_name ) ) {
				$antifraud['first_name'] = $billing_first_name;
			}

			if( ! empty( $billing_last_name ) ) {
				$antifraud['last_name'] = $billing_last_name;
			}

			if( ! empty( $billing_address_1 ) ) {
				$antifraud['address'] = $billing_address_1;
			}

			if( ! empty( $billing_city ) ) {
				$antifraud['address_city'] = $billing_city;
			}

			if( ! empty( $billing_country ) ) {
				$antifraud['country_code'] = $billing_country;
			}
			elseif( ! empty($country_code) ) {
				$antifraud['country_code'] = $country_code;
			}

			if( ! empty( $billing_phone ) ) {
				$antifraud['phone_number'] = $billing_phone;
			}

			if( ! empty( $postData['device'] ) ) {
				$antifraud['device_finger_print_id'] = $postData['device'];
			}


			// Metadata Order
			$metadata = [
				'wc_order_id'        => $order->get_id(),
				'wc_order_number'    => $order->get_order_number(),
				'wc_order_key'       => $order->get_order_key(),
				'wc_order_email'     => $order->get_billing_email(),
				'wc_order_firstname' => $order->get_billing_first_name(),
				'wc_order_lastname'  => $order->get_billing_last_name(),
				'wc_order_country'   => $order->get_billing_country(),
				'wc_order_city'      => $order->get_billing_city(),
				'wc_order_phone'     => $order->get_billing_phone(),
				'post_customer_id'   => $postCustomerID ?? null,
				'culqi_customer_id'  => $culqiCustomerID ?? null,
			];

			$args = [
				'amount'			=> \fullculqi_format_total( $order->get_total() ),
				'currency_code'		=> $order->get_currency(),
				'description'		=> \substr( \str_pad( $desc, 5, '_' ), 0, 80 ),
				'capture'			=> true,
				'email'				=> $order->get_billing_email(),
				'installments'		=> $installments,
				'source_id'			=> $token,
				'metadata'			=> $metadata,
				'antifraud_details'	=> $antifraud,
			];

			// params 3Ds
			if( ! empty( $postData['params3DS'] ) ) {
				$args['authentication_3DS'] = $postData['params3DS'];
			}

			$args   = \apply_filters( 'fullculqi/process/charge_args', $args, $order );
			$charge = Charges::getInstance()->create( $args );

			if ( ! $charge->success ) {

				$error = \sprintf(
					/* translators: %s: Culqi Charge Error */
					\esc_html__( 'Culqi Charge Error: %s', 'fullculqi' ),
					$charge->data->message
				);

				self::$log->set_notice( $error );

				return false;
			}


			// If it needs 3Ds
			if ( $charge->data->needs3Ds ) {

				$order->update_meta_data( '_culqi_needs3Ds', true );
				$order->save_meta_data();
				
				self::$log->set_notice(
					\esc_html__( 'The charge needs 3Ds validation', 'fullculqi' )
				);

				return true;
			}


			// Remove needs3Ds
			$order->delete_meta_data( '_culqi_needs3Ds' );

			
			$culqiChargeID = $charge->data->culqiChargeID;
			$postChargeID  = $charge->data->postChargeID;

			// Meta value
			$order->update_meta_data( '_culqi_charge_id', $culqiChargeID );
			$order->update_meta_data( '_post_charge_id', $postChargeID );

			$notice = \sprintf(
				/* translators: %s: Culqi Charge Created */
				\esc_html__( 'Culqi Charge Created: %s', 'fullculqi' ),
				$culqiChargeID
			);

			$order->add_order_note( $notice );
			self::$log->set_notice( $notice );

			$notice = \sprintf(
				/* translators: %s: Culqi Charge Created */
				\esc_html__( 'Post Charge Created: %s', 'fullculqi' ), $postChargeID
			);
			self::$log->set_notice( $notice );

			// Update OrderID in CulqiCharges
			\update_post_meta( $postChargeID, 'culqi_wc_order_id', $order->get_id() );

			$status = \apply_filters( 'fullculqi/process/change_status', [
				'name'	=> $method['status_success'],
				'note'	=> \sprintf(
					/* translators: %s: new WC Status */
					\esc_html__( 'Status changed by FullCulqi (to %s)', 'fullculqi' ),
					$method['status_success']
				),
			], $order );

			// Change Status
			$order->update_status( $status['name'], $status['note'] );
			$order->save_meta_data();
		}

		\do_action( 'fullculqi/process/charge_success', $order );

		return true;
	}


	/**
	 * Create Customer
	 * @param  WP_OBJECT $order
	 * @return mixed
	 */
	public static function customer( \WC_Order $order ) {

		// If logged
		if ( \is_user_logged_in() ) {
			$customer = Customers::getInstance()->get( get_current_user_id() );
		}

		// If exist the order email
		if ( ! $customer->success ) {
			$customer = Customers::getInstance()->getByEmail( $order->get_billing_email() );
		}
		

		if ( $customer->success ) {

			$notice = sprintf(
				/* translators: %s: Culqi Customer ID */
				esc_html__( 'Culqi Customer: %s', 'fullculqi' ), $customer->data->culqiCustomerID
			);
			self::$log->set_notice( $notice );

			$notice = sprintf(
				/* translators: %s: Culqi Customer Post */
				esc_html__( 'Post Customer: %s', 'fullculqi' ), $customer->data->postCustomerID
			);
			self::$log->set_notice( $notice );

			// Update meta post in wc order
			$order->update_meta_data( '_culqi_customer_id', $customer->data->culqiCustomerID );
			$order->update_meta_data( '_post_customer_id', $customer->data->postCustomerID );
			$order->save_meta_data();

			Customers::getInstance()->setCurrentItemIDs( [
				'culqi_customer_id' => $customer->data->culqiCustomerID,
				'post_customer_id'  => $customer->data->postCustomerID,
			] );

			return true;
		}


		if ( ! is_user_logged_in() ) {
			return false;
		}

		$args = [
			'email'    => $order->get_billing_email(),
			'metadata' => [ 'user_id' => get_current_user_id() ],
		];

		$billing_first_name = $order->get_billing_first_name();
		$billing_last_name  = $order->get_billing_last_name();
		$billing_phone      = $order->get_billing_phone();
		$billing_address_1  = $order->get_billing_address_1();
		$billing_city       = $order->get_billing_city();
		$billing_country    = $order->get_billing_country();

		if ( ! empty( $billing_first_name ) ) {
			$args['first_name'] = $billing_first_name;
		}

		if ( ! empty( $billing_last_name ) ) {
			$args['last_name'] = $billing_last_name;
		}

		if ( ! empty( $billing_phone ) ) {
			$args['phone_number'] = $billing_phone;
		}

		if ( ! empty( $billing_address_1 ) ) {
			$args['address'] = $billing_address_1;
		}

		if ( ! empty( $billing_city ) ) {
			$args['address_city'] = $billing_city;
		}

		if ( ! empty( $billing_country ) ) {
			$args['country_code'] = $billing_country;
		}

		$args   = apply_filters( 'fullculqi/process/customer_args', $args, $order );

		$customer = Customers::getInstance()->create(
			get_current_user_id(), $args
		);


		// Error
		if( ! $customer->success ) {
			
			$error = sprintf(
				/* translators: %s: Culqi Customer Error */
				esc_html__( 'Culqi Customer Error: %s', 'fullculqi' ),
				$customer->data->message
			);
			self::$log->set_error( $error );

			return false;
		}


		$culqiCustomerID = $customer->data->culqiCustomerID;
		$postCustomerID  = $customer->data->postCustomerID;


		$notice = sprintf(
			/* translators: %s: Culqi Customer Created */
			esc_html__( 'Culqi Customer Created: %s', 'fullculqi' ), $culqiCustomerID
		);
		self::$log->set_notice( $notice );

		// Update meta culqi id in wc order
		//update_post_meta( $order->get_id(), '_culqi_customer_id', $culqiCustomerID );
		$order->update_meta_data( '_culqi_customer_id', $culqiCustomerID );

		
		$notice = sprintf(
			/* translators: %s: Culqi Customer Created */
			esc_html__( 'Post Customer Created: %s', 'fullculqi' ), $postCustomerID
		);
		self::$log->set_notice( $notice );

		// Update meta post in wc order
		$order->update_meta_data( '_post_customer_id', $postCustomerID );
		$order->save_meta_data();

		Customers::getInstance()->setCurrentItemIDs( [
			'culqi_customer_id' => $culqiCustomerID,
			'post_customer_id'  => $postCustomerID,
		] );

		return true;
	}

}