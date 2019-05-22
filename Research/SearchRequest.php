<?php
	$params = "";
	if(isset($_GET['name']) && $_GET['name'] === "login"){
		require('../db/loginCheck.php');
		require('../db/memberConnection.php');
		require('../errorReporter.php');
		$qry = "SELECT MemberNum, Verified FROM Members WHERE Username = ?";
		$stmt = sqlsrv_query($userConn, $qry, array($_SESSION['uname']));
		if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
		while($row = sqlsrv_fetch_array($stmt)){
			$memberNum = $row['MemberNum'];
			$verified = $row['Verified'];
		}

		$qry = "SELECT Expiry FROM Membership WHERE MemberNum = ?";
		$stmt = sqlsrv_query($userConn, $qry, array($memberNum));
		if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
		$expiry = sqlsrv_fetch_array($stmt)['Expiry']->format("Y-m-d");

		if($expiry < date("Y-m-d") && $verified != 1){
			$_SESSION['error'] = "You must renew your account before accessing this page.";
			header("location: /myAccount/");
		}

		$params = "?name=login";
	}
?>
<!DOCTYPE HTML>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
    	<title>Manitoba Genealogical Society</title>
	    <meta name="description" content="">
	    <meta name="viewport" content="width=device-width">
	    <link rel="stylesheet" href="/css/normalize.css">
	    <link rel="stylesheet" href="/css/main.css">
	</head>
	<body>
		<div id="resultsbackground">

        <div id="container" class="home">
		<?php require('../header.php'); ?>
		<div class="content">
			<h1>MGS Basic Research Package</h1>
			<p>MGS will do a one name search in our facility in the following Manitoba sources to query for, for a fee of $60:</p>
			<ul>
				<li>Cemetery Index</li>
				<li>Crown Land Registry Index</li>
				<li>Obituary Index (Winnipeg papers and some rural)</li>
				<li>Local History Books</li>
				<li>Census (pre 1881, 1881, 1891, 1906, 1911)</li>
				<li>Henderson Directories</li>
				<li>Phone Directories</li>
			</ul>
			<p>This search will give you a minimun of 5 hours of a volunteer researcher's time and will cover photocopying and postage up to a $5.00 maximum.</p>
			<form action="basicPackage.php<?= $params ?>" method="POST">
				<input type="submit" value="Start Basic Package">
			</form>
			<h1>Or a Custom Search</h1>
			<p>We will do one name search in any of the above sources or those listed below for a fee of $10 for <u>each</u> source:</p>
			<ul>
				<li>Anglican Marriage and Baptism Registers - Over 60 church indexes from the Diocese of Rupertsland (ranging from 1813 to 1925). Parents' names are listed as well as date and place of marriage or baptism.</li>
				<li>Catholic Marriage and Baptism Register - 30 Manitoba registers that list the parents' names, date and place of marriage. Years range from 1834 to 1982.</li>
				<li>United Church Archives - marriage, baptism and burial indexes for a large number of Manitoba and N.W Ontario charges</li>
			</ul>
			<form action="customPackage.php<?= $params ?>" method="POST">
				<input type="submit" value="Start Custom Package">
			</form>
			<h2>Additonal Information</h2>
			<p>
				In addition to sources in our library, we have access to many Internet websites(some free and
				some we subscribe to for a fee) that could be used where we feel they may be of help in collecting
				information for you. We will advise of you any possible further research and cost that would be useful to
				you when we respond to your query.
			</p>
			<p>Payment must be in advance. Please note that the time spent by our volunteers when no useful information is found is just as valuable as the time spent where we do find useful information. No refunds will be made.</p>
			<p>
				Note that MGS inhouse and mailing orders will only accept personal cheques, money orders or bank drafts.
				<a href="">Download the Search Request Form</a> and mail your payment to:
			</p>
			<p><strong>Manitoba Genealogical Society Inc.<br />
			Unit E - 1045 St. James St.<br />
			Winnipeg, MB<br />
			Canada R3H 1B1</strong></p>
		 </div>

</div>
	</body>
</html>