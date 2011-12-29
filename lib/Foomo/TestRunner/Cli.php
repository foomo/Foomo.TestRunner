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
 * cli interface
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Cli
{
	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * @var Foomo\TestRunner
	 */
	protected $model;

	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	/**
	 *
	 */
	public function __construct()
	{
		$this->model = new \Foomo\TestRunner\Frontend\Model(\Foomo\TestRunner\Frontend\Model::RENDER_MODE_TEXT);
	}

	//---------------------------------------------------------------------------------------------
	// ~ Public methods
	//---------------------------------------------------------------------------------------------

	/**
	 * run tests and display results
	 *
	 * @param string[] $testNames names of the tests as comma separated list
	 */
	public function runTests(array $testNames)
	{
		foreach($testNames as $testName) {
			$this->model->runTest(trim($testName));
		}
	}

	/**
	 * run tests for a module
	 *
	 * @param string $moduleName
	 */
	public function runModuleTests($moduleName)
	{
		$this->model->runModule($moduleName);
	}

	/**
	 * generate a module test suite
	 *
	 * @param string $moduleName name of the module
	 * @param string $classDir directory where to put the class to
	 * @return string content of the generated class
	 */
	public function generateTestSuiteForModule($moduleName, $classDir)
	{
		$php = \Foomo\TestRunner\Module::getView($this, 'testSuite.tpl', $moduleName)->render();
		$fileName = $classDir . DIRECTORY_SEPARATOR . 'FoomoModuleTestSuite' . ucfirst(str_replace('.', '', $moduleName)) . '.php';
		if(!is_writable($fileName)) {
			trigger_error('con not write to ' . $fileName, E_USER_WARNING);
		}
		return 'wrote ' . file_put_contents($fileName, $php) . ' bytes to ' . $fileName . ' ' . $php . PHP_EOL;
	}

	/**
	 * run unit tests for all enabled modules including foomo core
	 *
	 * @param string $reportDirectory where to put the report xml-files
	 */
	public function runProjectTestsForHudson($reportDirectory)
	{
		$modules = Foomo\Modules\Manager::getEnabledModules();

		set_error_handler(array('Foomo\\TestRunner\\Frontend\\Model', 'handleError'), E_ALL);
		$result = new \PHPUnit_Framework_TestResult;
		$logFile = $reportDirectory . DIRECTORY_SEPARATOR . 'TestReport.xml';
		$result->collectCodeCoverageInformation(true);
		$result->addListener(new PHPUnit_Util_Log_JUnit($logFile));
		$suite = new \PHPUnit_Framework_TestSuite('Testsuite for all modules and the core');
		foreach($modules as $moduleName) {
			$testClasses = $this->model->getModuleTests($moduleName);
			foreach($testClasses as $testClass) {
				$suite->addTestSuite($testClass);
			}
		}
		$suite->run($result);
		$result->flushListeners();
		//new \PHP_CodeCoverage_Report_Clover()
		$writer = new \PHPUnit_Util_Log_CodeCoverage_XML_Clove($reportDirectory . DIRECTORY_SEPARATOR . 'Clover.xml');
		$writer->process($result);
		PHPUnit_Util_Report::render($result, $reportDirectory . DIRECTORY_SEPARATOR . 'coverage');
	}

	/**
	 * run all unit tests in a module and render Hudson friendly output
	 *
	 * @param string $moduleName name of the module to run all tests for
	 * @param string $reportDirectory where to put the report xml-files
	 */
	public function runModuleTestsForHudson($moduleName, $reportDirectory)
	{
		$testClasses = $this->model->getModuleTests($moduleName);
		set_error_handler(array('Foomo\\TestRunner\\Frontend\\Model', 'handleError'), E_ALL);
		$result = new \PHPUnit_Framework_TestResult;
		//$friendlyName = $moduleName . '-' . str_replace('\\', '.', $testClass);
		$logFile = $reportDirectory . DIRECTORY_SEPARATOR . $moduleName . '-TestReport.xml';
		$base = $reportDirectory . DIRECTORY_SEPARATOR . 'coverage' . DIRECTORY_SEPARATOR . $moduleName;
		$result->collectCodeCoverageInformation(true);
		$result->addListener(new \PHPUnit_Util_Log_JUnit($logFile));
		$suite = new \PHPUnit_Framework_TestSuite('Testsuite for $moduleName');
		foreach($testClasses as $testClass) {
			$suite->addTestSuite($testClass);
		}
		$suite->run($result);
		$result->flushListeners();
		
		/*
		$writer = new \PHPUnit_Util_Log_CodeCoverage_XML_Clover($base . '-Clover.xml');
		new \PHP_CodeCoverage_Report_Clover();
		$writer->process($result);
		\PHPUnit_Util_Report::render($result, $base . '-Clover', $moduleName);
		 */
	}
	/**
	 * list tests and test suites of module(s)
	 *
	 * @param string $moduleName name of the module, if none given all will be listed
	 * @return string
	 */
	public function listModule($moduleName='')
	{
		$ret = '';
		if($moduleName == '') {
			$moduleNames = \Foomo\Modules\Manager::getEnabledModules();
		} else {
			$moduleNames = array($moduleName);
		}
		foreach($moduleNames as $moduleName) {
			$ret .= $this->listAModule($moduleName);
		}
		return $ret . PHP_EOL;
	}

	//---------------------------------------------------------------------------------------------
	// ~ Private methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $moduleName
	 * @return string
	 */
	private function listAModule($moduleName)
	{
		$ret = '';
		$ret .= 'Module ' . $moduleName . ':' . PHP_EOL . PHP_EOL;
		$ret .= ' Tests :' . PHP_EOL;
		$ret .= '    ' . implode(', ', $this->model->getModuleTests($moduleName)) . PHP_EOL . PHP_EOL;
		$ret .= ' Suites :' . PHP_EOL;
		$ret .= '    ' . implode(', ', $this->model->getModuleSuites($moduleName)) . PHP_EOL;
		$ret .= PHP_EOL;
		return $ret;
	}

}