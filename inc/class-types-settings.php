<?php
namespace DanaDonBoomBoomDoo_Types;

use DDBBD as D;

class Settings {

	use D\Singleton;

	private $page;

	protected function __construct() {
		#var_dump( class_exists( 'DanaDonBoomBoomDoo' ) );
	}

}
