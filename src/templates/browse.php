<?php $this->layout(
	'layout',
	array('selectedMenu' => $selectedMenu, 'countData' => $countData, )
) ?>

<p>This is a list of reports currently notified to Awooga.</p>

<table class="table" id="reports">
	<thead>
		<tr>
			<th>Title</th>
			<th>Links</th>
			<th>Issues</th>
			<th>Description</th>
			<th>Repository</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($reports as $report): ?>
			<?php $this->insert('partials/browse-row', ['report' => $report, ]) ?>
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
