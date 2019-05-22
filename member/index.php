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

    <title>MGS <?= (isset($_SESSION['uname']) && strtolower($_SESSION['uname']) === 'inhouse')
      ? 'Library' : 'Member' ?> </title>

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
      <div id="resultsbackground">
        <div id="container" class="home">
          <div id="searchresults">
            <?php require('header.php'); ?>

<div style="width:25%;overflow:hidden;float:right;"><!--right hand column -->

<div style="height:80px;padding-right:40px;">
            <?php require('balanceWidget.php'); ?>
</div>

<!-- FACEBOOK widget code -->
<div style="margin-top:20px;background-color:white;">
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = 'https://connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v2.12&appId=1609865052672387&autoLogAppEvents=1';
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script> 

<div class="fb-page" data-height="600" data-href="https://www.facebook.com/mani.mbgenealogy" data-tabs="timeline" data-small-header="false" data-adapt-container-width="true"
 data-hide-cover="false" data-show-facepile="true"><blockquote cite="https://www.facebook.com/mani.mbgenealogy" class="fb-xfbml-parse-ignore">
<a href="https://www.facebook.com/mani.mbgenealogy">MANI</a></blockquote></div>
</div>

</div><!--end of right hand column -->

<div style="width:70%;margin-right:30%;overflow:hidden;"><!--left hand column -->
<div style="margin-top:40px;">
<div id="mgslogo" style="width:313px;text-align:right;float:left;"><a href="search.php">
<img width="290" style="border:1px solid black;" src="../img/mani.jpg" style="border:none;" /></a></div>
    <div style="margin-left:325px;">
      <h1 class="speal">Welcome to MANI</h1>
	<p>A project of the <a href="http://www.mbgenealogy.com">Manitoba Genealogical Society</a>.</p>

<p><a href="search.php"><strong>Go here to SEARCH Mani records</strong></a></p>
             
</div>
</div>

<br clear="all" />

<div style="margin-top:40px;">
<div id="mgslogo" style="width:313px;text-align:right;float:left;"><a href="../docs/index.php">
<img width="291" src="../img/mgs-members-only-area.png" style="border:none;" /></a></div>
    <div style="margin-left:325px;">
      <h1 class="speal">MGS Members Only Area</h1>
              <p class="speal">
                Manitoba Genealogical Society Members only: Documents, MGS Minutes</p>  

<p><a href="../docs/index.php"><strong>Members Only Area</strong></a></p>
</div>
</div>

<br clear="all" />

<div style="margin-top:40px;">
<div id="mgslogo" style="width:313px;text-align:right;float:left;"><a href="../member/generations.php">
<img width="180" src="../img/cover.jpg" style="border:none;" /></a></div>
    <div style="margin-left:325px;">
      <h1 class="speal">Generations Magazine</h1>
              <p class="speal">Manitoba Genealogical Society's quarterly journal</p>  

<p>
<!-- Pull the link to the latest issue of Generations -->
<?php
  $dir    = 'C:\inetpub\wwwroot\generations';
  $files = array_diff(scandir($dir,1), array('.', '..'));
  for ($i=0; $i<3; $i++) {
	if ($files[$i] != "index.php") {
		if ($files[$i] != "tmp") {	
		$filename = $files[$i]; 

	echo "<a target='_blank' href='/generations/$filename'><strong>Latest Issue</strong></a>";
		}
	}
  } 
?>
<!-- End pf link to the latest issue of Generations -->   
and 
<a href="generations.php"><strong>Archive of past issues</strong></a></p>
  
 </div> <!-- end of left hand column -->      

</div>
</div>


         
        </div>
      </div>
    </div>
  </body>
</html>
