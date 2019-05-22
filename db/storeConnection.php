<?php
  // This file is used to create connection object to the Store database

  // Setting server name and connection array to connect with
  $storeConn = sqlsrv_connect('DESKTOP-OQEATD7\PHONGNGO', array(
   					    'Database' => 'Store',
    					'UID'      => 'Store-Login',
    					'PWD'      => 'g70uU8GR'
  						));

  // Tests connectivity and exits if connection fails
  if ($storeConn === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
?>