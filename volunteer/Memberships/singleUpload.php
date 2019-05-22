<?php
	require('../../db/membershipAdminCheck.php');
	require('../../errorReporter.php');
	require('../../db/memberConnection.php');
	require('../../retrieveColumns.php');

    //gets the table name we will be using in the database
	if(isset($_GET['tableName']) && isset($_GET['pk'])){
		$tableName = $_GET['tableName'];
		$pk = $_GET['pk'];
		$_SESSION['tableName'] = $tableName;
	}
	else{ //redirect back to uploadDashboard if we don't have the DB tablename
		header("Location:tablesDashboard.php");
	}

	$sql = "SELECT MAX($pk) FROM $tableName";
    $stmt = sqlsrv_query($userConn, $sql, array());
    if (sqlsrv_fetch($stmt) === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    $id = sqlsrv_get_field($stmt, 0);
    $id++;

	//array of values that were posted

	$placeHolders = array('?');
	$values = array($id);

	//for each posted value we store them into the array
	foreach($_POST as $field => $val) {
	   //array_push($posted_values, $val);
		$placeHolders[] = '?';
		$values[] = ($val == '') ? 'null' : $val;
	}

	//build the SQL string
	$placeHolders = implode(', ', $placeHolders);

	$sql = "INSERT INTO $tableName VALUES ($placeHolders);";

    $stmt = sqlsrv_query($userConn, $sql, $values, array( "Scrollable" => 'static' ));
  //if errors lets display
	if( $stmt === false) {
		errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	}
	else{
		$_SESSION['message'] = 'You have entered a row successfully into ' . $tableName;
		header("Location:tablesDashboard.php");
	}
?>
