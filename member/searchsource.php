<?php
    header('X-UA-Compatible: IE=edge,chrome=1');
  require('../db/memberCheck.php');

  if (!isset($_SESSION['error'])) $_SESSION['error'] = '';
?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
  <head>


    <!-- Global Site Tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-135619744-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'UA-135619744-1');
</script>



    <meta charset="utf-8">

    <title>MANI Source Search</title>

    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->

    <link rel="stylesheet" href="../css/normalize.css">
    <link rel="stylesheet" href="../css/main.css">
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
      <div id="resultsbackground" >
        <div id="container" class="home" >
          <div id="searchresults" >
            <?php require('header.php'); ?>
            <?php require('balanceWidget.php'); ?>
             <div id="introtext" style="width:100%;">
              <h1>MANI Search Source</h1>
 
<!-- newe code goes here -->


<P>You can use the “Source Search” to look at sources referenced in MANI including Newspapers, Cemeteries, Local History books etc. 
This can help you find information on Cemeteries or Newspapers that covered a specific area or to see
what additional information we have on each item</P>

<div style="width:100%;overflow:hidden;"><!--right hand column -->
  <div style=" float:left; width: calc(100% - 321px);">
      <div style="background-color:#99ffff;padding:8px;border:1px solid #999;">
          Welcome to our new Source Search feature. As our Volunteers add content to the individual table the information you see in each search will grow. We will be bringing online the items below one at a time starting with the Cemeteries .    
      </div>      
  </div>
  <img width="291" src="../img/search_source/search-source3.jpg" style="border:none; float:right;" />
  


<div style="clear:both;"></div>
<div id="content">

         

          <div style="margin:15px 0px;padding:10px;border-radius:10px;border:1px solid #999;">
            <ul style="margin:0px 15px;padding:0px 15px;">
              <div style="float: left; width: 50%;">
                <li><a href="https://mani.mbgenealogy.com/searchsource/book.php">MGS Indexed Books</a></li>
                <li><a href="https://mani.mbgenealogy.com/searchsource/cemetery.php">Cemeteries</a></li>
                <li><a href="https://mani.mbgenealogy.com/searchsource/church.php">Churches</a></li>
                <li><a href="https://mani.mbgenealogy.com/searchsource/funeralhome.php">Funeral Homes</a></li>
              </div>

              <div style="float: left; width: 50%;">
                <li><a href="https://mani.mbgenealogy.com/searchsource/newspaper.php">Newspapers</a></li></li>
                <li><a href="https://mani.mbgenealogy.com/searchsource/manitobia.php">Manitobia Local History Digital Books</a></li>
                <li><a href="https://mani.mbgenealogy.com/">extra</a>extra</li>
              </div>

            </ul>

            <br clear="all">


<div> 
</div>

</div><!--end of right hand column -->
<img scr="search-source3.jpg"/img>
<P>
new code</P>


        </div>
      </div>
    </div>
    </div>
<div style='clear:both'></div>
    <?php require('../footer.php'); ?>
    </div>
  </body>
</html>
