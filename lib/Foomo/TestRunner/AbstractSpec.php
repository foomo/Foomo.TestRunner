<?php

namespace Foomo\TestRunner;
/**
 *	Given
 *		I'm a software developer who loves TDD and communicating well with customers,
 *	When
 *		I hear a talk on BDD that seems to promise exactly what I get from TDD and good customer communication,
 *	Then
 *		I find myself wondering exactly what all the fuss is about.
 *
 *	http://anthonybailey.livejournal.com/34156.html
 *
 *
 */
abstract class AbstractSpec extends \PHPUnit_Framework_TestCase {
	/**
	 *
	 * @var WorldProxy
	 */
	public $world;

	protected function setWorld($world)
	{
		$this->world = new WorldProxy($world, $this);
	}
}