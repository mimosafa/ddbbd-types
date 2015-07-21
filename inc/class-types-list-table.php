<?php
namespace DanaDonBoomBoomDoo;

use DDBBD as D;

class Types_List_Table extends \DDBBD\List_Table {

	public function __construct() {
		parent::__construct( [
			'singular' => 'ddbbd_type',
			'plural'   => 'ddbbd_types',
		] );
	}

	public function prepare_items() {
		$this->_column_headers = [
			$this->get_columns(),
			$this->get_hidden_columns(),
			$this->get_sortable_columns()
		];

		$custom_types = Options::get_types();
		if ( ! $custom_types ) {
			$_REQUEST['view'] = 'all';
			$this->items = array_filter( D\Types\Objects::getAll(), function( $array ) { return isset( $array['type'] ); } );
		}
	}

	public function get_columns() {
		$columns = [
			'cb'     => '<input type="checkbox" />',
			'label' => __( 'Label' ),
			'type'   => __( 'Content Type', 'ddbbd' ),
		];
		return $columns;
	}
	public function get_hidden_columns() { return []; }
	public function get_sortable_columns() { return []; }

	public function column_cb( $item ) {
		return isset( $item['_builtin'] ) ? '' : sprintf( '<input type="checkbox" name="types[]" value="%s" />', $item['name'] );
	}

	public function column_default( $item, $column_name ) {
		$return = isset( $item[$column_name] ) ? esc_html( $item[$column_name] ) : '';
		if ( isset( $item['_builtin'] ) && $item['_builtin'] )
			$return .= ' <small>core</small>';
		return $return;
	}

}
