<?php
	require('../../db/adminCheck.php');
	require('../../db/adminConnection.php');
	require('../../retrieveColumns.php');

	error_reporting(0);

	$tableName = $_GET['table'];
	if($tableName != 'products' && $tableName != 'category' && $tableName != 'cemeterytranscriptions')
		header('location: /admin/store/');

	$_SESSION['storetable'] = $tableName;
	// if($tableName === 'products'){
	// 	$and = "AND COLUMN_NAME NOT IN('Category') OR TABLE_NAME = 'Category' AND COLUMN_NAME IN ('Category') ORDER BY TABLE_NAME DESC;";
	// 	$aCol = retrieveColumns($tableName, $and, $conn);
	// }else{
		$aCol = retrieveColumns($tableName, 0, $conn);
	//}
    //	add two extra columns for edit and delete
    $aCol[] = 'Edit';
    $aCol[] = 'Delete';

    header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
	header( 'Cache-Control: post-check=0, pre-check=0', false );
	header( 'Pragma: no-cache' );
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
					window.location.href = 'delete.php?tablename='+table+'&id='+id+'&delete=true';
					return true;
				}
				else{
					window.location.href = 'delete.php?tablename='+table+'&id='+id;
					return false;
				}
			}

			var asInitVals = new Array();

			var j_cols = new Array();
			var hash_cols = {};
			<?php foreach ($aCol as $key => $value) : ?>;
				hash_cols = {'sTitle' : '<?php echo ($value); ?>'};
				j_cols.push(hash_cols);
    		<?php endforeach; ?>

        $(document).ready(function() {
            window.alert = function(){return null;};
            //FireFox
            var height = 23;
            //IE & Chrome
		    if (navigator.userAgent.match(/msie/i) || navigator.userAgent.match(/trident/i) || navigator.userAgent.match(/chrome/i) ){
			    height = 100;
			}

            var calcDataTableHeight = function() {
                return $(window).height()*55/height;
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
                "sAjaxSource": "query.php",
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
				<h2>Table : <?php echo $tableName; ?></h2>
				<table cellpadding="0" cellspacing="0" border="0" class="display" id="example">
					<thead>
					</thead>
					<tbody>
					</tbody>
					<tfoot>
						<tr>
						<?php
                            foreach($aCol as $col_data){
                            	if($col_data != "Edit" && $col_data != "Delete")
                                	echo "<th><input type='text' name='search_" . $col_data . "' value='" . $col_data . "' class='search_init' /></th>";
                            }
                        ?>
                        <th><input type='hidden'></th>
                        <th><input type='hidden'></th>
                    </tr>
					</tfoot>
				</table>
				</div>
			</div>
		</div>
	</body>
</html>
