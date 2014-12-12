<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Awooga</title>
		<link rel="stylesheet" href="/assets/main.css">
		<link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
		<script src="/assets/bootstrap/js/bootstrap.min.js"></script>
		<?php if ($debugbarRenderer): ?>
			<?php echo $debugbarRenderer->renderHead() ?>
		<?php endif ?>
	</head>

	<body>
		<div class="container">
			<nav class="navbar navbar-default" role="navigation">
				<div id="navbar" class="navbar-collapse collapse">
					<ul class="nav navbar-nav">
						<li class="<?php echo $selectedMenu == 'home' ? 'active' : '' ?>">
							<a href="/">Awooga</a>
						</li>
						<li class="<?php echo $selectedMenu == 'browse' ? 'active' : '' ?>">
							<a href="/browse">Browse</a>
						</li>
						<li class="<?php echo $selectedMenu == 'issues' ? 'active' : '' ?>">
							<a href="/issues">Issues</a>
						</li>
						<li class="<?php echo $selectedMenu == 'repos' ? 'active' : '' ?>">
							<a href="/repos">Repositories</a>
						</li>
						<li class="<?php echo $selectedMenu == 'logs' ? 'active' : '' ?>">
							<a href="/logs">Logs</a>
						</li>
						<li class="<?php echo $selectedMenu == 'about' ? 'active' : '' ?>">
							<a href="/about">About</a>
						</li>
					</ul>
					<div class="nav navbar-nav navbar-right">
						<p class="navbar-text">
							Awooga has <?php echo $countData['issue_count'] ?> issues
							in <?php echo $countData['report_count'] ?> reports
						</p>
					</div>
				</div>
			</nav>

			<?= $this->section('content') ?>
		</div>
		<footer class="footer">
		  <div class="container">
			<p class="text-muted">
				<a href="https://github.com/halfer/awooga-app">GitHub</a>
				|
				<a href="https://twitter.com/ilovephp">Twitter</a>
			</p>
		  </div>
		</footer>
		<?php if ($debugbarRenderer): ?>
			<?php echo $debugbarRenderer->render() ?>
		<?php endif ?>
	</body>
</html>