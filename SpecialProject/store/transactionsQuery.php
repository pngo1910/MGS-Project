<?php
    require('../../db/adminCheck.php');
	require('../../errorReporter.php');
    require('../../retrieveColumns.php');
    require('../../db/storeConnection.php');
    
    $search = array();
    $details = array();
    $_SESSION['post'] = preg_replace('/\s/', "%", $_SESSION['post']);

    if(isset($_SESSION['post']['transaction']) && $_SESSION['post']['transaction'] != "") $search[] = "PayPalTransactions.TransactionID LIKE '%".$_SESSION['post']['transaction']."%'";
    if(isset($_SESSION['post']['date']) && $_SESSION['post']['date'] != "") $search[] = "Created LIKE '%".$_SESSION['post']['date']."%'";

    $and = "AND COLUMN_NAME NOT IN('ID', 'TransactionsID')";
    $columns = retrieveColumns("PayerDetails", $and, $storeConn);
    foreach($columns as $col)
        if(isset($_SESSION['post'][$col]) && $_SESSION['post'][$col] != "") $details[] = "$col LIKE '%".$_SESSION['post'][$col]."%'";

    $transactionIDs = array();
    if(count($details) != 0){
        $where = implode(" AND ", $details);
        $qry = "SELECT TransactionsID FROM PayerDetails JOIN Transactions ON TransactionsID = Transactions.ID WHERE $where";
        $stmt = sqlsrv_query($storeConn, $qry);
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
        while($row = sqlsrv_fetch_array($stmt)){
            $transactionIDs[] = $row['TransactionsID'];
        }
    }

    $where = "";
    if(count($search) != 0)
        $where = implode(" AND ", $search);

    $sTable = "Transactions";
    $primaryKey = retrievePrimaryKeys($sTable, $storeConn);
    $sIndexColumn = $primaryKey[0];
    $and = "OR TABLE_NAME = 'PayPalTransactions' AND COLUMN_NAME IN('PayerID') OR TABLE_NAME = 'PayerDetails' AND COLUMN_NAME IN('FirstName', 'LastName') ORDER BY TABLE_NAME DESC";
    $cols = retrieveColumns($sTable, $and, $storeConn);

    $searchColumns = array();

    foreach($cols as $column)
        array_push($searchColumns, $column);

    $searchColumns = preg_replace('/^ID/', "$sTable.ID", $searchColumns);
    $searchColumns = preg_replace('/^TransactionID/', "PayPalTransactions.TransactionID", $searchColumns);
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
    $top = (isset($_GET['iDisplayStart']))?((int)$_GET['iDisplayStart']):0 ;
    $limit = (isset($_GET['iDisplayLength']))?((int)$_GET['iDisplayLength'] ):10;
    $iCurrentPage = ceil(($_GET['iDisplayStart']) / ($_GET['iDisplayLength']));
    $offset =  $iCurrentPage * $limit;

    $sjoin = "JOIN PayPalTransactions ON $sTable.TransactionID = PayPalTransactions.ID JOIN PayerDetails ON $sTable.ID = PayerDetails.TransactionsID";
    $sWhere = preg_replace('/^ID/', "$sTable.ID", $sWhere);
    $sWhere = preg_replace('/^TransactionID/', "PayPalTransactions.TransactionID", $sWhere);
   
    if(count($transactionIDs) != 0){
        $queries = array();
        $where = $sWhere;
        for($i = 0; $i < count($transactionIDs); $i++){
            $sWhere = (($sWhere === "")?" WHERE $sTable.ID LIKE '%" . $transactionIDs[$i] . "%' ":" $sWhere AND $sTable.ID LIKE '%".$transactionIDs[$i]."%' ");
            $queries[] = "SELECT TOP $limit " . implode($searchColumns, ", ") . " FROM $sTable $sjoin
                            $sWhere ".(($sWhere=="")?"WHERE ":" AND ")."$sTable.$sIndexColumn NOT IN (
                                SELECT $sIndexColumn FROM (
                                        SELECT TOP $top " . implode($searchColumns, ", ") . "
                                        FROM $sTable $sjoin $sWhere $sOrder ) AS [virtTable] )";
        }
        $ssQuery = implode(" UNION ", $queries);
        $ssQuery .= $sOrder;
    } else{
        $ssQuery = "SELECT TOP $limit " . implode($searchColumns, ", ") . " FROM $sTable $sjoin
                    $sWhere ".(($sWhere=="")?"WHERE ":" AND ")."$sTable.$sIndexColumn NOT IN (
                        SELECT $sIndexColumn FROM (
                                SELECT TOP $top " . implode($searchColumns, ", ") . "
                                FROM $sTable $sjoin $sWhere $sOrder ) AS [virtTable] )
                    $sOrder ";
    }
   
    $rResult = sqlsrv_query($storeConn, $ssQuery, array(), array("Scrollable" => "static"));
    if ($rResult === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__ . $ssQuery);

    $sQueryCnt = "SELECT * FROM $sTable $sjoin ".(($sWhere=="") ? " " : "$sWhere");
    $rResultCnt = sqlsrv_query($storeConn, $sQueryCnt, array(), array("Scrollable" => "static"));
    if ($rResultCnt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    $iFilteredTotal = sqlsrv_num_rows($rResultCnt);

    $sQuery = " SELECT DISTINCT COUNT( * ) AS ROW_COUNT FROM $sTable";
    $rResultTotal = sqlsrv_query($storeConn, $sQuery, array(), array("Scrollable" => "static"));
    if ($rResultTotal === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    $iTotal = sqlsrv_fetch_array($rResultTotal)['ROW_COUNT'];
    
    $output = array(
        "sEcho" => intval($_GET['sEcho']),
        "iTotalRecords" => $iTotal,
        "iTotalDisplayRecords" => $iFilteredTotal,
        "aaData" => array(),
        "error" => "S: " . $sWhere . " W:" . $where . " Q:" . $ssQuery
    );

    $searchColumns = preg_replace('/^Transactions.ID/', "ID", $searchColumns);
    $searchColumns = preg_replace('/^PayPalTransactions.TransactionID/', "TransactionID", $searchColumns);
    $searchColumns[] = "View";
    while ( $aRow = sqlsrv_fetch_array($rResult, SQLSRV_FETCH_ASSOC) ) {
        $row = array();
        
        for ( $i=0 ; $i<count($searchColumns) ; $i++ ) {
            if ( $searchColumns[$i] != ' ' && $searchColumns[$i] != "Total" && $searchColumns[$i] != "View" && $searchColumns[$i] != "Created" && $searchColumns[$i] != "Shipped" )
                $v = $aRow[ $searchColumns[$i] ];

            if($searchColumns[$i] === "Total")
                $v = "$" . $aRow[ $searchColumns[$i] ];

            if($searchColumns[$i] === "Created")
                $v = $aRow[ $searchColumns[$i] ]->format('Y-m-d');

             if($searchColumns[$i] === "Shipped")
                $v = $aRow[ $searchColumns[$i] ] == 1 ? "Yes" : "No";

            if($searchColumns[$i] === "View")
                $v = "<a href='transactionSummary.php?transaction=" . $aRow[ $searchColumns[1] ] . "'>View</a>";

            $v = mb_check_encoding($v, 'UTF-8') ? $v : utf8_encode($v);
            $row[]=$v;
        }
        if (!empty($row)) { $output['aaData'][] = $row; }
    }

    if (!isset($noJsonEcho) || !$noJsonEcho) echo json_encode($output);