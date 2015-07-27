<?php
namespace DanaDonBoomBoomDoo\Types;

use DDBBD as D;

/**
 *
 */
class Settings {

	/**
	 * Singleton pattern
	 *
	 * @uses DDBBD\Singleton
	 */
	use D\Singleton;

	/**
	 * @var DDBBD\Options
	 */
	private $options;

	/**
	 * Actions
	 */
	private $actions = [
		'add-new' => 'Add New'
	];

	/**
	 * @uses DanaDonBoomBoomDoo\INDEX
	 */
	protected function __construct() {
		$this->options = _ddbbd_options();
		$this->add_menu_page();
		add_action( 'setup_theme', [ &$this, 'general_settings' ], 1000 + INDEX );
	}

	/**
	 *
	 */
	private function add_menu_page() {
		if ( ! $this->options->get_use_types() )
			return;

		$page = _ddbbd_settings_page();
		$page->init( 'ddbbd_types', __( 'Content Types' ), __( 'Types' ) );
		$page->file( DDBBD_TYPES_INC . '/inc-ddbbd-types.php', $this->parse_requests() );
	}

	private function parse_requests() {
		$actionFilter = function( $var ) {
			return array_key_exists( $var, $this->actions ) ? $var : null;
		};
		$postTypeFilter = function( $var ) {
			return post_type_exists( $var ) ? $var : null;
		};
		$taxonomyFilter = function( $var ) {
			return taxonomy_exists( $var ) ? $var : null;
		};
		$def = [
			'action'    => [ 'filter' => \FILTER_CALLBACK, 'options' => $actionFilter ],
			'post_type' => [ 'filter' => \FILTER_CALLBACK, 'options' => $postTypeFilter ],
			'taxonomy'  => [ 'filter' => \FILTER_CALLBACK, 'options' => $taxonomyFilter ],
		];
		return filter_input_array( \INPUT_GET, $def );
	}

	/**
	 * Dana Don-Boom-Boom-Doo plugins general settings
	 */
	public function general_settings() {
		$page = _ddbbd_settings_page();
		if ( $page::current_cached_page() !== 'ddbbd_general_settings' ) {
			$page->init( 'ddbbd_general_settings', __( 'Dana Don-Boom-Boom-Doo General Settings' ), __( 'Settings', 'ddbbd' ) );
		}
		$page
			->section( 'custom-types-manager', __( 'Custom Types Management' ) )
			->description( __( 'Dana Don-Boom-Boom-Doo plugin will make you enable to manage Custom Content Types easier.') )
			->description( __( 'Every Custom Post Types, Custom Taxonomies, and Custom Endpoints will be managed as <strong>Type</strong> units.' ) )
			->description( __( 'If you enable to use Custom Types, "Types" menu will appear.' ) )
				->field( 'enable-custom-types', __( 'Enable Custom Types', 'ddbbd' ) )
				->option_name( $this->options->full_key( 'use_types' ), 'checkbox' )
		;
	}

}
