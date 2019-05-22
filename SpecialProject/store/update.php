<?php
    require('../../db/adminConnection.php');
    require('../../db/adminCheck.php');
    require('../../retrieveColumns.php');
    require('../../errorReporter.php');
   
    //  declare local variables equal to the global GET variables
    $id = $_GET['id'];
    $tableName = $_GET['tablename'];

    //  check if id is not numeric
    //  redirect to the front page
    // Kill the script    
    if (!is_numeric($id)){
       header('Location: /admin/store/'); // brings the user back to index.php
        die; 
    }

    //  if statement to see if the update button was pressed
    //  store posted values from textboxes in an array
    if(isset($_POST)){
        $posted_values = array();

        foreach($_POST as $key => $value)
            array_push($posted_values, $value);

        $formatted_values = array();

        for($i=0; $i<sizeof($posted_values)-1; $i++)
            array_push($formatted_values, $posted_values[$i]);
        
        array_push($formatted_values, 'UPDATED');
    }
    //  if cancel button is clicked
    //  go back to the table page
    if($_POST['submit'] == 'Cancel'){
        header("Location: /admin/store/");
    }
    else{

        $and = "AND COLUMN_NAME NOT IN('ID')";
        $colName = retrieveColumns($tableName, $and, $conn);

        //  declare an array to store column names
        //  and posted values
        $values_for_update = array();

        for($i=0; $i<sizeof($colName); $i++){
            if($formatted_values[$i] === "")
                array_push($values_for_update, $colName[$i] . "=NULL");
            else
                array_push($values_for_update, $colName[$i] . "='" . $formatted_values[$i] . "'");
        }

        //  implode the array with commas to use in sql stateent
        $values_for_update = implode(",",$values_for_update);
       
        //  update the table with values
        //  execute the statement, or give errors if any
        $sql_update = "UPDATE $tableName SET $values_for_update WHERE ID = $id;";
    
        $stmt_update = sqlsrv_query($conn, $sql_update);
        //if errors lets display
        if ($stmt_update === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

        switch ($tableName){
            case "products":
                $name = "product";
                break;
            case "category":
                $name = "category";
                break;
            case "cemeterytranscription":
                $name = "cemetery transcription";
                break;
        }
        $_SESSION['message'] = "The $name  was sucessfully updated. You can view it in View Updated Records under Live Dash.";
        header("Location: /admin/store/");
    }
?> 