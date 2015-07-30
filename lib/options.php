<?php
namespace DDBBD;

/**
 * Dana Don-Boom-Boom-Doo WP Options API interface
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
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct( $prefix ) {
		$regexp = [ 'regexp' => '/\A[a-zA-Z0-9][a-zA-Z0-9_]*_\z/' ];
		if ( ! $prefix = filter_var( $prefix, \FILTER_VALIDATE_REGEXP, [ 'options' => $regexp ] ) )
			return;

		$this->prefix = $prefix;
		$this->cache_group = $prefix . 'cache_group';

		add_filter( 'pre_update_option', [ &$this, '_pre_update_option' ], 10, 3 );
	}

	/**
	 * @access public
	 *
	 * @param  string $option_name
	 * @param  string|callable $sanitize
	 * @return void
	 */
	public function add( $option_name, $filter = null ) {
		$regexp = [ 'regexp' => '/\A[a-z0-9_]+\z/' ];
		if ( ! $option_name = filter_var( $option_name, \FILTER_VALIDATE_REGEXP, [ 'options' => $regexp ] ) )
			return;
		if ( $filter ) {
			if ( method_exists( __CLASS__, 'option_filter_' . $filter ) )
				$filter_cb = [ &$this, 'option_filter_' . $filter ];
			else if ( is_callable( $filter ) )
				$filter_cb = $filter;
		}
		$this->keys[$option_name] = isset( $filter_cb ) ? $filter_cb : null;
	}

	/**
	 * Option interface
	 *
	 * @access public
	 */
	public function __call( $name, $args ) {
		if ( ! $this->keys )
			return;

		if ( substr( $name, 0, 4 ) === 'get_' ) :
			/**
			 * @uses DDBBD\Options::get()
			 */
			array_unshift( $args, substr( $name, 4 ) );
			return call_user_func_array( [ &$this, 'get' ], $args );

		elseif ( substr( $name, 0, 7 ) === 'update_' ) :
			/**
			 * @uses DDBBD\Options::update()
			 */
			array_unshift( $args, substr( $name, 7 ) );
			return call_user_func_array( [ &$this, 'update' ], $args );

		elseif ( substr( $name, 0, 7 ) === 'delete_' ) :
			/**
			 * @uses DDBBD\Options::delete()
			 */
			return call_user_func_array( [ &$this, 'delete' ], $args );

		endif;
	}

	/**
	 * @access public
	 *
	 * @param  string $key
	 */
	public function full_key( $key = null ) {
		if ( ! $this->keys )
			return null;

		if ( is_string( $key ) && array_key_exists( $key, $this->keys ) )
			return $this->prefix . $key;

		if ( ! $key )
			return array_map( function( $key ) { return $this->prefix . $key; }, array_keys( $this->keys ) );

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
		return apply_filters( $this->prefix . 'options_pre_update_' . $key, $value, $old_value, $subkey );
	}

}
