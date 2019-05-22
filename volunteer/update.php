<?php
  require('../db/volunteerCheck.php');
  require('../errorReporter.php');
  require('../db/volunteerConnection.php');
  require('../retrieveColumns.php');

  // declare local variables equal to the global GET variables
  $id = $_GET['id'];
  $tableName = $_GET['tablename'];

  // if (!is_numeric($id))
  //   die(header("location: table.php?tableName=$tableName"));

  $columns = retrieveColumns($tableName, 0, $conn);
  $primaryKeys = retrievePrimaryKeys($tableName, $conn);
  $idColumn = $primaryKeys[0];
  $oldtypeid = null;

  if (isset($_POST)) {
    if (strtolower($tableName) === 'typecodes') {
      $oldtypeid = $_POST['oldtypeid'];
      unset($_POST['oldtypeid']);
    }

    $formatted_values = array_values($_POST);
    // drop the submit button value
    array_pop($formatted_values);

    if (strtolower($tableName) !== 'typecodes')
      array_shift($columns);
  }

  if ($_POST['submit'] == 'Cancel')
    die(header("Location: table.php?tableName=$tableName"));

  $values_for_update = array();
  $values = array();

  for ($i=0; $i<count($columns); $i++) {
    if ($columns[$i] === 'StatusCode' && ($formatted_values[$i] === 'NEW' OR $formatted_values[$i] === 'CURRENT') ){
      $formatted_values[$i] = 'UPDATED';
    }
    elseif ($columns[$i] === 'BookCode') {
      $formatted_values[$i] = strtoupper($formatted_values[$i]);
    }

    $values_for_update[] = $columns[$i] . ' = ?';
    $values[] = $formatted_values[$i];
  }

  $values_for_update = implode(',', $values_for_update);
  $sql_update = "UPDATE $tableName SET $values_for_update WHERE $idColumn = ?";
  $values[] = $id;

  $stmt_update = sqlsrv_query($conn, $sql_update, $values, array('Scrollable' => 'static' ));

  if ($stmt_update === false) {
    $errors = sqlsrv_errors();

    // show an error message on the edit page
    // if the data is too large for the column.
    if ($errors[0]['code'] == 8152) {
      $_SESSION['error'] = 'The data is too large for the column.';
      die(header("location: edit.php?tablename=$tableName&$idColumn=$id"));
    }
    // show an error message on the edit page
    // if the typecode id is already used.
    elseif ($errors[0]['code'] == 2627) {
      $typeid = $formatted_values[0];
      $_SESSION['error'] = "The typecode id '$typeid' is already used.";
      die(header("location: edit.php?tablename=$tableName&$idColumn=$id"));
    }
    else {
      errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    }
  }
  else {
    if ($columns[0] === 'TypeID') {
      // whitespace is added to these for some reason.
      list($typeid_path, $new_path) = array_map(function($type) {
        return '../PDF/' . preg_replace('/\s*$/', '', $type);
      }, array($oldtypeid, $formatted_values[0]));

      if (is_dir($typeid_path)) rename($typeid_path, $new_path);
      else mkdir($new_path);
    }
  }

  header("Location: table.php?tableName=$tableName");
?>
