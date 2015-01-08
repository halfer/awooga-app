<?php $this->layout(
	'layout',
	array('selectedMenu' => $selectedMenu, 'countData' => $countData, 'username' => $username, )
) ?>

<p>
	This is a FAQ (or a RAQ, possibly) continued from the front page.
</p>

<div class="row">
	<div class="col-md-4">
		<h3>
			What's the current focus?
		</h3>
		<p>
			Presently it's PHP tutorials, because that's what I know, and because there is a lot
			of material out there that needs improvement. But if we can get some solid subject
			experts for other languages, that would be great too.
		</p>
	</div>
	<div class="col-md-4">
		<h3>
			Can I reuse Awooga data?
		</h3>
		<p>
			All data in Awooga is owned by the community, and should be regarded as copyleft. I'll
			look into choosing a license for it, possibly Creative Commons. If you want to re-use
			the data in your own project, great. Get in touch, and I'll see if I can help.
		</p>
	</div>
	<div class="col-md-4">
		<h3>
			How can I contribute via a repo?
		</h3>
		<p>
			Copy or fork an <a href="/repos">existing repository</a> and let me take a look. If I
			agree with your initial commits, I'll set your repo to auto-publish.
		</p>
	</div>
</div>

<div class="row">
	<div class="col-md-4">
		<h3>
			Can I contribute to an existing repository?
		</h3>
		<p>
			If you would rather not maintain your own repo or edit reports online, you can instead
			fork an existing repository on GitHub, and send a pull request to the owner.
		</p>
	</div>
</div>