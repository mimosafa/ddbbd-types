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
	private $options;

	/**
	 * Constructor
	 *
	 * @access private
	 */
	protected function __construct() {
		$this->add_options();
		register_activation_hook( DDBBD_TYPES_FILE, [ &$this, '_activation' ] );
		register_deactivation_hook( DDBBD_TYPES_FILE, [ &$this, '_deactivation' ] );
		$this->init();
	}

	private function add_options() {
		$this->options = _ddbbd_options();

		/**
		 *
		 */
		$this->options->add( 'use_types', 'boolean' );

		/**
		 *
		 */
		$this->options->add( 'types' );

		/**
		 *
		 */
		$this->options->add( 'type' );
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
		if ( $this->options->get_use_types() )
			Manager::getInstance();
	}

}

/**
 * Test
 */
add_filter( 'ddbbd_options_get_types', function( $value ) {
	return [ 'ddbbd_types' ];
} );

add_filter( 'ddbbd_options_get_type', function( $value, $subkey ) {
	return [
		'post_type' => $subkey,
		'args' => [ 'public' => true ],
		'options' => [ 'permalink' => 'numeric' ]
	];
}, 10, 2 );
