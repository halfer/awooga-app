<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Awooga</title>
		<link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
		<script src="/assets/bootstrap/js/bootstrap.min.js"></script>
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
						<li class="<?php echo $selectedMenu == 'logs' ? 'active' : '' ?>">
							<a href="/logs">Logs</a>
						</li>
					</ul>
				</div>
			</nav>

			<?= $this->section('content') ?>
		</div>
	</body>
</html>