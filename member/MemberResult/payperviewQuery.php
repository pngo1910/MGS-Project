<?php
    require('../../retrieveColumns.php');
    $id = $_SESSION['id'];
    $table = $_SESSION['table'];
    $username = $_SESSION['uname'];

    if(!is_numeric($id))
    {
    	$_SESSION['error'] = "There was an error. Please try again.";
    	header("Location: /member/");
    	exit(0);
    }

    //Select the info from the table
    $qry = "SELECT ID, LastName, FirstName FROM $table WHERE ID = ?";

    $stmt = sqlsrv_query( $conn, 
                        $qry, 
                        array($id), 
                        array( "Scrollable" => 'static' ));
   
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    $values = array();

    //Loop through the returned results and add them and the column name to the array
    while($row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
    {
    	$values[] = 'ID';
    	$values[] = $row['ID'];
    	$values[] = 'LastName';
    	$values[] = $row['LastName'];
    	$values[] = 'FirstName';
    	$values[] = $row['FirstName'];
    }

    //The username and credits are in different tables in a different database
    $membership = "Accounts.dbo.Membership";
    $members = "Accounts.dbo.Members";
    //Get the user's credits
    $memberqry = "SELECT Credit FROM $membership JOIN $members ON $membership.MemberNum = $members.MemberNum WHERE $members.Username = ?";
    $stmt = sqlsrv_query($conn, $memberqry, array($username), array("Scrollable" => "static"));
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    $credits = 0;
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))
        $credits = $row['Credit'];

?>