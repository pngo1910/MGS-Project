<?php
	function exportcsv($table, $statusCode = "", $selected = "", $connection = "", $research = ""){
		require_once('../../db/adminConnection.php');
		require_once('../../db/memberConnection.php');
		require_once('../../db/storeConnection.php');
		$filename = $table .'-Export.csv';

		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=' . $filename);
		if ($statusCode != "") {
			$sql = "SELECT * FROM $table WHERE StatusCode = '$statusCode'";
		}
		else if ($research ==="YES") {
			$sql = "SELECT * FROM $table WHERE Description LIKE '%RESEARCH%'";
		}
		else
		{
			$sql = "SELECT * FROM $table";
		}
		
		if ($selected != "") {
			$uniqueIDS = "(";
			foreach($selected as $uniqueID)
			{
				$uniqueIDS .= $uniqueID . ", ";
			}
			$uniqueIDS = substr($uniqueIDS, 0, -2);

			$uniqueIDS .= ")";
			
			if (strpos($sql, 'WHERE') !== false) {
			    $sql .= " AND ";
			}
			else 
			{
				$sql .= " WHERE ";
			}

			if(strtolower($table) === 'cemeterytranscriptions'){
				$sql .= "UniqueID IN " . $uniqueIDS;				
			}
			else if(strtolower($table) === 'articles')
			{
				$sql .= "ArticleID IN " . $uniqueIDS;
			}
			else if(strtolower($table) === 'booklets')
			{
				$sql .= "BookletID IN " . $uniqueIDS;
			}
			else if(strtolower($table) === 'deathrecords')
			{
				$sql .= "DeathRecordID IN " . $uniqueIDS;
			}
			else if(strtolower($table) === 'parts')
			{
				$sql .= "PartID IN " . $uniqueIDS;
			}
			else if(strtolower($table) === 'provinces')
			{
				$sql .= "ProvID IN " . $uniqueIDS;
			}
			else if(strtolower($table) === 'rows')
			{
				$sql .= "RowID IN " . $uniqueIDS;
			}
			else if(strtolower($table) === 'sections')
			{
				$sql .= "SectionID IN " . $uniqueIDS;
			}
			else if(strtolower($table) === 'transcribers')
			{
				$sql .= "TranscriberID IN " . $uniqueIDS;
			}
			else if(strtolower($table) === 'typecodes')
			{
				$sql .= "TypeID IN " . $uniqueIDS;
			}
			else
			{
				$sql .= "ID IN " . $uniqueIDS;
			}
		}
		
		if ($connection == "") {
			$rows = sqlsrv_query($conn, $sql);
		}
		else if ($connection == "storeConnection")
		{
			$rows = sqlsrv_query($storeConn, $sql);
		}
		else if ($connection == "memberConnection")
		{
			$rows = sqlsrv_query($userConn, $sql);
		}

		//print_r($sql);
		while ($row = sqlsrv_fetch_array($rows,SQLSRV_FETCH_ASSOC))
		{
			if ($table === "Transactions") {
				$row['Created'] = $row['Created']->format('Y-m-d');
			}
		 	$line = getcsvline( $row, ",", "\"", "\r\n" );
			echo "$line";
		}
	}

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
?>