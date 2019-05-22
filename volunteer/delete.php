<?php
  require('../db/volunteerCheck.php');
  require('../errorReporter.php');
	require('../db/volunteerConnection.php');
  require('../retrieveColumns.php');

  // store the get varibles in local variables
  $id = $_GET['id'];
  $tablename = $_GET['tablename'];
  $delete = $_GET['delete'];

  /*
  * Primary Key Columns
  */
  $primaryKeys = retrievePrimaryKeys($tablename, $conn);

  /* Indexed column (used for fast and accurate table cardinality) */
  $sIndexColumn = $primaryKeys[0];

  if (isset($_GET['delete']) && isset($_GET['status'])) {
    // if ($_GET['status'] == 'NEW') {
    //   $sql = "DELETE FROM $tablename WHERE $sIndexColumn = ?";

    //   $stmt = sqlsrv_query($conn, $sql, array($id), array( "Scrollable" => 'static' ));s

    //   if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    // }
    // else {
      $sql = "UPDATE $tablename SET StatusCode = 'DELETED' WHERE $sIndexColumn = ?";
      $stmt = sqlsrv_query($conn, $sql, array($id), array( "Scrollable" => 'static' ));

      if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    //}
  }

  header("Location: table.php?tableName=$tablename&pageNum=0");
?>
