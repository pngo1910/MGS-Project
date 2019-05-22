<?php 
	
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
	} else{
		session_start();
	}
	$_SESSION['basicValues'] = $_POST;
	//Grab the info they entered in the last page
	$Surname = $_POST['surname'];
	$GivenName = $_POST['givenName'];
	$Description = $_POST['description'];
	//print_r($_SESSION['basicValues']);


	//Mail basic user info prior to paypal submission, (temporary measure)
	require('../config_mail.php');
	$defaultEmail = "research@mbgenealogy.com";
	$separator = md5(time());

    $eol = PHP_EOL;


    $name  = $_POST['firstName'].' '.$_POST['lastName'];
		$email = $defaultEmail;

		// subject
		$subject = 'Basic user info prior to paypal submission, (temporary measure)';
		// message
		$FirstName = $_POST['firstName'];
		$LastName = $_POST['lastName'];
		$Address = $_POST['address'];
		$City = $_POST['city'];
		$Province = $_POST['province'];
		$CountryCode = $_POST['countryCode'];
		$Phone = $_POST['phone'];
		$Email = $_POST['email'];
		$memberNum = $_POST['memberNum'];
		
		
		
		$message = "
		<html>
			<head>
				<meta charset='utf-8'>
			  <title>Basic user info prior to paypal submission, (temporary measure)</title>
			</head>
			<body>
				<header>
					<table>
						<tr>
							<td>
								<img src='http://mani.mbgenealogy.com/img/mgs-square.png' style='float:left; width:80px;' width='80'>
							</td>
							<td>
								<h2 style='text-align:center;'>Manitoba Genealogical Society Inc.</h2>
					   			<h4 style='text-align:center;'>Unit E – 1045 St. James Street, Winnipeg, MB Canada   R3H 1B1</h4>
					   			<h4 style='text-align:center;'>Phone: 204-783-9139   www.mbgenealogy.com</h4>
					   		</td>
					   	</tr>
					</table>
		   		</header>
		   		<div style='clear:both;'></div>
				
				<table border=1 width=100% style='border-collapse: collapse;'>
					<tr>
						<td>
							<h4>User Information: </h4>
							<p>First Name: ".$FirstName ."</p>
							<p>Last Name: ". $LastName ."</p>
							<p>Address: ". $Address ."</p>
							<p>City: ". $City ."</p>
							<p>Province: ". $Province ."</p>
							<p>Postal Code: ". $CountryCode ."</p>
							<p>Phone: ". $Phone ."</p>
							<p>Email: ". $Email ."</p>
							<h4>Search Information: </h4>
							<p>Surname: ". $Surname ."</p>
							<p>Given Name(s): ". $GivenName ." </p>
							<p>Description about ". $GivenName . $Surname .": ". $Description ."</p>
						</td>
					</tr>
				</table>
			</body>
		</html>
		";
		

	    $message = (new Swift_Message())
		  ->setSubject($subject)
		  ->setFrom($defaultEmail)
		  ->setTo("research@mbgenealogy.com")
		  ->setBody($message, 'text/html');

		$result = $mailer->send($message);	
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
			<h2>Please make sure all information looks correct.</h2>
			<!--  -->
			<?php include('customerInfoCheckout.php'); ?>
			<!--  -->
			<p>Surname: <?= $Surname ?></p>
			<p>Given Name(s): <?= $GivenName ?> </p>
			<p>Description about <?= $GivenName ?> <?= $Surname ?>: <?= $Description ?></p>
			<?php if(isset($_GET['name']) && $_GET['name'] === "login"): ?>
			<!-- <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" target="_top">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="AWVUYMHFVFXY8">
				<input type="image" src="https://www.sandbox.paypal.com/en_US/i/btn/btn_paynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
				<img alt="" border="0" src="https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form> -->

			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="MUBD64PZGXUT2">
				<!--test
				<input type="hidden" name="hosted_button_id" value="5F6SN7ZDFPBYW"> 
				<input type="hidden" name="amount" value="0.01"> -->
				<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_paynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
				<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
			<?php else: ?>
			<!-- <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" target="_top">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="FYY6G68KEB76S">
				<input type="image" src="https://www.sandbox.paypal.com/en_US/i/btn/btn_paynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
				<img alt="" border="0" src="https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form> -->
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="VCLNFNQVJS5Y4">
				<!-- <input type="hidden" name="hosted_button_id" value="5F6SN7ZDFPBYW"> -->
				<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_paynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
				<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
			<?php endif; ?>
		 </div>
</div>
	</body>
</html>