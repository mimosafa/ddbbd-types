<?php
namespace DDBBD\Types;

class Post_Type extends Type {

	/**
	 * Singleton pattern
	 *
	 * @uses DDBBD\Singleton
	 */
	use \DDBBD\Singleton;

	protected static $regexp = '/\A[a-z][a-z0-9_]{0,18}[a-z0-9]\z/';

	private static $arguments = [
		'name',
		'post_type_singular',
		'post_type_plural',
		'singular_label',
		'plural_label',
		'features',
		'rewrite_options',
		'public',
		'public_in_admin',
		'supports',
	];

	private static $default_arguments = [
		'name'               => '',
		'post_type_singular' => '',
		'post_type_plural'   => '',
		'singular_label'     => '',
		'plural_label'       => '',
		'features'           => [],
		'rewrite_options'    => [],
		'public'             => false,
		'public_in_admin'    => true,
		'supports'           => false
	];

	public function __construct( Array $args ) {
		foreach ( self::$arguments as $param ) {
			//
		}
	}

}
