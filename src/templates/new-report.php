<?php $this->layout(
	'layout',
	array('selectedMenu' => $selectedMenu, 'countData' => $countData, 'username' => $username, )
) ?>

<p>
	A page to create new reports.
</p>

<form method="post">
	<p>This form will save a demo report against this user.</p>

	<input type="submit" value="Save" />
</form>