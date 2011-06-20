<?php

namespace Foomo\TestRunner\VerbosePrinter;

use PHPUnit_Framework_TestListener;

class HTML extends AbstractPrinter implements PHPUnit_Framework_TestListener {
	private $err = 0;
	private $indent = 0;
	public $stats = array();
	/**
	 * @var PHPUnit_Framework_TestSuite
	 */
	private $currentSuite;
	private $currentTest;
	/**
	 * @var Foomo\Log\Printer
	 */
	private $errorPrinter;
	/**
	 * @var Foomo\TestRunner\Frontend\Model
	 */
	public $model;
	private $errorContainerSent;
	private $done = false;
	public function __construct()
	{
		$this->errorPrinter = new \Foomo\Log\Printer();
		// make sure that the shutdown listener is reached
		\Foomo\Log\Logger::getInstance()->autoExitOnError = false;
		register_shutdown_function(array($this, 'shutdownListener'));
	}
	public function shutdownListener()
	{
		if(!$this->done) {
			// sth really bad must have happened
			$this->sendErrorContainer();
			$this->lineOut('<div id="sthReallyBad">Something really bad happened - shutting down (check your error log):</div>');
			$lastError = error_get_last();
			$this->printError(array(
				'errno' => $lastError['type'],
				'errstr' => $lastError['message'],
				'errline' => $lastError['line'],
				'errfile' => $lastError['file'],
				'errtrace' => array()
			));
		}
	}
	private function lineOut($line, $color = null)
	{
		if(strpos($line, PHP_EOL) !== false) {
			foreach(explode(PHP_EOL, $line) as $subLine) {
				$this->lineOut($subLine, $color);
			}
		} else {
			if(empty($color)) {
				echo  $line . PHP_EOL;				
			} else {
				echo str_repeat(' &nbsp;', $this->indent) . '<span style="color:' . $color . '">' . $line . '</span><br>' . PHP_EOL;				
			}
			
			//flush();
		}
		if(ob_get_length() > 0) {
			ob_flush();
			flush();
		}
	}
	public function startOutput()
	{
		$this->lineOut('<ul>');
	}

	/**
     * An error occurred.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addError(\PHPUnit_Framework_Test $test, \Exception $e, $time){
		$this->err ++;
		//var_dump($e);
		$this->printError(
			array(
				'errno' => $e->getCode(),
				'errstr' => $e->getMessage(),
				'errline' => $e->getLine(),
				'errfile' => $e->getFile(),
				'errtrace' => $e->getTrace()
			)
		);

		//$this->lineOut($e->getTraceAsString(),'red');
		$this->indent --;
	}
	private function sendErrorContainer()
	{
		if(!$this->errorContainerSent) {
			$this->errorContainerSent = true;
			$this->lineOut('<div style="padding:10px;margin-left:20px;background-color:lightgrey">');
		}
	}
    /**
     * A failure occurred.
     *
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @param  float                                  $time
     */
    public function addFailure(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_AssertionFailedError $e, $time){
		$this->sendErrorContainer();
		$this->err ++;
		$this->lineOut(htmlspecialchars($e->getMessage()), 'red');
		$this->lineOut('FAIL', 'red');
	}

    /**
     * Incomplete test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addIncompleteTest(\PHPUnit_Framework_Test $test, \Exception $e, $time){
		$this->sendErrorContainer();
		$this->err ++;
		$this->lineOut(htmlspecialchars($e->getMessage()), 'grey');
		$this->lineOut('INCOMPLETE', 'grey');
	}

    /**
     * Skipped test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @since  Method available since Release 3.0.0
     */
    public function addSkippedTest(\PHPUnit_Framework_Test $test, \Exception $e, $time){
		$this->sendErrorContainer();
		$this->err ++;
		$this->lineOut($e->getMessage(), 'grey');
		$this->lineOut('SKIPPED', 'grey');
	}

    /**
     * A test suite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite){
		$this->currentSuite = $suite;
		//$view = \Foomo\MVC\View::$viewStack[count(\Foomo\MVC\View::$viewStack)-1];
		if(
			$this->suiteExists($this->currentSuite->getName()) || 
			$this->testExists($this->currentSuite->getName())
		) {
			$this->lineOut('<li><h1><a href="'.$this->getUrlHandler()->renderUrl('Foomo\\TestRunner\\Frontend\\Controller', 'runTest', array($this->currentSuite->getName())).'">Suite ' . $this->currentSuite->getName() . '</a></h1><ul>');
		} else {
			$this->lineOut('<li><h1>Suite ' . $suite->getName() . '</h1><ul>');
		}
	}
    /**
     * A test suite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(\PHPUnit_Framework_TestSuite $suite){
		$this->lineOut('</li></ul>');
	}

    /**
     * A test started.
     *
     * @param  PHPUnit_Framework_Test $test
     */
    public function startTest(\PHPUnit_Framework_Test $test)
	{
		$this->currentTest = $test;
		$this->errorContainerSent = false;
		\Foomo\TestRunner\Frontend\Model::$errorBuffer = array();
		$this->err = 0;
		$this->indent = 0;
		if($this->testExists($this->currentSuite->getName())) {
			$this->lineOut('<li><span><a name="' . $this->getAnchorName($test) . '" href="' . $this->getUrlHandler()->renderURL('Foomo\\TestRunner\\Frontend\\Controller', 'runTestCase', array($this->currentSuite->getName(), $this->currentTest->getName())) . '">' . $test->getName() . '</a></span>');
		} else {
			$this->lineOut('<li><span>' . $test->getName() . '</span>');
		}
		ob_start();
	}
	private function getAnchorName(\PHPUnit_Framework_Test $test)
	{
		return $test->getName();
	}
	/**
	 *
	 * @return \Foomo\MVC\URLHandler
	 */
	private function getUrlHandler()
	{
		$keys = array_keys(\Foomo\MVC::$handlers);
		return \Foomo\MVC::$handlers[$keys[0]];
	}
    /**
     * A test ended.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  float                  $time
     */
    public function endTest(\PHPUnit_Framework_Test $test, $time)
	{
		$lines = ob_get_clean();
		if(strlen(trim($lines))>0) {
			$this->sendErrorContainer();
		}
		$lines = explode(PHP_EOL, $lines);
		$isSpec = $this->isSpec($test);
		if($isSpec) {
			$this->sendErrorContainer();
			$this->lineOut('<div class="story">');
		}
		$this->indent ++;
		foreach($lines as $line) {
			if(strlen($line) > 0) {
				if($this->isStoryLine($line)) {
					$this->lineOut('<b>' . $line . '</b>', 'black');
				} else {
					$this->lineOut($line, 'grey');
				}
			}
		}
		if(count(\Foomo\TestRunner\Frontend\Model::$errorBuffer) > 0) {
			$this->sendErrorContainer();
			$this->lineOut('Ignored errors:', 'grey');
			foreach(\Foomo\TestRunner\Frontend\Model::$errorBuffer as $error) {
				$this->printError($error);
			}
		}
		$this->indent --;
		if($this->err == 0) {
			$this->lineOut('OK ' . round($time, 3) . ' s', 'green');
		} else {
			$this->lineOut(round($time, 3) . ' s');
		}
		if($isSpec) {
			$this->lineOut('</div>');
		}
		if($this->errorContainerSent) {
			$this->lineOut('</div>');
		}
		$this->lineOut('</li>');
	}
	private function printError(array $error)
	{
		static $errorI = 0;
		
		$errorI ++;
		$errId = 'err-' . $errorI;
		$this->lineOut('<code>');
		$this->lineOut(
			'<div class="error" onclick="var el=document.getElementById(\'' . $errId . '\');el.style.display=(el.style.display==\'none\')?\'block\':\'none\'">' . $this->errorPrinter->phpErrorIntToString($error['errno']). ': ' . $error['errstr'] . PHP_EOL .
			'line: ' . $error['errline'] . PHP_EOL .
			'file: ' . $error['errfile']. '</div>',
			'red');
		$this->indent ++;
		$this->lineOut('<div id="' . $errId . '" class="errorTrace">');
		foreach($error['errtrace'] as $trace) {
			$this->lineOut('--------------------------------------', 'red');
			$func = '';
			if(!empty($trace['class'])) {
				$func = 'method   : ' . $trace['class'] . $trace['type'] .$trace['function'];
			} elseif($trace['function']) {
				$func = 'function : ' . $trace['function'];
			}
			// skip myself
			if($func == '') {
				continue;
			}
			if(!empty($trace['args'])) {
				$args = array();
				foreach($trace['args'] as $arg) {
					$args[] = htmlspecialchars(\Foomo\Log\Logger::getInstance()->getVarAsString($arg));
				}
				$args = implode(', ', $args);
			} else {
				$args = '';
			}
			$this->lineOut($func . '(' . $args . ')', 'red');
			if(!empty($trace['file'])) {
				$this->lineOut(
					'file     : ' . $trace['file'] . PHP_EOL . 
					'line     : ' . $trace['line'],
					'red'
				);
			}
		}
		$this->lineOut('</div>');
		$this->indent --;
		$this->lineOut('</code>');	
	}
	public function printResult(\Foomo\TestRunner\Result $result)
	{
		// there is some ob_ mess @the end of the process
		$this->lineOut('</ul>');
		$this->lineOut('<div id="testResult">');
		if($result->result->failureCount() == 0) {
			$doneClass = 'valid';
		} else {
			$doneClass = 'invalid';
		}
		$this->lineOut('<h1 class="' . $doneClass . '">Done</h1>');
		$failures = $result->result->failures();
		if(count($failures)>0) {
			$this->lineOut('<h2>Failed tests</h2>');
			$this->lineOut('<ul>');
			foreach($failures as $error) {
				$this->lineOut(
					'<li><a href="#' . $this->getAnchorName($error->failedTest()) . '">' . $error->failedTest()->getName() . '</a></li>'
				);
			}
			$this->lineOut('</ul>');
		}
		$this->lineOut(
			'<pre>-----------------------------------------------------' . PHP_EOL .
			'time       : ' . round($result->result->time(), 3) . ' s' . PHP_EOL .
			'failed     : ' . $result->result->failureCount() . PHP_EOL .
			'skipped    : ' . $result->result->skippedCount() . PHP_EOL .
			//'incomplete : ' . $result->result->incompleteCount() . PHP_EOL .
			'total      : ' . $result->result->count() . PHP_EOL
		);
		$this->lineOut('</pre></div></body></html>');
		$this->done = true;
	}
}