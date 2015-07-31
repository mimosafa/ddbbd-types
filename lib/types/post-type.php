<?php
namespace DDBBD\Types;

class Post_Type extends Type {

	/**
	 * @var string
	 */
	private $name;
	private $quey_var;
	private $rewrite;
	private $label;

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct( $name ) {
		if ( ! parent::__construct( $name ) )
			return;
		//
	}

	public static function is_valid_name( $name ) {
		static $regexp = [ 'regexp' => '/\A[a-z][a-z0-9_\-]*[a-z0-9]\z/'];
		if ( ! filter_var( $name, \FILTER_VALIDATE_REGEXP, [ 'options' => $regexp ] ) )
			return false;
		if ( strlen( $name ) > 20 )
			return false;
		return true;
	}

}
