<?php
	require('../../db/adminCheck.php');
	require('../../db/memberConnection.php');
	require('../../retrieveColumns.php');
	
	if(isset($_POST['update']) && isset($_POST['checked']))
	{
		$sql = "UPDATE Transactions SET Checked = '1' WHERE ID = ?";
        $stmt = sqlsrv_query( $userConn, $sql, array($_POST['purchaseID']), array( "Scrollable" => 'static' ));

        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	}
	else if(strpos($_SERVER['HTTP_REFERER'], 'searchResearch.php') !== false)
	{
		$_SESSION['post'] = $_POST;
	}
	$and = "OR TABLE_NAME = 'PayPalTransactions' AND COLUMN_NAME IN('PayerID') ORDER BY TABLE_NAME DESC";
	$aCol = retrieveColumns('Transactions', $and, $userConn);

	//add new checkbox column
	array_unshift($aCol,'Print');

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
		            "sAjaxSource": "researchQuery.php",	
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
					<h2>store purchases</h2>
					<p class="errorColor">
			          <?php if (isset($_SESSION['error'])) : ?>
			            <?= $_SESSION['error'] ?>
			            <?php unset($_SESSION['error']) ?>
			          <?php endif ?>
			        </p>
				</div>
				<form action="researchExport.php" method="POST">
					<input type="submit" name="export_all" value="Export All"/>
			        <input type="submit" name="export_selected" value="Export Selected">
			        <button type="button" id="btn1">Select All</button>
			        <button type="button" id="btn2">Unselect All</button>
			        <br/>
					<table class="compact display" id="example">
						<thead>
						</thead>
						<tfoot>
							<tr>
								<?php
					                foreach($aCol as $col_data){
					                	if($col_data != "View" && $col_data != "Print"){
					                		echo "<th><input type='text' name='search_" . $col_data . "' placeholder=\"" . $col_data . "\" class='search_init' /></th>";
					                	}
					                    else{
					                    	echo "<th></th>";
					                    }
					                }                            
					            ?>
					        </tr>
						</tfoot>
						<tbody>
						</tbody>
					</table>
				</form>
			</div>
		</div>
	</body>
</html>