<?php
    require('../../db/adminCheck.php');
	require('../../errorReporter.php');
    require('../../retrieveColumns.php');
    require('../../db/storeConnection.php');

    //$_SESSION['purchase'] = $_GET['purchase'];
    $sTable = 'TransactionDetails';
    $primaryKey = retrievePrimaryKeys($sTable, $storeConn);

    $sIndexColumn = $primaryKey[0];
    $where = "$sTable.TransactionsID = " . $_SESSION['purchase'];
    
    $and = "AND COLUMN_NAME NOT IN ('ID', 'TransactionsID', 'ItemID') OR TABLE_NAME = 'Products' AND COLUMN_NAME NOT IN ('ID', 'Price', 'Shipping', 'Download', 'StatusCode', 'Category')";

    $aCols = retrieveColumns($sTable, $and, $storeConn);
    $aCols[] = "Category.Category";

    $searchColumns = array();

    foreach($aCols as $column)
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
    $sWhere = $_GET['sSearch'] != ""? "" : "WHERE $sTable.TransactionsID = " . $_SESSION['purchase'];
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

    $aJoin = "JOIN Products ON TransactionDetails.ItemID = Products.ID
                JOIN Category ON Products.Category = Category.ID";
    $qryDetails = "SELECT TOP $limit " . implode($searchColumns, ", ") . " FROM $sTable $aJoin $sWhere ".(($sWhere=="")?"WHERE $where AND ":" AND $where AND ")."$sTable.$sIndexColumn NOT IN 
                (
                    SELECT $sTable.$sIndexColumn FROM 
                    (
                            SELECT TOP $top " . implode($searchColumns, ", ") . "
                            FROM $sTable $aJoin
                            $sWhere ".(($sWhere=="")?"WHERE $where":" AND $where ")."
                            $sOrder
                    ) 
                    AS [virtTable]
                )
                $sOrder ";
    
    $rResult = sqlsrv_query($storeConn, $qryDetails, array(), array("Scrollable" => "static"));
    if($rResult === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    // $qryDetailsNum = "SELECT TOP 10 " . implode($aCols, ", ") . " FROM TransactionDetails $aJoin WHERE TransactionsID = ?";
    // $rResultNum = sqlsrv_query($storeConn, $qryDetailsNum, array($_SESSION['purchase']), array("Scrollable" => "static"));
    // if($rResultNum === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    $iFilteredTotal = sqlsrv_num_rows($rResult);
    
    $output = array(
        "sEcho" => intval($_GET['sEcho']),
        "iTotalRecords" => $iFilteredTotal,
        "iTotalDisplayRecords" => $iFilteredTotal,
        "aaData" => array()
    );

    $searchColumns = preg_replace('/^Category.Category/', "Category", $searchColumns);

    while ( $aRow = sqlsrv_fetch_array($rResult, SQLSRV_FETCH_ASSOC) ) {
        $row = array();
        
        for ( $i=0 ; $i<count($searchColumns) ; $i++ ) {
            
            if ( $searchColumns[$i] != ' ' && $searchColumns[$i] != "PurchasePrice" )
                $v = $aRow[ $searchColumns[$i] ];

            if($searchColumns[$i] === "PurchasePrice")
                $v = "$" . $aRow[ $searchColumns[$i] ];

            $v = mb_check_encoding($v, 'UTF-8') ? $v : utf8_encode($v);
            $row[]=$v;
        }
        if (!empty($row)) { $output['aaData'][] = $row; }
    }

    if (!isset($noJsonEcho) || !$noJsonEcho) echo json_encode($output);