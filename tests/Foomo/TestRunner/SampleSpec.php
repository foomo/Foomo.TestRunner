<?php

namespace Foomo\TestRunner;
/**
 * @property SampleWorld $world
 */
class SampleSpec extends AbstractSpec {
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