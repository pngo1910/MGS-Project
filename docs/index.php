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
    <meta charset="utf-8">

    <title>MGS Members Only Area</title>

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

<style type="text/css">
#doclist li {padding-bottom:10px;}
</style>

  </head>
  <body>
    <!--[if lt IE 7]>
      <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
    <![endif]-->

      <!-- Add your site or application content here -->
      <div id="resultsbackground">
        <div id="container" class="home">
          <div id="searchresults">
            <?php require('../header.php'); ?>

<div>
<!-- PAGE TITLE -->
<h1>MGS Members Only Area</h1>

<div id="doclist">
<h2>MGS Documents</h2>

<div id="documents">
<ul id="doclist">

<li><a href="Constitution_and_Bylaws">Constitution and Bylaws</a></li>

<li style="padding:0px;">MANI Documents &#8628;</li>
	<ul>
		<li><a href="MANI_Docs">MANI Documents</a></li>
		<li><a href="MANI_Manuals">MANI Manuals</a></li>
		<li><a href="MANI_Newsletters_and_PR">MANI Newsletters and PR</a></li>
		<li><a href="MANI_Training">MANI Training</a></li>
	</ul>

<li><a href="Manuals">Manuals</a></li>

<li><a href="MGS_Docs">MGS Documents</a></li>

<li style="padding:0px;">Minutes &#8628;</li>
	<ul>
		<li><a href="Minutes/agm">AGM Minutes</a></li>
		<li><a href="Minutes/council">Council Minutes</a></li>
		<li><a href="Minutes/exec">Executive Minutes</a></li>
	</ul>

<li><a href="Newsletter">Newsletter</a></li>

<li><a href="Policy_Procedures_MGS">Policy & Procedures MGS</a></li>

<li><a href="Strategic_Plan">Strategic Plan</a></li>

<li><a href="Training">Training</a></li>


</ul>
</div>
</div>               
        </div>
      </div>
    </div>
  </body>
</html>
