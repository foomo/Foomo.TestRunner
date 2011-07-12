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
	<? if(count($stuff['tests'])>0 || count($stuff['suites'])>0): ?>
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
			<div class="toggleOpenContent"><?= $domain ?></div>
		</div>
		<div class="toggleContent">
			
			<?= $view->link('Test '.$domain, 'runModuleTests', array('moduleName' => $domain), array('title' => 'run all tests in this module', 'class' => 'linkButtonYellow')) ?>
			
			<div class="tabBox">
				<div class="tabNavi">
					<ul>
						<li class="selected">Tests</li>
						<li>Specs</li>
						<li>Suites</li>
					</ul>
					<hr class="greyLine">
				</div>
				<div class="tabContentBox">
				
					<div class="tabContent tabContent-1 selected">
					
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
							<h2>Tests (<?= count($nonSuiteTests) ?>)</h2>
							
							<div class="greyBox">
								<div class="innerBox">
									<ul>
									<? foreach($nonSuiteTests as $testName): ?>
										<li>
											<?= $view->link($testName,  'runTest', array('name' => $testName ));?>
											<?= $view->partial('testMethods', array('testName' => $testName)) ?>
										</li>
									<? endforeach; ?>
									</ul>
								</div>
							</div>
						<? else: ?>
							<h2>No tests!</h2>
						<? endif; ?>

						
					</div>
					
					<div class="tabContent tabContent-2">
					
						<? if(count($stuff['specs'])>0): ?>
							<h2>Specs (<?= count($stuff['specs']) ?>)</h2>
							
							<div class="greyBox">
								<div class="innerBox">
									<ul>
										<? foreach($stuff['specs'] as $specName): ?>
											<li>
												<?= $view->link($specName,  'runTest', array('name' => $specName ));?>
												<?= $view->partial('testMethods', array('testName' => $specName)) ?>
											</li>

										<? endforeach; ?>
									</ul>
								</div>
							</div>
						<? else: ?>
							<h2>No specs!</h2>
						<? endif; ?>
						
					</div>
					
					<div class="tabContent tabContent-3">
					
						<? if(count($stuff['suites'])>0): ?>
							<h2>Suites (<?= count($stuff['suites']) ?>)</h2>
							
							<div class="greyBox">
								<div class="innerBox">
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
								</div>
							</div>
						<? else: ?>
							<h2>No suites!</h2>
						<? endif; ?>

					</div>
					
				</div>
			</div>
						
		</div>
	</div>
	
	<? endif; ?>
	<? endforeach; ?>

<? endif; ?>
<div>