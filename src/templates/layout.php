<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php if (isset($title)): ?>
			<title><?php echo $this->escape($title) ?> &mdash; Awooga</title>
		<?php else: ?>
			<title>Awooga</title>
		<?php endif ?>
		<link type="text/css" rel="stylesheet" href="/assets/main.css">
		<script type="text/javascript" src="/assets/jquery.min.js"></script>
		<link type="text/css" rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
		<script type="text/javascript" src="/assets/bootstrap/js/bootstrap.min.js"></script>
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
						<li class="nav-reports <?php echo $selectedMenu == 'browse' ? 'active' : '' ?>">
							<a href="/browse">Browse
								<span class="badge"><?php echo $countData['report_count'] ?></span></a>
						</li>
						<li class="nav-issues <?php echo $selectedMenu == 'issues' ? 'active' : '' ?>">
							<a href="/issues">Issues
								<span class="badge"><?php echo $countData['issue_count'] ?></span></a>
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
						<?php if ($username): ?>
							<li class="<?php echo $selectedMenu == '/report/new' ? 'active' : '' ?>">
								<a href="/report/new">New report</a>
							</li>
						<?php endif ?>
					</ul>
					<div class="nav navbar-nav navbar-right">
						<ul id="auth-status" class="nav navbar-nav">
							<?php if ($username): ?>
								<li id="auth-logout">
									<a href="/logout">
										Logout <span class="auth-username"><?php echo $this->escape($username, 'trim_username') ?></span>
									</a>
								</li>
							<?php else: ?>
								<li class="<?php echo $selectedMenu == 'auth' ? 'active' : '' ?>">
									<a href="/auth">Login</a>
								</li>
							<?php endif ?>
						</ul>
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