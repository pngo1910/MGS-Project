<?php
  require('../../db/membershipAdminCheck.php');
  require('../../db/memberConnection.php');
  require('../../errorReporter.php');
  require('../../addBranches.php');

  // set the session 'error' variable to null ("")
  $_SESSION['error'] = '';

  $fname = isset($_POST['fname']) ? $_POST['fname'] : "";
  $lname = isset($_POST['lname']) ? $_POST['lname'] : "";
  $address = isset($_POST['address']) && $_POST['address']!== ''? $_POST['address']: null;
  $city = isset($_POST['city']) && $_POST['city']!== ''? $_POST['city']: null;
  $country = isset($_POST['nation']) && $_POST['nation']!== ''? $_POST['nation']: null;
  $province = isset($_POST['province']) && $_POST['province']!== ''? $_POST['province']: (isset($_POST['provbox']) && $_POST['provbox']!== ''? $_POST['provbox']: null);
  $postalCode = isset($_POST['postalCode']) && $_POST['postalCode']!== ''? strtoupper($_POST['postalCode']): null;
  $phone = isset($_POST['phone']) && $_POST['phone']!== ''? $_POST['phone']: null;
  $email = isset($_POST['email']) && $_POST['email']!== ''? $_POST['email']: null;
  $generations = isset($_POST['generations']) && $_POST['generations'] !== ''? $_POST['generations']: null;
  $associatedWith = isset($_POST['associate']) && $_POST['associate'] !== ''? $_POST['associate']: null;
  $branches = isset($_POST['branch'])? $_POST['branch']: null;

  switch ($country){
    case 'Canada':
      break;
    case 'United States':
      $postalCode = 'USA'.' '.$postalCode;
      break;
    case 'United Kingdom':
      $postalCode = 'UK'.' '.$postalCode;
      break;
    default:
      if ($postalCode !== null){
        $postalCode = $country.' '.$postalCode;
    } else {
        $postalCode = $country;
    }
  }

  //get the highest member number
  $sqlMax = "SELECT MAX(MemberNum) FROM Membership WHERE MemberNum < 9000 OR MemberNum > 9999";
  $stmtMax = sqlsrv_query($userConn, $sqlMax, array());

  // fetch the row from the executed query
  if (sqlsrv_fetch($stmtMax) === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

  //incremet the highest member number by 1 to get a new member number
  $memberNum = (int)sqlsrv_get_field($stmtMax, 0);
  $memberNum = $memberNum == 8999? 10000: $memberNum + 1;

  //set the join date to today and the expiry to 1 year from today
  $joinDate = date('Y-m-d');
  $expiry = date('Y-m-t', strtotime("+1 year", strtotime($joinDate)));

  // insert all the field values in database table
  $sql = "INSERT INTO Membership (MemberNum, Generations, TypeOfMember, YearJoined, Expiry, Credit)
          VALUES (?, ?, ?, ?, ?, ?)
          INSERT INTO memberInfo (MemberNum, FirstName, LastName, Address, City, Province, CountryCode, Phone, Email)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

  $values = array($memberNum, $generations, 1, $joinDate, $expiry, 0,
                  $memberNum, $fname, $lname, $address, $city, $province, $postalCode, $phone, $email);

  // execute the query
  $stmt = sqlsrv_query($userConn, $sql, $values);

  //if query does not get executed print the error
  if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__); 

  if ($associatedWith !== null){
    $sql = "INSERT INTO AssociateMembers (AssociateMemberID, IndividualMemberID) VALUES (?, ?)";
    $stmt = sqlsrv_query($userConn, $sql, array($memberNum, $associatedWith));
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
  }

  if ($branches != null) addBranches($memberNum, $branches, $userConn);

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
          <h2>The member: <?= $fname.' '.$lname ?> is now Registered with the member number: <?= $memberNum ?></h2>
          <form target="_blank" action="sendPackage.php" method="get">
            <input type="hidden" name="memberid" value="<?= $memberNum ?>">
            <label class="label" for="send">Generate New Member Email</label>
            <input class="submit" type="submit" name="send" value="Generate">
          </form>
        </div>
      </div>
    </div>
  </body>
</html>
