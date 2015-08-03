<?php
namespace DDBBD\Types;

abstract class Type {

	protected static $black_list;

	protected function __construct() {
		if ( ! self::$black_list ) {
			global $wp;
			self::$black_list = $wp->public_query_vars + $wp->private_query_vars;
		}
	}

}
