<?
/* @var $model Foomo\TestRunner\Frontend\Model */
/* @var $result PHPUnit_Framework_TestResult */
/* @var $testSuite PHPUnit_Framework_TestSuite */

$result = $model->currentResult->result;
$testSuite = $model->currentResult->testSuite;
$buffer = $model->currentResult->buffer;
$errorBuffer = $model->currentResult->errorBuffer;
$phpErrors = $model->currentResult->phpErrors;

if($result->errorCount() > 0 || $result->failureCount() > 0) {
  $css = 'nok';
} else {
  $css = 'ok';
}

exit;

?>

<div class="innerBox">
		<h2><?= $model->currentResult->name ?> <small>run <?= $result->count() . ' (' . round($result->time(), 4) . ')'; if($result->notImplementedCount() > 0): ?>, not implemented <?= $result->notImplementedCount();endif;  ?></small></h2>
	
		<? if($result->errorCount() > 0): ?>
			<h3>Errors (<?= $result->errorCount() ?>) :</h3>
			<?= \Foomo\TestRunner\ErrorPrinter\Html::renderErrors($result->errors())?>
		<? endif; ?>
		<? if($result->failureCount() > 0): ?>
			<h3>Failures (<?= $result->failureCount() ?>) :</h3>
			<?= \Foomo\TestRunner\ErrorPrinter\Html::renderErrors($result->failures()) ?>
		<? endif; ?>
		<? if(strlen($buffer)>0): ?>
			<h3>buffer output</h3>
			<pre><?= $buffer ?></pre>
		<? endif; ?>
		<? if(count($errorBuffer)>0): ?>
			<h3>caught E_USER_WARNING, E_USER_NOTICE, E_STRICT</h3>
			<pre><? foreach($errorBuffer as $b): ?><?= 	$b['name'] . ' in <acronym title="'.$b['file'].'">' . basename($b['file']) . '</acronym> : ' . $b['line'] . ' :: ' . $b['error'] . PHP_EOL; ?><? endforeach; ?></pre>
		<? endif; ?>
		<? if($phpErrors): ?>
			<h3>php errors</h3>
			<pre><?= $phpErrors ?></pre>
		<? endif; ?>

</div>