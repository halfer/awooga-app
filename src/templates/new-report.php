<?php $this->layout(
	'layout',
	array('selectedMenu' => $selectedMenu, 'countData' => $countData, 'username' => $username, )
) ?>

<p>
	A page to create new reports.
</p>

<form method="post" class="form-horizontal">

	<div class="form-group">
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
					<button id="url-add" class="btn btn-default" type="button">+</button>
					<button id="url-remove" class="btn btn-default" type="button">-</button>
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

	<div class="form-group">
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
					<button id="issue-add" class="btn btn-default" type="button">+</button>
					<button id="issue-remove" class="btn btn-default" type="button">-</button>
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

<script type="text/javascript">
	$(document).ready(function() {
		$('#url-add').on('click', function() {
			alert('Add');
		});
		$('#url-remove').on('click', function() {
			alert('Remove');
		});
		$('#issue-add').on('click', function() {
			alert('Add');
		});
		$('#issue-remove').on('click', function() {
			alert('Remove');
		});
	});
</script>