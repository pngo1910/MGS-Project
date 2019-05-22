<?php
	ini_set('memory_limit', '-1'); //fixes a memory cap limit
	error_reporting(0);
	require('../db/storeConnection.php');
	require('../retrieveColumns.php');

	$name = isset($_GET['name'])? $_GET['name']: 'store';

    switch($name){
    	case 'login':
            require('../db/loginCheck.php');
            require('../db/memberConnection.php');
            require('../errorReporter.php');
            break;
        default:
            session_name($name);
            session_start();
    }

    $categoryIDs = array();
    $categoryNames = array();
	$catSql = "SELECT ID, Category FROM Category ORDER BY ID";
	$catStmt = sqlsrv_query($storeConn, $catSql); 
	if ($catStmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
 	while ($catRow = sqlsrv_fetch_array($catStmt)) {
 		$categoryIDs[] = $catRow['ID'];
 		$categoryNames[] = $catRow['Category'];
	}


	$category = isset($_POST['category'])? $_POST['category']: null;

	if (($category != 'all') && !empty($category)){
		$_SESSION['whereForCategory'] = $category;
	}
	else
	{
		unset($_SESSION['whereForCategory']);
	}

    if(!isset($_SESSION['message'])) $_SESSION['message'] = "";
	if(!isset($_SESSION['error'])) $_SESSION['error'] = "";
	if(!isset($_SESSION['values'])) $_SESSION['values'] = "";

	$sTable = "Products";

	
	if(isset($_SESSION['values']) && $_SESSION['values'] != "" && !empty($_SESSION['values'])){
		$total = 0;
		$holdvalues = array();
		for($i = 0; $i < count($_SESSION['values']); $i++){
			$holdvalues[] = $_SESSION['values'][$i];
			$i++;
			$holdvalues[] = $_SESSION['values'][$i];
			$total += $_SESSION['values'][$i];
		}
		$holdvalues = implode(",", $holdvalues);
	}

	//$and = "AND COLUMN_NAME NOT IN('ID', 'Shipping', 'Download', 'StatusCode')";
	$and = "AND COLUMN_NAME NOT IN('ID', 'Download', 'StatusCode') ";
	$aCol = retrieveColumns($sTable, $and, $storeConn);
	$aCol[] = "Add Product";

	header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache');
?>
<!DOCTYPE HTML>
<html lang="en-US">
	<head>
    	<meta charset="utf-8">
    	 <?php header('X-UA-Compatible: IE=edge,chrome=1');?>
    	<title>MGS Store</title> 
    	<meta name="description" content="">
    	<meta name="viewport" content="width=device-width">
    	<link rel="stylesheet" href="/css/normalize.css">
	    <link rel="stylesheet" href="/css/main.css">

	    <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables.css">
	    <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables.min.css">
	    <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables_themeroller.css">

	    <script src="/DataTables-1.10.6/media/js/jquery.js"></script>
	    <script src="/DataTables-1.10.6/media/js/jquery.dataTables.min.js"></script>
	    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
	    <script src="/DataTables-1.10.6/media/js/jquery.dataTables.js"></script>
		<script type='text/javascript'>
			 $(document).ready(function() { 
			   $('input[name=category]').change(function(){
			        $('form[name=category]').submit();
			   });
			  });
		</script>
    	<?php require('storeJS.php'); ?>
		<style>
			#example tfoot{
				display: table-header-group;
			}
		</style>
	</head>
	<body>
		<div id="resultsbackground">
	    	<div id="container" class="home">
	    		<?php require('../header.php'); ?>
	    		<?php if ($name === 'login')
	    			require('../balanceWidget.php'); ?>
				<div id="head">
					<p class="successColor"><?= $_SESSION['message'] ?></p>
					<p class = "errorColor"><?= $_SESSION['error'] ?></p>
					<?php $_SESSION['message'] = ""; ?>
					<?php $_SESSION['error'] = ""; ?>
					<h2 id="headerStore">e-Store Information</h2>

						<!-- new content foes here -->
						new content
								https://mani.mbgenealogy.com/member/searchsource.php
			</div>

		<?php require('../footer.php'); ?>
		</div>
	</body>
</html>