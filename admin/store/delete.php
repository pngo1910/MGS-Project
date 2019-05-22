<?php
	require('../../db/adminCheck.php');
    require('../../db/adminConnection.php');

	//  store the get varibles in local variables
    $id = $_GET['id'];
    $tablename = $_GET['tablename'];

    //  check if OK is pressed for delete pop up
    if(isset($_GET['delete'])){
        $qry = "UPDATE $tablename SET StatusCode = 'DELETED' WHERE ID = ?";
        $stmt = sqlsrv_query($conn, $qry, array($id));
        //if errors lets display
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    }
    switch ($tablename){
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
    $_SESSION['message'] = "The $name StatusCode has been successfully set to DELETED. You can view and/or remove it from the database in View Deleted Records under Live Dash.";
    //  redirect to the table page
    header("Location: /admin/store/");   
   
?>