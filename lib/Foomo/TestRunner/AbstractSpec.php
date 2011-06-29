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
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
abstract class AbstractSpec extends \PHPUnit_Framework_TestCase
{
	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 *
	 * @var WorldProxy
	 */
	public $world;

	//---------------------------------------------------------------------------------------------
	// ~ Protected methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param stdClass $world
	 */
	protected function setWorld($world)
	{
		$this->world = new WorldProxy($world, $this);
	}
}