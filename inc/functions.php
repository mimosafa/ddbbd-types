<?php
/**
 * Common functions for Dana Don-Boom-Boom-Doo plugins
 *
 * @package    WordPress
 * @subpackage DDBBD
 */

if ( ! defined( 'DDBBD_FUNCTIONS_INCLUDED' ) )
	define( 'DDBBD_FUNCTIONS_INCLUDED', 1 );

if ( ! defined( 'DDBBD_REQUWIRED_PHP_VER' ) )
	define( 'DDBBD_REQUWIRED_PHP_VER', '5.4.0' );

if ( ! defined( 'DDBBD_REQUWIRED_WP_VER' ) )
	define( 'DDBBD_REQUWIRED_WP_VER', '4.1' );

if ( ! function_exists( '_ddbbd_plugin_requirements' ) ) {
	/**
	 * Plugin's requirements check
	 *
	 * @param  string $file   Plugin's file path
	 * @param  string $plugin Plugin's name
	 * @param  string $phpReq Required PHP Ver.
	 * @param  string $wpReq  Required WordPress Ver.
	 * @return boolean
	 */
	function _ddbbd_plugin_requirements( $file, $plugin, $phpReq = null, $wpReq = null ) {
		$e = new WP_Error();

		// Required Ver.
		$phpReq = $phpReq ?: DDBBD_REQUWIRED_PHP_VER;
		$wpReq  = $wpReq  ?: DDBBD_REQUWIRED_WP_VER;

		// Current Ver.
		$phpEnv = PHP_VERSION;
		$wpEnv  = $GLOBALS['wp_version'];

		// Check PHP Ver.
		if ( version_compare( $phpEnv, $phpReq, '<' ) ) {
			$e->add( 'error' );
		}

		// Check WordPress Ver.
		if ( version_compare( $wpEnv, $wpReq, '<' ) ) {
			$e->add( 'error' );
		}

		if ( $e->get_error_code() ) {
			//
			return false;
		}
		return true;
	}
}

if ( ! function_exists( '_ddbbd_register_classloader' ) ) {
	/**
	 * Register ClassLoader
	 *
	 * @param  string $namespace
	 * @param  string $path
	 * @return void
	 */
	function _ddbbd_register_classloader( $namespace, $path = null, $options = null ) {
		if ( ! $path ) {
			$path  = trailingslashit( WP_PLUGIN_DIR );
			$path .= str_replace( '\\', '-', trim( $namespace, '\\' ) );
		}
		if ( class_exists( 'DDBBD\\ClassLoader' ) )
			DDBBD\ClassLoader::register( $namespace, $path, $options );
	}
}

if ( ! function_exists( '_ddbbd_plugins_settings_ui' ) ) {
	/**
	 * DDBBD plugins settings UI
	 */
	function _ddbbd_plugins_settings_ui() {
		if ( class_exists( 'DDBBD\\Settings\\Page' ) )
			do_action( '_ddbbd_plugins_settings' );
	}
}
