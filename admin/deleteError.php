<?php
  require('../db/adminCheck.php');
  require('../errorReporter.php');
	require('../db/errorFormConnection.php');

	//get varables to delete the table
	$id = $_GET['id'];

	$query = "DELETE FROM ErrorForm WHERE ID = ?";
	$result = sqlsrv_query($conn, $query, array($id));

	if ($result === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

	header("Location: viewErrors.php");
?>
