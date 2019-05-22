<?php
	// if(!isset($_GET['test'])){
	// 	header("Location: notFound.php");
	// }
	$name = isset($_GET['name'])? $_GET['name']: 'store';
	$active = "";
    switch($name){
        case 'login':
            require('../db/loginCheck.php');
            require('../db/memberConnection.php');
            require('../errorReporter.php');
            $username = $_SESSION['uname'];
            $qry = "SELECT Expiry, YearJoined, FirstName, LastName FROM Members 
            		LEFT JOIN Membership ON Members.MemberNum = Membership.MemberNum
            		LEFT JOIN MemberInfo ON Members.MemberNum = MemberInfo.MemberNum
            		WHERE Username = ?";
            $stmt = sqlsrv_query($userConn, $qry, array($username));
            if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
            $row = sqlsrv_fetch_array($stmt);
            $active = $row['YearJoined'];
            $expiry = $row['Expiry'];
            break;
        default:
            session_name($name);
            session_start();
    }
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
	    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
	</head>
	<body>
		<div id="resultsbackground">
	    	<div id="container" class="home">
	    		<?php require('../header.php'); ?>
	    		<?php if($name === 'login')
	    			require('balanceWidget.php'); 
    			?>

				<div id="head">
					<h2>Welcome to the MGS e-Store.<br>
					 This site is in test mode but all features currently are working except for the ordering of printed copies of Cemetery Transcriptions. Email contact@mbgenealogy for a quote of shipping costs for your location.</h2> <p>
				<strong> Directions: </strong> Click on the products link below. It will take you to the complete list of products available in our store. Click on one of the radio buttons to select from: Books, CD, DVD, Booklets, Cemetery Transcriptions or Digital Downloads. When you find an item you want either use the up arrow in the add box or replace the 0 with the number of items you want. You must then click the "ADD TO CART" button at the bottom, to add it to your shopping cart. Once you have selected all of the items you want click on the "VIEW SHOPPING CART" button. You will be presented with a page containing the details of your order so that you can confirm what you have entered. When you are ready to pay click on the "Pay Now" button to be taken to the PayPal screen where you have a choice to use your PayPal account or select to pay by Credit card without creating a PayPal account.</p> 
				</div>
				<div id="content">
					<?php if($name === 'login') : ?>

						<p>Welcome <?= $row['FirstName']." ".$row['LastName']; ?>!</p>
					<?php else : ?>
						<p>Welcome Guest! Would you like to <a href="../login.php">log yourself in</a>? This store will allow you to purchase items without being a member or signing in as a member. If you are a member and you sign in to your MGS Member account, you will be able to access the MANI database and other member-only features.</p>
					<?php endif ?>
					<h3 class="h3store">The MGS e-Store</h3>
					<p>The e-Store currently offers books, CDs, and electronic downloads for the family historian and genealogist. Initially, Cemetery Transcripts will be delivered via PDF files emailed to you and no physical item will be shipped to you. You will not be charged a shipping fee. ( there are over 1,700 Cemetery Transcriptions available electronically.).</p>
					<h3 class="h3store">Pay-Per-View (This feature will be added in the future) </h3>
					<p>Many items will have links to PPV copies of records and images in the future.</p>
					<?php if($active != ""): ?>
					<h3 class="h3store">MGS Member Status</h3>
					<p>Your membership expires <?= $expiry->format('Y-m-d') ?>. Follow the link below to gain access to MANI and the MGS Member Only area. <a href="http://mani.mbgenealogy.com/member/">mani.mbgenealogy.com</a></p>
					<?php endif; ?>
					<hr />
					<p><strong>Click here to browse through the <a target="_blank" href="store.php?name=<?= $name ?>">products in our online eStore</a>.</strong> We will be adding additional items for sale. For Now the Cemetery Transcriptions offered on the site will be PDF copies sent by email. In the future we will add the ability to order printed copies of Cemetery Transcriptions to be mailed to you.</p>
					<p><strong> All shipping and handling costs on this site are within Canada only. Contact us at contact@mbgenealogy.com and we will send you the cost for US and Internetional shipping.</strong></p>
					<p>If you wish to order Cemetery Transcriptions by mail, please print our <a href="http://mbgenealogy.com/wp-content/uploads/2016/04/Publications-Order-Form3.pdf">order form</a>.</p>
					<p>Please <a href="http://www.mbgenealogy.com/contact-us">contact us</a> if you have any suggestions about how we can improve our service.</p>
					<p>See the <a href="http://www.mbgenealogy.com/conditions-of-use">Conditions of Use</a> page for several Frequently Asked Questions.</p>
				</div>
			</div>
		</div>
	</body>
</html>