<?php
	require('../errorReporter.php');
    require('../retrieveColumns.php');
    require('../db/storeConnection.php');
    $name = isset($_GET['name'])? $_GET['name']: 'store';
    switch($name){
        case 'login':
            require('../db/loginCheck.php');
            require('../db/memberConnection.php');
            break;
        default:
            session_name($name);
            session_start();
    }

    $sTable = "Products";
    if(isset($_GET['municipality'])){
        $municipality = $_GET['municipality'];
        $sTable = "CemeteryTranscriptions";
    }

    //$and = "AND COLUMN_NAME NOT IN('ID', 'Shipping', 'Download', 'StatusCode')";
    $and = "AND COLUMN_NAME NOT IN('ID', 'Download', 'StatusCode')";
    $cols = retrieveColumns($sTable, $and, $storeConn);
    //$primaryKey = retrievePrimaryKeys($sTable, $storeConn)[0];
    $sIndexColumn = "$sTable.ID";

    //This array is for the select statement since a JOIN is going to be used
    $sArrayOfColumns = array();
    //This array is for everywhere else the columns are needed
    $searchColumns = array();
    array_push($searchColumns, 'ID');
    array_push($sArrayOfColumns, $sIndexColumn);
    foreach($cols as $column) {
        if($column === 'Category'){
            array_push($sArrayOfColumns, "Category.$column AS 'Category'");
        }else{
            array_push($sArrayOfColumns, $column);
        }
       array_push($searchColumns, $column);
    }

    /* Ordering */
    $sOrder = "";
    if (isset($_GET['iSortCol_0'])) {
        $sOrder = "ORDER BY  ";
        for ($i=0; $i<intval($_GET['iSortingCols']); $i++) {
            if ($_GET['bSortable_'.intval($_GET["iSortCol_$i"])] == "true") {
                $sOrder .= $searchColumns[intval($_GET["iSortCol_$i"])+1].' '
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

        $sWhere = substr_replace($sWhere, '', -3);
        $sWhere .= ')';
    }

    /* Individual column filtering */
    for ($i = 0; $i < count($searchColumns); $i++) {
        if (isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )  {
          $sWhere .= (($sWhere == '') ? ' where ' : ' and ');
          $sWhere .= $searchColumns[$i+1]." LIKE '%".addslashes($_GET['sSearch_'.$i])."%' ";
        }
    }
    
    /* Paging */
    $top = (isset($_GET['iDisplayStart']))?((int)$_GET['iDisplayStart']):0 ;
    $limit = (isset($_GET['iDisplayLength']))?((int)$_GET['iDisplayLength'] ):10;
    $iCurrentPage = ceil(($_GET['iDisplayStart']) / ($_GET['iDisplayLength']));
    $offset =  $iCurrentPage * $limit; 

    $sjoin = "";
    if($sTable === "Products"){
        //Because there will be a join, what needs to be done is the table name has to be put in front of ID or else there will be an ambiguous
        //column error.
        $sWhere = preg_replace('/ID/', "Products.ID", $sWhere);
        $sWhere = preg_replace('/Category/', "Category.Category", $sWhere);

        /*add for storeTest*/
        if (isset($_SESSION['whereForCategory'])) {
            if (!empty($sWhere)) {
                $sWhere .= 'AND Category.ID = ' . $_SESSION["whereForCategory"];
            }
            else{
                $sWhere = 'WHERE Category.ID = ' . $_SESSION["whereForCategory"];
            }
        }
        /*add for storeTest*/

        $sjoin = "JOIN Category ON Products.Category = Category.ID";

        $ssQuery = "SELECT TOP $limit " . implode($sArrayOfColumns, ", ") . " FROM $sTable
                $sjoin $sWhere ".(($sWhere=="")?" WHERE ":" AND ")." $sIndexColumn NOT IN 
                (
                    SELECT ID FROM 
                    (
                            SELECT TOP $top " . implode($sArrayOfColumns, ", ") . "
                            FROM products
                            $sjoin 
                            $sWhere 
                            $sOrder
                    ) 
                    AS [virtTable]
                )
                $sOrder ";
        $sQueryCnt = "SELECT * FROM $sTable $sjoin ".(($sWhere=="") ? "" : "$sWhere");
    } else{
        $and = "Municipality LIKE '%$municipality%'";
        $ssQuery = "SELECT TOP $limit " . implode($sArrayOfColumns, ", ") . " FROM $sTable
                $sjoin $sWhere ".(($sWhere=="")?" WHERE $and AND ":" AND $and AND ")." $sIndexColumn NOT IN 
                (
                    SELECT ID FROM 
                    (
                            SELECT TOP $top " . implode($sArrayOfColumns, ", ") . "
                            FROM products
                            $sjoin 
                            ".(($sWhere =="")?"WHERE $and" : "$sWhere AND $and")."
                            $sOrder
                    ) 
                    AS [virtTable]
                )
                $sOrder ";
        $sQueryCnt = "SELECT * FROM $sTable $sjoin ".(($sWhere=="") ? "WHERE $and" : "$sWhere AND $and");
    }
                
    $rResult = sqlsrv_query($storeConn, $ssQuery, array(), array("Scrollable" => "static"));
    if ($rResult === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

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
        "aaData" => array()
    );

    //The Add Product column is added here so it doesn't interfere with the paging, ordering, etc.
    array_push($searchColumns, "Add Product");
    while ( $aRow = sqlsrv_fetch_array($rResult, SQLSRV_FETCH_ASSOC) ) {
        $row = array();
        
        for ( $i=1 ; $i<count($searchColumns) ; $i++ ) {
            
            if ( $searchColumns[$i] != ' ' && $searchColumns[$i] != "Add Product" && $searchColumns[$i] != "Price" && $searchColumns[$i] != "Shipping")
                $v = $aRow[ $searchColumns[$i] ];

            if($searchColumns[$i] === "Price" || $searchColumns[$i] === "Shipping")
                $v = "$" . $aRow[ $searchColumns[$i] ];

            if($searchColumns[$i] === "Add Product")
                $v = "<label for='" . $aRow[ $searchColumns[0] ] . "'>Add: </label><input onchange='holdProducts(this.getAttribute(\"id\"), this.value)' type='number' name='" . $aRow[ $searchColumns[0] ] . "' id='" . $aRow[ $searchColumns[0] ] . "' class='products' value='0' min='0' />";

            $v = mb_check_encoding($v, 'UTF-8') ? $v : utf8_encode($v);
            $row[]=$v;
        }
        if (!empty($row)) { $output['aaData'][] = $row; }
    }

    if (!isset($noJsonEcho) || !$noJsonEcho) echo json_encode($output);