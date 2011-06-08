<?php

namespace Foomo\TestRunner;

use Foomo\Modules\ModuleBase;

/**
 * test runner for radact
 */
class Module extends ModuleBase {
	const NAME = 'Foomo.TestRunner';

	public static function initializeModule()
	{
		
	}

	public static function getDescription()
	{
		return 'brings phpUnit to radact';
	}
	/*
	public static function getIncludePaths()
	{
		return array(
			\Foomo\CORE_CONFIG_DIR_MODULES . DIRECTORY_SEPARATOR .
			self::NAME . DIRECTORY_SEPARATOR .
			'vendor' . DIRECTORY_SEPARATOR . 'phpUnit'
		);
	}
	*/
}