<?php // Expects $report to be populated ?>

<tr>
	<td>
		<a
			href="/report/<?php echo $report['id'] ?>"
			alt="More info"
		><?php echo $this->escape($report['title']) ?></a>
	</td>
	<td>
		<?php // If there is a 0th URL (there should be) let's use that ?>
		<?php if (isset($report['urls'][0])): ?>
			<a
				href="<?php echo $this->escape($report['urls'][0]) ?>"
				alt="Primary link for this resource"
				rel="nofollow"
				target="_blank"
			>Primary link</a>

			<?php // If there are more URLs, list them here ?>
			<?php if (count($report['urls']) > 1): ?>
				<small>[
					<?php for($urlId = 1; $urlId < count($report['urls']); $urlId++): ?>
						<a
							href="<?php echo $this->escape($report['urls'][$urlId]) ?>"
							alt="Secondary link for this resource"
							nofollow="nofollow"
							target="_blank"
						>Secondary link</a>
					<?php endfor ?>
				]</small>
			<?php endif ?>
		<?php else: ?>
			<?php echo $this->escape($report['title']) ?>
		<?php endif ?>
	</td>
	<?php // Here are the issues lozenges ?>
	<td class="issues">
		<?php foreach ($report['issues'] as $issue): ?>
			<?php if ($issue['resolved_at']): ?>
				<span class="label label-success">
					<?php echo $issue['code'] ?> (fixed)
				</span>
			<?php else: ?>
				<span class="label label-danger">
					<?php echo $issue['code'] ?>
				</span>
			<?php endif ?>
		<?php endforeach ?>
	</td>
	<td>
		<?php // Converted from markdown, so should be safe ?>
		<?php echo $report['description_html'] ?>
	</td>
	<td>
		#<?php echo $report['repository_id'] ?>
	</td>
</tr>
