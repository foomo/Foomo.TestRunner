<?php

namespace Foomo\TestRunner;

use PHPUnit_Framework_TestResult;

/**
 * result of a suite, that was run
 */
class Result {
	/**
	 * name of the test suite class
	 *
	 * @var string
	 */
	public $name = '';
	/**
	 *
	 * @var PHPUnit_Framework_TestResult
	 */
	public $result;
	/**
	 *
	 * @var Exception
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
	 * @var PHPUnit_Framework_TestSuite
	 */
	public $testSuite;
	/**
	 * errors from the php error log
	 * 
	 * @var string
	 */
	public $phpErrors;
	/**
	 * @var VerbosePrinter
	 */
	public $verbosePrinter;
	public function  __construct() {
		$this->result = new PHPUnit_Framework_TestResult;
		if(php_sapi_name() == 'cli') {
			$this->result->addListener($this->verbosePrinter = new VerbosePrinter\Text);
		} else {
			$this->result->addListener($this->verbosePrinter = new VerbosePrinter\HTML);
		}
	}
}