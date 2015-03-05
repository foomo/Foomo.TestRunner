<?php

/*
 * This file is part of the foomo Opensource Framework.
 *
 * The foomo Opensource Framework is free software: you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public License as
 * published  by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * The foomo Opensource Framework is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with
 * the foomo Opensource Framework. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Foomo\TestRunner;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Module extends \Foomo\Modules\ModuleBase implements \Foomo\Frontend\ToolboxInterface
{
	//---------------------------------------------------------------------------------------------
	// ~ Constants
	//---------------------------------------------------------------------------------------------

	const NAME = 'Foomo.TestRunner';

	const VERSION = '4.5.0';

	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 *
	 */
	public static function initializeModule()
	{

	}

	/**
	 * @return string
	 */
	public static function getDescription()
	{
		return 'PHPUnit integration';
	}
	/**
	 * get all the module resources
	 *
	 * @return \Foomo\Modules\Resource[]
	 */
	public static function getResources()
	{
		return array(
			\Foomo\Modules\Resource\Module::getResource('Foomo', '0.3.*'),
			\Foomo\Modules\Resource\ComposerPackage::getResource('phpunit/phpunit', '4.5.*'),
			\Foomo\Modules\Resource\ComposerPackage::getResource('phpunit/phpunit-selenium', '>=1.4.2')
		);
	}

	//---------------------------------------------------------------------------------------------
	// ~ Toolbox interface methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @internal
	 * @return array
	 */
	public static function getMenu()
	{
		return array(
			\Foomo\Frontend\ToolboxConfig\MenuEntry::create('Root.Modules.TestRunner', 'Test Runner', self::NAME, 'Foomo.TestRunner')
		);
	}

}