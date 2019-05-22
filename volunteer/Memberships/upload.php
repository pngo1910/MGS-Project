<?php
  require('../../db/membershipAdminCheck.php');
  require('../../errorReporter.php');
  require('../../db/memberConnection.php');
  require('../../retrieveColumns.php');

  $tableName = $_POST['tableName'];
  $columns = retrieveColumns($tableName, 0, $userConn);
  $primaryKey = retrievePrimaryKeys($tableName, $userConn)[0];
?>

<!DOCTYPE HTML>
<html>
  <head>
    <meta charset="utf-8">
    <?php header('X-UA-Compatible: IE=edge,chrome=1'); ?>
    <title>MGS Volunteer</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->

    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/main.css">
    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
  </head>
  <body>
    <div id="resultsbackground_table">
      <div id="container" class="home">
        <div id="searchresults">
          <?php require('header.php'); ?>
        </div>
      </div>
    </div>

    <div class="bulkForm">
      <h1><?= $tableName ?></h1>
        <h2>Single Upload</h2>
        <form action="singleUpload.php?tableName=<?= $tableName ?>&amp;pk=<?= $primaryKey ?>" method="post" enctype="multipart/form-data">
          <h3>Please fill out the information to the best of your ability</h3>

          <?php foreach ($columns as $colName) : ?>
            <?php if ($colName != $primaryKey) : ?>
              <ul>
                <li class="nodot">
                  <label for="<?= $colName ?>"><?= $colName ?></label>
                  <input name='<?= $colName ?>' id="<?= $colName ?>" />
                </li>
              </ul>
            <?php endif ?>
          <?php endforeach ?>

        <input type="submit" class="submit" value="Submit">
      </form>
    </div>
  </body>
</html>
