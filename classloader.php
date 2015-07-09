<?php
namespace DDBBD;

/**
 *
 */
class ClassLoader {

	/**
	 * @var string
	 */
	private $namespace;
	private $path;

	private $sep = '\\';

	/**
	 * @access public
	 */
	public static function register( $namespace, $path = null, $flags = 0 ) {
		$self = new self( $namespace, $path, $flags );
		$self->_autoload_register();
	}

	/**
	 * Constructor
	 *
	 * @access private
	 */
	private function __construct( $namespace, $path, $flags ) {
		$this->namespace = $namespace;
		if ( ! $path ) {
			$path  = trailingslashit( WP_PLUGIN_DIR );
			$path .= str_replace( '\\', '-', trim( $this->namespace, '\\' ) );
		}
		$this->path = trailingslashit( $path );
	}

	private function _autoload_register() {
		spl_autoload_register( [ &$this, 'loadClass' ] );
	}

	public function loadClass( $class ) {
		$sep = '\\';
		if ( $this->namespace . $sep !== substr( $class, 0, strlen( $this->namespace . $sep ) ) )
			return;
		//

		$class = substr( $class, strlen( $this->namespace ) + 1 );
		$file = '';
		if ( 0 < $lastNsPos = strripos( $class, $sep ) ) {
			$subNs = substr( $class, 0, $lastNsPos );
			$class = substr( $class, $lastNsPos + 1 );

			$file = str_replace( [ '_', $sep ], [ '-', '/' ], $subNs );
		}
		$file .= str_replace( '_', '-', $class ) . '.php';
		$file = $this->path . $file;

		if ( file_exists( $file ) )
			require_once $file;
	}

}
