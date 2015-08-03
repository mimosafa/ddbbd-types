<?php
namespace DanaDonBoomBoomDoo\Types;

use DDBBD as D;

// Options
$options = _ddbbd_options();

if ( isset( $action ) && $action = 'add-new' ) {
	$type_obj = [];
	$nonce = _ddbbd_nonce( 'add_new_type' );
} else if ( isset( $type ) ) {
	if ( ! $type_obj = $options->get_type( $type ) ) {
		global $wp_post_types, $wp_taxonomies;
		if ( isset( $wp_post_types[$type] ) ) {
			$type_obj = json_decode( json_encode( $wp_post_types[$type] ), true );
		} else if ( isset( $wp_taxonomies[$type] ) ) {
			$type_obj = json_decode( json_encode( $wp_taxonomies[$type] ), true );
		}
	}
	if ( isset( $type_obj ) )
		$nonce = _ddbbd_nonce( 'edit_type' );
}


?>
<form action="" method="post" id="ddbbd-type">
	<?php $nonce->nonce_field(); ?>
	<pre>
<?php if ( isset( $type_obj ) ) var_dump( $type_obj ); ?>
	</pre>
</form>
