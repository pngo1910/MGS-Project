<?php
	require('../../db/adminCheck.php');
    require('../../db/adminConnection.php');
    require('../../errorReporter.php');

	//  store the get varibles in local variables
    $id = $_GET['id'];
    $tablename = $_GET['tablename'];
    $delete = $_GET['delete'];

    //  check if OK is pressed for delete pop up
    if(isset($_GET['delete'])){
        $sql = "UPDATE $tablename SET StatusCode = 'DELETED' WHERE ID = ?";
        $stmt = sqlsrv_query($conn, $sql, array($id), array("Scrollable" => 'static'));
     
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    }
    $_SESSION['message'] = "PayPerView StatusCode is successfully set to DELETED. You can view and/or remove it from the database in View Deleted Records.";
    //  redirect to the table page
    header("Location: /admin/live/");   
   
?>