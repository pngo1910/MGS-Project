<?php
  header('X-UA-Compatible: IE=edge,chrome=1');

  require('../db/adminCheck.php');
  require('../errorReporter.php');
  require('../db/mgsConnection.php');
  require('../retrieveColumns.php');

  $tablename = $_GET['tablename'];
  $params = array();
  $options = array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

  /*
  * Primary Key Columns
  */
  $primaryKeys = retrievePrimaryKeys($tablename, $conn);

  /* Indexed column (used for fast and accurate table cardinality) */
  $sIndexColumn = $primaryKeys[0];

  $columns = retrieveColumns($tablename, 0, $conn);

  // store the get varibles in local variables
  $id = $_GET[$sIndexColumn];
  $value = array();

  // if (!is_numeric($id))
  //   die(header("Location: table.php?tableName=$tablename"));
?>

<!DOCTYPE HTML>
<html lang="en-US">
  <head>
    <meta charset="utf-8">
    <title>MGS Volunteer</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->

    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/main.css">
    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="/DataTables-1.10.6/media/js/jquery.js"></script>
    <script type="text/javascript" charset="utf-8" src="/DataTables-1.10.6/media/js/jquery.dataTables.js"></script>
    <!-- <script class="jsbin" src="http://datatables.net/download/build/jquery.dataTables.nightly.js"></script> -->
    <script>
      function close_window() {
        close();
    }
    </script>
  </head>
  
  <body>
    <div class='adminTables'>
      <div id="resultsbackground">
        <div id="container" class="home">
          <div id="searchresults"><?php require('header.php'); ?></div>
          <div class='alignEdit'><h2>Edit This Entry</h2></div>

          <div id="postdiv">
            <p class="errorColor"><?= $_SESSION['error'] ?></p>
            <?php $_SESSION['error'] = ''; ?>
            <form action="update.php?id=<?= $id ?>&amp;tablename=<?= $tablename ?>" method="post">
              <?php
                $sql_value = "SELECT * FROM $tablename WHERE $sIndexColumn = ?";
                $stmt_value = sqlsrv_query($conn, $sql_value, array($id), array( "Scrollable" => 'static' ));

                if ($stmt_value === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

                while ($row = sqlsrv_fetch_array($stmt_value)) {
                  if (strtolower($tablename) === 'typecodes') {
                    $oldtypeid = $row['TypeID'];
                    echo "<input type='hidden' name='oldtypeid' value='$oldtypeid'>";
                  }

                  foreach ($columns as $colName) {
                    // let the user edit the index column for typecodes
                    if ((strtolower($tablename) === 'typecodes'
                    && strtolower($colName) === strtolower($sIndexColumn))
                    || strtolower($colName) !== strtolower($sIndexColumn)) {

                      if (strtolower($colName) === 'statuscode') {
                        echo '<input type="hidden" name="statuscode" value="' . $row[$colName] . '">';
                      } 
                      elseif (strtolower($colName) === 'owner') {
                        echo '<input type="hidden" name="owner" value="' . $row[$colName] . '">';
                      }
                      else {
                        // the quotes here are required or the value still
                        // becomes truncated in the text field.
                        $value = '"' . htmlentities($row[$colName]) . '"';

                        echo "<ul class='editUl'><li>";
                        echo "<label>$colName</label>";
                        echo "<input name='$colName' id='$colName' value=$value />";
                        echo "</li></ul>";
                      }
                    }
                  }
                }
              ?>

              <input name="submit" value="Update" type="submit" />
              <input name="submit" value="Cancel" type="submit" />
            </form>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
