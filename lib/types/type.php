<?php
namespace DDBBD\Types;

abstract class Type {

	protected static $black_list;

	protected function __construct() {
		if ( ! self::$black_list ) {
			global $wp;
			self::$black_list = array_merge( $wp->public_query_vars, $wp->private_query_vars );
		}
	}

	public static function filter_name( $name ) {
		static::getInstance();
		$options = [ 'options' => [ 'regexp' => static::$regexp ] ];
		if ( ! $name = filter_var( $name, \FILTER_VALIDATE_REGEXP, $options ) )
			return null;
		if ( in_array( $name, self::$black_list, true ) )
			return null;
		self::$black_list[] = $name;
		return $name;
	}

}
