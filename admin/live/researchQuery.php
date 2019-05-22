<?php
    require('../../db/adminCheck.php');
	require('../../errorReporter.php');
    require('../../retrieveColumns.php');
    require('../../db/memberConnection.php');
    
    $search = array();
    $_SESSION['post'] = preg_replace('/\s/', "%", $_SESSION['post']);
    //$_SESSION['post'] = preg_replace('/\s/', "%", $_POST);

    if(!empty($_SESSION['post']['surname'])) $search[] = "ResearchDetails.Surname LIKE '%".$_SESSION['post']['surname']."%'";

    if(!empty($_SESSION['post']['givenname'])) $search[] = "ResearchDetails.GivenName LIKE '%".$_SESSION['post']['givenname']."%'";

    if(!empty($_SESSION['post']['description'])) $search[] = "ResearchDetails.Description LIKE '%". str_replace("$","",$_SESSION['post']['description'])."%'";

    if(!empty($_SESSION['post']['locations'])) $search[] = "ResearchDetails.Locations LIKE '%".$_SESSION['post']['locations']."%'";

    if(!empty($_SESSION['post']['package'])) $search[] = "TransactionDetails.Name LIKE '%".$_SESSION['post']['package']."%'";

    if(!empty($_SESSION['post']['firstname'])) $search[] = "FirstName LIKE '%".$_SESSION['post']['firstname']."%'";

    if(!empty($_SESSION['post']['lastname'])) $search[] = "LastName LIKE '%".$_SESSION['post']['lastname']."%'";

    if(!empty($_SESSION['post']['address'])) $search[] = "Address LIKE '%".$_SESSION['post']['address']."%'";

    if(!empty($_SESSION['post']['city'])) $search[] = "City LIKE '%".$_SESSION['post']['city']."%'";

    if(!empty($_SESSION['post']['province'])) $search[] = "Province LIKE '%".$_SESSION['post']['province']."%'";

    if(!empty($_SESSION['post']['postalcode'])) $search[] = "PostalCode LIKE '%".$_SESSION['post']['postalcode']."%'";

    if(!empty($_SESSION['post']['date'])) $search[] = "CONVERT(VARCHAR, Transactions.Created, 120) LIKE '%".$_SESSION['post']['date']."%'";

    if(!empty($_SESSION['post']['email'])) $search[] = "Email LIKE '%".$_SESSION['post']['email']."%'";

    $where = "";
    if(count($search) != 0)
        $where = implode(" AND ", $search);

    $sTable = "Transactions";
    $primaryKey = retrievePrimaryKeys($sTable, $userConn);
    $sIndexColumn = $primaryKey[0];
    $and = "OR TABLE_NAME = 'PayPalTransactions' AND COLUMN_NAME IN('PayerID') ORDER BY TABLE_NAME DESC";
    $cols = retrieveColumns($sTable, $and, $userConn);

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

    $sjoin1 = "LEFT JOIN TransactionDetails ON Transactions.ID = TransactionDetails.TransactionsID
                JOIN ResearchDetails ON ResearchDetails.TransactionsID = Transactions.ID
                JOIN MemberInfo ON Transactions.MemberNum = MemberInfo.MemberNum
                        AND Transactions.MemberTable = 'MemberInfo'";

    $sjoin2 = "LEFT JOIN TransactionDetails ON Transactions.ID = TransactionDetails.TransactionsID
                JOIN ResearchDetails ON ResearchDetails.TransactionsID = Transactions.ID
                JOIN UnloggedInUserInfo ON Transactions.MemberNum = UnloggedInUserInfo.MemberNum
                        AND Transactions.MemberTable = 'UnloggedInUserInfo'";

    $sWhere = preg_replace('/^ID/', "$sTable.ID", $sWhere);
    $sWhere = preg_replace('/^TransactionID/', "$sTable.TransactionID", $sWhere);

    $ssQuery = "SELECT TOP $limit " . implode($searchColumns, ", ") . " FROM $sTable JOIN PayPalTransactions ON 
                PayPalTransactions.ID = Transactions.TransactionID WHERE $sTable.$sIndexColumn IN 
                (SELECT $sTable.$sIndexColumn FROM $sTable $sjoin1 $sWhere ". (($sWhere=="") ? (($where=="")? "" : "WHERE $where") : (($where=="")? "" : " AND $where")) . " UNION SELECT $sTable.$sIndexColumn FROM $sTable $sjoin2 $sWhere ". (($sWhere=="") ? (($where=="")? "" : "WHERE $where") : (($where=="")? "" : " AND $where")) . ") AND $sTable.$sIndexColumn  NOT IN(
                    SELECT $sIndexColumn FROM (
                            SELECT TOP $top $sTable.$sIndexColumn 
                            FROM $sTable $sjoin1 $sWhere UNION SELECT TOP $top $sTable.$sIndexColumn 
                            FROM $sTable $sjoin2 $sWhere) AS [virtTable] )
                 $sOrder ";

    $rResult = sqlsrv_query($userConn, $ssQuery, array(), array("Scrollable" => "static"));
    if ($rResult === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__.$sQuery);

    $sQueryCnt = "SELECT * FROM $sTable ".(($sWhere=="") ? " " : "$sWhere");
    $rResultCnt = sqlsrv_query($userConn, $sQueryCnt, array(), array("Scrollable" => "static"));
    if ($rResultCnt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    $iFilteredTotal = sqlsrv_num_rows($rResultCnt);

    $sQuery = " SELECT DISTINCT COUNT( * ) AS ROW_COUNT FROM $sTable";
    $rResultTotal = sqlsrv_query($userConn, $sQuery, array(), array("Scrollable" => "static"));
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
                $v = "<a href='researchSummary.php?research=" . $aRow[ $searchColumns[0] ] . "'>View</a>";
                //$v = "<a href='purchaseDetailsQuery.php?purchase=" . $aRow[ $searchColumns[0] ] . "'>View</a>";
                //$v = $ssQuery;
            $v = mb_check_encoding($v, 'UTF-8') ? $v : utf8_encode($v);
            $row[]=$v;
        }
        if (!empty($row)) { $output['aaData'][] = $row; }
    }

    if (!isset($noJsonEcho) || !$noJsonEcho) echo json_encode($output);