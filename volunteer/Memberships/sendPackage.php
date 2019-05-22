<?php require('../../config_mail.php');

require('../../db/membershipAdminCheck.php');
require('../../errorReporter.php');
require('../../db/memberConnection.php');
require('generatePackage.php');

$error = '';
$memberNum = $_GET['memberid'];
$mail = generatePackage($memberNum, $userConn);

if(isset($_POST['send'])) {

	global $mailer;

	$message = Swift_Message
		::newInstance($mail['subject'], $mail['message'])
		->setFrom('noreply@mbgenealogy.com')
		->setTo($mail['email'])
		->setContentType('text/html')
	;

	$result = $mailer->send($message);

	$sent = true;
	if ($sent) {
		$error = 'Message has been sent';
		$colour = 'green';
	} else {
		$error = 'Message has NOT been sent';
		$colour = 'red';
	}

}
?>
<!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8">
</head>
<body>
<h2>Send New Member Package</h2>
<p style="color: <?= $colour ?>"><?= $error ?></p>
<p>Send the following to the new member?</p>
<form action="sendPackage.php?memberid=<?= $memberNum ?>" method="post">
	<input type="submit" name="send" value="Send">
	<button type="button" onclick="window.open('', '_self', ''); window.close();">Cancel</button>
</form>
<?php echo $mail['message']; ?>
</body>
</html>
