<?php
namespace DanaDonBoomBoomDoo\Types;

if ( ! defined( 'DDBBD_TYPES_INC' ) )
	die('-1');

/**
 * @var string $action
 * @var string $type
 */
$r = parse_requests();
extract( $r );

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
} else {
	/**
	 * Type's list page
	 */
	$h2 = __( 'Content Types', 'ddbbd' );
	if ( current_user_can( 'manage_options' ) )
		$h2 .= sprintf( '<a href="%s" class="add-new-h2">%s</a>', '?page=ddbbd_types&action=add-new', __( 'Add New Type', 'ddbbd' ) );
	$callback = 'render_types_list_table';
}

function parse_requests() {
	$actionFilter = function( $var ) {
		static $actions = [ 'add-new' ];
		return in_array( $var, $actions, true ) ? $var : null;
	};
	$def = [
		'action' => [ 'filter' => \FILTER_CALLBACK, 'options' => $actionFilter ],
		'type'   => \FILTER_DEFAULT,
	];
	return filter_input_array( \INPUT_GET, $def );
}

/**
 * Rendering Types list table
 */
function render_types_list_table() {
	$lt = new Types_List_Table();
	$lt->prepare_items();
	$lt->display();

	echo '<pre>';
	global $wp_rewrite;
	var_dump( $wp_rewrite->endpoints );
	echo '</pre>';
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
?>
<div class="wrap">
<h2><?php echo $h2; ?></h2>
<?php call_user_func_array( __NAMESPACE__ . '\\' . $callback, $args ); ?>
</div>
