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
					<h2>Store Heading</h2>
				</div>

				<div id="content">

					<div style="background-color:#99ffff;padding:8px;border:1px solid #999;">Welcome to our newly redesigned e-store.etc. Welcome Message ...Welcome Message ...Welcome Message ...Welcome Message ...Welcome Message ...Welcome Message ...Welcome Message ...Welcome Message ...Welcome Message ...Welcome Message ...Welcome Message ... </div>

					<div style="margin:15px 0px;padding:10px;border-radius:10px;border:1px solid #999;">
						<ul style="margin:0px 15px;padding:0px 15px;">
							<div style="float: left; width: 50%;">
								<li>Books, CDs, and DVDs</li>
								<li>Manitoba Cemetery Transcriptions</li>
								<li>Full Product Listing</li>
								<li>e-Store Information</li>
							</div>

							<div style="float: left; width: 50%;">
								<li>Donate to MGS</li>
								<li>MGS Conference Registration</li>
								<li>Print Order Form</li>
							</div>
						</ul>
						<br clear="all">

					</div>

					<?php if($name === 'login') : ?>			
					<p>Welcome <?= $row['FirstName']." ".$row['LastName']; ?>!</p>
					<?php else : ?>
						<p>Welcome Guest! Would you like to <a href="../login.php">log yourself in</a>? This store will allow you to purchase items without being a member or signing in as a member. If you are a member and you sign in to your MGS Member account, you will be able to access the MANI database and other member-only features.</p>
					<?php endif ?>


					<?php if($active != ""): ?>
					<h3 class="h3store">MGS Member Status</h3>
					<p>Your membership expires <?= $expiry->format('Y-m-d') ?>. Follow the link below to gain access to MANI and the MGS Member Only area. <a href="http://mani.mbgenealogy.com/member/">mani.mbgenealogy.com</a></p>
					<?php endif; ?>



					<!-- new code goes here -->

				</div>
				<?php require('../footer.php'); ?>
			</div>
		</div>
	</body>
</html>