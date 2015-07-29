<?php
namespace DanaDonBoomBoomDoo\Types;

use DDBBD as D;

class Types_List_Table extends \WP_List_Table {

	/**
	 * @var DDBBD\Options
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct() {
		$this->options = _ddbbd_options();

		parent::__construct( [
			'singular' => 'ddbbd_type',
			'plural'   => 'ddbbd_types',
		] );
	}

	public function __get( $name ) {
		if ( property_exists( __CLASS__, $name ) )
			return $this->$name;
	}

	public function prepare_items() {
		$this->_column_headers = [
			$this->get_columns(),
			$this->get_hidden_columns(),
			$this->get_sortable_columns()
		];

		$custom_types = $this->options->get_types();
		if ( ! $custom_types ) {
			$_REQUEST['view'] = 'all';
			$this->items = array_filter( D\Types\Objects::getAll(), function( $array ) { return isset( $array['type'] ); } );
		}
	}

	public function get_columns() {
		$columns = [
			'cb'     => '<input type="checkbox" />',
			'label'  => __( 'Label' ),
			'type'   => __( 'Content Type', 'ddbbd' ),
			'item_of' => __( 'Item of', 'ddbbd' ),
		];
		return $columns;
	}
	public function get_hidden_columns() { return []; }
	public function get_sortable_columns() { return []; }

	public function column_cb( $item ) {
		return isset( $item['_builtin'] ) ? '' : sprintf( '<input type="checkbox" name="types[]" value="%s" />', $item['name'] );
	}

	public function column_label( $item ) {
		$href = sprintf( '?page=ddbbd_types&type=%s', esc_html( $item['name'] ) );
		return sprintf( '<a href="%s">%s</a>', $href, esc_html( $item['label'] ) );
	}

	public function column_item_of( $item ) {
		if ( isset( $item['_builtin'] ) && $item['_builtin'] )
			return __( 'WordPress Core', 'ddbbd' );
		return '';
	}

	public function column_default( $item, $column_name ) {
		return isset( $item[$column_name] ) ? esc_html( $item[$column_name] ) : '';
	}

}
