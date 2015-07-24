<?php
/**
 * Dana Don-Boom-Boom-Doo Types plugin bootstrap file.
 *
 * @package    WordPress
 * @subpackage DDBBD
 * @author     Toshimichi Mimoto
 */
namespace DanaDonBoomBoomDoo\Types;

/**
 * Index in 'Dana Don-Boom-Boom-Doo' plugins
 */
const ORDER = 10;

/**
 * Bootstrap after plugins loaded
 */
add_action( 'plugins_loaded', __NAMESPACE__ . '\\Bootstrap::getInstance' );

/**
 * Bootstrap Class
 */
class Bootstrap {

	/**
	 * Singleton pattern
	 *
	 * @uses DDBBD\Singleton
	 */
	use \DDBBD\Singleton;

	/**
	 * Constructor
	 *
	 * @access private
	 */
	protected function __construct() {
		register_activation_hook( DDBBD_TYPES_FILE, [ &$this, '_activation' ] );
		register_deactivation_hook( DDBBD_TYPES_FILE, [ &$this, '_deactivation' ] );
		$this->init();
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
			add_action( 'init', __NAMESPACE__ . '\\Settings::getInstance', ORDER );
	}

}
