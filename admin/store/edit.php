<?php
    require('../../db/adminCheck.php');
    require('../../db/adminConnection.php');
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
                location.href = "/admin/store/";
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
            		<form action="update.php?id=<?php echo $id ?>&amp;tablename=<?php echo $tablename ?>" method="post">
            			<?php
            				require('editPHP.php');
		                ?>
            			<input name="submit" value="Update" type="submit" /> 
            			<input name="submit" value="Cancel" onclick="cancel()" type="submit" />            			
            		</form>
            	</div>
            </div>
      </div>
    </body>
</html>
    