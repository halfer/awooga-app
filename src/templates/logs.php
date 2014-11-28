<?php $this->layout(
	'layout',
	array('selectedMenu' => $selectedMenu, 'countData' => $countData, )
) ?>

<table class="table">
	<?php foreach ($logs as $log): ?>
		<tr>
			<td>
				<?php echo $log['log_type'] ?>
			</td>
			<td>
				<?php echo $log['log_level'] ?>
			</td>
			<td>
				<?php echo $log['created_at'] ?>
			</td>
		</tr>
	<?php endforeach ?>
</table>

<?php // @todo Make this a partial, so it can be shared with browse.php ?>
<?php if ($maxPage > 1): ?>
	<div id="paginator">
		<?php for($pageNo = 1; $pageNo <= $maxPage; $pageNo++): ?>
			<?php if ($pageNo == $currentPage): ?>
				<span><?php echo $pageNo ?></span>
			<?php else: ?>
				<span>
					<a href="/logs/<?php echo $pageNo ?>"><?php echo $pageNo ?></a>
				</span>
			<?php endif ?>
		<?php endfor ?>
	</div>
<?php endif ?>
