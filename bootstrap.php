<?php
/**
 * Dana Don-Boom-Boom-Doo Types plugin bootstrap file.
 *
 * @package    WordPress
 * @subpackage DDBBD
 * @author     Toshimichi Mimoto
 */

add_action( 'plugins_loaded', 'DanaDonBoomBoomDoo_Types::getInstance' );

/**
 * Bootstrap Class
 */
class DanaDonBoomBoomDoo_Types {

	/**
	 * Singleton pattern
	 *
	 * @uses DDBBD\Singleton
	 */
	use DDBBD\Singleton;

	/**
	 * Constructor
	 *
	 * @access private
	 */
	protected function __construct() {
		$this->_register_classloader();
		register_activation_hook( DDBBD_TYPES_FILE, [ &$this, '_activation' ] );
		register_deactivation_hook( DDBBD_TYPES_FILE, [ &$this, '_deactivation' ] );
		$this->init();
	}

	/**
	 * @access private
	 */
	private function _register_classloader() {
		$options = [ 'file_prefix' => 'class-types-' ];
		_ddbbd_register_classloader( 'DanaDonBoomBoomDoo_Types', DDBBD_TYPES_INC, $options );
	}

	/**
	 * @access private
	 */
	public function _activation() {
		//
	}

	/**
	 * @access private
	 */
	public function _deactivation() {
		//
	}

	/**
	 * @access private
	 */
	private function init() {
		if ( is_admin() )
			add_action( 'init', 'DanaDonBoomBoomDoo_Types\\Settings::getInstance' );
	}

}
