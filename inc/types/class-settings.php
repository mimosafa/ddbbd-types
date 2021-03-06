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

	private $actions = [ 'add-new' ];

	/**
	 * @uses DanaDonBoomBoomDoo\Types\INDEX
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
		$page->init( 'ddbbd_types', '', __( 'Types' ) );
		if ( filter_input( \INPUT_GET, 'page' ) !== 'ddbbd_types' )
			return;

		/**
		 * Settings page, Now.
		 */
		$r = $this->parse_requests();
		extract( $r );
		if ( isset( $type ) ) {
			$page->title( $type );
			$page->file( __DIR__ . '/inc-types.php', $r, true );
		} else if ( isset( $action ) ) {
			//
		} else {
			$page->callback( [ &$this, 'render_types_list_table' ] );
		}
	}

	/**
	 *
	 */
	private function parse_requests() {
		$actionFilter = function( $var ) {
			return in_array( $var, $this->actions, true ) ? $var : null;
		};
		$def = [
			'action' => [ 'filter' => \FILTER_CALLBACK, 'options' => $actionFilter ],
			'type'   => \FILTER_DEFAULT,
		];
		return filter_input_array( \INPUT_GET, $def, true );
	}

	/**
	 *
	 */
	public function render_types_list_table() {
		$h2 = __( 'Content Types', 'ddbbd' );
		if ( current_user_can( 'manage_options' ) )
			$h2 .= sprintf( '<a href="%s" class="add-new-h2">%s</a>', '?page=ddbbd_types&action=add-new', __( 'Add New Type', 'ddbbd' ) );
		$lt = new Types_List_Table();
		$lt->prepare_items();

		echo '<div class="wrap">';
		echo '<h2>' . $h2 . '</h2>';
		$lt->display();
		echo '</div>';
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
			->section( 'custom-types-manager', __( 'Custom Types Management', 'ddbbd' ) )
				->description( __( 'Dana Don-Boom-Boom-Doo plugin will make you enable to manage Custom Content Types easier.') )
				->description( __( 'Every Custom Post Types, Custom Taxonomies, and Custom Endpoints will be managed as <strong>Type</strong> units.' ) )
				->description( __( 'If you enable to use Custom Types, "Types" menu will appear.' ) )
					->field( 'enable-custom-types', __( 'Enable Custom Types', 'ddbbd' ) )
						->option_name( $this->options->full_key( 'use_types' ), 'checkbox' )
		;
		do_action( '_ddbbd_types_settings_general_settings', $page, $this->options->get_use_types() );
		/*
		if ( $this->options->get_use_types() ) {
			$page
				->field( 'export-types-as-json', __( 'Save as json files', 'ddbbd' ) )
					->option_name( $this->options->full_key( 'save_types_as_json' ), 'checkbox' )
				->field( 'dir-for-save-json', __( 'Directory for saving json files', 'ddbbd' ) )
					->option_name( $this->options->full_key( 'types_json_dir' ), [ &$this, 'save_json_dir' ] )
			;
		}
		*/
	}

	/**
	 *
	 */
	public function save_json_dir( $args ) {
		$key = $this->options->full_key( 'types_json_dir' );
		$pathPrefix = substr( WP_CONTENT_DIR, strlen( ABSPATH ) - 1 ) . '/';
		if ( ! $jsonDir = $this->options->get_types_json_dir() ) {
			$jsonDir = WP_CONTENT_DIR . '/json';
		}
		$value = substr( $jsonDir, strlen( WP_CONTENT_DIR ) + 1 );
		$class = 'regular-text';
		$disabled = '';
		if ( ! $this->options->get_export_types_as_json() ) {
			$class .= ' disabled';
			$disabled = ' disabled="disabled"';
		}
		//
?>
<label for="<?php esc_attr_e( $key ); ?>">
	<code>
		<?php echo $pathPrefix; ?>
	</code>
	<?php printf( '<input type="text" name="%1$s" id="%1$s" class="%2$s" value="%3$s"%4$s />', esc_attr( $key ), $class, esc_attr( $value ), $disabled ); ?>
</label>
<script>
	(function( $ ) {
		//
	} )( jQuery );
</script>
<?php
	}

}
