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
					<h2>Search Store Purchases</h2>
				</div>
				<p><b>Note:</b> None of it is required but it will help narrow down the search.</p>
				<p>You may either enter purchaser's personal information or the store infornation.</p>
				<p>If you hit "search" without entering anything, the whole table will be displayed.</p>				
					
				<!-- purchases.php hasn't been created yet -->
				<form method="POST" action="purchases.php">
					<h3>Product Information</h3>
					<label for="productname">Product Name:</label>
					<input type="text" class="searching" name="productname" id="productname" placeholder="Product Name" autofocus />
					<label for="description">Description:</label>
					<!-- not sure if title will be needed e.g:title="Example: Credits, Renewal, Purchase"  -->
					<input type="text" class="searching" name="description" id="description" placeholder="Description" />
					<label for="price">Price:</label>
					<input type="text" class="searching" name="price" id="price" placeholder="Price" />
					<label for="category">Category:</label>
					<!-- not sure if title will be needed e.g:title="Example: Credits, Renewal, Purchase"  -->
					<input type="text" class="searching" name="category" id="category" placeholder="Category" />
<!-- 					<label for="transaction">Transaction ID:</label>
					<input type="text" class="searching" name="transaction" id="transaction" placeholder="Transaction ID" /> -->
					<label for="quantity">Quantity:</label>
					<input type="number" class="searching" name="quantity" id="quantity" placeholder="Quantity" />
					<label for="date">Date Purchased:</label>
					<input type="text" class="searching" name="date" id="date" placeholder="YYYY-MM-DD" />
					<h3>User Information</h3>
					<label for="firstname">First Name:</label>
					<input type="text" class="searching" name="firstname" id="firstname" placeholder="First Name" />
					<label for="lastname">Last Name:</label>
					<!-- not sure if title will be needed e.g:title="Example: Credits, Renewal, Purchase"  -->
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