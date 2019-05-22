<?php
  header('X-UA-Compatible: IE=edge,chrome=1');


  require('../db/loginCheck.php');
  require('../db/memberConnection.php');
  require('../errorReporter.php');

  //add for rescent update
  require('updateChange.php');
  //add for send email
  require('confirmation_mail.php');

  // if session error is set
  if (isset($_SESSION['error'])) {
    // set the session error equal to error variable and then to null ("")
    $error = $_SESSION['error'];
    $_SESSION['error'] = '';
  }
  
  // if not, set the error variable to null ("")
  else {
    $error = '';
  }

  $sql = "SELECT MemberNum FROM Members WHERE Username = ?";
  $stmt = sqlsrv_query($userConn, $sql, array($_SESSION['uname']));
  if (sqlsrv_fetch($stmt) === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
  
  $memberNum = sqlsrv_get_field($stmt, 0);

  // if form is submitted
  if(isset($_POST['update'])){
    $values = array();
    $sql = array();
    //add for rescent update
    $changes = array();

    if (isset($_POST['fname']) && $_POST['fname'] != '') {
      $values[] = $_POST['fname'];
      $sql[] = 'firstName = ?';
      //add for rescent update
      $changes[] = 'firstName';
    }
    if (isset($_POST['lname']) && $_POST['lname'] != '') {
      $values[] = $_POST['lname'];
      $sql[] = 'lastName = ?';
      //add for rescent update
      $changes[] = 'lastName';
    }
    if (isset($_POST['address']) && $_POST['address'] != '') {
      $values[] = $_POST['address'];
      $sql[] = 'address = ?';
      //add for rescent update
      $changes[] = 'address';
    }
    if (isset($_POST['city']) && $_POST['city'] != '') {
      $values[] = $_POST['city'];
      $sql[] = 'city = ?';
      //add for rescent update
      $changes[] = 'city';
    }
    if (isset($_POST['province']) && $_POST['province'] != '') {
      $values[] = $_POST['province'];
      $sql[] = 'province = ?';
      //add for rescent update
      $changes[] = 'province';
    }
    if (isset($_POST['postalcode']) && $_POST['postalcode'] != '') {
      $values[] = $_POST['postalcode'];
      $sql[] = 'countryCode = ?';
      //add for rescent update
      $changes[] = 'countryCode';
    }
    if (isset($_POST['phoneno']) && $_POST['phoneno'] != '') {
      $values[] = $_POST['phoneno'];
      $sql[] = 'phone = ?';
      //add for rescent update
      $changes[] = 'phone';
    }
    if (isset($_POST['newEmail']) && $_POST['newEmail'] != '') {
      $sql[] = 'email = ?';
      $values[] = $_POST['newEmail'];
      //add for rescent update
      $changes[] = 'email';
    }

    $sqlStr = implode(', ', $sql);

    /*ADD for email generation*/
    $vowels = array(" = ?");
    $sqlStrForSearch = str_replace($vowels, "", $sqlStr);

    $sqlBeforeChange = "SELECT ".$sqlStrForSearch." FROM MemberInfo WHERE MemberNum = ?";
    $stmt = sqlsrv_query($userConn, $sqlBeforeChange, array($memberNum));
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    $oldValues = array();
    $oldEmail = "";
    foreach ($changes as $columnName) {
      $oldValues[] = $row[$columnName];
      if ($columnName === "email") {
        $oldEmail = $row[$columnName];
      }
    }

    if (empty($oldEmail)) {
      $sqlForEmail = "SELECT email FROM MemberInfo WHERE MemberNum = ?";
      $stmt = sqlsrv_query($userConn, $sqlForEmail, array($memberNum));
      if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
      $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

      $oldEmail = $row['email'];
    }

    /*end for email generation*/

    $sqlFinal = "UPDATE MemberInfo SET ".$sqlStr." WHERE MemberNum = ?";
    $values[] = $memberNum;
    $stmt = sqlsrv_query($userConn, $sqlFinal, $values);
    
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    //add for rescent update
    updateChange($memberNum, $changes, $userConn);

    /*ADD for email generation*/
    send_confirm_email($oldValues, $values, $changes, $oldEmail);
    /*end for email generation*/
  }

  // sql statement to select from members table using posted username in the index.php
  $sql = "SELECT FirstName, LastName, Address, City, Province, CountryCode, Phone, Email FROM MemberInfo WHERE MemberNum = ?";
  $values = array($memberNum);
  
  // execute the query
  $stmt = sqlsrv_query($userConn, $sql, $values, array('Scrollable' => 'static'));

  // if query does nto et executed, print the errors and kill the script
  if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

  // fetch the row from the executed query
  $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_ABSOLUTE);
?>
<!DOCTYPE HTML>
<html lang="en-US">
	<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>MGS Member</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/main.css">
    <script src="js/vendor/modernizr-2.6.2.min.js"></script>
  </head>
	<body>
		<div id="resultsbackground">
			<div id="container" class="home">
				<div id="searchresults">
          <?php require('../header.php'); ?>
				</div>
  			<div class="account">
          <h3>Personal Info</h3>
          <form action="personalInfo.php" method="post">
            <p class="errorColor"><?= $error ?></p>
            <label class="label" for="fname">First Name:</label>
            <label class="info" for="fname"><?php echo $row['FirstName']; ?></label>
            <input type="text" class="searching" name="fname" id="fname" placeholder="change First Name" autofocus="autofocus"><br/>
            <label class="label" for="lname">Last Name:</label>
            <label class="info" for="lname"><?php echo $row['LastName']; ?></label>
            <input type="text" class="searching" name="lname" id="lname" placeholder="Change Last Name"><br/>
            <label class="label" for="address">Address:</label>
            <label class="info" for="address"><?php echo $row['Address']; ?></label>
            <input type="text" class="searching" name="address" id="address" placeholder="Change Address"><br/>
            <label class="label" for="city">City:</label>
            <label class="info" for="city"><?php echo $row['City']; ?></label>
            <input type="text" class="searching" name="city" id="city" placeholder="Change City"><br/>
            <label class="label" for="province">Province:</label>
            <label class="info" for="province"><?php echo $row['Province']; ?></label>
            <input type="text" class="searching" name="province" id="province" placeholder="Change Province"><br/>
            <label class="label" for="postalcode">Postal Code:</label>
            <label class="info" for="postalcode"><?php echo $row['CountryCode']; ?></label>
            <input type="text" class="searching" name="postalcode" id="postalCode" placeholder="Change Postal Code"><br/>
            <label class="label" for="phoneno">Phone Number:</label>
            <label class="info" for="phoneno"><?php echo $row['Phone']; ?></label>
            <input type="phone" class="searching" name="phoneno" id="phoneno" placeholder="Change Phone Number"><br/>
            <label class="label" for="newEmail">Email Address:</label>
            <label class="info" for="newEmail"><?php echo $row['Email']; ?></label>
            <input type="text" class="searching" name="newEmail" id="newEmail" placeholder="new email address"><br/>
            <input type="submit" class="submit" name="update" value="Update" onclick="return confirm('Are you sure you want this/these change(s)?')">
          </form>
  			</div>
  		</div>
    </div>
	</body>
</html>
