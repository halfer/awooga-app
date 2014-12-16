<?php $this->layout(
	'layout',
	array('selectedMenu' => $selectedMenu, 'countData' => $countData, )
) ?>

<?php if ($isSearch): ?>
	<h2>Search</h2>

	<form>
		<?php $this->insert('partials/search', ['searchString' => $_GET['search'], ]) ?>
	</form>

	<h3 style="margin-top: 8px;"><?php echo $rowCount ?> results</h3>
	
<?php else: ?>
	<p>This is a list of reports currently notified to Awooga.</p>
<?php endif ?>

<?php if ($reports): ?>
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
<?php elseif ($isSearch): ?>
	<p>
		No data to show. Try searching again?
	</p>
<?php endif ?>

<?php $this->insert(
	'partials/paginator',
	['maxPage' => $maxPage, 'currentPage' => $currentPage, 'urlPrefix' => '/browse', ]
) ?>
