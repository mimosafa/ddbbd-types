<?php
namespace DDBBD;

/**
 * Singleton trait
 *
 * @see http://php.net/manual/ja/language.oop5.traits.php#108293
 */
trait Singleton {

	public static function getInstance()
	{
		static $instance = null;
		$class = __CLASS__;
		return $instance ?: $instance = new $class();
	}

	private function __constract()
	{
		// Do nothing ( Overwrite, if Necessary )
	}

	public function __clone()
	{
		// Do nothing
	}

	public function __wakeup()
	{
		// Do nothing
	}

}
