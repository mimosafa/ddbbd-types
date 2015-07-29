<?php
namespace DanaDonBoomBoomDoo\Types;

if ( ! defined( 'DDBBD_TYPES_INC' ) )
	die('-1');

$objects = \DDBBD\Types\Objects::getAll();

$current_type = isset( $type ) && array_key_exists( $type, $objects ) ? $objects[$type] : null;
$args = [];

if ( isset( $action ) ) {
	//
} else if ( isset( $current_type ) ) {
	/**
	 * Show type's detail & Edit type form
	 */
	$h2 = __( ucwords( str_replace( '_', ' ', $current_type['type'] ) ), 'ddbbd' ) . ': ' . esc_html( $current_type['label'] );
	$args[] = $current_type;
	$callback = 'edit_object_type';
}

/**
 *
 */
function edit_object_type( $type ) {
	echo '<pre>';
	var_dump( $type );
	echo '</pre>';
}

/**
 * Render page
 */
call_user_func_array( __NAMESPACE__ . '\\' . $callback, $args );
