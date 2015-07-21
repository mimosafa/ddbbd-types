<?php
namespace DDBBD;

trait Options {

	/**
	 * Singleton pattern
	 *
	 * @uses DDBBD\Singleton
	 */
	use Singleton;

	/**
	 * Cache group name
	 * - If use WP_Cache API, define as '$cache_group'
	 *
	 * @var string
	 */

	/**
	 * Prefix of option keys
	 * - If necessary, define as '$prefix'
	 *
	 * @var string
	 */

	/**
	 * Option keys
	 * - You must define as '$keys'
	 * - Regexp: /[a-zA-Z0-9_]+/
	 * - e.g.:   private $keys = [ 'company_name', 'representative' ];
	 *
	 * @var array
	 */

	/**
	 * Option interface
	 *
	 * @access public
	 */
	public static function __callStatic( $name, $args ) {
		$self = self::getInstance();
		if ( ! property_exists( $self, 'keys' ) )
			return;

		if ( substr( $name, 0, 4 ) === 'get_' ) :
			/**
			 * @uses DDBBD\Options::get()
			 */
			array_unshift( $args, substr( $name, 4 ) );
			return call_user_func_array( [ $self, 'get' ], $args );

		elseif ( substr( $name, 0, 7 ) === 'update_' ) :
			/**
			 * @uses DDBBD\Options::update()
			 */
			array_unshift( $args, substr( $name, 7 ) );
			return call_user_func_array( [ $self, 'update' ], $args );

		elseif ( substr( $name, 0, 7 ) === 'delete_' ) :
			/**
			 * @uses DDBBD\Options::delete()
			 */
			return call_user_func_array( [ $self, 'delete' ], $args );

		endif;
	}

	/**
	 * @access public
	 *
	 * @param  string $key
	 */
	public static function full_key( $key = null ) {
		$self = self::getInstance();
		if ( ! property_exists( $self, 'keys' ) )
			return null;

		if ( is_string( $key ) && in_array( $key, $self->keys, true ) )
			return property_exists( $self, 'prefix' ) ? $self->prefix . $key : $key;

		if ( ! $key )
			if ( property_exists( $self, 'prefix' ) )
				return array_map( function( $key ) { return $this->prefix . $key; }, $self->keys );
			else
				return $self->keys;

		return null;
	}

	/**
	 * Constructor
	 *
	 * @access protected
	 */
	protected function __construct() {
		if ( ! property_exists( __CLASS__, 'keys' ) || ! is_array( $this->keys ) )
			return;
		if ( property_exists( __CLASS__, 'prefix' ) ) {
			$this->keys = apply_filters( $this->prefix . '_option_keys', $this->keys );
			add_filter( 'pre_update_option', [ &$this, '_pre_update_option' ], 10, 3 );
		}
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
		if ( ! in_array( $key, $this->keys, true ) )
			return null;
		if ( isset( $args[1] ) && filter_var( $args[1] ) )
			$key .= '_' . $args[1];

		if ( ! property_exists( __CLASS__, 'cache_group' ) ) {
			$option = property_exists( __CLASS__, 'prefix' ) ? $this->prefix . $key : $key;
			return get_option( $option, null );
		}

		if ( ! $value = wp_cache_get( $key, $this->cache_group ) ) {
			$option = property_exists( __CLASS__, 'prefix' ) ? $this->prefix . $key : $key;
			if ( $value = get_option( $option, null ) )
				wp_cache_set( $key, $value, $this->cache_group );
		}
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
		if ( $oldvalue === $newvalue )
			return false;

		$key .= isset( $subkey ) ? '_' . $subkey : '';

		if ( property_exists( __CLASS__, 'cache_group' ) )
			wp_cache_delete( $key, $this->cache_group );

		$option = property_exists( __CLASS__, 'prefix' ) ? $this->prefix . $key : $key;

		return update_option( $option, $newvalue );
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
		if ( ! in_array( $key, $this->keys, true ) )
			return null;
		if ( isset( $args[1] ) && filter_var( $args[1] ) )
			$key .= '_' . $args[1];

		if ( property_exists( __CLASS__, 'cache_group' ) )
			wp_cache_delete( $key, $this->cache_group );

		$option = property_exists( __CLASS__, 'prefix' ) ? $this->prefix . $key : $key;

		return delete_option( $option );
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
		if ( ! in_array( $key, $this->keys, true ) ) {
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
