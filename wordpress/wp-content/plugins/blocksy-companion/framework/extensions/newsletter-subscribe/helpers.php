<?php

if (! defined('ABSPATH')) {
	exit;
}

function blocksy_companion_ext_newsletter_subscribe_form() {
	if (blocksy_companion_theme_functions()->blocksy_get_theme_mod('newsletter_subscribe_single_post_enabled', 'yes') !== 'yes') {
		return '';
	}

	if (
		blocksy_companion_akg(
			'disable_subscribe_form',
			blocksy_companion_theme_functions()->blocksy_get_post_options(),
			'no'
		) === 'yes'
	) {
		return '';
	}

	$args = [
		'title' => blocksy_companion_theme_functions()->blocksy_get_theme_mod(
			'newsletter_subscribe_title',
			__('Newsletter Updates', 'blocksy-companion')
		),

		'description' => blocksy_companion_theme_functions()->blocksy_get_theme_mod('newsletter_subscribe_text', __(
			'Enter your email address below and subscribe to our newsletter',
			'blocksy-companion'
		)),

		'button_text' => blocksy_companion_theme_functions()->blocksy_get_theme_mod(
			'newsletter_subscribe_button_text',
			__('Subscribe', 'blocksy-companion')
		),
		'has_name' => blocksy_companion_theme_functions()->blocksy_get_theme_mod('has_newsletter_subscribe_name', 'no'),
		'name_required' => blocksy_companion_theme_functions()->blocksy_get_theme_mod('newsletter_subscribe_name_required', 'no'),
		'name_label' => blocksy_companion_theme_functions()->blocksy_get_theme_mod(
			'newsletter_subscribe_name_label',
			__('Your name', 'blocksy-companion')
		),
		'email_label' => blocksy_companion_theme_functions()->blocksy_get_theme_mod(
			'newsletter_subscribe_mail_label',
			__('Your email', 'blocksy-companion')
		)
	];

	$list_id = null;

	if (blocksy_companion_theme_functions()->blocksy_get_theme_mod(
		'newsletter_subscribe_list_id_source',
		'default'
	) === 'custom') {
		$args['list_id'] = blocksy_companion_theme_functions()->blocksy_get_theme_mod('newsletter_subscribe_list_id', '');
	}

	$args['class'] = 'ct-newsletter-subscribe-container is-width-constrained ' . blocksy_visibility_classes(
		blocksy_companion_theme_functions()->blocksy_get_theme_mod('newsletter_subscribe_subscribe_visibility', [
			'desktop' => true,
			'tablet' => true,
			'mobile' => false,
		])
	);

	return blocksy_companion_ext_newsletter_subscribe_output_form($args);
}

function blocksy_companion_ext_newsletter_subscribe_output_form($args = []) {
	$args = wp_parse_args($args, [
		'has_title' => true,
		'has_description' => true,
		'title' => __(
			'Newsletter Updates', 'blocksy-companion'
		),
		'description' => __(
			'Enter your email address below to subscribe to our newsletter',
			'blocksy-companion'
		),
		'button_text' => __(
			'Subscribe', 'blocksy-companion'
		),
		'has_name' => 'no',
		'name_required' => 'no',
		'name_label' => __('Your name', 'blocksy-companion'),
		'email_label' => __('Your email', 'blocksy-companion'),
		'list_id' => '',
		'class' => '',

		'container_style' => 'default',
		'form_style' => 'inline'
	]);

	$has_name = $args['has_name'] === 'yes';

	$manager = \Blocksy\Extensions\NewsletterSubscribe\Provider::get_for_settings();
	$settings = $manager->get_settings();

	$list_id = $settings['list_id'];

	if (! empty($args['list_id'])) {
		$list_id = $args['list_id'];
	}

	$provider = $settings['provider'] . ':' . $list_id;

	$fields_number = '2';
	$gdpr_checkbox_id_suffix = substr(blocksy_rand_md5(), 0, 3);

	if ($has_name) {
		$fields_number = '3';
	}

	$html_args = [];
	$html_args['class'] = trim($args['class']);

	if (is_customize_preview()) {
		$html_args['data-shortcut'] = 'border';

		$prefix = blocksy_manager()->screen->get_prefix();
		$html_args['data-shortcut-location'] = blocksy_first_level_deep_link($prefix) . ':newsletter_subscribe_single_post_enabled';
	}

	ob_start();

	?>

	<div <?php blocksy_companion_attr_to_html_e($html_args) ?>>
		<?php if ($args['has_title']) { ?>
			<h3><?php echo esc_html($args['title']) ?></h3>
		<?php } ?>

		<?php if ($args['has_description'] && ! empty($args['description'])) { ?>
			<p>
				<?php echo wp_kses_post($args['description']) ?>
			</p>
		<?php } ?>

		<form action="#" method="post"
			data-provider="<?php echo esc_attr($provider) ?>"
			class="ct-newsletter-subscribe-form">

			<div
				<?php
					blocksy_companion_attr_to_html_e(
						array_merge(
							[
								'class' => trim(
									'ct-newsletter-subscribe-form-elements' . (
										$args['container_style'] === 'boxed'
											? ' ct-pseudo-input'
											: ''
									)
								),
								'data-container' => $args['container_style'],
							],
							$args['form_style'] === 'inline' ? [
								'data-columns' => $fields_number
							] : []
						)
					)
			?>>
				<?php if ($has_name) { ?>
					<input
						type="text"
						name="FNAME"
						placeholder="<?php echo esc_attr($args['name_label'], 'blocksy-companion') . ($args['name_required'] === 'yes' ? ' *' : ''); ?>"
						aria-label="<?php echo esc_attr__('First name', 'blocksy-companion') ?>"
						<?php echo ($args['name_required'] === 'yes' ? 'required' : ''); ?>
					>
				<?php } ?>

				<input type="email" name="EMAIL" placeholder="<?php echo esc_attr($args['email_label']); ?> *" aria-label="<?php echo esc_attr__('Email address', 'blocksy-companion') ?>" required>

				<button class="wp-element-button">
					<?php echo esc_html($args['button_text']) ?>
				</button>
			</div>

			<?php if (function_exists('blocksy_companion_ext_cookies_checkbox')) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo blocksy_companion_ext_cookies_checkbox(
					'subscribe',
					$gdpr_checkbox_id_suffix
				);
			} ?>

			<div class="ct-newsletter-subscribe-message"></div>
		</form>

	</div>

	<?php

	return ob_get_clean();
}
