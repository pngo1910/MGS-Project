<?php
	require('../db/volunteerCheck.php');
?>

<!DOCTYPE HTML>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js"> <!--<![endif]-->
	<head>
		<meta charset="utf-8">
		<?php header('X-UA-Compatible: IE=edge,chrome=1');?>
		<title>MGS Volunteer</title>
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width">

		<!-- Place favicon.ico and apple-touch-icon.png in the root directory -->

    <link rel="stylesheet" href="../css/normalize.css">
    <link rel="stylesheet" href="../css/main.css">
		<script src="/js/vendor/modernizr-2.6.2.min.js"></script>
	</head>
	<body>
    <div id="resultsbackground">
      <div id="container" class="home">
        <?php require('header.php'); ?>
        <?php require('balanceWidget.php'); ?>
        <div>
          <h2 class="volunteerSpeal">Thank you volunteers for helping upload content to the MGS database.</h2>
          <h3>Valued Volunteers</h3>
          <p>The navigation contains links to upload and search through the database you are connected to.  Each link will contain a drop down list for the tables you will be working with.</p>
        </div>
      </div>
    </div>
  </body>
</html>
