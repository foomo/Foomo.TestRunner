<?= '<?php'; ?>

class FoomoModuleTestSuite<?= ucfirst($model) ?> extends PHPUnit_Framework_TestSuite {
	public static function suite()
	{
		$testRunner = new \Foomo\TestRunner;
		return $testRunner->composeModuleSuite('<?= $model ?>');
	}
}