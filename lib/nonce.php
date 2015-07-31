<?php
namespace DDBBD;

/**
 * Dana Don-Boom-Boom-Doo WP Nonce interface
 *
 * @package    WordPress
 * @subpackage DDBBD
 *
 * @author     Toshimichi Mimoto <mimosafa@gmail.com>
 */
class Nonce {

	/**
	 * @var string
	 */
	private $context;

	/**
	 * Prefixes
	 * @var string
	 */
	private $nonce_prefix;
	private $action_prefix;

	/**
	 * @var array { @type DDBBD\Nonce }
	 */
	private static $instances = [];

	/**
	 * @access public
	 *
	 * @param  string $context
	 * @return DDBBD\Nonce
	 */
	public static function getInstance( $context ) {
		if ( ! filter_var( $context ) )
			return;
		if ( ! isset( self::$instances[$context] ) )
			self::$instances[$context] = new self( $context );
		return self::$instances[$context];
	}

	/**
	 * Constructor
	 *
	 * @access private
	 *
	 * @param  string $context
	 * @return void
	 */
	private function __construct( $context ) {
		$this->context = $context;
		$this->nonce_prefix  = '_ddbbd_nonce';
		$this->action_prefix = wp_create_nonce( $context ) . '_action';
	}

	/**
	 * @access public
	 *
	 * @param  string $actionurl
	 * @param  string $field
	 * @return string
	 */
	public function nonce_url( $actionurl, $field = '' ) {
		return wp_nonce_url(
			$actionurl,
			$this->get_action_key( $field ),
			$this->get_nonce_key( $field )
		);
	}

	/**
	 * @access public
	 *
	 * @param  string  $field
	 * @param  boolean $referer
	 * @param  boolean $echo
	 * @return void|string
	 */
	public function nonce_field( $field = '', $referer = true, $echo = true ) {
		return wp_nonce_field(
			$this->get_action_key( $field ),
			$this->get_nonce_key( $field ),
			$referer,
			$echo
		);
	}

	/**
	 * @access public
	 *
	 * @param  string $field
	 * @return string
	 */
	public function create_nonce( $field = '' ) {
		return wp_create_nonce( $this->get_action_key( $field ) );
	}

	/**
	 * @access public
	 *
	 * @param  string $field
	 * @return boolean|void
	 */
	public function check_admin( $field = '' ) {
		return check_admin_referer(
			$this->get_action_key( $field ),
			$this->get_nonce_key( $field )
		);
	}

	// check_ajax_referer()

	/**
	 * @access public
	 *
	 * @param  string $field
	 * @return int|boolean
	 */
	public function verify_nonce( $field = '' ) {
		return wp_verify_nonce(
			$this->get_nonce_key( $field ),
			$this->get_action_key( $field )
		);
	}

	/**
	 * @access public
	 *
	 * @param  string $field
	 * @return string
	 */
	public function get_nonce_key( $field = '' ) {
		$str = [ $this->nonce_prefix, $this->context ];
		if ( $field = filter_var( $field ) )
			$str[] = $field;
		return implode( '_', $str );
	}

	/**
	 * @access public
	 *
	 * @param  string $field
	 * @return string
	 */
	public function get_action_key( $field = '' ) {
		$str = [ $this->action_prefix, $this->context ];
		if ( $field = filter_var( $field ) )
			$str[] = $field;
		return implode( '-', $str );
	}

}
