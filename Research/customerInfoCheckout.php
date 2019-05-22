<?php
	$FirstName = $_POST['firstName'];
	$LastName = $_POST['lastName'];
	$Address = $_POST['address'];
	$City = $_POST['city'];
	$Province = $_POST['province'];
	$CountryCode = $_POST['countryCode'];
	$Phone = $_POST['phone'];
	$Email = $_POST['email'];
	$memberNum = $_POST['memberNum'];
	$_SESSION['memberNum'] = $memberNum;
	$_SESSION['UnloggedInUserInfo'] = "";
	$_SESSION['surname'] = $_POST['surname'];
	$_SESSION['givenName'] = $_POST['givenName'];
	$_SESSION['description'] = $_POST['description'];
	
	if ($memberNum === -1) {
		$_SESSION['UnloggedInUserInfo'] = implode("<>", array($FirstName, $LastName, $Address, $City, $Province, $CountryCode, $Email, $Phone));
	}
?>
			<h4>User Information: </h4>
			<p>First Name: <?= $FirstName ?></p>
			<p>Last Name: <?= $LastName ?></p>
			<p>Address: <?= $Address ?></p>
			<p>City: <?= $City ?></p>
			<p>Province: <?= $Province ?></p>
			<p>Postal Code: <?= $CountryCode ?></p>
			<p>Phone: <?= $Phone ?></p>
			<p>Email: <?= $Email ?></p>
			<h4>Search Information: </h4>