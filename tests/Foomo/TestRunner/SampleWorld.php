<?php

namespace Foomo\TestRunner;

class SampleWorld {

	/**
	 *
	 * @var \PHPUnit_Framework_TestCase
	 */
	public $testCase;
	public $score;
	/**
	 *
	 * @var what do we have to pay
	 */
	public $billTotal;

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
	 *
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
	 * 
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
	 *
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