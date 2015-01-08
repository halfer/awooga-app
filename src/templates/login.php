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

<p>
	<a class="btn btn-default" href="?provider=github">Login with GitHub</a>
</p>

<p>
	Awooga uses GitHub as a single sign-on (SSO) provider. Awooga stores just your alias
	(e.g. "https://github.com/username") and the usual things, like your IP address and access
	times. Email addresses and passwords are not stored at all, which is why SSO is so secure. The
	least possible read-only permissions on your account are used, and you can revoke access at
	any time via <a href="https://github.com/settings/applications">your application settings</a>.
</p>

<p>
	If anyone is interested in support for other SSO providers e.g. Twitter, do get in touch.
</p>
