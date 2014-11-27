<?php $this->layout(
	'layout',
	array('selectedMenu' => $selectedMenu, 'countData' => $countData, )
) ?>

<table class="table">
	<?php foreach ($reports as $report): ?>
		<tr>
			<td>
				<?php echo htmlentities($report['title'], ENT_HTML5, 'UTF-8') ?>
			</td>
			<td>
				<?php echo htmlentities($report['description'], ENT_HTML5, 'UTF-8') ?>
			</td>
		</tr>
	<?php endforeach ?>
</table>