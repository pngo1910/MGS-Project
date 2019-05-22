<?php
  require('../../db/adminCheck.php');
  require('../../errorReporter.php');
	require('../../db/adminConnection.php');
  require('../../retrieveColumns.php');
?>

<!DOCTYPE HTML>
<html class="no-js">
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

        <h1 class="adminSpeal">Live Dashboard</h1>
        <h3>select table to delete records</h3>

        <p class="errorColor">
          <?php if (isset($_SESSION['error'])) : ?>
            <?= $_SESSION['error'] ?>
          <?php endif ?>
          <?php unset($_SESSION['error']) ?>
        </p>

        <p class="successColor">
          <?php if (isset($_SESSION['success'])) : ?>
            <?= $_SESSION['success'] ?>
          <?php endif ?>
          <?php unset($_SESSION['success']) ?>
        </p>

        <form action="deletedRecords.php" method="POST">
          <select name="tableName" id="tableName">
            <!-- <?php $and //= "AND TABLE_NAME NOT IN('Purchases', 'CemeteryTranscriptions')"; ?> -->
            <?php foreach (retrieveTableNames($conn, $and) as $table) : ?>
              <option value="<?= $table ?>"><?= $table ?></option>
            <?php endforeach; ?>
          </select>
          <input type="submit" Value="Search" />
        </form>
        <h5><a href='/admin/live/'>Back To Live Dashboard</a></h5>
			</div>
		</div>
	</body>
</html>
