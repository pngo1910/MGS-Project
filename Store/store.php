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
					<h2 id="headerStore">MGS Store</h2>
				<aside>
					<form name="category" action="store.php?name=<?= $name ?>" method="post">
						<div id="filter" style="float:left;">
							
<strong> Directions: </strong> Click on one of the radio buttons to select from: Books, CD, DVD, Booklets, Cemetery Transcriptions or Digital Downloads. <p>
When you find an item you want either use the up arrow in the add box or replace the 0 with the number of items you want. You must then click the "ADD TO CART" button at the bottom, to add it to your shopping cart. Once you have selected all of the items you want click on the "VIEW SHOPPING CART" button. You will be presented with a page containing the details of your order so that you can confirm what you have entered. When you are ready to pay click on the "Pay Now" button to be taken to the PayPal screen where you have a choice to use your PayPal account or select to pay by Credit card without creating a PayPal account.</p> 


							<h4>Filter by Category</h4>
              					<input type="radio" id="all_result" name="category" value="all" checked>
            					<label for="all_result" style="display:inline">All</label>						

								<?php for ($i=0; $i < count($categoryIDs); $i++):?>
									<input type="radio" id="<?= $categoryIDs[$i]?>" name="category" value="<?= $categoryIDs[$i]?>"
		             				<?php if ($category == $categoryIDs[$i]): ?>checked<?php endif;?>>
		             				<label for="<?= $categoryNames[$i]?>" style="display:inline">
		             				<?php if ($categoryIDs[$i] === 8): ?>
		             					Digital
		             				<?php else: ?>
		             					<?= $categoryNames[$i]?>
	             					<?php endif ?>
		             				</label>
		             			<?php endfor?>
						</div>
					</form>
				</aside>
				</div>

				<span id="message"></span>
				<div id="items">Items in Cart: <span id="cart">0</span></div>
				</br>
				<table class="display" id="example">
					<thead>
					</thead>
					<tfoot>
						<tr>
							<?php
					            foreach($aCol as $col_data){
					                if($col_data != "Add Product")
					                	echo "<th><input type='text' name='search_" . $col_data . "' placeholder=\"" . $col_data . "\" class='search_init' /></th>";
					            }                            
					        ?>
					        <th><input type='hidden'></th>
				        </tr>
					</tfoot>
					<tbody>
					</tbody>
				</table>
				</br>
				<form id="viewcart" method="post" action="shoppingCart.php?name=<?= $name ?>" onsubmit="return toCart()">
					<input type="hidden" id="finalvalues" name="finalvalues" value="" />
					<input type="hidden" id="values" name="values" value="" />
					<input type="submit" id="submit" name="submit" value="View Shopping Cart" />
				</form>
				<button id="addtocart" name="add" class="addcart" onclick="addToShoppingCart()">Add to Cart</button>
				<p><b>**Note:</b>To add an item in the cart, put the quantity of item you would like to purchase in the text box, then click the Add to Cart button. When you finished adding items to the cart click the View Shopping Cart button to finalize the purchase of the items.  </p>
			</div>
		</div>
	</body>
</html>