<?php
	header('X-UA-Compatible: IE=edge,chrome=1');

	// start the session
	session_start();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Password Recovery</title>
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width">
		<!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
		<link rel="stylesheet" href="/css/normalize.css">
		<link rel="stylesheet" href="/css/main.css">
		<script src="/js/vendor/modernizr-2.6.2.min.js"></script>
	</head>
	<body>
		<div id="homeBackgroundFree"></div>
	    <div id="container" class="home">
	        <?php require('header.php'); ?>
	      	<form name="forgotPasswordForm" method="post" action="forgotPasswordQuery.php">
		        <h1>Password Recovery</h1>
		        <!-- display error message if there is any, and then set the session error variable to null ("") -->
		        <p class="errorColor"><?php echo isset($_SESSION['error'])? $_SESSION['error']: ''; ?></p>
		        <?php $_SESSION['error'] = ''; ?>
		        <label for='userName'>Your Username</label>
		        <input type="text" placeholder="username" class="searching" name="userName" required='required' id = "userName" autofocus="autofocus"><br/>
		        <label for='Email'>Your Email Address</label>
		        <input type="text" placeholder="email address" class="searching" name="Email" required='required' id = "Email"><br/>
		        <input type="submit" class="submit" name = "Submit" value = "Request New Password" style="width:250px">
		    </form>
		</div>
	</body>
</html>
