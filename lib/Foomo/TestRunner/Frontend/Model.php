<?php

namespace Foomo\TestRunner\Frontend;

use PHPUnit_Framework_TestSuite, PHPUnit_Util_ErrorHandler;

use Foomo\Config;

/**
 * run tests and offer heir results
 */
class Model extends \Foomo\TestRunner {
	const RENDER_MODE_HTML = 'html';
	const RENDER_MODE_TEXT = 'text';
	
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
	/**
	 * run a single test
	 *
	 */
	public function runTest($name)
	{
		$this->currentResult = $this->runOne($name);
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
	private function runOne($name)
	{
		if(!class_exists($name)) {
			trigger_error('can not run a unit test on a not existing class ' . $name, E_USER_ERROR);
		}
		$suite	= new PHPUnit_Framework_TestSuite($name);
		return $this->runASuite($suite);
	}
	private function runASuite(PHPUnit_Framework_TestSuite $suite, $streamHtml = true)
	{
		if($streamHtml) {
			\Foomo\MVC::abort();
			echo \Foomo\HTMLDocument::getInstance()->outputWithOpenBody();
		}
		//ini_set('html_errors', 'Off');
		//header('Content-type: text/plain;charset=utf-8');
		//echo '<pre>';
		$ret = new \Foomo\TestRunner\Result();
		$ret->verbosePrinter->model = $this;
		$ret->verbosePrinter->startOutput();
		// check mode
		if(Config::getMode() != Config::MODE_TEST) {
			trigger_error('you MUST be in test mode to run tests', E_USER_ERROR);
		}
		try {
			clearstatcache();
			$startSize = filesize(ini_get('error_log'));
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
		
		if($streamHtml) {
			$ret->verbosePrinter->printResult($ret);
			exit;
		}
		return $ret;
	}
	public static function handleError($errno, $errstr, $errfile, $errline)
	{
		$name = '';
		switch($errno) {
			case E_USER_NOTICE:
				$name = 'E_USER_NOTICE';
			case E_USER_WARNING:
				if(empty($name)) {
					$name = 'E_USER_WARNING';
				}
			case E_STRICT:
				if(empty($name)) {
					$name = 'E_STRICT';
				}
				self::$errorBuffer[] = array(
					'errno' => $errno,
					'errstr' => $errstr,
					'errfile' => $errfile,
					'errline' => $errline,
					'errtrace' => array_slice(debug_backtrace(), 0)
				);
				self::errorBufferHidingHack(array('file' => $errfile, 'line' => $errline, 'name' => $name, 'error' => $errstr));
				return true;
			default:
				if(!function_exists('PHPUnit_Util_ErrorHandler')) {
					return PHPUnit_Util_ErrorHandler::handleError($errno, $errstr, $errfile, $errline);
				} else {
					return PHPUnit_Util_ErrorHandler($errno, $errstr, $errfile, $errline);
				}
		}
	}
	/**
	 * @internal
	 * @var array
	 */
	public static $errorBuffer;
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

	private function getPhpErrors($startSize)
	{
		clearstatcache();
		$length = filesize(ini_get('error_log')) - $startSize;
		if($length > 0) {
			$fp = fopen(ini_get('error_log'), 'r');
			fseek($fp, $startSize);
			$errors = fread($fp, $length);
			fclose($fp);
		} else {
			$errors = '';
		}
		return $errors;
	}
}