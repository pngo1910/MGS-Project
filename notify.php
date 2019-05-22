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
		$subject = 'Your Membership Has Expired TODAY';
		// message
		$message = "
		<html>
			<head>
				<meta charset='utf-8'>
			  <title>Membership Expiry TODAY </title>
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
		   		<p>To: ".$name."</p>
				<p>".$today."</p><br/>
				<p>This notice is to inform you that your <b> membership in the Manitoba Genealogical Society  expired as of ".$today."</B></p> 
				<p>If you wish to renew, you can renew online at <a href='http://mani.mbgenealogy.com'>mani.mbgenealogy.com</a> and use PayPal. You can find directions at <a href='http://www.mbgenealogy.com'>www.mbgenealogy.com</a> and click on the MANI link in the menu on the left.</p>
				<p>You can also mail it in. A renewal form is attached. Please fill it in and send it along with your membership fee to the address at the top of this page.</p>
				<p>MGS Membership fees are $50.00 for Individual and $20.00 for Associate members. You can also renew Branch Memberships for you and any Associate member you have associated with your MGS Membership. Thank you for your attention to this matter. </p>
				<p> Please ignore this notice if you have recently sent in your renewal.</p>
				<p>Yours truly,</p>
				<img width='140' src='http://mani.mbgenealogy.com/img/Signature001bw.jpg'>
				<p>Kenda Wood - MGS Membership Committee</p>
								<table border=1 width=100% style='border-collapse: collapse;'>
					<tr>
						<td>
							If Your MGS Membership and Membership in Branches expire at the same time you will receive multiple emails. If they expire at different months, you will receive both a notice that it will expire and then an expirery notice for each. 
							<br><br>
							If your MGS Membership expires you will still have access to the “My Account” part of MANI where you can renew your membership, but you will not be able to search the MANI database. Avoid being blocked from using MANI by renewing early, a full year is added to your account no matter when you renew!

						</td>
					</tr>
				</table>
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
			  <title>Membership Expiry SOON </title>
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
		   		<p>To: ".$name."</p>
				<p>".$today."</p><br/>
				<p>This notice is to inform you that your membership in the Manitoba Genealogical Society will, <b> as of the end of this month. It will expire on ".$nextMonthLastDay." </B></p> 
				<p>If you wish to renew, you can renew online at <a href='http://mani.mbgenealogy.com'>mani.mbgenealogy.com</a> and use PayPal. You can find directions at <a href='http://www.mbgenealogy.com'>www.mbgenealogy.com</a> and click on the MANI link in the menu on the left.</p>
				<p>You can also mail it in. A renewal form is attached. Please fill it in and send it along with your membership fee to the address at the top of this page.</p>
				<p>MGS Membership fees are $50.00 for Individual and $20.00 for Associate members. Thank you for your attention to this matter. Please ignore this notice if you have recently sent in your renewal.</p>
				<p>Yours truly,</p>
				<img width='140' src='http://mani.mbgenealogy.com/img/Signature001bw.jpg'>
				<p>Kenda Wood - MGS Membership Committee</p>
								<table border=1 width=100% style='border-collapse: collapse;'>
					<tr>
						<td>
							If Your MGS Membership and Membership in Branches expire at the same time you will receive multiple emails. If they expire at different months, you will receive both a notice that it will expire and then an expirery notice for each. 
							<br><br>
							If your MGS Membership expires you will still have access to the “My Account” part of MANI where you can renew your membership, but you will not be able to search the MANI database. Avoid being blocked from using MANI by renewing early, a full year is added to your account no matter when you renew!

						</td>
					</tr>
				</table>
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
		$subject = 'Your Branch Membership Will Expire SOON';
		// message
		$message = "
		<html>
			<head>
				<meta charset='utf-8'>
			  <title>Membership Expiry SOON </title>
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
		   		<p>To: ".$name."</p>
				<p>".$today."</p><br/>
				<p>This notice is to inform you that your <B> branch membership to the ".$branch." in the Manitoba Genealogical Society will expire as of ".$today."</B></p> 
				<p>If you wish to renew, you can renew online at <a href='http://mani.mbgenealogy.com'>mani.mbgenealogy.com</a> and use PayPal. You can find directions at <a href='http://www.mbgenealogy.com'>www.mbgenealogy.com</a> and click on the MANI link in the menu on the left.</p>
				<p>You can also mail it in. A renewal form is attached. Please fill it in and send it along with your membership fee to the address at the top of this page.</p>
				<p>".$brannch." Membership fees are ".$price.". Thank you for your attention to this matter. Please ignore this notice if you have recently sent in your renewal.</p>
				<p>Yours truly,</p>
				<img width='140' src='http://mani.mbgenealogy.com/img/Signature001bw.jpg'>
				<p>Kenda Wood - MGS Membership Committee</p>
					<table border=1 width=100% style='border-collapse: collapse;'>
					<tr>
					<td>
							If Your MGS Membership and Membership in Branches expire at the same time you will receive multiple emails. If they expire at different months, you will receive both a notice that it will expire and then an expirery notice for each. 
							<br><br>
							If your MGS Membership expires you will still have access to the “My Account” part of MANI where you can renew your membership, but you will not be able to search the MANI database. Avoid being blocked from using MANI by renewing early, a full year is added to your account no matter when you renew!

						</td>
					</tr>
				</table>
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
		$subject = 'Your Branch Membership Will Expire at the End of this Month';
		// message
		$message = "
		<html>
			<head>
				<meta charset='utf-8'>
			  <title>Membership Will Expire SOON</title>
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
		   		<p>To: ".$name."</p>
				<p>".$today."</p><br/>
				<p>This notice is to inform you that your <B> branch membership to the ".$branch." in the Manitoba Genealogical Society will, as of the end of this month, expire on ".$nextMonthLastDay."</B>
				<p>If you wish to renew, you can renew online at <a href='http://mani.mbgenealogy.com'>mani.mbgenealogy.com</a> and use a credit card. You can find directions at <a href='http://www.mbgenealogy.com'>www.mbgenealogy.com</a> and click on the MANI link in the menu on the left.</p>
				<p>You can also mail it in. A renewal form is attached. Please fill it in and send it along with your membership fee to the address at the top of this page.</p>
				<p>".$brannch." Membership fees are ".$price.". Thank you for your attention to this matter. Please ignore this notice if you have recently sent in your renewal.</p>
				<p>Yours truly,</p>
				<img width='140' src='http://mani.mbgenealogy.com/img/Signature001bw.jpg'>
				<p>Kenda Wood - MGS Membership Committee</p>
				<table border=1 width=100% style='border-collapse: collapse;'>
					<tr>
						<td>
							If Your MGS Membership and Membership in Branches expire at the same time you will receive multiple emails. If they expire at different months, you will receive both a notice that it will expire and then an expirery notice for each. 
							<br><br>
							If your MGS Membership expires you will still have access to the “My Account” part of MANI where you can renew your membership, but you will not be able to search the MANI database. Avoid being blocked from using MANI by renewing early, a full year is added to your account no matter when you renew!

						</td>
					</tr>
				</table>
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