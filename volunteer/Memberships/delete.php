<?php
  require('../../db/membershipAdminCheck.php');
  require('../../errorReporter.php');
	require('../../db/memberConnection.php');
  require('../../retrieveColumns.php');

  // store the get varibles in local variables
  $id = $_GET['id'];
  $tablename = $_GET['tablename'];
  $delete = $_GET['delete'];

  /*
  * Primary Key Columns
  */
  $primaryKeys = retrievePrimaryKeys($tablename, $userConn);

  /* Indexed column (used for fast and accurate table cardinality) */
  $sIndexColumn = $primaryKeys[0];

  if (isset($_GET['delete'])) {
    $sql = "DELETE FROM $tablename WHERE $sIndexColumn = ?";
    $stmt = sqlsrv_query($userConn, $sql, array($id), array( "Scrollable" => 'static' ));
  }

  header("Location: table.php?tableName=$tablename&pageNum=0");
?>
