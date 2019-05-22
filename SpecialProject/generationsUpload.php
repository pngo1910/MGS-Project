
<?php
  require('../db/adminCheck.php');
?>

<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Adminstration Panel</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->

    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/main.css">
    <script src="js/vendor/modernizr-2.6.2.min.js"></script>
    <script type="text/javascript">
      // When the View the PDF button is clicked, the pdf either loads the wrong file or doesn't load at all
      // To fix this, the page needs to refresh but without prompting the user to reload the page
      //   window.onload = function() {
      //     if(!window.location.hash) {
      //         window.location.href = window.location.pathname + '#loaded';
      //     }
      // }
    </script>
  </head>

  <body>
    <div id="resultsbackground_table">
      <div id="container" class="home">
        <div id="searchresults">
          <?php require('header.php'); ?>
        </div>
      </div>
    </div>

    <?php
      if ($_FILES["file"]["error"] > 0) {
        echo "Error: " . $_FILES["file"]["error"] . "<br>";
      }

      $name = $_FILES['file']['name'];
      $tmp = $_FILES['file']['tmp_name'];

      if (preg_match('/\.pdf$/i', $name) && $_FILES['file']['type'] === 'application/pdf') {
        if (preg_match('/^ (\d{4}) _ V(\d{2,}) N(\d{2,}) \. pdf$/ix', $name)) {
          if (file_exists("../generations/$name")) {
            move_uploaded_file($tmp, "../generations/tmp/$name");

            echo "<div class='inputPDF'>";
            echo "<p>A file named $name already exists, would you like to replace it?</p>";
            echo "<form action='generationsDelete.php' method='post'>";
            echo "<input type='hidden' name='file' value='$name'>";
            echo "<input type='submit' class='submit' name='yes' value='yes'>";
            echo "<input type='submit' class='submit' name='no' value='no'>";
            echo "</form>";
            echo "<a href='../generations/$name' target='_blank'><button>View the PDF</button></a>";
            echo "</div>";
          } else {
            move_uploaded_file($tmp, "../generations/$name");
            $_SESSION['genmessage'] = "PDF uploaded.";
            header("location: pdfUploadForm.php");
          }
        } else {
          $_SESSION['genmessage'] = 'PDF name is not formatted correctly.';
          header('location: pdfUploadForm.php');
        }
      } else {
        $_SESSION['genmessage'] = 'Must upload a .pdf file.';
        header('location: pdfUploadForm.php');
      }
    ?>
  </body>
</html>
