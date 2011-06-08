<?= '<?php'; ?>

class FoomoModuleTestSuite<?= ucfirst($model) ?> extends PHPUnit_Framework_TestSuite {
	public static function suite()
	{
		$testRunner = new FoomoTestRunner;
		return $testRunner->composeModuleSuite('<?= $model ?>');
	}
}