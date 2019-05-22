<?php
	require('../../db/membershipAdminCheck.php');
 	require('../../errorReporter.php');
	require('../../db/memberConnection.php');

	$membernum = urldecode($_GET['membernum']);
	$verify = $_GET['verify'];

	if (isset($verify)) {
		if ($verify === 'false') {
			$sql = "UPDATE members SET verified = 0 WHERE membernum = ?";
			$stmt = sqlsrv_query($userConn, $sql, array($membernum));
	    	if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

	    	header('location: /volunteer/Memberships/userInfo.php');

		} elseif ($verify === 'true') {
			$sql = "UPDATE members SET verified = 1 WHERE membernum = ?";
			$stmt = sqlsrv_query($userConn, $sql, array($membernum));
    		if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    		header("location: /volunteer/Memberships/sendPackage.php?memberid=$membernum");
		}
	}
?>
