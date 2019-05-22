<?php
  //connect to the member database
  require('db/memberConnection.php');

  $name = session_name();
  session_destroy();
  unset($_COOKIE[$name]);
  setcookie($name, null, -1, '/');

  session_name('login');
  session_start();

  //set varibles needed for query
  $counter = 0;

  $_SESSION['verified'] = true;
  $_SESSION['type'] = 8;
  $_SESSION['access'] = 1;

  //Grab the password from shortcut
  $password = $_GET['shadow'];




  // ***** Really wanted this one to work :(  ***** //

  // //Path to file location
  // $path = 'C:\ShadowsEverywhere.txt';
  // // going to the desktop isn't needed   Users\Rachelle\Desktop\

  // //check if this file exists
  // if( file_exists($path) ){
  //   //get the password from the .txt file
  //   //This function is only grabing 13 characters starting from the 289th.
  //   $password = file_get_contents($path, NULL, NULL, 289, 13);
  // } else {
  //   //if the computer doesn't have the file send them a error.
  //   $_SESSION['error'] = "It seems your trying to access the site from outside MGS Inhouse location.";
  //   header("location:memberLogin.php");
  // }




  // *****     Checking the username and password ***** //

  //encrpyt the password
  $encrypted = hash("sha512", $password);

  $sql = "SELECT 1 FROM Members WHERE Username = 'inhouse' AND Password = ? AND Verified = 1";
  $stmt = sqlsrv_query($userConn, $sql, array($encrypted));

  if ($stmt === false) errorReport(sqlsrv_error(), __FILE__, __LINE__);

  // loop through the rows returned
  // and increment the counter variable
  while (sqlsrv_fetch_array($stmt)) $counter++;

  //	if counter is 1 i.e if there is a row returned
	//	store the username and password in the session
	//	redirect to the member search page
	if($counter==1){
		$_SESSION['uname'] = 'inhouse';
		$_SESSION['pword'] = $encrypted;
		header("location:/member/");
	}

	//	if there isnt any row returned
	//	store an error in the session
	//	redirect to the index page
	else {
		$_SESSION['error'] = "The password on this computer doesn't match the current password being used.";
		header("location:/login.php");
	}
?>
