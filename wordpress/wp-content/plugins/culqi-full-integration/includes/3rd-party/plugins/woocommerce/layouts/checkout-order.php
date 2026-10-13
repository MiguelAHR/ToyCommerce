<?php if ( ! empty( $qr ) || ! empty( $cip ) ) : ?>

	<h2 class="woocommerce-culqi-orders__title" tyle="margin: unset;"><?php esc_html_e( 'CIP Code', 'fullculqi' ); ?></h2>

	<div class="culqi_code_box" style="display: flex; justify-content: space-evenly; align-items: center; margin-bottom: 40px;">

		<div class="culqi_code_box_qr">
			<img src="<?php echo $qr; ?>" alt="qr code" style="width: 80%;" />
		</div>

		<div class="culqi_code_box_cip">
			<h1 style="text-align: center;"><?php echo $cip; ?></h1>
		</div>
	</div>

<?php endif; ?>