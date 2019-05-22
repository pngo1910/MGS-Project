<?php
  require('../db/memberCheck.php');
  require('../errorReporter.php');
  require('../db/mgsConnection.php');
  require('../retrieveColumns.php');

  if (isset($_GET['tablename']) && isset($_GET['id'])) {
    list($tableName, $recordID) = array($_GET['tablename'], $_GET['id']);
  } elseif(isset($_GET['tablename']) && isset($_GET['uniqueid'])) {
    list($tableName, $recordID) = array($_GET['tablename'], $_GET['uniqueid']);
  } else {
    header("Location: /member/");
  }

  $id = ($tableName === 'Cemeteries') ? 'CemCode' : 'ID';
  $ID = ($tableName === 'Churches') ? 'ChurchID' : 'ID';
  $uniqueID = ($tableName === 'CemeteryTranscriptions') ? 'CemeteryCode' : 'UniqueID';
  //$ruralCode = ($tableName === 'ObituariesRural')? 'ID':'ID';
  
  //Checks if the table is CemeteryTranscriptions for the unique id variable
  if ($tableName === 'CemeteryTranscriptions') {
    $uniqueid = $_GET['uniqueid'];
    $sql = "SELECT * FROM CemeteryTranscriptions WHERE uniqueID = $uniqueid"; 
  }else{
    $sql = "SELECT * FROM $tableName WHERE $id = ?"; 
  } 
  $stmt = sqlsrv_query($conn, $sql, array($recordID));
  if ($stmt === false) {
    errorReport(sqlsrv_errors(), __FILE__, __LINE__);
  }

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
    <script src="/js/vendor/jquery.min.js"></script>
    <script src="/js/plugins.js"></script>
  </head>
  <body>
    <div id="resultsbackground">
      <div id="container" class="home">
        <div id="searchresults">
          <?php require('header.php'); ?>
          
          <h1>Single Record Information - <?=$tableName?></h1>
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
                if(isset($row['CemeteryCode'])){
                  $cemID = $row['CemeteryCode'];

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

                if(isset($row['CTPictures'])){
                  $ctPics = $row['CTPictures'];
                  $sqlCtPics = "SELECT CTPictures FROM CemeteryTranscriptions WHERE UniqueID = $uniqueid";
                  $stmtCTPics = sqlsrv_query($conn, $sqlCtPics, array($ctPics), array( "Scrollable" => 'static' ));

                  if ($stmtCTPics === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

                  $rowCTPictures = sqlsrv_fetch_array($stmtCTPics);
                    $ctPictures = $rowCTPictures[0];
                }

                if(isset($row['ChurchID'])){
                  $churchID = $row['ChurchID'];
                  $sqlChurchID = "SELECT ChurchName, Municipality, Denomination, ID FROM Churches WHERE ID = ?";
                  $stmtChurchID = sqlsrv_query($conn,$sqlChurchID, array($churchID), array("Scrollable" => 'static'));

                  if ($stmtChurchID === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

                  $rowChurchID = sqlsrv_fetch_array($stmtChurchID);
                  $churchName = $rowChurchID[0];
                  $municipality = $rowChurchID[1];
                  $denomination = $rowChurchID[2];
                  $ID = $rowChurchID[3];

                }
                // if(isset($row['PaperCode'])){
                //   $paperCode = $row['PaperCode'];
                //   $sqlPaperCode = "SELECT NewspaperCode, NameOfNewspaper, Town, Municipality, ID FROM Newspapers WHERE ID = ?";
                //   $stmtPaperCode = sqlsrv_query($conn,$sqlPaperCode, array($paperCode), array("Scrollable" => 'static'));

                //   if ($stmtPaperCode === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

                //   $rowPaperCode = sqlsrv_fetch_array($stmtPaperCode);
                //   $newspaperCode = $rowPaperCode[0];
                //   $nameOfNewspaper = $rowPaperCode[1];
                //   $town = $rowPaperCode[2];
                //   $municipality = $rowPaperCode[3];
                //   $id = $rowPaperCode[4];

                // }



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
                  $sqlBookCode = "SELECT BookTitle, Author, ID, DEWEY, MGSURL, BookDescr FROM Books WHERE BookCode = ?";
                  $stmtBookCode = sqlsrv_query( $conn, $sqlBookCode, array($row['BookCode']), array( "Scrollable" => 'static' ));

                  if ($stmtBookCode === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

                  $rowBookCode = sqlsrv_fetch_array($stmtBookCode);
                  $bookTitle = $rowBookCode[0];
                  $author = $rowBookCode[1];
                  $id = $rowBookCode[2];
                  $dewey = $rowBookCode[3];
                  $URL = $rowBookCode[4];
                  $bookdescr = $rowBookCode[5];
                }

                if(isset($row['PaperCode'])){
                  $sqlPaperCode = "SELECT NameOfNewspaper, NewspaperCode, ID FROM Newspapers WHERE NewspaperCode = ?";
                  $stmtPaperCode = sqlsrv_query( $conn, $sqlPaperCode, array($row['PaperCode']), array( "Scrollable" => 'static' ));

                  if ($stmtPaperCode === false) {
                    die(print_r(sqlsrv_errors(), true));
                  }

                  $rowPaperCode = sqlsrv_fetch_array($stmtPaperCode);
                  $newspaperName = $rowPaperCode[0];
                  $newspaperCodes = $rowPaperCode[1];
                  $id = $rowPaperCode[2];
                }

                $pageNum = isset($row['PageNumber'])? $row['PageNumber']: null;

                // we dont want to retrieve the last column, Status Code, so it goes
                // until size of columns array -1
                echo "<tr><td>Table</td><td>$tableName</td></tr>";

                for ($i = 0; $i < count($columns)-1; $i++) {
                  if ($columns[$i] == 'CemeteryCode') {
                    echo "<tr><td>Cemetery</td><td><a href='singleRecord.php?tablename=Cemeteries&id=$id'>$cemeteryName</a></td></tr>";
                    echo "<tr><td>CemLink</td><td><a href='$cemLink'>Link</a></td>";
                  } elseif ($columns[$i] == 'CemLink'){
                    echo "<tr><td>CemLink</td><td><a href='".$row[$i]."'>".$row[$i]."</a></td></tr>";
                  }elseif ($columns[$i] == 'ChurchID') {
                    echo "<tr><td>ChurchName</td><td><a href='singleRecord.php?tablename=Churches&id=$ID'>$churchName</a></td>";
                    echo "<tr><td>Municipality</td><td>" . $municipality . "</td>";
                    echo "<tr><td>Denomination</td><td>" . $denomination . "</td>";
                  }elseif($columns[$i] == 'CTPictures'){
                    echo "<tr><td>CTPictures</td><td><a href='$ctPictures'>Link</a></td></tr>";
                  // }elseif ($columns[$i] == 'PaperCode') {
                  //   echo "<tr><td>NewspaperCode</td><td><a href='singleRecord.php?tablename=Newspapers&id=$ruralID'>$newspaperCode</a></td>";
                  //   echo "<tr><td>NewspaperName</td><td>" . $newspaperName . "</td>";
                  //   echo "<tr><td>Town</td><td>" . $town . "</td>"
                  //   echo "<tr><td>Municipality</td><td>" . $municipality . "</td>";
                  //   echo "<tr><td>Denomination</td><td>" . $denomination . "</td>";
                  } elseif ($columns[$i] == 'TypeCode') {
                    echo "<tr><td>" . $columns[$i] . "</td><td>" . $typeCode . "</td>";
                  } elseif ($columns[$i] == 'BookCode') {
                    echo "<tr><td>" . $columns[$i] . "</td><td>" . $row[$i] . "</td>";
                    echo "<tr><td>DEWEY</td><td>$dewey</td></tr>";
                     echo "<tr><td>URL</td><td><a href='$URL' Target='_New'>$URL</a></td></tr>";
                    echo "<tr><td>BookDescr</td><td>$bookdescr</td></tr>";
                    echo "<tr><td>Title</td><td><a href='singleRecord.php?tablename=Books&id=$id'>$bookTitle</a></td>";
                    echo "<tr><td>Author</td><td>$author</td>";
                  } elseif ($columns[$i] == 'PaperCode') {
                     echo "<tr><td>" . $columns[$i] . "</td><td><a href='singleRecord.php?tablename=Newspapers&id=$id'>" . $newspaperName . "</a></td>";
                    // echo "<tr><td>" . $columns[$i] . "</td><td>" . $newspaperName . "</td>";
                  } elseif ($columns[$i] == 'Owner') {
                    echo "<input type = 'hidden'/>";
                    
                  } elseif ($columns[$i] === 'Historic_Write_Up') {
                    echo "<tr><td>" . $columns[$i] . "</td><td><div class='more'>" . $row[$i] . "</div></td>";
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
