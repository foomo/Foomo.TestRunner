<?
/* @var $model Foomo\TestRunner\Frontend\Model */
/* @var $view Foomo\MVC\View */

$tests = array();

foreach(Foomo\Modules\Manager::getEnabledModules() as $enabledModuleName) {
	$tests[$enabledModuleName] = array(
		'tests' => $model->getModuleTests($enabledModuleName),
		'suites' => $model->getModuleSuites($enabledModuleName),
		'specs' => $model->getModuleSpecs($enabledModuleName)
	);
}
$suiteTests = array();
?>
<h1>TestRunner</h1>
<? if(Foomo\Config::getMode()!= Foomo\Config::MODE_TEST): ?>
	<h1>The test runner is only available, when the run mode is set to <i>test</i> -you are currently in <i><?= Foomo\Config::getMode(); ?></i></h1>
<? else: ?>
	<h2><?= $view->link('Pack everything into a suite and run it all', 'runAll') ?></h2>
	<h2>Modules</h2>
	<ul>
		<? foreach($tests as $domain => $stuff):
			if(!(count($stuff['tests'])>0 || count($stuff['suites'])>0)) {
				continue;
			}
		?>
			<li><a href="#<?= $domain ?>"><?= $domain ?></a></li>
		<? endforeach; ?>
	</ul>
	<? if($model->showTestCases): ?>
		<?= $view->link('hide test cases', 'default', array(false)) ?>
	<? else: ?>
		<?= $view->link('show test cases', 'default', array(true)) ?>
	<? endif; ?>
	<div id="testRunnerMenu">
		<table id="testRunnerMenuTable">
			<thead>
				<tr>
					<td>Modules</td>
					<td>Tests</td>
					<td>Specs</td>
					<td>Suites</td>
				</tr>
			</thead>
			<tbody>
				<? foreach($tests as $domain => $stuff): ?>
				<? if(count($stuff['tests'])>0 || count($stuff['suites'])>0): ?>
					<?
					$domainId = 'domain' . $domain;
					$domainTestId = $domainId . 'Tests';
					$domainSuiteId = $domainId . 'Suites';
					$allDomainSuiteTests = array();foreach($stuff['suites'] as $suiteName) {
						$allDomainSuiteTests = array_merge($allDomainSuiteTests, $model->getSuiteTests($suiteName));
					}
					?>
			<tr>
				<td>
					<h1><?= $view->link($domain, 'runModuleTests', array('moduleName' => $domain), array('title' => 'run all tests in this module', null, $domain)) ?></h1>
				</td>
				<td>
					<?
					// how many non suite tests
					$nonSuiteTests = array();
					foreach($stuff['tests'] as $testName) {
						if(!in_array($testName, $allDomainSuiteTests)) {
							$nonSuiteTests[] = $testName;
						}
					}
					?>
					<? if(count($nonSuiteTests)>0): ?>
						<h2>tests (<?= count($nonSuiteTests) ?>)</h2>
					<? else: ?>
						<h2>no tests</h2>
					<? endif; ?>
					<ul>
					<? foreach($nonSuiteTests as $testName): ?>
						<li>
							<?= $view->link($testName,  'runTest', array('name' => $testName ));?>
							<?= $view->partial('testMethods', array('testName' => $testName)) ?>
						</li>
					<? endforeach; ?>
					</ul>
				</td>
				<td>
					<? if(count($stuff['specs'])>0): ?>
						<h2>specs (<?= count($stuff['specs']) ?>)</h2>
						<ul>
							<? foreach($stuff['specs'] as $specName): ?>
								<li>
									<?= $view->link($specName,  'runTest', array('name' => $specName ));?>
									<?= $view->partial('testMethods', array('testName' => $specName)) ?>
								</li>

							<? endforeach; ?>
						</ul>
					<? else: ?>
						<h2>no specs</h2>
					<? endif; ?>
				</td>
				<td>
					<? if(count($stuff['suites'])>0): ?>
						<h2>suites (<?= count($stuff['suites']) ?>)</h2>
					<? else: ?>
						<h2>no suites</h2>
					<? endif; ?>
					<ul id="<?= $domainSuiteId ?>">
					<? foreach($stuff['suites'] as $suiteName): ?>
						<li>
							<h3><?
									$suiteTests = $model->getSuiteTests($suiteName);
								?><?= $view->link($suiteName, 'runSuite', array('name' => $suiteName ));?></h3>
							<ul>
								<? foreach($suiteTests as $testName): ?>
									<li>
										<?= $view->link($testName, 'runTest', array('name' => $testName )) ?>
										<?= $view->partial('testMethods', array('testName' => $testName)) ?>
									</li>
								<? endforeach; ?>
							</ul>
						</li>
					<? endforeach; ?>
					</ul>
				</td>
			</tr>
				<? endif; ?>
				<? endforeach; ?>
			</tbody>
		</table>
	</div>
<? endif; ?>