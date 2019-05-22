<?php
  // This file is used to create connection object to the MGSTemp_Dev database

  // Setting server name and connection array to connect with
  $conn = sqlsrv_connect('DESKTOP-OQEATD7\PHONGNGO', array(
    // 'Database' => 'MGS',
    // 'UID'      => 'MGS-Login',
    // 'PWD'      => 'g70uU8GR'
    'Database' => 'MGSTemp_Dev',
    'UID'      => 'MGS_Temp_Dev-Login',
    'PWD'      => 'Password01'
  ));

  // Tests connectivity and exits if connection fails
  if ($conn === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
?>
