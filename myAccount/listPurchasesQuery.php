<?php
    require('../db/loginCheck.php');
    require('../db/memberConnection.php');
    require('../db/mgsConnection.php');
    require('../errorReporter.php');
    require('../retrieveColumns.php');

    $username = $_SESSION['uname'];
    //Get the names of the tables the user has purchased records from
    $sql = "SELECT TableName FROM Purchases WHERE MemberID = (SELECT MemberNum FROM Accounts.dbo.Members WHERE Username = ?)";
    $stmt = sqlsrv_query($conn, $sql, array($username), array("Scrollable" => "static"));
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    $tableNames = array();
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))
        $tableNames[] = $row['TableName'];

    //The columns being retrieved are all the same in the tables being searched for in memberResult so I just chose one of the tables 
    $and = "AND COLUMN_NAME NOT IN('BookCode', 'PageNumbers', 'EventYear', 'Info', 'TypeCode', 'StatusCode') OR TABLE_NAME = 'Purchases' AND COLUMN_NAME IN('FileName', 'PageNum')";
    $columns = retrieveColumns('BookRecords', $and, $conn);

    //This is needed for the select statements because a join will be used
    $sArrayOfColumns = array();
    //This is used for ordering, filtering, etc.
    $searchColumns = array();

    foreach($columns as $column) {
        if($column != 'ID')
            array_push($sArrayOfColumns, $column);

        array_push($searchColumns, $column);
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

    //This array is for the queries selecting the information from the tables
    $queries = array();
    //This array is for iTotalDisplayRecords
    $sQueriesCnt = array();    
    $values = array();
    //Loop through the table names
    for($i = 0; $i < count($tableNames); $i++){
        //Because there will be a join, what needs to be done is the table name has to be put in front of ID or else there will be an ambiguous
        //column error.
        $where = preg_replace('/ID/', $tableNames[$i].".ID", $sWhere);
        $sjoin = "JOIN Purchases ON " . $tableNames[$i] . ".ID = Purchases.RecordID";

        $queries[] = "SELECT TOP $limit " . $tableNames[$i] . ".ID, " . implode($sArrayOfColumns, ", ") . " FROM " . $tableNames[$i] . " 
                $sjoin $where ".(($where == "")?"WHERE ":"AND ") . $tableNames[$i] . ".ID NOT IN (
                    SELECT ID FROM 
                    (
                        SELECT TOP $top " . $tableNames[$i] . ".ID, " . implode($sArrayOfColumns, ", ") . " FROM " . $tableNames[$i] . " $sjoin $where $sOrder
                    ) AS [virtTable] )
                AND Purchases.MemberID = (SELECT MemberNum FROM Accounts.dbo.Members WHERE Username = ?)";

        $values[] = $username;
        // $sQueriesCnt[] = "SELECT " . $tableNames[$i] . ".ID, " . implode($sArrayOfColumns, ", ") . " FROM " . $tableNames[$i] . " $sjoin $where";
        $sQueriesCnt[] = "SELECT " . $tableNames[$i] . ".ID, " . implode($sArrayOfColumns, ", ") . " FROM " . $tableNames[$i] . " $sjoin ".
                        (($where == "")?"WHERE Purchases.MemberID = (SELECT MemberNum FROM Accounts.dbo.Members WHERE Username = ?)": "$where AND Purchases.MemberID = (SELECT MemberNum FROM Accounts.dbo.Members WHERE Username = ?)");
    }
    $sQuery = implode(" UNION ", $queries);

    if($sQuery != ''){
        //When using UNION, it doesn't like having more than one ORDER BY so it's added to the query at the end
        $sQuery .= $sOrder;
       
        $rResult = sqlsrv_query($conn, $sQuery, $values, array("Scrollable" => "static"));
        if ($rResult === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    
        $sQueryCnt = implode( " UNION ", $sQueriesCnt);
        
        $rResultCnt = sqlsrv_query($conn, $sQueryCnt, $values, array("Scrollable" => "static"));
        if ($rResultCnt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
        $iFilteredTotal = sqlsrv_num_rows($rResultCnt);

        $sQueryTotal = "SELECT DISTINCT COUNT( * ) AS ROW_COUNT FROM Purchases WHERE Purchases.MemberID = (SELECT MemberNum FROM Accounts.dbo.Members WHERE Username = ?)";
        $rResultTotal = sqlsrv_query($conn, $sQueryTotal, array($username), array("Scrollable" => "static"));
        if ($rResultTotal === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
        $iTotal = sqlsrv_fetch_array($rResultTotal)['ROW_COUNT'];

        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array(),
            "error" => $sQueryCnt
        );
    } else{
        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "aaData" => array()
        );
    }

    array_push($searchColumns, "View");
    if($sQuery != ''){
        while ( $aRow = sqlsrv_fetch_array($rResult, SQLSRV_FETCH_ASSOC)) {
            $row = array();
            
            for ( $i=0 ; $i<count($searchColumns) ; $i++ ) {

                if ( $searchColumns[$i] != ' ' && $searchColumns[$i] != "View")
                    $v = $aRow[ $searchColumns[$i] ];
                
                if($searchColumns[$i] === "View"){
                    if($aRow[ $searchColumns[4] ] != '')
                        $v = "<button onclick=\"view('".$aRow[$searchColumns[3]]."', ".$aRow[$searchColumns[4]].")\">View</button>";
                    else
                        $v = "<button onclick=\"view('".$aRow[$searchColumns[3]]."', null)\">View</button>";
                }

                $v = mb_check_encoding($v, 'UTF-8') ? $v : utf8_encode($v);
                $row[]=$v;
            }
            if (!empty($row)) { $output['aaData'][] = $row; }
        }
    }

    if (!isset($noJsonEcho) || !$noJsonEcho) echo json_encode($output);