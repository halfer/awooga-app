<?php $this->layout(
	'layout',
	array('selectedMenu' => $selectedMenu, 'countData' => $countData, )
) ?>

<table class="table" id="reports">
	<thead>
		<tr>
			<th>Links</th>
			<th>Issues</th>
			<th>Description</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($reports as $report): ?>
			<tr>
				<td>
					<?php // If there is a 0th URL (there should be) let's use that ?>
					<?php if (isset($report['urls'][0])): ?>
						<a
							href="<?php echo htmlentities($report['urls'][0], ENT_HTML5, 'UTF-8') ?>"
							alt="Primary link for this resource"
							nofollow="nofollow"
							target="_blank"
						><?php echo htmlentities($report['title'], ENT_HTML5, 'UTF-8') ?></a>

						<?php // If there are more URLs, list them here ?>
						<?php if (count($report['urls']) > 1): ?>
							<small>[
								<?php for($urlId = 1; $urlId < count($report['urls']); $urlId++): ?>
									<a
										href="<?php echo htmlentities($report['urls'][$urlId], ENT_HTML5, 'UTF-8') ?>"
										alt="Secondary link for this resource"
										nofollow="nofollow"
										target="_blank"
									>Secondary link</a>
								<?php endfor ?>
							]</small>
						<?php endif ?>
					<?php else: ?>
						<?php echo htmlentities($report['title'], ENT_HTML5, 'UTF-8') ?>
					<?php endif ?>
				</td>
				<?php // Here are the issues lozenges ?>
				<td class="issues">
					<?php foreach ($report['issues'] as $issue): ?>
						<span class="label label-danger">
							<?php echo $issue ?>
						</span>
					<?php endforeach ?>
				</td>
				<td>
					<?php echo htmlentities($report['description'], ENT_HTML5, 'UTF-8') ?>
				</td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>

<?php if ($maxPage > 1): ?>
	<div id="paginator">
		<?php for($pageNo = 1; $pageNo <= $maxPage; $pageNo++): ?>
			<?php if ($pageNo == $currentPage): ?>
				<span><?php echo $pageNo ?></span>
			<?php else: ?>
				<span>
					<a href="/browse/<?php echo $pageNo ?>"><?php echo $pageNo ?></a>
				</span>
			<?php endif ?>
		<?php endfor ?>
	</div>
<?php endif ?>
