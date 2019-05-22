<?php
  require('../../db/adminCheck.php');
  if(!isset($_SESSION['message'])) $_SESSION['message'] = '';
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
        <?php $_SESSION['message'] = ""; ?>
        <h2 class="adminSpeal">Welcome to the Live Dashboard!</h2>
        <h2 class="adminSpeal">Here you can view additions and modifications made to the development database by volunteers before pushing them to the MGS production database.</h2>
        <ul id="livedash">
          <!--li><a href='/admin/dev/?admin'>View Development Controls</a></li-->
          <li><a href='newTables.php'>View New Records</a></li>
          <li><a href='updatedTables.php'>View Updated Records </a></li>
          <li><a href='deletedTables.php'>View Deleted Records</a></li>
          <li><a href='addPayPerViewForm.php'>Add PPV File</a></li>
          <li><a href='editPayPerView.php'>Edit PPV Files</a></li>
        </ul>
      </div>
    </div>
  </body>
</html>
