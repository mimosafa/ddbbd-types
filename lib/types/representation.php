<?php
namespace DDBBD\Types;

class Representation {

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
			$this->init();
	}

	private function init() {
		if ( ! is_array( $this->types ) )
			return;

		$this->add_hooks();

		foreach ( $this->types as $type ) {
			if ( $type_args = $this->options->get_type( $type ) )
				$this->represent( $type_args );
		}
	}

	private function add_hooks() {
		add_action( 'ddbbd_types_register_post_type_options', [ &$this, 'post_type_options' ], 10, 3 );
	}

	/**
	 * @access private
	 *
	 * @uses   DDBBD\Types\Register
	 */
	private function represent( Array $args ) {
		extract( $args );
		if ( isset( $post_type ) )
			Register::post_type( $post_type, $args, $options );
		else if ( isset( $taxonomy ) )
			Register::taxonomy( $taxonomy, $object_type, $args, $options );
		else if ( isset( $endpoint ) )
			Register::endpoint( $post_type, $args, $options );
	}

	public function post_type_options( $options, $name, $args ) {
		if ( isset( $options['permalink'] ) && $options['permalink'] === 'numeric' )
			Numeric_Permalink::set( $name );
	}

}
