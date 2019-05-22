<?php
//this page is super simple and wont be a permient feature.
//if this page is to stay on the site then we'll have to add form checking.
//right now the form is very easy to hack!! Plus there is no custom css

	require('../db/memberCheck.php');
  require('../errorReporter.php');
	require('../db/errorFormConnection.php');

	$databaseMessage = "";
	$errorMessage = "";

	if( isset($_POST['name']) ){
		//check to see if the user entered anything in name and description.
		//these are the only required areas.
		if( $_POST['name'] != "" && $_POST['description'] != "") {
			//find the data inside the form
			$username = $_POST['name'];
			$title = $_POST['title'];
			$userDescr = $_POST['description'];
			$userEmail = $_POST['email'];

			//make a query for the database

			$query_to_insert = "INSERT INTO dbo.ErrorForm (name, title, descr, email) VALUES  (?, ?, ?, ?)";
			$values = array($username, $title, $userDescr, $userEmail);

      //send to database
			$results = sqlsrv_query($conn , $query_to_insert, $values);//$conn->query($query_to_insert);
			
			if( is_resource($results) ){
				//check to see if it was sent
				$databaseMessage = "Added your report to the database.";
				//redirct to the home page?
			} else {
				//check if it didn't get saved to the database.
				//let them try again.
				$databaseMessage = "Failed to send your report please try again.";
			}
		}else{
			//send the user an error message when they dont enter the right data
			if($_POST['name'] == "" && $_POST['description'] == ""){
				//this is when they dont enter a name and description
				$errorMessage = "Please enter a name and description of the error.";
			}else if($_POST['name'] == ""){
				//when they dont enter a name
				$errorMessage = "Please enter your name.";
			}else{
				//when they dont have description
				$errorMessage = "Please enter a description of what went wrong.";
			}
		}
	}
?>


<!DOCTYPE HTML>
<html lang="en-US">
	<head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

        <title>MGS <?= (isset($_SESSION['uname']) && strtolower($_SESSION['uname']) === 'inhouse')
          ? 'Library' : 'Member' ?> </title>

        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">
        <link rel="stylesheet" href="../css/normalize.css">
        <link rel="stylesheet" href="../css/main.css">
        <script src="/js/vendor/modernizr-2.6.2.min.js"></script>
    </head>
	<body>
		<div id="resultsbackground">
			<div id="container" class="home">
				<div id="searchresults">
          <?php require('header.php'); ?>
				</div>
        <div id="errorForm" >
          <?php echo "<p class='successColor'>$databaseMessage</p>" ?>
          <h1>Error Report</h1>
          <p>
            Please submit the form with as much information as you can. <br />
            We'll try and fix the problem as fast as we can. <br />
            Only the name and description is required.
          </p>
          <font color="red"> <?= $errorMessage ?> </font>
          <form method="post" >
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" placeholder="John Doe" />
            <label for="email">Email: </label>
            <input type="email" id="email" name="email" placeholder="Example@examples.com"/>
            <h6 id="inprotantInfo">
              *We'll be using your email to contact you if we have further questions about the error. <br />
              Feel free to leave it blank if you don't wish to be contacted.
            </h6>
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" placeholder="Example: Search error"/>
            <label for="description">Description of how the error occured:</label>
            <textarea rows="5" cols="50" id="description" name="description" placeholder="Example: When I'm searching under my last name there's some random code/words and numbers showing up at the top of the page."></textarea>
            <br /> <br/>
            <input type="submit" />
          </form>
        </div>
      </div>
		</div>
	</body>
</html>
