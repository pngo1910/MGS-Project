
<?php
  session_start();
  require('../db/memberConnection.php');
  require('../errorReporter.php');

  $sql = 'SELECT usrname, mail FROM members WHERE username = ?';
  $stmt = sqlsrv_query($userConn, $sql, array('testuser'));

  if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
  // if ($stmt === false) errorReport(sqlsrv_errors(), $sql, print_r($values, true));

  // $sql = "SELECT 1 WHERE soundex(?) = soundex(?)";
  // $stmt = sqlsrv_query($userConn, $sql, array('smith', 'smythe'));
  // if ($stmt === false)
  //   errorReport(sqlsrv_errors(), __FILE__, __LINE__);
  // die(var_dump(sqlsrv_fetch_array($stmt)));
?>
