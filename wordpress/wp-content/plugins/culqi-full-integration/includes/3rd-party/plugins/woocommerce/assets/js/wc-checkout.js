(function ($) {

	const FullCulqi = {

		/**
		 * Device
		 * @type string
		 */
		deviceFingerPrintID : null,

		/**
		 * Start the engine.
		 *
		 * @since 2.0.0
		 */
		init: function () {

			// Document ready
			$(document).ready(FullCulqi.ready);

			// Page load
			$(window).on('load', FullCulqi.load);
		},
		/**
		 * Document ready.
		 *
		 * @since 2.0.0
		 */
		ready: function () {

			// Execute
			FullCulqi.executeUIActions();
		},
		/**
		 * Page load.
		 *
		 * @since 2.0.0
		 */
		load: function () {

			// Bind all actions.
			FullCulqi.bindUIActions();
		},

		/**
		 * Execute when the page is ready
		 * @return mixed
		 */
		executeUIActions: function() {

			FullCulqi.setCredentials();
			FullCulqi.setDevice();
			FullCulqi.setSettings();
			FullCulqi.setOptions();
			FullCulqi.timeModal();
		},

		/**
		 * Element bindings.
		 *
		 * @since 2.0.0
		 */
		bindUIActions: function () {
			
			$('#fullculqi_button').on('click', function (e) {
				e.preventDefault();
				FullCulqi.openModal();
			});


			window.addEventListener("message", async function (event) {

				if ( event.origin === window.location.origin ) {

					const { loading, parameters3DS, error } = event.data;

					if ( parameters3DS ) {
						FullCulqi.payProcess( parameters3DS );
					}

					if ( error ) {
						console.log( error );
					}
				}
			}, false );

		},
		/**
		 * Check if the browser is Safari
		 * @return bool
		 */
		isSafari: function() {
			return /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
		},
		/**
		 * Set Culqi Credentials
		 * @return mixed
		 */
		setCredentials: function() {
			Culqi.publicKey    = fullculqi_vars.public_key;
			Culqi3DS.publicKey = fullculqi_vars.public_key;
		},
		/**
		 * Set Culqi Credentials
		 * @return mixed
		 */
		setDevice: async function() {
			FullCulqi.deviceFingerPrintID = await Culqi3DS.generateDevice();
		},
		/**
		 * Set Culqi Settings
		 * @return mixed
		 */
		setSettings: function() {

			let args_settings = {
				title: fullculqi_vars.commerce,
				currency: fullculqi_vars.currency,
				description: fullculqi_vars.description,
				amount: fullculqi_vars.total
			};

			if( fullculqi_vars.multipayment.length > 0 && fullculqi_vars.multi_order != '' ) {
				args_settings.order = fullculqi_vars.multi_order;
			}

			Culqi.settings( args_settings );
		},
		/**
		 * Set Culqi Options
		 * @return mixed
		 */
		setOptions: function() {

			let args_options = {
				'lang'				: fullculqi_vars.lang,
				'installments'		: fullculqi_vars.installments == 'yes' ? true : false
			};
			

			// Check the logo option
			if( fullculqi_vars.url_logo.length > 0 ) {
				args_options.style = { logo : fullculqi_vars.url_logo };	
			}

			// Multipayments
			args_options.paymentMethods = {
				'tarjeta'		: true,
				'yape'			: $.inArray( 'yape', fullculqi_vars.multipayment ) !== -1 ? true : false,
				'bancaMovil'	: $.inArray( 'bancaMovil', fullculqi_vars.multipayment ) !== -1 ? true : false,
				'agente'		: $.inArray( 'agente', fullculqi_vars.multipayment ) !== -1 ? true : false,
				'billetera'		: $.inArray( 'billetera', fullculqi_vars.multipayment ) !== -1 ? true : false,
				'cuotealo'		: $.inArray( 'cuotealo', fullculqi_vars.multipayment ) !== -1 ? true : false,
			};

			// Check if there is options
			if( Object.keys( args_options ).length > 0 ) {
				Culqi.options( args_options );
			}

			// Culqi 3Ds
			Culqi3DS.options = {
				showModal: true,
				showLoading: true,
				showIcon: true,
				closeModalAction: () => window.location.reload(true),
			};
		},
		/**
		 * Time to open modal
		 * @return mixed
		 */
		timeModal: function() {
			if( ! FullCulqi.isSafari() && fullculqi_vars.time_modal > 0 ) {
				setTimeout(function() {
					//$('#fullculqi_button').trigger('click');
					FullCulqi.openModal();
				}, fullculqi_vars.time_modal);
			}
		},
		/**
		 * Open Modal
		 * @return mixed
		 */
		openModal: function() {
			Culqi.open();
			$('#culqi_notify').empty();
		},
		/**
		 * waitMe to Ajax
		 * @return mixed
		 */
		waitMe: function() {

			// Ajax Start
			$( document ).ajaxStart( function() {
				$('#fullculqi_notify').removeClass('woocommerce-error').empty();
				
				$('#fullculqi_receipt_page').waitMe({
					effect		: 'pulse',
					text 		: fullculqi_vars.loading_text,
					bg			: 'rgba(255,255,255,0.7)',
					color		: '#000000',
					maxSize		: '',
					waitTime	: -1,
					textPos		: 'vertical',
					fontSize	: '',
					source		: '',
					onClose : function() {},
				});
			});

			// Ajax Complete
			$( document ).ajaxComplete( function() {
				$('#fullculqi_receipt_page').waitMe('hide');
			});
		},
		/**
		 * Pay Process
		 * @return mixed
		 */
		payProcess: function( params3DS = null ) {

			if( Culqi.error ) {

				$('#fullculqi_notify').addClass('woocommerce-error').html( Culqi.error.user_message );
			
			} else {

				FullCulqi.waitMe();

				let data;

				if ( Culqi.order ) {

					data = {
						action 		: 'order',
						id 			: Culqi.order.id,
						cip_code	: Culqi.order.payment_code,
						order_id	: Culqi.order.order_number,
						device 		: FullCulqi.deviceFingerPrintID,
						wpnonce		: fullculqi_vars.wpnonce
					};

				} else if( Culqi.token ) {

					data = {
						action 			: 'charge',
						token_id		: Culqi.token.id,
						order_id 		: fullculqi_vars.order_id,
						country_code	: Culqi.token.client?.ip_country_code || 'PE',
						installments	: Culqi.token.metadata?.installments || 0,
						device 			: FullCulqi.deviceFingerPrintID,
						wpnonce			: fullculqi_vars.wpnonce
					};
				}


				if( params3DS != null ) {
					data.params3DS = params3DS;
				}

				FullCulqi.loadAjax( data );
			}
		},


		/**
		 * Load to Ajax
		 * @param  objet post_data
		 * @return mixed
		 */
		loadAjax: function( post_data ) {

			$.ajax({
				url 		: fullculqi_vars.url_actions,
				type 		: 'POST',
				dataType	: 'json',
				data 		: post_data,
				
				success: function( response ) {

					$( document.body ).trigger( 'fullculqi.checkout.success', [ post_data, response ] );
					
					if ( response.success ) {

						if ( response.data.needs3Ds ) {

							Culqi.close();

							Culqi3DS.settings = {
								charge: {
									currency: fullculqi_vars.currency,
									totalAmount: fullculqi_vars.total,
									returnUrl: fullculqi_vars.url_payment
								},
								card: {
									email: Culqi.token.email,
								}
							};

							Culqi3DS.initAuthentication( Culqi.token.id );

						} else {

							$('#fullculqi_notify').empty();

							if( post_data.action == 'order' ) {
								const interval = setInterval( function() {
									if( ! Culqi.isOpen ){
										location.href = fullculqi_vars.url_success;
										clearInterval( interval );
									}
								}, 1000 );
							} else {
								location.href = fullculqi_vars.url_success;
							}
						}

					} else {

						$('#fullculqi_notify').addClass('woocommerce-error').html( fullculqi_vars.msg_fail );
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					
					console.log(jqXHR);
					console.log(textStatus);
					console.log(errorThrown);
					
					$('#fullculqi_notify').empty();
					$('#fullculqi_notify').addClass('woocommerce-error').html( fullculqi_vars.msg_error );

					$( document.body ).trigger('fullculqi.checkout.error', [ post_data, jqXHR, textStatus, errorThrown ] );
				}
			});
		}
	};

	FullCulqi.init();
	// Add to global scope.
	window.fullculqi = FullCulqi;

	//window.load = FullCulqi.load();
	//window.culqi = culqi;
})(jQuery);


window.culqi = function () {
	window.fullculqi.payProcess();
}