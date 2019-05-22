<?php
  require('../db/volunteerCheck.php');
  require('../errorReporter.php');
  require('../db/volunteerConnection.php');
  require('../retrieveColumns.php');

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
  {
    $aColumns[] = "$sTable.$val";
    // if (strtolower($sTable) !== 'cemeteries' || $val !== 'Owner') {
    //   //$aColumns[] = "cmow";
    //   $aColumns[] = "$sTable.$val";
    // }

  }
  

  if (strtolower($sTable) === 'cemeteryrecords') {
    $join = 'join cemeteries on cemeterycode = cemcode';
    $searchColumns = $aColumns;
    $searchColumns[] = 'cemeteries.cemdescr';
    $formatted_values[] = 'cemdescr';
  }
   else {
    $searchColumns = $aColumns;
    $join = '';
  }

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
  $offset =  $iCurrentPage * $limit;

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
    //$row[] = "<input type='checkbox' name='check[]' value='".$aRow[$sIndexColumn]."'>";
    /**
    @modified by Billy
    convert data retrieved from DB from Latin-1 to UTF-8
    */
    $row[] = mb_convert_encoding("<input class='all' type='checkbox' name='check[]' value='".$aRow[$sIndexColumn]."'>",'UTF-8','ISO-8859-1');

    for ($i=0; $i<count($searchColumns); $i++) {
      if ($formatted_values[$i] != ' ') {
        $v = $aRow[ $formatted_values[$i] ];
        //$v = mb_check_encoding($v, 'UTF-8') ? $v : utf8_encode($v);

        if ($v != null) {
          if ($formatted_values[$i] == 'CemLink')
          $v = "<a href='".$aRow['CemLink']."' target='_blank'>Link</a>";
          if ($formatted_values[$i] == 'MHSLink')
          $v = "<a href='".$aRow['MHSLink']."' target='_blank'>Link</a>";
          if ($formatted_values[$i] == 'ManitobiaLink')
          $v = "<a href='".$aRow['ManitobiaLink']."' target='_blank'>Link</a>";
          if ($formatted_values[$i] == 'CTPictures')
          $v = "<a href='".$aRow['CTPictures']."' target='_blank'>Link</a>";
          //if ($formatted_values[$i] == 'PaperCode')
          // {
          //   // if (isset($_GET['tablename']) && isset($_GET['id'])) 
          //   // {
          //   //   list($tableName, $recordID) = array($_GET['tablename'], $_GET['id']);
          //   // }
          //    // $sql = "SELECT * FROM $tableName WHERE $id = ?";
          //    // $stmt = sqlsrv_query($conn, $sql, array($recordID));
          //   // if ($stmt === false) {
          //   //     errorReport(sqlsrv_errors(), __FILE__, __LINE__);
          //   //  }
          //    $sqlPaperCode = "SELECT NameOfNewspaper, NewspaperCode, ID FROM Newspapers WHERE NewspaperCode = ?";
          //    $stmtPaperCode = sqlsrv_query( $conn, $sqlPaperCode, array($row['PaperCode']), array( "Scrollable" => 'static' ));

          //         $rowPaperCode = sqlsrv_fetch_array($stmtPaperCode);
          //         $newspaperName = $rowPaperCode[0];
          //         $newspaperCodes = $rowPaperCode[1];
          //         $id = $rowPaperCode[2];
          //$v = "<a href='member/singleRecord.php?$table=Newspapers'>123</a>";
          // }

        }

        //$row[] = $v;
        $row[]=mb_convert_encoding($v,'UTF-8','ISO-8859-1');
      }
    }

    //error need to be fixed here
    //Edit and Delete button are trying to convert the checkbox as a string
    foreach ($row as $key => $value) {
      if ($key == 'EDIT')
        //$row[] = "<a href='edit.php?tablename=$sTable&amp;$sIndexColumn=$value's>Edit</a>";
         //$source = $_GET['table'];
        if ($aRow['StatusCode'] == 'NEW') {
              $row[] = " ";
        }else{
            $row[] = "<a target=\"_blank\" href='edit.php?tablename=$sTable&amp;$sIndexColumn=".$aRow[$sIndexColumn]."'>Edit</a>";
        }

      if ($key == 'DEL') {
      	//$deleteValue = $value->getAttribute('value');
        $statusCode = (($aRow['StatusCode'] == 'NEW') ? 'NEW' : 'null');
        //$row[] ="<a href=\"javascript:confirmDelete('$sTable','$value','$statusCode');\">Delete</a>";
        $row[] ="<a href=\"javascript:confirmDelete('$sTable','".$aRow[$sIndexColumn]."','$statusCode');\">Delete</a>";

      }
    }
  
  if (!empty($row))
    $output['aaData'][] = $row;
  }
  $jasonStr=json_encode($output);
  if($jasonStr===false){
    echo json_last_error_msg ();
    echo print_r($output);
    exit;
  }
  echo $jasonStr;
?>
