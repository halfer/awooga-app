<div class="input-group">
	<input
		name="search"
		type="text"
		class="form-control"
		id="addressSearch"
		value="<?php echo $this->escape($searchString) ?>"
		placeholder="Enter tutorial address or keywords"
	/>
	<span class="input-group-btn">
		<input
			type="submit"
			class="btn btn-default pull-right"
			value="Search"
		/>
	</span>
</div>