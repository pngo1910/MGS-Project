<?php
  // This file is used to create connection object to the Accounts database

  // Setting server name and connection array to connect with
//   $serverName = "DESKTOP-OQEATD7\PHONGNGO"; //serverName\instanceName

// // Since UID and PWD are not specified in the $connectionInfo array,
// // The connection will be attempted using Windows Authentication.
// $connectionInfo = array( "Database"=>"Accounts","UID"=>"Accounts-login","PWD" => "Password01");
// $conn = sqlsrv_connect( $serverName, $connectionInfo);

//   // Tests connectivity and exits if connection fails
//   //if ($conn === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
//   if( $conn ) {
//     echo "Connection established.<br />";
// }else{
//     echo "Connection could not be established.<br />";
//     die( print_r( sqlsrv_errors(), true));
// }

	$userConn = sqlsrv_connect('DESKTOP-OQEATD7\PHONGNGO', array(
    'Database'=>'Accounts',
	'UID'=>'Accounts-Login',
    'PWD'=>'Password01'
    
  ));

  // Tests connectivity and exits if connection fails
  if ($userConn === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
?>
