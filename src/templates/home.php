<?php $this->layout(
	'layout',
	array('selectedMenu' => $selectedMenu, 'countData' => $countData, )
) ?>

<div class="jumbotron">
	<h1>Awooga</h1>
	<p>
		Awooga is a website designed to warn coders of problems with non-optimal teaching
		materials &mdash; especially in relation to security issues. Just type in the
		address and see if it's in the database.
	</p>

	<form
		method="get"
		action="/browse"
	>
		<?php $this->insert('partials/search', ['searchString' => '', ]) ?>
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

<p class="pull-right">
	<a
		href="/about"
		class="btn btn-default"
		alt="More information about this site"
	>More interesting questions...</a>
</p>

<h2>Questions about this site</h2>

<div class="row">
	<div class="col-md-4">
		<h3>
			What does a listing mean?
		</h3>
		<p>
			A listing in this database means that, in the opinion of the editor who added it,
			the referenced tutorial has a number of security or quality issues, and thus that
			beginners need to be cautious about using that material.
		</p>
	</div>
	<div class="col-md-4">
		<h3>
			How can I contribute?
		</h3>

		<p>
			If you are an experienced programmer, I'd love to have your help. Please create a
			public Git repository <a href="#">using this template</a> and let me take a look. If I
			agree with your initial commits, I'll add your repo to auto-publish.
			You'll need to be willing to keep your list maintained - if a tutorial is improved,
			the database needs to reflect that.
		</p>
	</div>
	<div class="col-md-4">
		<h3>
			How should reports be written?
		</h3>
		<p>
			Reports should treat problematic teaching material as a bug to be fixed. We ask
			all editors to remain polite with their writing, and to employ a positive, can-do
			tone.
		</p>
	</div>
</div>

<div class="row">
	<div class="col-md-4">
		<h3>
			How should authors respond?
		</h3>
		<p>
			If you are listed, don't panic! If you agree with the assessment made of your resource,
			you can either fix the mistakes in it, take it down temporarily whilst you fix it,
			or remove it entirely. Remember that sometimes taking down really old material is
			sometimes the right thing to do. Feel free to liaise with the editor, too.
		</p>
	</div>
	<div class="col-md-4">
		<h3>
			What happens if a tutorial is improved?
		</h3>
		<p>
			If you've made a change as a result of a report here, thank you! Please ask the editor
			to review the changes, and to mark the issue(s) as resolved.
		</p>
	</div>
	<div class="col-md-4">
		<h3>
			Can we comment on resources via the Awooga website?
		</h3>
		<p>
			Not yet, but it's a good idea! If there's demand I'll add that to the to-list.
		</p>
	</div>
</div>
