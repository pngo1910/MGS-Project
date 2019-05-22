<?php
	require('../../db/adminCheck.php');
    require('../../db/adminConnection.php');
    require('../../errorReporter.php');
    require('../../retrieveColumns.php');
    
    $tableName = $_GET['table'];
  
    $and = "AND COLUMN_NAME NOT IN('ID')";
    $cols = retrieveColumns($tableName, $and, $conn);
    $columns = implode(", ", $cols);
    
    $values = array();
    foreach($_POST as $key => $value){
        if($value != "Submit"){
            if($value == ""){
                $values[] = "NULL";
            } else{
                $values[] = "'$value'";
            }
        }
    }
    $values[] = "'NEW'";
    $values = implode(", ", $values);
    
    $qry = "INSERT INTO $tableName ($columns) VALUES($values)";
   
    $stmt = sqlsrv_query($conn, $qry);
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    
    switch ($tableName){
        case "products":
            $name = "product";
            break;
        case "category":
            $name = "category";
            break;
        case "cemeterytranscriptions":
            $name = "cemetery transcript";
            break;
    }
    $_SESSION['message'] = "The $name was successfully created. You can view it in View New Records under Live Dash.";
    header("Location:/admin/store/");
?>