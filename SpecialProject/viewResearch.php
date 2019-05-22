<?php
	require('../db/adminCheck.php');
	require('../db/memberConnection.php');
	require('../retrieveColumns.php');

	$and = "AND COLUMN_NAME NOT IN('TransactionsID') OR TABLE_NAME = 'Transactions' AND COLUMN_NAME IN ('MemberNum', 'Created')";
	$cols = retrieveColumns('ResearchDetails', $and, $userConn);
	$columns = implode(", ", $cols);
	$columns = preg_replace('/ID/', 'ResearchDetails.ID', $columns);
	$columns = preg_replace('/Description/', 'ResearchDetails.Description', $columns);
	//make the query for selecting the errors
	$query = "SELECT $columns FROM ResearchDetails JOIN Transactions ON ResearchDetails.TransactionsID = Transactions.ID";

	//find the resultsr
	$result = sqlsrv_query($userConn, $query);
?>

<!DOCTYPE HTML>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js"> <!--<![endif]-->
	<head>
		<meta charset="utf-8">
		<?php header('X-UA-Compatible: IE=edge,chrome=1');?>
		<title>MGS Administrator</title>
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width">
		<link rel="stylesheet" href="/css/main.css">
		<link rel="stylesheet" href="/css/normalize.css">
		<!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
	    <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables.css">
	    <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables.min.css">
	    <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables_themeroller.css">

	    <script src="/DataTables-1.10.6/media/js/jquery.js"></script>
	    <script src="/DataTables-1.10.6/media/js/jquery.dataTables.min.js"></script>
	    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
	    <script src="/DataTables-1.10.6/media/js/jquery.dataTables.js"></script>
	<script>
		function confirmDelete(id){
			var confirmDelete = confirm('Are you sure, you want to remove this researchReport?');

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
        		<h2>View Research Packages</h2>
		      	<table style="width:100%">
		      		<thead>
			        	<tr>
			          		<?php foreach($cols as $col): ?>
			          		<th><?= $col ?></th>
			          		<?php endforeach; ?>
			        	</tr>
			        </thead>
			        <tbody>
		        	<?php
		        		while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
		        			echo "<tr>";
		        			for($i = 0; $i < count($cols); $i++){
		        				if($cols[$i] == 'Created')
		        					echo "<td>" . $row[$cols[$i]]->format('Y-m-d') . "</td>";
		        				else
		        					echo "<td>". $row[$cols[$i]] . "</td>";
		        			}
		        			echo "<td><a href=javascript:confirmDelete(".$row['ID'].");>Finish the report</a></td>";
		        			echo "</tr>";
		        		}
		        	?>
		      	</table>
			</div>
		</div>
	</body>
</html>