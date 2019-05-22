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
       header("Location: /admin/live/");
        die; 
    }

    //  if statement to see if the add button was pressed
    //  store posted values from textboxes in an array
    if(isset($_POST)){
        $posted_values = array();
        
        $and = "AND COLUMN_NAME NOT IN ('ID')";
        $colName = retrieveColumns('payperview', $and, $conn);

        
        foreach($_POST as $key => $value){
            array_push($posted_values, $value);
        }

        $formatted_values = array();
        for($i=0; $i<sizeof($posted_values)-1; $i++){
            array_push($formatted_values, $posted_values[$i]);
        }
        array_push($formatted_values, "NEW");
    }

    //  if cancel button is clicked
    //  go back to the live page
    if($_POST['submit'] == 'Cancel'){
        header("Location: /admin/live/");
    }
    else{

        $colNames = implode(", ", $colName);
        //  declare an array to store column names
        //  and posted values
        $values_for_insert = array();
        
        for($i=0; $i<sizeof($colName); $i++){
            if($formatted_values[$i] == "")
                array_push($values_for_insert, "NULL");
            else
                array_push($values_for_insert, $formatted_values[$i]);
        }
        
        $placeholders = array();
        for($i = 0; $i < count($values_for_insert); $i++)
            $placeholders[] = "?";

        $placeholders = implode(", ", $placeholders);

        //  execute the statement, or give errors if any
        $sql_insert = "INSERT INTO PayPerView ($colNames) VALUES($placeholders);";

        $stmt_insert = sqlsrv_query($conn, $sql_insert, $values_for_insert);
        //if errors lets display
        if ($stmt_insert === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

        $_SESSION['message'] = "PayPerView successfully created. You can view it in View New Records.";
        header("Location: /admin/live/");
    }
?> 