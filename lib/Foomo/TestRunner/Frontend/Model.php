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

use PHPUnit_Framework_TestSuite, PHPUnit_Util_ErrorHandler;

use Foomo\Config;

/**
 * run tests and offer heir results
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Model extends \Foomo\TestRunner
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
	 * @var Foomo\TestRunner\Result
	 */
	public $currentResult;
	/**
	 * show details in the menu or not
	 *
	 * @var boolean
	 */
	public $showTestCases = true;

	//---------------------------------------------------------------------------------------------
	// ~ Public methods
	//---------------------------------------------------------------------------------------------

	/**
	 * run a single test
	 *
	 */
	public function runTest($name)
	{
		$refl = new \ReflectionClass($name);
		if($refl->isSubclassOf('PHPUnit_Framework_TestSuite')) {
			$this->runSuite($name);
		} else {
			$this->currentResult = $this->runOne($name);
		}

	}

	public function runTestCase($suiteName, $caseName)
	{
		$suite = new PHPUnit_Framework_TestSuite();
		$suite->setName($suiteName . ucfirst($caseName));
		$suite->addTest(new $suiteName($caseName));
		$this->currentResult = $this->runASuite($suite);

	}

	public function runModule($name)
	{
		$this->currentResult = $this->runASuite($this->composeModuleSuite($name));
		$this->currentModuleTest = $name;
	}

	public function runAll()
	{
		$this->currentResult = $this->runASuite($this->composeCompleteSuite());
	}

	public function runSuite($name)
	{
		$this->currentResult = $this->runASuite($this->composeSuiteFromFoomoTestSuite($name));
	}

	//---------------------------------------------------------------------------------------------
	// ~ Private methods
	//---------------------------------------------------------------------------------------------

	private function runOne($name)
	{
		if(!class_exists($name)) {
			trigger_error('can not run a unit test on a not existing class ' . $name, E_USER_ERROR);
		}
		$suite	= new PHPUnit_Framework_TestSuite($name);
		return $this->runASuite($suite);
	}

	private function runASuite(PHPUnit_Framework_TestSuite $suite)
	{
		$streamHtml = php_sapi_name() != 'cli';
		if($streamHtml) {
			\Foomo\MVC::abort();
			echo \Foomo\HTMLDocument::getInstance()->outputWithOpenBody();
		}
		$ret = new \Foomo\TestRunner\Result();
		$ret->verbosePrinter->model = $this;
		$ret->verbosePrinter->startOutput();
		// check mode
		if(Config::getMode() != Config::MODE_TEST) {
			trigger_error('you MUST be in test mode to run tests', E_USER_ERROR);
		}
		try {
			clearstatcache();
			if(file_exists($errorLogFilename = ini_get('error_log'))) {
				$startSize = filesize($errorLogFilename);
			} else {
				$startSize = 0;
			}

			\PHPUnit_Framework_Error_Notice::$enabled = true;
			\PHPUnit_Framework_Error_Warning::$enabled = true;
			set_error_handler(array(__CLASS__, 'handleError'), E_ALL);
			self::errorBufferHidingHack(false);
			ob_start();
			$suite->run($ret->result);

			$phpErrors = $this->getPhpErrors($startSize);
			$ret->name = $suite->getName();
			$ret->buffer = ob_get_clean();
			//ob_end_clean();
			$ret->errorBuffer = self::errorBufferHidingHack();
 			$ret->testSuite = $suite;
			$ret->phpErrors = $phpErrors;
		} catch(Exception $e) {
			$ret->exception = $e;
		}
		$ret->verbosePrinter->printResult($ret);
		if($streamHtml) {
			exit;
		}
		return $ret;
	}

	private function getPhpErrors($startSize)
	{
		clearstatcache();
		if(file_exists($errorLogFilename = ini_get('error_log'))) {
			$length = filesize($errorLogFilename) - $startSize;
			if($length > 0) {
				$fp = fopen($errorLogFilename, 'r');
				fseek($fp, $startSize);
				$errors = fread($fp, $length);
				fclose($fp);
			} else {
				$errors = '';
			}
			return $errors;
		} else {
			return '';
		}
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	public static function handleError($errno, $errstr, $errfile, $errline)
	{
		$name = '';
		$errorHandled = false;
		$delegateErrorToPHPUnit = false;
		switch($errno) {
			case E_USER_NOTICE:
				$delegateErrorToPHPUnit = \PHPUnit_Framework_Error_Notice::$enabled;
				$name = 'E_USER_NOTICE';
				break;
			case E_USER_WARNING:
				$delegateErrorToPHPUnit = \PHPUnit_Framework_Error_Warning::$enabled;
				$name = 'E_USER_WARNING';
				break;
			case E_STRICT:
				$name = 'E_STRICT';
				break;
			default:
				return self::delegateErrorToPHPUnit($errno, $errstr, $errfile, $errline);
		}

		if($delegateErrorToPHPUnit) {
			return self::delegateErrorToPHPUnit($errno, $errstr, $errfile, $errline);
		} else {
			self::$errorBuffer[] = array(
				'errno' => $errno,
				'errstr' => $errstr,
				'errfile' => $errfile,
				'errline' => $errline,
				'errtrace' => array_slice(debug_backtrace(), 0)
			);
			self::errorBufferHidingHack(array('file' => $errfile, 'line' => $errline, 'name' => $name, 'error' => $errstr));			
			return $errorHandled;
		}

	}
	
	private static function delegateErrorToPHPUnit($errno, $errstr, $errfile, $errline)
	{
		if(!function_exists('PHPUnit_Util_ErrorHandler')) {
			return PHPUnit_Util_ErrorHandler::handleError($errno, $errstr, $errfile, $errline);
		} else {
			return PHPUnit_Util_ErrorHandler($errno, $errstr, $errfile, $errline);
		}
		
	}

	//---------------------------------------------------------------------------------------------
	// ~ Private static methods
	//---------------------------------------------------------------------------------------------

	private static function errorBufferHidingHack($bufferEntry = null)
	{
		static $buf;
		if(is_null($bufferEntry)) {
			if(!is_array($buf)) {
				return $buf = array();
			} else {
				return $buf;
			}
		} elseif(is_array($bufferEntry)) {
			$buf = self::errorBufferHidingHack();
			$buf[] = $bufferEntry;
		} else {
			$buf = array();
		}
	}
}