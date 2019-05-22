<?php
    require('../db/loginCheck.php');
    require('../db/storeConnection.php');
    require('../db/memberConnection.php');
    require('../errorReporter.php');
    require('../retrieveColumns.php');

    $username = $_SESSION['uname'];
    $sTable = "Transactions";
    $sql = "SELECT MemberNum FROM Members WHERE Username = ?";
    $stmt = sqlsrv_query($userConn, $sql, array($username), array("Scrollable" => "static"));
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    $memberNum = sqlsrv_fetch_array($stmt)['MemberNum'];

    $and = "AND COLUMN_NAME NOT IN('ID')";
    $searchColumns = retrieveColumns($sTable, $and, $storeConn);

    $searchColumns = preg_replace("/^ID/", "$sTable.ID", $searchColumns);
    $searchColumns = preg_replace("/^TransactionID/", "PayPalTransactions.TransactionID", $searchColumns);

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

    $sjoin = "JOIN PayPalTransactions ON Transactions.TransactionID = PayPalTransactions.ID";
    $sQuery = "SELECT TOP $limit " . implode($searchColumns, ", ") . " FROM $sTable $sjoin 
                $sWhere ".(($sWhere == "")?"WHERE MemberNum = ? AND ":" AND MemberNum = ? AND ") . "$sTable.ID NOT IN (
                    SELECT Transactions.ID FROM 
                    (
                            SELECT TOP $top " . implode($searchColumns, ", ") . "
                            FROM $sTable $sjoin ".(($sWhere == "")?"WHERE MemberNum = ?":"$sWhere AND MemberNum = ?")." $sOrder
                    ) AS [virtTable] )
                $sOrder";
       
    $rResult = sqlsrv_query($storeConn, $sQuery, array($memberNum, $memberNum), array("Scrollable" => "static"));
    if ($rResult === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    $sQueryCnt = "SELECT * FROM $sTable $sjoin ".(($sWhere=="")?"WHERE MemberNum = ?":"$sWhere AND MemberNum = ?");
    
    $rResultCnt = sqlsrv_query($storeConn, $sQueryCnt, array($memberNum), array("Scrollable" => "static"));
    if ($rResultCnt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    $iFilteredTotal = sqlsrv_num_rows($rResultCnt);

    $sQueryTotal = "SELECT DISTINCT COUNT( * ) AS ROW_COUNT FROM $sTable WHERE MemberNum = ?";
    $rResultTotal = sqlsrv_query($storeConn, $sQueryTotal, array($memberNum), array("Scrollable" => "static"));
    if ($rResultTotal === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    $iTotal = sqlsrv_fetch_array($rResultTotal)['ROW_COUNT'];

    $output = array(
        "sEcho" => intval($_GET['sEcho']),
        "iTotalRecords" => $iTotal,
        "iTotalDisplayRecords" => $iFilteredTotal,
        "aaData" => array()
    );

    $searchColumns = preg_replace("/^Transactions.ID/", "ID", $searchColumns);
    $searchColumns = preg_replace("/^PayPalTransactions.TransactionID/", "TransactionID", $searchColumns);
    while ( $aRow = sqlsrv_fetch_array($rResult, SQLSRV_FETCH_ASSOC)) {
        $row = array();
        
        for ( $i=0 ; $i<count($searchColumns) ; $i++ ) {

            if ( $searchColumns[$i] != ' ' && $searchColumns[$i] != "Total" && $searchColumns[$i] != "Created")
                $v = $aRow[ $searchColumns[$i] ];
            
            if($searchColumns[$i] === "Total")
                $v = "$" . $aRow[ $searchColumns[$i] ];
            
            if($searchColumns[$i] === "Created")
                $v = $aRow[ $searchColumns[$i] ]->format("Y-m-d");

            $v = mb_check_encoding($v, 'UTF-8') ? $v : utf8_encode($v);
            $row[]=$v;
        }
        if (!empty($row)) { $output['aaData'][] = $row; }
    }

    if (!isset($noJsonEcho) || !$noJsonEcho) echo json_encode($output);