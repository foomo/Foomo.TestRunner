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

namespace Foomo\TestRunner\VerbosePrinter;

use Foomo\TestRunner\AbstractSpec;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
abstract class AbstractPrinter
{
	//---------------------------------------------------------------------------------------------
	// ~ Abstract methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @return string
	 */
	abstract function startOutput();

	/**
	 * @param Foomo\TestRunner\Result $result
	 * @return string
	 */
	abstract function printResult(\Foomo\TestRunner\Result $result);

	//---------------------------------------------------------------------------------------------
	// ~ Protected methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $line
	 * @return boolean
	 */
	protected function isStoryLine($line)
	{
		$isStoryLine = false;
		foreach(array('when', 'then', 'given') as $storyKeyword) {
			if(strpos($line, $storyKeyword) == $storyKeyword) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param string $name
	 * @return boolean
	 */
	protected function suiteExists($name)
	{
		foreach(\Foomo\Modules\Manager::getEnabledModules() as $enabledModuleName) {
			foreach($suites = $this->model->getModuleSuites($enabledModuleName) as $suite) {
				if($suite == $name) {
					return true;
				}
			}
			//var_dump($name, $suites);
		}
		return false;
	}

	/**
	 * @param string $name
	 * @return boolean
	 */
	protected function testExists($name)
	{
		foreach(\Foomo\Modules\Manager::getEnabledModules() as $enabledModuleName) {
			foreach($tests = array_merge($this->model->getModuleTests($enabledModuleName), $this->model->getModuleSpecs($enabledModuleName)) as $test) {
				if($test == $name) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 *
	 * @param PHPUnit_Framework_Test $test
	 * @return boolean
	 */
	protected function isSpec(\PHPUnit_Framework_Test $test)
	{
		return ($test instanceof AbstractSpec);
	}
}