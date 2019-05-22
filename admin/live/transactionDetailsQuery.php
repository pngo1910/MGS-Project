<?php
    require('../../db/adminCheck.php');
	require('../../errorReporter.php');
    require('../../retrieveColumns.php');
    require('../../db/memberConnection.php');

    $num = $_SESSION['transaction'];
    $match = $_SESSION['researchMatch'];
    if($match)
        $sTable = "ResearchDetails";
    else
        $sTable = "TransactionDetails";
    $primaryKey = retrievePrimaryKeys($sTable, $userConn);
    $sIndexColumn = $primaryKey[0];
    $cols = retrieveColumns($sTable, 0, $userConn);
    $where = "TransactionsID LIKE '%$num%'";

    $searchColumns = array();

    foreach($cols as $column)
        array_push($searchColumns, $column);

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
    $sWhere = $_GET['sSearch'] != ""? "" : "WHERE TransactionsID LIKE '%$num%' ";
    if (isset($_GET['sSearch']) && $_GET['sSearch'] != '') {
        $sWhere = 'WHERE (';

        for ($i=0; $i<count($searchColumns); $i++) {
            $sWhere .= $searchColumns[$i]." LIKE '%".addslashes( $_GET['sSearch'] )."%' OR ";
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
    $top = (isset($_GET['iDisplayStart']))?((int)$_GET['iDisplayStart']):0 ;
    $limit = (isset($_GET['iDisplayLength']))?((int)$_GET['iDisplayLength'] ):10;
    $iCurrentPage = ceil(($_GET['iDisplayStart']) / ($_GET['iDisplayLength']));
    $offset =  $iCurrentPage * $limit; 

    $sQuery = "SELECT TOP $limit " . implode($searchColumns, ", ") . " FROM $sTable
                $sWhere ".(($sWhere=="")?"WHERE $where AND ":" AND $where AND ")."$sIndexColumn NOT IN 
                (
                    SELECT $sIndexColumn FROM 
                    (
                            SELECT TOP $top " . implode($searchColumns, ", ") . "
                            FROM $sTable
                            $sWhere ".(($sWhere=="")?"WHERE $where":" AND $where ")."
                            $sOrder
                    ) 
                    AS [virtTable]
                )
                $sOrder ";
    
    $rResult = sqlsrv_query($userConn, $sQuery, array(), array("Scrollable" => "static"));
    if ($rResult === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__.$sQuery);

    $sQueryCnt = "SELECT * FROM $sTable ".(($sWhere=="") ? "WHERE $where " : "$sWhere AND $where");
    $rResultCnt = sqlsrv_query($userConn, $sQueryCnt, array(), array("Scrollable" => "static"));
    if ($rResultCnt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    $iFilteredTotal = sqlsrv_num_rows($rResultCnt);

    $sQuery = " SELECT DISTINCT COUNT( * ) AS ROW_COUNT FROM $sTable WHERE $where";
    $rResultTotal = sqlsrv_query($userConn, $sQuery, array(), array("Scrollable" => "static"));
    if ($rResultTotal === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    $iTotal = sqlsrv_fetch_array($rResultTotal)['ROW_COUNT'];
    
    $output = array(
        "sEcho" => intval($_GET['sEcho']),
        "iTotalRecords" => $iTotal,
        "iTotalDisplayRecords" => $iFilteredTotal,
        "aaData" => array()
    );

    while ( $aRow = sqlsrv_fetch_array($rResult, SQLSRV_FETCH_ASSOC) ) {
        $row = array();
        
        for ( $i=0 ; $i<count($searchColumns) ; $i++ ) {
            
            if ( $searchColumns[$i] != ' ' && $searchColumns[$i] != "Price" )
                $v = $aRow[ $searchColumns[$i] ];

            if($searchColumns[$i] === "Price")
                $v = "$" . $aRow[ $searchColumns[$i] ];

            $v = mb_check_encoding($v, 'UTF-8') ? $v : utf8_encode($v);
            $row[]=$v;
        }
        if (!empty($row)) { $output['aaData'][] = $row; }
    }

    if (!isset($noJsonEcho) || !$noJsonEcho) echo json_encode($output);