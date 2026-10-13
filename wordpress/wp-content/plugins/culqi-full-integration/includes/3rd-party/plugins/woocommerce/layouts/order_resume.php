<?php if ( ! empty( $qr ) || ! empty( $cip ) ) : ?>

	<div class="culqi_code_box">
		<div class="culqi_code_box_container">

			<?php if( ! empty( $cip ) ) : ?>
				<h2><?php esc_html_e( 'CIP Code', 'fullculqi' ); ?></h2>
				<h1 style="text-align: center;"><?php echo $cip; ?></h3>

				<hr />
			<?php endif; ?>

			<?php if( ! empty( $qr ) ) : ?>
				<h2><?php esc_html_e( 'QR Code', 'fullculqi' ); ?></h2>
			
				<img src="<?php echo $qr; ?>" alt="qr code" style="width: 100%;" />
			<?php endif; ?>
			
		</div>
	</div>

<?php endif; ?>