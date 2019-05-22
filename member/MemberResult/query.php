<?php
    require('../../db/loginCheck.php');
    require('../../db/mgsConnection.php');
    require('../../errorReporter.php');
    require('../../retrieveColumns.php');

    $sTable = "Cemeteries";
    $and = "AND COLUMN_NAME IN('ID', 'Municipality')";
    $searchColumns = retrieveColumns($sTable, $and, $conn);

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
    $sWhere = "";
    if (isset($_GET['sSearch']) && $_GET['sSearch'] != '') {
        $sWhere = 'WHERE (';

        for ($i=0; $i<count($searchColumns); $i++) {
            $sWhere .= $searchColumns[$i]." LIKE '%".addslashes($_GET['sSearch'])."%' OR ";
        }
        $sWhere = substr_replace( $sWhere, "", -3 );
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
    $top = (isset($_GET['iDisplayStart']))&&$_GET['iDisplayStart']!=""?((int)$_GET['iDisplayStart']):0 ;
    $limit = (isset($_GET['iDisplayLength']))?((int)$_GET['iDisplayLength'] ):10;
    $iCurrentPage = ceil(($_GET['iDisplayStart']) / ($_GET['iDisplayLength']));
    $offset =  $iCurrentPage * $limit; 

    $ssQuery = "SELECT TOP $limit " . implode($searchColumns, ", ") . " FROM $sTable 
            $sWhere ".(($sWhere == "")?"WHERE Municipality IS NOT NULL AND Municipality <> '' AND ":"AND Municipality IS NOT NULL AND Municipality <> '' AND ") . "ID NOT IN (
                SELECT ID FROM 
                (
                    SELECT TOP $top " . implode($searchColumns, ", ") . " FROM $sTable ".(($sWhere == "")?"WHERE Municipality IS NOT NULL AND Municipality <> ''":"$sWhere AND Municipality IS NOT NULL AND Municipality <> ''")." $sOrder
                ) AS [virtTable] )
            $sOrder";
       
    $rResult = sqlsrv_query($conn, $ssQuery);
    if ($rResult === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__.$ssQuery);

    $sQueryCnt = "SELECT * FROM $sTable ".(($sWhere == "")?"WHERE Municipality IS NOT NULL AND Municipality <> ''":"$sWhere AND Municipality IS NOT NULL AND Municipality <> ''");
    
    $rResultCnt = sqlsrv_query($conn, $sQueryCnt, array(), array("Scrollable" => "static"));
    if ($rResultCnt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    $iFilteredTotal = sqlsrv_num_rows($rResultCnt);

    $sQueryTotal = "SELECT DISTINCT COUNT( * ) AS ROW_COUNT FROM $sTable";
    $rResultTotal = sqlsrv_query($conn, $sQueryTotal, array(), array("Scrollable" => "static"));
    if ($rResultTotal === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    $iTotal = sqlsrv_fetch_array($rResultTotal)['ROW_COUNT'];

    $output = array(
        "sEcho" => intval($_GET['sEcho']),
        "iTotalRecords" => $iTotal,
        "iTotalDisplayRecords" => $iFilteredTotal,
        "aaData" => array(),
        "error" => $ssQuery
    );

    while ( $aRow = sqlsrv_fetch_array($rResult, SQLSRV_FETCH_ASSOC)) {
        $row = array();
        
        for ( $i=0 ; $i<count($searchColumns) ; $i++ ) {

            if ( $searchColumns[$i] != ' ')
                $v = $aRow[ $searchColumns[$i] ];

            $v = mb_check_encoding($v, 'UTF-8') ? $v : utf8_encode($v);
            $row[]=$v;
        }
        if (!empty($row)) { $output['aaData'][] = $row; }
    }

    if (!isset($noJsonEcho) || !$noJsonEcho) echo json_encode($output);