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

use Foomo\MVC;
use Foomo\Config;

/**
 * run tests and offer heir results
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Model
{
	//---------------------------------------------------------------------------------------------
	// ~ Constants
	//---------------------------------------------------------------------------------------------

	const RENDER_MODE_HTML = 'html';
	const RENDER_MODE_TEXT = 'text';

	//---------------------------------------------------------------------------------------------
	// ~ Static variables
	//---------------------------------------------------------------------------------------------

	/**
	 * @internal
	 * @var array
	 */
	public static $errorBuffer;

	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * which module is currently under test
	 *
	 * @var string
	 */
	public $currentModuleTest;
	/**
	 * result of the last suite
	 *
	 * @var \Foomo\TestRunner\Result
	 */
	public $currentResult;
	/**
	 * show details in the menu or not
	 *
	 * @var boolean
	 */
	public $showTestCases = true;

    /**
     * @var TestRunner
     */
    public $testRunner;

    public function __construct()
    {
        $this->testRunner = new TestRunner();
        $this->currentResult = new \Foomo\TestRunner\Result();
    }

}