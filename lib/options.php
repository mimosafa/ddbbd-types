<?php
namespace DDBBD;

class Options {

	/**
	 * Singleton pattern
	 *
	 * @uses DDBBD\Singleton
	 */
	use Singleton;

	/**
	 * Cache group name
	 *
	 * @var string
	 */
	private $group = 'ddbbd_caches';

	/**
	 * Prefix of option keys
	 *
	 * @var string
	 */
	private $prefix = 'ddbbd_';

	/**
	 * Option keys
	 *
	 * @var array
	 */
	private $keys = [
		/**
		 * Custom content types data
		 */
		'domains',
	];

	/**
	 * Option interface (Getter/Setter)
	 *
	 * @access public
	 */
	public static function __callStatic( $name, $args ) {
		$self = self::getInstance();

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

		endif;
	}

	/**
	 * Constructor
	 *
	 * @access protected
	 */
	protected function __construct() {
		$this->keys = apply_filters( 'ddbbd_options_keys', $this->keys );
		add_filter( 'pre_update_option', [ &$this, '_pre_update_option' ], 10, 3 );
	}

	/**
	 * Option getter
	 *
	 * @access private
	 *
	 * @param  string $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	private function get( $key, $default = false ) {
		if ( ! $value = wp_cache_get( $key, $this->group ) ) {
			if ( ! in_array( $key, $this->keys, true ) )
				return $default;
			$value = get_option( $this->prefix . $key, $default );
			wp_cache_set( $key, $value, $this->group );
		}
		return $value;
	}

	/**
	 * Option setter
	 *
	 * @access private
	 *
	 * @param  string $key
	 * @param  mixed  $newvalue
	 * @return boolean
	 */
	private function update( $key, $newvalue ) {
		if ( $this->get( $key ) === $newvalue )
			return false;
		wp_cache_delete( $key, $this->group );
		return update_option( $this->prefix . $key, $newvalue );
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
		if ( ! in_array( $key, $this->keys, true ) )
			return $value;

		return apply_filters( 'ddbbd_options_pre_update_' . $key, $value, $old_value );
	}

}
