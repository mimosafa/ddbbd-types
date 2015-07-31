<?php
namespace DDBBD\Types;

abstract class Type {

	public function __construct( $name ) {
		if ( ! did_action( 'setup_theme' ) )
			return false;
		//
	}

	public static function is_reserved_string( $str ) {
		global $wp;
		//
	}

}
