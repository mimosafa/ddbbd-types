<?php
namespace DDBBD;

use DDBBD\ClassLoader as cl;

class Bootstrap {

	public static function init() {
		$page = new Settings_Page( 'ddbbd' );
		$page->done();
	}

}
