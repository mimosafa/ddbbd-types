<?php
namespace DDBBD\Types;

class Post_Type extends Type {

	use \DDBBD\Singleton;

	protected function __construct() {
		parent::__construct();
		$this->init();
	}

	private function init() {
		add_filter( 'ddbbd_types_register_post_type_name', [ &$this, 'filter_name' ], 10, 3 );
	}

	public function filter_name( $name, $args, $options ) {
		static $regexp = [ 'regexp' => '/\A[a-z][a-z0-9_\-]*[a-z0-9]\z/' ];
		if ( ! $name = filter_var( $name, \FILTER_VALIDATE_REGEXP, [ 'options' => $regexp ] ) )
			return null;
		if ( strlen( $name ) > 20 )
			return null;
		if ( in_array( $name, self::$black_list, true ) )
			return null;
		return $name;
	}

}
