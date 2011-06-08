<code>
	<h4><?= $model->name ?> has failed saying: <i class="nok"><b>&quot;<?= $model->message ?>&quot;</b></i></h4>
	<ol>
		<? foreach($model->stack as $stackEntry): ?>
			<li title="<?= htmlspecialchars($stackEntry->file . ' - line ' . $stackEntry->line) ?>">
				<?= $stackEntry->call ?>(<?= htmlspecialchars(implode(', ', $stackEntry->args)) ?>)
			</li>
		<? endforeach; ?>		
	</ol>
</code>