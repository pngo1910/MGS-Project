<?php
	require_once('../config_mail.php');

	function send_confirm_email($oldValues, $values, $changes, $oldEmail){
		//add user information
		$changesTable = "<table border='1'>";
		$changesTable .= "<tr><th colspan='3'><strong>change(s)</strong></th></tr>";
		$changesTable .= "<tr><th>FIELDS</th><th>OLD</th><th>NEW</th></tr>";
		$newEmail = "";
		for ($i=0; $i < count($changes); $i++) { 
			$changesTable .= "<tr><td><strong>" . $changes[$i] . "</strong></td><td>". $oldValues[$i]  . "</td><td>". $values[$i]  . "</td></tr>";
			if ($changes[$i] === "email") {
				$newEmail = $values[$i];
			}
		}

		global $mailer;
		//send email 
	    $adminEmail = 'membership@mbgenealogy.com';
	    //$adminEmail = 'qichen.z@hotmail.com';
	    // subject
		$subjectForAdmin = 'subjectForAdmin';
		$subjectForUser = 'Your account details have been updated';
		// message body
		$bodyForAdmin = "
		<html>
			<head>
				<meta charset='utf-8'>
			</head>
			<body>
				<h3>An user has changed account details</h3>
				<p>For more details please login to <a href='https://mani.mbgenealogy.com/volunteer/memberships/userUpdate.php'>Recent Updates</a> to view recent table changes</p>
				<p>All the changes:</p>
				".$changesTable."
			</body>
		</html>
		";

		$bodyForUser = "
		<html>
			<head>
				<meta charset='utf-8'>
			  <title>bodyForUser</title>
			</head>
			<body>
				<p>bodyForUser</p>
				<!--This field needs to add a function for user's first name -->
				<p>Dear ,</p>
				<br>
				<p>This is an automated message please do not reply.</p>
				<br>
				<h3>Account details updated</h3>
				<br/>
				<P>This message is to confirm that all the changes have been updated on your 
				MGS Member Profile in our membership system. If you have updated your eamil address,
                this confirmation email will be sent to the old and new address.
				<br>
				<p>If you did not request this update, please contact MGS at 
				<a href='mailto:membership@mbgenealogy.com'>membership@mbgenealogy.com</a>
				<br>
				<br>
				<p>All the best,</p>
				<p>Manitoba Genealogical Society</p>
				<p>All the changes:</p>
				".$changesTable."
			</body>
		</html>
		";

		// Mail it
		$message = Swift_Message
			::newInstance($subjectForAdmin, $bodyForAdmin)
			->setFrom('noreply@mbgenealogy.com')
			->setTo($adminEmail)
			->setContentType('text/html')
		;

		$result = $mailer->send($message);

		$message = Swift_Message
			::newInstance($subjectForUser, $bodyForUser)
			->setFrom('noreply@mbgenealogy.com')
			->setTo($oldEmail)
			->setContentType('text/html')
		;

		$result = $mailer->send($message);

		if (!empty($newEmail)) {
			$message = Swift_Message
				::newInstance($subjectForUser, $bodyForUser)
				->setFrom('noreply@mbgenealogy.com')
				->setTo($newEmail)
				->setContentType('text/html')
			;

			$result = $mailer->send($message);
		}
	}
?>