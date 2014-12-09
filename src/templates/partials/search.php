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

<div style="margin-top: 12px;">
	<small>
		Examples:
			<a href="/browse?search=AJAX">keyword</a>,
			<a href="/browse?search=%22Creating+a+Login+System+in+PHP%22">tutorial title</a> (use quotes),
			<a href="/browse?search=phppot.com">domain</a>,
			<a href="/browse?search=http%3A%2F%2Fwww.amitpatil.me%2Fyoutube-like-rating-script-jquery-php%2F">full address</a>,
			<a href="/browse?search=sql-injection">specific issue</a>.
	</small>
</div>