<?php
/**
 * Logs Class
 * @since  1.0.0
 * @package Includes / Logs
 */
class FullCulqi_Logs {

	protected $post_id = 0;
	protected $permission;
	protected $slug = 'culqi_log';

	/**
	 * Construct
	 * @param integer $post_id
	 * @return mixed
	 */
	public function __construct( $post_id = 0 ) {
		if( ! empty( $post_id ) )
			$this->post_id = $post_id;
	}

	/**
	 * Set Notice
	 * @param string $message
	 * @return mixed
	 */
	public function set_notice( $message = '' ) {
		$this->register( 'notice', $message );
	}

	/**
	 * Set Error
	 * @param string $message
	 * @return mixed
	 */
	public function set_error( $message = '' ) {
		$this->register( 'error', $message );
	}

	/**
	 * Set a log message
	 * @param string $type
	 * @param string $message
	 */
	protected function register( $type = 'notice', $message = '' ) {
		if( empty( $this->post_id ) )
			return;

		$order    = \wc_get_order( $this->post_id );
		$messages = $order->get_meta( $this->slug ) ?: [];

		$messages[] = [
			'dateh'		=> date('Y-m-d H:i:s'),
			'type'		=> $type,
			'message'	=> $message,
		];

		$order->update_meta_data( $this->slug, $messages );
		$order->save_meta_data();

		return true;
	}
}