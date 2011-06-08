<?php

namespace Foomo\TestRunner;

class SampleTest extends \PHPUnit_Framework_TestCase {
	/**
	 * @expectedException Exception
	 */
	public function testException() {
		throw new \Foomo\Services\Mock\Exception('test');
	}
	public function testTestIncomplete() 
	{
		$this->markTestIncomplete('incomplete');
	}
	public function testTestSkipped()
	{
		fuckuoff();
		$this->markTestSkipped('php ext bla not present');
	}
	public function testFail()
	{
		//$this->fail('AAAAAAAAAAAAAAAAAArgh');
		$this->assertTrue(false);
	}
	
}