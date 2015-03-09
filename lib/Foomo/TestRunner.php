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

namespace Foomo;

use Foomo\TestRunner\Suite;
use ReflectionClass;
use PHPUnit_Framework_TestSuite;

/**
 * wraps sinple model around phpUnit
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 * @todo non html rendering is a hack / experiment
 */
class TestRunner
{
	//---------------------------------------------------------------------------------------------
	// ~ Public methods
	//---------------------------------------------------------------------------------------------

	/**
	 * get all tests in a suite
	 *
	 * @param string $suiteName
	 * @return string[] class names
	 */
	public function getSuiteTests($suiteName)
	{
        $lister = new $suiteName;
        if($this->isAFoomoSuite($suiteName)) {
		    $ret = array();
            $classesToAdd = $lister->foomoTestSuiteGetList();
            foreach ($classesToAdd as $className) {
                if (class_exists($className)) {
                    $ret[] = $className;
                }
            }
		    return $ret;
        } else {
            $ret = [];
            /* @var $lister PHPUnit_Framework_TestSuite */
            foreach($lister->getIterator() as $testCase) {
                /* @var $testCase \PHPUnit_Framework_TestCase */
                $ret[] = $testCase->getName();
            }
            return $ret;
        }
	}

    public function isAFoomoSuite($suiteName)
    {
        $refl = new ReflectionClass($suiteName);
        return $refl->isSubclassOf(__CLASS__ . '\\' . 'Suite');
    }

	/**
	 * get a list of suites
	 *
	 * @return array of suite names
	 */
	public function listSuites()
	{
		return $this->getList('Foomo\\TestRunner\\TestSuite');
	}

	/**
	 * list all available tests
	 *
	 * @return array
	 */
	public function listTests()
	{
		return $this->getList('PHPUnit_Framework_TestCase');
	}

	/**
	 * Get all tests for a module, that are not in a suite in that module
	 *
	 * @param string $moduleName name og the module
	 */
	public function getModuleStandAloneTests($moduleName)
	{
		$moduleSuites = $this->getModuleSuites($moduleName);
		$coveredTests = array();
		foreach ($moduleSuites as $moduleSuite) {
			$suiteTests = $this->getSuiteTests($moduleSuite);
			$coveredTests = array_merge($coveredTests, $suiteTests);
		}
		$coveredTests = array_unique($coveredTests);
		$standAloneTests = array();
		$moduleTests = $this->getModuleTests($moduleName);
		foreach ($moduleTests as $moduleTest) {
			if (!in_array($moduleTest, $coveredTests)) {
				$standAloneTests[] = $moduleTest;
			}
		}
		return $standAloneTests;
	}

	/**
	 * return all tests for a module
	 *
	 * @param string $moduleName name of the module
	 *
	 * @return array
	 */
	public function getModuleTests($moduleName)
	{
		$specs = $this->getModuleSpecs($moduleName);
		$ret = array();
		foreach($this->getTestsInFolder(\Foomo\CORE_CONFIG_DIR_MODULES . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'tests') as $test) {
			if(!in_array($test, $specs)) {
				$ret[] = $test;
			}
		}
		return $ret;
	}

	public function getModuleSpecs($moduleName)
	{
		return $this->getSpecsInFolder(\Foomo\CORE_CONFIG_DIR_MODULES . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'tests');
	}

	/**
	 * return all tests for a module
	 *
	 * @param string $moduleName name of the module
	 *
	 * @return array
	 */
	public function getModuleSuites($moduleName)
	{
		$dir = \Foomo\CORE_CONFIG_DIR_MODULES . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'tests';
		return array_unique(array_merge($this->getSuitesInFolder($dir), $this->getPHPUnitSuites($dir)));
	}

	public function composeCompleteSuite()
	{
		$enabledModules = Modules\Manager::getEnabledModules();
		$suite = new PHPUnit_Framework_TestSuite();
		foreach($enabledModules as $enabledModule) {
			$this->composeModuleSuite($enabledModule, $suite);
		}
		$suite->setName('complete suite for modules ' . implode(', ', $enabledModules));
		return $suite;
	}
	/**
	 *
	 *  Scans through modules/moduleName/tests for Test and Suite and Composes them into a suite
	 *
	 * @param string $moduleName name of the module
	 *
	 * @return PHPUnit_Framework_TestSuite
	 */
	public function composeModuleSuite($moduleName, $suite = null)
	{
		if(is_null($suite)) {
			$suite = new PHPUnit_Framework_TestSuite();
		}
		$suite->setName('ModuleTestSuite' . ucfirst($moduleName));

		// look for suites
		$moduleSuiteClasses = $this->getModuleSuites($moduleName);
		foreach ($moduleSuiteClasses as $moduleSuiteClass) {
			$moduleSuiteTests = $this->getSuiteTests($moduleSuiteClass);
			if (count($moduleSuiteTests) > 0) {
				$moduleSuite = new PHPUnit_Framework_TestSuite();
				$moduleSuite->setName($moduleSuiteClass);
				foreach ($moduleSuiteTests as $moduleSuiteTest) {
					$moduleSuite->addTestSuite($moduleSuiteTest);
				}
				$suite->addTestSuite($moduleSuite);
			}
		}

		// take everything standalone
		$standAloneTests = $this->getModuleStandAloneTests($moduleName);
		if (count($standAloneTests) > 0) {
			$standAloneSuite = new PHPUnit_Framework_TestSuite();
			$standAloneSuite->setName('Stand alone Tests');
			foreach ($standAloneTests as $standAloneTest) {
				$standAloneSuite->addTestSuite($standAloneTest);
			}
			$suite->addTestSuite($standAloneSuite);
		}
		$specs = $this->getModuleSpecs($moduleName);
		if (count($specs) > 0) {
			$specsSuite = new PHPUnit_Framework_TestSuite();
			$specsSuite->setName('Specs');
			foreach ($specs as $spec) {
				$specsSuite->addTestSuite($spec);
			}
			$suite->addTestSuite($specsSuite);
		}

		return $suite;
	}

	/**
	 * get test cases in a class
	 *
	 * @param string $className name of the class
	 *
	 * @return string[]
	 */
	public function getTestMethods($className)
	{
		$ret = array();
        if(class_exists($className)) {
            $classRefl = new ReflectionClass($className);
            foreach ($classRefl->getMethods() as $method) {
                /* @var $method \ReflectionMethod */
                if (strpos($method->getName(), 'test') === 0 && !$method->isAbstract() && !$method->isStatic() && $method->getDeclaringClass()->getName() == $className) {
                    $ret[] = $method->getName();
                }
            }
        }
		return $ret;
	}

	/**
	 * compose a suite
	 *
	 * @param string $suiteName a class extending Foomo\TestRunner\TestSuite
	 *
	 * @return PHPUnit_Framework_TestSuite
	 */
	public function composeSuiteFromFoomoTestSuite($suiteName)
	{
        $originalSuite = new $suiteName;
        if($originalSuite instanceof Suite) {
            $suite = new PHPUnit_Framework_TestSuite();
            $suite->setName($suiteName);
            foreach ($this->getSuiteTests($suiteName) as $className) {
                $suite->addTestSuite($className);
            }
            return $suite;
        } else {
            return $originalSuite;
        }
	}

	//---------------------------------------------------------------------------------------------
	// ~ Private methods
	//---------------------------------------------------------------------------------------------

	private function getList($keys)
	{
		$reflections = $this->gatherExtensionsOfClass($keys);
		$list = array();
		foreach ($reflections as $reflection) {
			$list[] = $reflection->name;
		}
		sort($list);
		return $list;
	}

	private function getSuitesInFolder($folder)
	{
		return $this->getShitInFolder($folder, 'Foomo\\TestRunner\\Suite');
	}

	private function getSpecsInFolder($folder)
	{
		return $this->getShitInFolder($folder, 'Foomo\\TestRunner\\AbstractSpec');
	}

	private function getTestsInFolder($folder)
	{
		return $this->getShitInFolder($folder, 'PHPUnit_Framework_TestCase');
	}

	private function getPHPUnitSuites($folder)
	{
		return $this->getShitInFolder($folder, 'PHPUnit_Framework_TestSuite');
	}

	private function getShitInFolder($folder, $typeOfShit)
	{
		$classMap = AutoLoader::getClassMap();
		$allTests = $this->getList($typeOfShit);
		$ret = array();
		$folder = realpath($folder);
		foreach ($allTests as $test) {
			if (isset($classMap[$test])) {
				if (strpos(realpath($classMap[$test]), $folder) === 0) {
					$ret[] = $test;
				}
			}
		}
		return $ret;
	}

	private function gatherExtensionsOfClass($baseClassName)
	{
		$results = array();
		$classMap = AutoLoader::getClassMap();
		$classes = array_keys($classMap);
		foreach ($classes as $class) {
			if (class_exists($class)) {
				$reflection = new ReflectionClass($class);
				if ($reflection->isSubclassOf($baseClassName) && !$reflection->isAbstract()) {
					$results[] = $reflection;
				}
			}
		}
		return $results;
	}
}