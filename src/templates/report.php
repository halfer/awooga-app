<?php $this->layout(
	'layout',
	array('selectedMenu' => $selectedMenu, 'countData' => $countData, )
) ?>

<p>
	This is information about a single report in the Awooga system.</p>

<div class="alert alert-info">
	If you are the author of the referenced work, please note that it appears here because it has
	been judged to contain serious errors, usually of a security nature. Please read the
	introductory notes on the <a href="/">home page</a>.
</div>

<h1><?php echo htmlentities($report['title'], ENT_HTML5, 'UTF-8') ?></h1>

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
						href="<?php echo htmlentities($url['url'], ENT_HTML5, 'UTF-8') ?>"
						alt="Link to learning resource"
						rel="nofollow"
					><?php echo htmlentities($url['url'], ENT_HTML5, 'UTF-8') ?></a>
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
							><?php echo htmlentities($issue['code'], ENT_HTML5, 'UTF-8') ?></span>
					<?php else: ?>
						<span class="label label-danger"
							><?php echo htmlentities($issue['code'], ENT_HTML5, 'UTF-8') ?></span>
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
	</tbody>
</table>
