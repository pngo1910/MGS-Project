<?php
	require('../../db/adminCheck.php');
	require('../../db/memberConnection.php');
	require('../../retrieveColumns.php');
	require('../../errorReporter.php');
	error_reporting(0);
	$_SESSION['research'] = $_GET['research'];

	$and = " AND COLUMN_NAME NOT IN ('MemberNum')";
	$sTable = "Transactions";

	$cols = retrieveColumns($sTable, $and, $userConn);

	$cols[] = "PayerID";

	$cols = preg_replace('/^ID/', "$sTable.ID", $cols);
    $cols = preg_replace('/^TransactionID/', "$sTable.TransactionID", $cols);
    $cols = preg_replace('/^PayerID/', "PayPalTransactions.PayerID", $cols);

	$qry = "SELECT " . implode($cols, ", ") . " FROM $sTable JOIN PayPalTransactions ON 
                PayPalTransactions.ID = $sTable.TransactionID WHERE $sTable.ID = ?";

	$stmt = sqlsrv_query($userConn, $qry, array($_SESSION['research']), array("Scrollable" => "static"));
	if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

	$cols = preg_replace('/^Transactions.ID/', "ID", $cols);
    $cols = preg_replace('/^Transactions.TransactionID/', "TransactionID", $cols);
    $cols = preg_replace('/^PayPalTransactions.PayerID/', "PayerID", $cols);

	$transactionInfo = sqlsrv_fetch_array($stmt);

	$and = " AND COLUMN_NAME NOT IN ('MemberNum')";
	$sTable = $transactionInfo['MemberTable'];

	$colsResearcher = retrieveColumns($sTable, $and, $userConn);

	$qry = "SELECT " . implode($colsResearcher, ", ") . " FROM $sTable JOIN Transactions 
			ON $sTable.MemberNum = Transactions.MemberNum AND Transactions.ID = ?";

	$stmt = sqlsrv_query($userConn, $qry, array($_SESSION['research']), array("Scrollable" => "static"));
	if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

	$researcherInfo = sqlsrv_fetch_array($stmt);

	$and = " AND COLUMN_NAME NOT IN ('TransactionsID', 'ID', 'Complete')";
	$sTable = "ResearchDetails";

	$colsDetails = retrieveColumns($sTable, $and, $userConn);

	$qry = "SELECT " . implode($colsDetails, ", ") . " FROM $sTable WHERE TransactionsID = ?";

	$stmt = sqlsrv_query($userConn, $qry, array($_SESSION['research']), array("Scrollable" => "static"));
	if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

	$researchDetails = sqlsrv_fetch_array($stmt);

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
	    <script type="text/javascript" charset="utf8" src="/DataTables-1.10.6/media/js/test.js"></script>
    	<script type="text/javascript">
			var asInitVals = new Array();
			var j_cols = new Array();
			<?php foreach ($aCol as $key => $value) : ?>
				j_cols.push({'sTitle' : '<?= $value ?>'});       		
			<?php endforeach; ?>

		    $(document).ready(function() {
		        window.alert = function(){return null;};
		        var calcDataTableHeight = function() {
	                return $(window).height()*55/100;
	            };
		        var oTable = $('#example').dataTable( {
		        	"scrollY": calcDataTableHeight(),
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
		            "sAjaxSource": "purchaseDetailsQuery.php",	
		            "oLanguage": {
		                "sSearch": "Search all columns:"
		            },
		            "fnInitComplete": function() {
		                $('.dataTables_scrollFoot').insertAfter($('.dataTables_scrollHead'));
		            }
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
		<div id="resultsbackground">
	    	<div id="container" class="home">
	    		<?php require('header.php'); ?>
				<div id="head">
					<h2>Transactions</h2>
				</div>
				<?php if ($transactionInfo != NULL) : ?>
					<table class="receipt">
						<thead>
							<tr>
								<?php foreach($cols as $col): ?>
									<?php if ($col!=="MemberTable"): ?>
										<th><?= $col ?></th>
									<?php endif; ?>
								<?php endforeach; ?>
							</tr>
						</thead>
						<tfoot>
						</tfoot>
						<tbody>
							<tr>
								<?php for($i = 0; $i < count($cols); $i++): ?>
									<?php
										if($cols[$i] !== "MemberTable")
										{
											if($cols[$i] === "Created")
												echo "<td>" . $transactionInfo[$cols[$i]]->format('Y-m-d') . "</td>";
											elseif(is_numeric($transactionInfo[$cols[$i]]) && !is_int($transactionInfo[$cols[$i]]))
												echo "<td>$" . $transactionInfo[$cols[$i]] . "</td>";
											else
												echo "<td>" . $transactionInfo[$cols[$i]] . "</td>";
										}
									?>
								<?php endfor; ?>
							</tr>
						</tbody>
					</table>
		        <?php endif ?>
		        <?php if ($researcherInfo != NULL) : ?>
		        	<h3>Researcher Info</h3>
					<table class="receipt">
						<thead>
							<tr>
								<?php foreach($colsResearcher as $col): ?>
									<th><?= $col ?></th>
								<?php endforeach; ?>
							</tr>
						</thead>
						<tfoot>
						</tfoot>
						<tbody>
							<tr>
								<?php for($i = 0; $i < count($colsResearcher); $i++): ?>
									<?php
										echo "<td>" . $researcherInfo[$colsResearcher[$i]] . "</td>";
									?>
								<?php endfor; ?>
							</tr>
						</tbody>
					</table>
		        <?php endif ?>
		        <?php if ($researchDetails != NULL) : ?>
		        	<h3>Researcher Info</h3>
					<table class="receipt">
						<thead>
							<tr>
								<?php foreach($colsDetails as $col): ?>
									<th><?= $col ?></th>
								<?php endforeach; ?>
							</tr>
						</thead>
						<tfoot>
						</tfoot>
						<tbody>
							<tr>
								<?php for($i = 0; $i < count($colsDetails); $i++): ?>
									<?php
										echo "<td>" . $researchDetails[$colsDetails[$i]] . "</td>";
									?>
								<?php endfor; ?>
							</tr>
						</tbody>
					</table>
		        <?php endif ?>
			</div>
		</div>
		
	</body>
</html>