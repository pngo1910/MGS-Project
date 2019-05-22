<?php
	//header('location:  userInfo.php');

	require('../../db/membershipAdminCheck.php');
?>

<!DOCTYPE HTML>
<html class="no-js">
	<head>
		<meta charset="utf-8">
		<?php header('X-UA-Compatible: IE=edge,chrome=1');?>
		<title>MGS Administrator</title>
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width">

		<!-- Place favicon.ico and apple-touch-icon.png in the root directory -->

    <link rel="stylesheet" href="../../css/normalize.css">
    <link rel="stylesheet" href="../../css/main.css">
		<script src="/js/vendor/modernizr-2.6.2.min.js"></script>
	</head>
	<body>
    <div id="resultsbackground">
      <div id="container" class="home">
        <?php require('header.php'); ?>
        <div>
        	<h3>Membership Administration Panel</h3>
        	<p>The navigation contains links to view members lists, mailing lists, recent changes a member has made, and the page to register new members.</p>
        </div>
      </div>
    </div>
  </body>
</html>