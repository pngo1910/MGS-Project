<?php
	require('../db/adminCheck.php');
	require('../db/mgsConnection.php');
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

	//	if TypeCode is posted and is not null
	if(isset($_POST['TypeCode']) && ($_POST['TypeCode'] != ""))
	{
		//	declare the local variable with post global variable
		$TypeCode = $_POST['TypeCode'];

		//	put that local variable into a session variable
		$_SESSION['pdfTypeCode'] = $TypeCode;

		//Get the type code letters at the beginning of the name
		preg_match('/^\D*(?=\d)/', $TypeCode, $m);
		$folder = $m[0];

		//Get the type description that matches the type code
		$qry = "SELECT TypeDescr FROM TypeCodes WHERE TypeID = ?";
		$stmt = sqlsrv_query($conn, $qry, array($folder));
		if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
		$folder = sqlsrv_fetch_array($stmt)['TypeDescr'];
			
		//Find if there's a forward slash in the folder name
		if (strpos($folder, '/') !== FALSE){
			//Replace the forward slash with an underscore
			$folder = preg_replace('/\//', "_", $folder);
			//Find if there's whitespace in the type description
			if(strpos($folder, ' ') !== FALSE){
				//Capitalize the words in the description
				$folder = ucwords($folder);
			}
		} else{
			//Capitalize the words in the description
			$folder = ucwords($folder);
		}
		//Replace any whitespace with nothing
		$folder = preg_replace('/\s*/', "", $folder);
		//Add a forward slash to the end of the name
		$folder .= "/";

		//	check if TypeCode's length is 5 or 6
		if(strlen($TypeCode) == 5 || strlen($TypeCode) == 6)
		{
			//	match the file name with the reg ex
			if(preg_match('/\.pdf$/i', $_FILES["file"]["name"]))
			{
				//	check if file exists
				if (file_exists("../PDF/$folder" . $TypeCode.".pdf"))
		      	{
		      		//	move temp file to the pdftemp folder
		      		move_uploaded_file($_FILES["file"]["tmp_name"],	'../PDF/tmp/' . $TypeCode.".pdf");
		      		//	php code to create html script
		      		//	show a message that file already exists
		      		//	a form to delete the file

		      		echo "<div class='inputPDF'>";
		      		echo "<p>" . $TypeCode . ".pdf already exists, would you like to replace it?</p>";
					echo "<form action='deletePDF.php?folder=$folder' method='post'>";
						echo "<input type='submit' class='submit' name='YES' value='YES' />";
						echo "<input type='submit' class='submit' name='NO' value='NO' />";
					echo "</form>";
					echo "<a href='../PDF/$folder" . $TypeCode . ".pdf' target='_blank'><button>View the PDF</button></a>";
					echo "</div>";
		      	}
		      	//	if file does not exist
		    	else
		      	{
		      		//	move the file to the pdf folder
		      		move_uploaded_file($_FILES["file"]["tmp_name"],	"../PDF/$folder" . $TypeCode . ".pdf");
		      		//	store a message in the session
		      		$_SESSION['pdfmessage'] = "PDF Uploaded";
		      		//	redirect to the pdfUploadForm file
		      		header("Location: pdfUploadForm.php");
		      	}
		  	}

		  	//	file name does not match the reg ex
		  	else
		  	{
		  		//	store the message in the session variable
				$_SESSION['pdfmessage'] = "Must upload a .pdf file.";
		  		header("Location: pdfUploadForm.php");
		  	}
		}

		//	if TypeCode length is not 5 or 6
		else
		{
			//	store the message in the session variable
			$_SESSION['pdfmessage'] = "Type Code must be 5 or 6 characters long.";
	  		header("Location: pdfUploadForm.php");
  		}
  	}

  	//	if TypeCode is not posted or is null
  	else
  	{
  		//	store the message in the session variable
		$_SESSION['pdfmessage'] = "File name must be entered.";
		header("Location: pdfUploadForm.php");
  	}
?>
</body>
</html>
