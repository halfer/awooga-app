<?php
/**
 * Renders a URL editing control
 * 
 * @var string $description The description string being edited
 * @var string $typeCode Code chosen from the drop-down
 * @var string $id The id for the overall div, if required
 * @var array $issues List of issue codes to choose from
 * @var boolean $firstItem True for first item, false otherwise
 */
?>

<div
	<?php if ($id): ?>
		id="<?php echo $id ?>"
	<?php endif ?>
	class="form-group issue-group"
>
	<label
		for="input-issue"
		class="col-sm-2 control-label"
		<?php if (!$firstItem): ?>
			style="visibility: hidden;"
		<?php endif ?>
	>Issue(s):</label>
	<div class="col-sm-10">
		<div class="input-group">
			<select
				name="issue-type-code[]"
				<?php if ($firstItem): ?>
					id="input-issue"
				<?php endif ?>
				class="form-control"
			>
				<?php foreach ($issues as $issue): ?>
					<option
						<?php if ($issue == $typeCode): ?>selected="selected"<?php endif ?>
						><?php echo $this->escape($issue) ?></option>
				<?php endforeach ?>
			</select>
			<span class="input-group-btn">
				<button
					class="issue-add btn btn-default <?php echo $firstItem ? '' : 'hide' ?>"
					type="button"
				>+</button>
				<button class="issue-remove btn btn-default" type="button">-</button>
			</span>
		</div>
		<!-- @todo How to do this in Bootstrap? -->
		<div style="margin-top: 8px;">
			<textarea
				name="issue-description[]"
				class="form-control"
				rows="3"
				placeholder="An optional English description of this issue can go here (Markdown is supported)"
			><?php echo $this->escape($description) ?></textarea>
		</div>
	</div>
</div>
