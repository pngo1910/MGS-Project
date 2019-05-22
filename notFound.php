<?php require('/db/loginCheck.php') ?>
<!DOCTYPE html>
<html lang="en-us">
	<head>
    <meta charset="utf-8">
    <title>Page Not Found</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/main.css">
    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
	</head>
	<body>
		<div id="homeBackgroundFree"></div>
  	<div id="container" class="home">
  		<?php require('header.php'); ?>
  		<h1>404: Page Not Found</h1>
  		<p>It seems the page you were looking for doesn't exist,
  		either you typed something in wrong or the link you followed
  		is broken. If this page should exist, please file an <a href="/errorForm.php">error report</a></p>
  	</div>
	</body>
</html>
