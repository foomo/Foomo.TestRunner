<? if($model->showTestCases && count($testCases = $model->getTestMethods($testName)) > 0): ?>
	<ul>
		<? foreach($testCases as $testCase): ?>
            <li><?= $view->link($testCase, 'runTestCase', array($testName, $testCase)) ?></li>
		<? endforeach; ?>
	</ul>
<? endif; ?>
