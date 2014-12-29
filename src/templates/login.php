<?php $this->layout(
	'layout',
	array('selectedMenu' => $selectedMenu, 'countData' => $countData, 'username' => $username, )
) ?>

<?php // Remote login issues can be presented here ?>
<?php if ($error): ?>
	<div class="alert alert-danger" role="alert">
		<?php echo $this->escape($error) ?>
	</div>
<?php endif ?>

<?php // We've come from a requires auth redirect ?>
<?php if ($requiresAuth): ?>
	<div class="alert alert-info" role="alert">
		That operation requires you to sign in first; you can do that on this page.
	</div>	
<?php endif ?>

<a href="?provider=github">Login with GitHub</a>
