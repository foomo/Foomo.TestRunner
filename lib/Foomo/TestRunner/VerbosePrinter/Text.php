<?php

namespace Foomo\TestRunner\VerbosePrinter;

use PHPUnit_Framework_AssertionFailedError;
use PHPUnit_Framework_TestListener;
use PHPUnit_Framework_TestSuite;
use PHPUnit_Framework_Test;
use Console_Color;
use Exception;

class Text extends AbstractPrinter implements PHPUnit_Framework_TestListener {
	const COLOR_GREEN = 'g';
	const COLOR_RED = 'r';
	const COLOR_BLACK = 'k';
	const COLOR_WHITE = 'w';
	const COLOR_GREY = 'w';
	const BG_COLOR_BLACK = '0';
	const BG_COLOR_GREY = '7';
	const BG_COLOR_WHITE = '7';
	const STYLE_NONE = '';
	const STYLE_BOLD = '_';
	const STYLE_UNDERLINE = 'U';
	const INDENT = '  ';
	const OUTPUT_WIDTH = 132;
	const LINE_SEPARATOR = '-------------||| SEPARATOR |||---------------';

	public $useColors = true;
	
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
		\Foomo\Log\Logger::getInstance()->autoExitOnError = false;
		register_shutdown_function(array($this, 'shutdownListener'));
	}
	public function shutdownListener()
	{
		if(!$this->done) {
			// sth really bad must have happened
			$this->lineOut('Something really bad happened - shutting down (check your error log):');
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
	private function getIndent()
	{
		return str_repeat(self::INDENT, ($this->indent>=0)?$this->indent:0);
	}
	private $lastLineWasSeparator = false;
	private function getLineSeparator()
	{
		return self::LINE_SEPARATOR;
	}
	
	private function lineOut($line, $color = self::COLOR_WHITE, $bgColor = self::BG_COLOR_BLACK, $styles = array())
	{
		if($line == self::LINE_SEPARATOR) {
			if($this->lastLineWasSeparator) {
				return;
			} else {
				$line = str_repeat('-', self::OUTPUT_WIDTH - strlen($this->getIndent()));
			}
			$this->lastLineWasSeparator = true;
		} else {
			$this->lastLineWasSeparator = false;
		}
		if(strpos($line, PHP_EOL) !== false) {
			foreach(explode(PHP_EOL, $line) as $subLine) {
				$this->lineOut($subLine, $color);
			}
		} else {
			$indent = $this->getIndent();
			$str = $line;
			if(strlen($indent . $str) < self::OUTPUT_WIDTH) {
				$postFix = str_repeat(' ', self::OUTPUT_WIDTH - strlen($indent . $str));
			} else {
				$postFix = '';
			}
			if($this->useColors && class_exists('Console_Color')) {
				$styles = implode('%', $styles);
				echo Console_Color::convert(
					$indent . '%' . $color . '%'. $bgColor . ($styles?'%'. $styles:'') . ' ' . Console_Color::escape($str) . $postFix .'%n', $this->useColors
				) . PHP_EOL;
				//echo '%' . $color . '%'. $bgColor . ($styles?'%'. $styles:'') . $str . PHP_EOL;
			} else {
				echo $indent . $str . $postFix . PHP_EOL;				
			}
		}
		if(ob_get_length() > 0) {
			ob_flush();
			flush();
		}
	}
	public function startOutput() 
	{
		ini_set('html_errors', 'Off');
		if(!headers_sent()) {
			header('Content-Type: text/plain');
		}
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

		//$this->lineOut($e->getTraceAsString(),self::COLOR_RED);
		$this->indent --;
	}
    /**
     * A failure occurred.
     *
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @param  float                                  $time
     */
    public function addFailure(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_AssertionFailedError $e, $time){
		$this->err ++;
		$this->lineOut($e->getMessage(), self::COLOR_RED);
		$this->lineOut('FAIL', self::COLOR_RED);
	}

    /**
     * Incomplete test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addIncompleteTest(\PHPUnit_Framework_Test $test, \Exception $e, $time){
		$this->err ++;
		$this->lineOut($e->getMessage(), self::COLOR_GREY);
		$this->lineOut('INCOMPLETE', self::COLOR_GREY);
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
		$this->err ++;
		$this->lineOut($e->getMessage(), self::COLOR_GREY);
		$this->lineOut('SKIPPED', self::COLOR_GREY);
	}

    /**
     * A test suite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite){
		$this->lineOut($this->getLineSeparator());
		$this->currentSuite = $suite;
		if(
			$this->suiteExists($this->currentSuite->getName()) || 
			$this->testExists($this->currentSuite->getName())
		) {
			$this->lineOut('Suite ' . $suite->getName(), self::COLOR_WHITE, self::BG_COLOR_BLACK, array(self::STYLE_BOLD));
			//$this->lineOut($line, $color, $bgColor, $styles)
		}
		$this->lineOut($this->getLineSeparator());
		$this->indent ++;
	}
    /**
     * A test suite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(\PHPUnit_Framework_TestSuite $suite){
		$this->indent --;
	}

    /**
     * A test started.
     *
     * @param  PHPUnit_Framework_Test $test
     */
    public function startTest(\PHPUnit_Framework_Test $test)
	{
		$this->lineOut($this->getLineSeparator());
		$this->currentTest = $test;
		$this->errorContainerSent = false;
		\Foomo\TestRunner\Frontend\Model::$errorBuffer = array();
		$this->err = 0;
		//$this->indent = 0;
		if($this->testExists($this->currentSuite->getName())) {
			$this->lineOut($test->getName());
		}
		$this->indent ++;
		ob_start();
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
		$lines = explode(PHP_EOL, $lines);
		$isSpec = $this->isSpec($test);
		if($isSpec) {
			$this->lineOut('', self::COLOR_BLACK, self::BG_COLOR_GREY);
		}
		foreach($lines as $line) {
			if(strlen($line) > 0) {
				if($isSpec) {
					if($this->isStoryLine($line)) {
						$this->lineOut(' ' . $line, self::COLOR_BLACK, self::BG_COLOR_WHITE, array());
					} else {
						$this->lineOut(' ' . $line, self::COLOR_GREY);
					}
				} else {
					$this->lineOut($line);
				}
			}
		}
		if($isSpec) {
			$this->lineOut('', self::COLOR_BLACK, self::BG_COLOR_GREY);
		}
		if(count(\Foomo\TestRunner\Frontend\Model::$errorBuffer) > 0) {
			$this->lineOut('Ignored errors:', self::COLOR_GREY);
			foreach(\Foomo\TestRunner\Frontend\Model::$errorBuffer as $error) {
				$this->printError($error);
			}
		}
		if($this->err == 0) {
			$this->lineOut('OK ' . round($time, 3) . ' s', self::COLOR_GREEN);
		} else {
			$this->lineOut(round($time, 3) . ' s');
		}
		$this->indent --;
	}
	private function printError(array $error)
	{
		static $errorI = 0;
		
		$errorI ++;
		$errId = 'err-' . $errorI;
		$this->lineOut(
			$this->errorPrinter->phpErrorIntToString($error['errno']). ': ' . $error['errstr'] . PHP_EOL .
			'line: ' . $error['errline'] . PHP_EOL .
			'file: ' . $error['errfile'],
			self::COLOR_RED);
		$this->indent ++;
		foreach($error['errtrace'] as $trace) {
			$this->lineOut($this->getLineSeparator(), self::COLOR_RED);
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
					$args[] = \Foomo\Log\Logger::getInstance()->getVarAsString($arg);
				}
				$args = implode(', ', $args);
			} else {
				$args = '';
			}
			$this->lineOut($func . '(' . $args . ')', self::COLOR_RED);
			if(!empty($trace['file'])) {
				$this->lineOut(
					'file     : ' . $trace['file'] . PHP_EOL . 
					'line     : ' . $trace['line'],
					self::COLOR_RED
				);
			}
		}
		$this->indent --;
	}
	public function printResult(\Foomo\TestRunner\Result $result)
	{
		// there is some ob_ mess @the end of the process
		$this->lineOut($this->getLineSeparator());
		$this->lineOut('DONE', ($result->result->failureCount() == 0)?self::COLOR_GREEN:self::COLOR_RED, self::STYLE_BOLD);
		$this->lineOut($this->getLineSeparator());
		$failures = $result->result->failures();
		if(count($failures)>0) {
			$this->indent ++;
			$this->lineOut('Failed tests');
			$this->indent ++;
			foreach($failures as $error) {
				$this->lineOut(
					$error->failedTest()->getName(),
					self::COLOR_RED
				);
			}
			$this->indent --;
		}
		$this->lineOut($this->getLineSeparator());
		$this->lineOut('time       : ' . round($result->result->time(), 3) . ' s');
		$this->lineOut('failed     : ' . $result->result->failureCount(), ($result->result->failureCount()>0)?self::COLOR_RED:self::COLOR_GREY);
		$this->lineOut('skipped    : ' . $result->result->skippedCount(), self::COLOR_GREY);
		$this->lineOut('total      : ' . $result->result->count());
		$this->done = true;
	}
}