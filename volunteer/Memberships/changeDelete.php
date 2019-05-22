<?php
  require('../../db/membershipAdminCheck.php');
  require('../../errorReporter.php');
  require('../../db/memberConnection.php');

  $idStr = $_GET['id'];
  $id = explode(' ', $idStr);

  if (isset($id)) {
    foreach ($id as $value) {
      $sql = "DELETE FROM Changes WHERE ID = ?";
      $stmt = sqlsrv_query($userConn, $sql, array((int)$value));
      if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    }
  }
  header('location: /volunteer/Memberships/userUpdate.php');
?>
