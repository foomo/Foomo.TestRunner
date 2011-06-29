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

namespace Foomo\TestRunner\Frontend;

use Foomo\TestRunner;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Controller
{
	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * @var Foomo\TestRunner\Frontend\Model
	 */
	public $model;

	//---------------------------------------------------------------------------------------------
	// ~ Action methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param boolean $details
	 */
	public function actionDefault($details=true)
	{
		$this->model->showTestCases = $details;
	}

	/**
	 * @param string $moduleName
	 */
	public function actionRunModuleTests($moduleName)
	{
		$this->model->runModule($moduleName);
	}

	/**
	 * @param string $name
	 */
	public function actionRunTest($name)
	{
		$this->model->runTest($name);
	}

	/**
	 *
	 */
	public function actionRunAll()
	{
		$this->model->runAll();
	}

	/**
	 * @param string $name
	 */
	public function actionRunSuite($name)
	{
		$this->model->runSuite($name);
	}

	/**
	 *
	 * @param string $suiteName
	 * @param string $caseName
	 */
	public function actionRunTestCase($suiteName, $caseName)
	{
		$this->model->runTestCase($suiteName, $caseName);
	}
}