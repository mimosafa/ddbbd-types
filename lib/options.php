<?php
namespace DDBBD;

/**
 * Dana Don-Boom-Boom-Doo WP Options API interface
 *
 * @package    WordPress
 * @subpackage DDBBD
 * @author     Toshimichi Mimoto <mimosafa@gmail.com>
 */
class Options {

	/**
	 * Prefix of option keys
	 *
	 * @var string
	 */
	private $prefix;

	/**
	 * Cache group name
	 *
	 * @var string
	 */
	private $cache_group;

	/**
	 * Option keys
	 *
	 * @var array
	 */
	private $keys = [];

	/**
	 * @var array { @type DDBBD\Options }
	 */
	private static $instances = [];

	/**
	 * @access public
	 *
	 * @param  string $context
	 * @return DDBBD\Nonce
	 */
	public static function getInstance( $context ) {
		$regexp = [ 'options' => [ 'regexp' => '/\A[a-z0-9_]{1,62}\z/' ] ];
		if ( ! filter_var( $context, \FILTER_VALIDATE_REGEXP, $regexp ) )
			return;
		if ( ! isset( self::$instances[$context] ) )
			self::$instances[$context] = new self( $context );
		return self::$instances[$context];
	}

	/**
	 * Constructor
	 *
	 * @access private
	 */
	private function __construct( $context ) {
		$this->prefix = $context . '_';
		$this->cache_group = $this->prefix . 'cache_group';
		add_filter( 'pre_update_option', [ &$this, '_pre_update_option' ], 10, 3 );
	}

	/**
	 * Define option
	 *
	 * @access public
	 *
	 * @param  string          $option_name
	 * @param  string|callable $sanitize
	 * @return void
	 */
	public function def( $option_name, $filter = null ) {
		$regexp = [ 'options' => [ 'regexp' => '/\A[a-z0-9_]+\z/' ] ];
		if ( ! filter_var( $option_name, \FILTER_VALIDATE_REGEXP, $regexp ) )
			return;
		if ( strlen( $this->prefix . $option_name ) > 64 )
			return;

		if ( $filter ) {
			if ( is_string( $filter ) && method_exists( __CLASS__, 'option_filter_' . $filter ) )
				$filter_cb = [ &$this, 'option_filter_' . $filter ];
			else if ( is_callable( $filter ) )
				$filter_cb = $filter;
		}
		$this->keys[$option_name] = isset( $filter_cb ) ? $filter_cb : null;
	}

	/**
	 * Add filter triggered pre update option
	 *
	 * @access public
	 *
	 * @param  string   $key
	 * @param  callable $filter_function
	 * @param  int      $priority
	 * @return void
	 */
	public function pre_update_filter( $key, callable $filter_function, $priority = 10 ) {
		if ( array_key_exists( $key, $this->keys ) )
			add_filter( $this->prefix . 'options_pre_update_' . $key, $filter_function, $priority, 4 );
	}

	/**
	 * Interfaces - Overwrite methods
	 *
	 * @access public
	 *
	 * DDBBD\Options::verbose_{$key}() - Get full key string (stored in options table)
	 * @return string
	 *
	 * DDBBD\Options::get_{$key}( [ string $subkey ] ) - Get option's value
	 * @param  string $subkey Optional
	 * @return string
	 *
	 * DDBBD\Options::update_{$key}( [ string $subkey, ] mixed $value ) - Update option's value
	 * @param  string $subkey Optional
	 * @param  mixed  $value
	 * @return boolean
	 *
	 * DDBBD\Options::delete_{$key}( [ string $subkey ] ) - Delete option's value
	 * @param  string $subkey Optional
	 * @return boolean
	 */
	public function __call( $name, $args ) {
		if ( $this->keys && ( $sep = strpos( $name, '_' ) ) && $sep > 2 ) {
			$method = substr( $name, 0, $sep );
			if ( ! method_exists( __CLASS__, $method ) )
				return;
			if ( ! $option_name = substr( $name, $sep + 1 ) )
				return;
			array_unshift( $args, $option_name );

			return call_user_func_array( [ &$this, $method ], $args );
		}
	}

	/**
	 * Return raw option key (With prefix)
	 *
	 * @param  string $key
	 * @return string
	 */
	private function verbose( $key = null ) {
		if ( $this->keys ) {
			if ( ! $key )
				return array_map( function( $key ) { return $this->prefix . $key; }, array_keys( $this->keys ) );
			if ( is_string( $key ) && array_key_exists( $key, $this->keys ) )
				return $this->prefix . $key;
		}
		return null;
	}

	/**
	 * Option getter
	 * - If the option dose not exists, return null value
	 *
	 * @access private
	 *
	 * @param  string $key
	 * @param  string $subkey Optional
	 * @return mixed|null
	 */
	private function get() {
		$args = func_get_args();
		$key = $args[0];
		if ( ! array_key_exists( $key, $this->keys ) )
			return null;

		$subkey =  isset( $args[1] ) && filter_var( $args[1] ) ? $args[1] : null;
		$key .= $subkey ? '_' . $subkey : '';

		if ( ! $value = wp_cache_get( $key, $this->cache_group ) ) {
			if ( $value = get_option( $this->prefix . $key, null ) )
				wp_cache_set( $key, $value, $this->cache_group );
		}
		// for Test
		$value = apply_filters( $this->prefix . 'options_get_' . $args[0], $value, $subkey );
		return $value;
	}

	/**
	 * Option setter
	 *
	 * @access private
	 *
	 * @param  string $key
	 * @param  string $subkey   Optional
	 * @param  mixed  $newvalue
	 * @return boolean
	 */
	private function update() {
		$args = func_get_args();
		$key = $args[0];
		if ( count( $args ) > 2 && ! is_array( $args[1] ) && ! is_object( $args[1] ) ) {
			$newvalue = $args[2];
			$subkey = $args[1];
			$oldvalue = $this->get( $key, $subkey );
		} else {
			$newvalue = $args[1];
			$oldvalue = $this->get( $key );
		}
		if ( $filter = $this->keys[$key] ) {
			$newvalue = call_user_func( $filter, $newvalue );
			if ( ! isset( $newvalue ) )
				return null;
		}
		if ( $oldvalue === $newvalue )
			return false;

		$key .= isset( $subkey ) ? '_' . $subkey : '';

		wp_cache_delete( $key, $this->cache_group );

		return update_option( $this->prefix . $key, $newvalue );
	}

	/**
	 * Deleter
	 *
	 * @access private
	 *
	 * @param  string $key
	 * @param  string $subkey Optional
	 * @return
	 */
	private function delete() {
		$args = func_get_args();
		$key = $args[0];
		if ( ! array_key_exists( $key, $this->keys ) )
			return null;
		if ( isset( $args[1] ) && filter_var( $args[1] ) )
			$key .= '_' . $args[1];

		wp_cache_delete( $key, $this->cache_group );

		return delete_option( $this->prefix . $key );
	}

	/**
	 * Sanitize filter functions
	 *
	 * @access private
	 *
	 * @param  unknown $var
	 * @return mixed|null
	 */
	private function option_filter_default( $var ) {
		$var = filter_var( $var );
		return $var ?: null;
	}
	private function option_filter_boolean( $var ) {
		return filter_var( $var, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE );
	}

	/**
	 * @access private
	 *
	 * @see https://github.com/WordPress/WordPress/blob/4.2-branch/wp-includes/option.php#L270
	 *
	 * @param mixed  $value     The new, unserialized option value.
	 * @param string $option    Name of the option.
	 * @param mixed  $old_value The old option value.
	 */
	public function _pre_update_option( $value, $option, $old_value ) {
		if ( $this->prefix !== substr( $option, 0, strlen( $this->prefix ) ) )
			return $value;

		$key = substr( $option, strlen( $this->prefix ) );
		$subkey = '';
		if ( ! array_key_exists( $key, $this->keys ) ) {
			foreach ( $this->keys as $string ) {
				if ( $string . '_' === substr( $key, 0, strlen( $string ) + 1 ) ) {
					$key = $string;
					$subkey = substr( $key, strlen( $string ) + 2 );
					break;
				}
			}
			if ( ! $subkey )
				return $value;
		}

		/**
		 * Filter Hook: {$prefix}_options_pre_update_{$key}
		 *
		 * @param  mixed  $value
		 * @param  mixed  $old_value
		 * @param  string $subkey
		 * @return mixed  $value
		 */
		return apply_filters( $this->prefix . 'options_pre_update_' . $key, $value, $old_value, $subkey );
	}

}
