<?php
namespace DDBBD\Types;

class Representation {

	/**
	 * Singleton pattern
	 *
	 * @uses DDBBD\Singleton
	 */
	use \DDBBD\Singleton;

	/**
	 * @var DDBBD\Options
	 */
	private $opt;

	/**
	 * @var array
	 */
	private $types;

	protected function __construct() {
		$this->opt = _ddbbd_options();
		if ( ! $this->types = $this->opt->get_types() )
			return;
		//
	}

}
