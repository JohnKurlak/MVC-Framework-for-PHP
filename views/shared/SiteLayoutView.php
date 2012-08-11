<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title><?= ($this->ViewBag->title != '') ? $this->ViewBag->title . ' | ' : '' ?><?= $this->ViewBag->ConfigModel->siteName ?></title>
		<link rel="stylesheet" href="<?= $this->Url('css/styles.css') ?>" type="text/css">
<?php
if (file_exists($this->ViewBag->fileHost . 'css/' . $this->ViewBag->controller . '.css')) {
?>
		<link rel="stylesheet" href="<?= $this->Url('css/' . $this->ViewBag->controller . '.css') ?>" type="text/css">
<?php
}
?>
		<link rel="icon" type="image/png" href="<?= $this->Url('images/favicon.png') ?>">
		<meta name="keywords" content="<?= $this->ViewBag->keywords ?>">
		<meta name="description" content="<?= $this->ViewBag->description ?>">
		<script src="//code.jquery.com/jquery-1.8.0.min.js"></script>
		<script src="<?= $this->Url('js/global.js') ?>"></script>
<?php
if (file_exists($this->ViewBag->fileHost . 'js/' . $this->ViewBag->controller . '.js')) {
?>
		<script src="<?= $this->Url('js/' . $this->ViewBag->controller . '.js') ?>"></script>
<?php
}
?>
		<!--[if lt IE 9]>
		<script type="text/javascript" src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
	</head>
	<body>
		<header>
			<div class="wrapper">
				
			</div>
		</header>
		<nav>
			<ul class="wrapper">
				<li><a href="<?= $this->Url() ?>">Home</a></li>
			</ul>
		</nav>
		<div class="wrapper">
			<section id="main">
<?php $this->RenderBody(); ?>
			</section>
		</div>
		<div class="spacer"></div>
		<footer>
			<div class="wrapper">
				<p>
					Copyright &copy; 2012-<?= date('Y'); ?>
					<a href="<?= $this->Url() ?>">Site</a>.
					All rights reserved.
				</p>
				<ul>
					<li><a href="<?= $this->Url('about') ?>">About</a></li>
					<li><a href="<?= $this->Url('contact') ?>">Contact</a></li>
				</ul>
			</div>
		</footer>
		<div id="fb-root"></div>
		<script type="text/javascript">
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', '']);
		_gaq.push(['_trackPageview']);
		$.getScript('http://www.google-analytics.com/ga.js');
		</script>
	</body>
</html>