<?php
	require_once('../config_mail.php');

	$memberNum = $_SESSION['memberNum'];
	if($memberNum !== -1){
		$sql = "SELECT * FROM MemberInfo WHERE MemberNum = ?";
        $stmt = sqlsrv_query($userConn, $sql, array($memberNum));
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
        $row = sqlsrv_fetch_array($stmt);

        $customerFirstName = $row['FirstName'];
        $customerLastName = $row['LastName'];
        $customerAddress = $row['Address'];
        $customerCity = $row['City'];
        $customerProvince = $row['Province'];
        $customerCountryCode = $row['CountryCode'];
        $customerPhone = $row['Phone'];
        $customerEmail = $row['Email'];
	}
	else{
		//get the highest member number
		$sqlMax = "SELECT MAX(MemberNum) FROM UnloggedInUserInfo";
		$stmtMax = sqlsrv_query($userConn, $sqlMax, array());

		// fetch the row from the executed query
		if (sqlsrv_fetch($stmtMax) === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

		//incremet the highest member number by 1 to get a new member number
		$memberNum = (int)sqlsrv_get_field($stmtMax, 0);
		$memberNum ++;

		$UnloginUser = explode("<>", ($memberNum . "<>" . $_SESSION['UnloggedInUserInfo']));

		$customerFirstName = $UnloginUser[1];
        $customerLastName = $UnloginUser[2];
        $customerAddress = $UnloginUser[3];
        $customerCity = $UnloginUser[4];
        $customerProvince = $UnloginUser[5];
        $customerCountryCode = $UnloginUser[6];
        $customerPhone = $UnloginUser[7];
        $customerEmail = $UnloginUser[8];
        
		$cols = retrieveColumns('UnloggedInUserInfo', 0, $userConn);
        $cols = implode(",", $cols);

		$sql = "INSERT INTO UnloggedInUserInfo ($cols) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = sqlsrv_query($userConn, $sql, $UnloginUser);
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	}

	//send email 
    $email = 'research@mbgenealogy.com';
    //$email = 'george880131@gmail.com';

    global $mailer;

    //add user information
	$customerInfo = "<table border='1'>";

    //set customerInfo
    $customerInfo .= "<tr><td><strong>FirstName</strong></td><td>". $customerFirstName . "</td></tr>";
    $customerInfo .= "<tr><td><strong>LastName</strong></td><td>". $customerLastName . "</td></tr>";
    $customerInfo .= "<tr><td><strong>Address</strong></td><td>". $customerAddress . "</td></tr>";
    $customerInfo .= "<tr><td><strong>City</strong></td><td>". $customerCity . "</td></tr>";
    $customerInfo .= "<tr><td><strong>Province</strong></td><td>". $customerProvince . "</td></tr>";
    $customerInfo .= "<tr><td><strong>CountryCode</strong></td><td>". $customerCountryCode . "</td></tr>";
    $customerInfo .= "<tr><td><strong>Phone</strong></td><td>". $customerPhone . "</td></tr>";
    $customerInfo .= "<tr><td><strong>Email</strong></td><td>". $customerEmail . "</td></tr>";
    $customerInfo .= "</table>";

    $purchaseDetails = "<table border='1'>";
    //set purchaseDetails
    $purchaseDetails .= "<tr><td><strong>surname</strong></td><td>". $_SESSION['surname'] . "</td></tr>";
    $purchaseDetails .= "<tr><td><strong>givenname</strong></td><td>". $_SESSION['givenName'] . "</td></tr>";
    $purchaseDetails .= "<tr><td><strong>Description</strong></td><td>". $_SESSION['description'] . "</td></tr>";
    $searchLocations = empty($searchLocations) ? "Basic package" : $searchLocations;
    $purchaseDetails .= "<tr><td><strong>searchLocations</strong></td><td>". $searchLocations . "</td></tr>";
    $purchaseDetails .= "</table>";

    // subject
	$subject = 'A research has been made';
	// message body
	$body = "
	<html>
		<head>
			<meta charset='utf-8'>
		  <title>Purchase</title>
		</head>
		<body>
			<p>".$customerFirstName.' '.$customerLastName." has just made a research from the store.</p>
			<p>Please log in and go to 'Store Management' and click on 'Search Transactions'. Enter the following transaction id to get the details of this transaction, such as which products need to be shipped and where to ship them to.</p>
			".$purchaseDetails."
			<p>Transaction ID: need to fix previous code bug</p>
			<p>Customer Info:</p>
			".$customerInfo."
		</body>
	</html>
	";

	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

	// Additional headers
	$headers .= 'To: '.$customerFirstName.' '.$customerLastName.'<'.$customerEmail.'>' . "\r\n";
	
	$headers .= 'From: Manitoba Genealogical Society <mani@mbgenealogy.com>' . "\r\n";

	// Mail it
	$message = Swift_Message
		::newInstance($subject, $body)
		->setFrom('noreply@mbgenealogy.com')
		->setTo($email)
		->setContentType('text/html')
	;

	$result = $mailer->send($message);
?>