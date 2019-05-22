<?php function generatePackage($memberNum, $userConn) {

	$branches = array();
	$defaultEmail = 'contact@mbgenealogy.com';

	$sql = "SELECT FirstName, LastName, Email, Expiry FROM MemberInfo
				LEFT JOIN Membership ON MemberInfo.MemberNum = Membership.MemberNum
				WHERE MemberInfo.MemberNum = ?";
	// execute the query
	$stmt = sqlsrv_query($userConn, $sql, array($memberNum), array('Scrollable' => 'static'));
	// if query does nto et executed, print the errors and kill the script
	if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

	$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_ABSOLUTE);
	$name = $row['FirstName'] . ' ' . $row['LastName'];
	$email = $row['Email'] != '' ? $row['Email'] : $defaultEmail;
	$expiry = $row['Expiry']->format('j F, Y');

	$sql = "SELECT BranchID, Expiry FROM BranchMembership
				WHERE MemberID = ?";
	// execute the query
	$stmt = sqlsrv_query($userConn, $sql, array($memberNum));
	// if query does nto et executed, print the errors and kill the script
	if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

	while ($row = sqlsrv_fetch_array($stmt)) {
		if (date_format($row['Expiry'], 'U') > time()) {
			Switch ($row['BranchID']) {
				case 1:
					$branches[] = 'BP';
					break;
				case 2:
					$branches[] = 'D';
					break;
				case 3:
					$branches[] = 'SW';
					break;
				case 4:
					$branches[] = 'SV';
					break;
				case 5:
					$branches[] = 'SE&W';
					break;
			}
		}
	}

	$branchesStr = implode(', ', $branches);

	// subject
	$subject = 'Welcome to The Manitoba Genealogical Society';
	// message
	$message = "
		<html>
			<head>
				<meta charset='utf-8'>
				<title>Welcome</title>
			</head>
			<body style='width: 800px'>
				<header>
					<img style='float:left;width:160px;' src='http://mani.mbgenealogy.com/img/mgs-square.png'>
					<h1 style='text-align:center;'>Manitoba Genealogical Society Inc.</h1>
		   			<h4 style='text-align:center;'>Unit E – 1045 St. James Street, Winnipeg, MB Canada   R3H 1B1</h4>
		   			<h4 style='text-align:center;'>Phone: 204-783-9139   www.mbgenealogy.com</h4>
		   		</header>
		   		<div style='clear:both;'></div><br/>
		   		<p>To:" . $name . "</p>
				<p>" . date('j F, Y') . "</p><br/>
				<p>Thank you for joining the Manitoba Genealogical Society</p>
				<p>Your receipt and membership card are below. Please print this email and cut out the card for your wallet.</p>
				<div style='border:solid black; height:2.125in; width:3.37in; border-radius:10px; padding-top:5px'>
					<div style='float:left; padding:6px;width:85px;'><img width='85' src='http://mani.mbgenealogy.com/img/mgs-square.png'></div>
<div style='margin-left:100px;'>
					<p style='margin:0; padding:0; font-size:11pt; font-family: Times;'>This is to certify</p>
					<p style='margin:0; padding:0; font-size:11pt; font-family: Times; text-align:center;'>" . $name . "</p><hr>
					<p style='margin:0; padding:0; font-size:11pt; font-family: Times;'>is a member in good standing of the</p>
					<h4 style='margin:0; padding:0; font-size:11pt; font-family: Times;'>Manitoba Genealogical Society</h4>
					<p style='margin:0; padding:0; font-size:11pt; font-family: Times;'>Renewal Date: " . $expiry . "</p>
					<p style='margin:0; padding:0; font-size:11pt; font-family: Times;'>Member Number: " . $memberNum . "</p>
					<p style='margin:0; padding:0; font-size:11pt; font-family: Times;'>Branch Membership: " . $branchesStr . "</p>
					<br><p style='margin:0; padding:0; font-size:11pt; font-family: Times;'>Signed:<img height='20' width='100' src='http://mani.mbgenealogy.com/img/Signature001bw.jpg'></p>
				</div><br/>
				<p>Yours truly,</p>
				<img width='280' src='http://mani.mbgenealogy.com/img/Signature001bw.jpg'>
				<p>Kenda Wood - MGS Membership Committee</p>
			</body>
		</html>
		";

	return array('email' => $email, 'subject' => $subject, 'message' => $message);

}