<?php
namespace DanaDonBoomBoomDoo\Types;

const INDEX = 10;

_ddbbd_register_classloader( 'DDBBD', DDBBD_TYPES_DIR . '/lib' );
_ddbbd_register_classloader( 'DanaDonBoomBoomDoo', DDBBD_TYPES_DIR . '/inc', [ 'file_prefix' => 'class-' ] );

add_action( 'plugins_loaded', __NAMESPACE__ . '\\Bootstrap::getInstance', INDEX );

class Bootstrap {

	/**
	 * Singleton pattern
	 *
	 * @uses  DDBBD\Singleton
	 */
	use \DDBBD\Singleton;

	private $options;

	protected function __construct() {
		$this->options = _ddbbd_options();
		$this->define_options();
		$this->init();
	}

	private function define_options() {
		$this->options->def( 'use_types', 'boolean' );
		$this->options->def( 'types' );
		$this->options->def( 'type' );
	}

	private function init() {
		if ( is_admin() && current_user_can( 'manage_options' ) )
			Settings::getInstance();
		if ( $this->options->get_use_types() )
			Manager::getInstance();
	}

}

add_filter( 'ddbbd_options_get_types', function( $value ) {
	return [ 'ddbbd_types' ];
} );

add_filter( 'ddbbd_options_get_type', function( $value, $subkey ) {
	if ( $subkey === 'ddbbd_types' ) {
		return [
			'post_type' => $subkey,
			'args' => [ 'public' => true ],
			'options' => [ /*'permalink' => 'numeric'*/ ]
		];
	}

}, 10, 2 );
