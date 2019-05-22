<?php
	function updateChange($memberNum, $changes, $userConn){
		foreach ($changes as $value) {
		    /*$sql = "SELECT MAX(ID) FROM Changes";
		    $stmt = sqlsrv_query($userConn, $sql, array());
		    if (sqlsrv_fetch($stmt) === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

		    $id = sqlsrv_get_field($stmt, 0);
		    $id++;*/

		    $sql = "INSERT INTO Changes (MemberNum, Change) VALUES (?, ?)";
		    $stmt = sqlsrv_query($userConn, $sql, array($memberNum, $value));
		    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	    }
	    /* comment out, for more infomation please check confirmation_mail.php*/
	    /* this part not work well */
	 //    $sql = "SELECT FirstName, LastName FROM MemberInfo WHERE MemberNum = ?";
	 //    $stmt = sqlsrv_query($userConn, $sql, array($memberNum), array('Scrollable' => 'static'));
		// if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
		// $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_ABSOLUTE);
		// $memberName = $row['FirstName']." ".$row['LastName'];

		// $sql = "SELECT FirstName, LastName, Email FROM MemberInfo
		// 		LEFT JOIN Members ON Members.MemberNum = MemberInfo.MemberNum
		// 		WHERE Members.AccessLevel = 4";
	 //    $stmt = sqlsrv_query($userConn, $sql, array());
		// if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

		// while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
		// 	$name = $row['FirstName']." ".$row['LastName'];
		// 	$email = $row['Email'];
		// 	// subject
		// 	$subject = $memberName.' has made changes to his/her account';
		// 	// message
		// 	$message = "
		// 	<html>
		// 		<head>
		// 			<meta charset='utf-8'>
		// 		  <title>Account Changed</title>
		// 		</head>
		// 		<body>
		// 			<p>".$memberName." (member number: ".$memberNum.") has made changes to his/her account.</p>
		// 			<p>Please log in and go to 'Recent Changes' under 'Memberships' to view the changes.</p>
		// 		</body>
		// 	</html>
		// 	";

		// 	// To send HTML mail, the Content-type header must be set
		// 	$headers  = 'MIME-Version: 1.0' . "\r\n";
		// 	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		// 	// Additional headers
		// 	$headers .= 'To: '.$name.'<'.$email.'>' . "\r\n";
		// 	$headers .= 'From: Manitoba Genealogical Society <mani@mbgenealogy.com>' . "\r\n";

		// 	// Mail it
		// 	mail($email, $subject, $message, $headers);
		// }
		/* comment out, for more infomation please check confirmation_mail.php*/
	}
?>