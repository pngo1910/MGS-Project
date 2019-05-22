<?php
    require('../../db/adminCheck.php');
	require('../../errorReporter.php');
    require('../../retrieveColumns.php');
    require('../../db/storeConnection.php');
    
    $search = array();
    $_SESSION['post'] = preg_replace('/\s/', "%", $_SESSION['post']);
    //$_SESSION['post'] = preg_replace('/\s/', "%", $_POST);

    if(!empty($_SESSION['post']['productname'])) $search[] = "Products.Name LIKE '%".$_SESSION['post']['productname']."%'";

    if(!empty($_SESSION['post']['description'])) $search[] = "Products.Description LIKE '%".$_SESSION['post']['description']."%'";

    if(!empty($_SESSION['post']['price'])) $search[] = "TransactionDetails.PurchasePrice LIKE '%". str_replace("$","",$_SESSION['post']['price'])."%'";

    if(!empty($_SESSION['post']['category'])) $search[] = "Category.Category LIKE '%".$_SESSION['post']['category']."%'";

    if(!empty($_SESSION['post']['quantity'])) $search[] = "TransactionDetails.Quantity LIKE '%".$_SESSION['post']['quantity']."%'";

    if(!empty($_SESSION['post']['firstname'])) $search[] = "Transactions.FirstName LIKE '%".$_SESSION['post']['firstname']."%'";

    if(!empty($_SESSION['post']['lastname'])) $search[] = "Transactions.LastName LIKE '%".$_SESSION['post']['lastname']."%'";

    if(!empty($_SESSION['post']['address'])) $search[] = "Transactions.Address LIKE '%".$_SESSION['post']['address']."%'";

    if(!empty($_SESSION['post']['city'])) $search[] = "Transactions.City LIKE '%".$_SESSION['post']['city']."%'";

    if(!empty($_SESSION['post']['province'])) $search[] = "Transactions.Province LIKE '%".$_SESSION['post']['province']."%'";

    if(!empty($_SESSION['post']['postalcode'])) $search[] = "Transactions.PostalCode LIKE '%".$_SESSION['post']['postalcode']."%'";

    if(!empty($_SESSION['post']['date'])) $search[] = "CONVERT(VARCHAR, Transactions.Created, 120) LIKE '%".$_SESSION['post']['date']."%'";

    if(!empty($_SESSION['post']['email'])) $search[] = "Transactions.Email LIKE '%".$_SESSION['post']['email']."%'";

    $where = "";
    if(count($search) != 0)
        $where = implode(" AND ", $search);

    $sTable = "Transactions";
    $primaryKey = retrievePrimaryKeys($sTable, $storeConn);
    $sIndexColumn = $primaryKey[0];
    $and = "OR TABLE_NAME = 'PayPalTransactions' AND COLUMN_NAME IN('PayerID') ORDER BY TABLE_NAME DESC";
    $cols = retrieveColumns($sTable, $and, $storeConn);

    $searchColumns = array();

    foreach($cols as $column)
        array_push($searchColumns, $column);

    $searchColumns = preg_replace('/^ID/', "$sTable.ID", $searchColumns);
    $searchColumns = preg_replace('/^TransactionID/', "$sTable.TransactionID", $searchColumns);
    $searchColumns = preg_replace('/^PayerID/', "PayPalTransactions.PayerID", $searchColumns);
    /* Ordering */
    $sOrder = "";
    if (isset($_GET['iSortCol_0'])) {
        $sOrder = "ORDER BY  ";
        for ($i=0; $i<intval($_GET['iSortingCols']); $i++) {
            if ($_GET['bSortable_'.intval($_GET["iSortCol_$i"])] == "true" && intval($_GET["iSortCol_$i"])>0) {
                $sOrder .= $searchColumns[$_GET["iSortCol_$i"]-1].' '
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
    $top = (isset($_GET['iDisplayStart']))?((int)$_GET['iDisplayStart']):0;
    $limit = (isset($_GET['iDisplayLength']))?((int)$_GET['iDisplayLength'] ):10;
    $iCurrentPage = ceil(($_GET['iDisplayStart']) / ($_GET['iDisplayLength']));
    $offset =  $iCurrentPage * $limit;

    $sjoin = "LEFT JOIN TransactionDetails ON Transactions.ID = TransactionDetails.TransactionsID
                JOIN Products ON Products.ID = TransactionDetails.ItemID
                JOIN Category ON Category.ID = Products.Category";

    $sWhere = preg_replace('/^ID/', "$sTable.ID", $sWhere);
    $sWhere = preg_replace('/^TransactionID/', "$sTable.TransactionID", $sWhere);

    $ssQuery = "SELECT TOP $limit " . implode($searchColumns, ", ") . " FROM $sTable JOIN PayPalTransactions ON 
                PayPalTransactions.ID = Transactions.TransactionID WHERE $sTable.$sIndexColumn IN 
                (SELECT $sTable.$sIndexColumn FROM $sTable $sjoin $sWhere ". (($sWhere=="") ? (($where=="")? "" : "WHERE $where") : (($where=="")? "" : " AND $where")) . ") AND $sTable.$sIndexColumn  NOT IN(
                    SELECT $sIndexColumn FROM (
                            SELECT TOP $top $sTable.$sIndexColumn 
                            FROM $sTable $sjoin $sWhere) AS [virtTable] )
                 $sOrder ";

    //echo $ssQuery;

    $rResult = sqlsrv_query($storeConn, $ssQuery, array(), array("Scrollable" => "static"));
    if ($rResult === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__.$sQuery);

    $sQueryCnt = "SELECT * FROM $sTable ".(($sWhere=="") ? " " : "$sWhere");
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
        "error" => "S: " . $sWhere . " W: " . $where . " Q: " . $ssQuery
    );

    $searchColumns = preg_replace('/^Transactions.ID/', "ID", $searchColumns);
    $searchColumns = preg_replace('/^Transactions.TransactionID/', "TransactionID", $searchColumns);
    $searchColumns = preg_replace('/^PayPalTransactions.PayerID/', "PayerID", $searchColumns);
    $searchColumns[] = "View";

    while ( $aRow = sqlsrv_fetch_array($rResult, SQLSRV_FETCH_ASSOC) ) {
        $row = array();
        $row[] = "<input class='all' type='checkbox' name='check[]' value='".$aRow[$sIndexColumn]."'>";
        
        for ( $i=0 ; $i<count($searchColumns) ; $i++ ) {
            if ( $searchColumns[$i] != ' ' && $searchColumns[$i] != "ShippingFee" && $searchColumns[$i] != "Total" && $searchColumns[$i] != "View" && $searchColumns[$i] != "Checked" && $searchColumns[$i] != "Created")
                $v = $aRow[ $searchColumns[$i] ];
            if($searchColumns[$i] === "ShippingFee" || $searchColumns[$i] === "Total")
                $v = "$" . $aRow[ $searchColumns[$i] ];
            if($searchColumns[$i] === "Created")
                $v = $aRow[ $searchColumns[$i] ]->format('Y-m-d');
            if($searchColumns[$i] === "Checked")
            	if(!empty($aRow[ $searchColumns[$i] ]))
                	$v = "yes";
                else
                	$v = "no";

            if($searchColumns[$i] === "View")
                $v = "<a href='purchaseSummary.php?purchase=" . $aRow[ $searchColumns[0] ] . "'>View</a>";
                //$v = "<a href='purchaseDetailsQuery.php?purchase=" . $aRow[ $searchColumns[0] ] . "'>View</a>";
                //$v = $sssSort5;
            $v = mb_check_encoding($v, 'UTF-8') ? $v : utf8_encode($v);
            $row[]=$v;
        }
        if (!empty($row)) { $output['aaData'][] = $row; }
    }

    if (!isset($noJsonEcho) || !$noJsonEcho) echo json_encode($output);