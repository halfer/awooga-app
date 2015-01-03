<?php // Template for a URL ?>
<?php $this->insert(
	'partials/new-report/url',
	array(
		'id' => 'template-url',
		'url' => '',
		'firstItem' => false,
	)
) ?>

<?php // Template for an issue ?>
<?php $this->insert(
	'partials/new-report/issue',
	array(
		'id' => 'template-issue',
		'description' => '',
		'typeCode' => null,
		'firstItem' => false,
	)
) ?>
