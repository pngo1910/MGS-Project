<?php
  require('../db/volunteerCheck.php');
  require('../errorReporter.php');
  require('../db/volunteerConnection.php');
  require('../retrieveColumns.php');

  $tableName = $_POST['tableName'];
  $columns = retrieveColumns($tableName, 0, $conn);
  $primaryKey = retrievePrimaryKeys($tableName, $conn)[0];

  $muniNames = array();
  $sql = "SELECT MunName FROM Municipalities ORDER BY MunName";
  $stmt = sqlsrv_query($conn, $sql);
  if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
  while ($row = sqlsrv_fetch_array($stmt))
    {
      $muniNames[] = $row['MunName'];
      
    }

  $newspaperCodes = array();
  $newspaperNames = array(); 
  $sql = "SELECT NewspaperCode, NameOfNewspaper FROM NewsPapers ORDER BY NameOfNewspaper";
  $stmt = sqlsrv_query($conn, $sql); 
  if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
  while ($newsRow = sqlsrv_fetch_array($stmt)) {
    $newspaperCodes[] = $newsRow['NewspaperCode'];
    $newspaperNames[] = $newsRow['NameOfNewspaper'];
  }

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
        <ul>
        <h2>Bulk Upload</h2>
        <ul>
          <li class ="csvInfo">Uploaded files can only be ".csv" files. </li>
          <li class ="csvInfo">Person IDs should be omitted from the CSV file. </li>
          <li class ="csvInfo">Commas should be the leading character in a line.</li>
          <li class ="csvInfo">NULL values must still be seperated using a comma.</li>
        </ul>

        <br/>

        <form action="bulkUpload.php?tableName=<?= $tableName ?>" method="post" enctype="multipart/form-data">
           <h3>File Upload</h3> 
          <input type="file" class="searching"  name="file" id="file"><br>
          <input type="submit" name="submit" class="submit" value="Submit">
        </form> 

        <hr/>

        <h2>Single Upload</h2>
        <form action="singleUpload.php?tableName=<?= $tableName ?>" method="post" enctype="multipart/form-data">
          <h3>Please fill out the information to the best of your ability</h3>

          <?php foreach ($columns as $colName) : ?>

            <?php if ($colName != $primaryKey && $colName != "StatusCode" || $colName == "TypeID") : ?>
              <ul>
                <li class="nodot">
                  <label for="<?= $colName ?>"><?= $colName ?></label>
                  <?php if ($colName == "Municipality" || $colName == "New_Municipality" ) :?>
                    <input name='<?= $colName ?>' id="<?= $colName ?>" type="text"
                     placeholder="Select a Value" onfocus="this.select()" />
                    <select name="<?= $colName?>"
                       onchange="document.getElementById('<?= $colName?>').value=this.options[this.selectedIndex].text; document.getElementById('<?= $colName?>').value=this.options[this.selectedIndex].value;">
                      <option value =''></option>
                      <?php foreach ($muniNames as $name):?>
                        <option value = '<?= $name?>'><?= $name?></option>
                      <?php endforeach?>
                    </select>
                  <?php elseif ($colName == "PaperCode") :?>
                    <input name='<?= $colName ?>' id="<?= $colName ?>" type="text"
                     placeholder="Select a Value" onfocus="this.select()" />
                    <select name="<?= $colName?>"
                       onchange="document.getElementById('<?= $colName?>').value=this.options[this.selectedIndex].text; document.getElementById('<?= $colName?>').value=this.options[this.selectedIndex].value;">
                      <option value =''></option>
                      <?php for ($i=0; $i < count($newspaperNames); $i++):?>
                        <option value= '<?= $newspaperCodes[$i] ?>'><?= $newspaperNames[$i]?></option>
                      <?php endfor?>
                    </select>
                  <?php else:?>
                    <input name='<?= $colName ?>' id="<?= $colName ?>" />
                  <?php endif?>  
                </li>
              </ul>
            <?php endif?>
          <?php endforeach ?>

        <input type="submit" class="submit" value="Submit">
      </form>
    </div>
  </body>
</html>
