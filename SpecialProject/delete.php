<?php
  require('../db/adminCheck.php');
  require('../errorReporter.php');
	require('../db/adminConnection.php');

    //  store the get varibles in local variables
    $id = $_GET['id'];
    $tablename = $_GET['tablename'];
    $delete = $_GET['delete'];

    if(isset($_GET['delete'])){
        if(isset($_GET['status'])){
            if($_GET['status'] == 'NEW'){
                $sql = "DELETE FROM $tablename WHERE ID = ?";
                $stmt = sqlsrv_query( $conn, $sql, array($id), array( "Scrollable" => 'static' ));
                if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
            }

            else {
                $sql = "UPDATE $tablename SET StatusCode = 'DELETED' WHERE ID = ?";
                $stmt = sqlsrv_query( $conn, $sql, array($id), array( "Scrollable" => 'static' ));

                if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
            }
        }
    }

    header("Location: table.php?tableName=$tablename&pageNum=0");
?>
