
<?php
  require('../db/adminCheck.php');
  require('../errorReporter.php');
  require('../db/adminConnection.php');

  // if session is not started yet, start the session
  if (session_id() == '') {
    session_start();
  }
?>

<!DOCTYPE HTML>
<html>
  <head>
    <meta charset="utf-8">
    <?php header('X-UA-Compatible: IE=edge,chrome=1'); ?>
    <title>Adminstration Panel</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->

    <link rel="stylesheet" href="../css/normalize.css">
    <link rel="stylesheet" href="../css/main.css">
    <script src="../js/vendor/modernizr-2.6.2.min.js"></script>
  </head>
  <body>
    <!-- this element is not a table, but this element must have the table id
         or the links in the header are not clickable on internet explorer. -->
    <div id="resultsbackground_table">
      <div id="container" class="home">
        <div id="searchresults">
          <?php require('header.php'); ?>
        </div>
      </div>
    </div>
    <div class="pageAlign" style="padding-top:20px; padding-left:12%;">
      <?php require('../member/typelegend.php'); ?>
    </div>
    <form action="pdfUpload.php" method="post" enctype="multipart/form-data">
      <div class="uploadDiv" style="padding-top:30px">
        <h2>PDF Upload</h2>
        <ul>
          <li><b>File must be ".PDF" format.</b></li>
          <li><b>To upload a file, select the file from where it is stored on the computer.</b></li>
        </ul>
        <div id="cem">
          <p><b>For Cemetery Transcriptions:</b></p>
          <ul>
            <li><b>You must enter a Cemetery Code for the file.</b></li>
            <li><b>The Cemetery Code is the code that the cemetery is associated with.</b></li>
            <li><b>A Cemetery Code MUST start with two letters and end with 4 digits.</b></li>
          </ul>
          <p><b>Example 1: If your Cemetery Code is #4 you must fill the other 3 digits with 0's (0004).</b></p>
          <p><b>Example 2: Woodlands St. George's Anglican Cemetery is Cemetery Code 0637.</b></p>
          <p><b>Example 3: A Cemetery Transcription would be ct0637.</b></p>
        </div>
        <br />
        <div id="other">
          <p><b>For Others:</b></p>
          <ul>
            <li><b>You must enter an item number for the file.</b></li>
            <li><b>For each type code (see event legend above), the item number will start at 0001 and increment as files are added.</b></li>
            <li><b>The filename MUST start with the type code letter(s) and end with 4 digits</b></li>
          </ul>
          <p><b>Example: If the item number is #4 you must fill the other 3 digits with 0's (0004).</b></p>
          <p><b>Example 2: An obituary would be o0001.</b></p>
        </div>
        <div class="errorColor">
          <?php
            // if message is stored in a session variable
            // display the message
            // after that, set it to null ("")
            if (isset($_SESSION['pdfmessage'])) {
              echo $_SESSION['pdfmessage'];
              $_SESSION['pdfmessage'] = "";
            }
          ?>
        </div>
        <br />
        <input type="file" class="searching" name="file" class="file"><br>
        <input type="text" class="searching" pattern='([A-Za-z]{2}[0-9]{4})|([A-Za-z]{1}[0-9]{4})' title="aa0000" placeholder="Type Code" name="TypeCode" id="TypeCode">
        <input type="submit" name="submit" class="submit" value="Submit">
      </div>
    </form>

    <hr />

    <form action="generationsUpload.php" method="post" enctype="multipart/form-data">
      <div class="uploadDiv">
        <h2>Generations Upload</h2>
        <div class="errorColor">
          <?php
            // if message is stored in a session variable
            // display the message
            // after that, set it to null ("")
            if (isset($_SESSION['genmessage'])) {
              echo $_SESSION['genmessage'];
              $_SESSION['genmessage'] = "";
            }
          ?>
        </div>
        <br />
        <input type="file" class="searching" name="file" class="file">
        <input type="submit" name="submit" class="submit" value="Submit">
      </div>
    </form>

    <hr />

    <form action="productUpload.php" method="post" enctype="multipart/form-data">
      <div class="uploadDiv">
        <h2>Products Upload</h2>
        <ul>
          <li><b>This is for uploading products for the Store that can be downloaded.</b></li>
          <li><b>Spaces shouldn't be in the file names because browsers have trouble downloading them.</b></li>
          <li><b>Don't forget to add the product under Store Management.</b></li>
        </ul>
        <div class="errorColor">
          <?php
            if (isset($_SESSION['productmessage'])) {
              echo $_SESSION['productmessage'];
              $_SESSION['productmessage'] = "";
            }
          ?>
        </div>
        <br />
        <input type="file" class="searching" name="file" class="file">
        <input type="submit" name="submit" class="submit" value="Submit">
      </div>
    </form>
  </body>
</html>
