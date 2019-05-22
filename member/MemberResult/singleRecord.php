<?php
  require('../../db/memberCheck.php');
  require('../../errorReporter.php');
  require('../../db/mgsConnection.php');
  require('../../retrieveColumns.php');

  if (isset($_GET['tablename']) && isset($_GET['id'])) {
    list($tableName, $recordID) = array($_GET['tablename'], $_GET['id']);
  } else {
    header("Location: /member/");
  }

  $id = ($tableName === 'Cemeteries') ? 'CemCode' : 'ID';
  $sql = "SELECT * FROM $tableName WHERE $id = ?";
  $stmt = sqlsrv_query($conn, $sql, array($recordID));
  if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
  <head>
    <meta charset="utf-8">

    <title>MGS <?= (isset($_SESSION['uname']) && strtolower($_SESSION['uname']) === 'inhouse')
      ? 'Library' : 'Member' ?> </title>

    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->

    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/DataTables-1.10.6/media/css/jquery.dataTables.css">
    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
  </head>
  <body>
    <div id="resultsbackground">
      <div id="container" class="home">
        <div id="searchresults">
          <?php require('header.php'); ?>
          <h1>Single Record Information</h1>
          <table class="singleTable display dataTable">
            <thead>
              <tr>
                <th>Column</th>
                <th>Data</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $cemeteryName = '';
                $typeCode = '';
                // get column names
                $columns = retrieveColumns($tableName, 0, $conn);
                // get rows from the database
                $row = sqlsrv_fetch_array($stmt);

                if(isset($row['CemID'])){
                  $cemID = $row['CemID'];

                  $sqlCemName = "SELECT CemDescr, CemLink, CemCode FROM Cemeteries WHERE CemCode = ?";
                  $stmtCemName = sqlsrv_query($conn, $sqlCemName, array($cemID), array( "Scrollable" => 'static' ));
                  if ($stmtCemName === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

                  $rowCemName = sqlsrv_fetch_array($stmtCemName);
                  //for($i=0; $i<sizeof($rowCemName)-1;$i++){
                      $cemeteryName = $rowCemName[0];
                      $cemLink = $rowCemName[1];
                      $id = $rowCemName[2];
                      //echo $rowCemName[$i];
                  //}
                }

                if(isset($row['TypeCode'])){
                  $typeID = $row['TypeCode'];
                  $sqlTypeCode = "SELECT TypeDescr FROM TypeCodes WHERE TypeID = ?";
                  $stmtTypeCode = sqlsrv_query($conn, $sqlTypeCode, array($typeID), array( "Scrollable" => 'static' ));

                  if ($stmtTypeCode === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

                  $rowTypeCode = sqlsrv_fetch_array($stmtTypeCode);
                  for($i=0; $i<count($rowTypeCode)-1;$i++){
                    $typeCode = $rowTypeCode[$i];
                  }
                }

                if(isset($row['BookCode'])){
                  $sqlBookCode = "SELECT BookTitle, Author, ID, DEWEY, BookDescr FROM Books WHERE BookCode = ?";
                  $stmtBookCode = sqlsrv_query( $conn, $sqlBookCode, array($row['BookCode']), array( "Scrollable" => 'static' ));

                  if ($stmtBookCode === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

                  $rowBookCode = sqlsrv_fetch_array($stmtBookCode);
                  $bookTitle = $rowBookCode[0];
                  $author = $rowBookCode[1];
                  $id = $rowBookCode[2];
                  $dewey = $rowBookCode[3];
                  $bookdescr = $rowBookCode[4];
                }

                if(isset($row['PaperCode'])){
                  $sqlPaperCode = "SELECT NameOfNewspaper FROM Newspapers WHERE NewspaperCode = ?";
                  $stmtPaperCode = sqlsrv_query( $conn, $sqlPaperCode, array($row['PaperCode']), array( "Scrollable" => 'static' ));

                  if ($stmtPaperCode === false) {
                    die(print_r(sqlsrv_errors(), true));
                  }

                  $rowPaperCode = sqlsrv_fetch_array($stmtPaperCode);
                  $newspaperName = $rowPaperCode[0];
                }

                $pageNum = isset($row['PageNumber'])? $row['PageNumber']: null;

                // we dont want to retrieve the last column, Status Code, so it goes
                // until size of columns array -1
                echo "<tr><td>Table</td><td>$tableName</td></tr>";

                for ($i = 0; $i < count($columns)-1; $i++) {
                  if ($columns[$i] == 'CemeteryID') {
                    echo "<tr><td>Cemetery</td><td><a href='singleRecord.php?tablename=Cemeteries&id=$id'>$cemeteryName</a></td></tr>";
                    echo "<tr><td>CemLink</td><td><a href='$cemLink'>".$cemLink."</a></td>";
                    $fileName = $typeID.$cemID;
                    if (file_exists('../../PDF/CemeteryTranscription/'.$fileName.'.pdf')){
                      echo "<tr><td>Cemetery PDF</td><td>";
                      if ($pageNum !== null){
                        echo "<a href='../../PDF/singlePage.php?fileName=".$fileName."&amp;pageNum=".$pageNum."&amp;path=CemeteryTranscription'>Single Page</a> | ";
                      }
                      echo "<a href='../../PDF/wholeDocument.php?fileName=".$fileName."&amp;path=CemeteryTranscription'>Whole Document</a></td></tr>";
                    }
                  } elseif ($columns[$i] == 'CemLink'){
                    echo "<tr><td>CemLink</td><td><a href='".$row[$i]."'>".$row[$i]."</a></td></tr>";
                  } elseif ($columns[$i] == 'TypeCode') {
                    echo "<tr><td>" . $columns[$i] . "</td><td>" . $typeCode . "</td>";
                  } elseif ($columns[$i] == 'BookCode') {
                    echo "<tr><td>" . $columns[$i] . "</td><td>" . $row[$i] . "</td>";
                    echo "<tr><td>DEWEY</td><td>$dewey</td></tr>";
                    echo "<tr><td>BookDescr</td><td>$bookdescr</td></tr>";
                    echo "<tr><td>Title</td><td><a href='singleRecord.php?tablename=Books&id=$id'>$bookTitle</a></td>";
                    echo "<tr><td>Author</td><td>$author</td>";
                  } elseif ($columns[$i] == 'PaperCode') {
                    echo "<tr><td>" . $columns[$i] . "</td><td>" . $newspaperName . "</td>";
                  } else {
                    echo "<tr><td>" . $columns[$i] . "</td><td>" . $row[$i] . "</td>";
                  }
                }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </body>
</html>
