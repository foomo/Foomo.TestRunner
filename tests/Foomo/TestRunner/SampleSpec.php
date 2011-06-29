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
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 * @property SampleWorld $world
 */
class SampleSpec extends AbstractSpec
{
	//---------------------------------------------------------------------------------------------
	// ~ Public methods
	//---------------------------------------------------------------------------------------------

	public function setUp()
	{
		$this->setWorld(new SampleWorld());
	}

	public function testScenarioSimpleBowling()
	{
		$this->world
				->givenNewGame()
				->whenPlayerRolls('Hansi', 10)
				->whenBuddyDrinksBeer('Sebastian', 'Augustiner')
				->whenBuddyDrinksBeer('Jan', 'Unertl')
				->whenBuddySpillsBeer('Jan')
				->thenTheBeerBillIs(7)
				->thenTheScoreIs(10)
		;
	}
	
	public function testScenarioUmtrunk()
	{
		$this->world
			->givenUmtrunk()
			->whenBuddyDrinksBeer('Udo', 'Augustiner')
			->thenTheBeerBillIs(4)
		;
	}
}