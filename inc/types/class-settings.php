<?php
namespace DanaDonBoomBoomDoo\Types;

use DDBBD as D;

class Settings {

	use D\Singleton;

	private $page;

	protected function __construct() {
		$this->page = _ddbbd_settings_page();
		$this->menu_page();
	}

	private function menu_page() {
		$this->page->init( 'Types' )->file( __DIR__ . '/inc-test.php', [ 'a' => 10*99*85647 ] );
	}

}
