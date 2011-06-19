<?php

namespace Foomo\TestRunner\VerbosePrinter;

use Foomo\TestRunner\AbstractSpec;
use PHPUnit_Framework_Test;

abstract class AbstractPrinter {
	
	abstract function startOutput();
	abstract function printResult(\Foomo\TestRunner\Result $result);
	protected function isStoryLine($line)
	{
		$isStoryLine = false;
		foreach(array('when', 'then', 'given') as $storyKeyword) {
			if(strpos($line, $storyKeyword) == $storyKeyword) {
				return true;
			}
		}
		return false;
	}
	protected function suiteExists($name)
	{
		foreach(\Foomo\Modules\Manager::getEnabledModules() as $enabledModuleName) {
			foreach($suites = $this->model->getModuleSuites($enabledModuleName) as $suite) {
				if($suite == $name) {
					return true;
				}
			}
			//var_dump($name, $suites);
		}	
		return false;
	}
	protected function testExists($name)
	{
		foreach(\Foomo\Modules\Manager::getEnabledModules() as $enabledModuleName) {
			foreach($tests = array_merge($this->model->getModuleTests($enabledModuleName), $this->model->getModuleSpecs($enabledModuleName)) as $test) {
				if($test == $name) {
					return true;
				}
			}
		}
		return false;
	}
	
	protected function isSpec(PHPUnit_Framework_Test $test)
	{
		return $test instanceof AbstractSpec;
	}
	
}