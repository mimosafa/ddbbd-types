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
	 * @param  string $phpReq Required PHP Ver.
	 * @param  string $wpReq  Required WordPress Ver.
	 * @return boolean
	 */
	function _ddbbd_plugin_requirements( $file, $phpReq = null, $wpReq = null ) {
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
	function _ddbbd_register_classloader( $namespace, $path, $options = null ) {
		$options = is_array( $options ) ? $options : [];
		$options = array_merge( $options, [ 'hyphenate_classname' => true ] );
		if ( class_exists( 'DDBBD\\ClassLoader' ) )
			DDBBD\ClassLoader::register( $namespace, $path, $options );
	}
}

if ( ! function_exists( '_ddbbd_settings_page' ) ) {
	/**
	 * Dana Don-Boom-Boom-Doo plugin's settings page instance returner
	 *
	 * @return DDBBD\Settings_Page
	 */
	function _ddbbd_settings_page() {
		static $instance;
		if ( ! $instance ) {
			$instance = new DDBBD\Settings_Page( 'ddbbd', '', __( 'DDBBD', 'ddbbd' ) );
			add_action( 'wp_loaded', array( $instance, 'done' ) );
		}
		return $instance;
	}
}
