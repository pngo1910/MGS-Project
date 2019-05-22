<?php
	// function exportcsv($table, $statusCode, $selected = "" ){
	// 	require_once('../../db/mgsConnection.php');
	// 	$filename = $table .'-Export.csv';

	// 	header('Content-Type: text/csv; charset=utf-8');
	// 	header('Content-Disposition: attachment; filename=' . $filename);
	// 	$sql = "SELECT * FROM $table WHERE StatusCode = '$statusCode'";

	// 	if ($selected != "") {
	// 		$uniqueIDS = "(";
	// 		foreach($selected as $uniqueID)
	// 		{
	// 			$uniqueIDS .= $uniqueID . ", ";
	// 		}
	// 		$uniqueIDS = substr($uniqueIDS, 0, -2);

	// 		$uniqueIDS .= ")";
	// 		$sql .= " AND UniqueID IN " . $uniqueIDS;
	// 	}
	// 	$rows = sqlsrv_query($conn,$sql);
		
	// 	while ($row = sqlsrv_fetch_array($rows,SQLSRV_FETCH_ASSOC))
	// 	{
	// 	 	$line = getcsvline( $row, ",", "\"", "\r\n" );
	// 		echo "$line";
	// 	}
	// }
	
	function getcsvline($list,  $seperator, $enclosure, $newline = "" ){
		$fp = fopen('php://temp', 'r+'); 

		fputcsv($fp, $list, $seperator, $enclosure );
		rewind($fp);

		$line = fgets($fp);
		if( $newline and $newline != "\n" ) {
			if( $line[strlen($line)-2] != "\r" and $line[strlen($line)-1] == "\n") {
			  $line = substr_replace($line,"",-1) . $newline;
			} else {
			  // return the line as is (literal string)
			  //die( 'original csv line is already \r\n style' );
			}
		}

		return $line;
	}
		function exportAll($table, $selected = "" ){
		require('../db/mgsConnection.php');
		$filename = $table .'-Export.csv';

		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=' . $filename);
		$sql = "SELECT * FROM $table";

		if ($selected != "") {
			$uniqueIDS = "(";
			foreach($selected as $uniqueID)
			{
				$uniqueIDS .= $uniqueID . ", ";
			}
			$uniqueIDS = substr($uniqueIDS, 0, -2);

			$uniqueIDS .= ")";
			
			$tableName = $_POST['tableName'];
			if(strtolower($table) === 'cemeterytranscriptions'){
				$sql .= " WHERE UniqueID IN " . $uniqueIDS;				
			}
			else if(strtolower($table) === 'articles')
			{
				$sql .= " WHERE ArticleID IN " . $uniqueIDS;
			}
			else if(strtolower($table) === 'booklets')
			{
				$sql .= " WHERE BookletID IN " . $uniqueIDS;
			}
			else if(strtolower($table) === 'deathrecords')
			{
				$sql .= " WHERE DeathRecordID IN " . $uniqueIDS;
			}
			else if(strtolower($table) === 'parts')
			{
				$sql .= " WHERE PartID IN " . $uniqueIDS;
			}
			else if(strtolower($table) === 'provinces')
			{
				$sql .= " WHERE ProvID IN " . $uniqueIDS;
			}
			else if(strtolower($table) === 'rows')
			{
				$sql .= " WHERE RowID IN " . $uniqueIDS;
			}
			else if(strtolower($table) === 'sections')
			{
				$sql .= " WHERE SectionID IN " . $uniqueIDS;
			}
			else if(strtolower($table) === 'transcribers')
			{
				$sql .= " WHERE TranscriberID IN " . $uniqueIDS;
			}
			else if(strtolower($table) === 'typecodes')
			{
				$sql .= " WHERE TypeID IN " . $uniqueIDS;
			}
			else
			{
				$sql .= " WHERE ID IN " . $uniqueIDS;
			}
		}
		$rows = sqlsrv_query($conn,$sql);
		
		while ($row = sqlsrv_fetch_array($rows,SQLSRV_FETCH_ASSOC))
		{
		 	$line = getcsvline( $row, ",", "\"", "\r\n" );
			echo "$line";
		}
	}
?>