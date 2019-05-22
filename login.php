<?php
  header('X-UA-Compatible: IE=edge,chrome=1');

  session_start();

  // if session error is not set, set it to null ("")
  if (!isset($_SESSION['error'])) {
    $_SESSION['error'] = '';
  }

  if (!isset($_SESSION['Message'])) {
    $_SESSION['Message'] = '';
  }
?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<html>
  <head>
    <meta charset="utf-8">
    <title>Login</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/normalize.css">
    <script src="js/vendor/modernizr-2.6.2.min.js"></script>
  </head>
  <body>
    <div id="resultsbackground">
      <div id="container" class="home">
        <?php require('header.php'); ?>

        <div id="head">
          <h2>Login</h2>
        </div>

        <form name="loginForm" method="get" action="db/loginScript.php">
        <!-- <form name="loginForm" method="post" action="db/loginScript.php">

          <!-- display the session error and session message and then set it to null ("") after displaying it -->
          <p class="errorColor"><?= $_SESSION['error'] ?></p>
          <p class="msgColor"><?= $_SESSION['Message'] ?></p>
          <?php $_SESSION['error'] = ""; ?>
          <?php $_SESSION['Message'] = ""; ?>

          <label for="username">Username</label>
          <input type="text" class="searching" name="username" required="true" id="username" autofocus="autofocus"/><br/>
          <label for="password">Password</label>
          <input type="password" class="searching" name="password" required="true" id="password"/><br/>
          <input type="submit" class="submit" name="Submit" value="Login">
        </form>

        <div class="indexOptions">
          <a href="forgotUsername.php">Forgot Username</a>
          </br><a href="forgotPassword.php">Forgot Password</a>
        </div>
      </div>
    </div>
  </body>
</html>
