<?php
/*
 * @var array $report A report to edit
 * @var array $issues The list of permitted issue codes
 * @var array $errors A list of errors to report, as a result of a failed validation/save
 * @var integer $editId If applicable the ID we are editing
 */
?>

<?php $this->layout(
	'layout',
	array('selectedMenu' => $selectedMenu, 'countData' => $countData, 'username' => $username, )
) ?>

<?php if ($editId): ?>
	<p class="pull-right">
		<a href="/report/<?php echo $editId ?>">View report</a>
	</p>
<?php endif ?>

<p>
	A page to create new reports.
</p>

<?php // Report any errors here ?>
<?php if (count($errors) == 1): ?>
	<div class="alert alert-warning">
		<?php echo $this->escape(current($errors)) ?>
	</div>
<?php // Multiple errors go here ?>
<?php elseif (count($errors) > 1): ?>
	<div class="alert alert-warning">
		<ul>
			<?php foreach ($errors as $error): ?>
				<li>
					<?php echo $this->escape($error) ?>
				</li>
			<?php endforeach ?>
		</ul>
	</div>
<?php // Save success message goes here ?>
<?php elseif ($saveMessage): ?>
	<div class="alert alert-success">
		<?php echo $this->escape($saveMessage) ?>
	</div>
<?php endif ?>

<form
	id="edit-report"
	method="post"
	class="form-horizontal"
	action="<?php echo $editId ? "/report/$editId/edit" : '/report/new' ?>"
>

	<?php foreach ($report['urls'] as $ord => $url): ?>
		<?php $this->insert(
			'partials/new-report/url',
			array(
				'id' => false,
				'url' => $url,
				'firstItem' => $ord === 0,
			)
		) ?>
	<?php endforeach ?>
	
	<div class="form-group">
		<label for="input-title" class="col-sm-2 control-label">Title:</label>
		<div class="col-sm-10">
			<input
				type="text"
				name="title"
				value="<?php echo $this->escape($report['title']) ?>"
				id="input-title"
				class="form-control"
				placeholder="The title of the resource (you can just copy and paste this from the resource)"
			/>
		</div>
	</div>

	<div class="form-group">
		<label for="report-description" class="col-sm-2 control-label">Description:</label>
		<div class="col-sm-10">
			<textarea
				name="description"
				id="input-report-description"
				class="form-control"
				rows="3"
				placeholder="An English description of the problem(s) should go here (Markdown is supported)"
			><?php echo $this->escape($report['description']) ?></textarea>
		</div>
	</div>

	<?php foreach ($report['issues'] as $ord => $issue): ?>
		<?php $this->insert(
			'partials/new-report/issue',
			array(
				'id' => false,
				'description' => $issue['description'],
				'typeCode' => $issue['issue_cat_code'],
				'resolvedAt' => $issue['resolved_at'],
				'issues' => $issues,
				'firstItem' => $ord === 0,
			)
		) ?>
	<?php endforeach ?>

	<div class="form-group">
		<label for="input-notified-date" class="col-sm-2 control-label">Author notified date:</label>
		<div class="col-sm-10">
			<input
				type="date"
				id="input-notified-date"
				class="form-control"
				name="author-notified-at"
				value="<?php echo $this->escape($report['author_notified_at']) ?>"
				placeholder="The date the author was notified, in the format yyyy-mm-dd (optional)"
			/>
		</div>
	</div>

	<input
		type="submit"
		class="btn btn-default"
		value="Save"
	/>

</form>

<?php // This is outside of the form, so elements aren't detected inside it ?>
<?php $this->insert('partials/new-report/templates', array('issues' => $issues, )) ?>

<script type="text/javascript">
	$(document).ready(function() {
		function cloneBlock(type) {
			var lastGroup = $('#edit-report div.' + type + '-group').last();
			$('#template-' + type).
				clone().
				removeAttr('id').
				insertAfter(lastGroup);
		}

		$('#edit-report').on('click', '.url-add', function() {
			cloneBlock('url');
		});
		$('#edit-report').on('click', '.issue-add', function() {
			cloneBlock('issue');
		});

		// @todo In some cases we need to add in an 'id' to get the label working
		function removeBlock(clicked, type) {
			// See if we are allowed to delete
			if ($('#edit-report .' + type + '-group').length === 1) {
				alert("You must have at least one " + type);
				return;
			}

			// If the add button is not hidden, then we deleted the top row
			var add = clicked.siblings('.' + type + '-add.hide');

			if (add.length === 0) {
				// ... need to make a new top row
				var secondRow = $('#edit-report .' + type + '-group').eq(1);
				secondRow.find('.' + type + '-add').removeClass('hide');
				secondRow.find('label').css('visibility', 'visible');
			}

			clicked.parents('.form-group').remove();

			// If we no longer have an id on the first element, add it again
			if ($('#input-' + type).length === 0)
			{
				$('#edit-report div.' + type + '-group').
					first().
					find('input,select').
					attr('id', 'input-' + type);
			}
		}

		$('#edit-report').on('click', '.url-remove', function() {
			removeBlock($(this), 'url');
		});
		$('#edit-report').on('click', '.issue-remove', function() {
			removeBlock($(this), 'issue');
		});
	});
</script>
