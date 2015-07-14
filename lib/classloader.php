<?php
namespace DDBBD;

/**
 * Dana Don-Boom-Boom-Doo ClassLoader
 *
 * @package DDBBD
 * @author  Toshimichi Mimoto
 */
class ClassLoader {

	/**
	 * @var string
	 */
	private $namespace;
	private $path;

	/**
	 * Namespace separater
	 */
	private $nsSep = '\\';

	/**
	 * ClassLoader Options
	 *
	 * - Hyphenate Classname
	 * - e.g.
	 *   Name_space\Class_Name
	 *   => Name_space/Class-Name.php
	 *
	 * @var boolean
	 */
	private $cnHypenate = true;
	/**
	 * - Hyphenate Namespace
	 * - e.g.
	 *   Name_space\Class_Name
	 *   => Name-space/Class_Name.php
	 *
	 * @var boolean
	 */
	private $nsHyphenate = true;
	/**
	 * - Decamelize Classname
	 * - e.g.
	 *   NameSpace\ClassName.php
	 *   => NameSpace/Class_Name.php
	 *
	 * @todo
	 *
	 * @var boolean
	 */
	private $cnDecamelize = false;
	/**
	 * - Decamelize Namespace
	 * - e.g.
	 *   NameSpace\ClassName.php
	 *   => Name_Space/ClassName.php
	 *
	 * @todo
	 *
	 * @var boolean
	 */
	private $nsDecamelize = false;
	/**
	 * - Add prefix to filename
	 * - e.g.
	 *   Namespace\ClassName
	 *   => Namespace/class-ClassName.php
	 *
	 * @todo
	 *
	 * @var string
	 */
	private $filePrefix = '';

	/**
	 * Interface of registering ClassLoader
	 *
	 * @access public
	 *
	 * @param  string $namespace
	 * @param  string $path
	 * @param  null|array $options Optional
	 */
	public static function register( $namespace, $path, $options = null ) {
		$self = new self( $namespace, $path, $options );
		if ( $self->path )
			$self->_autoload_register();
	}

	/**
	 * Constructor
	 *
	 * @access private
	 *
	 * @param  string $namespace
	 * @param  string $path
	 * @param  null|array $options
	 */
	private function __construct( $namespace, $path, $options ) {
		if ( ! filter_var( $namespace ) || ! $path = realpath( $path ) )
			return;
		$this->namespace = $namespace;
		$this->path = rtrim( $path, '/' ) . '/';

		if ( is_array( $options ) && $options )
			$this->_set_options( $options );
	}

	/**
	 * @access private
	 *
	 * @param  array $options {
	 *     @type boolean $hyphenate_classname
	 *     @type boolean $hyphenate_namespace
	 *     @type boolean $decamelize_classname
	 *     @type boolean $decamelize_namespace
	 * }
	 */
	private function _set_options( Array $options ) {
		static $def;
		if ( ! $def ) {
			$boolFilter = [ 'filter' => \FILTER_VALIDATE_BOOLEAN, 'flags' => \FILTER_NULL_ON_FAILURE ];
			$def = [
				'hyphenate_classname'  => $boolFilter,
				'hyphenate_namespace'  => $boolFilter,
				'decamelize_classname' => $boolFilter,
				'decamelize_namespace' => $boolFilter,
			];
		}
		$options = filter_var_array( $options, $def );
		extract( $options );
		
		if ( $hyphenate_classname === false )
			$this->cnHypenate = false;
		
		if ( $hyphenate_namespace === false )
			$this->nsHyphenate = false;

		if ( isset( $decamelize_classname ) )
			$this->cnDecamelize = $decamelize_classname;

		if ( isset( $decamelize_namespace ) )
			$this->nsDecamelize = $decamelize_namespace;
	}

	/**
	 * Autoloader register
	 *
	 * @access private
	 */
	private function _autoload_register() {
		spl_autoload_register( [ &$this, 'loadClass' ] );
	}

	/**
	 * Autoloader
	 */
	public function loadClass( $class ) {
		$sep = $this->nsSep;
		if ( $this->namespace . $sep !== substr( $class, 0, strlen( $this->namespace . $sep ) ) )
			return;

		$class = substr( $class, strlen( $this->namespace ) + 1 );

		$file = '';
		if ( 0 < $lastNsPos = strripos( $class, $sep ) ) {
			$subNs = substr( $class, 0, $lastNsPos );
			$class = substr( $class, $lastNsPos + 1 );

			if ( $this->nsHyphenate )
				list( $search, $replace ) = [ [ '_', $sep ], [ '-', '/' ] ];
			else
				list( $search, $replace ) = [ $sep, '/' ];

			$file = str_replace( $search, $replace, $subNs ) . '/';
		}
		
		$file .= $this->cnHypenate ? str_replace( '_', '-', $class ) : $class;
		$file .= '.php';
		$file  = $this->path . $file;

		if ( file_exists( $file ) )
			require_once $file;
	}

}
