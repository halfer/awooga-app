<?php
/*
 * Shared by the report browse and detail modes
 */
?>

<?php if ($report['repository_id']): ?>
	Repo:&nbsp;<?php echo $report['repository_id'] ?>
<?php else: ?>
	User:&nbsp;<?php echo $this->escape($report['username'], 'trim_username') ?>
<?php endif ?>
