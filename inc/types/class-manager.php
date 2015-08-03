<?php
namespace DanaDonBoomBoomDoo\Types;

use DDBBD\Types;

class Manager {

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
	 * @var array
	 */
	private $types;

	protected function __construct() {
		$this->options = _ddbbd_options();
		if ( $this->types = $this->options->get_types() )
			add_action( 'setup_theme', [ &$this, 'init' ] );
	}

	public function init() {
		if ( ! doing_action( 'setup_theme' ) || ! is_array( $this->types ) )
			return;

		foreach ( $this->types as $type ) {
			if ( $type_args = $this->options->get_type( $type ) )
				$this->represent( $type_args );
		}
	}

	/**
	 * @access private
	 *
	 * @uses   DDBBD\Types\Register
	 */
	private function represent( Array $type_args ) {
		extract( $type_args );
		if ( isset( $post_type ) )
			Types\Register::post_type( $post_type, $args, $options );
		else if ( isset( $taxonomy ) )
			Types\Register::taxonomy( $taxonomy, $object_type, $args, $options );
		else if ( isset( $endpoint ) )
			Types\Register::endpoint( $post_type, $args, $options );
	}

}
