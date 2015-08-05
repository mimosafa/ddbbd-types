<?php
namespace DanaDonBoomBoomDoo\Types;

// Index in Dana Don-Boom-Boom-Doo plugins
const INDEX = 10;

add_action( 'plugins_loaded', __NAMESPACE__ . '\\Bootstrap::getInstance', INDEX );

class Bootstrap {

	/**
	 * Singleton pattern
	 *
	 * @uses  DDBBD\Singleton
	 */
	use \DDBBD\Singleton;

	protected function __construct() {
		$this->define_options();
		$this->init();
	}

	private function define_options() {
		$opt = _ddbbd_options( 'types' );
		$opt->def( 'active', 'boolean' );
		$opt->def( 'types' );
		$opt->def( 'type' );
	}

	private function init() {
		if ( is_admin() && current_user_can( 'manage_options' ) )
			Settings::getInstance();
		if ( _ddbbd_options( 'types' )->get_active() )
			Manager::getInstance();
	}

}

add_filter( 'ddbbd_types_options_get_types', function( $value ) {
	return [ 'ddbbd_types' ];
} );

add_filter( 'ddbbd_types_options_get_type', function( $value, $subkey ) {
	if ( $subkey === 'ddbbd_types' ) {
		return [
			'post_type' => $subkey,
			'args' => [ 'public' => true ],
			'options' => [ /*'permalink' => 'numeric'*/ ]
		];
	}

}, 10, 2 );
