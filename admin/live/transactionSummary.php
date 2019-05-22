<?php
	require('../../db/adminCheck.php');
	require('../../db/memberConnection.php');
	require('../../retrieveColumns.php');
	require('../../errorReporter.php');
	error_reporting(0);
	$_SESSION['transaction'] = $_GET['transaction'];
	
	$qryPayer = "SELECT * FROM PayerDetails WHERE TransactionsID = ?";
	$stmtPayer = sqlsrv_query($userConn, $qryPayer, array($_SESSION['transaction']));
	if($stmtPayer === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	$and = "AND COLUMN_NAME NOT IN ('ID')";
	$payerCols = retrieveColumns('PayerDetails', $and, $userConn);

	$qry = "SELECT Description FROM Transactions WHERE Transactions.ID = ?";
	$stmt = sqlsrv_query($userConn, $qry, array($_SESSION['transaction']));
	if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	$description = sqlsrv_fetch_array($stmt)['Description'];

	$descriptions = array("Non-Member Basic Research Package", "Non-Member Custom Research Package", "Member Basic Research Package", "Member Custom Research Package");
	$match = false;
	for($i = 0; $i < count($descriptions); $i++){
		if($description === $descriptions[$i]){
			$match = true;
			$i = count($descriptions);
		}
	}
	$_SESSION['researchMatch'] = $match;
	if($match)
		$aCol = retrieveColumns('ResearchDetails', 0, $userConn);
	else
		$aCol = retrieveColumns('TransactionDetails', 0, $userConn);
	
	$and = "OR TABLE_NAME = 'PayPalTransactions' AND COLUMN_NAME IN('PayerID') ORDER BY TABLE_NAME DESC";
	$cols = retrieveColumns('Transactions', $and, $userConn);

	$sjoin = "JOIN PayPalTransactions ON Transactions.TransactionID = PayPalTransactions.ID";
	$qry = "SELECT Transactions.ID, PayPalTransactions.TransactionID, MemberNum, Description, Total, Created, PayerID FROM Transactions $sjoin WHERE Transactions.ID = ?";
	$stmt = sqlsrv_query($userConn, $qry, array($_SESSION['transaction']), array("Scrollable" => "static"));
	if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

	$qryMember = "SELECT MemberNum FROM Transactions WHERE ID = ?";
	$stmtMember = sqlsrv_query($userConn, $qryMember, array($_SESSION['transaction']));
	if($stmtMember === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	$isMember = sqlsrv_fetch_array($stmtMember)['MemberNum'];

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
		        	"scrollCollapse": true,
		        	"scrollX": true,
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
							<?php foreach($cols as $col): ?>
								<th><?= $col ?></th>
							<?php endforeach; ?>
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
				<?php if($isMember == ""): ?>
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
				<?php endif; ?>
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