<?php
namespace DanaDonBoomBoomDoo;

use DDBBD as D;

class Types {

	/**
	 * Singleton pattern
	 * @uses DDBBD\Singleton
	 */
	use D\Singleton;

	/**
	 * Actions
	 * @var array
	 */
	private $actions = [
		'add-new' => 'Add New'
	];

	/**
	 * @var string
	 */
	private $action;

	/**
	 * Constructor
	 *
	 * @access private
	 */
	protected function __construct() {
		$this->parse_query_vars();
		$this->load_page();
	}

	/**
	 * @access private
	 */
	private function parse_query_vars() {
		$actionFilter = function( $var ) {
			return array_key_exists( $var, $this->actions ) ? $var : null;
		};
		$def = [
			'action' => [ 'filter' => \FILTER_CALLBACK, 'options' => $actionFilter ]
		];
		$vars = filter_input_array( \INPUT_GET, $def );
		extract( $vars );

		if ( isset( $action ) )
			$this->action = $action;
	}

	private function load_page() {
		if ( ! $this->action ) {
			$h2 = __( 'Types', 'ddbbd' );
			if ( current_user_can( 'manage_options' ) )
				$h2 .= sprintf( '<a href="%s" class="add-new-h2">%s</a>', '?page=ddbbd-types&action=add-new', __( 'Add New' ) );
		} else {
			$h2 = _x( $this->actions[$this->action], 'type', 'ddbbd' );
		}

		echo '<div class="wrap"><h2>' . $h2 . '</h2>';

		if ( ! isset( $this->action ) )
			$this->types_list();
		
		echo '</div>';

	}

	private function types_list() {
		$lt = new Types_List_Table();
		$lt->prepare_items();
		$lt->display();

		echo '<pre>';
		global $wp_rewrite;
		var_dump( $wp_rewrite->endpoints );
		#var_dump( \DanaDonBoomBoomDoo::getAllTypes() );
		echo '</pre>';
	}

}
