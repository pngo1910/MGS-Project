<?php
  require('../../db/membershipAdminCheck.php');
  require('../../errorReporter.php');
  require('../../db/memberConnection.php');

  //$downloadcsv = isset($_POST['downloadcsv']);
  $subset = isset($_POST['subset'])? $_POST['subset']: null;
  $branch = isset($_POST['branch'])? $_POST['branch']: null;
  $where = array();

  switch ($subset) {
    case 'all':
      $_SESSION['checked'] = 'all';
      break;

    case 'expired':
      $where[] = "membership.expiry <= GETDATE()";
      $_SESSION['checked'] = 'expired';
      break;

    case 'mani':
      $where[] = "members.membernum IS NOT NULL";
      $_SESSION['checked'] = 'mani';
      break;

    default:
      $where[] = "membership.expiry > GETDATE()";
      $_SESSION['checked'] = 'active';
  }

  /*add for branch expiry*/
  $sTitleForBranch = array();
  $searchInitForBranch = "";
  /*add for branch expiry*/

  switch ($branch) {
    case 'bplains':
      $where[] = "Membership.membernum IN (SELECT MemberID FROM BranchMembership WHERE BranchID = 1)";
      $_SESSION['branchchecked'] = 'bplains';
      /*add for branch expiry*/
      $whereStrForBranch = "AND Branch.ID = 1";
      $sTitleForBranch[] = "Beautiful Plains";
      $searchInitForBranch = "<th><input type='text' value='BPExpiry' class='search_init' /></th>";
      /*add for branch expiry*/
      break;

    case 'dauphin':
      $where[] = "Membership.membernum IN (SELECT MemberID FROM BranchMembership WHERE BranchID = 2)";
      $_SESSION['branchchecked'] = 'dauphin';
      /*add for branch expiry*/
      $whereStrForBranch = "AND Branch.ID = 2";
      $sTitleForBranch[] = "Dauphin";
      $searchInitForBranch = "<th><input type='text' value='DpnExpiry' class='search_init' /></th>";
      /*add for branch expiry*/
      break;

    case 'southwest':
      $where[] = "Membership.membernum IN (SELECT MemberID FROM BranchMembership WHERE BranchID = 3)";
      $_SESSION['branchchecked'] = 'southwest';
      /*add for branch expiry*/
      $whereStrForBranch = "AND Branch.ID = 3";
      $sTitleForBranch[] = "Southwest";
      $searchInitForBranch = "<th><input type='text' value='SwExpiry' class='search_init' /></th>";
      /*add for branch expiry*/
      break;

    case 'svalley':
      $where[] = "Membership.membernum IN (SELECT MemberID FROM BranchMembership WHERE BranchID = 4)";
      $_SESSION['branchchecked'] = 'svalley';
      break;

    case 'winnipeg':
      $where[] = "Membership.membernum IN (SELECT MemberID FROM BranchMembership WHERE BranchID = 5)";
      $_SESSION['branchchecked'] = 'winnipeg';
      /*add for branch expiry*/
      $whereStrForBranch = "AND Branch.ID = 5";
      $sTitleForBranch[] = "Winnipeg";
      $searchInitForBranch = "<th><input type='text' value='WpgExpiry' class='search_init' /></th>";
      /*add for branch expiry*/
      break;

    default:
      $_SESSION['branchchecked'] = 'all';
      /*add for branch expiry*/
      $sTitleForBranch[] = "Beautiful Plains";
      $sTitleForBranch[] = "Dauphin";
      $sTitleForBranch[] = "Southwest";
      $sTitleForBranch[] = "Winnipeg";
      $searchInitForBranch = "<th><input type='text' value='BPExpiry' class='search_init' /></th>
                              <th><input type='text' value='DpnExpiry' class='search_init' /></th>
                              <th><input type='text' value='SwExpiry' class='search_init' /></th>
                              <th><input type='text' value='WpgExpiry' class='search_init' /></th>";
      /*add for branch expiry*/
  }

  $whereStr = implode(" AND ", $where);

  $userInfo = array();
  $sql = "SELECT membership.membernum, type.name as memtype, accessLevel.name as access,
    generations.name as gen, yearjoined, expiry, info.firstname, info.lastname, info.address, info.city,
    info.province, info.countrycode, info.phone, info.email, members.username, members.verified
    FROM membership
    LEFT JOIN members ON members.membernum = membership.membernum
    LEFT JOIN typeofmember as type ON membership.typeofmember = type.id
    LEFT JOIN accesslevel ON members.accesslevel = accesslevel.id
    LEFT JOIN generations ON membership.generations = generations.id
    LEFT JOIN memberinfo as info ON membership.membernum = info.membernum";

  if ($whereStr != null){
    $sql .= " WHERE ";
    $sql .= $whereStr;
  }

  $stmt = sqlsrv_query($userConn, $sql, array(), array('Scrollable' => 'static'));
  if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

  while ($row = sqlsrv_fetch_array($stmt)) {
    $aRow = array();
    $user = $row['username'];
    $membernum = $row['membernum'];
    $encname = urlencode($user);

    $aRow[] = $membernum;
    $aRow[] = $row['memtype'];
    $aRow[] = $row['access'];
    $aRow[] = $row['gen'];
    $aRow[] = isset($row['yearjoined'])? $row['yearjoined']->format('Y-m-d'): null;
    /*modified for red color*/
    // $aRow[] = isset($row['expiry'])? $row['expiry']->format('Y-m-d'): null;
    $aRow[] = isset($row['expiry'])? (date_format($row['expiry'], 'U') < time() ? sprintf("<span style='color:Red;'>%s</span>", $row['expiry']->format('Y-m-d')) : $row['expiry']->format('Y-m-d')): null;
    /*modified for red color*/

    /*add for branch expiry*/
    $sqlForBranch = "SELECT Name ,Expiry FROM Branch
            LEFT JOIN BranchMembership ON Branch.ID = BranchMembership.BranchID AND BranchMembership.MemberID = ?
            WHERE Branch.ID <> '4'";

    $sqlForBranch .= ($whereStrForBranch != null) ? $whereStrForBranch : "";

    $stmtForBranch = sqlsrv_query($userConn, $sqlForBranch, array($membernum));
    if ($stmtForBranch === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    while ($rowForBranch = sqlsrv_fetch_array($stmtForBranch)) {

      $aRow[] = isset($rowForBranch['Expiry'])? (date_format($rowForBranch['Expiry'], 'U') < time() ? sprintf("<span style='color:Red;'>%s</span>", $rowForBranch['Expiry']->format('Y-m-d')) : $rowForBranch['Expiry']->format('Y-m-d')): null;
    }

    /*add for branch expiry*/

    $aRow[] = $row['firstname'];
    $aRow[] = $row['lastname'];
    $aRow[] = $row['address'];
    $aRow[] = $row['city'];
    $aRow[] = $row['province'];
    $aRow[] = $row['countrycode'];
    $aRow[] = $row['phone'];
    $aRow[] = $row['email'];
    if ($_SESSION['checked'] == 'mani'){
      $aRow[] = $user;
      $aRow[] = ($row['verified'] == '1')
        ? "<a target='_blank' href='userVerify.php?membernum=$membernum&amp;verify=false'>Verified</a>"
        : "<a target='_blank' href='userVerify.php?membernum=$membernum&amp;verify=true'>Not Verified</a>";
      $aRow[] = "<a href=\"javascript:confirmDelete('$encname','$membernum')\">Delete</a>";
    }

    $aRow[] = "<a target='_blank' href='userEdit.php?user=$membernum'>Edit</a>";

    for ($i = 0; $i < count($aRow); $i++)
      if (strtolower($row['memtype']) === 'deceased')
        $aRow[$i] = sprintf("<span style='color:grey'>%s</span>", $aRow[$i]);

    $userInfo[] = json_encode($aRow);
  }
/*
  if ($downloadcsv) {
    array_unshift($userInfo, array(
      'member number', 'member type', 'generations', 'year joined',
      'expiry', 'first name', 'last name', 'address', 'city', 'province',
      'country code', 'phone number', 'email'
    ));

    // create a csv file with the member info
    $filename = 'mgs-members-' . $_SESSION['branchchecked'] . '-' .
      $_SESSION['checked'] . '-' . time() . '.csv';

    $csvfh = fopen($filename, 'w');
    foreach ($userInfo as $user) fputcsv($csvfh, $user);
    fclose($csvfh);

    // provide the file for download
    header("Content-disposition: attachment; filename=$filename");
    header("Content-type: plain/text");

    readfile($filename);
    unlink($filename);
    exit;
  }
*/

  //print_r($userInfo);
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
    <link rel="stylesheet" type="text/css" href="/DataTables-1.10.6/dataTables.css">
    <link rel="stylesheet" type="text/css" href="/DataTables-1.10.6/extensions/ColReorder/css/dataTables.colReorder.css">
    <link rel="stylesheet" type="text/css" href="/DataTables-1.10.6/extensions/ColVis/css/dataTables.colVis.css">
    
    <link rel="stylesheet" type="text/css" href="/DataTables-1.10.6/extensions/TableTools/css/dataTables.tableTools.css">

    <script type="text/javascript" charset="utf-8" src="/DataTables-1.10.6/media/js/jquery.js"></script>
    <script type="text/javascript" charset="utf-8" src="/DataTables-1.10.6/dataTables.js"></script>
    <script type="text/javascript" charset="utf-8" src="/DataTables-1.10.6/extensions/ColReorder/js/dataTables.colReorder.js"></script>
    <script type="text/javascript" charset="utf-8" src="/DataTables-1.10.6/extensions/ColVis/js/dataTables.colVis.js"></script>
    
    <script type="text/javascript" charset="utf-8" src="/DataTables-1.10.6/extensions/TableTools/js/dataTables.tableTools.js"></script>





<link rel="stylesheet" type="text/css" href="/DataTables-1.10.6/Buttons-1.5.1/css/buttons.dataTables.css"/>
<script type="text/javascript" src="/DataTables-1.10.6/pdfmake-0.1.32/pdfmake.js"></script>
<script type="text/javascript" src="/DataTables-1.10.6/pdfmake-0.1.32/vfs_fonts.js"></script>
<script type="text/javascript" src="/DataTables-1.10.6/Buttons-1.5.1/js/dataTables.buttons.js"></script>
<script type="text/javascript" src="/DataTables-1.10.6/Buttons-1.5.1/js/buttons.html5.js"></script>
<script type="text/javascript" src="/DataTables-1.10.6/Buttons-1.5.1/js/buttons.print.js"></script>
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
          <?php if($row != '') : ?>
          
            tableData.push(JSON.parse('<?= addslashes($row) ?>').map(function(text){return '' + text}));
          <?php endif; ?>
        <?php endforeach; ?>

        var oTable = $('#example').dataTable({
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
                        /*add for branch expiry*/
                        <?php foreach ($sTitleForBranch as $key => $value) : ?>
                          {'sTitle': '<?php echo ($value); ?>'},
                        <?php endforeach; ?>
                        /*add for branch expiry*/
                        {"sTitle": "First Name"},
                        {"sTitle": "Last Name"},
                        {"sTitle": "Address"},
                        {"sTitle": "City"},
                        {"sTitle": "Province"},
                        {"sTitle": "CountryCode"},
                        {"sTitle": "Phone"},
                        {"sTitle": "Email"},
                        <?php if ($_SESSION['checked'] == 'mani'): ?>
                          {"sTitle": "User Name"},
                          {"sTitle": "Verified"},
                          {"sTitle": "Delete"},
                        <?php endif; ?>
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

        $('input[name=subset]').change(function(){
          //$('#subset').submit();
          $('form').submit();
        });

        $('input[name=branch]').change(function(){
          //$('#subet').submit();
          $('form').submit();
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
        <h2> Members List </h2>
        <form id="subset" action="userInfo.php" method="post">
          <div id="filter" style="float:left;margin-right:50px;">
            <h4>Filter Results</h4>
            <input type="radio" id="all_result" name="subset" value="all"
              <?php if (!isset($_SESSION['checked']) || $_SESSION['checked'] == 'all'): ?>checked<?php endif; ?>>
            <label for="all_result" style="display:inline">All</label>

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
          <div id="branchfilter" style="float:left;">
            <h4>Filter by Branch</h4>
            <input type="radio" class="filter" name="branch" id="all_branch" value="all"
              <?php if (!isset($_SESSION['branchchecked']) || $_SESSION['branchchecked'] == 'all'): ?>checked<?php endif; ?>>
            <label for="all_branch" style="display:inline">All</label>

            <input type="radio" class="filter" name="branch" id="bplains" value="bplains"
              <?php if (isset($_SESSION['branchchecked']) && $_SESSION['branchchecked'] == 'bplains'): ?>checked<?php endif; ?>>
            <label for="bplains" style="display:inline">Beautiful Plains</label>

            <input type="radio" class="filter" name="branch" id="dauphin" value="dauphin"
              <?php if (isset($_SESSION['branchchecked']) && $_SESSION['branchchecked'] == 'dauphin'): ?>checked<?php endif; ?>>
            <label for="dauphin" style="display:inline">Dauphin</label>

            <input type="radio" class="filter" name="branch" id="southwest" value="southwest"
              <?php if (isset($_SESSION['branchchecked']) && $_SESSION['branchchecked'] == 'southwest'): ?>checked<?php endif; ?>>
            <label for="southwest" style="display:inline">Southwest</label>

            <input type="radio" class="filter" name="branch" id="svalley" value="svalley"
              <?php if (isset($_SESSION['branchchecked']) && $_SESSION['branchchecked'] == 'svalley'): ?>checked<?php endif; ?>>
            <label for="svalley" style="display:inline">Branch 4(Closed)</label>

            <input type="radio" class="filter" name="branch" id="winnipeg" value="winnipeg"
              <?php if (isset($_SESSION['branchchecked']) && $_SESSION['branchchecked'] == 'winnipeg'): ?>checked<?php endif; ?>>
            <label for="winnipeg" style="display:inline">Winnipeg</label>
          </div>

          <!--input style="clear:both;float:left;margin:10px 0px" value="Download table as csv" type="submit" name="downloadcsv"-->
        </form>
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
            <!-- add for branch expiry -->
            <?= $searchInitForBranch; ?>
            <!-- add for branch expiry -->
            <th><input type='text' value='First Name' class='search_init' /></th>
            <th><input type='text' value='Last Name' class='search_init' /></th>
            <th><input type='text' value='Address' class='search_init' /></th>
            <th><input type='text' value='City' class='search_init' /></th>
            <th><input type='text' value='Province' class='search_init' /></th>
            <th><input type='text' value='CountryCode' class='search_init' /></th>
            <th><input type='text' value='Phone' class='search_init' /></th>
            <th><input type='text' value='E-mail' class='search_init' /></th>
            <?php if ($_SESSION['checked'] == 'mani'): ?>
              <th><input type='text' value='Username' class='search_init' /></th>
              <th><input type='text' value='Verification' class='search_init' /></th>
            <?php endif; ?>
            <th></th>
          </tfoot>
        </table>
      </div>
    </div>
  </body>
</html>