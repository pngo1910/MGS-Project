<?php
	ini_set('max_execution_time', -1);
	require('errorReporter.php');
	require('db/memberConnection.php');
require('config_mail.php');
	//Date stuff
	$today = date('j F, Y');
	$thisYear = date('Y');
	$thisMonth = date('m');
	$nextMonth = date('m', strtotime('+1 month', strtotime($thisYear.'-'.$thisMonth)));
	$nextMonthLastDay = date('t F, Y', strtotime($thisYear.'-'.$nextMonth));
	$memberNums = array();

	//email stuff
	$defaultEmail = "membership@mbgenealogy.com";
	$filename = "MGS_Brochure.pdf";
	$file = "./PDF/".$filename;
    $file_size = filesize($file);
    $handle = fopen($file, "r");
    $content = fread($handle, $file_size);
    fclose($handle);
    $content = chunk_split(base64_encode($content));

    // a random hash will be necessary to send mixed content
    $separator = md5(time());

    // carriage return type (we use a PHP end of line constant)
    $eol = PHP_EOL;

	// MEMBERSHIP EXPIRY
	// MEMBERSHIP EXPIRED TODAY

	//sql query to select the member numbers of members whose accounts have expired
	$sql = "SELECT FirstName, LastName, Email FROM Membership
			LEFT JOIN MemberInfo ON Membership.MemberNum = MemberInfo.MemberNum
			WHERE DATEPART(YEAR, Expiry) = ? AND DATEPART(MONTH, Expiry) = ?";
	// execute the query
	$stmt = sqlsrv_query($userConn, $sql, array($thisYear, $thisMonth), array('Scrollable' => 'static'));
	// if query does nto et executed, print the errors and kill the script
	if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	
	// fetch the row from the executed query
	while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
		$name  = $row['FirstName'].' '.$row['LastName'];
		$email = isset($row['Email'])? $row['Email']: $defaultEmail;

		// subject
		$subject = 'Your Membership Has Expired';
		// message
		$message = "
		<html>
			<head>
				<meta charset='utf-8'>
			  <title>Membership Expiry</title>
			</head>
			<body>
				<header>
					<table>
						<tr>
							<td>
								<img src='http://mani.mbgenealogy.com/img/mgs-square.png' style='float:left; width:160px;' width='160'>
							</td>
							<td>
								<h1 style='text-align:center;'>Manitoba Genealogical Society Inc.</h1>
					   			<h4 style='text-align:center;'>Unit E – 1045 St. James Street, Winnipeg, MB Canada   R3H 1B1</h4>
					   			<h4 style='text-align:center;'>Phone: 204-783-9139   www.mbgenealogy.com</h4>
					   		</td>
					   	</tr>
					</table>
		   		</header>
		   		<div style='clear:both;'></div>
		   		<p>To: ".$name."</p>
				<p>".$today."</p><br/>
				<p>This notice is to inform you that your membership in the Manitoba Genealogical Society expired as of ".$today.".</p> 
				<p>If you wish to renew, you can renew online at <a href='http://mani.mbgenealogy.com'>mani.mbgenealogy.com</a> and use PayPal. You can find directions at <a href='http://www.mbgenealogy.com'>www.mbgenealogy.com</a> and click on the MANI link in the menu on the left.</p>
				<p>You can also mail it in. A renewal form is attached. Please fill it in and send it along with your membership fee to the address at the top of this page.</p>
				<p>MGS Membership fees are $50.00 for Individual and $20.00 for Associate members. Thank you for your attention to this matter. Please ignore this notice if you have recently sent in your renewal.</p>
				<p>Yours truly,</p>
				<img width='140' src='http://mani.mbgenealogy.com/img/Signature001bw.jpg'>
				<p>Kenda Wood - MGS Membership Committee</p>
			</body>
		</html>
		";

		// main header (multipart mandatory)
	    //$headers = "To: ".$name."<".$email.">" . $eol;
	    //$headers .= "From: Manitoba Genealogical Society <mani@mbgenealogy.com>" . $eol;
	    $headers = "MIME-Version: 1.0" . $eol;
	    $headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol . $eol;
	    $headers .= "Content-Transfer-Encoding: 7bit" . $eol;
	    $headers .= "This is a MIME encoded message." . $eol . $eol;

	    // message
	    $headers .= "--" . $separator . $eol;
	    $headers .= "Content-Type: text/html; charset=iso-8859-1" . $eol;
	    $headers .= "Content-Transfer-Encoding: 8bit" . $eol . $eol;
	    $headers .= $message . $eol . $eol;

	    // attachment
	    $headers .= "--" . $separator . $eol;
	    $headers .= "Content-Type: application/octet-stream; name=\"" . $filename . "\"" . $eol;
	    $headers .= "Content-Transfer-Encoding: base64" . $eol;
	    $headers .= "Content-Disposition: attachment" . $eol . $eol;
	    $headers .= $content . $eol . $eol;
	    $headers .= "--" . $separator . "--";

		// Mail it
		//mail($email, $subject, "", $headers);

		//$message = Swift_Message
		//	::newInstance($subject, $headers )
		//	->setFrom($defaultEmail)
		//	->setTo($email)
		//	->setContentType('text/html')
		//;
$message = (new Swift_Message())
  ->setSubject($subject)
  ->setFrom($defaultEmail)
  ->setTo($email)
  ->setBody($message, 'text/html')
  ->attach(Swift_Attachment::fromPath($file));

		$result = $mailer->send($message);
	}

	// MEMBERSHIP EXPIRES NEXT MONTH

	$memberNums = array();

	//sql query to select the member numbers of members whose accounts have expired
	$sql = "SELECT FirstName, LastName, Email FROM Membership
			LEFT JOIN MemberInfo ON Membership.MemberNum = MemberInfo.MemberNum
			WHERE DATEPART(YEAR, Expiry) = ? AND DATEPART(MONTH, Expiry) = ?";
	// execute the query
	$stmt = sqlsrv_query($userConn, $sql, array($thisYear, $nextMonth), array('Scrollable' => 'static'));
	// if query does nto et executed, print the errors and kill the script
	if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	
	// fetch the row from the executed query
	while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
		$name  = $row['FirstName'].' '.$row['LastName'];
		$email = $email = isset($row['Email'])? $row['Email']: $defaultEmail;

		// subject
		$subject = 'Your Membership Will Expire Soon, at the end of this month';
		// message
		$message = "
		<html>
			<head>
				<meta charset='utf-8'>
			  <title>Membership Expiry</title>
			</head>
			<body>
				<header>
					<table>
						<tr>
							<td>
								<img src='http://mani.mbgenealogy.com/img/mgs-square.png' style='float:left; width:160px;' width='160'>
							</td>
							<td>
								<h1 style='text-align:center;'>Manitoba Genealogical Society Inc.</h1>
					   			<h4 style='text-align:center;'>Unit E – 1045 St. James Street, Winnipeg, MB Canada   R3H 1B1</h4>
					   			<h4 style='text-align:center;'>Phone: 204-783-9139   www.mbgenealogy.com</h4>
					   		</td>
					   	</tr>
					</table>
		   		</header>
		   		<div style='clear:both;'></div>
		   		<p>To: ".$name."</p>
				<p>".$today."</p><br/>
				<p>This notice is to inform you that your membership in the Manitoba Genealogical Society will, <strong>as of the end of this month</strong>, expire on ".$nextMonthLastDay.".</p> 
				<p>If you wish to renew, you can renew online at <a href='http://mani.mbgenealogy.com'>mani.mbgenealogy.com</a> and use PayPal. You can find directions at <a href='http://www.mbgenealogy.com'>www.mbgenealogy.com</a> and click on the MANI link in the menu on the left.</p>
				<p>You can also mail it in. A renewal form is attached. Please fill it in and send it along with your membership fee to the address at the top of this page.</p>
				<p>MGS Membership fees are $50.00 for Individual and $20.00 for Associate members. Thank you for your attention to this matter. Please ignore this notice if you have recently sent in your renewal.</p>
				<p>Yours truly,</p>
				<img width='140' src='http://mani.mbgenealogy.com/img/Signature001bw.jpg'>
				<p>Kenda Wood - MGS Membership Committee</p>
			</body>
		</html>
		";

		// main header (multipart mandatory)
	   // $headers = "To: ".$name."<".$email.">" . $eol;
	    //$headers .= "From: Manitoba Genealogical Society <mani@mbgenealogy.com>" . $eol;
	    $headers = "MIME-Version: 1.0" . $eol;
	    $headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol . $eol;
	    $headers .= "Content-Transfer-Encoding: 7bit" . $eol;
	    $headers .= "This is a MIME encoded message." . $eol . $eol;

	    // message
	    $headers .= "--" . $separator . $eol;
	    $headers .= "Content-Type: text/html; charset=iso-8859-1" . $eol;
	    $headers .= "Content-Transfer-Encoding: 8bit" . $eol . $eol;
	    $headers .= $message . $eol . $eol;

	    // attachment
	    $headers .= "--" . $separator . $eol;
	    $headers .= "Content-Type: application/octet-stream; name=\"" . $filename . "\"" . $eol;
	    $headers .= "Content-Transfer-Encoding: base64" . $eol;
	    $headers .= "Content-Disposition: attachment" . $eol . $eol;
	    $headers .= $content . $eol . $eol;
	    $headers .= "--" . $separator . "--";

		// Mail it
		//mail($email, $subject, "", $headers);

		//$message = Swift_Message
		//	::newInstance($subject, $headers )
		//	->setFrom('membership@mbgenealogy.com')
		//	->setTo($email)
		//	->setContentType('text/html')
		//;
$message = (new Swift_Message())
  ->setSubject($subject)
  ->setFrom('membership@mbgenealogy.com')
  ->setTo($email)
  ->setBody($message, 'text/html')
  ->attach(Swift_Attachment::fromPath($file));

		$result = $mailer->send($message);
	}

	// BRANCH EXPIRY
	// BRANCH EXPIRED TODAY

	//sql query to select the member numbers of members whose accounts have expired
	$sql = "SELECT FirstName, LastName, Email, Branch.Name, Price FROM Membership
			LEFT JOIN MemberInfo ON Membership.MemberNum = MemberInfo.MemberNum
			LEFT JOIN BranchMembership ON BranchMembership.MemberID = Membership.MemberNum
			LEFT JOIN Branch ON Branch.ID = BranchMembership.BranchID
			WHERE DATEPART(YEAR, BranchMembership.Expiry) = ?
			AND DATEPART(MONTH, BranchMembership.Expiry) = ?
			AND Membership.Expiry > GETDATE()";
	// execute the query
	$stmt = sqlsrv_query($userConn, $sql, array($thisYear, $thisMonth), array('Scrollable' => 'static'));
	// if query does nto et executed, print the errors and kill the script
	if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	
	// fetch the row from the executed query
	while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
		$name  = $row['FirstName'].' '.$row['LastName'];
		$email = isset($row['Email'])? $row['Email']: $defaultEmail;
		$branch = $row['Name'];
		$price = $row['Price'];

		// subject
		$subject = 'Your Branch Membership Will Expire Soon';
		// message
		$message = "
		<html>
			<head>
				<meta charset='utf-8'>
			  <title>Membership Expiry</title>
			</head>
			<body>
				<header>
					<table>
						<tr>
							<td>
								<img src='http://mani.mbgenealogy.com/img/mgs-square.png' style='float:left; width:160px;' width='160'>
							</td>
							<td>
								<h1 style='text-align:center;'>Manitoba Genealogical Society Inc.</h1>
					   			<h4 style='text-align:center;'>Unit E – 1045 St. James Street, Winnipeg, MB Canada   R3H 1B1</h4>
					   			<h4 style='text-align:center;'>Phone: 204-783-9139   www.mbgenealogy.com</h4>
					   		</td>
					   	</tr>
					</table>
		   		</header>
		   		<div style='clear:both;'></div>
		   		<p>To: ".$name."</p>
				<p>".$today."</p><br/>
				<p>This notice is to inform you that your <strong>branch membership</strong> to the ".$branch." in the Manitoba Genealogical Society will expire as of ".$today.".</p> 
				<p>If you wish to renew, you can renew online at <a href='http://mani.mbgenealogy.com'>mani.mbgenealogy.com</a> and use PayPal. You can find directions at <a href='http://www.mbgenealogy.com'>www.mbgenealogy.com</a> and click on the MANI link in the menu on the left.</p>
				<p>You can also mail it in. A renewal form is attached. Please fill it in and send it along with your membership fee to the address at the top of this page.</p>
				<p>".$brannch." Membership fees are ".$price.". Thank you for your attention to this matter. Please ignore this notice if you have recently sent in your renewal.</p>
				<p>Yours truly,</p>
				<img width='140' src='http://mani.mbgenealogy.com/img/Signature001bw.jpg'>
				<p>Kenda Wood - MGS Membership Committee</p>
			</body>
		</html>
		";

	    // main header (multipart mandatory)
	    //$headers = "To: ".$name."<".$email.">" . $eol;
	    //$headers .= "From: Manitoba Genealogical Society <mani@mbgenealogy.com>" . $eol;
	    $headers = "MIME-Version: 1.0" . $eol;
	    $headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol . $eol;
	    $headers .= "Content-Transfer-Encoding: 7bit" . $eol;
	    $headers .= "This is a MIME encoded message." . $eol . $eol;

	    // message
	    $headers .= "--" . $separator . $eol;
	    $headers .= "Content-Type: text/html; charset=iso-8859-1" . $eol;
	    $headers .= "Content-Transfer-Encoding: 8bit" . $eol . $eol;
	    $headers .= $message . $eol . $eol;

	    // attachment
	    $headers .= "--" . $separator . $eol;
	    $headers .= "Content-Type: application/octet-stream; name=\"" . $filename . "\"" . $eol;
	    $headers .= "Content-Transfer-Encoding: base64" . $eol;
	    $headers .= "Content-Disposition: attachment" . $eol . $eol;
	    $headers .= $content . $eol . $eol;
	    $headers .= "--" . $separator . "--";

		// Mail it
		//mail($email, $subject, "", $headers);

		//$message = Swift_Message
		//	::newInstance($subject, $headers )
		//	->setFrom('membership@mbgenealogy.com')
		//	->setTo($email)
		//	->setContentType('text/html')
		//;
$message = (new Swift_Message())
  ->setSubject($subject)
  ->setFrom('membership@mbgenealogy.com')
  ->setTo($email)
  ->setBody($message, 'text/html')
  ->attach(Swift_Attachment::fromPath($file));

		$result = $mailer->send($message);
	}

	// BRANCH EXPIRES NEXT MONTH

	//sql query to select the member numbers of members whose accounts have expired
	$sql = "SELECT FirstName, LastName, Email, Branch.Name, Price FROM Membership
			LEFT JOIN MemberInfo ON Membership.MemberNum = MemberInfo.MemberNum
			LEFT JOIN BranchMembership ON BranchMembership.MemberID = Membership.MemberNum
			LEFT JOIN Branch ON Branch.ID = BranchMembership.BranchID
			WHERE DATEPART(YEAR, BranchMembership.Expiry) = ?
			AND DATEPART(MONTH, BranchMembership.Expiry) = ?
			AND Membership.Expiry > GETDATE()";
	// execute the query
	$stmt = sqlsrv_query($userConn, $sql, array($thisYear, $nextMonth), array('Scrollable' => 'static'));
	// if query does nto et executed, print the errors and kill the script
	if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	
	// fetch the row from the executed query
	while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
		$name  = $row['FirstName'].' '.$row['LastName'];
		$email = isset($row['Email'])? $row['Email']: $defaultEmail;
		$branch = $row['Name'];
		$price = $row['Price'];

		// subject
		$subject = 'Your Branch Membership Will Expire at the End of 
		this Month';
		// message
		$message = "
		<html>
			<head>
				<meta charset='utf-8'>
			  <title>Membership Will Expire Soon</title>
			</head>
			<body>
				<header>
					<table>
						<tr>
							<td>
								<img src='http://mani.mbgenealogy.com/img/mgs-square.png' style='float:left; width:160px;' width='160'>
							</td>
							<td>
								<h1 style='text-align:center;'>Manitoba Genealogical Society Inc.</h1>
					   			<h4 style='text-align:center;'>Unit E – 1045 St. James Street, Winnipeg, MB Canada   R3H 1B1</h4>
					   			<h4 style='text-align:center;'>Phone: 204-783-9139   www.mbgenealogy.com</h4>
					   		</td>
					   	</tr>
					</table>
		   		</header>
		   		<div style='clear:both;'></div>
		   		<p>To: ".$name."</p>
				<p>".$today."</p><br/>
				<p>This notice is to inform you that your branch membership to the ".$branch." in the Manitoba Genealogical Society will, <strong>as of the end of this month</strong>, expire on ".$nextMonthLastDay.".
				<p>If you wish to renew, you can renew online at <a href='http://mani.mbgenealogy.com'>mani.mbgenealogy.com</a> and use PayPal. You can find directions at <a href='http://www.mbgenealogy.com'>www.mbgenealogy.com</a> and click on the MANI link in the menu on the left.</p>
				<p>You can also mail it in. A renewal form is attached. Please fill it in and send it along with your membership fee to the address at the top of this page.</p>
				<p>".$brannch." Membership fees are ".$price.". Thank you for your attention to this matter. Please ignore this notice if you have recently sent in your renewal.</p>
				<p>Yours truly,</p>
				<img width='140' src='http://mani.mbgenealogy.com/img/Signature001bw.jpg'>
				<p>Kenda Wood - MGS Membership Committee</p>
			</body>
		</html>
		";

	    // main header (multipart mandatory)
	    //$headers = "To: ".$name."<".$email.">" . $eol;
	   // $headers .= "From: Manitoba Genealogical Society <mani@mbgenealogy.com>" . $eol;
	    $headers = "MIME-Version: 1.0" . $eol;
	    $headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol . $eol;
	    $headers .= "Content-Transfer-Encoding: 7bit" . $eol;
	    $headers .= "This is a MIME encoded message." . $eol . $eol;

	    // message
	    $headers .= "--" . $separator . $eol;
	    $headers .= "Content-Type: text/html; charset=iso-8859-1" . $eol;
	    $headers .= "Content-Transfer-Encoding: 8bit" . $eol . $eol;
	    $headers .= $message . $eol . $eol;

	    // attachment
	    $headers .= "--" . $separator . $eol;
	    $headers .= "Content-Type: application/octet-stream; name=\"" . $filename . "\"" . $eol;
	    $headers .= "Content-Transfer-Encoding: base64" . $eol;
	    $headers .= "Content-Disposition: attachment" . $eol . $eol;
	    $headers .= $content . $eol . $eol;
	    $headers .= "--" . $separator . "--";

		// Mail it
		//mail($email, $subject, "", $headers);

		//$message = Swift_Message
		//	::newInstance($subject, $headers )
		//	->setFrom('membership@mbgenealogy.com')
		//	->setTo($email)
		//	->setContentType('text/html')
		//;
$message = (new Swift_Message())
  ->setSubject($subject)
  ->setFrom('membership@mbgenealogy.com')
  ->setTo($email)
  ->setBody($message, 'text/html')
  ->attach(Swift_Attachment::fromPath($file));

		$result = $mailer->send($message);
	}
?>