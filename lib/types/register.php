<?php
namespace DDBBD\Types;

/**
 * WordPress custom content types registration interface
 *
 * @access private
 *
 * @package    WordPress
 * @subpackage DDBBD
 *
 * @author mimosafa <mimosafa@gmail.com>
 */
class Register {

	/**
	 * Singleton pattern
	 *
	 * @uses DDBD\Singleton
	 */
	use \DDBBD\Singleton;

	/**
	 * Arguments for registration
	 *
	 * @var array
	 */
	private $post_types = [];
	private $taxonomies = [];
	private $endpoints  = [];

	/**
	 * Add Post Type
	 *
	 * @access public
	 *
	 * @param  string $name
	 * @param  array  $args    Optional
	 * @param  array  $options Optional
	 * @return void
	 */
	public static function post_type( $name, $args = [], $options = [] ) {
		if ( ! $name = apply_filters( 'ddbbd_types_register_post_type_name', $name, $args, $options ) )
			return;

		$self = self::getInstance();

		$args = apply_filters( 'ddbbd_types_register_post_type_args', $args, $name, $options );

		// Stored, for registration
		$self->post_types[] = [ 'post_type' => $name, 'args' => $args ];

		// Do somthing, if necessary
		do_action( 'ddbbd_types_register_post_type_options', $options, $name, $args );
	}

	/**
	 * Add Taxonomy
	 *
	 * @access public
	 *
	 * @param  string        $name
	 * @param  string|array  $object_type Optional
	 * @param  array         $args        Optional
	 * @param  array         $options     Optional
	 * @return void
	 */
	public static function taxonomy( $name, $object_type = [], $args = [], $options = [] ) {
		if ( ! $name = apply_filters( 'ddbbd_types_register_taxonomy_name', $name, $object_type, $args, $options ) )
			return;

		$self = self::getInstance();

		$object_type = apply_filters( 'ddbbd_types_register_taxonomy_object_type', $object_type, $name, $args, $options );
		$args = apply_filters( 'ddbbd_types_register_taxonomy_args', $args, $name, $object_type, $options );

		// Stored, for registration
		$self->taxonomies[] = [ 'taxonomy' => $name, 'object_type' => $object_type, 'args' => $args ];

		// Do somthing, if necessary
		do_action( 'ddbbd_types_register_taxonomy_options', $options, $name, $object_type, $args );
	}

	/**
	 * Add Endpoint
	 *
	 * @access public
	 *
	 * @param  string $name
	 * @param  array  $args    Optional
	 * @param  array  $options Optional
	 * @return void
	 */
	public static function endpoint( $name, $args = [], $options = [] ) {
		if ( ! $name = apply_filters( 'ddbbd_types_register_endpoint_name', $name, $args, $options ) )
			return;

		$self = self::getInstance();

		$args = apply_filters( 'ddbbd_types_register_endpoint_args', $args, $name, $options );

		// Stored, for registration
		$self->endpoints[] = [ 'endpoint' => $name, 'args' => $args ];

		// Do somthing, if necessary
		do_action( 'ddbbd_types_register_endpoint_options', $options, $name, $args );
	}

	/**
	 * Constructor - Add actions/filters
	 *
	 * @access private
	 */
	private function __construct() {
		add_action( 'init', [ &$this, 'register_taxonomies' ],   1 );
		add_action( 'init', [ &$this, 'register_post_types' ],   1 );
		add_action( 'init', [ &$this, 'add_rewrite_endpoints' ], 1 );
		add_filter( 'query_vars', [ &$this, 'add_query_vars'] );
	}

	/**
	 * @access private
	 */
	public function register_post_types() {
		if ( ! $this->post_types )
			return;

		static $thumbnail_supported;
		$thumbnail_supported = current_theme_supports( 'post-thumbnails' );

		foreach ( $this->post_types as $array ) {
			/**
			 * @var string $post_type
			 * @var array  $args
			 */
			extract( $array );

			if ( ! $post_type = filter_var( $post_type ) )
				continue;

			$args = is_array( $args ) ? $args : [];

			if ( ! isset( $args['label'] ) || ! filter_var( $args['label'] ) ) {
				if ( ! isset( $args['labels'] ) || ! isset( $args['labels']['name'] ) || ! filter_var( $args['labels']['name'] ) ) {
					$args['label'] = self::labelize( $post_type );
				}
			}

			if ( $this->taxonomies ) {
				$taxonomies = isset( $args['taxonomies'] ) ? (array) $args['taxonomies'] : [];
				foreach ( $this->taxonomies as $tax ) {
					if ( in_array( $post_type, (array) $tax['object_type'], true ) )
						$taxonomies[] = $tax['taxonomy'];
				}
				if ( $taxonomies )
					$args = array_merge( $args, [ 'taxonomies' => $taxonomies ] );
			}

			if ( ! $thumbnail_supported && isset( $args['supports'] ) && in_array( 'thumbnail', (array) $args['supports'], true ) ) {
				add_theme_support( 'post-thumbnails' );
				$thumbnail_supported = true;
			}

			register_post_type( $post_type, $args );
		}
	}

	/**
	 * @access private
	 */
	public function register_taxonomies() {
		if ( ! $this->taxonomies )
			return;
		
		foreach ( $this->taxonomies as $array ) {
			/**
			 * @var string       $taxonomy
			 * @var string|array $object_type
			 * @var array        $args
			 */
			extract( $array );

			/**
			 * @todo
			 */

			register_taxonomy( $taxonomy, $object_type, $args );
		}
	}

	/**
	 * @access private
	 */
	public function add_rewrite_endpoints() {
		if ( ! $this->endpoints )
			return;
		// ~
	}

	/**
	 * @access private
	 */
	public function add_query_vars( $vars ) {
		if ( $this->endpoints ) {
			// ~
		}
		return $vars;
	}

	// String methods

	private static function labelize( $name ) {
		return ucwords( str_replace( [ '-', '_' ], ' ', $name ) );
	}

}
