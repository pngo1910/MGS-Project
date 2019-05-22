<?php
    require('../../db/adminCheck.php');
    require('../../errorReporter.php');
    require('../../db/mgsConnection.php');
    require('../../retrieveColumns.php');

    $params = array();
    $options = array("Scrollable" => SQLSRV_CURSOR_KEYSET);

    $sTable = $_SESSION['table'];

    /* Primary Key Columns */
    $primaryKeys = retrievePrimaryKeys($sTable, $conn);

    /* Indexed column (used for fast and accurate table cardinality) */
    $sIndexColumn = $primaryKeys[0];

    $and = "AND COLUMN_NAME NOT IN ('CemLink', 'StatusCode')";
    $formatted_values = retrieveColumns($sTable, $and, $conn);

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
    $sWhere = "";
    if (isset($_GET['sSearch']) && $_GET['sSearch'] != '') {
        $sWhere = 'where (';

        for ($i=0; $i<count($searchColumns); $i++) {
            $sWhere .= $searchColumns[$i]." LIKE '%".addslashes($_GET['sSearch'])."%' or ";
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
    $offset = $iCurrentPage * $limit;

    $ssQuery = "SELECT TOP $limit ".implode(",",$searchColumns)." FROM $sTable $join
    $sWhere ".(($sWhere=="")?" WHERE ":" AND ")." $sTable.ID NOT IN (
      SELECT ID FROM (
        SELECT TOP $top ".implode(",",$searchColumns)."
        FROM $sTable $join $sWhere $sOrder ) as [virtTable] )
      $sOrder";

    $rResult = sqlsrv_query($conn, $ssQuery);

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

    //Retrieve the RecordID from payperview that's associated with the table the user chose
    $paysql = "SELECT RecordID FROM MGSTemp_Dev.dbo.PayPerView WHERE TableName = ?";
    $paystmt = sqlsrv_query($conn, $paysql, array($sTable), array("Scrollable" => "static"));
    if ($paystmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    $records = array();
    //Retrieve the RecordID(s)
    while($payrow = sqlsrv_fetch_array($paystmt))
        $records[] = $payrow['RecordID'];

    //Add 'Add' to the arrays
    $formatted_values[] = "Add";
    $searchColumns[] = "Add";
    while ( $aRow = $aRow = sqlsrv_fetch_array($rResult)) {
        $row = array();
        for ($i=0; $i<count($searchColumns); $i++) {
            if ($formatted_values[$i] != ' ' && $formatted_values[$i] != 'Add') {
                $v = $aRow[ $formatted_values[$i] ];

            }

            if ($formatted_values[$i] == 'Add'){
                $match = false;
                //Check that $records isn't empty to avoid entering the loop
                if($records != ""){
                    //Loop through the returned records
                    for($j = 0; $j < count($records); $j++){
                        //Check if RecordID matches the ID
                        if($records[$j] == $aRow[ $formatted_values[0] ]){
                            $v = "Has a file";
                            $match = true;
                            //End the loop
                            $j = count($records);
                        }
                    }
                }

                if(!$match)
                    $v = "<a href='addPayPerView.php?id=".$aRow[ $formatted_values[0] ]."&amp;tablename=$sTable'>Add";
             }

            $v = mb_check_encoding($v, 'UTF-8') ? $v : utf8_encode($v);
            $row[] = $v;
        }

        if (!empty($row))
            $output['aaData'][] = $row;
    }

    echo json_encode( $output );
?>