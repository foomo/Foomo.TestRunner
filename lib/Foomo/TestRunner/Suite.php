<?php

namespace Foomo\TestRunner;

abstract class Suite extends \PHPUnit_Framework_TestSuite {
  /**
   * get a list of class name, which will be accumulated into a test as a suite
   *
   * @return array
   */
  abstract public function foomoTestSuiteGetList();
}