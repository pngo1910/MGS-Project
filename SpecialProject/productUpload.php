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
    <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
    <script type="text/javascript">
    	//When the View the PDF button is clicked, the pdf either loads the wrong file or doesn't load at all
    	//To fix this, the page needs to refresh but without prompting the user to reload the page
	    window.onload = function() {
		    if(!window.location.hash) {
		        window.location.href = window.location.pathname + '#loaded';
		    }
		}
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

	//	check for file errors, and display the errors
	if ($_FILES["file"]["error"] > 0)
	{
		echo "Error: " . $_FILES["file"]["error"] . "<br>";
	}

	$file = $_FILES["file"]["name"];
  if(strpos($file, ' ') !== FALSE){
    $file = preg_replace('/\s*/', "", $file);
  }
	//	match the file name with the reg ex
	if(preg_match('/\.pdf$/i', $file))
	{
  		//	check if file exists
  		if (file_exists("../PDF/Store/" . $file))
    	{
    		//	move temp file to the pdftemp folder
    		move_uploaded_file($_FILES["file"]["tmp_name"],	'../PDF/tmp/' . $file);
    		//	php code to create html script
    		//	show a message that file already exists
    		//	a form to delete the file

    		echo "<div class='inputPDF'>";
    		echo "<p>" . $file . ".pdf already exists, would you like to replace it?</p>";
  			echo "<form action='deleteProductPDF.php?file=$file' method='post'>";
  				echo "<input type='submit' class='submit' name='YES' value='YES' />";
  				echo "<input type='submit' class='submit' name='NO' value='NO' />";
  			echo "</form><br />";
  			echo "<a href='../PDF/Store/" . $file."' target='_blank'><button>View the PDF</button></a>";
  			echo "</div>";
    	}
    	//	if file does not exist
  	else
    	{
    		//	move the file to the pdf folder
    		move_uploaded_file($_FILES["file"]["tmp_name"],	'../PDF/Store/' . $file);
    		//	store a message in the session
    		$_SESSION['productmessage'] = "PDF Uploaded";
    		//	redirect to the pdfUploadForm file
    		header("Location: pdfUploadForm.php");
    	}
	}

	//	file name does not match the reg ex
	else
	{
		//	store the message in the session variable
	$_SESSION['productmessage'] = "Must upload a .pdf file.";
		header("Location: pdfUploadForm.php");
	}
?>
</body>
</html>