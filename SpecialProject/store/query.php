<?php 
    require('../../db/adminCheck.php');
    require('../../db/adminConnection.php');
    require('../../errorReporter.php');
    require('../../retrieveColumns.php');
    
    $sTable = $_SESSION['storetable'];
    $params = array();
    $options = array("Scrollable" => SQLSRV_CURSOR_KEYSET);

    if($sTable === 'products'){
        //$and = "AND COLUMN_NAME NOT IN('Category', 'StatusCode') OR TABLE_NAME = 'Category' AND COLUMN_NAME IN ('Category') ORDER BY TABLE_NAME DESC;";
        $columns = retrieveColumns($sTable, 0, $conn);
        $arrayOfColumns = array();
        $sArrayOfColumns = array();

        foreach ($columns as $val){
            if($val == "ID")
                array_push($sArrayOfColumns, "Products.".$val." AS 'ID'");
            elseif($val == "Category")
                array_push($sArrayOfColumns, "Category.Category");
            elseif($val == "StatusCode")
                array_push($sArrayOfColumns, "Products.StatusCode");
            else
                array_push($sArrayOfColumns, $val);

            array_push($arrayOfColumns, $val);
        }

        $searchColumns = $arrayOfColumns;
    } else {
        $searchColumns = retrieveColumns($sTable, 0, $conn);

    }           
    $sjoin = '';

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
       
    //* Filtering */
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
    $top = (isset($_GET['iDisplayStart']))?((int)$_GET['iDisplayStart']):0 ;
    $limit = (isset($_GET['iDisplayLength']))?((int)$_GET['iDisplayLength'] ):10;
    $iCurrentPage = ceil(($_GET['iDisplayStart']) / ($_GET['iDisplayLength'])) ;
    $offset =  $iCurrentPage * $limit; 

    if($sTable === 'products'){
        $sWhere = preg_replace('/ID/', "Products.ID", $sWhere);
        $sjoin = "JOIN Category ON Products.Category = Category.ID";

        $sQuery = "SELECT TOP $limit ".implode(",",$sArrayOfColumns).", 'EDIT' AS 'EDIT', 'DEL' AS 'DEL' FROM $sTable
            $sjoin $sWhere ".(($sWhere=="")?" WHERE ":" AND ")." Products.ID NOT IN (
                SELECT ID FROM (
                    SELECT TOP $top ".implode(",",$sArrayOfColumns)."
                    FROM $sTable $sjoin $sWhere $sOrder ) as [virtTable] )
            $sOrder";
    } else {
        $sQuery = "SELECT TOP $limit ".implode(",",$searchColumns).", 'EDIT' AS 'EDIT', 'DEL' AS 'DEL' FROM $sTable
            $sWhere ".(($sWhere=="")?" WHERE ":" AND ")." ID NOT IN (
                SELECT ID FROM (
                    SELECT TOP $top ".implode(",",$searchColumns)."
                    FROM $sTable $sWhere $sOrder ) as [virtTable] )
            $sOrder";
    }

    $rResult = sqlsrv_query($conn, $sQuery);
    if ($rResult === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    $sQueryCnt = "SELECT * FROM $sTable $sWhere";
    $rResultCnt = sqlsrv_query($conn, $sQueryCnt, $params, $options);
    if ($rResultCnt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    $iFilteredTotal = sqlsrv_num_rows($rResultCnt);

    $sQuery = " SELECT DISTINCT COUNT( * ) AS ROW_COUNT FROM $sTable";
    $rResultTotal = sqlsrv_query($conn, $sQuery, $params, $options);
    if ($rResultTotal === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    $iTotal = sqlsrv_fetch_array($rResultTotal)['ROW_COUNT'];
    
    $output = array(
        "sEcho" => intval($_GET['sEcho']),
        "iTotalRecords" => $iTotal,
        "iTotalDisplayRecords" => $iFilteredTotal,
        "aaData" => array()
    );
       
    while ( $aRow = sqlsrv_fetch_array($rResult) ) {
        $row = array();
        for ( $i=0 ; $i<count($searchColumns) ; $i++ ) {
            if ( $searchColumns[$i] == 'Shipping' && $aRow[ $searchColumns[$i] ] != "" || $searchColumns[$i] == 'Price' && $aRow[ $searchColumns[$i] ] != "" )
                $v = "$".$aRow[ $searchColumns[$i] ];
            elseif ( $searchColumns[$i] == 'Shipping' && $aRow[ $searchColumns[$i] ] == "" || $searchColumns[$i] == 'Price' && $aRow[ $searchColumns[$i] ] == "" )
                $v = "";
            elseif( $searchColumns[$i] == 'Download')
                $v = $aRow[ $searchColumns[$i] ] . (($aRow[ $searchColumns[$i] ] == 0) ? " (No)" : " (Yes)");
            else
                $v = $aRow[ $searchColumns[$i] ];
            
            $v = mb_check_encoding($v, 'UTF-8') ? $v : utf8_encode($v);
            $row[]=$v;
        }

        foreach ($row as $key => $value) {
            if($key == 'EDIT'){
                $val = "<a href='edit.php?tablename=$sTable&amp;id=".$aRow['ID']."'>Edit"; 
                $row[] = $val;                   
            }
            if($key == 'DEL'){
                global $id;
                $id = $aRow['ID'];
                $val = "<a href="."javascript:confirmDelete('$sTable','$id');".">Delete";
                $row[] = $val;      
            }
        }
           
        If (!empty($row)) { $output['aaData'][] = $row; }
    } 
      
    echo json_encode( $output );
?>