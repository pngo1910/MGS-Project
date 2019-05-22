<?php
	$params = "";
	$memberNum = -1;
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
		<div id="resultsbackground"></div>

<div id="container">
		<?php require('../header.php'); ?>
		<div class="content">
			<h1>MGS Basic Research Package form</h1> <p>
				<strong> Directions: </strong> Once there, complete the form and finalize the process by clicking on the "Checkout" button at the bottom of the form. You will be presented with a page containing the details of your order so that you can confirm what you have entered. When you are ready to pay click on the "Pay Now" button to be taken to the PayPal screen where you have a choice to use your PayPal account or select to pay by Credit card without creating a PayPal account.</p> 
			<form action="basicCheckout.php<?= $params ?>" method="post">
				<!--  -->
				<?php include('customerInfoForm.php'); ?>
				<!--  -->
				<h2>Search Information:</h2>
				<label for="surname">Surname (the person being searched):</label>
				<input type="text" id="surname" name="surname" required="true" /> *
				<label for="givenName">Given name(s):</label>
				<input type="text" id="givenName" name="givenName" required="true"/> *
				<label for="description">Provide as much pertinent information about this person that you know of (eg. birth date, marriage, death, locations, religion, etc.) Be specific about what information you are searching for and the resources you have already checked. We have resources from many countries but our main resources and collections are those about the province of Manitoba.</label>
            	<textarea rows="5" cols="100" id="description" name="description" placeholder="Enter description here." required="true"></textarea>
				<br />
				<input type="submit" name="formSubmit" value="Checkout"/>
			</form>
		 </div>

</div>
	</body>
</html>