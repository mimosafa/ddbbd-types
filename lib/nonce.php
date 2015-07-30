<?php
namespace DDBBD;

class Nonce {

	/**
	 * @var string
	 */
	private $context;

	private $nonce_prefix  = '_wp_nonce';
	private $action_prefix = 'wp-nonce';

	private static $instances = [];

	public static function getInstance( $context ) {
		if ( ! filter_var( $context ) )
			return;
		if ( ! isset( self::$instances[$context] ) )
			self::$instances[$context] = new self( $context );
		return self::$instances[$context];
	}

	private function __construct( $context ) {
		$this->context = $context;
	}

	public function nonce_field( $field = '', $referer = true, $echo = true ) {
		if ( $this->context ) {
			return wp_nonce_field( $this->get_action( $field ), $this->get_nonce( $field ), $referer, $echo );
		}
	}

	// create_nonce(), verify_nonce(), check_admin_refer()

	public function get_nonce( $field = '' ) {
		if ( $this->context ) {
			$str = [ $this->nonce_prefix, $this->context ];
			if ( $field = filter_var( $field ) )
				$str[] = $field;
			return implode( '_', $str );
		}
	}

	public function get_action( $field = '' ) {
		if ( $this->context ) {
			$str = [ $this->action_prefix, $this->context ];
			if ( $field = filter_var( $field ) )
				$str[] = $field;
			return implode( '-', $str );
		}
	}

}
