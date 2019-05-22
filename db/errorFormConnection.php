<?php
  // This file will be used to connect to the error form database

  // connect to the server
  $conn = sqlsrv_connect('DESKTOP-OQEATD7\PHONGNGO', array(
    'Database' => 'ErrorForm',
    'UID'      => 'ErrorForm-Login',
    'PWD'      => 'Password01'
  ));

  // test if the connection fails
  if ($conn === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
?>
