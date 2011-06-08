<?php

namespace Foomo\TestRunner\Frontend;

use Foomo\TestRunner;
/**
 * controller for the unit test module
 *
 */
class Controller {
	/**
	 * testRunner
	 *
	 * @var Foomo\TestRunner\Frontend\Model
	 */
	public $model;
	public function actionDefault($details = true)
	{
		$this->model->showTestCases = $details;
	}
	public function actionRunModuleTests($moduleName)
	{
		$this->model->runModule($moduleName);
	}
	public function actionRunTest($name)
	{
		$this->model->runTest($name);
	}
	public function actionRunAll()
	{
		$this->model->runAll();
	}
	public function actionRunSuite($name)
	{
		$this->model->runSuite($name);
	}
	public function actionRunTestCase($suiteName, $caseName)
	{
		$this->model->runTestCase($suiteName, $caseName);
	}
}