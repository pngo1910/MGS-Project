<?php
  require('../../db/memberCheck.php');
  require('../../errorReporter.php');
  require('../../db/mgsConnection.php');
  require('../../retrieveColumns.php');
  require('../../db/storeConnection.php');

  if (isset($_GET['table']) && isset($_GET['id']) && isset($_GET['fileName'])) {
    list($tableName, $recordID, $fileName) = array($_GET['table'], $_GET['id'], $_GET['fileName']);
  } else {
    header("Location: /member/");
  }
  $tableName = "CemeteryRecords";

  $qryCem = "SELECT CemID FROM MGS.dbo.CemeteryRecords WHERE ID = ?";
  $stmtCem = sqlsrv_query($conn, $qryCem, array($recordID));
  if ($stmtCem === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
  $cemCode = sqlsrv_fetch_array($stmtCem)['CemID'];

  $qryCem = "SELECT ID, Name, Description, Price FROM CemeteryTranscriptions WHERE Name LIKE '%".$cemCode."%'";
  $stmtCem = sqlsrv_query($storeConn, $qryCem);
  if ($stmtCem === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
  while($row = sqlsrv_fetch_array($stmtCem)){
    $cemPrice = $row['Price'];
    $cemName = $row['Name'];
    $cemDescription = $row['Description'];
    $cemID = $row['ID'];
  }

  if(isset($_POST) && !empty($_POST) && $_POST != ''){
    $_SESSION['values'] = "";
    $_SESSION['values'] = array($cemID, $_POST['quantity']);
  }

  $qry = "SELECT DISTINCT Municipality FROM Cemeteries WHERE Municipality IS NOT NULL AND Municipality <> '' ORDER BY Municipality";
  $stmt = sqlsrv_query($conn, $qry);
  if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
  // builds the array for the JQry table
  $tableRows = array();
  while ($row = sqlsrv_fetch_array($stmt)) {
    $tableRows[] = json_encode(array(
        "<a href=/Store/store.php?test&amp;name=login&amp;municipality=" . $row['Municipality'] . " target=\"_blank\">".$row['Municipality']."</a>"
    ));
  }
?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
  <head>
    <meta charset="utf-8">

    <title>MGS Member</title>

    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->

    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables.css">
      <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables.min.css">
      <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables_themeroller.css">

      <script src="/DataTables-1.10.6/media/js/jquery.js"></script>
      <script src="/DataTables-1.10.6/media/js/jquery.dataTables.min.js"></script>
      <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
      <script src="/DataTables-1.10.6/media/js/jquery.dataTables.js"></script>
    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
    <script type="text/javascript">
    $(document).ready(function() {
        var calcDataTableHeight = function() {
          return $(window).height()*55/100;
        };

        var asInitVals = [];
        var tableData = [];

        <?php foreach ($tableRows as $key => $row) : ?>
          tableData.push(JSON.parse('<?= addslashes($row) ?>').map(function(text){return '' + text}));
        <?php endforeach; ?>

        var oTable = $('#example').dataTable({
          "scrollY": calcDataTableHeight(),
          "scrollCollapse": true,
          "bProcessing": true,
          "bPaginate": true,
          "bsortClasses": false,
          "sPaginationType": 'full_numbers',
          "aLengthMenu": [ 10, 25, 50, 100, 500 ],
          "bFilter": true,
          "bInput": true,
          "fnInitComplete": function() {
            $('.dataTables_scrollFoot').insertAfter($('.dataTables_scrollHead'));
          },
          "aaData": tableData,
          "oLanguage": { "sSearch": "Search all columns:" },
          "aoColumns": [{"sTitle": "Municipality"}]});

        $(window).resize(function () {
          var oSettings = oTable.fnSettings();
          oSettings.oScroll.sY = calcDataTableHeight();
          oTable.fnDraw();
        });

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
    });
    
  </script>
  <style>
    tfoot{
      display:table-header-group;
    }
  </style>
  </head>
  <body>
    <div id="resultsbackground">
      <div id="container" class="home">
        <div id="searchresults">
          <?php require('header.php'); ?>
          <h1>E-Store</h1>
          <div id="estoreLeft">
            <h2>Categories</h2>
            <table class="display" id="example">
              <thead>
              </thead>
              <tbody>
              </tbody>
              <tfoot>
                <tr>
                  <th><input type='text' name='search_Municipality' placeholder='Municipality' class='search_init' /></th>
                </tr>
              </tfoot>
            </table>
          </div>
          <div id="estoreRight">
          <table class="singleTable display dataTable">
            <thead>
              <tr>
                <th>Column</th>
                <th>Data</th>
              </tr>
            </thead>
            <tbody>
              <?php
                // get column names
                $and = "AND COLUMN_NAME NOT IN('TypeCode', 'StatusCode')";
                $columns = retrieveColumns($tableName, $and, $conn);
                $cols = implode(", ", $columns);
                $qry = "SELECT $cols FROM CemeteryRecords WHERE ID = ?";
                $stmt = sqlsrv_query($conn, $qry, array($recordID), array( "Scrollable" => 'static' ));
                if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

                while($row = sqlsrv_fetch_array($stmt)){
                    for ($i = 0; $i < count($columns); $i++) {
                        echo "<tr><td>".$columns[$i]."</td><td>".$row[$columns[$i]]."</td></tr>";
                    }
                }
              ?>
            </tbody>
          </table>
          <p><b>Name:</b> <?= $cemName ?></p>
          <p><b>Description:</b> <?= $cemDescription ?></p>
          <p><b>Price:</b> $<?= $cemPrice ?></p>
          <label for="quantity"><b>Add:</b></label>
          <form action="e-store.php?id=<?= $recordID ?>&amp;table=<?= $tableName ?>&amp;fileName=<?= $fileName ?>" method="POST">
          <input type="number" id="quantity" name="quantity" value="1" min="1" /><br /><br />
          <input type="submit" name="submit" class="addcart" value="Add to Cart" />
          </form>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>