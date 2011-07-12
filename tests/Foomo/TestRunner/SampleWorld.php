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
 */
class SampleWorld
{
	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * @var PHPUnit_Framework_TestCase
	 */
	public $testCase;
	/**
	 * @var int
	 */
	public $score;
	/**
	 * what do we have to pay
	 *
	 * @var int
	 */
	public $billTotal;

	//---------------------------------------------------------------------------------------------
	// ~ Public methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @story given new game
	 * @return Foomo\TestRunner\SampleWorld
	 */
	public function givenNewGame()
	{
		$this->score = 0;
	}

	/**
	 * @story when player "<?= $arg_0 ?>" rolls <?= $arg_1 ?>
	 *
	 * @param string $arg_0 comment
	 * @param integer $arg_1 comment
	 * @return Foomo\TestRunner\SampleWorld
	 */
	public function whenPlayerRolls($arg_0, $arg_1)
	{
		$this->score += $arg_1;
	}

	/**
	 * @story then the score is <?= $arg_0 ?>
	 *
	 * @param integer $arg_0 comment
	 * @return Foomo\TestRunner\SampleWorld
	 */
	public function thenTheScoreIs($arg_0)
	{
		$this->testCase->assertEquals($arg_0, $this->score);
	}

	/**
	 * @story when buddy "<?= $buddy ?>" drinks beer "<?= $brand ?>"
	 * @param string $buddy comment
	 * @param string $brand comment
	 * @return Foomo\TestRunner\SampleWorld
	 */
	public function whenBuddyDrinksBeer($buddy, $brand)
	{
		switch ($brand) {
			case 'Augustiner':
				$this->billTotal += 4;
				break;
			case 'Unertl':
				$this->billTotal += 3;
				break;
		}
	}

	/**
	 * @story then the beer bill is <?= $arg_0 ?> .
	 * @param integer $arg_0 comment
	 * @return Foomo\TestRunner\SampleWorld
	 */
	public function thenTheBeerBillIs($arg_0)
	{
		$this->testCase->assertEquals($arg_0, $this->billTotal);
	}

	/**
	 * @story given umtrunk
	 * @return Foomo\TestRunner\SampleWorld
	 */
	public function givenUmtrunk()
	{

	}
	
	
}