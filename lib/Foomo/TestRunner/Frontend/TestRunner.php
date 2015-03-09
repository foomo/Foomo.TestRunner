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
use PHPUnit_Framework_TestSuite, PHPUnit_Util_ErrorHandler;

use Foomo\Config;

/**
 * run tests and offer heir results
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class TestRunner extends \Foomo\TestRunner
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
     *
	 * @var array
	 */
	public static $errorBuffer;

	//---------------------------------------------------------------------------------------------
	// ~ Public methods
	//---------------------------------------------------------------------------------------------

	public function getTestCaseSuite($suiteName, $caseName)
	{
		$suite = new PHPUnit_Framework_TestSuite();
		$suite->setName($suiteName . ucfirst($caseName));
		$suite->addTest(new $suiteName($caseName));
		return $suite;
	}

	//---------------------------------------------------------------------------------------------
	// ~ Private methods
	//---------------------------------------------------------------------------------------------

	public function getASuiteForOne($name)
	{
		if(!class_exists($name)) {
			trigger_error('can not run a unit test on a not existing class ' . $name, E_USER_ERROR);
		}
		return new PHPUnit_Framework_TestSuite($name);
	}

	public function runASuite(PHPUnit_Framework_TestSuite $suite, \Foomo\TestRunner\Result $result)
	{
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
			$suite->run($result->result);
			$phpErrors = $this->getPhpErrors($startSize);
			$result->name = $suite->getName();
			$result->buffer = ob_get_clean();
			$result->errorBuffer = self::errorBufferHidingHack();
 			$result->testSuite = $suite;
			$result->phpErrors = $phpErrors;
		} catch(Exception $e) {
			$result->exception = $e;
		}
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