<?php
  require('../../db/membershipAdminCheck.php');
  require('../../errorReporter.php');
  require('../../db/memberConnection.php');
  require('../../retrieveColumns.php');

  error_reporting(0);

  if (isset($_POST['tableName'])) {
    $tableName = $_POST['tableName'];
  } elseif (isset($_GET['tableName'])) {
    $tableName = $_GET['tableName'];
  }

  $tableName = validateTableName($tableName, $userConn);

  if ($tableName === null) {
    $_SESSION['error'] = 'Invalid table name.';
    die(header('location: /volunteer/memberships/tablesDashboard.php'));
  }

  $_SESSION['tableName'] = $tableName;
  $aCol = retrieveColumns($tableName, 0, $userConn);

  $aCol[] = 'Edit';
  $aCol[] = 'Delete';

  header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
  header('Cache-Control: post-check=0, pre-check=0', false);
  header('Pragma: no-cache');
?>

<!DOCTYPE HTML>
<html class="no-js">
	<head>
		<meta charset="utf-8">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">

		<?php header('X-UA-Compatible: IE=edge,chrome=1');?>

    <title>MGS Volunteer</title>
		<link rel="stylesheet" href="/css/normalize.css">
		<link rel="stylesheet" href="/css/main.css">

    <link rel="stylesheet" href="/css/demo_table.css">
    <link rel="stylesheet" type="text/css" href="/DataTables-1.10.6/media/css/jquery.dataTables.css">

    <script type="text/javascript" charset="utf8" src="/DataTables-1.10.6/media/js/jquery.js"></script>
    <script type="text/javascript" charset="utf8" src="/DataTables-1.10.6/media/js/jquery.dataTables.js"></script>

		<script type="text/javascript">
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
			//window.alert = function(){return null;};
			var asInitVals = new Array();

			var j_cols = new Array();
			<?php foreach ($aCol as $key => $value) : ?>
				j_cols.push({'sTitle' : '<?= $value ?>'});
      <?php endforeach; ?>

        $(document).ready(function() {
          var calcDataTableHeight = function() {
            return $(window).height()*55/100;
          };

            window.alert = function(){return null;};

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
                "oLanguage": {
                    "sSearch": "Search all columns:"
                }
            } );

            $(window).resize(function () {
              var oSettings = oTable.fnSettings();
              oSettings.oScroll.sY = calcDataTableHeight();
              oTable.fnDraw();
            });

            $("tfoot input").keyup( function () {
                /* Filter on the column (the index) of this element */
                oTable.fnFilter(this.value, $("tfoot input").index(this));
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
				<h2>Table : <?= $tableName ?></h2>
				<table cellpadding="0" cellspacing="0" border="0" class="display" id="example">
					 <tfoot>
            <tr>
              <?php for ($i = 0; $i < count($aCol)-2; $i++) : $col = $aCol[$i] ?>
                <th><input type="text" name="search_<?= $col ?>" value="<?= $col ?>" class="search_init"></th>
              <?php endfor ?>
              <th></th>
              <th></th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </body>
</html>
