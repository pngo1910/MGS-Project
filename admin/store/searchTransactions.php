<?php
	require('../../db/adminCheck.php');
	require('../../db/mgsConnection.php');
	require('../../db/storeConnection.php');
	require('../../retrieveColumns.php');

	$and = "AND COLUMN_NAME NOT IN('ID', 'TransactionsID')";
	$cols = retrieveColumns("PayerDetails", $and, $storeConn);
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
				<form method="POST" action="transactions.php" id="storetransactions">
					<label for="transaction">Transaction ID:</label>
					<input type="text" class="searching" name="transaction" id="transaction" placeholder="Transaction ID" autofocus />
					<label for="date">Date Purchased:</label>
					<input type="text" class="searching" name="date" id="date" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" title="Example: 2015-12-01" placeholder="YYYY-MM-DD" />
					<?php foreach($cols as $column): ?>
					<label for="<?= $column ?>"><?= $column ?>:</label>
					<?php if($column === "Phone"): ?>
					<input type="tel" class="searching" name="<?= $column ?>" id="<?= $column ?>" placeholder="<?= $column ?>" />
					<?php elseif($column === "Email"): ?>
					<input type="email" class="searching" name="<?= $column ?>" id="<?= $column ?>" placeholder="<?= $column ?>" />
					<?php else: ?>
					<input type="text" class="searching" name="<?= $column ?>" id="<?= $column ?>" placeholder="<?= $column ?>" />
					<?php endif; ?>
					<?php endforeach; ?>
		            <br/><br/>
		            <input class="submit" value="Search" type="submit">
				</form>
			</div>
		</div>
	</body>
</html>