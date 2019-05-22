<?php
	require('../../db/adminCheck.php');
  	require('../../errorReporter.php');
	require('../../db/adminConnection.php');

	if(!isset($_GET['tableName']))
		header("Location: /admin/store/");
	else
		$tableName = $_GET['tableName'];

	//	check for file erros in the file uploaded
	if ($_FILES["file"]["error"] > 0){
		echo "Error: " . $_FILES["file"]["error"] . "<br>";
	//	else get all other attributes of the file
	} else{
		echo "Upload: " . $_FILES["file"]["name"] . "<br>";
		echo "Type: " . $_FILES["file"]["type"] . "<br>";
		echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
		echo "Stored in: " . $_FILES["file"]["tmp_name"];
	}

	//	link to return to the specific table
	echo "<br /><a href='index.php'>Return</a><br />";

	//	get path of file
	$filePath = $_FILES["file"]["tmp_name"];

	//	match the regular expression for file name
	if (preg_match('/\.csv$/i', $_FILES["file"]["name"]))
	{
		//	insert into table
		$sql = "BULK INSERT $tableName
		FROM '".$filePath."'
		WITH(
				FIELDTERMINATOR = ',',
				ROWTERMINATOR = '\r\n',
				MAXERRORS = 500
			)";

		//	debug the sql statement
		echo $sql;
		echo "<br />";

		//	execute the statement
		$stmt = sqlsrv_query( $conn, $sql);
		//	if execute could not be executed give errors and kill the script
		if( $stmt === false)
		{
			$_SESSION['error'] = 'There was an error uploading bulk into ' . $tableName . '. Please check to see if any of the rows were uploaded before you try again because the file could have been partially uploaded.';
			header("Location:/admin/store/");
		}
		else{
			$_SESSION['message'] = 'You have uploaded bulk successfully into ' . $tableName;
			header("Location:/admin/store/");
		}
	}

	//	if file name does not match the regex, show this message
	else
	{
		$_SESSION['error'] = 'The file must be .csv for bulk upload into ' . $tableName;
		header("Location:/admin/store/");
	}
?>
