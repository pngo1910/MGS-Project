<?php
  session_name('register');
  session_start();

  require('../db/memberConnection.php');
  require('../errorReporter.php');

  // set the session 'error' variable to null ("")
  $_SESSION['error'] = '';

  //return to previous page
  if (isset ($_POST['return']))
  {
    header('location: index.php');
    die;
  }

  //reset session
  if (isset ($_POST['reset']))
  {
    unset($_SESSION['generations']);
    unset($_SESSION['branches']);
    unset($_SESSION['associatedWith']);
    unset($_SESSION['associateAddress']);

    header('Location: ' . $_SERVER['HTTP_REFERER']);
    die;
  }

  // IF THE USER IS LINKING THEIR NEW MANI LOGIN TO AN EXISTING ACCOUNT
  if (isset($_SESSION['existingAccount']) && $_SESSION['existingAccount']) {
    $memberNum = $_POST['membernum'];
    $fname = isset($_POST['fname']) ? $_POST['fname'] : "";
    $lname = isset($_POST['lname']) ? $_POST['lname'] : "";

    $sql = "SELECT FirstName, LastName FROM MemberInfo WHERE MemberNum = ?";
    $stmt = sqlsrv_query($userConn, $sql, array($memberNum), array('Scrollable' => 'static'));

    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_ABSOLUTE);

    //verify that the member number enterd exists, if not output an error
    if ($row === null) {
      $_SESSION['error'] = "The Member Number you entered does not exist.";
      // header('location: maniRegister.php');
      header('Location: ' . $_SERVER['HTTP_REFERER']);
      die;
    }
    //verify that the name entered matches the name associated with the member number entered,
    //if not output an error
    if ($fname !== $row['FirstName'] || $lname !== $row['LastName']){
      $_SESSION['error'] = "The name you entered does not match the name associated with this Member Number.";
      // header('location: maniRegister.php');
      header('Location: ' . $_SERVER['HTTP_REFERER']);

      die;
    }

    // insert all the field values in database table
    $sql = "INSERT INTO Members (MemberNum, Username, Password, AccessLevel, Verified) VALUES (?, ?, ?, ?, ?);
            UPDATE MemberInfo SET Email = ? WHERE MemberNum = ?";

    //write a statement to find the members expiry date.
    $findExpiryScript = "SELECT Expiry FROM Membership WHERE MemberNum = ?";
    //send the script the membernum to look for
    $expiryValue = array($memberNum);
    //exucute the script
    $statement = sqlsrv_query($userConn, $findExpiryScript, $expiryValue);
    //show errors if they occured
     if (sqlsrv_fetch($statement) === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    //trying to get the expiry date.
    $expiryDate = sqlsrv_get_field($statement, 0);

    $verified = date_format($expiryDate, 'U') < time()? 0: 1;
    $verified = 1;

    $values = array($memberNum, $_SESSION['username'], $_SESSION['encryptpw'], 1, $verified, $_SESSION['email'], $memberNum);

    // execute the query
    $stmt = sqlsrv_query($userConn, $sql, $values);

    // if query does not get executed print the error
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    session_name('register');
    session_destroy();

    unset($_COOKIE['register']);
    setcookie('register', null, -1, '/');

    // IF THE USER IS CREATING A NEW ACCOUNT AND PAYING WITH PAYPAL

  } else {
    $_SESSION['generations'] = isset($_POST['generations']) && $_POST['generations'] !== ''? $_POST['generations']: null;

    $_SESSION['associatedWith'] = isset($_POST['associateNumber']) && $_POST['associateNumber'] !== ''? $_POST['associateNumber']: null;

    $_SESSION['associateAddress'] = isset($_POST['associateAddress']) && $_POST['associateAddress'] !== ''? $_POST['associateAddress']: null;

    $associatedWith = $_SESSION['associatedWith'];
    $associateAddress = $_SESSION['associateAddress'];

    $_SESSION['branches'] = isset($_POST['branch'])? $_POST['branch']: null;
    //if the associated with variable has a value then that means that this is an asoociated account (2) otherwise it's an individual account (1)
    $_SESSION['type'] = isset($_SESSION['associatedWith']) && $_SESSION['associatedWith'] != null? 2 : 1;

    //add for checking the member num and address
    if(!(empty($associatedWith) && empty($associateAddress)))
    {
      // set the counter variable to zero
      // $counter = 0;

      // check if the associated member exists or no
      // $sql = "SELECT 1 FROM MemberInfo WHERE MemberNum = ? AND Address = ?";
      // modify for checking the expiry
      $sql = "SELECT Expiry FROM MemberInfo JOIN Membership ON MemberInfo.MemberNum=Membership.MemberNum WHERE MemberInfo.MemberNum = ? AND MemberInfo.Address = ?";

      // execute the query
      $stmt = sqlsrv_query($userConn, $sql, array($associatedWith, $associateAddress));
      if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

      // increment the counter variable, if row is found
      // modify for checking the expiry
      // while (sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) $counter++;
      $expiry = sqlsrv_fetch_array($stmt)['Expiry'];

      // if ($counter == 0) {
      if (empty($expiry)) {
        $_SESSION['error'] = "The Member Number and Address you entered are not matched.";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        die;
      }
      //add for checking the expiry
      $date = new DateTime();
      if ($expiry < $date) {
        $_SESSION['error'] = "The Member you entered are already expired.";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        die;
      }
    }

    //get the highest member number
    $sqlMax = "SELECT MAX(MemberNum) FROM Membership WHERE MemberNum < 9000 OR MemberNum > 9999";
    $stmtMax = sqlsrv_query($userConn, $sqlMax, array());

    // fetch the row from the executed query
    if (sqlsrv_fetch($stmtMax) === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    //incremet the highest member number by 1 to get a new member number
    $memberNum = (int)sqlsrv_get_field($stmtMax, 0);
    $_SESSION['memberNum'] = $memberNum == 8999? 10000: $memberNum + 1;

    //set the join date to today and the expiry to 1 year from today
    $_SESSION['joinDate'] = date('Y-m-d');
    $_SESSION['expiry'] = date('Y-m-t', strtotime("+1 year", strtotime($_SESSION['joinDate'])));

    header("location: paypal.php");
  }
?>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
  <head>
    <meta charset="utf-8">
    <title>Member Registration</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">
    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/main.css">
    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
  </head>
  <body>
    <div id="resultsbackground">
      <div id="container" class="home">
        <div id="searchresults">
          <?php require('header.php'); ?>
        </div>
        <div id="head">
          <h2>You are now Registered!</h2>
        </div>
      </div>
    </div>
  </body>
</html>
