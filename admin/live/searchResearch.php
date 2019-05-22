<?php
	require('../../db/adminCheck.php');
	// require('../../db/mgsConnection.php');
?>
<!DOCTYPE HTML>
<html lang="en-US">
	<head>
    	<meta charset="utf-8">
    	 <?php header('X-UA-Compatible: IE=edge,chrome=1');?>
    	<title>MGS Administrator</title>
    	<meta name="description" content="">
    	<meta name="viewport" content="width=device-width">
    	<link rel="stylesheet" href="/css/normalize.css">
    	<link rel="stylesheet" href="/css/main.css">
    	<script src="/js/vendor/modernizr-2.6.2.min.js"></script>
	</head>
	<body>
		<div id="resultsbackground">
	    	<div id="container" class="home">
	    		<?php require('header.php'); ?>
				<div id="head">
					<h2>Search Research</h2>
				</div>
				<p><b>Note:</b> None of it is required but it will help narrow down the search.</p>
				<p>You may either enter purchaser's personal information or the store infornation.</p>
				<p>If you hit "search" without entering anything, the whole table will be displayed.</p>				
					
				<!-- purchases.php hasn't been created yet -->
				<form method="POST" action="research.php">
					<h3>Research Details</h3>
					<label for="surname">Surname:</label>
					<input type="text" class="searching" name="surname" id="surname" placeholder="Surname" autofocus />
					<label for="givenname">GivenName:</label>
					<!-- not sure if title will be needed e.g:title="Example: Credits, Renewal, Purchase"  -->
					<input type="text" class="searching" name="givenname" id="givenname" placeholder="GivenName" />
					<label for="description">Description:</label>
					<input type="text" class="searching" name="description" id="description" placeholder="Description" />
					<label for="locations">Locations:</label>
					<input type="text" class="searching" name="locations" id="locations" placeholder="Locations" />
					<label for="package">Package:</label>
					<input type="text" class="searching" name="package" id="package" placeholder="package" />

					<h3>Researcher Information</h3>
					<label for="firstname">First Name:</label>
					<input type="text" class="searching" name="firstname" id="firstname" placeholder="First Name" />
					<label for="lastname">Last Name:</label>
					<input type="text" class="searching" name="lastname" id="lastname" placeholder="Last Name" />
					<label for="address">Address:</label>
					<input type="text" class="searching" name="address" id="address" placeholder="Shipping Address" />
					<label for="city">City:</label>
					<input type="text" class="searching" name="city" id="city" placeholder="City" />
					<label for="province">Province:</label>
					<input type="text" class="searching" name="province" id="province" placeholder="Province" />
					<label for="postalcode">Postal Code:</label>
					<input type="text" class="searching" name="postalcode" id="postalcode" placeholder="Postal Code" />
					<label for="email">Email:</label>
					<input type="text" class="searching" name="email" id="email" placeholder="xxx@gmail.com" />
		            <br/><br/>
		            <input class="submit" value="Search" type="submit">
				</form>
			</div>
		</div>
	</body>
</html>