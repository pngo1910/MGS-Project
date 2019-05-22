<?php
    if($tablename === 'products' || $tablename === 'cemeterytranscriptions'){
        $qry = "SELECT Download FROM $tablename WHERE ID = ?";
        $stmt = sqlsrv_query($conn, $qry, array($id));
        $download = sqlsrv_fetch_array($stmt)['Download'];

        if($tablename === 'products'){
            $qryCategory = "SELECT * FROM Category";
        	$stmtCategory = sqlsrv_query($conn, $qryCategory, array(), array("Scrollable" => "static"));
        	//if errors lets display
            if ($stmtCategory === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
        }

        $and = "AND COLUMN_NAME NOT IN('StatusCode')";
        $columns = retrieveColumns($tablename, $and, $conn);		                    

		$sql_value = "SELECT * FROM $tablename WHERE ID = $id";

		$stmt_value = sqlsrv_query($conn, $sql_value, array(), array("Scrollable" => "static"));

    		//if errors lets display
        if ($stmt_value === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

        $skipRow = 0; // skips the Primary Key in the resultset

        echo "<ul class='editUl'>";
        while($row_value = sqlsrv_fetch_array($stmt_value, SQLSRV_FETCH_ASSOC)){
            //cycle therw the results to capture the values
            foreach($columns as $colName){
            	 //avoid the PK
    			if ($skipRow > 0) { 
                	$value = $row_value[$colName];
                	
                	    echo "<li>";
                            if($tablename === "products"){
                                if($colName == 'Category'){
                                    echo "<label>$colName</label><select name='$colName' id='$colName'>";
                                    while($row_category = sqlsrv_fetch_array($stmtCategory, SQLSRV_FETCH_ASSOC)){
                                        if($row_category['ID'] == $value)
                                            echo "<option value=\"" . $row_category['ID'] . "\" selected>" . $row_category['Category'] . "</option>";
                                        else
                                            echo "<option value=\"" . $row_category['ID'] . "\">" . $row_category['Category'] . "</option>";
                                    }
                                    echo "</select>";
                                }
                            }

                            if($tablename === "cemeterytranscriptions"){
                                if($colName == 'Municipality'){
                                    $qry = "SELECT DISTINCT Municipality FROM Cemeteries WHERE Municipality IS NOT NULL AND Municipality <> '' ORDER BY Municipality";
                                    $stmt = sqlsrv_query($conn, $qry);
                                    if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
                                    echo "<label>$colName</label><select name='$colName' id='$colName'>";
                                    while($row = sqlsrv_fetch_array($stmt)){
                                        if($value === $row['Municipality'])
                                            echo "<option value=\"".$row['Municipality']."\" selected>".$row['Municipality']."</option>";
                                        else
                                            echo "<option value=\"".$row['Municipality']."\">".$row['Municipality']."</option>";
                                    }
                                    echo "</select>";
                                }
                            }

                	    	if($skipRow == 0){
    							echo "<input name='$colName' type='hidden' id='$colName' value='$value' />";
    						} elseif($colName == 'Description'){
    							echo "<label for='$colName'>$colName</label><textarea name='$colName' id='$colName' rows='5'>".$value."</textarea>";
    						} elseif($colName == 'Shipping'){
                                echo "<label for='$colName'>$colName</label><input name='$colName' id='$colName' value=\"$value\" pattern=\"[0-9]+\.[0-9]{2}\" title=\"Example: 12.50\" />";
                            } elseif($colName == 'Price'){
                                echo "<label for='$colName'>$colName</label><input name='$colName' id='$colName' value=\"$value\" pattern=\"[0-9]+\.[0-9]{2}\" title=\"Example: 12.50\" required />";
                            } elseif($colName == 'Download'){
                                echo "<input type='hidden' name='$colName' value=\"0\" />";
                                echo "<label for='$colName'>$colName</label><input type='checkbox' name='$colName' id='$colName' value=\"1\"".(($download == 1) ? "checked" : "" )." />";
                            } elseif($colName != 'Category' && $colName != 'Municipality'){
    							echo "<label for='$colName'>$colName</label><input name='$colName' id='$colName' value=\"$value\" required />";
    						}
                        echo "</li>";
                } 
                $skipRow++;  
            }
        }
        echo "</ul>"; 
    } else {
        $and = "AND COLUMN_NAME NOT IN('StatusCode')";
        $columns = retrieveColumns($tablename, $and, $conn);

        $sql_value = "SELECT * FROM $tablename WHERE ID = $id";

        $stmt_value = sqlsrv_query($conn, $sql_value);

        //if errors lets display
        if ($stmt_value === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

        $skipRow = 0; // skips the Primary Key in the resultset

        echo "<ul class='editUl'>";
        while($row_value = sqlsrv_fetch_array($stmt_value, SQLSRV_FETCH_ASSOC)){
                 
            //cycle therw the results to capture the values
            foreach($columns as $colName){
                 //avoid the PK
                if ($skipRow > 0) { 
                    $value = $row_value[$colName];
                        echo "<li>";
                            if($skipRow == 0){
                                echo "<input name='$colName' type='hidden' id='$colName' value='$value' />";
                            }
                            else{
                                echo "<label>$colName</label>";
                                echo "<input name='$colName' id='$colName' value='$value' required />";
                            }
                        echo "</li>";
                } 
                $skipRow++;  
            }
        }
        echo "</ul>";  
    }
?>