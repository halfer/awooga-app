<?php $this->layout(
	'layout',
	array('selectedMenu' => $selectedMenu, 'countData' => $countData, )
) ?>

<p>
	This is a list of repositories from which Awooga imports its reports. They are all public
	access, so you should be able to see the edit history too.
</p>

<table class="table">
	<thead>
		<tr>
			<th>
				URL
			</th>
			<th>
				Reports
			</th>
			<th>
				Creation date
			</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($repos as $repo): ?>
			<tr>
				<td>
					<?php echo htmlentities($repo['url'], ENT_HTML5, 'UTF-8') ?>
				</td>
				<td><?php echo $repo['report_count'] ?></td>
				<td><?php echo $repo['created_at'] ?></td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>
