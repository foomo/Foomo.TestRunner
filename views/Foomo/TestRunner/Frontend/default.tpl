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
<div id="fullContent">
	
	<div class="rightBox">
		<?= $view->link('Pack everything into a suite and run it all', 'runAll', array(), array('class' => 'linkButtonYellow')) ?>
	</div>

	<h2>TestRunner - Modules</h2>

<? if(Foomo\Config::getMode()!= Foomo\Config::MODE_TEST): ?>

	<div class="errorMessage">
		The test runner is only available, when the run mode is set to <i>test</i> -you are currently in <i><?= Foomo\Config::getMode(); ?></i>
	</div>
	
<? else: ?>
		
	<? foreach($tests as $domain => $stuff): ?>
	<? if(count($stuff['tests'])>0 || count($stuff['suites'])>0 || count($stuff['specs'])>0): ?>
		<?
		$domainId = 'domain' . $domain;
		$domainTestId = $domainId . 'Tests';
		$domainSuiteId = $domainId . 'Suites';
		$allDomainSuiteTests = array();foreach($stuff['suites'] as $suiteName) {
			$allDomainSuiteTests = array_merge($allDomainSuiteTests, $model->getSuiteTests($suiteName));
		}
		?>
	
	<div class="toggleBox">
		<div class="toogleButton">
			<div class="toggleOpenIcon">+</div>
			<div class="toggleOpenContent"> <?= $domain ?></div>
			<div class="toggleOpenInfo">
				<?= $view->link('Test module', 'runModuleTests', array('moduleName' => $domain), array('title' => 'run all tests in this module', 'class' => 'linkButtonSmallYellow')) ?>
			</div>
		</div>
		<div class="toggleContent">
			
			<table>
				<thead>
					<tr>
						<th width="33%">Tests</th>
						<th width="33%">Specs</th>
						<th width="33%">Suites</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							
							<?
							$nonSuiteTests = array();
							foreach($stuff['tests'] as $testName) {
								if(!in_array($testName, $allDomainSuiteTests)) {
									$nonSuiteTests[] = $testName;
								}
							}
							?>
							
							<? if(count($nonSuiteTests)>0): ?>
								<ul>
								<? foreach($nonSuiteTests as $testName): ?>
									<li>
										<?= $view->link($testName,  'runTest', array('name' => $testName ));?>
										<?= $view->partial('testMethods', array('testName' => $testName)) ?>
									</li>
								<? endforeach; ?>
								</ul>
							<? else: ?>
								<b>No tests!</b>
							<? endif; ?>
							
						</td>
						<td>
							
							<? if(count($stuff['specs'])>0): ?>
								<ul>
									<? foreach($stuff['specs'] as $specName): ?>
										<li>
											<?= $view->link($specName,  'runTest', array('name' => $specName ));?>
											<?= $view->partial('testMethods', array('testName' => $specName)) ?>
										</li>

									<? endforeach; ?>
								</ul>
							<? else: ?>
								<b>No specs!</b>
							<? endif; ?>
							
						</td>
						<td>
							
							<? if(count($stuff['suites'])>0): ?>
								<? foreach($stuff['suites'] as $suiteName): ?>
								<ul>	
									<li>
										<h3><?
												$suiteTests = $model->getSuiteTests($suiteName);
											?><?= $view->link($suiteName, 'runSuite', array('name' => $suiteName ));?></h3>
										<ul>
											<?
                                                $isAFoomoSuite = $model->isAFoomoSuite($suiteName);
                                                foreach($suiteTests as $testName):
                                            ?>
												<li>
                                                    <? if($isAFoomoSuite): ?>
    													<?= $view->link($testName, 'runTest', array('name' => $testName )) ?>
	    												<?= $view->partial('testMethods', array('testName' => $testName)) ?>
                                                    <? else: ?>
                                                        <?= $view->escape($testName) ?>
                                                    <? endif; ?>
												</li>
											<? endforeach; ?>
										</ul>
									</li>
								<? endforeach; ?>
								</ul>
							<? else: ?>
								<b>No suites!</b>
							<? endif; ?>
							
						</td>
					</tr>
				</tbody>
			</table>
						
		</div>
	</div>
	
	<? endif; ?>
	<? endforeach; ?>

<? endif; ?>
<div>