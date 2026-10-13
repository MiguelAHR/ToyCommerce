<?php

if (! defined('ABSPATH')) {
	exit;
}

?>
<form name="lostpasswordform" id="lostpasswordform" action="#" method="post">
	<?php
	/**
	 * Fires at the start of the account modal lost password form.
	 *
	 * @since 1.7.43
	 */
	do_action('blocksy:account:modal:lostpassword:start'); ?>

	<p>
		<label for="user_login_forgot"><?php echo esc_html__('Username or Email Address', 'blocksy-companion')?></label>
		<input type="text" name="user_login" id="user_login_forgot" class="input" value="" size="20" autocomplete="username" autocapitalize="off" required>
	</p>

	<?php do_action('lostpassword_form'); ?>

	<p>
		<button class="ct-button ct-account-lost-password-submit has-text-align-center" name="wp-submit">
			<?php echo esc_html__('Get New Password', 'blocksy-companion') ?>

			<svg class="ct-button-loader" width="16" height="16" viewBox="0 0 24 24">
				<circle cx="12" cy="12" r="10" opacity="0.2" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="2.5"/>

				<path d="m12,2c5.52,0,10,4.48,10,10" fill="none" stroke="currentColor" stroke-linecap="round" stroke-miterlimit="10" stroke-width="2.5">
					<animateTransform
						attributeName="transform"
						attributeType="XML"
						type="rotate"
						dur="0.6s"
						from="0 12 12"
						to="360 12 12"
						repeatCount="indefinite"
					/>
				</path>
			</svg>
		</button>
	</p>

	<?php
	/**
	 * Fires at the end of the account modal lost password form.
	 *
	 * @since 1.7.43
	 */
	do_action('blocksy:account:modal:lostpassword:end'); ?>
</form>