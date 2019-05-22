<?php 
require('../config_mail.php');
require('../errorReporter.php');
require('../db/memberConnection.php');

header('X-UA-Compatible: IE=edge,chrome=1');
session_name('register');
session_start();

$isConfirmed = $_GET['confirm'];

if($isConfirmed) {

	$memberNum = $_SESSION['memberNum'];
	$sql = "SELECT FirstName, LastName FROM MemberInfo WHERE MemberNum = ?";
	$stmt = sqlsrv_query($userConn, $sql, array($memberNum), array('Scrollable' => 'static'));
	if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_ABSOLUTE);
	$memberName = $row['FirstName'] . " " . $row['LastName'];

	$sql = "SELECT FirstName, LastName, Email FROM MemberInfo
                LEFT JOIN Members ON Members.MemberNum = MemberInfo.MemberNum
                WHERE Members.AccessLevel = 4";
	$stmt = sqlsrv_query($userConn, $sql, array());
	if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

	while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

		$name = $row['FirstName'] . " " . $row['LastName'];
		$email = $row['Email'];
		// subject
		$subject = 'A New Account Has Been Registered';
		// message
		$body = "
            <html>
                <head>
                    <meta charset='utf-8'>
                  <title>Account Changed</title>
                </head>
                <body>
                    <p>" . $memberName . " (member number: " . $memberNum . ") has just registered for a new account.</p>
                    <p>Please log in and go to 'Members List' under 'Memberships' and filter by 'Mani' to verify the account.</p>
                </body>
            </html>
            ";

		// To send HTML mail, the Content-type header must be set
		$headers = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		// Additional headers
		$headers .= 'To: ' . $name . '<' . $email . '>' . "\r\n";
		$headers .= 'From: Manitoba Genealogical Society <mani@mbgenealogy.com>' . "\r\n";

		// Mail it
		global $mailer;
		
		$body = Swift_Message
			::newInstance($subject, $body)
			->setFrom('noreply@mbgenealogy.com')
			->setTo($email)
			->setContentType('text/html')
		;

		$result = $mailer->send($body);

	}
}

session_name('register');
session_destroy();

unset($_COOKIE['register']);
setcookie('register', null, -1, '/');

?>
<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>MGS Member</title>
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width">
	<link rel="stylesheet" href="/css/normalize.css">
	<link rel="stylesheet" href="/css/main.css">
	<script src="/js/vendor/modernizr-2.6.2.min.js"></script>
</head>
<body>
<div id="resultsbackground">
	<div id="container" class="home">
		<div id="searchresults">
			<?php require('header.php'); ?>
		</div>
		<?php if ($isConfirmed === 'true'): ?>
			<p>You are now Registered!</p>
			<p>You will receive an email with your membership card and new member package in the coming week on a Wednesday when the volunteers work. You will now have access to your MANI account and the MGS member's area and the MANI database search engine.</p>
		<?php else: ?>
			<p>You have chosen not to register.</p>
		<?php endif; ?>
	</div>
</div>
</body>
</html>