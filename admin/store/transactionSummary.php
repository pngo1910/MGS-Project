<?php
	require('../../db/adminCheck.php');
	require('../../db/storeConnection.php');
	require('../../retrieveColumns.php');
	require('../../errorReporter.php');
	error_reporting(0);
	$_SESSION['transaction'] = $_GET['transaction'];
	$and = "OR TABLE_NAME = 'PayPalTransactions' AND COLUMN_NAME IN('PayerID') ORDER BY TABLE_NAME DESC";
	$cols = retrieveColumns('Transactions', $and, $storeConn);

	$sjoin = "JOIN PayPalTransactions ON Transactions.TransactionID = PayPalTransactions.ID";
	$qry = "SELECT Transactions.ID, MemberNum, PayPalTransactions.TransactionID, Total, TotalItems, Created, PayerID FROM Transactions $sjoin WHERE Transactions.ID = ?";
	$stmt = sqlsrv_query($storeConn, $qry, array($_SESSION['transaction']), array("Scrollable" => "static"));
	if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

	$qryPayer = "SELECT * FROM PayerDetails WHERE TransactionsID = ?";
	$stmtPayer = sqlsrv_query($storeConn, $qryPayer, array($_SESSION['transaction']));
	if($stmtPayer === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	$and = "AND COLUMN_NAME NOT IN ('ID')";
	$payerCols = retrieveColumns('PayerDetails', $and, $storeConn);

	$qry = "SELECT ItemID FROM TransactionDetails WHERE TransactionsID = ?";
	$stmtDetails = sqlsrv_query($storeConn, $qry, array($_SESSION['transaction']));
	if($stmtDetails === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	$items = array();
	while($row = sqlsrv_fetch_array($stmtDetails))
		$items[] = $row['ItemID'];

	$queries = array();
	for($i = 0; $i < count($items); $i++)
		$queries[] = "SELECT ID, Shipping FROM Products WHERE ID = ?";

	$qry = implode(" UNION ", $queries);
	$stmtProducts = sqlsrv_query($storeConn, $qry, $items);
	if($stmtProducts === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	$shipping = 0;
	while($row = sqlsrv_fetch_array($stmtProducts)){
		$shipping += $row['Shipping'];
	}
	$aCol = retrieveColumns('TransactionDetails', 0, $storeConn);

	header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache');
?>
<!DOCTYPE HTML>
<html lang="en-US">
	<head>
    	<meta charset="utf-8">
    	 <?php header('X-UA-Compatible: IE=edge,chrome=1');?>
    	<title>MGS Administrator</title>
    	<meta name="description" content="">
    	<meta name="viewport" content="width=device-width">
    	<link rel="stylesheet" href="/css/normalize.css">
    	<link rel="stylesheet" href="/css/main.css">
	    <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables.css">
	    <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables.min.css">
	    <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables_themeroller.css">

	    <script src="/DataTables-1.10.6/media/js/jquery.js"></script>
	    <script src="/DataTables-1.10.6/media/js/jquery.dataTables.min.js"></script>
	    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
	    <script src="/DataTables-1.10.6/media/js/jquery.dataTables.js"></script>
    	<script type="text/javascript">
			var asInitVals = new Array();
			var j_cols = new Array();
			<?php foreach ($aCol as $key => $value) : ?>
				j_cols.push({'sTitle' : '<?= $value ?>'});       		
			<?php endforeach; ?>

		    $(document).ready(function() {
		        window.alert = function(){return null;};
		        var oTable = $('#example').dataTable( {
		            "bProcessing": true,
		            "bPaginate": true, 
		            "bServerSide": true,                 
		            "bsortClasses": false,              
		            "sPaginationType": 'full_numbers',
					"aLengthMenu": [ 10, 25, 50, 100, 500 ],
		            "bFilter": true,
		            "bInput" : true,
		            "aoColumns": j_cols,
		            "sAjaxSource": "transactionDetailsQuery.php",	
		            "oLanguage": {
		                "sSearch": "Search all columns:"
		            },
		        } );

		        $("tfoot input").keyup( function () {
		             //Filter on the column (the index) of this element 
		            oTable.fnFilter( this.value, $("tfoot input").index(this) );
		        } );

		        /*
		         * Support functions to provide a little bit of 'user friendlyness' to the textboxes in 
		         * the footer
		         */
		        $("tfoot input").each( function (i) {
		            asInitVals[i] = this.value;
		        } );
		        
		        $("tfoot input").focus( function () {
		            if ( this.className == "search_init" )
		            {
		                this.className = "";
		                this.value = "";
		            }
		        } );
		        
		        $("tfoot input").blur( function (i) {
		            if ( this.value == "" )
		            {
		                this.className = "search_init";
		                this.value = asInitVals[$("tfoot input").index(this)];
		            }
		        } );
		    } );
		</script>
		<style>
		#example tfoot{
			display: table-header-group;
		}
		</style>
	</head>
	<body>
		<div id="resultsbackground_table">
	    	<div id="container" class="home">
	    		<?php require('header.php'); ?>
				<div id="head">
					<h2>Transactions</h2>
				</div>
				<table class="receipt">
					<thead>
						<tr>
							<?php 
								foreach($cols as $col){
								 	if($col === "Shipped")
								 		echo "<th>Shipping</th>";
								 	else
										echo "<th>$col</th>";
								}
							?>
						</tr>
					</thead>
					<tfoot>
					</tfoot>
					<tbody>
						<?php while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
						<tr>
							<?php for($i = 0; $i < count($cols); $i++): ?>
								<?php
									if($cols[$i] === "Created")
										echo "<td>" . $row[$cols[$i]]->format('Y-m-d') . "</td>";
									elseif($cols[$i] === "Shipped")
										echo "<td>$".number_format($shipping, 2, '.', '')."</td>";
									elseif(is_numeric($row[$cols[$i]]) && !is_int($row[$cols[$i]]))
										echo "<td>$" . $row[$cols[$i]] . "</td>";
									else
										echo "<td>" . $row[$cols[$i]] . "</td>";
								?>
							<?php endfor; ?>
						</tr>
						<?php endwhile; ?>
					</tbody>
				</table>
				<table class="receipt">
					<thead>
						<tr>
							<?php 
								foreach($payerCols as $col)
									echo "<th>$col</th>";
							?>
						</tr>
					</thead>
					<tfoot>
					</tfoot>
					<tbody>
						<?php while($row = sqlsrv_fetch_array($stmtPayer, SQLSRV_FETCH_ASSOC)): ?>
						<tr>
							<?php for($i = 0; $i < count($payerCols); $i++): ?>
									<td><?= $row[$payerCols[$i]] ?></td>
							<?php endfor; ?>
						</tr>
						<?php endwhile; ?>
					</tbody>
				</table>
				<table class="display" id="example">
					<thead>
					</thead>
					<tfoot>
						<tr>
							<?php
								foreach($aCol as $col)
									echo "<th><input type='text' name='search_$col' placeholder='$col' id='$col' class='search_init' /></th>";
							?>
						</tr>
					</tfoot>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
	</body>
</html>