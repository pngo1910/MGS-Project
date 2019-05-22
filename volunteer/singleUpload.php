<?php
	require('../db/volunteerCheck.php');
  require('../errorReporter.php');
  require('../db/volunteerConnection.php');

    //gets the table name we will be using in the database
	if(isset($_GET['tableName'])){
		$tableName = $_GET['tableName'];
		$_SESSION['tableName'] = $tableName;
	}
	else{ //redirect back to uploadDashboard if we don't have the DB tablename
		header("Location:uploadDashboard.php");
	}

	//array of values that were posted
	//$posted_values = array();

  $placeHolders = array();
  $values = array();

	//for each posted value we store them into the array
	foreach($_POST as $field => $val) {
	   //array_push($posted_values, $val);
		$placeHolders[] = '?';
		if($field == 'BookCode' && $val != ''){
			$val = strtoupper($val);
			$values[] = $val;
		} else {
			$values[] = ($val == '') ? 'null' : $val;
			echo $val . "\n";
		}
	}

  $placeHolders[] = '?';
  $values[] = 'NEW';

	////this array is holding the formatted array that will
	//// append to the SQL query
	//$formatted_values = array();

	////for each of the posted values
	//for($i=0; $i<sizeof($posted_values)-1; $i++){
	//	//if the value us blank then
	//	if($posted_values[$i] == ''){
	//		//null it, since blanks will post 0 in int columns
	//		$posted_values[$i] = 'null';
	//	}
	//	else{ //otherwise we want to add ''s to the value
	//		$posted_values[$i] = "'" . $posted_values[$i] . "'";
	//	}
	//	//add the new values to the formated array
	//	array_push($formatted_values, $posted_values[$i]);
	//}
	////here we want to add the missing "STATUS" feild value, as NEW.
	//$formatted_values[] = "'NEW'";
	//
	////break them up with commas
	//$formatted_values = implode(',', $formatted_values);

	//build the SQL string
  	$placeHolders = implode(', ', $placeHolders);
	$sql = "INSERT INTO $tableName VALUES ($placeHolders);";
  	$stmt = sqlsrv_query($conn, $sql, $values, array( "Scrollable" => 'static' ));

  //if errors lets display
	if( $stmt === false) {
		$_SESSION['error'] = 'There was an error entering the row into ' . $tableName;

		header("Location:uploadDashboard.php");
	}
	else{
		$_SESSION['message'] = 'You have entered a row successfully into ' . $tableName;

    if (strtolower($tableName) === 'typecodes') {
      $typeid_path = '../PDF/' . $values[0];

      if (!is_dir($typeid_path))
        mkdir($typeid_path);
    }

		header("Location:uploadDashboard.php");
	}
?>
