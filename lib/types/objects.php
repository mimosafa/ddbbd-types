<?php
namespace DDBBD\Types;

class Objects {

	/**
	 * Singleton pattern
	 * @uses DDBBD\Singleton
	 */
	use \DDBBD\Singleton;

	public static $types = [];

	public static function getAll() {
		return self::$types;
	}

	protected function __construct() {
		if ( ! did_action( 'init' ) )
			$this->record_all_types();
	}

	private function record_all_types() {
		add_action( 'registered_post_type', [ &$this, 'record_all_post_types' ], 10, 2 );
		add_action( 'registered_taxonomy',  [ &$this, 'record_all_taxonomies' ], 10, 3 );
	}

	public function record_all_post_types( $post_type, $args ) {
		$array = [
			'type' => 'post_type',
			'name' => $post_type,
			'label' => $args->labels->name,
			'args' => $args,
		];
		if ( ! isset( self::$types[$post_type] ) )
			self::$types[$post_type] = [];

		self::$types[$post_type] = array_merge( self::$types[$post_type], $array );
		if ( $args->_builtin )
			self::$types[$post_type]['_builtin'] = true;
	}

	public function record_all_taxonomies( $taxonomy, $object_type, $args ) {
		self::$types[$taxonomy] = [
			'type' => 'taxonomy',
			'name' => $taxonomy,
			'label' => $args['labels']->name,
			'object_type' => (array) $object_type,
			'args' => $args
		];
		if ( isset( $args['_builtin'] ) && $args['_builtin'] )
			self::$types[$taxonomy]['_builtin'] = true;
		foreach ( self::$types[$taxonomy]['object_type'] as $post_type ) {
			if ( ! isset( self::$types[$post_type] ) )
				self::$types[$post_type] = [];
			if ( ! isset( self::$types[$post_type]['object_type'] ) )
				self::$types[$post_type]['object_type'] = [];
			self::$types[$post_type]['object_type'][] = $taxonomy;
		}
	}

}
