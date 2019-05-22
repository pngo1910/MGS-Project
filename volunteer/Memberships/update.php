<?php
  require('../../db/membershipAdminCheck.php');
  require('../../errorReporter.php');
  require('../../db/memberConnection.php');
  require('../../retrieveColumns.php');

  // declare local variables equal to the global GET variables
  $id = $_GET['id'];
  $tableName = $_GET['tablename'];

  // if (!is_numeric($id))
  //   die(header("location: table.php?tableName=$tableName"));

  $columns = retrieveColumns($tableName, 0, $userConn);
  $primaryKeys = retrievePrimaryKeys($tableName, $userConn);
  $idColumn = $primaryKeys[0];

  if (isset($_POST)) {
    $formatted_values = array_values($_POST);
    // drop the submit button value
    array_pop($formatted_values);
    array_shift($columns);
  }

  if ($_POST['submit'] == 'Cancel')
    die(header("Location: table.php?tableName=$tableName"));

  $values_for_update = array();
  $values = array();

  for ($i=0; $i<count($columns); $i++) {
    $values_for_update[] = $columns[$i] . ' = ?';
    $values[] = $formatted_values[$i];
  }

  $values_for_update = implode(',', $values_for_update);
  $sql_update = "UPDATE $tableName SET $values_for_update WHERE $idColumn = ?";
  $values[] = $id;

  $stmt_update = sqlsrv_query($userConn, $sql_update, $values, array('Scrollable' => 'static' ));

  if ($stmt_update === false) {
    $errors = sqlsrv_errors();

    // show an error message on the edit page
    // if the data is too large for the column.
    if ($errors[0]['code'] == 8152) {
      $_SESSION['error'] = 'The data is too large for the column.';
      die(header("location: edit.php?tablename=$tableName&$idColumn=$id"));
    }
    else {
      errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    }
  }

  header("Location: table.php?tableName=$tableName&pageNum=0");
?>
