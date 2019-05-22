<?php
 require('config_mail.php');

session_start();

// we want to connect to the members server
require('errorReporter.php');
require('db/memberConnection.php');

//  function to create a random password
function randomPassword() {
	$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
	$pass = array(); //remember to declare $pass as an array
	$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
	for ($i = 0; $i < 8; $i++) $pass[] = $alphabet[rand(0, $alphaLength)];
	return implode($pass); //turn the array into a string
}

//set variables
$email = $_POST['Email']; //from forgotPassword.php form
$userName = $_POST['userName'];

//we want to see if the username exists and get the member number if it does
$sql = "SELECT MemberNum FROM Members WHERE Username = ?";
$stmt = sqlsrv_query($userConn, $sql, array($userName), array('Scrollable' => 'static'));

if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_ABSOLUTE);

//  if row variable is null, i.e if no any row is returned
if ($row === null) {
	$_SESSION['error'] = "The user name you entered does not exist.";
	header("location:forgotPassword.php");
	die;
}

//we want to see if the email exists
$sql = "SELECT FirstName, LastName FROM MemberInfo WHERE MemberNum = ? AND Email = ?";
$values = array($row['MemberNum'], $email);
$stmt = sqlsrv_query($userConn, $sql, $values, array('Scrollable' => 'static'));

if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_ABSOLUTE);

//  if row variable is null, i.e if no any row is returned
if ($row === null) {
	$_SESSION['error'] = "The email address you enetered does not match the email address of the username you entered.";
	header("location:forgotPassword.php");
	die;
}

$name = $row['FirstName'] . ' ' . $row['LastName'];

//temporary password to email the user
$randomTempPass = randomPassword();
$encrypted = hash("sha512", $randomTempPass);

// update the password in the database
$sql = "UPDATE Members SET Password = ? WHERE UserName = ?";
$values = array($encrypted, $userName);
$stmt = sqlsrv_query($userConn, $sql, $values);

if ($stmt === false) {
	//update password fails
	$_SESSION['error'] = "Password reset unsuccessful, try again.";
	header("location:forgotPassword.php");
	die;
}

//we mail the user their new password information

// subject
$subject = 'Password Reset';
// message
$body = "
  <html>
    <head>
      <title>Password Reset</title>
    </head>
    <body>
      <p>Hi " . $name . ",</p>
      <p>Your Password has been reset. Here is your new password:</p><br/>
      <p>" . $randomTempPass . "</p><br/>
      <p>Once you've logged in with this password go to 'My Account' -> 'Account Info' to change your password to something you will remember.</p>
      <p>If you did not request a password reset then someone may be trying to access your account.</p>
      <p>Please do not reply to this email, it is not monitored.</p>
    </body>
  </html>
  ";

// To send HTML mail, the Content-type header must be set
$headers = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

// Additional headers
$headers .= 'To: ' . $name . '<' . $email . '>' . "\r\n";
$headers .= 'From: Manitoba Genealogical Society(Password Reset) <mani@mbgenealogy.com>' . "\r\n";

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
	<title>Password Recovery</title>
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
	<h1>Password Recovery</h1>
	<p>Password Reset successful.</p>
	<p>Your new password has been emailed to you.</p>
</div>
</body>
</html>
