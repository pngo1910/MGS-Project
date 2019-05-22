<?php
  header('X-UA-Compatible: IE=edge,chrome=1');

  require('../db/loginCheck.php');
?>
<!DOCTYPE HTML>
<html lang="en-US">
	<head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>MGS Member</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">
        <link rel="stylesheet" href="/css/normalize.css">
        <link rel="stylesheet" href="/css/main.css">
        <script src="js/vendor/modernizr-2.6.2.min.js"></script>
    </head>
	<body>
		<div id="resultsbackground">
			<div id="container" class="home">
				<div id="searchresults">
          <?php require('../header.php'); ?>
				</div>
			</div>
			<div class="memberContent">
        <h2>My Account</h2>
        <p>This is where you can view/edit information about your account.<br/>
          You can also view a list of everything you've purchased as well as renew your membership and/or branch membership(s) and/or join new branches.</p>
			</div>
		</div>
	</body>
</html>
