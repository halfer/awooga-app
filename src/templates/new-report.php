<?php
/*
 * @var array $report A report to edit
 * @var array $errors A list of errors to report, as a result of a failed validation/save
 */
?>

<?php $this->layout(
	'layout',
	array('selectedMenu' => $selectedMenu, 'countData' => $countData, 'username' => $username, )
) ?>

<p>
	A page to create new reports.
</p>

<?php // Report any errors here ?>
<?php if (count($errors) == 1): ?>
	<div class="alert alert-warning">
		<?php echo $this->escape(current($errors)) ?>
	</div>
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
<?php endif ?>

<form id="edit-report" method="post" class="form-horizontal">

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
		<label for="inputTitle" class="col-sm-2 control-label">Title:</label>
		<div class="col-sm-10">
			<input
				type="text"
				name="title"
				value="<?php echo $this->escape($report['title']) ?>"
				id="inputTitle"
				class="form-control"
				placeholder="The title of the resource (you can just copy and paste this from the resource)"
			/>
		</div>
	</div>

	<div class="form-group">
		<label for="reportDescription" class="col-sm-2 control-label">Description:</label>
		<div class="col-sm-10">
			<textarea
				name="description"
				id="reportDescription"
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
				'firstItem' => $ord === 0,
			)
		) ?>
	<?php endforeach ?>

	<div class="form-group">
		<label for="inputDate" class="col-sm-2 control-label">Author notified date:</label>
		<div class="col-sm-10">
			<input
				type="date"
				id="inputDate"
				class="form-control"
				name="author-notified-date"
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

<?php // Move this outside of the form, so elements aren't detected inside it ?>
<?php $this->insert('partials/new-report/templates') ?>


<script type="text/javascript">
	$(document).ready(function() {
		function cloneBlock(type) {
			var lastGroup = $('#edit-report div.' + type + '-group').last();
			$('#template-' + type).
				clone().
				removeAttr('id').
				insertAfter(lastGroup).
				css('display', 'block');			
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
		}

		$('#edit-report').on('click', '.url-remove', function() {
			removeBlock($(this), 'url');
		});
		$('#edit-report').on('click', '.issue-remove', function() {
			removeBlock($(this), 'issue');
		});
	});
</script>