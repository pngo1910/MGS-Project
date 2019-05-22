<?php
    if($tableName === "products" || $tableName === "cemeterytranscriptions"){
        foreach($cols as $column){
            echo "<label for='$column'>$column</label>";
            if($tableName === "products"){
                if($column == "Category"){
                    echo "<select name='$column'>";
                    for($i = 0; $i < sizeOf($categories); $i++){
                        $option = "<option value='".$categories[$i]."'>";
                        $i++;
                        $option .= $categories[$i] . "</option>";
                        echo $option;
                    }
                    echo "</select>";
                }
            }

            if($tableName === "cemeterytranscriptions"){
                if($column == "Municipality"){
                    $qry = "SELECT DISTINCT Municipality FROM Cemeteries wHERE Municipality IS NOT NULL AND Municipality <> '' ORDER BY Municipality";
                    $stmt = sqlsrv_query($conn, $qry);
                    if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
                    echo "<select name='$column'>";
                        while($row = sqlsrv_fetch_array($stmt))
                            echo "<option value='".$row['Municipality']."'>".$row['Municipality']."</option>";
                    echo "</select>";
                }
            }

            if($column == "Download"){
                echo "<input type='hidden' name='$column' value='0' />";
                echo "<input type='checkbox' name='$column' value='1' />";
            } elseif($column == "Price"){
                echo "<input name='$column' pattern='[0-9]+\.[0-9]{2}' title='Example: 12.50' required />";
            } elseif($column == "Shipping"){
                echo "<input name='$column' pattern='[0-9]+\.[0-9]{2}' title='Example: 12.50' />";
            } elseif($column == "Description"){
                echo "<textarea name='$column' rows='6' cols='40'></textarea>";
            } elseif($column != "Category" && $column != "Municipality"){
                echo "<input name='$column' required />";
            }
        }
    } else{
        foreach($cols as $column){
            echo "<label for='$column'>$column</label>";
            echo "<input name='$column' autofocus required />";
        }
    }
?>