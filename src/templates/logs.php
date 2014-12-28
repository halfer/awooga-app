<?php $this->layout(
	'layout',
	array('selectedMenu' => $selectedMenu, 'countData' => $countData, 'username' => $username, )
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

<?php $this->insert(
	'partials/paginator',
	['maxPage' => $maxPage, 'currentPage' => $currentPage, 'urlPrefix' => '/logs', ]
) ?>
