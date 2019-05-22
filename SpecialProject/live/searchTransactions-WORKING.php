<?php
	require('../../db/adminCheck.php');
	require('../../db/mgsConnection.php');
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
					<h2>Search Transactions</h2>
				</div>
				<p><b>Note:</b> None of it is required but it will help narrow down the search.</p>
				<form method="POST" action="transactions.php">
					<label for="membernum">Member Number:</label>
					<input type="number" class="searching" name="membernum" id="membernum" placeholder="Member Number" autofocus />
					<label for="description">Description:</label>
					<input type="text" class="searching" name="description" id="description" title="Example: Credits, Renewal, Purchase" placeholder="Description" />
					<label for="transaction">Transaction ID:</label>
					<input type="text" class="searching" name="transaction" id="transaction" placeholder="Transaction ID" />
					<label for="date">Date Purchased:</label>
					<input type="text" class="searching" name="date" id="date" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" title="Example: 2015-12-01" placeholder="YYYY-MM-DD" />
		            <br/><br/>
		            <input class="submit" value="Search" type="submit">
				</form>
			</div>
		</div>
	</body>
</html>