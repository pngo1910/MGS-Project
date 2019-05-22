<?php
    require('../../db/adminConnection.php');
    require('../../db/adminCheck.php');
    require('../../errorReporter.php');
    require('../../retrieveColumns.php');
   
    //  declare local variables equal to the global GET variables
    $id = $_GET['id'];
    $tableName = $_GET['tablename'];
    $recordID = $_GET['recordID'];

    //  check if id is not numeric
    //  redirect to the front page
    // Kill the script    
    if (!is_numeric($id)){
       header("Location: editPayPerView.php?tableName=$tableName&id=$id&recordID=$recordID");
        die; 
    }

    //  if statement to see if the update button was pressed
    //  store posted values from textboxes in an array
    if(isset($_POST)){
        $posted_values = array();

        $and = "AND COLUMN_NAME IN ('FileName', 'PageNum', 'StatusCode')";
        $colName = retrieveColumns('payperview', $and, $conn);

        
        foreach($_POST as $key => $value){
            array_push($posted_values, $value);
        }

        $formatted_values = array();
        for($i=0; $i<sizeof($posted_values)-2; $i++)
            array_push($formatted_values, $posted_values[$i]);
        array_push($formatted_values, "UPDATED");
    }

    //  if cancel button is clicked
    //  go back to the editPayPerView page
    if($_POST['submit'] == 'Cancel'){
        header("Location: editPayPerView.php?tableName=$tableName&pageNum=0");
    }
    else{

        $values_for_update = array();
        
        for($i=0; $i<sizeof($colName); $i++){
            if($formatted_values[$i] === "")
                array_push($values_for_update, $colName[$i] . " = NULL");
            else
                array_push($values_for_update, $colName[$i] . " = '" . $formatted_values[$i] . "'");
        }

        //  implode the array with commas to use in sql stateent
        $values_for_update = implode(",",$values_for_update);

        //  update the table with values
        //  execute the statement, or give errors if any
        $sql_update = "UPDATE PayPerView SET $values_for_update WHERE ID = ?;";

        $stmt_update = sqlsrv_query($conn, $sql_update, array($id));
        //if errors lets display
        if ($stmt_update === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

        $_SESSION['message'] = "PayPerView successfully updated. You can view it in View Updated Records.";
        header("Location: /admin/live/");
    }
?> 