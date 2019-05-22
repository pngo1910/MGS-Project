<?php
  require('errorReporter.php');
  require ('db/memberConnection.php');
  header('X-UA-Compatible: IE=edge,chrome=1');

  // if session is not set, start the session
  if (!isset($_SESSION)) {
    session_start();
  }

  // if session freeError variable is not set, set it to null ("")
  if (!isset($_SESSION['error'])){
    $_SESSION['error'] = '';
  }
?>

<!DOCTYPE HTML>
<html lang="en-US">
  <head>
    <meta charset="utf-8">
    <title>Manitoba Genealogical Society</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/main.css">
    <script src="js/vendor/modernizr-2.6.2.min.js"></script>
  </head>
  <body>
    <div id="resultsbackground"></div>
    <div id="container" class="home">
      <?php require('header.php'); ?>
      <!-- display the error, if occurs -->
      <?php echo $_SESSION['error']; ?>
      <!-- destroy the session -->
      <?php session_destroy(); ?>
      <div id="searchresults">
        <div id="country">&nbsp;</div>
        <div id="introtext">
          <!-- <h1 style="color:red">Use the same username and password</h1> -->
          <h1 class="speal">Welcome to the Members Only website of the <br/><br/><a id="manilink" href="http://www.mbgenealogy.com">Manitoba Genealogical Society</a></h1>
          <p class="speal">
            The Manitoba Genealogical Society Inc. (MGS) is a non-profit organization formed in 1976 and incorporated in 1982.<br/><br/>
            A major component of this members only website is MANI, the Manitoba Name Index.<br/>MANI will allow MGS Members to search the indexes of the MGS.<br/>You will be able to:
          </p>
          <ul class="speal">
            <li class="speal">Locate individuals that match your search criteria</li>
            <li class="speal">Discover the resource that was indexed and information on how to access further information</li>
            <li class="speal">In a later phase you will be able to purchase copies of the page or the complete MGS publication where the information exists</li>
          </ul>
          <p class="speal">
            The MANI database is growing and will have over one and a quarter million references indexed. We will also be adding additional members only features in the future.
          </p>
          <ul class="speal">
            <li class="speal">Our <a target="_blank" href="/store/index.php">Store</a> is now accessible to the public</li>
          </ul>
        </div>
        <h2 class = "colorSearch">Free Search of MANI Records:</h2>
        <p class="speal">Non members can search below to find out the number of records contained in MANI that match their search criteria.
You can join MGS and get access using the Register link above.
        </p>
        <form action="freeResult.php" method="post">
          <input type="text" placeholder="Last Name" class="searching" name="lname">
          <input type="text" placeholder="First Name" class="searching" name="fname"><br />
          <span class="freeLink"><input type="submit" class="submit" name="Submit" value="Search"></span>
          <!-- <span class="memberLink"><a href="memberLogin.php">Already A Member?</a></span> -->
        </form>

	<!-- add links for MGS research and MGS store -->
        <h2 class = "colorSearch">MGS Services:</h2>
	<div style="margin:15px 0px;">
		<div style="width:260px;float:left;text-align:center;">
		<a href="https://mani.mbgenealogy.com/Research/index.php"><img src="https://mani.mbgenealogy.com/images/mgs-research-service.jpg" style="width:220px;" /><br />MGS Research Service</a>
		</div>
		<div style="width:260px;margin-left:280px;;text-align:center;">
		<a href="https://mani.mbgenealogy.com/store/index.php"><img src="https://mani.mbgenealogy.com/images/MGS-online-store.jpg"  style="width:220px;" /><br />MGS Online Store</a>
		</div>
	</div>
      </div>
    </div>
  </body>
</html>
