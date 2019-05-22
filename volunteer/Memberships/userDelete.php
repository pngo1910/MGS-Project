<?php
  require('../../db/membershipAdminCheck.php');
  require('../../errorReporter.php');
  require('../../db/memberConnection.php');

  $username = urldecode($_GET['username']);
  $membernum = $_GET['membernum'];

  if (isset($membernum) && isset($username)) {
    if (strtolower($_SESSION['uname']) != strtolower($username)) {
      $sql = "DELETE FROM members WHERE membernum = ?";
      $stmt = sqlsrv_query($userConn, $sql, array($membernum));
      if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    }
    else {
      $_SESSION['error'] = "Cannot delete yourself as an admin.";
    }
  }

  header('location: /volunteer/Memberships/userInfo.php');
?>
