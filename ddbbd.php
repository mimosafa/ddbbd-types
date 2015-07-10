<?php
/**
 * Plugin Name: Dana Don-Boom-Boom-Doo
 * Plugin URI:  https://github.com/dana-don-boom-boom-doo/ddbbd
 * Description: WordPress Custumize Package
 * Version:     0.0.0
 * Author:      Toshimichi Mimoto
 * Author URI:  http://mimosafa.me
 */

/**
 * Copyright (c) 2015 Toshimichi Mimoto ( http://mimosafa.me )
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

define( 'DDBBD_FILE', __FILE__ );
define( 'DDBBD_DIR', dirname( DDBBD_FILE ) );

if ( ! defined( 'DDBBD_NAME' ) )
	define( 'DDBBD_NAME', 'Dana Don-Boom-Boom-Doo' );

if ( ! defined( 'DDBBD_FUNCTIONS_INCLUDED' ) )
	require_once 'inc/functions.php';

if ( ! _ddbbd_plugin_requirements( DDBBD_FILE, DDBBD_NAME ) )
	return;

if ( ! class_exists( 'DDBBD\\ClassLoader' ) )
	require_once 'lib/classloader.php';

_ddbbd_register_classloader( 'DDBBD', DDBBD_DIR . '/lib' );

require_once 'bootstrap.php';
_ddbbd_plugin_init();
