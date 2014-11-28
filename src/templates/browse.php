<?php $this->layout(
	'layout',
	array('selectedMenu' => $selectedMenu, 'countData' => $countData, )
) ?>

<table class="table">
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
				<?php else: ?>
					<?php echo htmlentities($report['title'], ENT_HTML5, 'UTF-8') ?>
				<?php endif ?>
			</td>
			<td>
				<?php echo htmlentities($report['description'], ENT_HTML5, 'UTF-8') ?>
			</td>
		</tr>
	<?php endforeach ?>
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
