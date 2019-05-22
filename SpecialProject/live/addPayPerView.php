<?php
	require('../../db/adminCheck.php');
    require('../../db/mgsConnection.php');
    require('../../retrieveColumns.php');
    require('../../errorReporter.php');
  	header('X-UA-Compatible: IE=edge,chrome=1');

	//  store the get varibles in local variables
    $id = $_GET['id'];
    $tablename = $_GET['tablename'];
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
	        <script src="/js/vendor/jquery.min.js"></script>
	    	<script type="text/javascript">
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
                    	<h2>Add This Entry</h2>
                	</div>
                	<div id="postdiv">
                		<p id='message' class='errorColor'></p>
                		<form action="payperviewInsert.php?id=<?php echo $id ?>&amp;tablename=<?php echo $tablename ?>" method="post" onsubmit="return checkboxes()">
                			<?php
                				$and = "AND COLUMN_NAME NOT IN('ID', 'StatusCode')";
                				$cols = retrieveColumns('PayPerView', $and, $conn);
                				echo "<ul class='editUl'>";
			                    foreach($cols as $colName){
		                    	    echo "<li>";
        								if($colName == 'RecordID')
	                    					echo "<input name='$colName' type='hidden' id='$colName' value='$id' />";
	                    				elseif($colName == 'TableName')
	                    					echo "<input name='$colName' type='hidden' id='$colName' value='$tablename' />";
	                    				elseif($colName == 'FileName')
	                    					echo "<label for\"$colName\">$colName</label><input autofocus='autofocus' name='$colName' id='$colName' pattern='[A-Za-z0-9]*[^.pdf]' required='' />";
	                    				else
	                    					echo "<label for\"$colName\">$colName</label><input name='$colName' id='$colName' />";
	                                echo "</li>";
			                	}
			                	echo "</ul>";
			                ?>
                			<input name="submit" value="Add" type="submit" /> 
                			<input name="submit" value="Cancel" onclick="cancel()" type="submit" />            			
                		</form>
                		<p><b>**Note</b>: File names MUST reference a pdf file. Don't add .pdf to the end of the entered file name.</p>
                		<p><b>**Note</b>: Make sure the pdf being referenced has been uploaded using PDF Upload.</p>
                		<p><b>**Temporary</b>: For now, when the PPV file is pushed to the live database it won't be viewed on the Member Results page.</p>
                	</div>
                </div>
          </div>
        </body>
    </html>
    