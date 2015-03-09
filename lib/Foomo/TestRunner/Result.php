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
 * result of a suite, that was run
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Result
{
	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * name of the test suite class
	 *
	 * @var string
	 */
	public $name = '';
	/**
	 *
	 * @var \PHPUnit_Framework_TestResult
	 */
	public $result;
	/**
	 *
	 * @var \Exception
	 */
	public $exception;
	/**
	 * everything that was caught from stdout
	 *
	 * @var string
	 */
	public $buffer;
	/**
	 * contents of the error buffer
	 *
	 * @var string
	 */
	public $errorBuffer;
	/**
	 *
	 * @var \PHPUnit_Framework_TestSuite
	 */
	public $testSuite;
	/**
	 * errors from the php error log
	 *
	 * @var string
	 */
	public $phpErrors;
	/**
	 * @var VerbosePrinter\AbstractPrinter
	 */
	public $verbosePrinter;


	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	public function  __construct()
	{
		$this->result = new \PHPUnit_Framework_TestResult;
	}
}