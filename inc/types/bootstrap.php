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
	 * @var DDBBD\Options
	 */
	private $optionsInstance;

	/**
	 * Dana Don-Boom-Boom-Doo Types plugin options
	 */
	private $options = [
		'use_types' => 'boolean',
		'export_types_as_json' => 'boolean',
		'types_json_dir' => null,
		'types' => null,
		'type'  => null,
	];

	/**
	 * Constructor
	 *
	 * @access private
	 */
	protected function __construct() {
		$this->optionsInstance = _ddbbd_options();
		foreach ( $this->options as $option => $filter ) {
			$this->optionsInstance->add( $option, $filter );
		}
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
		if ( is_admin() && current_user_can( 'manage_options' ) ) {
			\DDBBD\Types\Objects::getInstance();
			Settings::getInstance();
		}
		\DDBBD\Types\Representation::getInstance();
	}

}
