<?php

namespace Foomo\TestRunner;

/**
 * cli interface
 */
class Cli {
	/**
	 * 
	 * @var Foomo\TestRunner
	 */
	protected $model;
	public function __construct()
	{
		$this->model = new \Foomo\TestRunner\Frontend\Model(\Foomo\TestRunner\Frontend\Model::RENDER_MODE_TEXT);	
	}
	/**
	 * run tests and display results
	 * 
	 * @param string $testNames names of the tests as comma separated list
	 * @return string unit test results
	 */
	public function runTests($testNames) {
		foreach(explode(',', $testNames) as $testName) {
			$this->model->runTest(trim($testName));
		}	
	}
	/**
	 * generate a module test suite
	 * 
	 * @param string $moduleName name of the module
	 * @param string $classDir directory where to put the class to
	 *
	 * @return string content of the generated class
	 */
	public function generateTestSuiteForModule($moduleName, $classDir)
	{
		$php = \Foomo\TestRunner\Module::getView($this, 'testSuite.tpl', $moduleName)->render();
		$fileName = $classDir . DIRECTORY_SEPARATOR . 'FoomoModuleTestSuite' . ucfirst($moduleName) . '.class.php';
		return 'wrote ' . file_put_contents($fileName, $php) . ' bytes to ' . $fileName . ' ' . $php . PHP_EOL;
	}

	/**
	 * run unit tests for all enabled modules including radact core
	 * 
	 * @param string $reportDirectory where to put the report xml-files
	 */
	public function runProjectTestsForHudson($reportDirectory)
	{
		$modules = Foomo\Modules\Manager::getEnabledModules();
		
		set_error_handler(array('Foomo\\TestRunner\\Frontend\\Model', 'handleError'), E_ALL);
		$result = new PHPUnit_Framework_TestResult;
		$logFile = $reportDirectory . DIRECTORY_SEPARATOR . 'TestReport.xml';
		$result->collectCodeCoverageInformation(true);
		$result->addListener(new PHPUnit_Util_Log_JUnit($logFile));
		$suite = new PHPUnit_Framework_TestSuite('Testsuite for all modules and the core');
		foreach($modules as $moduleName) {
			$testClasses = $this->model->getModuleTests($moduleName);
			foreach($testClasses as $testClass) {
				$suite->addTestSuite($testClass);
			}
		}
		$suite->run($result);
		$result->flushListeners();
		$writer = new PHPUnit_Util_Log_CodeCoverage_XML_Clover($reportDirectory . DIRECTORY_SEPARATOR . 'Clover.xml');
		$writer->process($result);
		PHPUnit_Util_Report::render($result, $reportDirectory . DIRECTORY_SEPARATOR . 'coverage');
	}

	/**
	 * run all unit tests in a module and render Hudson friendly output
	 * 
	 * @param string $moduleName name of the module to run all tests for
	 * @param string $reportDirectory where to put the report xml-files
	 *
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
		$writer = new \PHPUnit_Util_Log_CodeCoverage_XML_Clover($base . '-Clover.xml');
		$writer->process($result);
		\PHPUnit_Util_Report::render($result, $base . '-Clover', $moduleName);
	}
	/**
	 * list tests and test suites of module(s)
	 * 
	 * @param string $moduleName name of the module, if none given all will be listed
	 * @return string
	 */
	public function listModule($moduleName = '')
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