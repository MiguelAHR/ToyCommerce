<?php if( ! empty( $qr ) || ! empty( $cip ) ) : ?>

	<div class="culqi_code_box">
		<div class="metabox_column_container">

			<?php if( ! empty( $cip ) ) : ?>
				<h2 class="metabox_h2">
					<?php esc_html_e( 'CIP Code', 'fullculqi' ); ?>
				</h2>

				<h3 class="metabox_warnings_msg"><?php echo esc_html( $cip ); ?></h3>

				<hr />
			<?php endif; ?>

			<?php if( ! empty( $qr ) ) : ?>
				<h2 class="metabox_h2">
					<?php esc_html_e( 'QR Code', 'fullculqi' ); ?>
				</h2>
			
				<div class="metabox_img_fluid"><img src="<?php echo esc_url( $qr ); ?>" alt="qr code" /></div>
			<?php endif; ?>
			
		</div>
	</div>

<?php endif; ?>