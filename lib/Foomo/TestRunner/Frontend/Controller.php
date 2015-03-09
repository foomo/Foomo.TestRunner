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

use Foomo\HTMLDocument;
use Foomo\Modules\Manager;
use Foomo\MVC;
use Foomo\TestRunner;
use Foomo\TestRunner\VerbosePrinter;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Controller
{
	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * @var Model
	 */
	public $model;

	//---------------------------------------------------------------------------------------------
	// ~ Action methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param boolean $details
	 */
	public function actionDefault()
	{
		// $this->model->showTestCases = $details;
	}

	/**
	 * @param string $moduleName
	 */
	public function actionRunModuleTests($moduleName)
	{
        $moduleName = (string) $moduleName;
        if(!in_array($moduleName, Manager::getEnabledModules())) {
            throw new \InvalidArgumentException("invalid module name " . $moduleName);
        }
        $this->model->currentModuleTest = $moduleName;
		$this->runSuite($this->model->testRunner->composeModuleSuite($moduleName));
        exit;
	}

	/**
	 * @param string $name
	 */
	public function actionRunTest($name)
	{
        $this->validateNameIsSubclassOf($name, 'PHPUnit_Framework_TestCase');
        $this->runSuite($this->model->testRunner->getASuiteForOne($name));
        exit;
	}


	/**
	 *
	 */
	public function actionRunAll()
	{
		$this->runSuite($this->model->testRunner->composeCompleteSuite());
        exit;
	}

	/**
	 * @param string $name
	 */
	public function actionRunSuite($name)
	{
		$this->runSuite($this->model->testRunner->composeSuiteFromFoomoTestSuite((string)$name));
        exit;
	}

	/**
	 *
	 * @param string $suiteName
	 * @param string $caseName
	 */
	public function actionRunTestCase($suiteName, $caseName)
	{
        $this->runSuite(
            $this->model->testRunner->getTestCaseSuite(
                (string) $suiteName,
                (string) $caseName
            )
        );
        exit;
	}

    private static function startStreaming()
    {
        ob_implicit_flush(true);
        ini_set('output_buffering', false);

    }
    public function runSuite(\PHPUnit_Framework_TestSuite $suite, $format = null)
    {
        $cli = php_sapi_name() == 'cli';

        if($format == null) {
            if($cli) {
                $format = 'text';
            } else {
                switch(true) {
                    case isset($_GET['junit']):
                        header('Content-Type: text/xml; charset=utf-8;');
                        $format = 'junit';
                        break;
                    case isset($_GET['text']):
                        header('Content-Type: text/plain; charset=utf-8;');
                        $format = 'text';
                        break;
                    default:
                        $format = 'html';
                }
            }
        }

        switch($format) {
            case 'text':
                MVC::abort();
                self::startStreaming();
                $this->model->currentResult->result->addListener($verbosePrinter = new VerbosePrinter\Text);
                break;
            case 'html':
                MVC::abort();
                self::startStreaming();
                echo HTMLDocument::getInstance()->outputWithOpenBody();
                $this->model->currentResult->result->addListener($verbosePrinter = new VerbosePrinter\HTML);
                break;
            case 'junit':
                MVC::abort();
                $junitPrinter = new \PHPUnit_Util_Log_JUnit();
                $this->model->currentResult->result->addListener($junitPrinter);
                break;
        }
        if(isset($verbosePrinter)) {
            $verbosePrinter->startOutput();
            $verbosePrinter->model = $this->model;
            $this->model->currentResult->verbosePrinter = $verbosePrinter;
        }
        $this->model->testRunner->runASuite($suite, $this->model->currentResult);
        switch($format) {
            case 'text':
            case 'html':
                $verbosePrinter->printResult($this->model->currentResult);
                break;
            case 'junit':
                echo $junitPrinter->getXML();
                break;
            default:

        }
    }
    private function validateNameIsSubclassOf($name, $parentClassname)
    {
		$refl = new \ReflectionClass($name);
		if(!$refl->isSubclassOf($parentClassname)) {
            throw new \InvalidArgumentException("has to inherit from " . $parentClassname);
        }

    }
}
