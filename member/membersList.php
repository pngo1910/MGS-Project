<?php
  
  require('../db/memberCheck.php');
  require('../errorReporter.php');
  require('../db/memberConnection.php');

  $sql = "SELECT MemberNum FROM Members WHERE Username = ?";
  $stmt = sqlsrv_query($userConn, $sql, array($_SESSION['uname']), array('Scrollable' => 'static'));
  if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

  $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_ABSOLUTE);

  Switch($row['MemberNum']){
    case 9913:
      $branch = 1;
        $branchName = 'Beautiful Plains Branch Members List';
      break;
    case 9902:
      $branch = 2;
      $branchName = 'Dauphin Branch Members List';
      break;
    case 9904:
      $branch = 3;
      $branchName = 'Southwest Branch Members List';
      break;
    case 9905:
      $branch = 4;
      $branchName = 'MGS Members';
      break;
    case 9906:
      $branch = 5;
      $branchName = 'Winnipeg Branch Members List';
      break;
    default:
      header('location: /member/');
  }

  $subset = isset($_POST['subset'])? $_POST['subset']: null;

  switch ($subset) {
    case 'all':
      $_SESSION['checked'] = 'all';
      $where = '';
      break;

    case 'expired':
      $where = " AND membership.expiry <= GETDATE()";
      $_SESSION['checked'] = 'expired';
      break;

    default:
      $where = " AND membership.expiry > GETDATE()";
      $_SESSION['checked'] = 'active';
  }

  $userInfo = array();
  if($branch == 4){
    $sql = "SELECT  membership.membernum, type.name as memtype, generations.name as gen, yearjoined, expiry,
      info.firstname, info.lastname, info.address, info.city, info.province, info.countrycode, info.phone,
      info.email FROM membership
      LEFT JOIN members ON members.membernum = membership.membernum
      LEFT JOIN typeofmember as type ON membership.typeofmember = type.id
      LEFT JOIN generations ON membership.generations = generations.id
      LEFT JOIN memberinfo as info ON membership.membernum = info.membernum
      WHERE membership.membernum <> 4440 AND 1=1 ".$where;


  }else{
    $sql = "SELECT membership.membernum, type.name as memtype, generations.name as gen, yearjoined, expiry,
      info.firstname, info.lastname, info.address, info.city, info.province, info.countrycode, info.phone,
      info.email FROM membership
      LEFT JOIN members ON members.membernum = membership.membernum
      LEFT JOIN typeofmember as type ON membership.typeofmember = type.id
      LEFT JOIN generations ON membership.generations = generations.id
      LEFT JOIN memberinfo as info ON membership.membernum = info.membernum
      WHERE Membership.membernum IN (SELECT MemberID FROM BranchMembership WHERE BranchID = ?)".$where;
}

  $stmt = sqlsrv_query($userConn, $sql, array($branch), array('Scrollable' => 'static'));
  if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

  while ($row = sqlsrv_fetch_array($stmt)) {
    $aRow = array();

    $aRow[] = $row['membernum'];
    $aRow[] = $row['memtype'];
    $aRow[] = $row['gen'];
    $aRow[] = isset($row['yearjoined'])? $row['yearjoined']->format('Y-m-d'): null;
    $aRow[] = isset($row['expiry'])? $row['expiry']->format('Y-m-d'): null;
    $aRow[] = $row['firstname'];
    $aRow[] = $row['lastname'];
    $aRow[] = $row['address'];
    $aRow[] = $row['city'];
    $aRow[] = $row['province'];
    $aRow[] = $row['countrycode'];
    $aRow[] = $row['phone'];
    $aRow[] = $row['email'];
//echo $row['firstname']."<br>";
    for ($i = 0; $i < count($aRow); $i++)
      if (strtolower($row['memtype']) === 'deceased')
       $aRow[$i] = sprintf("<span style='color:grey'>%s</span>", $aRow[$i]);

    $userInfo[] = json_encode($aRow);
  }
 // echo $userInfo;
  //die('');
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

    <link rel="stylesheet" type="text/css" href="/DataTables-1.10.6/media/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="/DataTables-1.10.6/extensions/ColReorder/css/dataTables.colReorder.css">
    <link rel="stylesheet" type="text/css" href="/DataTables-1.10.6/extensions/ColVis/css/dataTables.colVis.css">
    <link rel="stylesheet" type="text/css" href="/DataTables-1.10.6/extensions/TableTools/css/dataTables.tableTools.css">

    <script type="text/javascript" charset="utf-8" src="/DataTables-1.10.6/media/js/jquery.js"></script>
    <script type="text/javascript" charset="utf-8" src="/DataTables-1.10.6/media/js/jquery.dataTables.js"></script>
    <script type="text/javascript" charset="utf-8" src="/DataTables-1.10.6/extensions/ColReorder/js/dataTables.colReorder.js"></script>
    <script type="text/javascript" charset="utf-8" src="/DataTables-1.10.6/extensions/ColVis/js/dataTables.colVis.js"></script>
    <script type="text/javascript" charset="utf-8" src="/DataTables-1.10.6/extensions/TableTools/js/dataTables.tableTools.js"></script>

    <script>
      $(document).ready(function() {

        var calcDataTableHeight = function() {
          return $(window).height()*55/100;
        };

        var asInitVals = [];
        var tableData = [];

        <?php foreach ($userInfo as $key => $row) : ?>
          tableData.push(JSON.parse('<?= addslashes($row) ?>').map(function(text){return '' + text}));
        <?php endforeach; ?>

        var oTable = $('#example').dataTable({
          "dom": 'TC<"clear">Rlfrtip',
          "tableTools": {
            "sSwfPath": "/DataTables-1.10.6/extensions/TableTools/swf/copy_csv_xls_pdf.swf"
          },
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
                        {"sTitle": "Email"}]});

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
        <h2><?php echo $branchName ?> </h2>
        <form id="subset" action="membersList.php" method="post">
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
        </form>
      </div>
      <div id="content">
        <table class="display" id="example" cellspacing="0" width="100%">
          <thead></thead>
          <tfoot>
            <th><input type='text' value='Member Number' class='search_init' /></th>
            <th><input type='text' value='Member Type' class='search_init' /></th>
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
          </tfoot>
        </table>
      </div>
    </div>
  </body>
</html>
