<?php
// @todo It would probabl be better to split this into two partials, and then have the main
// template use each of them for the initial rendering.
?>

<?php // Template for a URL ?>
<div id="template-url" class="form-group url-group">
	<label class="col-sm-2 control-label" style="visibility: hidden;">URL(s):</label>
	<div class="col-sm-10">
		<div class="input-group">
			<input
				type="text"
				class="form-control"
				placeholder="The web address(es) for this tutorial, including the http/https protocol"
			/>
			<span class="input-group-btn">
				<button class="url-add btn btn-default hide" type="button">+</button>
				<button class="url-remove btn btn-default" type="button">-</button>
			</span>
		</div>
	</div>
</div>

<?php // Template for an issue ?>
<div id="template-issue" class="form-group issue-group">
	<?php // @todo The invisible label isn't very elegant, is there a better way to do this? ?>
	<label class="col-sm-2 control-label" style="visibility: hidden;">Issue(s):</label>
	<div class="col-sm-10">
		<div class="input-group">
			<select
				class="form-control"
			>
				<option>SQL injection</option>
				<option>XSS</option>
			</select>
			<span class="input-group-btn">
				<button class="issue-add btn btn-default hide" type="button">+</button>
				<button class="issue-remove btn btn-default" type="button">-</button>
			</span>
		</div>
		<!-- @todo How to do this in Bootstrap? -->
		<div style="margin-top: 8px;">
			<textarea
				name="description"
				class="form-control"
				rows="3"
				placeholder="An optional English description of this issue can go here (Markdown is supported)"
			></textarea>
		</div>
	</div>
</div>