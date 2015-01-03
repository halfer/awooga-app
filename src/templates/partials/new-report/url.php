<?php
/**
 * Renders a URL editing control
 * 
 * @var string $url The URL to edit
 * @var string $id The id for the overall div, if required
 * @var boolean $firstItem True for first item, false otherwise
 */
?>

<div
	<?php if ($id): ?>
		id="<?php echo $id ?>"
	<?php endif ?>
	class="form-group url-group"
>
	<label
		for="input-url"
		class="col-sm-2 control-label"
		<?php if (!$firstItem): ?>
			style="visibility: hidden;"
		<?php endif ?>
	>URL(s):</label>
	<div class="col-sm-10">
		<div class="input-group">
			<input
				type="text"
				name="urls[]"
				value="<?php echo $this->escape($url) ?>"
				<?php if ($firstItem): ?>
					id="input-url"
				<?php endif ?>
				class="form-control"
				placeholder="The web address(es) for this tutorial, including the http/https protocol"
			/>
			<span class="input-group-btn">
				<button
					class="url-add btn btn-default <?php echo $firstItem ? '' : 'hide' ?>"
					type="button"
				>+</button>
				<button class="url-remove btn btn-default" type="button">-</button>
			</span>
		</div>
	</div>
</div>
