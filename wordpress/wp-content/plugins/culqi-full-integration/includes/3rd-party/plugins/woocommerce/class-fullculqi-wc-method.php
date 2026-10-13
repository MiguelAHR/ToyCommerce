<?php

use Fullculqi\Syncs\Charges;
use Fullculqi\Syncs\Customers;
use Fullculqi\Syncs\Refunds;
use Fullculqi\Syncs\Orders;

/**
 * Method Payment Class
 * @since  1.0.0
 * @package Includes / Method Payment
 */
class WC_Gateway_FullCulqi extends WC_Payment_Gateway {

	/**
	 * Card Installment
	 * @var string
	 */
	public string $installments;

	/**
	 * Multipayment
	 * @var array
	 */
	public array $multipayment;

	/**
	 * Time duration of the CIP
	 * @var int
	 */
	public int $multi_duration;


	/**
	 * WC Status to the Culqi Multipayment
	 * @var string
	 */
	public string $multi_status;


	/**
	 * Failed message to Customer
	 * @var string
	 */
	public string $msg_fail;

	/**
	 * Time to the modal appears
	 * @var int
	 */
	public int $time_modal;


	/**
	 * Instruction to payment
	 * @var string
	 */
	public string $instructions;

	/**
	 * Construct
	 */
	public function __construct() {

		$this->id 					= 'fullculqi';
		$this->method_title			= esc_html__( 'Culqi Integration Popup', 'fullculqi' );
		$this->method_description 	= esc_html__( 'Culqi Integration allows use the popup Culqi to enter the credit card and have a safe purchase.', 'fullculqi' );
		$this->icon 				= FULLCULQI_WC_URL . 'assets/images/cards.png';
		
		// Define user set variables
		$this->has_fields		= apply_filters( 'fullculqi/method/has_fields', false );
		$this->title			= $this->get_option( 'title' );
		$this->installments 	= $this->get_option( 'installments', 'no' );
		$multipayment 	        = $this->get_option( 'multipayment', [] );
		$this->multipayment 	= \is_array( $multipayment ) ? $multipayment : [];
		$this->multi_duration	= $this->get_option( 'multi_duration', 24 );
		$this->multi_status		= $this->get_option( 'multi_status', 'wc-pending' );
		$this->description		= $this->get_option( 'description' );
		$this->instructions		= $this->get_option( 'instructions', $this->description );
		$this->msg_fail			= $this->get_option( 'msg_fail' );
		$this->time_modal		= $this->get_option( 'time_modal', 0 );

		$this->supports = apply_filters('fullculqi/method/supports',
			[ 'products', 'refunds', 'pre-orders' ]
		);

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Actions
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
		add_action( 'woocommerce_receipt_' . $this->id, [ $this, 'receipt_page' ] );
		add_action( 'woocommerce_thankyou_' . $this->id, [ $this, 'thankyou_page' ] );

		// Script JS && CSS
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}


	/**
	 * Script JS && CSS
	 * @return [type] [description]
	 */
	public function enqueue_scripts() {
		
		// Check if it is /checkout/pay page
		if ( is_checkout_pay_page() ) {
			global $wp;

			if( ! isset( $wp->query_vars['order-pay'] ) )
				return;

			$pnames  = [];
			$orderID = $wp->query_vars['order-pay'];
			$order   = new WC_Order( $orderID );

			if( ! $order instanceof WC_Order ) {
				return;
			}

			// Log
			$log = new FullCulqi_Logs( $order->get_id() );

			$settings = fullculqi_get_settings();

			// Search multipayment
			$hasMultipayment = count( $this->multipayment ) > 0;

			// Disabled from thirds
			$enableMultipayment = apply_filters( 'fullculqi/method/disabled_multipayments', false, $order, 'order') ? 'no' : wc_bool_to_string( $hasMultipayment );

			$this->installments = apply_filters( 'fullculqi/method/disabled_installments', false, $order, 'order') ? 'no' : $this->installments;


			// Description
			$pnames = [];

			foreach ( $order->get_items() as $item ) {
				$product  = $item->get_product();
				$pnames[] = $product->get_name() ?: '';
			}

			$desc = count( array_filter( $pnames ) ) == 0 ? 'Product' : implode( ', ', $pnames );

			// Check if there is multipayment
			if ( $enableMultipayment == 'yes' ) {

				$culqiOrderID = $order->get_meta( '_culqi_order_id' );

				if ( empty( $culqiOrderID ) ) {

					// Antifraud Customer Data
					$client = [ 'email' => $order->get_billing_email() ];

					$billing_first_name = $order->get_billing_first_name();
					$billing_last_name  = $order->get_billing_last_name();
					$billing_phone      = $order->get_billing_phone();

					if ( ! empty( $billing_first_name ) ) {
						$client['first_name'] = $billing_first_name;
					}

					if ( ! empty( $billing_last_name ) ) {
						$client['last_name'] = $billing_last_name;
					}

					if ( ! empty( $billing_phone ) ) {
						$client['phone_number'] = $billing_phone;
					}

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
					];

					// Customer
					if ( \is_user_logged_in() ) {
						$customer = Customers::getInstance()->get( get_current_user_id() );

						if ( $customer->success ) {
							$metadata['culqi_customer_id'] = $customer->data->culqiCustomerID;
							$metadata['post_customer_id']  = $customer->data->postCustomerID;
						}
					}

					$args = apply_filters( 'fullculqi/orders/create/args', [
						'amount'			=> fullculqi_format_total( $order->get_total() ),
						'currency_code'		=> $order->get_currency(),
						'description'		=> substr( str_pad( $desc, 5, '_' ), 0, 80 ),
						'order_number'		=> $order->get_order_number(),
						'client_details'	=> $client,
						'confirm'			=> false,
						'expiration_date'	=> time() + ( $this->multi_duration * HOUR_IN_SECONDS ),
						'metadata'			=> $metadata,
					], $order);

					$culqiOrder = Orders::getInstance()->create( $args );

					if ( $culqiOrder->success ) {
						$culqiOrderID = $culqiOrder->data->culqiOrderID;

						// Save meta order
						$order->update_meta_data( '_culqi_order_id', $culqiOrderID );
						$order->save_meta_data();

					} else {
						$error = sprintf(
							/* translators: %s: Culqi Multipayment Error */
							esc_html__( 'Culqi Multipayment Error: %s', 'fullculqi' ),
							$culqiOrder->data->message
						);
						$log->set_notice( $error );
					}
				}
			}

		
			$js_3ds      = 'https://3ds.culqi.com';
			$js_library  = 'https://checkout.culqi.com/js/v4';
			$js_checkout = FULLCULQI_WC_URL . 'assets/js/wc-checkout.js';
			$js_waitme   = FULLCULQI_WC_URL . 'assets/js/waitMe.min.js';
			$css_waitme  = FULLCULQI_WC_URL . 'assets/css/waitMe.min.css';

			wp_enqueue_script( 'culqi-3ds-js', $js_3ds, [ 'jquery' ], false, true );
			wp_enqueue_script( 'culqi-library-js', $js_library, [ 'jquery' ], false, true );
			wp_enqueue_script(
				'fullculqi-js', $js_checkout,
				[ 'jquery', 'culqi-library-js', 'culqi-3ds-js' ],
				false, true
			);

			// Waitme
			wp_enqueue_script( 'waitme-js', $js_waitme, [ 'jquery' ], false, true );
			wp_enqueue_style( 'waitme-css', $css_waitme );

			wp_localize_script( 'fullculqi-js', 'fullculqi_vars',
				apply_filters('fullculqi/method/localize', [
					'url_actions'  => site_url( 'fullculqi-api/wc-actions/' ),
					'url_success'  => $order->get_checkout_order_received_url(),
					'url_payment'  => $order->get_checkout_payment_url( true ),
					'public_key'   => sanitize_text_field( $settings['public_key'] ),
					'installments' => sanitize_title( $this->installments ),
					'multipayment' => $enableMultipayment == 'yes' ? $this->multipayment : [],
					'multi_order'  => $enableMultipayment == 'yes' ? $culqiOrderID : '',
					'lang'         => fullculqi_language(),
					'time_modal'   => absint( $this->time_modal*1000 ),
					'order_id'     => $order->get_id(),
					'user_email'   => $order->get_billing_email(),
					'commerce'     => sanitize_text_field( $settings['commerce'] ),
					'url_logo'     => esc_url( $settings['logo_url'] ),
					'currency'     => get_woocommerce_currency(),
					'description'  => substr( str_pad( $desc, 5, '_' ), 0, 80 ),
					'loading_text' => esc_html__( 'Loading. Please wait.', 'fullculqi' ),
					'total'        => fullculqi_format_total( $order->get_total() ),
					'msg_fail'     => sanitize_text_field( $this->msg_fail ),
					'msg_error'    => esc_html__( 'There was some problem in the purchase process. Try again please', 'fullculqi' ),
					'wpnonce'		=> wp_create_nonce( 'fullculqi' ),
				], $order )
			);

			do_action( 'fullculqi/method/enqueue_scripts/pay_page', $order );
		}

		do_action( 'fullculqi/method/enqueue_scripts/after', $this );
	}
	

	/**
	 * Fields Form
	 * @return mixed
	 */
	public function init_form_fields() {

		$this->form_fields = apply_filters( 'fullculqi/method/form_fields', [
			'basic_section' => [
				'title' => esc_html__( 'BASIC SETTING', 'fullculqi' ),
				'type'  => 'title'
			],
			'enabled' => [
				'title'		=> esc_html__( 'Enable/Disable', 'fullculqi' ),
				'type'		=> 'checkbox',
				'label'		=> esc_html__( 'Enable Culqi', 'fullculqi' ),
				'default'	=> 'no',
			],
			'installments' => [
				'title'			=> esc_html__( 'Installments', 'fullculqi' ),
				'description'	=> esc_html__( 'If checked, a selection field will appear in the modal with the available installments.', 'fullculqi' ),
				'class'			=> '',
				'type'			=> 'checkbox',
				'label'			=> esc_html__( 'Enable Installments', 'fullculqi' ),
				'default'		=> 'no',
				'desc_tip'		=> true,
			],
			'title' => [
				'title'			=> esc_html__( 'Title', 'fullculqi' ),
				'type'			=> 'text',
				'description'	=> esc_html__( 'This controls the title which the user sees during checkout.', 'fullculqi' ),
				'desc_tip'		=> true,
				'default'		=> esc_html__( 'Culqi Popup', 'fullculqi' ),
			],
			'description' => [
				'title'			=> esc_html__( 'Description', 'fullculqi' ),
				'description'	=> esc_html__( 'Brief description of the payment gateway. This message will be seen by the buyer', 'fullculqi' ),
				'class'			=> '',
				'default'		=> esc_html__( 'Payment gateway Culqi accepts VISA, Mastercard, Diners, American Express', 'fullculqi' ),
				'type'			=> 'textarea',
				'desc_tip'		=> true,
			],
			'multi_section' => [
				'title'			=> esc_html__( 'MULTIPAYMENT SETTING', 'fullculqi' ),
				'type'			=> 'title',
				'description'	=> apply_filters( 'fullculqi/method/multi_html', '' ),
			],
			'multipayment' => [
				'title'			=> esc_html__('Multipayment', 'fullculqi'),
				'class'			=> '',
				'type'			=> 'multicheckbox',
				'label'			=> esc_html__('Enable Multipayment', 'fullculqi'),
				'options'		=> [
					'yape'			=> [
						'label' 		=> esc_html__( 'Yape', 'fullculqi' ),
						'desc_tip'		=> true,
						'description'	=> esc_html__( 'Tus clientes pueden pagar con Yape, Plin y las principales billeteras móviles del país', 'fullculqi' ),
					],
					'bancaMovil'	=> [
						'label' => esc_html__( 'Banca Movil', 'fullculqi' ),
					],
					'agente'		=> [
						'label' => esc_html__( 'Agente y bodegas', 'fullculqi' ),
					],
					'billetera'		=> [
						'label' => esc_html__( 'Billeteras Moviles', 'fullculqi' ),
					],
					'cuotealo'		=> [
						'label' 	=> esc_html__( 'Cuotealo BCP', 'fullculqi' ),
						'desc_tip'	=> true,
						'description'	=> esc_html__( 'Paga en cuotas y sin tarjetas de crédito con Cuotéalo', 'fullculqi' ),
					],
				],
				'default'		=> 'no',
				'desc_tip'		=> true,
			],
			'multi_duration' => [
				'title'			=> esc_html__( 'Duration', 'fullculqi' ),
				'description'	=> esc_html__( 'If enable Multipayment option, you must choose the order duration. This is the time you give the customer to make the payment.', 'fullculqi' ),
				'class'			=> '',
				'type'			=> 'select',
				'options'		=> [
					'1'		=> esc_html__( '1 Hour', 'fullculqi' ),
					'2'		=> esc_html__( '2 Hours', 'fullculqi' ),
					'4'		=> esc_html__( '4 Hours', 'fullculqi' ),
					'8'		=> esc_html__( '8 Hours', 'fullculqi' ),
					'12'	=> esc_html__( '12 Hours', 'fullculqi' ),
					'24'	=> esc_html__( '1 Day', 'fullculqi' ),
					'48'	=> esc_html__( '2 Days', 'fullculqi' ),
					'96'	=> esc_html__( '4 Days', 'fullculqi' ),
					'168'	=> esc_html__( '7 Days', 'fullculqi' ),
					'360'	=> esc_html__( '15 Days', 'fullculqi' ),
				],
				'default'		=> '24',
				'desc_tip'		=> true,
			],
			'multi_status' => [
				'title'			=> esc_html__( 'Status', 'fullculqi' ),
				'description'	=> esc_html__( 'If the sale is made via multipayments, you must specify the status.', 'fullculqi' ),
				'type'			=> 'select',
				'class'			=> 'wc-enhanced-select',
				'options'		=> wc_get_order_statuses(),
				'default'		=> 'wc-pending',
				'desc_tip'		=> true,
			],

			'additional_section' => [
				'title' => esc_html__( 'ADDITIONAL SETTING', 'fullculqi' ),
				'type'  => 'title'
			],

			'status_success' => [
				'title'			=> esc_html__( 'Success Status', 'fullculqi' ),
				'type'			=> 'select',
				'class'			=> 'wc-enhanced-select',
				'description'	=> esc_html__( 'If the purchase is success, apply this status to the order', 'fullculqi' ),
				'default'		=> 'wc-processing',
				'desc_tip'		=> true,
				'options'		=> wc_get_order_statuses(),
			],
			'msg_fail' => [
				'title'			=> esc_html__( 'Failed Message', 'fullculqi' ),
				'description'	=> esc_html__( 'This is the message will be shown to the customer if there is a error in the payment', 'fullculqi' ),
				'class'			=> '',
				'type'			=> 'textarea',
				'desc_tip'		=> false,
				'default'		=> esc_html__( 'Im sorry! an error occurred making the payment. A email was sent to shop manager with your information.', 'fullculqi' ),
			],
			'time_modal' => [
				'title'			=> esc_html__( 'Popup/Modal Time', 'fullculqi' ),
				'type'			=> 'text',
				'description'	=> esc_html__( 'If you want the modal window to appear after a while without clicking "buy", put the seconds here. (Warning: may it not work in Safari). If you do not want to, leave it at zero.', 'fullculqi' ),
				'default'		=> '0',
				'placeholder'	=> '0',
				'desc_tip'		=> false,
			],
		] );
	}

	/**
	 * Payment fields ( credit card form )
	 * @return mixed
	 */
	public function payment_fields() {
		if ( $this->description ) {
			echo wpautop( wptexturize( $this->description ) ); // @codingStandardsIgnoreLine.
		}

		do_action( 'fullculqi/method/payment_fields', $this );
	}

	/**
	 * Thanks You Page
	 * @param  integer $order_id
	 * @return mixed
	 */
	public function thankyou_page( $order_id = 0 ) {

		$order = new WC_Order( $order_id );
	}

	/**
	 * Payment Receipt Page
	 * @param  integer $order_id
	 * @return mixed
	 */
	public function receipt_page( $order_id = 0 ) {

		$order = new WC_Order( $order_id );	

		$args = apply_filters( 'fullculqi/receipt_page/args', [
			'src_image'		=> $this->icon,
			'url_cancel'	=> esc_url( $order->get_cancel_order_url() ),
			'order_id'		=> $order_id,
			'class_button'	=> [ 'button', 'alt' ],
		], $order );

		do_action('fullculqi/form-receipt/before', $order);

		wc_get_template(
			'layouts/checkout-receipt.php', $args, false, FULLCULQI_WC_DIR
		);

		do_action('fullculqi/form-receipt/after', $order);
	}


	/**
	 * Process Payment
	 * 
	 * @param  integer $order_id
	 * @return mixed
	 */
	public function process_payment( $order_id = 0 ) {
		$order = new WC_Order( $order_id );

		$output = [
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url( true ),
		];

		return apply_filters( 'fullculqi/method/process_payment', $output, $order, $this );
	}


	/**
	 * Can the order be refunded via Culqi?
	 * 
	 * @param  WC_Order $order Order object.
	 * @return bool
	 */
	public function can_refund_order( $order ) {

		$settings = fullculqi_get_settings();

		$hasAPI = ! empty( $settings['public_key'] ) && ! empty( $settings['secret_key'] );

		$culqiChargesID = $order->get_meta( '_culqi_charge_id' );
		$postChargeID   = $order->get_meta( '_post_charge_id' );

		return $order && $hasAPI && ! empty( $culqiChargesID ) && ! empty( $postChargeID );
	}

	/**
	 * Process a refund if supported.
	 *
	 * @param  int    $order_id Order ID.
	 * @param  float  $amount Refund amount.
	 * @param  string $reason Refund reason.
	 * @return bool|WP_Error
	 */
	public function process_refund( $orderID = 0, $amount = null, $reason = '' ) {
		$order = wc_get_order( $orderID );

		if ( ! $this->can_refund_order( $order ) ) {
			$message = esc_html__( 'The refund cannot be made from FullCulqi', 'fullculqi' );
			return new WP_Error( 'error', $message );
		}

		// Logs
		$log = new FullCulqi_Logs( $order->get_id() );

		$culqiChargeID = $order->get_meta( '_culqi_charge_id' );
		$postChargeID  = $order->get_meta( '_post_charge_id' );


		if ( empty( $culqiChargeID ) ) {
			$message = esc_html__( 'The refund cannot be made from FullCulqi', 'fullculqi' );
			return new WP_Error( 'error', $message );
		}

		$args = [
			'amount'	=> round( $amount*100, 0 ),
			'charge_id'	=> $culqiChargesID,
			'reason'	=> 'solicitud_comprador',
			'metadata'	=> [
				'post_charge_id'  => $postChargeID,
				'culqi_charge_id' => $culqiChargeID,
				'wc_order_id'     => $order->get_id(),
				'wc_order_number' => $order->get_order_number(),
				'wc_order_key'    => $order->get_order_key(),
			],
		];
		
		$refund = Refunds::getInstance()->create( $postChargeID, $args );

		if ( ! $refund->success ) {

			$error = sprintf(
				/* translators: %s: Culqi Refund Error */
				esc_html__( 'Culqi Refund Error : %s','fullculqi' ), $refund->data->message
			);

			$log->set_error( $error );

			return new WP_Error( 'error', $error );
		}

		$notice = sprintf(
			/* translators: %s: Culqi Refund Created */
			esc_html__( 'Culqi Refund created: %s', 'fullculqi' ),
			$refund->data->culqiRefundID
		);
		$order->add_order_note( $notice );
		$log->set_notice( $notice );


		$charge = Charges::getInstance()->processRefund( $postChargeID, $refund->data );

		return true;
	}

	/**
	 * Validate Fields
	 * @return bool
	 */
	public function validate_fields() {
		return apply_filters( 'fullculqi/method/validate', true, $this );
	}


	/**
	 * Create new field to settings
	 * @param  string $key
	 * @param  array  $data
	 * @return mixed
	 */
	public function generate_radio_html( $key = '', $data = [] ) {

		$field_key = $this->get_field_key( $key );
		$defaults  = [
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'radio',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => [],
			'options'           => [],
		];

		$data = wp_parse_args( $data, $defaults );
		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
				<?php echo $this->get_tooltip_html( $data ); ?>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
					
					<?php foreach ( (array) $data['options'] as $option_key => $option_value ) : ?>
						<label for="<?php echo esc_attr( $option_key ); ?>">
							<input type="radio" value="<?php echo esc_attr( $option_key ); ?>" name="<?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $option_key ); ?>" <?php checked( $this->get_option( $key ), $option_key ); ?> /><?php echo esc_attr( $option_value ); ?>
						</label>
						<br />
					<?php endforeach; ?>

					<?php echo $this->get_description_html( $data ); ?>
				</fieldset>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	/**
	 * MultiUrl Field
	 * @param  string $key
	 * @param  array  $data
	 * @return mixed
	 */
	public function generate_multiurl_html( $key = '', $data = [] ) {

		$field_key = $this->get_field_key( $key );

		ob_start();
		?>

		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
				<?php echo $this->get_tooltip_html( $data ); ?>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
					<b><?php echo site_url('wc-api/fullculqi_update_order'); ?></b>
					<?php echo $this->get_description_html( $data ); ?>
				</fieldset>
			</td>
		</tr>

		<?php
		return ob_get_clean();
	}


	/**
	 * Generate Multi Checkbox HTML.
	 *
	 * @param string $key Field key.
	 * @param array  $data Field data.
	 * @since  1.0.0
	 * @return string
	 */
	public function generate_multicheckbox_html( string $key, array $data ) {
		$field_key = $this->get_field_key( $key );
		$defaults  = array(
			'title'             => '',
			'label'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => [],
			'options'			=> [],
		);

		$data = wp_parse_args( $data, $defaults );

		if ( ! $data['label'] ) {
			$data['label'] = $data['title'];
		}

		$mutlicheckbox = $this->get_option( $key, [] );
		$mutlicheckbox = is_array( $mutlicheckbox ) ? $mutlicheckbox : [];

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?> <?php echo $this->get_tooltip_html( $data ); // WPCS: XSS ok. ?></label>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>

					<?php foreach( (array) $data['options'] as $optionKey => $optionData ) : ?>
						<label for="<?php echo esc_attr( $optionKey ); ?>">
							<input class="<?php echo esc_attr( $data['class'] ); ?>" type="checkbox" name="<?php echo esc_attr( $field_key ); ?>[<?php echo $optionKey; ?>]" id="<?php echo esc_attr( $field_key . '_' . $optionKey ); ?>" value="<?php echo $optionKey; ?>" <?php checked( in_array($optionKey, $mutlicheckbox ), true ); ?> /> <?php echo wp_kses_post( $optionData['label'] ); ?>
							<?php echo $this->get_tooltip_html( $data ); ?>
						</label><br/>
					<?php endforeach; ?>
					
				</fieldset>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}


	/**
	 * Validate Checkbox Field.
	 *
	 * If not set, return "no", otherwise return "yes".
	 *
	 * @param  string $key Field key.
	 * @param  array $value Posted Value.
	 * @return string
	 */
	public function validate_multicheckbox_field( string $key, ?array $value ) {


		return ! empty( $value ) ? array_values( array_filter( $value ) ) : [];
	}
}

?>