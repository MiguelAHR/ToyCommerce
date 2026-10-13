<div class="culqi_charges_box">
	<h2 class="metabox_h2">
		<?php /* translators: %s: Culqi Charge ID */ ?>
		<?php printf( esc_html__( 'Culqi ID : %s','fullculqi'), esc_attr( $id ) ); ?>
	</h2>
	<p class="metabox_subh2">
		<?php /* translators: %1$s: Culqi Charge Date Creation */ ?>
		<?php /* translators: %2$s: Culqi Charge IP */ ?>
		<?php
			printf(
				esc_html__( 'Charge via FullCulqi. Paid on %1$s,. Customer IP: %2$s', 'fullculqi' ),
				esc_attr( $creation_date ), esc_attr( $ip )
			);
		?>
	</p>

	<div class="metabox_column_container">
		<div class="metabox_column">
			<h3 class="metabox_h3">
				<?php esc_html_e( 'Charge Data', 'fullculqi' ); ?>
			</h3>
			<ul>
				<li>
					<b><?php esc_html_e( 'Type', 'fullculqi' ); ?> : </b>
					<?php
						if( $type == 'yape' ) {
							$classType = 'is_yape';
							$labelType = esc_html__( 'Yape', 'fullculqi' );
						} else {
							$classType = '';
							$labelType = esc_html__( 'Charge', 'fullculqi' );
						}

						printf(
							'<mark class="metabox_badged %s"><span>%s</span></mark>',
							esc_attr( $classType ), esc_html( $labelType )
						);
					?>
				</li>
				<li>
					<b><?php esc_html_e( 'Creation Date', 'fullculqi' ); ?> : </b>
					<?php echo esc_html( $creation_date ); ?>
				</li>
				<li>
					<b><?php esc_html_e( 'Capture Date', 'fullculqi' ); ?> : </b>
					<?php echo esc_html( $capture_date ); ?>
				</li>
				<li>
					<b><?php esc_html_e( 'Currency', 'fullculqi' ); ?> : </b>
					<?php echo esc_html( $currency ); ?>
				</li>
				<li>
					<b><?php esc_html_e( 'Amount', 'fullculqi' ); ?> : </b>
					<?php echo esc_html( $amount ); ?>
				</li>
				<li>
					<b><?php esc_html_e( 'Current Amount', 'fullculqi' ); ?> : </b>
					<?php echo esc_html( $current_amount ); ?>
				</li>
				<li>
					<b><?php esc_html_e( 'Refund', 'fullculqi' ); ?> : </b>
					<?php echo esc_html( $refunded ); ?>
				</li>
				<?php do_action( 'fullculqi/charges/basic/print_data', $post_id ); ?>
			</ul>
			<?php
				if( ! empty( $status ) && isset( $statuses[$status] ) ) {
					printf(
						'<mark class="metabox_badged %s"><span>%s</span></mark>',
						esc_attr( $status_class ), esc_html( $statuses[$status] )
					);

					if ( $can_refund ) {
						echo '&nbsp';

						printf(
							'<a href="" id="culqi_refunds" class="metabox_simple_link" data-post="%d">%s</a>',
							esc_attr( $post_id ), esc_html__( 'Refund Charge', 'fullculqi' )
						);

						echo '&nbsp';

						echo '<span id="culqi_refunds_notify"></span>';
					}
				}
			?>
			<?php do_action( 'fullculqi/layout_basic/status' ); ?>
		</div>
		<div class="metabox_column">
			<h3 class="metabox_h3">
				<?php esc_html_e( 'Customer', 'fullculqi' ); ?>
			</h3>
			<ul>
				<li>
					<b><?php esc_html_e( 'Email', 'fullculqi' ); ?> : </b>
					<?php echo sanitize_email( $email ); ?>
				</li>
				<li>
					<b><?php esc_html_e( 'First Name', 'fullculqi' ); ?> : </b>
					<?php echo esc_html( $first_name ); ?>
				</li>
				<li>
					<b><?php esc_html_e( 'Last Name', 'fullculqi' ); ?> : </b>
					<?php echo esc_html( $last_name ); ?>
				</li>
				<li>
					<b><?php esc_html_e( 'City', 'fullculqi' ); ?> : </b>
					<?php echo esc_html( $city ); ?>
				</li>
				<li>
					<b><?php esc_html_e( 'Country', 'fullculqi' ); ?> : </b>
					<?php echo esc_html( $country ); ?>
				</li>
				<li>
					<b><?php esc_html_e( 'Phone', 'fullculqi' ); ?> : </b>
					<?php echo esc_html( $phone ); ?>
				</li>
			</ul>
		</div>
	</div>
	<div class="clear"></div>
</div>