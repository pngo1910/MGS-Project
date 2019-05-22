<?php
  require('../../db/adminCheck.php');
  require('../../errorReporter.php');
  require('../../db/adminConnection.php');
  require('../../retrieveColumns.php');

  error_reporting(0);

  if (isset($_POST['tableName']))
    $tableName = $_POST['tableName'];

  $_SESSION['tableName'] = $tableName;
  $_SESSION['where'] = "StatusCode = 'UPDATED'";

  $aCol = retrieveColumns($tableName, 0, $conn);
  array_unshift($aCol, '');

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

    <title>MGS Administrator</title>

    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/main.css">

    <link rel="stylesheet" href="/css/demo_table.css">
    <link rel="stylesheet" type="text/css" href="/DataTables-1.10.6/media/css/jquery.dataTables.css">

    <script type="text/javascript" charset="utf8" src="/DataTables-1.10.6/media/js/jquery.js"></script>
    <script type="text/javascript" charset="utf8" src="/DataTables-1.10.6/media/js/jquery.dataTables.js"></script>
        <!-- a script for select all and unselect all buttons -->
    <script type="text/javascript" charset="utf8" src="/DataTables-1.10.6/media/js/test.js"></script>

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
      //  array for tfoot inputs
      var asInitVals = new Array();

      //  array for column names
      var j_cols = new Array();
      <?php foreach ($aCol as $key => $value) : ?>;
        j_cols.push({'sTitle' : '<?php echo ($value); ?>'});
      <?php endforeach; ?>

        $(document).ready(function() {
          var calcDataTableHeight = function() {
            return $(window).height()*55/100;
          };

            window.alert = function(){return null;};
            var tableData = new Array([]);
            var rowNum = 0; //current row number

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

            $(window).resize(function () {
              var oSettings = oTable.fnSettings();
              oSettings.oScroll.sY = calcDataTableHeight();
              oTable.fnDraw();
            });

            $("tfoot input").keyup( function () {
                /* Filter on the column (the index) of this element */
                oTable.fnFilter( this.value, $("tfoot input").index(this)-1 );
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
  <div id="resultsbackground">
    <div id="container" class="home">
    <div id="searchresults">
      <?php require('header.php'); ?>
    </div>
  </div>
      <h3>Updated <?= $tableName ?> Records</h3>
      <form action="update.php?table=<?php echo($_POST['tableName']);?>" method="POST">
        <input type="submit" name="export_all_updated" value="Export All"/>
        <input type="submit" name="export_selected_updated" value="Export Selected">
        <button type="button" id="btn1">Select All</button>
        <button type="button" id="btn2">Unselect All</button>
        <br/>
        <input type="submit" name="all" value="Update All" />
        <input type="submit" name="selected" value="Update Selected" />
        <input type="submit" name="revert_all" value="Revert All" />
        <input type="submit" name="revert_selected" value="Revert Selected" />
        

        <table class="display" id="example">
          <thead>
          </thead>
          <tfoot>
            <tr>
              <th><input type='hidden'></th>

              <?php for ($i = 1; $i < count($aCol); $i++) : ?>
                <th><input type="text" name="<?= $aCol[$i] ?>" value="<?= $aCol[$i] ?>" class="search_init"></th>
              <?php endfor ?>
            </tr>
          </tfoot>
        </table>
      </form>
    </div>
  </body>
</html>
