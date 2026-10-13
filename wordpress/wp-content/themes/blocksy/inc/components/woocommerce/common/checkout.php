<?php

namespace Blocksy;

class WooCommerceCheckout {
	public function __construct() {
		add_action('wp', function () {
			if (! blocksy_get_theme_mod('blocksy_has_checkout_coupon', false)) {
				remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
			}
		});

		add_action('elementor/widget/before_render_content', function($widget) {
			if (
				(
					class_exists('Elementor\Jet_Woo_Builder_Checkout_Order_Review')
					&&
					$widget instanceof \Elementor\Jet_Woo_Builder_Checkout_Order_Review
				)
				||
				(
					class_exists('ElementorPro\Modules\Woocommerce\Widgets\Checkout')
					&&
					$widget instanceof \ElementorPro\Modules\Woocommerce\Widgets\Checkout
				)
			) {
				global $ct_skip_checkout;
				$ct_skip_checkout = true;
			}

			if (
				class_exists('ElementorPro\Modules\Woocommerce\Widgets\Checkout')
				&&
				$widget instanceof ElementorPro\Modules\Woocommerce\Widgets\Checkout
			) {
				global $ct_skip_checkout;
				$ct_skip_checkout = true;
			}
		}, 10, 1);

		add_action('woocommerce_before_checkout_form', function () {
			add_action('sellkit_checkout_one_page_express_methods', function() {
				global $ct_skip_checkout;
				$ct_skip_checkout = true;
			});

			if (function_exists('wcmultichecout_woocommerce_locate_template')) {
				global $ct_skip_checkout;
				$ct_skip_checkout = true;
			}

			add_action('kco_wc_before_wrapper', function() {
				global $ct_skip_checkout;
				$ct_skip_checkout = true;
			});

			add_action( 'wc_dibs_before_checkout_form', function() {
				global $ct_skip_checkout;
				$ct_skip_checkout = true;
			});

			if (class_exists('Svea_Checkout_For_Woocommerce\Template_Handler')) {
				global $ct_skip_checkout;
				$ct_skip_checkout = true;
			}
		}, 10, 1);

		add_action('wpfunnels/before_gb_checkout_form', function($widget) {
			global $ct_skip_checkout;
			$ct_skip_checkout = true;
		}, 10 , 1);

		add_action('cfw_checkout_main_container_start', function($widget) {
			global $ct_skip_checkout;
			$ct_skip_checkout = true;
		}, 10, 1);

		add_action('woocommerce_sco_before_checkout_page', function() {
			global $ct_skip_checkout;
			$ct_skip_checkout = true;
		});

		add_action('wp', function () {
			if (! $this->has_custom_checkout()) {
				return;
			}

			add_action('woocommerce_checkout_before_customer_details', function () {
				global $ct_skip_checkout;

				if ($ct_skip_checkout) {
					return;
				}

				echo '<div class="ct-customer-details">';
			}, PHP_INT_MIN);

			add_action('woocommerce_checkout_after_customer_details', function () {
				global $ct_skip_checkout;

				if ($ct_skip_checkout) {
					return;
				}

				echo '</div>';
			}, PHP_INT_MAX);

			add_action('woocommerce_checkout_before_order_review_heading', function () {
				global $ct_skip_checkout;

				if ($ct_skip_checkout) {
					return;
				}

				echo '<div class="ct-order-review">';
			}, PHP_INT_MIN);

			add_action('woocommerce_checkout_after_order_review', function () {
				global $ct_skip_checkout;

				if ($ct_skip_checkout) {
					return;
				}

				echo '</div>';
			}, PHP_INT_MAX);
		});

		add_action(
			'woocommerce_before_template_part',
			function ($template_name, $template_path, $located, $args) {
				if ($template_name !== 'checkout/form-checkout.php') {
					return;
				}

				ob_start();
			},
			1,
			4
		);

		add_action(
			'woocommerce_after_template_part',
			function ($template_name, $template_path, $located, $args) {
				if ($template_name !== 'checkout/form-checkout.php') {
					return;
				}

				$result = ob_get_clean();

				global $ct_skip_checkout;

				if ($this->has_custom_checkout() && ! $ct_skip_checkout) {
					$form_reader = new \WP_HTML_Tag_Processor($result);

					if (
						$form_reader->next_tag([
							'tag_name' => 'form',
							'class_name' => 'woocommerce-checkout',
						])
					) {
						$form_reader->add_class('ct-woocommerce-checkout');
						$result = $form_reader->get_updated_html();
					}
				}

				if (class_exists('Woocommerce_German_Market')) {
					$search = '/' . preg_quote('<h3 id="order_review_heading">', '/') . '/';

					$result = preg_replace(
						$search,
						'<div class="ct-order-review"><h3 id="order_review_heading">',
						$result,
						1
					);
				}

				echo $result;
			},
			1,
			4
		);

		// Empty cart on classic checkout: reload instead of WC's "session
		// expired" notice, so WC redirects to the cart page. Mirrors
		// WC_AJAX::update_order_review(). See #5649.
		add_action(
			'wc_ajax_update_order_review',
			function () {
				check_ajax_referer('update-order-review', 'security');

				if (
					! WC()->cart
					||
					! WC()->cart->is_empty()
					||
					is_customize_preview()
				) {
					return;
				}

				// Empty-cart checkout allowed by the site.
				if (! apply_filters('woocommerce_checkout_update_order_review_expired', true)) {
					return;
				}

				// No redirect after reload means an infinite reload loop.
				if (! apply_filters('woocommerce_checkout_redirect_empty_cart', true)) {
					return;
				}

				wp_send_json(['reload' => true]);
			},
			5
		);
	}

	public function has_custom_checkout() {
		$has_custom_checkout = true;

		if (class_exists('FluidCheckout')) {
			$has_custom_checkout = false;
		}

		if (class_exists('WFFN_Core')) {
			$has_custom_checkout = false;
		}

		global $post;

		if ($post && $post->post_type === 'cartflows_step') {
			$has_custom_checkout = false;
		}

		/**
		 * Filters whether Blocksy's custom checkout markup is applied.
		 *
		 * Return false to keep WooCommerce's default checkout layout, e.g.
		 * when a checkout builder plugin renders the checkout.
		 *
		 * @since 1.8.79
		 *
		 * @param bool $has_custom_checkout Whether the custom checkout markup is used.
		 */
		$has_custom_checkout = apply_filters(
			'blocksy:woocommerce:checkout:has-custom-markup',
			$has_custom_checkout
		);

		return $has_custom_checkout;
	}
}
