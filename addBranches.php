<?php
	function addBranches($memberNum, $branches, $conn){
		//calculate the expiry date to one year from today
		$date = date('Y-m-t', strtotime("+1 year", strtotime(date('Y-m-d'))));
		$swdate = date('Y')."-12-31";
		$sql = array();
		$values = array();

		//loop through the array of branch numbers and generate an sql query for each one
		foreach ($branches as $value) {
			$branchCheck = "SELECT Expiry FROM BranchMembership WHERE BranchID = ? AND MemberID = ?";
			$stmt = sqlsrv_query($conn, $branchCheck, array($value, $memberNum), array('Scrollable' => 'static'));
			if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
			$numrows = 0;

			// fetch the row from the executed query
			while (sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) $numrows++;
			if ($numrows == 0){
				$sql[] = "INSERT INTO BranchMembership VALUES (?, ?, ?)";
				$values[] = $value;
				$values[] = $memberNum;
				$values[] = $value == 3? $swdate: $date;
			} else {
				$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC, SQLSRV_SCROLL_ABSOLUTE);
				$oldExpiry = strtotime($row['Expiry']->format('Y-m-d'));
				$newExpiry = $oldExpiry < strtotime(date('Y-m-d'))? $date: date('Y-m-t', strtotime("+1 year", $oldExpiry));

				$sql[] = "UPDATE BranchMembership SET Expiry = ? WHERE BranchID = ? AND MemberID = ?";
				$values[] = $newExpiry;
				$values[] = $value;
				$values[] = $memberNum;
			}
		}

		$sqlstr = implode(' ', $sql);	//convert the array of sql queries into a single string
		$stmt = sqlsrv_query($conn, $sqlstr, $values);	//execute the query

		// if query does not get executed print the error
		if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	}
?>