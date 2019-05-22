<?php
	require('../../db/adminCheck.php');
	require('../../db/storeConnection.php');
	require('../../retrieveColumns.php');
	error_reporting(0);
	$_SESSION['post'] = $_POST;
	$and = "OR TABLE_NAME = 'PayPalTransactions' AND COLUMN_NAME IN('PayerID') OR TABLE_NAME = 'PayerDetails' AND COLUMN_NAME IN('FirstName', 'LastName') ORDER BY TABLE_NAME DESC";
	$aCol = retrieveColumns('Transactions', $and, $storeConn);
	$aCol[] = "View";
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
		            "sAjaxSource": "transactionsQuery.php",	
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
		<div id="resultsbackground">
	    	<div id="container" class="home">
	    		<?php require('header.php'); ?>
				<div id="head">
					<h2>Transactions</h2>
				</div>
				<table class="display" id="example">
					<thead>
					</thead>
					<tfoot>
						<tr>
							<?php
				                foreach($aCol as $col_data){
				                	if($col_data != "View")
				                    	echo "<th><input type='text' name='search_" . $col_data . "' placeholder=\"" . $col_data . "\" class='search_init' /></th>";
				                }                            
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