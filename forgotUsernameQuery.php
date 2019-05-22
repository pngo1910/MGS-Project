<?php
 require('config_mail.php');

session_start();

// we want to connect to the members server
require('errorReporter.php');
require('db/memberConnection.php');

//set variables
$email = $_POST['Email']; //from forgotPassword.php form



//we want to see if the email exists
$sql = "SELECT FirstName, LastName, MemberNum FROM MemberInfo WHERE Email = ?";
$values = array($email);
$stmt = sqlsrv_query($userConn, $sql, $values, array('Scrollable' => 'static'));

if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_ABSOLUTE);

//  if row variable is null, i.e if no any row is returned
if ($row === null) {
	$_SESSION['error'] = "The email address you enetered does not match the email address of the username you entered.";
	header("location:forgotUsername.php");
	die;
}
$name = $row['FirstName'] . ' ' . $row['LastName'];


//we want to see if the username exists and get the member number if it does
$sql = "SELECT Username FROM Members WHERE MemberNum = ?";
$stmt = sqlsrv_query($userConn, $sql, array($row['MemberNum']), array('Scrollable' => 'static'));

if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_ABSOLUTE);

//  if row variable is null, i.e if no any row is returned
// if ($row === null) {
// 	$_SESSION['error'] = "The user name you entered does not exist.";
// 	header("location:forgotUsername.php");
// 	die;
// }

$username = $row['Username'];






//we mail the user their new password information

// subject
$subject = 'Forgot Username';
// message
$body = "
  <html>
    <head>
      <title>Forgot Username</title>
    </head>
    <body>
      <p>Hi " . $name . ",</p>
      <p>You have requested your Username:</p><br/>
      <p>" . $username . "</p><br/>
      <p></p>
      <p>If you did not request your username then someone may be trying to access your account.</p>
      <p>Please do not reply to this email, it is not monitored.</p>
    </body>
  </html>
  ";

// To send HTML mail, the Content-type header must be set
$headers = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

// Additional headers
$headers .= 'To: ' . $name . '<' . $email . '>' . "\r\n";
$headers .= 'From: Manitoba Genealogical Society(Username Request) <mani@mbgenealogy.com>' . "\r\n";

global $mailer;

$message = Swift_Message
	::newInstance($subject, $body)
	->setFrom('noreply@mbgenealogy.com')
	->setTo($email)
	->setContentType('text/html')
;

$result = $mailer->send($message);

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Username Recovery</title>
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width">
	<!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
	<link rel="stylesheet" href="/css/normalize.css">
	<link rel="stylesheet" href="/css/main.css">
	<script src="/js/vendor/modernizr-2.6.2.min.js"></script>
</head>
<body>
<div id="homeBackgroundFree"></div>
<div id="container" class="home">
	<?php require('header.php'); ?>
	<h1>Username Recovery</h1>
	<p>Your username has been emailed to you.</p>
</div>
</body>
</html>
