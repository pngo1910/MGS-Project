<?php
  require('../../db/membershipAdminCheck.php');
  require('../../errorReporter.php');
  require('../../db/memberConnection.php');
  require('../../retrieveColumns.php');

  if (!isset($_SESSION['message'])) $_SESSION['message'] = '';
  if (!isset($_SESSION['tableName'])) $_SESSION['tableName'] = '';
  if (!isset($_SESSION['error'])) $_SESSION['error'] = '';

  $and = "AND TABLE_NAME IN('Branch', 'Generations', 'TypeOfMember', 'AccessLevel')";
?>

<!DOCTYPE HTML>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js"> <!--<![endif]-->
  <head>
    <meta charset="utf-8">
    <?php header('X-UA-Compatible: IE=edge,chrome=1');?>
    <title>MGS Volunteer</title>
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
        <h2 class="volunteerSpeal">Select a table to see its contents and edit it.</h2>
        <h4 class="volunteerSpeal">You are currently connected to the Accounts Database.</h4>
        <p class="errorColor"><?= $_SESSION['error'] ?></p>
        <p class="msgColor"><?= $_SESSION['message'] ?></p>
        <?php $_SESSION['error'] = ""; ?>
        <?php $_SESSION['message'] = ""; ?>
        <?php $_SESSION['tableName'] = ""; ?>
          <h3>Search</h3>
          <form action="table.php" method="post">
            <select name="tableName" id="tableName">
              <?php foreach (retrieveTableNames($userConn, $and) as $table) : ?>
                <option value="<?= $table ?>"><?= $table ?></option>
              <?php endforeach; ?>
            </select>
            <input type="submit" Value="Edit" />
          </form>
          <hr>
          <h3>Upload</h3>
          <form action="upload.php" method="post">
            <select name="tableName" id="tableName">
              <?php foreach (retrieveTableNames($userConn, $and) as $table) : ?>
                <option value="<?= $table ?>"><?= $table ?></option>
              <?php endforeach; ?>
            </select>
            <input type="submit" Value="Upload" />
          </form>
        </div>
      </div>
    </div>
  </body>
</html>
