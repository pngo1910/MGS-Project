<?php
  require('../../db/membershipAdminCheck.php');
  require('../../errorReporter.php');
  require('../../db/memberConnection.php');

  $whereGen = array();
  $_SESSION['mailchecked'] = true;
  $_SESSION['emailchecked'] = true;
  $_SESSION['printchecked'] = true;
  $subset = isset($_POST['subset'])? $_POST['subset']: null;

  if (isset($_POST['submit'])){
    $_SESSION['mailchecked'] = isset($_POST['mail'])? true: false;
    $_SESSION['emailchecked'] = isset($_POST['email'])? true: false;
    $_SESSION['printchecked'] = isset($_POST['print'])? true: false;
  }

  if ($_SESSION['mailchecked']) $whereGen[] = "membership.generations = 1";
  if ($_SESSION['emailchecked']) $whereGen[] = "membership.generations = 2";
  if ($_SESSION['printchecked']) $whereGen[] = "membership.generations = 3";

  $whereGen = implode(" OR ", $whereGen);

  switch ($subset) {
    case 'all':
      $_SESSION['checked'] = 'all';
      $whereSubset = '';
      break;

    case 'expired':
      $whereSubset = "membership.expiry <= GETDATE()";
      $_SESSION['checked'] = 'expired';
      break;

    case 'mani':
      $whereSubset = "members.membernum IS NOT NULL";
      $_SESSION['checked'] = 'mani';
      break;

    default:
      $whereSubset = "membership.expiry > GETDATE()";
      $_SESSION['checked'] = 'active';
  }

  if ($whereGen != ''){
    if ($whereSubset != ''){
      $where = '('.$whereGen.') AND '.$whereSubset;
    } else {
      $where = $whereGen;
    }
  } elseif ($whereSubset != ''){
    $where = $whereSubset;
  } else {
    $where = '';
  }

  $userInfo = array();
  $sql = "SELECT membership.membernum, generations.name as gen, info.firstname, info.lastname,
    info.address, info.city, info.province, info.countrycode, info.email FROM membership
    LEFT JOIN generations ON membership.generations = generations.id
    LEFT JOIN memberinfo as info ON membership.membernum = info.membernum
    LEFT JOIN members ON membership.membernum = members.membernum";

  if ($where != ''){
    $sql .= " WHERE ";
    $sql .= $where;
  }

  $stmt = sqlsrv_query($userConn, $sql, array(), array('Scrollable' => 'static'));
  if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

  while ($row = sqlsrv_fetch_array($stmt)) {
    $aRow = array();
    $membernum = $row['membernum'];

    $aRow[] = $membernum;
    $aRow[] = $row['gen'];
    $aRow[] = $row['firstname'];
    $aRow[] = $row['lastname'];
    if ($_SESSION['mailchecked']){
      $aRow[] = $row['address'];
      $aRow[] = $row['city'];
      $aRow[] = $row['province'];
      $aRow[] = $row['countrycode'];
    }
    if ($_SESSION['emailchecked']){
      $aRow[] = $row['email'];
    }
    $userInfo[] = json_encode($aRow);
  }
?>
<!DOCTYPE HTML>
<html class="no-js">
  <head>
    <meta charset="utf-8">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">

    <?php header('X-UA-Compatible: IE=edge,chrome=1'); ?>

    <title>MGS Administrator</title>
    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/demo_table.css">
<!--
    <link rel="stylesheet" type="text/css" href="/DataTables-1.10.6/media/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="/DataTables-1.10.6/extensions/ColReorder/css/dataTables.colReorder.css">
    <link rel="stylesheet" type="text/css" href="/DataTables-1.10.6/extensions/ColVis/css/dataTables.colVis.css">
    <link rel="stylesheet" type="text/css" href="/DataTables-1.10.6/extensions/TableTools/css/dataTables.tableTools.css">

    <script type="text/javascript" charset="utf8" src="/DataTables-1.10.6/media/js/jquery.js"></script>
    <script type="text/javascript" charset="utf8" src="/DataTables-1.10.6/media/js/jquery.dataTables.js"></script>
    <script type="text/javascript" charset="utf8" src="/DataTables-1.10.6/extensions/ColReorder/js/dataTables.colReorder.js"></script>
    <script type="text/javascript" charset="utf8" src="/DataTables-1.10.6/extensions/ColVis/js/dataTables.colVis.js"></script>
    <script type="text/javascript" charset="utf8" src="/DataTables-1.10.6/extensions/TableTools/js/dataTables.tableTools.js"></script>
-->

<link rel="stylesheet" type="text/css" href="/datatables-1.10.16/DataTables-1.10.16/css/jquery.dataTables.css"/>
<link rel="stylesheet" type="text/css" href="/datatables-1.10.16/Buttons-1.5.1/css/buttons.dataTables.css"/>
<link rel="stylesheet" type="text/css" href="/datatables-1.10.16/ColReorder-1.4.1/css/colReorder.dataTables.css"/>
 
<script type="text/javascript" src="/datatables-1.10.16/jQuery-3.2.1/jquery-3.2.1.js"></script>
<script type="text/javascript" src="/datatables-1.10.16/JSZip-2.5.0/jszip.js"></script>
<script type="text/javascript" src="/datatables-1.10.16/pdfmake-0.1.32/pdfmake.js"></script>
<script type="text/javascript" src="/datatables-1.10.16/pdfmake-0.1.32/vfs_fonts.js"></script>
<script type="text/javascript" src="/datatables-1.10.16/DataTables-1.10.16/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="/datatables-1.10.16/Buttons-1.5.1/js/dataTables.buttons.js"></script>
<script type="text/javascript" src="/datatables-1.10.16/Buttons-1.5.1/js/buttons.colVis.js"></script>
<script type="text/javascript" src="/datatables-1.10.16/Buttons-1.5.1/js/buttons.html5.js"></script>
<script type="text/javascript" src="/datatables-1.10.16/Buttons-1.5.1/js/buttons.print.js"></script>
<script type="text/javascript" src="/datatables-1.10.16/ColReorder-1.4.1/js/dataTables.colReorder.js"></script>


<style>

#resultsbackground{
  z-index: unset;

}
</style>
    <script>
      function confirmDelete(user, membernum) {
        var confirmDelete = confirm('Are you sure, you want to delete this entry?');

        if (confirmDelete == true) {
          window.location.href = 'userDelete.php?username=' + user +
                                 '&membernum=' + membernum;
          return true;
        }
        else {
          window.href.location = window.href.location;
          return false;
        }
      }

      $(document).ready(function() {

        var calcDataTableHeight = function() {
          return $(window).height()*0.4;
        };

        var asInitVals = [];
        var tableData = [];

        <?php foreach ($userInfo as $key => $row) : ?>
          tableData.push(JSON.parse('<?= addslashes($row) ?>').map(function(text){return '' + text}));
        <?php endforeach; ?>

        var oTable = $('#example').dataTable({
          //"dom": 'TC<"clear">Rlfrtip',
          //"tableTools": {
          //  "sSwfPath": "/DataTables-1.10.6/extensions/TableTools/swf/copy_csv_xls_pdf.swf"
          //},
           "dom": 'RBlfrtip',
          //"dom": 'TC<"clear">Rlfrtip',
      "buttons": ['copy', 
            'excel', 
            'csv', 
            'pdf', 
            'print',  
            {
                  extend: 'colvis',
                  text: 'Show/Hide Columns'
                }
               ],
          "scrollY": calcDataTableHeight(),
          "scrollCollapse": true,
          "scrollX": true,
          "bProcessing": true,
          "bPaginate": true,
          "bsortClasses": false,
          "sPaginationType": 'full_numbers',
          "aLengthMenu": [ 10, 25, 50, 100, 500 ],
          "bFilter": true,
          "bInput" : true,
          "fnInitComplete": function() {
            $('.dataTables_scrollFoot').insertAfter($('.dataTables_scrollHead'));
          },
          "aaData": tableData,
          "oLanguage": {"sSearch": "Search all columns:"},
          "aoColumns": [{"sTitle": "Member Number"},
                        {"sTitle": "Generations"},
                        {"sTitle": "First Name"},
                        {"sTitle": "Last Name"}
                        <?php if ($_SESSION['mailchecked']): ?>
                          ,{"sTitle": "Address"},
                          {"sTitle": "City"},
                          {"sTitle": "Province"},
                          {"sTitle": "CountryCode"}
                        <?php endif;
                        if ($_SESSION['emailchecked']): ?>
                          ,{"sTitle": "Email"}
                        <?php endif; ?>]});

        $(window).resize(function () {
          var oSettings = oTable.fnSettings();
          oSettings.oScroll.sY = calcDataTableHeight();
          oTable.fnDraw();
        });

        $("tfoot input").keyup(function() {
          oTable.fnFilter(this.value, $("tfoot input").index(this));
        });

        $("tfoot input").each(function(i) {
          asInitVals[i] = this.value;
        });

        $("tfoot input").focus(function() {
          if (this.className == "search_init") {
            this.className = "";
            this.value = "";
          }
        });

        $("tfoot input").blur(function(i) {
          if (this.value == "") {
            this.className = "search_init";
            this.value = asInitVals[$("tfoot input").index(this)];
          }
        });
      });
    </script>
    <style>
      tfoot {display: table-header-group;}
    </style>
  </head>
  <body>
    <div id="resultsbackground">
      <div id="container" class="home">
        <div id="searchresults">
          <?php require('header.php'); ?>
        </div>
        <h2> Mailing List </h2>
        <form id="subset" action="mailinglist.php" method="post">
          <div id="filter" style="float:left;margin-right:50px;">
            <h4>Filter Results</h4>
            <input type="radio" id="all" name="subset" value="all"
              <?php if (!isset($_SESSION['checked']) || $_SESSION['checked'] == 'all'): ?>checked<?php endif; ?>>
            <label for="all" style="display:inline">All</label>

            <input type="radio" id="active" name="subset" value="active"
              <?php if (isset($_SESSION['checked']) && $_SESSION['checked'] == 'active'): ?>checked<?php endif; ?>>
            <label for="active" style="display:inline">Active</label>

            <input type="radio" id="expired" name="subset" value="expired"
              <?php if (isset($_SESSION['checked']) && $_SESSION['checked'] == 'expired'): ?>checked<?php endif; ?>>
            <label for="expired" style="display:inline">Expired</label>

            <input type="radio" id="mani" name="subset" value="mani"
              <?php if (isset($_SESSION['checked']) && $_SESSION['checked'] == 'mani'): ?>checked<?php endif; ?>>
            <label for="mani" style="display:inline">Mani</label>
          </div>
          <div id="generationsfilter" style="float:left;">
            <h4>Method of Receiving Generations</h4>
            <input type="checkbox" class="filter" name="mail" value="mail" <?php if (isset($_SESSION['mailchecked']) && $_SESSION['mailchecked']): ?>checked<?php endif; ?>>
            <label for="mail" style="display:inline">Mailed</label>
            <input type="checkbox" class="filter" name="email" value="email" <?php if (isset($_SESSION['emailchecked']) && $_SESSION['emailchecked']): ?>checked<?php endif; ?>>
            <label for="email" style="display:inline">Emailed</label>
            <input type="checkbox" class="filter" name="print" value="print" <?php if (isset($_SESSION['printchecked']) && $_SESSION['printchecked']): ?>checked<?php endif; ?>>
            <label for="print" style="display:inline">Printed</label>
            <input type="submit" name="submit" value="GO">
          </div>
        </form>
      </div>
      <div id="content">
        <table class="display" id="example" cellspacing="0" width="100%">
          <thead></thead>
          <tfoot>
            <th><input type='text' value='Member Number' class='search_init' /></th>
            <th><input type='text' value='Generations' class='search_init' /></th>
            <th><input type='text' value='First Name' class='search_init' /></th>
            <th><input type='text' value='Last Name' class='search_init' /></th>
            <?php if ($_SESSION['mailchecked']): ?>
              <th><input type='text' value='Address' class='search_init' /></th>
              <th><input type='text' value='City' class='search_init' /></th>
              <th><input type='text' value='Province' class='search_init' /></th>
              <th><input type='text' value='CountryCode' class='search_init' /></th>
            <?php endif;
            if ($_SESSION['emailchecked']): ?>
              <th><input type='text' value='E-mail' class='search_init' /></th>
            <?php endif; ?>
          </tfoot>
        </table>
      </div>
    </div>
  </body>
</html>
