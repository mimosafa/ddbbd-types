<?php

define( 'DDBBD_TYPES_DIR',  dirname( __FILE__ ) );
define( 'DDBBD_TYPES_FILE', DDBBD_TYPES_DIR . '/ddbbd-types.php' );

/**
 * Register ClassLoader for DDBBD Types libraries
 */
_ddbbd_register_classloader( 'DDBBD\\Types', DDBBD_TYPES_DIR . '/lib/types' );

/**
 * Bootstrap
 */
add_action( 'plugins_loaded', 'DanaDonBoomBoomDoo_Types::getInstance' );

/**
 * Bootstrap Class
 */
class DanaDonBoomBoomDoo_Types {

	use DDBBD\Singleton;

	protected function __construct() {
		//
		register_activation_hook( DDBBD_TYPES_FILE, [ &$this, '_activation' ] );
		register_deactivation_hook( DDBBD_TYPES_FILE, [ &$this, '_deactivation' ] );
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

}
