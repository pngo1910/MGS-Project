<?php
  require('../../db/mgsConnection.php');
  require('../../db/memberCheck.php');
  require('../../errorReporter.php');

  if(!isset($_SESSION['error'])) $_SESSION['error'] = "";

  if(!isset($_SESSION['message'])) $_SESSION['message'] = "";
  require('payperviewQuery.php');

  if(isset($_POST) && !empty($_POST)){
      $fileName = $_SESSION['fileName'];
      if(empty($_SESSION['pageNum'])){
        header("location:../../PDF/wholeDocument.php?fileName=$fileName");
      } else{
        $pageNum = $_SESSION['pageNum'];
        header("location:../../PDF/singlePage.php?fileName=$fileName&pageNum=$pageNum");
      }
      $_POST = array();
  }

  header('X-UA-Compatible: IE=edge,chrome=1');
?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
  <head>
    <meta charset="utf-8">
    <title>Manitoba Genealogical Society</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->

    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/main.css">
    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
  </head>
  <body>
    <!--[if lt IE 7]>
      <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
    <![endif]-->

        <!-- Add your site or application content here -->
        <div id="resultsbackground">       
          <div id="container" class="home">
            <div id="searchresults">
              <?php require('header.php'); ?>
              <p><b>Your Pay-Per-View account has been charged $1.00.</b></p>
              <p><b>Your new account balance is $<?= number_format($credits / 4, 2, '.', '') ?> which is equal to <?= $credits ?> credits.</b></p>
              <a href="search.php"><button>Return to Search</button></a>
              <br />
              <br />
              <table>
                <tr>
                  <th>Item</th>
                  <th>Details</th>
                </tr>
                <?php for($i = 0; $i < count($values); $i++): ?>
                <tr>
                  <td><?= $values[$i] ?></td>
                  <?php $i++; ?>
                  <td><?= $values[$i] ?></td>
                </tr>
                <?php endfor; ?>
              </table>
              <p><b>The selected file is ready for download.</b></p>
              <form action="payperview.php" method="POST" id="pdfdownload">
              <p><input type="submit" name="0" value="Download" /></p>
              </form>
            </div>
          </div>
        </div>
  </body>
</html>
