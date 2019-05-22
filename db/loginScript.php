<?php
  session_start();

  require('../errorReporter.php');
  require('memberConnection.php');

  $counter = 0;
  $username = $_GET['username'];
  //$username = $_POST['username'];
  $password = $_GET['password'];
  //$password = $_POST['password'];
  $encrypted = hash('sha512', $password);

  if (!isset($username) || !isset($password)) {
    $_SESSION['error'] = 'Please enter both username and password.';
    die(header('location: /login.php'));
  }
  //eStore, SpecialProject were taken out.
  $sql = "SELECT MemberNum, AccessLevel, Verified FROM Members WHERE Username = ? AND Password = ?";
  $stmt = sqlsrv_query($userConn, $sql, array($username, $encrypted), array('Scrollable' => 'static'));

  if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

  while (sqlsrv_fetch_array($stmt)) $counter++;

  if ($counter == 1) {
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_ABSOLUTE);
    $verified = $row['Verified'] == 1 ? true : false;
    $memberNum = $row['MemberNum'];
    $access = $row['AccessLevel'];
    //$eStore = $row['eStore'];
    //$SpecialProject = $row['SpecialProject'];

    $sql = "SELECT TypeOfMember, expiry FROM Membership WHERE MemberNum = ?";
    $stmt = sqlsrv_query($userConn, $sql, array($memberNum), array('Scrollable' => 'static'));
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_ABSOLUTE);

    // get the unix timestamps to compare the dates
    $expiryDate = date_format($row['expiry'], 'U');
    $currentDate = time();

    if ($currentDate > $expiryDate && $verified) {
      $sql = "UPDATE members SET verified = 0 WHERE membernum = ?";
      $stmt = sqlsrv_query($userConn, $sql, array($memberNum));
      if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

      $verified = false;
    }

    $name = session_name();
    session_destroy();
    unset($_COOKIE[$name]);
    setcookie($name, null, -1, '/');

    session_name('login');
    session_start();

    $_SESSION['uname'] = $username;
    $_SESSION['pword'] = $encrypted;
    $_SESSION['type'] = $row['TypeOfMember'];
    $_SESSION['access'] = $access;
    $_SESSION['verified'] = $verified;
    //$_SESSION['eStore'] = $eStore;
    //$_SESSION['SpecialProject'] = $SpecialProject;
    header("location: /member/");

  }
  else {
    $_SESSION['error'] = "Information entered is incorrect.";
    header("location: /login.php");
  }
?>
