<?php
	require('../../db/adminCheck.php');
	require('../../db/storeConnection.php');
	require('../../retrieveColumns.php');
	require('../../errorReporter.php');
	error_reporting(0);
	$_SESSION['purchase'] = $_GET['purchase'];
	$and = " AND COLUMN_NAME NOT IN ('Shipped', 'MemberTable', 'MemberNum')";
	$sTable = "Transactions";

	$cols = retrieveColumns($sTable, $and, $storeConn);
	$cols[] = 'PayerID';

	$cols = preg_replace('/^ID/', "$sTable.ID", $cols);
    $cols = preg_replace('/^TransactionID/', "$sTable.TransactionID", $cols);
    $cols = preg_replace('/^PayerID/', "PayPalTransactions.PayerID", $cols);

	$qry = "SELECT " . implode($cols, ", ") . " FROM $sTable JOIN PayPalTransactions ON 
                PayPalTransactions.ID = $sTable.TransactionID WHERE $sTable.ID = ?";

	$stmt = sqlsrv_query($storeConn, $qry, array($_SESSION['purchase']), array("Scrollable" => "static"));
	if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

	$transactionInfo = sqlsrv_fetch_array($stmt);

	$cols = preg_replace('/^Transactions.ID/', "ID", $cols);
    $cols = preg_replace('/^Transactions.TransactionID/', "TransactionID", $cols);
    $cols = preg_replace('/^PayPalTransactions.PayerID/', "PayerID", $cols);

	$and = "AND COLUMN_NAME NOT IN ('ID', 'TransactionsID', 'ItemID') OR TABLE_NAME = 'Products' AND COLUMN_NAME NOT IN ('ID', 'Price', 'Shipping', 'Download', 'StatusCode', 'Category')";
    
    $aCol = retrieveColumns('TransactionDetails', $and, $storeConn);
    $aCol[] = "Category";

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
									<?php if ($col!=="Checked"): ?>
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
										if($cols[$i] !== "Checked")
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
					<h3>Purchase Details</h3>
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
					<form action="purchases.php" method="post">
						<input type="hidden" name="purchaseID" value="<?= $_SESSION['purchase'] ?>">
						<label class="label" for="checked">Checkded:</label>
		                <label class="info" style="visibility:hidden"></label><br>
		                <input type="checkbox" class="searching" name="checked" id="checked"
		                	<?php if (!empty($transactionInfo['Checked'])) echo 'checked disabled readonly'; ?>><br><br>
            			<input class="submit" type="submit" name="update" value="Update">
            		</form>
				<?php else : ?>
			        <div class="memberContent">
			            <?php if (is_numeric($_SESSION['purchase'])) : ?>
			             	<h3>No information was found for purchaseID #<?= $_SESSION['purchase'] ?>.</h3>
			            <?php else : ?>
			            	<h3>purchaseID must be a number.</h3>
			            <?php endif ?>
			        </div>
		        <?php endif ?>
			</div>
		</div>
		
	</body>
</html>