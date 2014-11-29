<?php $this->layout(
	'layout',
	array('selectedMenu' => $selectedMenu, 'countData' => $countData, )
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
			</tr>
		<?php endforeach ?>
	</tbody>
</table>