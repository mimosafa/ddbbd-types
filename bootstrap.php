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
const INDEX = 10;

/**
 * Bootstrap after plugins loaded
 */
add_action( 'plugins_loaded', __NAMESPACE__ . '\\Bootstrap::getInstance', INDEX );

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
	 * Dana Don-Boom-Boom-Doo Types plugin options
	 */
	private $options = [
		'use_types' => 'boolean',
		'types' => null,
	];

	/**
	 * Constructor
	 *
	 * @access private
	 */
	protected function __construct() {
		register_activation_hook( DDBBD_TYPES_FILE, [ &$this, '_activation' ] );
		register_deactivation_hook( DDBBD_TYPES_FILE, [ &$this, '_deactivation' ] );
		$this->set_options();
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

	private function set_options() {
		$options = _ddbbd_options();
		foreach ( $this->options as $option => $filter ) {
			$options->add( $option, $filter );
		}
	}

	/**
	 * @access private
	 */
	private function init() {
		if ( is_admin() ) {
			\DDBBD\Types\Objects::getInstance();
			Settings::getInstance();
		}
	}

}
