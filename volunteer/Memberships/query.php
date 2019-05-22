<?php
  require('../../db/membershipAdminCheck.php');
  require('../../errorReporter.php');
  require('../../db/memberConnection.php');
  require('../../retrieveColumns.php');

  $sTable = $_SESSION['tableName'];
  $params = array();
  $options = array("Scrollable" => SQLSRV_CURSOR_KEYSET);

  /* Primary Key Columns */
  $primaryKeys = retrievePrimaryKeys($sTable, $userConn);

  /* Indexed column (used for fast and accurate table cardinality) */
  $sIndexColumn = $primaryKeys[0];

  /*
   * Columns
   * If you don't want all of the columns displayed you need to hardcode
   * $aColumns array with your elements. If not this will grab all the columns
   * associated with $sTable
   */
  $formatted_values = retrieveColumns($sTable, 0, $userConn);

  foreach ($formatted_values as $val)
    $aColumns[] = "$sTable.$val";

  $searchColumns = $aColumns;
  $join = '';

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
  $sWhere = '';
  if (isset($_GET['sSearch']) && $_GET['sSearch'] != '') {
    $sWhere = 'WHERE (';

    for ($i=0; $i<count($searchColumns); $i++) {
      $sWhere .= $searchColumns[$i]." LIKE '%".addslashes($_GET['sSearch'])."%' OR ";
    }

    $sWhere = substr_replace($sWhere, '', -3);
    $sWhere .= ')';
  }

  /* Individual column filtering */
  for ($i = 0; $i < count($searchColumns); $i++) {
    if (isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )  {
      $sWhere .= (($sWhere == '') ? ' where ' : ' and ');
      $sWhere .= $searchColumns[$i]." LIKE '%".addslashes($_GET['sSearch_'.$i])."%' ";
    }
  }

  /* Paging */
  $top = (isset($_GET['iDisplayStart']))?((int)$_GET['iDisplayStart']):0;
  $limit = (isset($_GET['iDisplayLength']))?((int)$_GET['iDisplayLength']):10;
  $iCurrentPage = ceil(($_GET['iDisplayStart']) / ($_GET['iDisplayLength']));
  $offset =  $iCurrentPage * $limit;

  $sQuery = "SELECT TOP $limit ".implode(",",$searchColumns)." FROM $sTable $join
    $sWhere ".(($sWhere=="")?" WHERE ":" AND ")." $sTable.$sIndexColumn NOT IN (
      SELECT $sIndexColumn FROM (
        SELECT TOP $top ".implode(",",$searchColumns)."
        FROM $sTable $join $sWhere $sOrder ) as [virtTable] )
      $sOrder";

  $rResult = sqlsrv_query($userConn, $sQuery);
  if ($rResult === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

  $sQueryCnt = "SELECT * FROM $sTable $join $sWhere";
  $rResultCnt = sqlsrv_query($userConn, $sQueryCnt, $params, $options);
  if ($rResultCnt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

  $iFilteredTotal = sqlsrv_num_rows($rResultCnt);

  $sQuery = " SELECT distinct COUNT (*) AS ROW_COUNT FROM $sTable";
  $rResultTotal = sqlsrv_query($userConn, $sQuery, $params, $options);
  if ($rResultTotal === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

  $row = sqlsrv_fetch_array($rResultTotal);
  $iTotal = $row['ROW_COUNT'];

  $output = array(
    "sEcho" => intval($_GET['sEcho']),
    "iTotalRecords" => $iTotal,
    "iTotalDisplayRecords" => $iFilteredTotal,
    "aaData" => array()
  );

  while ($aRow = sqlsrv_fetch_array($rResult)) {
    $row = array();
    for ($i=0; $i<count($searchColumns); $i++) {
      if ($formatted_values[$i] != ' ') {
        $v = $aRow[ $formatted_values[$i] ];
        $v = mb_check_encoding($v, 'UTF-8') ? $v : utf8_encode($v);

        $row[] = $v;
      }
    }

    foreach ($row as $key => $value) {
      if ($key == 'EDIT')
        $row[] = "<a href='edit.php?tablename=$sTable&$sIndexColumn=$value'>Edit</a>";

      if ($key == 'DEL') {
        $row[] ="<a href=javascript:confirmDelete('$sTable','$value');>Delete</a>";
      }
    }

    if (!empty($row))
      $output['aaData'][] = $row;
  }

  echo json_encode($output);
?>
