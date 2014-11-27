<?php $this->layout('layout', array('selectedMenu' => $selectedMenu, )) ?>

<table class="table">
	<?php foreach ($logs as $log): ?>
		<tr>
			<td>
				<?php echo $log['log_type'] ?>
			</td>
			<td>
				<?php echo $log['log_level'] ?>
			</td>
			<td>
				<?php echo $log['created_at'] ?>
			</td>
		</tr>
	<?php endforeach ?>
</table>