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
				Added at
			</th>
			<th>
				Next update at
			</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($repos as $repo): ?>
			<tr>
				<td>
					<a
						href="<?php echo $this->escape($repo['url']) ?>"
					><?php echo $this->escape($repo['url']) ?></a>
				</td>
				<td><?php echo $repo['report_count'] ?></td>
				<td><?php echo $repo['created_at'] ?></td>
				<td><?php echo $repo['due_at'] ?></td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>

<?php /* @todo Missing a paginator device */ ?>
