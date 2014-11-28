<?php $this->layout(
	'layout',
	array('selectedMenu' => $selectedMenu, 'countData' => $countData, )
) ?>

<p>
	This is a peek into the Awooga import process, for the curious. The codes "trivial" and "serious"
	are different levels of error.
</p>

<table class="table">
	<thead>
		<tr>
			<th>Type</th>
			<th>Level</th>
			<th>Message</th>
			<th>Date/time</th>
			<th>Repository</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($logs as $log): ?>
			<tr>
				<td>
					<?php echo $log['log_type'] ?>
				</td>
				<td>
					<span class="label label-<?php echo $log['log_level'] == 'success' ? 'success' : 'danger' ?>">
						<?php echo $log['log_level'] ?>
					</span>
				</td>
				<td>
					<?php echo htmlentities($log['message'], ENT_HTML5, 'UTF-8') ?>
				</td>
				<td>
					<?php echo $log['created_at'] ?>
				</td>
				<td>
					#<?php echo $log['repository_id'] ?>
				</td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>

<?php // @todo Make this a partial, so it can be shared with browse.php ?>
<?php if ($maxPage > 1): ?>
	<div id="paginator">
		<?php for($pageNo = 1; $pageNo <= $maxPage; $pageNo++): ?>
			<?php if ($pageNo == $currentPage): ?>
				<span><?php echo $pageNo ?></span>
			<?php else: ?>
				<span>
					<a href="/logs/<?php echo $pageNo ?>"><?php echo $pageNo ?></a>
				</span>
			<?php endif ?>
		<?php endfor ?>
	</div>
<?php endif ?>
