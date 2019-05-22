<?php
	require('../../db/adminCheck.php');
	require('../../db/adminConnection.php');
	//require('../../retrieveColumns.php');
    require('../../errorReporter.php');

	//	some errors show up, but dont affect our page
	//	hide those errors
	error_reporting(0);
	if(!isset($_SESSION['message'])) $_SESSION['message'] = '';
?>
<html class="no-js"> <!--<![endif]-->
	<head>
		<meta charset="utf-8">
		<?php header('X-UA-Compatible: IE=edge,chrome=1');?>
		<title> Manitoba Genealogical Society</title>
		<link rel="stylesheet" href="/css/normalize.css">
		<link rel="stylesheet" href="/css/main.css">
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width">
		<link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables.css">
        <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables_themeroller.css">

        <script src="/DataTables-1.10.6/media/js/jquery.js"></script>
        <script src="/DataTables-1.10.6/media/js/jquery.dataTables.min.js"></script>
        <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
        <script src="/DataTables-1.10.6/media/js/jquery.dataTables.js"></script>
    	<!--<script class="jsbin" src="http://datatables.net/download/build/jquery.dataTables.nightly.js"></script>-->
<?php

	//	echo json_encode echoes out the data on the page
	//	to hide that we put the file in div and hide it in css
    echo "<div id='hide'>";
		require('payperviewQuery.php');
	echo "</div>";

	if (!isset($_SESSION['output'])) {
		$_SESSION['output'] = '';
	}

	if ($_SESSION['output'] == '') {
		$_SESSION['output'] = "Output was not captured from script";
	}

	$tableName = 'payperview';

	//	get all the columns in aCol array
	//	to use in server-side datatables
	$aCol = retrieveColumns($tableName, 0, $conn);
    //	add two extra columns for edit and delete
    $aCol[] = 'Edit';
    $aCol[] = 'Delete';

    header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
	header( 'Cache-Control: post-check=0, pre-check=0', false );
	header( 'Pragma: no-cache' );
?>
		<script type="text/javascript">
			(function()
			{
			  if( window.localStorage )
			  {
			    if( !localStorage.getItem( 'firstLoad' ) )
			    {
			      localStorage[ 'firstLoad' ] = true;
			      //window.location.reload();
			    }
			    else
			      localStorage.removeItem( 'firstLoad' );
			  }
			})();
			function confirmDelete(table, id){
				var confirmDelete = confirm('Are you sure, you want to delete this entry?');

				if(confirmDelete == true){
					//alert(table);
					//alert('delete.php?tablename=table&id=id&delete=true');
					window.location.href = 'payperviewDelete.php?tablename='+table+'&id='+id+'&delete=true';
					return true;
				}
				else{
					window.location.href = 'payperviewDelete.php?tablename='+table+'&id='+id;
					return false;
				}
			}
			window.alert = function(){return null;};
			var asInitVals = new Array();

			var j_cols = new Array();
			var hash_cols = {};
			<?php foreach ($aCol as $key => $value) : ?>;
				hash_cols = {'sTitle' : '<?php echo ($value); ?>'};
				j_cols.push(hash_cols);
    		<?php endforeach; ?>

        $(document).ready(function() {
            window.alert = function(){return null;};

            var tableData = new Array([]);
            var rowNum = 0; //current row number


                   //for each row in the result set
                <?php foreach ($output['aaData'] as $key => $rows) : ?>;
                    //we go threw all data per row
                    var rows = new Array();
                    //rows.push('<?php echo addslashes(json_encode($rows)) ?>');
                    rows = '<?php echo addslashes(json_encode($rows)) ?>'.match(/(".*?"|[^",\s]+)(?=\s*,|\s*$)/g);//replace(/,null|,"/g,'\",\"').split("\",\"");//.replace("\",\"", ",").split(",");

                    for(var i=1; i<rows.length; i++){
                        rows[i] = rows[i].replace(/\[|\]|"/g,'');
                        tableData[rowNum].push(rows[i]);
                    }

                    tableData.push([]);
                //  alert(tableData[rowNum]);
                    rowNum++; //next row
                <?php endforeach; ?>;

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
                "sAjaxSource": "payperviewQuery.php",
                "oLanguage": { "sSearch": "Search all columns:" },
                "fnInitComplete": function() {
                  $('.dataTables_scrollFoot').insertAfter($('.dataTables_scrollHead'));
                }
            } );

            $("tfoot input").keyup( function () {
                /* Filter on the column (the index) of this element */
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
    <!-- css for tfoot inputs to show as thead -->
	<style>
		tfoot{
			display: table-header-group;
		}
	</style>
	</head>
	<body>
		<div class = 'adminTables'>
		   <div id="resultsbackground_table">
				<div id="container" class="home">
					<div id="searchresults">
            <?php require('header.php'); ?>
					</div>
				</div>
				<div id="content">
				<p class="successColor"><?= $_SESSION['message'] ?></p>
				<?php $_SESSION['message'] = ''; ?>
				<h2>Table : <?php echo $tableName; ?></h2>
				<table cellpadding="0" cellspacing="0" border="0" class="display" id="example">
					<thead>
					</thead>
					<tbody>
					</tbody>
					<tfoot>
						<tr>
						<?php
							//	retrieve columns to make textboxes to search individual columns
							$columns = retrieveColumns($tableName, 0, $conn);

                            foreach($columns as $col_data)
                                echo "<th><input type='text' name='search_" . $col_data . "' value='" . $col_data . "' class='search_init' /></th>";
                        ?>
                    </tr>
					</tfoot>
				</table>
				</div>
				<!--<p><?php echo $_SESSION['output']; ?></p>-->
			</div>
		</div>
	</body>
</html>
