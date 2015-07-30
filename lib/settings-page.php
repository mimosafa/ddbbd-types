<?php
namespace DDBBD;

/**
 * WordPress Settings API interface
 *
 * Usage:
 * - Get instance <code>$instance = new \WP_Domain_Work\WP\admin\plugin\settings_page();</code>
 * - Initialize with page sulug <code>$instance->init( 'my-plugin' );</code>
 *   - You can also set page title & menu title by this method
 * ...
 *
 * **CAUTION** This class methods print basically non-escape text
 *
 * @access private
 *
 * @package    WordPress
 * @subpackage DDBBD
 *
 * @author mimosafa <mimosafa@gmail.com>
 */
class Settings_Page {

	/**
	 * DDBBD Options instance
	 *
	 * @var DDBBD\Options
	 */
	private $options;

	/**
	 * Top level page
	 *
	 * @var string
	 */
	private $toplevel;

	/**
	 * Pages structure argument
	 *
	 * @var array
	 */
	private $pages = [];

	/**
	 * Argument caches
	 *
	 * @var array
	 */
	private static $page;
	private static $section;
	private static $field;

	/**
	 * Arguments of '_add_settings' method 
	 * 
	 * @var array
	 */
	private $sections = [];
	private $fields   = [];
	private $settings = [];

	/**
	 * Arguments of callback functions
	 *
	 * @var array
	 */
	private static $callback_args = [];

	#private static $falseVal = false;

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @param  string $page       Optional
	 * @param  string $page_title Optional
	 * @param  string $menu_title Optional
	 */
	public function __construct( $page = null, $page_title = null, $menu_title = null ) {
		self::$page = self::$section = self::$field = [];
		$this->init( $page, $page_title, $menu_title );
	}

	/**
	 *
	 */
	public function set_options( Options $options ) {
		$this->options = $options;
	}

	/**
	 * Initialize instance
	 *
	 * @access public
	 *
	 * @param  string $page       Optional
	 * @param  string $page_title Optional
	 * @param  string $menu_title Optional
	 * @return WPDW\WP\settings_page
	 */
	public function init( $page = null, $page_title = null, $menu_title = null ) {
		$this->_init_page();
		if ( $page !== null )
			$this->_page( $page, $page_title, $menu_title );
		return $this;
	}

	/**
	 * Output settings page
	 *
	 * @access public
	 *
	 * @return (void)
	 */
	public function done() {
		$this->init();
		if ( $this->pages ) {
			add_action( 'admin_menu', [ &$this, '_add_pages' ] );
			add_action( 'admin_init', [ &$this, '_add_settings' ] );
		}
	}

	/**
	 * Initialize static cache $page
	 *
	 * @access private
	 */
	private function _init_page() {
		$this->_init_section();
		if ( ! empty( self::$page ) )
			$this->pages[] = self::$page;
		self::$page = [];
	}

	/**
	 * Initialize static cache $section
	 *
	 * @access private
	 */
	private function _init_section() {
		$this->_init_field();
		if ( ! empty( self::$section ) ) {
			if ( self::$page ) {
				if ( ! array_key_exists( 'sections', self::$page ) )
					self::$page['sections'] = [];
				self::$page['sections'][] = self::$section;
			}
		}
		self::$section = [];
	}

	/**
	 * Initialize static cache $field
	 *
	 * @access private
	 */
	private function _init_field() {
		if ( ! empty( self::$field ) ) {
			if ( self::$section ) {
				if ( ! array_key_exists( 'fields', self::$section ) )
					self::$section['fields'] = [];
				self::$section['fields'][] = self::$field;
			} else if ( self::$page ) {
				if ( ! array_key_exists( 'fields', self::$page ) )
					self::$page['fields'] = [];
				self::$page['fields'][] = self::$field;
			}
		}
		self::$field = [];
	}

	/**
	 * Add pages (run in action hook 'admin_menu')
	 *
	 * @access private
	 */
	public function _add_pages() {
		if ( ! doing_action( 'admin_menu' ) || ! $this->pages )
			return;
		foreach ( $this->pages as $page ) {
			$this->_add_page( $page );
		}

		do_action( '_ddbbd_settings_page_added_pages' );
	}

	/**
	 * Add page
	 *
	 * @access private
	 *
	 * @global array $admin_page_hook
	 *
	 * @param  array  $page_arg
	 * @param  string $toplevel
	 * @return (void)
	 */
	private function _add_page( $page_arg ) {
		if ( ! array_key_exists( 'page', $page_arg ) )
			return;

		global $admin_page_hooks;
		extract( $page_arg ); // $page must be generated.

		/**
		 * Avoid duplicate page body display
		 */
		if ( array_key_exists( $page, $admin_page_hooks ) )
			return;

		if ( ! isset( $title ) ) {
			$title = ucwords( trim( str_replace( [ '-', '_', '/', '.php' ], ' ', $page ) ) );
			$page_arg['title'] = $title;
		}
		if ( ! isset( $menu_title ) ) {
			$menu_title = $title;
			$page_arg['menu_title'] = $menu_title;
		}
		if ( ! isset( $capability ) ) {
			$capability = 'manage_options';
			$page_arg['capability'] = $capability;
		}

		if ( ! isset( $callback ) ) {
			if ( isset( $sections ) || isset( $fields ) || isset( $html ) || isset( $description ) ) {
				$callback = [ &$this, 'page_body' ];
			} else if ( $page === $this->toplevel && count( $this->pages ) > 1 ) {
				$callback = '';
				// Remove submenu
				add_action( '_ddbbd_settings_page_added_pages', function() {
					remove_submenu_page( $this->toplevel, $this->toplevel );
				} );
			} else {
				$callback = [ &$this, 'empty_page' ];
			}
		}
		else
			unset( $page_arg['callback'] ); // Optimize vars

		if ( $page === $this->toplevel && ! array_key_exists( $page, $admin_page_hooks ) ) {

			if ( ! isset( $icon_url ) )
				$icon_url = '';

			if ( ! isset( $position ) )
				$position = null;

			/**
			 * Add as top level page
			 */
			add_menu_page( $title, $menu_title, $capability, $page, $callback, $icon_url, $position );

		} else {
			/**
			 * Add as sub page
			 */
			add_submenu_page( $this->toplevel, $title, $menu_title, $capability, $page, $callback );
		}

		/**
		 * Sections
		 */
		if ( isset( $sections ) && $sections ) {
			foreach ( $sections as $section ) {
				$this->_add_section( $section, $page );
			}
			unset( $page_arg['sections'] ); // Optimize vars
		}

		/**
		 * fields
		 */
		if ( isset( $fields ) && $fields ) {
			foreach ( $fields as $field ) {
				$this->_add_field( $field, $page );
			}
			unset( $page_arg['fields'] ); // Optimize vars
		}

		/**
		 * Cache argument for callback method
		 */
		$argsKey = 'page_' . $page;
		self::$callback_args[$argsKey] = $page_arg;
	}

	/**
	 * Add section
	 *
	 * @access private
	 *
	 * @param  array $section
	 * @param  string $menu_slug
	 * @return (void)
	 */
	private function _add_section( $section, $menu_slug ) {
		if ( ! array_key_exists( 'id', $section ) )
			return;

		extract( $section ); // $id must be generated

		if ( ! isset( $title ) )
			$title = null;

		if ( ! isset( $callback ) )
			$callback = [ &$this, 'section_body' ];
		else
			unset( $section['callback'] ); // Optimize vars

		$this->sections[] = [ $id, $title, $callback, $menu_slug ];

		/**
		 * fields
		 */
		if ( isset( $fields ) && $fields ) {
			foreach ( $fields as $field ) {
				$this->_add_field( $field, $menu_slug, $id );
			}
			unset( $section['fields'] ); // Optimize vars
		}

		/**
		 * Cache argument for callback method
		 */
		$argsKey = 'section_' . $id;
		self::$callback_args[$argsKey] = $section;
	}

	/**
	 * Add & set field
	 *
	 * @access private
	 *
	 * @param  array $field
	 * @param  string $menu_slug
	 * @param  string $section_id (optional)
	 * @return (void)
	 */
	private function _add_field( $field, $menu_slug, $section_id = '' ) {
		if ( ! array_key_exists( 'id', $field ) )
			return;

		extract( $field ); // $id must be generated

		if ( ! isset( $title ) ) {
			$title = ucwords( str_replace( [ '-', '_' ], ' ', $id ) );
			$field['title'] = $title;
		}
		if ( ! isset( $callback ) )
			$callback = [ &$this, 'field_body' ];
		else
			unset( $field['callback'] ); // Optimize vars

		if ( isset( $option_name ) ) {
			$option_group = 'group_' . $menu_slug;
			if ( ! isset( $sanitize ) || ( ! method_exists( __CLASS__, $sanitize ) && ! is_callable( $sanitize ) ) )
				$sanitize = '';
			else if ( isset( $sanitize ) )
				unset( $field['sanitize'] ); // Optimize vars

			$this->settings[] = [ $option_group, $field['option_name'], $sanitize ];
		}

		$this->fields[] = [ $id, $title, $callback, $menu_slug, $section_id, $field ]; // $field is argument for callback method
	}

	/**
	 * Setting sections & fields method (run in action hook 'admin_init')
	 *
	 * @access private
	 */
	public function _add_settings() {
		if ( ! doing_action( 'admin_init' ) || ! $this->pages )
			return;

		foreach ( $this->sections as $section_arg ) {
			call_user_func_array( 'add_settings_section', $section_arg );
		}
		foreach ( $this->fields as $field_arg ) {
			call_user_func_array( 'add_settings_field', $field_arg );
		}
		foreach ( $this->settings as $setting_arg ) {
			call_user_func_array( 'register_setting', $setting_arg );
		}
	}

	/**
	 * Set page
	 *
	 * @access private
	 *
	 * @param  string $page (optional) if empty 'options.php' set
	 * @param  string $page_title (optional)
	 * @param  string $menu_title (optional)
	 * @return (void)
	 */
	private function _page( $page = null, $page_title = null, $menu_title = null ) {
		if ( $page === null && ! $this->toplevel )
			$page = 'options-general.php';

		if ( ! $page = filter_var( $page ) )
			return;

		self::$page['page'] = $page;
		if ( ! $this->toplevel )
			$this->toplevel = $page;

		if ( $page_title )
			$this->title( $page_title );

		if ( $menu_title )
			$this->menu_title( $menu_title );
	}

	/**
	 * Set section
	 *
	 * @access public
	 *
	 * @param  string $section_id (required)
	 * @param  string $section_title (optional) if blank, string made from section_id. if want to hide set empty string ''.
	 * @return WPDW\WP\settings_page
	 */
	public function section( $section_id, $section_title = null ) {
		$this->_init_section();

		if ( ! $section_id = filter_var( $section_id ) )
			return;

		self::$section['id'] = $section_id;

		if ( null !== $section_title && $section_title = filter_var( $section_title ) )
			self::$section['title'] = $section_title;

		return $this;
	}

	/**
	 * Set field (for only field)
	 *
	 * @access public
	 *
	 * @return WPDW\WP\settings_page
	 */
	public function field( $field_id, $field_title = null ) {
		$this->_init_field();

		if ( ! $field_id = filter_var( $field_id ) )
			return;

		self::$field['id'] = $field_id;

		if ( $field_title = filter_var( $field_title ) )
			self::$field['title'] = $field_title;

		return $this;
	}

	/**
	 * Set field's option & callback (for only field)
	 *
	 * @access public
	 *
	 * @return WPDW\WP\settings_page
	 */
	public function option_name( $option_name, $callback, $callback_args = [] ) {
		if ( ! self::$field || ! $option_name = filter_var( $option_name ) )
			return; // Error

		if ( ! $this->callback( $callback, $callback_args ) )
			return;

		self::$field['option_name'] = $option_name;

		if ( ! isset( self::$page['has_option_field'] ) )
			self::$page['has_option_fields'] = true;

		return $this;
	}

	/**
	 * Set title (for page, section, field)
	 *
	 * @access public
	 *
	 * @param  string $title
	 * @return WPDW\WP\settings_page
	 */
	public function title( $title ) {
		if ( ! $title = filter_var( $title ) )
			return;

		if ( ! $cache =& $this->get_cache() )
			return;

		$cache['title'] = $title;
		return $this;
	}

	/**
	 * Set menu title (for page only)
	 *
	 * @access public
	 *
	 * @param  string $title
	 * @return WPDW\WP\settings_page
	 */
	public function menu_title( $menu_title ) {
		if ( self::$page && $menu_title = filter_var( $menu_title ) )
			self::$page['menu_title'] = $menu_title;

		return $this;
	}

	/**
	 * Set capability
	 *
	 * @access public
	 *
	 * @param  string $capability
	 * @return WPDW\WP\settings_page
	 */
	public function capability( $capability ) {
		if ( self::$page && $capability = filter_var( $capability ) )
			self::$page['capability'] = $capability;

		return $this;
	}

	/**
	 * Set icon_url (for only top level page)
	 *
	 * @param  string $icon_url
	 * @return WPDW\WP\settings_page
	 */
	public function icon_url( $icon_url ) {
		if ( self::$page && $icon_url = filter_var( $icon_url ) )
			self::$page['icon_url'] = $icon_url;

		return $this;
	}

	/**
	 * Set position in admin menu (for only top level page)
	 *
	 * @param  integer $position
	 * @return WPDW\WP\settings_page
	 */
	public function position( $position ) {
		if ( self::$page && $position = filter_var( $position, \FILTER_VALIDATE_INT, [ 'options' => [ 'min_range' => 1 ] ] ) )
			self::$page['position'] = $position;

		return $this;
	}

	/**
	 * Set description text
	 * Description will be contained initialized cache(field, section, page) before calling this method
	 *
	 * @access public
	 *
	 * @param  string $text
	 * @param  bool   $wrap_p
	 * @return WPDW\WP\settings_page|(void)
	 */
	public function description( $text ) {
		if ( ! $text = filter_var( $text ) )
			return;

		if ( ! $cache =& $this->get_cache() )
			return;

		$format = '<p class="description">%s</p>';
		if ( ! array_key_exists( 'description', $cache ) ) {
			$cache['description'] = sprintf( $format, $text );
		} else {
			$format = "\n{$format}";
			$cache['description'] .= sprintf( $format, $text );
		}
		return $this;
	}

	/**
	 * Set html contents
	 *
	 * @access public
	 *
	 * @param  string $html
	 * @param  boolean $wrap_div (optional) if true wrap $html by 'div'
	 * @return WPDW\WP\settings_page|(void)
	 */
	public function html( $html, $wrap_div = false ) {
		if ( ! $html = filter_var( $html ) )
			return;

		if ( ! $cache =& $this->get_cache() )
			return;

		$format = $wrap_div ? '<div>%s</div>' : '%s';

		if ( ! array_key_exists( 'html', $cache ) ) {
			$cache['html'] = sprintf( $format, $html );
		} else {
			$format = "\n{$format}";
			$cache['html'] .= sprintf( $format, $html );
		}
		return $this;
	}

	/**
	 * Set callback function
	 * Callback will be contained initialized cache(field, section, page) before calling this method
	 *
	 * @access public
	 *
	 * @param  string $callback
	 * @return WPDW\WP\settings_page|(void)
	 */
	public function callback( $callback, $callback_args = [] ) {
		if ( ! is_callable( $callback ) && ( ! is_string( $callback ) || ! method_exists( __CLASS__, $callback ) ) )
			return;

		if ( ! $cache =& $this->get_cache() )
			return;

		$cache['callback'] = is_callable( $callback ) ? $callback : [ &$this, $callback ];
		if ( $callback_args ) {
			foreach ( $callback_args as $key => $val ) {
				if ( ! array_key_exists( $key, $cache ) )
					$cache[$key] = $val;
			}
		}
		return $this;
	}

	/**
	 *
	 */
	public function file( $path, $args = [], $wrap = false ) {
		if ( ! self::$page || ! $path = realpath( $path ) )
			return;
		self::$page['callback'] = [ &$this, 'include_file' ];
		self::$page['file_path'] = $path;
		self::$page['include_file_args'] = $args;
		self::$page['wrap_included_file'] = filter_var( $wrap, \FILTER_VALIDATE_BOOLEAN );
		return $this;
	}

	/**
	 * Set submit button ---- yet !!
	 *
	 * @todo
	 *
	 * @access public
	 *
	 * @param  string $text
	 * @return WPDW\WP\settings_page
	 */
	public function submit_button( $text ) {
		//
		return $this;
	}

	/**
	 * Return var references cache
	 *
	 * @access private
	 *
	 * @return &array|false
	 */
	private function &get_cache() {
		static $falseVal = false;

		if ( self::$field )
			return self::$field;
		else if ( self::$section )
			return self::$section;
		else if ( self::$page )
			return self::$page;

		return $falseVal;
	}

	/**
	 * Drow default page html (if has form)
	 * 
	 * @return (void)
	 */
	public function page_body() {
		$menu_slug = filter_input( \INPUT_GET, 'page' );
		if ( ! $arg = self::$callback_args['page_' . $menu_slug] )
			return;

		echo '<div class="wrap">';
		echo "<h2>{$arg['title']}</h2>";

		if ( isset( $arg['has_option_fields'] ) ) {
			/**
			 * @see http://wpcj.net/354
			 */
			global $parent_file;
			if ( $parent_file !== 'options-general.php' )
				require ABSPATH . 'wp-admin/options-head.php';
		}

		echo isset( $arg['description'] ) ? $arg['description'] : '';
		echo isset( $arg['html'] ) ? $arg['html'] : '';

		if ( isset( $arg['has_option_fields'] ) ) {
			echo '<form method="post" action="options.php">';
			settings_fields( 'group_' . $menu_slug );
		}
		
		do_settings_fields( $menu_slug, '' );
		do_settings_sections( $menu_slug );

		if ( isset( $arg['has_option_fields'] ) ) {
			submit_button();
			echo '</form>';
		}

		echo '</div>';
	}

	/**
	 *
	 */
	public function include_file() {
		$menu_slug = filter_input( \INPUT_GET, 'page' );
		$args = self::$callback_args['page_' . $menu_slug];
		$path = $args['file_path'];
		$wrap = $args['wrap_included_file'];
		$title = $wrap && isset( $args['title'] ) ? $args['title'] : '';
		if ( $args = self::$callback_args['page_' . $menu_slug]['include_file_args'] )
			extract( $args );
		echo $wrap ? '<div class="wrap">' : '';
		echo $title ? '<h2>' . $title . '</h2>' : '';
		include $path;
		echo $wrap ? '</div>' : '';
	}

	public function empty_page() {
		$menu_slug = filter_input( \INPUT_GET, 'page' );
		do_action( 'ddbbd_settings_page_empty_page_' . $menu_slug );
	}

	/**
	 * @param  array $array
	 */
	public function section_body( $array ) {
		$arg = self::$callback_args['section_' . $array['id']];
		echo isset( $arg['description'] ) ? $arg['description'] : '';
		echo isset( $arg['html'] ) ? $arg['html'] : '';
	}

	/**
	 * 
	 */
	public function field_body( $arg ) {
		echo isset( $arg['description'] ) ? $arg['description'] : '';
		echo isset( $arg['html'] ) ? $arg['html'] : '';
	}

	/**
	 *
	 */
	public function checkbox( $args ) {
		if ( ! isset( $args['option_name'] ) )
			return;

		$option = esc_attr( $args['option_name'] );
		$checked = \get_option( $option ) ? 'checked="checked" ' : '';
		$label = isset( $args['label'] ) ? $args['label'] : '';
?>
<label for="<?php echo $option; ?>">
	<input type="checkbox" name="<?php echo $option; ?>" id="<?php echo $option; ?>" value="1" <?php echo $checked ?>/>
	<?php echo $label; ?>
</label>
<?php
		if ( isset( $args['description'] ) )
			echo $args['description'];
	}

	/**
	 *
	 */
	public function text( $args ) {
		if ( ! isset( $args['option_name'] ) )
			return;

		$option = esc_attr( $args['option_name'] );
?>
<input type="text" name="<?php echo $option; ?>" id="<?php echo $option; ?>" value="" class="regular-text" />
<?php
		if ( isset( $args['description'] ) )
			echo $args['description'];
	}

	//

	/**
	 * @access public
	 *
	 * @return boolean
	 */
	public function toplevel_exists() {
		return !! $this->toplevel;
	}

	public static function current_cached_page() {
		if ( self::$page )
			return self::$page['page'];
		return null;
	}

}
