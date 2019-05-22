<?php
	header('X-UA-Compatible: IE=edge,chrome=1');

  require('../db/loginCheck.php');
  require('../db/memberConnection.php');

  /*<?php require('balanceWidget.php'); ?>*/  


  //Selects the memberNum with the username
  $sql = "SELECT MemberNum FROM Members WHERE Username = ?";
  $bwMemberstmt = sqlsrv_query($userConn, $sql, array($_SESSION['uname']));
  if ($bwMemberstmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
  $bwRow = sqlsrv_fetch_array($bwMemberstmt, SQLSRV_FETCH_ASSOC);

  $memberNum = $bwRow['MemberNum'];
  $accessLevel = $bwRow['AccessLevel'];

  //Retrieves the amount of credits the username has
  $sql = "SELECT Credit FROM Membership WHERE MemberNum = ?";
  $bwCreditstmt = sqlsrv_query($userConn, $sql, array($memberNum), array('Scrollable' => 'static'));
  if (sqlsrv_fetch($bwCreditstmt) === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
  $credit = sqlsrv_get_field($bwCreditstmt, 0);

?>

<!DOCTYPE html>
<html>
<style type="text/css">
	.container {
		float: right;
		border: 2px solid #000000;
		width: 170px;
	}
	.label {
		display: block;
	}	

		#balance{
			font-size: 16px;
			text-align: center;
			background: linear-gradient(to bottom, #0865F1, #000D34);
			color: #fff;
			width: 170px;
		}
		#user{
			margin-bottom: 8px; 
			font-size: 15px;
		}

		#credit{
			font-size: 14px;
		}
		#link{
			font-size: 14px;
			color: #0000EE;
			text-decoration: underline;
			font-weight: normal;
			}

	.clear {
		clear:both;
	}
</style>
<body>
	<div class="container">
		<div>
			<label class="header" id="balance">Balance</label>
			<label class="label" id="user">Username: <?php echo $_SESSION['uname'];?> </label>		
			<label class="label" id="credit">Credits: <?php echo $credit; ?> (<a id="link" href="/myAccount/credits_renewals.php">Add Credits</a>)</label>
    	<div class="clear"></div>
		</div>
	</div>
</body>
</html>