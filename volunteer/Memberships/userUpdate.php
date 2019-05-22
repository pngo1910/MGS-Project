<?php
	require('../../db/membershipAdminCheck.php');
	require('../../errorReporter.php');
	require('../../db/memberConnection.php');

  $userInfo = array();
  $sql = "SELECT membership.membernum, type.name as memtype, accessLevel.name as access,
    generations.name as gen, yearjoined, expiry, info.firstname, info.lastname, info.address, info.city,
    info.province, info.countrycode, info.phone, info.email, members.username FROM membership
    LEFT JOIN members ON members.membernum = membership.membernum
    LEFT JOIN typeofmember as type ON membership.typeofmember = type.id
    LEFT JOIN accesslevel ON members.accesslevel = accesslevel.id
    LEFT JOIN generations ON membership.generations = generations.id
    LEFT JOIN memberinfo as info ON membership.membernum = info.membernum
    WHERE membership.membernum IN (SELECT DISTINCT membernum FROM Changes)";

  $stmt = sqlsrv_query($userConn, $sql, array(), array('Scrollable' => 'static'));
  if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

  while ($row = sqlsrv_fetch_array($stmt)) {
    $aRow = array();
    $changes = array();
    $membernum = $row['membernum'];
    $gen = $row['gen'];
    $firstName = $row['firstname'];
    $lastName = $row['lastname'];
    $address = $row['address'];
    $city = $row['city'];
    $province = $row['province'];
    $countrycode = $row['countrycode'];
    $phone = $row['phone'];
    $email = $row['email'];
    $username = $row['username'];
    $id = array();

    $sqlChanges = "SELECT ID, Change FROM Changes WHERE membernum = ?";
    $stmtChanges = sqlsrv_query($userConn, $sqlChanges, array($membernum),array('Scrollable' => 'static'));
    if ($stmtChanges === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    while ($rowChanges = sqlsrv_fetch_array($stmtChanges)){
      Switch($rowChanges['Change']){
        case 'username':
          $username = sprintf("<span style='color:Red;'>%s</span>", $username);
          break;
        case 'generations':
          $gen = sprintf("<span style='color:Red;'>%s</span>", $gen);
          break;
        case 'firstName':
          $firstName = sprintf("<span style='color:Red;'>%s</span>", $firstName);
          break;
        case 'lastName':
          $lastName = sprintf("<span style='color:Red;'>%s</span>", $lastName);
          break;
        case 'address':
          $address = sprintf("<span style='color:Red;'>%s</span>", $address);
          break;
        case 'city':
          $city = sprintf("<span style='color:Red;'>%s</span>", $city);
          break;
        case 'province':
          $province = sprintf("<span style='color:Red;'>%s</span>", $province);
          break;
        case 'countryCode':
          $countrycode = sprintf("<span style='color:Red;'>%s</span>", $countrycode);
          break;
        case 'phone':
          $phone = sprintf("<span style='color:Red;'>%s</span>", $phone);
          break;
        case 'email':
          $email = sprintf("<span style='color:Red;'>%s</span>", $email);
          break;
      }

      $id[] = $rowChanges['ID'];
    }

    $idStr = implode(' ', $id);

    $aRow[] = $membernum;
    $aRow[] = $row['memtype'];
    $aRow[] = $row['access'];
    $aRow[] = $gen;
    $aRow[] = isset($row['yearjoined'])? $row['yearjoined']->format('Y-m-d'): null;
    $aRow[] = isset($row['expiry'])? $row['expiry']->format('Y-m-d'): null;
    $aRow[] = $firstName;
    $aRow[] = $lastName;
    $aRow[] = $address;
    $aRow[] = $city;
    $aRow[] = $province;
    $aRow[] = $countrycode;
    $aRow[] = $phone;
    $aRow[] = $email;
    $aRow[] = $username;
    $aRow[] = "<a href=\"javascript:confirmDelete('$idStr')\">Delete</a>";
    $aRow[] = "<a href='userEdit.php?user=$membernum'>Edit</a>";

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

    <script type="text/javascript" charset="utf-8" src="/DataTables-1.10.6/media/js/jquery.js"></script>
    <script type="text/javascript" charset="utf-8" src="/DataTables-1.10.6/media/js/jquery.dataTables.js"></script>
    <script type="text/javascript" charset="utf-8" src="/DataTables-1.10.6/extensions/ColReorder/js/dataTables.colReorder.js"></script>
    <script type="text/javascript" charset="utf-8" src="/DataTables-1.10.6/extensions/ColVis/js/dataTables.colVis.js"></script>
    <script type="text/javascript" charset="utf-8" src="/DataTables-1.10.6/extensions/TableTools/js/dataTables.tableTools.js"></script>
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
      function confirmDelete(id) {
        var confirmDelete = confirm('Are you sure, you want to delete this entry?');

        if (confirmDelete == true) {
            window.location.href = 'changeDelete.php?id=' + id;
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
                        {"sTitle": "Member Type"},
                        {"sTitle": "Access Level"},
                        {"sTitle": "Generations"},
                        {"sTitle": "Year Joined"},
                        {"sTitle": "Expiry"},
                        {"sTitle": "First Name"},
                        {"sTitle": "Last Name"},
                        {"sTitle": "Address"},
                        {"sTitle": "City"},
                        {"sTitle": "Province"},
                        {"sTitle": "CountryCode"},
                        {"sTitle": "Phone"},
                        {"sTitle": "Email"},
                        {"sTitle": "User Name"},
                        {"sTitle": "Delete"},
                        {"sTitle": "Edit"}]});

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
        <h4>Updated items are hilighted in red. "Delete" deletes the record of the change, not the membership.</h4>
      </div>
      <div id="content">
        <table class="display" id="example" cellspacing="0" width="100%">
          <thead></thead>
          <tfoot>
            <th><input type='text' value='Member Number' class='search_init' /></th>
            <th><input type='text' value='Member Type' class='search_init' /></th>
            <th><input type='text' value='Access Level' class='search_init' /></th>
            <th><input type='text' value='Generations' class='search_init' /></th>
            <th><input type='text' value='Year Joined' class='search_init' /></th>
            <th><input type='text' value='Expiry' class='search_init' /></th>
            <th><input type='text' value='First Name' class='search_init' /></th>
            <th><input type='text' value='Last Name' class='search_init' /></th>
            <th><input type='text' value='Address' class='search_init' /></th>
            <th><input type='text' value='City' class='search_init' /></th>
            <th><input type='text' value='Province' class='search_init' /></th>
            <th><input type='text' value='CountryCode' class='search_init' /></th>
            <th><input type='text' value='Phone' class='search_init' /></th>
            <th><input type='text' value='E-mail' class='search_init' /></th>
            <th><input type='text' value='Username' class='search_init' /></th>
            <th></th>
            <th></th>
          </tfoot>
        </table>
      </div>
    </div>
  </body>
</html>
