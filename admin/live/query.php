<?php
  require('../../db/adminCheck.php');
  require('../../errorReporter.php');
  require('../../db/adminConnection.php');
  require('../../retrieveColumns.php');

  $sTable = $_SESSION['tableName'];
  $params = array();
  $options = array("Scrollable" => SQLSRV_CURSOR_KEYSET);

  /* Primary Key Columns */
  $primaryKeys = retrievePrimaryKeys($sTable, $conn);

  /* Indexed column (used for fast and accurate table cardinality) */
  $sIndexColumn = $primaryKeys[0];

  /*
   * Columns
   * If you don't want all of the columns displayed you need to hardcode
   * $aColumns array with your elements. If not this will grab all the columns
   * associated with $sTable
   */
  $formatted_values = retrieveColumns($sTable, 0, $conn);

  foreach ($formatted_values as $val)
    $aColumns[] = "$sTable.$val";

  // if (strtolower($sTable) == 'cemeteryrecords') {
  //   $join = 'join cemeteries on cemid = cemcode';
  //   $searchColumns = $aColumns;
  //   $searchColumns[] = 'cemeteries.cemdescr';
  //   $formatted_values[] = 'cemdescr';
  // } else {
    $searchColumns = $aColumns;
    $join = '';
  // }

  /* Ordering */
  $sOrder = "";
  if (isset($_GET['iSortCol_0'])) {
    $sOrder = "ORDER BY  ";
    for ($i=0; $i<intval($_GET['iSortingCols']); $i++) {
      if ($_GET['bSortable_'.intval($_GET["iSortCol_$i"])] == "true") {
        $sOrder .= $searchColumns[intval($_GET["iSortCol_$i"])].' '
          .addslashes($_GET["sSortDir_$i"]).', ';
      }
    }

    $sOrder = substr_replace($sOrder, '', -2);
    if ($sOrder == 'ORDER BY') $sOrder = '';
  }

  /* Filtering */
  $sWhere = 'where ' . $_SESSION['where'];
  if (isset($_GET['sSearch']) && $_GET['sSearch'] != '') {
    $sWhere = 'where (';

    for ($i=0; $i<count($searchColumns); $i++) {
      $sWhere .= $searchColumns[$i]." LIKE '%".addslashes($_GET['sSearch'])."%' or ";
    }

    $sWhere = substr_replace($sWhere, '', -3) . ')';
  }

  /* Individual column filtering */
  for ($i = 0; $i < count($searchColumns); $i++) {
    if (isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )  {
      $sWhere .= (($sWhere === '') ? ' where ' : ' and ');
      $sWhere .= $searchColumns[$i]." LIKE '%".addslashes($_GET['sSearch_'.$i])."%' ";
    }
  }

  /* Paging */
  $top = (isset($_GET['iDisplayStart']))?((int)$_GET['iDisplayStart']):0;
  $limit = (isset($_GET['iDisplayLength']))?((int)$_GET['iDisplayLength']):10;
  $iCurrentPage = ceil(($_GET['iDisplayStart']) / ($_GET['iDisplayLength']));
  $offset = $iCurrentPage * $limit;

  $sQuery = "SELECT TOP $limit ".implode(",",$searchColumns)." FROM $sTable $join
    $sWhere ".(($sWhere=="")?" WHERE ":" AND ")." $sTable.$sIndexColumn NOT IN (
      SELECT $sIndexColumn FROM (
        SELECT TOP $top ".implode(",",$searchColumns)."
        FROM $sTable $join $sWhere $sOrder ) as [virtTable] )
      $sOrder";

  $rResult = sqlsrv_query($conn, $sQuery);
  if ($rResult === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

  $sQueryCnt = "SELECT * FROM $sTable $join $sWhere";
  $rResultCnt = sqlsrv_query($conn, $sQueryCnt, $params, $options);
  if ($rResultCnt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

  $iFilteredTotal = sqlsrv_num_rows($rResultCnt);

  $sQuery = " SELECT distinct COUNT (*) AS ROW_COUNT FROM $sTable";
  $rResultTotal = sqlsrv_query($conn, $sQuery, $params, $options);
  if ($rResultTotal === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

  $iTotal = sqlsrv_fetch_array($rResultTotal)['ROW_COUNT'];

  $output = array(
    "sEcho" => intval($_GET['sEcho']),
    "iTotalRecords" => $iTotal,
    "iTotalDisplayRecords" => $iFilteredTotal,
    "aaData" => array()
  );

  while ($aRow = sqlsrv_fetch_array($rResult)) {
    $row = array();

    // $row[] = "<input type='checkbox' name='check[]' value='".$aRow[$sIndexColumn]."'>";
    //class was added for testing select all function
    $row[] = "<input class='all' type='checkbox' name='check[]' value='".$aRow[$sIndexColumn]."'>";

    for ($i=0; $i<count($searchColumns); $i++) {
      if ($formatted_values[$i] != ' ') {
        $v = $aRow[ $formatted_values[$i] ];
        $v = mb_check_encoding($v, 'UTF-8') ? $v : utf8_encode($v);

         if ($v != null) {
          if ($formatted_values[$i] == 'CemLink')
          $v = "<a href='".$aRow['CemLink']."' target='_blank'>Link</a>";
          if ($formatted_values[$i] == 'MHSLink')
          $v = "<a href='".$aRow['MHSLink']."' target='_blank'>Link</a>";
          if ($formatted_values[$i] == 'ManitobiaLink')
          $v = "<a href='".$aRow['ManitobiaLink']."' target='_blank'>Link</a>";
          if ($formatted_values[$i] == 'CTPictures')
          $v = "<a href='".$aRow['CTPictures']."' target='_blank'>Link</a>";
        }

        $row[] = $v;
      }
    }

    if (!empty($row))
      $output['aaData'][] = $row;
  }

  echo json_encode($output);
?>
