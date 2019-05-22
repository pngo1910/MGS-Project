<?php
  require('../db/storeConnection.php');
  require('../errorReporter.php');
  $name = isset($_GET['name'])? $_GET['name']: 'store';
    switch($name){
      case 'login':
            require('../db/loginCheck.php');
            require('../db/memberConnection.php');
            break;
        default:
            session_name($name);
            session_start();
    }
  $message = "";
  if($_GET['confirm'] == "true"){
    $values = $_SESSION['values'];
    foreach($values as $key => $value){
      if($key % 2 === 0)
        $ids[] = $value;
      else
        $quantities[] = $value;
    }
    $names = array();

    $table = "Products";
    $dir = "Store";
    if(isset($_SESSION['municipality']) && $_SESSION['municipality'] != "" && !empty($_SESSION['municipality'])){
      $table = "CemeteryTranscriptions";
      $dir = "CemeteryTranscription";
    }

    for($i = 0; $i < count($ids); $i++){
      $qry = "SELECT Name FROM $table WHERE Download = ? AND ID = ?";
      $stmt = sqlsrv_query($storeConn, $qry, array(1, $ids[$i]));
      if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
      $downloadName = sqlsrv_fetch_array($stmt)['Name'];
      if($name != ''){
        $names[] = $downloadName;
      }
    }
    
    if(isset($_POST) && !empty($_POST)){
        $fileName = preg_replace('/\s*/', "", $_POST[0]);
        $file = "../PDF/". $dir . "/" . $fileName . ".pdf";

        if(file_exists($file)){
          header("location:../PDF/wholeDocument.php?fileName=$fileName&path=$dir");
        } else{
          $message = "There was a problem retrieving the download link for " . $_POST[0] . ". Please contact MGS to receive your purchase.<br /> Email: contact@mbgenealogy.com<br />Phone: 204-783-9139";
        }
        $_POST = array();
    }
  }
  header('X-UA-Compatible: IE=edge,chrome=1');

  //clear the shopping cart
  unset($_SESSION['values']);
?>
<!DOCTYPE HTML>
<html lang="en-US">
  <head>
    <meta charset="utf-8">
    <title>Manitoba Genealogical Society</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/main.css">
    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
  </head>
  <body>
    <div id="homeBackgroundFree"></div>
    <div id="container" class="home">
      <?php require('../header.php'); ?>
      <div id="searchresults">
        <p class="successColor">You have successfully purchased the chosen product(s).</p>
        <p>If any of the products you have purchased aren't required to be shipped, please click the download link(s) below. Make sure you have downloaded and saved them somewhere you can find them before leaving this page.
        <p class="errorColor"><?= $message ?></p>
        <?php $message = ""; ?>
        <form action="completePayment.php?confirm=true&amp;name=<?= $name ?>" method="POST" id="pdfdownload">
        <?php foreach($names as $value): ?>
        <p>Download <input type="submit" name="0" value="<?= $value ?>" /></p>
        <?php endforeach; ?>
        </form>
        <p><a href="/Store/store.php?test">Return to store</a></p>
      </div>    
    </div>
  </body>
</html>