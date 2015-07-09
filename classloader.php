<?php
namespace DDBBD;

class ClassLoader {

	private $namespace;
	private $path;

	private $sep = '\\';

	public static function register( $namespace ) {
		$self = new self( $namespace );
		$self->_autoload_register();
	}

	private function __construct( $namespace ) {
		$this->namespace = $namespace;
		$this->path  = trailingslashit( WP_PLUGIN_DIR );
		$this->path .= str_replace( '\\', '-', trim( $this->namespace, '\\' ) );
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
		$file = $this->path . '/' . $file;

		if ( file_exists( $file ) )
			require_once $file;
	}

}
