<?php
/**
 * Common functions for Dana Don-Boom-Boom-Doo plugins
 *
 * @package    WordPress
 * @subpackage DDBBD
 */

if ( ! defined( 'DDBBD_FUNCTIONS_INCLUDED' ) )
	define( 'DDBBD_FUNCTIONS_INCLUDED', __FILE__ );

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
		// Flag
		static $hasError = false;

		$e = new WP_Error();

		$basename = plugin_basename( $file );

		// Required Ver.
		$phpReq = $phpReq ?: DDBBD_REQUWIRED_PHP_VER;
		$wpReq  = $wpReq  ?: DDBBD_REQUWIRED_WP_VER;

		// Current Ver.
		$phpEnv = PHP_VERSION;
		$wpEnv  = $GLOBALS['wp_version'];

		// Check PHP Ver.
		if ( version_compare( $phpEnv, $phpReq, '<' ) ) {
			$e->add(
				'error',
				sprintf(
					__( '<p>PHP version %1$s does not meet the requirements to activate <code>%2$s</code>. %3$s or higher will be required.</p>' ),
					esc_html( $phpEnv ), $basename, esc_html( $phpReq )
				)
			);
		}

		// Check WordPress Ver.
		if ( version_compare( $wpEnv, $wpReq, '<' ) ) {
			$e->add(
				'error',
				sprintf(
					__( '<p>WordPress version %1$s does not meet the requirements to activate <code>%2$s</code>. %3$s or higher will be required.</p>' ),
					esc_html( $wpEnv ), $basename, esc_html( $wpReq )
				)
			);
		}

		if ( $e->get_error_code() ) {
			global $_ddbbd_version_error_messages;
			global $_ddbbd_deactivate_plugins;

			if ( ! $_ddbbd_version_error_messages )
				$_ddbbd_version_error_messages = array();

			$_ddbbd_version_error_messages = $_ddbbd_version_error_messages + $e->get_error_messages();

			if ( ! $_ddbbd_deactivate_plugins )
				$_ddbbd_deactivate_plugins = array();

			$_ddbbd_deactivate_plugins[] = $basename;

			if ( ! $hasError ) {
				$hasError = true;
				add_action( 'admin_notices', '_ddbbd_plugin_requirements_error' );
			}

			return false;
		}
		return true;
	}
}

if ( ! function_exists( '_ddbbd_plugin_requirements_error' ) ) {
	/**
	 * Print error
	 */
	function _ddbbd_plugin_requirements_error() {
		global $_ddbbd_version_error_messages;
		global $_ddbbd_deactivate_plugins;

		foreach ( $_ddbbd_version_error_messages as $msg ) {
			echo "<div class=\"message error notice is-dismissible\">\n\t{$msg}\n</div>\n";
		}
		deactivate_plugins( $_ddbbd_deactivate_plugins, true );
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
		$options = is_array( $options ) ? $options : array();
		$options = array_merge( $options, [ 'hyphenate_classname' => true ] );
		if ( class_exists( 'DDBBD\\ClassLoader' ) )
			DDBBD\ClassLoader::register( $namespace, $path, $options );
	}
}

if ( ! function_exists( '_ddbbd_settings_page' ) ) {
	/**
	 * Return 'Dana Don-Boom-Boom-Doo' plugin's settings page instance
	 *
	 * @return DDBBD\Settings_Page
	 */
	function _ddbbd_settings_page() {
		static $instance;
		if ( ! $instance ) {
			$instance = new DDBBD\Settings_Page( 'ddbbd', '', __( 'Dana Don-Boom-Boom-Doo', 'ddbbd' ) );
			$instance->set_options( _ddbbd_options() );
			add_action( 'setup_theme', array( $instance, 'done' ), 9999 );
		}
		return $instance;
	}
}

if ( ! function_exists( '_ddbbd_options' ) ) {
	/**
	 * Return 'Dana Don-Boom-Boom-Doo' plugin's option instance
	 *
	 * @return DDBBD\Options
	 */
	function _ddbbd_options() {
		static $instance;
		if ( ! $instance )
			$instance = new DDBBD\Options( 'ddbbd_' );
		return $instance;
	}
}
