<?php $this->layout(
	'layout',
	array('selectedMenu' => $selectedMenu, 'countData' => $countData, 'username' => $username, )
) ?>

<p>
	These are the issue categories that can be raised against a teaching resource.
</p>

<table class="table">
	<thead>
		<tr>
			<th>
				Code
			</th>
			<th>
				Description
			</th>
			<th>
				Count
			</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($issues as $issue): ?>
			<tr>
				<td>
					<span class="label label-danger"><?php echo $issue['code'] ?></span>
				</td>
				<td>
					<?php echo htmlentities($issue['description'], ENT_HTML5, 'UTF-8') ?>
				</td>
				<td>
					<?php echo $issue['report_count'] ?>
				</td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>

<?php /* @todo Missing a paginator device */ ?>
