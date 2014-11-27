<?php $this->layout('layout', array('selectedMenu' => $selectedMenu, )) ?>

<div class="jumbotron">
	<h1>Awooga</h1>
	<p>
		Awooga is a website to warn beginner coders of problems with non-optimal teaching materials
		&mdash; just type in the address and see if it's in the database.
	</p>

	<form>
		<div class="input-group">
			<input
				type="text"
				class="form-control"
				id="addressSearch"
				placeholder="Enter tutorial address"
			/>
			<span class="input-group-btn">
				<button
					type="button"
					class="btn btn-default pull-right"
				>Search</button>
			</span>
		</div>
		<div class="checkbox">
			<label>
				<input
					type="checkbox"
					value="1"
					checked="checked"
				>
				Add this address to our internal list, so we can take a peek at it
			</label>
		</div>
	</form>
</div>

<div class="row">
	<div class="col-md-4">
		<p>
			Why do we do it?
		</p>
	</div>
	<div class="col-md-4">
		<p>
			Let's have some info here about how to contribute.
		</p>
	</div>
	<div class="col-md-4">
		<p>
			Also the last 10 reports in the database
		</p>
	</div>
</div>

<div class="row">
	<div class="col-md-4">
		<p>
			Instructions for tutorial authors
		</p>
	</div>
	<div class="col-md-4">
		<p>
			What issues are we most interested in?
		</p>
	</div>
	<div class="col-md-4">
		<p>
			What features are we working on?
		</p>
	</div>
</div>
