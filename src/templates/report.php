<?php $this->layout(
	'layout',
	array('selectedMenu' => $selectedMenu, 'countData' => $countData, 'username' => $username, )
) ?>

<p>
	This is information about a single report in the Awooga system.
</p>

<div class="alert alert-info">
	If you are the author of the referenced work, please note that it appears here because it has
	been judged to contain serious errors, usually of a security nature. Please read the
	introductory notes on the <a href="/">home page</a>.
</div>

<?php if ($isOwner): ?>
	<p class="pull-right">
		<a href="/report/<?php echo $report['id'] ?>/edit">Edit report</a>
	</p>
<?php endif ?>

<h1><?php echo $this->escape($report['title']) ?></h1>

<table class="table table-bordered" id="report">
	<tbody>
		<tr>
			<th
				rowspan="<?php echo count($report['urls']) ?>"
				>URLs</th>
			<?php foreach ($report['urls'] as $ord => $url): ?>
				<?php if ($ord): ?>
					</tr><tr>
				<?php endif ?>
				<td colspan="3">
					<a
						href="<?php echo $this->escape($url['url']) ?>"
						alt="Link to learning resource"
						rel="nofollow"
					><?php echo $this->escape($url['url']) ?></a>
				</td>
			<?php endforeach ?>
		</tr>
		<tr>
			<th
				rowspan="<?php echo count($report['issues']) ?>"
				>Issues</th>
			<?php foreach ($report['issues'] as $ord => $issue): ?>
				<?php if ($ord): ?>
					</tr><tr>
				<?php endif ?>
				<td class="issues">
					<?php if ($issue['resolved_at']): ?>
						<?php // Doesn't need escaping,  but let's do it anyway ?>
						<span class="label label-success"
							><?php echo $this->escape($issue['code']) ?></span>
					<?php else: ?>
						<span class="label label-danger"
							><?php echo $this->escape($issue['code']) ?></span>
					<?php endif ?>
				</td>
				<?php // This is the date resolved column ?>
				<td>
					<?php if ($issue['resolved_at']): ?>
						<?php echo $issue['resolved_at'] ?>
					<?php else: ?>
						Unresolved
					<?php endif ?>
				</td>
				<?php // This is an optional description field, from the reporter ?>
				<td>
					<?php if ($issue['description_html']): ?>
						<?php // Converted from markdown, so should be safe ?>
						<?php echo $issue['description_html'] ?>
					<?php else: ?>
						(No comments added)
					<?php endif ?>
				</td>
			<?php endforeach ?>
		</tr>
		<tr>
			<th>Description</th>
			<td colspan="3">
				<?php // Converted from markdown, so should be safe ?>
				<?php echo $report['description_html'] ?>
			</td>
		</tr>
		<tr>
			<th>Source</th>
			<td colspan="3">
				<?php $this->insert('partials/source', ['report' => $report, ]) ?>
			</td>
		</tr>
	</tbody>
</table>
