<?php
	require('../../db/adminCheck.php');
    require('../../db/adminConnection.php');
    require('../../retrieveColumns.php');
    require('../../errorReporter.php');
  	header('X-UA-Compatible: IE=edge,chrome=1');

	//  store the get varibles in local variables
    $id = $_GET['id'];
    $tablename = $_GET['tablename'];
    $recordID = $_GET['recordID'];
?>

<!DOCTYPE HTML>
    <html lang="en-US">
        <head>
	        <meta charset="utf-8">
	        	<title>Administration Panel</title>
	        <meta name="description" content="">
	        <meta name="viewport" content="width=device-width">

	        <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->

	        <link rel="stylesheet" href="/css/normalize.css">
	        <link rel="stylesheet" href="/css/main.css">
	        <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
	    	<script>
	    		function cancel(){
	    			location.href = "/admin/live/";
	    		}
	    	</script>
    	</head>
        <body>        	
		<div class = 'adminTables'>    
		    <div id="resultsbackground">
				<div id="container" class="home">
					<div id="searchresults">
            <?php require('header.php'); ?>
					</div>
					<div class ='alignEdit'>
                    	<h2>Edit This Entry</h2>
                	</div>
                	<div id="postdiv">
                		<form action="payperviewUpdate.php?id=<?php echo $id ?>&amp;tablename=<?php echo $tablename ?>&amp;recordID=<?php echo $recordID ?>" method="post" onsubmit="return checkboxes()">
                			<?php
                				//build a query to return the columns of the tableName that has been selected
				                // so that we can build a dynamic table for the user to input data with.
			                    $cols = retrieveColumns('PayPerView', 0, $conn);		                    

			      				$sql_value = "SELECT * FROM PayPerView WHERE ID = $id";
			      				$stmt_value = sqlsrv_query($conn, $sql_value, array(), array("Scrollable" => "static"));

			      				//if errors lets display
			                    if ($stmt_value === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

			                    $skipRow = 0; // skips the Primary Key in the resultset
			                    echo "<ul class='editUl'>";
			                    while($row_value = sqlsrv_fetch_array($stmt_value)){
		    			                 
				                    //cycle therw the results to capture the values
				                    foreach($cols as $colName){
				                    	 //avoid the PK
                        				if ($skipRow > 0) { 
					                    	$value = $row_value[$colName];
					                    	    echo "<li>";
					                    	    	if($skipRow == 0 || $skipRow == 5){
                        								echo "<input name='$colName' type='hidden' id='$colName' value='$value' />";
                        							}
                        							elseif($colName == 'RecordID' || $colName == 'TableName'){
                        								echo "<label>$colName</label>";
                        								echo "<input name='$colName' id='$colName' value='$value' disabled />";
                        							}
                        							elseif($colName == 'FileName'){
                        								echo "<label>$colName</label>";
					                    				echo "<input name='$colName' id='$colName' value='$value' required='' />";
                        							}
                        							else{
                        								echo "<label>$colName</label>";
					                    				echo "<input name='$colName' id='$colName' value='$value' />";
                        							}
				                                echo "</li>";
				                        } 
				                        $skipRow++;  
				                    }
			                	}
			                	echo "</ul>";	                    
			                ?>
                			<input name="submit" value="Update" type="submit" /> 
                			<input name="submit" value="Cancel" onclick="cancel()" type="submit" />            			
                		</form>
                	</div>
                </div>
          </div>
        </body>
    </html>
    