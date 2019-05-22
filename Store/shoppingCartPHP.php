<?php
    require('../retrieveColumns.php');
    require('../errorReporter.php');
   
    if(isset($_POST['finalvalues'])) {
        //Add the values in $_POST['finalvalues'] into the session as an array so that if the page is refreshed
        //the values aren't lost
        $_SESSION['values'] = explode("," , $_POST['finalvalues']);
    } else {
        //Add $_SESSION['values'] to $_SESSION['values']
        $_SESSION['values'] = $_SESSION['values'];
    }

    $products = $_SESSION['values'];

    //This if statement is used to remove the products the user has chosen
    if(isset($_POST['remove']) && isset($_POST['ids'])) {
        //Remove the commas in the $_POST variable and add the values as an array to the variable
        $removedProducts = explode(",", $_POST['ids']);
        $index = array();
        //Loop through the products the user has chosen to remove
        foreach($removedProducts as $key => $value) {
            foreach($products as $keys => $values){
                $index[] = $keys;
            }
            //Index for the index array
            $j = 0;
            //Loop through the products in the cart
            for($i = 0; $i < sizeOf($products); $i++){
                if($value == $products[$i]){
                    //Remove the id
                    array_splice($products, $index[$j], 1);
                    //Remove the quantity
                    array_splice($products, $index[$j], 1);
                }
                //Increment to skip the key for the quantity
                $j+=2;
                //Increment to skip the quantity
                $i++;
            }
        }
    }

    //This if statement is used to update the quantities the user has changed
    if(isset($_POST['refresh']) && isset($_POST['quantities'])) {
        //Remove the commas in the $_POST variable and add the values as an array to the variable
        $changedQuantities = explode(",", $_POST['quantities']);
        $j = 0;
        //Loop through the products in the cart
        for($i = 0; $i < count($products); $i++) {
            //Skip the id to go to the quantity
            $i++;
            //Add the quantity to the cart
            $products[$i] = $changedQuantities[$j];
            //Move on to the next quantity
            $j++;
        }
    }
    
    //Set the session variable to the new values (removed products and/or updated quantities)
    $_SESSION['values'] = $products;
    $queries = [];
    $quantities = [];
    $ids = [];

    $sTable = "Products";
    if(isset($_SESSION['municipality']) && $_SESSION['municipality'] != "")
        $sTable = "CemeteryTranscriptions";

    $and = "AND COLUMN_NAME NOT IN('Municipality', 'Download', 'StatusCode')";
    $cols = retrieveColumns($sTable, $and, $storeConn);
    $sCols = implode(', ', $cols);

    $sjoin = "";
    if(!isset($_SESSION['municipality']) || $_SESSION['municipality'] == ""){
        $sCols = preg_replace('/ID/', "Products.ID", $sCols);
        $sCols = preg_replace('/Category/', "Category.Category", $sCols);
        $sjoin = "JOIN Category ON Products.Category = Category.ID";
    }

    if(!empty($_SESSION['values'])) {
        for($i=0; $i<count($products); $i++) {
            $queries[] = "SELECT $sCols FROM $sTable $sjoin WHERE $sTable.ID = ?";
            //Taking advantage of the loop to add the ids and quantities to arrays for the shoppingCart form
            $ids[] = $products[$i];
            $i++;
            $quantities[] = $products[$i];
        }

        $qry = implode(" UNION ", $queries);
        $stmt = sqlsrv_query($storeConn, $qry, $ids, array("Scrollable" => "static"));
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
        
        $rowValues = array();
        while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
            for($i = 0; $i < count($cols); $i++){
                $rowValues[] = $row[$cols[$i]];
            }
        }
    }

    $newarray = array();
    foreach($ids as $key => $value){
        foreach($rowValues as $keys => $values){
            if($value == $values){
                $j = $keys;
                $i = 1;
                $isFinished = false;
                while(!$isFinished){
                    if($i === count($rowValues)/count($ids)){
                        $isFinished = true;
                    }
                    $newarray[] = $rowValues[$j];
                    $j++;
                    $i++;
                }
            }
        }
    }
    $index = 0;
    $total = 0;
    $totalQuantity = 0;

    //These arrays are for the PayPal inputs
    $names = [];
    $amounts = [];
    $shipping = [];

    /* The following code is for the createPayPalCart.php */
    //It's easier to create the paypal items if they're initially in an associative array so
    //I need to create an array of column names for as many unique products as are in the cart
    $columns = array();
    foreach($ids as $key => $value){
        foreach($cols as $column){
            $columns[] = $column;
        }
    }

    $items = array();
    $j = 0;
    
    // for($i = 0; $i < count($columns); $i++){
    //     $items[$j][$columns[$i]] = $rowValues[$i];                
    //     //If the remainder equals the length of $cols - 1 then increment add the quantity to the
    //     //array and increment j
    //     if($i % count($cols) === count($cols) - 1){
    //         $items[$j]['Quantity'] = $quantities[$j];
    //         $j++;
    //     }
    // }

    foreach($ids as $key => $value){
        for($i = 0; $i < count($columns); $i++){
            if ($rowValues[$i] == $value) {
                for ($k=$i; $k < $i+count($cols); $k++) { 
                    $items[$j][$columns[$k]] = $rowValues[$k]; 
                }
                $items[$j]['Quantity'] = $quantities[$j];
                $j++;
            }
        }
    }

    $_SESSION['items'] = $items;
?>