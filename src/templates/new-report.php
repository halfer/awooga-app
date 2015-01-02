<?php $this->layout(
	'layout',
	array('selectedMenu' => $selectedMenu, 'countData' => $countData, 'username' => $username, )
) ?>

<p>
	A page to create new reports.
</p>

<form id="edit-report" method="post" class="form-horizontal">

	<div id="first-url" class="form-group url-group">
		<label for="inputUrl" class="col-sm-2 control-label">URL(s):</label>
		<div class="col-sm-10">
			<div class="input-group">
				<input
					type="text"
					id="inputUrl"
					class="form-control"
					placeholder="The web address(es) for this tutorial, including the http/https protocol"
				/>
				<span class="input-group-btn">
					<button class="url-add btn btn-default" type="button">+</button>
					<button class="url-remove btn btn-default" type="button">-</button>
				</span>
			</div>
		</div>
	</div>
	
	<div class="form-group">
		<label for="inputTitle" class="col-sm-2 control-label">Title:</label>
		<div class="col-sm-10">
			<input
				type="text"
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
			></textarea>
		</div>
	</div>

	<div class="form-group issue-group">
		<label for="issueType" class="col-sm-2 control-label">Issue(s):</label>
		<div class="col-sm-10">
			<div class="input-group">
				<select
					id="issueType"
					class="form-control"
				>
					<option>SQL injection</option>
					<option>XSS</option>
				</select>
				<span class="input-group-btn">
					<button class="issue-add btn btn-default" type="button">+</button>
					<button class="issue-remove btn btn-default" type="button">-</button>
				</span>
			</div>
			<!-- @todo How to do this in Bootstrap? -->
			<div style="margin-top: 8px;">
				<textarea
					name="description"
					id="issueDescription"
					class="form-control"
					rows="3"
					placeholder="An optional English description of this issue can go here (Markdown is supported)"
				></textarea>
			</div>
		</div>
	</div>

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
<?php $this->insert('partials/new-report-templates') ?>


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