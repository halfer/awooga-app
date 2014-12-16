<?php
/*
 * Variables:
 * 
 * $maxPage
 * $currentPage
 * $urlPrefix string Prefix for all links, e.g. /logs/
 */
?>

<?php if ($maxPage > 1): ?>
	<div id="paginator">
		<?php for($pageNo = 1; $pageNo <= $maxPage; $pageNo++): ?>
			<?php if ($pageNo == $currentPage): ?>
				<span><?php echo $pageNo ?></span>
			<?php else: ?>
				<span>
					<a
						href="<?php echo $this->escape($urlPrefix) ?>/<?php echo $pageNo ?>"
					><?php echo $pageNo ?></a>
				</span>
			<?php endif ?>
		<?php endfor ?>
	</div>
<?php endif ?>
