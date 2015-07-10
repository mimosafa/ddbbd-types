<?php

define( 'DDBBD_TYPES_DIR',  dirname( __FILE__ ) );
define( 'DDBBD_TYPES_FILE', DDBBD_TYPES_DIR . '/ddbbd-types.php' );

_ddbbd_register_classloader( 'DDBBD\\Types', DDBBD_TYPES_DIR . '/lib/types' );

/**
 * Bootstrap Class
 */
class DDBBD_TYPES {

	//
}
