<?php
  header('X-UA-Compatible: IE=edge,chrome=1');
  require('../db/adminCheck.php');
  require('../errorReporter.php');

  if (isset($_POST['change'])) {
    require('../db/memberConnection.php');

    $sql = "SELECT password FROM members WHERE username = 'inhouse'";
    $stmt = sqlsrv_query($userConn, $sql);
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    $currentpw = sqlsrv_fetch_array($stmt)['password'];
    $oldpw_encrypted = hash('sha512', $_POST['oldpassword']);

    if ($oldpw_encrypted === $currentpw) {
      $newpassword = $_POST['newpassword'];
      $confirmpw = $_POST['confirmpassword'];

      if ($newpassword === $confirmpw) {
        $encrypted = hash("sha512", $newpassword);

        $sql = "UPDATE members SET password = ? WHERE username = 'inhouse'";
        $stmt = sqlsrv_query($userConn, $sql, array($encrypted));
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

        $_SESSION['error'] = 'Password changed successfully';
      }
      else {
        $_SESSION['error'] = "New password does not match the confirm password.";
      }
    }
    else {
      $_SESSION['error'] = "The old password you have entered was incorrect. Please try again.";
    }
  }
?>

<!DOCTYPE HTML>
<html lang="en-US">
  <head>
    <meta charset="utf-8">
    <title>Inhouse Account Settings</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->

    <link rel="stylesheet" href="../css/normalize.css">
    <link rel="stylesheet" href="../css/main.css">
    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
  </head>

  <body>
    <div id="resultsbackground">
      <div id="container" class="home">
        <div id="searchresults">
          <?php require('header.php'); ?>
        </div>

        <h2>Change Inhouse Password</h2>

        <!-- display errors, if occur and set session error to null ("") after -->
        <?php if (isset($_POST['newpassword']) && isset($_POST['oldpassword'])) : ?>
          <?= $_SESSION['error'] ?>
          <?php $_SESSION['error'] = ""; ?>
        <?php endif; ?>

        <form action="changeInhousePassword.php" method="post">
          <input type="password" placeholder="Old Password" class="searching" name="oldpassword" required='' id = "oldpassword"><br />
          <input type="password" placeholder="New Password" class="searching" name="newpassword" required='' id = "newpassword"><br />
          <input type="password" placeholder="Confirm Password" class="searching" name="confirmpassword" required='' id = "password"><br />
          <input type="submit" id="change" class="submit" name="change" value="Submit" onclick="return confirm('Are you sure you want to change password?')">
        </form>
      </div>
    </div>
  </body>
</html>
