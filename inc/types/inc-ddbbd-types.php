<?php
namespace DanaDonBoomBoomDoo\Types;

$current_action = $action;
$current_type = $post_type ?: $taxonomy ?: null;

if ( isset( $current_action ) ) {
	//
} else if ( isset( $current_type ) ) {
	//
} else {
	$h2 = $h2 = __( 'Content Types', 'ddbbd' );
	if ( current_user_can( 'manage_options' ) )
		$h2 .= sprintf( '<a href="%s" class="add-new-h2">%s</a>', '?page=ddbbd-types&action=add-new', __( 'Add New Type', 'ddbbd' ) );
}

function render_types_list_table() {
	$lt = new Types_List_Table();
	$lt->prepare_items();
	$lt->display();

	echo '<pre>';
	global $wp_rewrite;
	var_dump( $wp_rewrite->endpoints );
	echo '</pre>';
}

?>
<div class="wrap">
<h2><?php echo $h2; ?></h2>
<?php

render_types_list_table();

?>
</div>
