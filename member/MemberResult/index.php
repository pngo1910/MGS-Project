<?php
  header('X-UA-Compatible: IE=edge,chrome=1');
  require('../../db/memberCheck.php');

  if (!isset($_SESSION['error'])) $_SESSION['error'] = '';
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
    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
    <script src="/DataTables-1.10.6/media/js/jquery.js"></script>

    <script type="text/javascript">
      $(document).ready(function(){
        var rather = $('#rather');
        var quite = $('#quite');

        var disable = function(maybe) {
          return function() {
            if (rather.prop) {
              rather.prop('disabled', maybe);
              quite.prop('disabled', maybe);
            } else {
              rather.disabled = maybe;
              quite.disabled = maybe;
            }
          };
        };

        $('#fuzzy').click(disable(false));
        $('#exact').click(disable(true));
        $('#loose').click(disable(true));
      });
    </script>
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

            <div id="country">&nbsp;</div>
            <div id="introtext">
              <h1 class="speal">Welcome to the MANI<br/><br/>A project of the <a id="manilink" href="http://www.mbgenealogy.com">Manitoba Genealogical Society</a></h1>
              <p class="speal">
                The Manitoba Genealogical Society Inc. (MGS) is a non-profit organization formed in 1976 and incorporated in 1982.<br/>
                MANI will allow you to search the indexes of the MGS. You will be able to:
              </p>
              <ul class="speal">
                <li class="speal">Locate individuals that match your search criteria</li>
                <li class="speal">Discover the resource that was indexed and information on how to access further information</li>
                <li class="speal">In a later phase you will be able to purchase copies of the page or the complete MGS publication where the information exists</li>
              </ul>
              <p class="speal">
                The MANI database is growing and will have over one and a quarter million references indexed.
              </p>
            </div>
          <h2 class = "colorSearch">Search MANI Records:</h2>
          <p class = "errorColor"><?= $_SESSION['error'] ?></p>
          <?php $_SESSION['error'] = "";?>
          <form action="memberResult.php" method="post" id="memberresult">
            <label for="lname" class="searchinglabel">Last Name</label>
            <input type="text" class="searching" name="lname" id='lname' placeholder="Last Name" autofocus="autofocus">
            <label for="fname" class="searchinglabel">First Name</label>
            <input type="text" class="searching" name="fname" id='fname' placeholder="First Name"><br />
            <label for="start" class="searchinglabel">Start Year</label>
            <input type="number" placeholder="YEAR (YYYY)" class="searching" id='start' name="start" min="1000" max="9999">
            <label for="end" class="searchinglabel">End Year</label>
            <input type="number" placeholder="YEAR (YYYY)" class="searching" id='end' name="end" min="1000" max="9999"><br/>
            <div class="left">
            <input type="checkbox" name="nullyears" id="nullyears" checked="">
            <label for="nullyears" style="display:inline">Include records with no year values.</label>

            <br><br>
            <input type="radio" name="search-option" id="loose" value="1" checked="">
            <label for="loose" style="display:inline">Match anything containing the name.</label>

            <br><br>
            <input type="radio" name="search-option" id="exact" value="2">
            <label for="exact" style="display:inline">Match exact name.</label>
            </div>
            <div class="right">
            <input type="radio" name="search-option" id="fuzzy" value="3">
            <label for="fuzzy" style="display:inline">Match names that sound similar.</label>
            <br><br>

            <!-- <div id="name-range"> -->
              <input type="radio" disabled="" name="name-range" id="rather" value="3">
              <label for="rather" style="display:inline">less similar</label>
              <br><br>

              <input type="radio" disabled="" name="name-range" id="quite" value="4">
              <label for="quite" style="display:inline">most similar</label>
            <!-- </div> -->
            </div>
            <div id="searchsubmit">
              <br>
              <input class="submit" value="Search" type="submit">
            </div>
          </form>
        </div>
      </div>
    </div>
  </body>
</html>
