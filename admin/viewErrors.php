<?php
	require('../db/adminCheck.php');
	require('../db/errorFormConnection.php');

	//make the query for selecting the errors
	$query = "SELECT * FROM dbo.ErrorForm";
	//find the results
	$result = sqlsrv_query($conn, $query);
	//find the number of rows returned
	$num = sqlsrv_num_rows($result);
?>

<!DOCTYPE HTML>
<html class="no-js">
	<head>
		<meta charset="utf-8">
		<?php header('X-UA-Compatible: IE=edge,chrome=1');?>
		<title>MGS Administrator</title>
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width">

		<!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
    <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables.css">
    <link rel="stylesheet" href="/css/demo_table.css">

		<link rel="stylesheet" href="../css/normalize.css">
    <link rel="stylesheet" href="../css/main.css">

    <script src="/DataTables-1.10.6/media/js/jquery.js"></script>
    <script src="/DataTables-1.10.6/media/js/jquery.dataTables.min.js"></script>
  	<script src="/js/vendor/modernizr-2.6.2.min.js"></script>
	<script>
		function confirmDelete(id){
			var confirmDelete = confirm('Are you sure, you want to delete this error report?');

			if(confirmDelete == true){
				window.location.href = 'deleteError.php?id=' + id;
			}
		}
	</script>
	</head>
	<body>
	    <div id="resultsbackground">
	    	<div id="container" class="home">
				<div id="searchresults">
        			<?php require('header.php'); ?>
        		</div>
        		<h2>View Errors</h2>
		      	<table style="width:100%" class="display dataTable">
		        	<thead>
                <tr>
  		          		<th>Sender</th>
  			          	<th>Title</th>
  			          	<th>Description</th>
  			          	<th>Sender Email</th>
  			          	<th>Date sent</th>
  			          	<th>Delete errors</th>
  		        	</tr>
              </thead>
              <tbody>
		        	<?php while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)):
		          		$timestamp = date_format($row['date'], 'm/d/Y');
		          		$id = $row['ID'];
		        	?>
		          	<tr>
		            	<td><?= $row['name'] ?></td>
		            	<td><?= $row['title'] ?></td>
		 	    	    <td><?= $row['descr'] ?></td>
		    	        <td><?= $row['email'] ?></td>
		            	<td><?= $timestamp ?></td>
		            	<td><a href=javascript:confirmDelete(<?= $id ?>);>Delete</a></td>
		          	</tr>
		        	<?php endwhile; ?>
              </tbody>
		      	</table>
			</div>
		</div>
	</body>
</html>
