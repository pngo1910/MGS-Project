<?php
  header('X-UA-Compatible: IE=edge,chrome=1');

  // if session is not started yet, start the session
  if (!session_start()) {
    session_start();
  }

  require('errorReporter.php');
  require('db/adminConnection.php');

  // set counter variable equal to zero
  $counter = 0;

  // These variables store what was passed in the previous form
  $lname = $_POST['lname'];
  $fname = $_POST['fname'];

  // if lastname or firstname feilds are not empty
  if (strlen(ltrim($lname)) != 0 || (strlen(ltrim($fname)) != 0)) {

    // array of all the tables
    $tables = array("Births", "CemeteryRecords", 'marriages', "ObituariesRural", "ObituariesWinPap","Census1827", "Census1831", "Census1834", "Census1870", "Census1891", "Census1901", "BookRecords");
    $arrayOfSQLStatements = array();
    $whereVals = array();

    // loop through each table creating the SQL statement
    foreach ($tables as $tableName) {
      $whereStmt = array();

      if (strtoupper($tableName) == strtoupper("Marriages")) {
        if ($lname != '') {
          $whereStmt[] = "GroomLastName LIKE ?";
          $whereVals[] = "%$lname%";

          $whereStmt[] = "BrideLastName LIKE ?";
          $whereVals[] = "%$lname%";
        }

        if ($fname != '') {
          $whereStmt[] = "GroomFirstName LIKE ?";
          $whereVals[] = "%$fname%";

          $whereStmt[] = "BrideFirstName LIKE ?";
          $whereVals[] = "%$fname%";
        }

        // $implode = implode(' AND ', $whereStmt);
        // $arrayOfSQLStatements[] = "SELECT ID FROM $tableName WHERE $implode";
      } else {
        if ($lname != '') {
          $whereStmt[] = "LastName LIKE ?";
          $whereVals[] = "%$lname%";
        }

        if ($fname != '') {
          $whereStmt[] = "FirstName LIKE ?";
          $whereVals[] = "%$fname%";
        }
      }

      $implode = implode(' AND ', $whereStmt);
      $arrayOfSQLStatements[] = "SELECT ID FROM $tableName WHERE $implode";
      unset($whereStmt);
    }

    $qry = implode(' UNION ', $arrayOfSQLStatements);
    $stmt = sqlsrv_query($conn, $qry, $whereVals);

    // if query does not execute, show the error message
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
  }

  // if lastname and firstname fields are empty
  else {
    // send user to the index page(suppose to be freesearch page)
    header("location: index.php");
    // set the session error to error message to show it on the freesearch page
    //free search page missing 
    // $_SESSION['freeError'] = "All fields cannot be blank.";
  }
?>

<!DOCTYPE HTML>
<html lang="en-US">
  <head>
    <meta charset="utf-8">
    <title> Manitoba Genealogical Society</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">
    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/main.css">
    <script src="js/vendor/modernizr-2.6.2.min.js"></script>
  </head>
  <body>
    <div id="homeBackgroundFree"></div>
    <div id="container" class="home">
      <?php require('header.php'); ?>
    </div>

    <?php
      // loop through the rows returned and increase the counter variable
      // to the number of rows returned to show the number of rows
      while (sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) $counter++;
    ?>

    <!-- display the number of results returned by displaying the number of rows -->
    <p class="freeSpeak">There are <span class="counter"><?= $counter ?></span> results. <a href="register/">Register</a> or <a href="login.php">Log In</a> to see them!</p>
    <p class="freeSpeak">You can also <a href="index.php">go back</a> to the Search page.</p>
  </body>
</html>
