<?php
  require('../../db/adminCheck.php');
  require('../../db/adminConnection.php');
  if(!isset($_SESSION['message'])) $_SESSION['message'] = '';
  if(!isset($_SESSION['error'])) $_SESSION['error'] = '';
?>

<!DOCTYPE HTML>
<html class="no-js"> <!--<![endif]-->
  <head>
    <meta charset="utf-8">
    <?php header('X-UA-Compatible: IE=edge,chrome=1');?>
    <title>MGS Administrator</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/main.css">
  </head>
  <body>
    <div id="resultsbackground">
      <div id="container" class="home">
        <div id="searchresults">
          <?php require('header.php'); ?>
        </div>
        <p class='successColor'><?= $_SESSION['message'] ?></p>
        <p class='errorColor'><?= $_SESSION['error'] ?></p>
        <?php $_SESSION['message'] = ""; ?>
        <?php $_SESSION['error'] = ""; ?>
        <h2 class="adminSpeal">Welcome to the Store Management!</h2>
        <h2 class="adminSpeal">Here you can add Categories and Products to the development database then go to the Live Dash to review them before pushing them to the Store database.</h2>
        <ul id="livedash">
          <li><a href='addRecords.php?table=category'>Add Category</a></li>
          <li><a href='editRecords.php?table=category'>Edit Categories</a></li>
          <li><a href='addRecords.php?table=products'>Add Product</a></li>
          <li><a href='editRecords.php?table=products'>Edit Products</a></li>
          <li><a href='searchTransactions.php'>Search Transactions</a></li>
        </ul>
      </div>
    </div>
  </body>
</html>
