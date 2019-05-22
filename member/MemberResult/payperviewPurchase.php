<?php
    require('../../db/mgsConnection.php');
    require('../../db/memberCheck.php');
    require('../../errorReporter.php');
    require('../../retrieveColumns.php');

    //ID and table need to be in $_SESSION variables so they can be used with payperviewQuery.php
    $id = $_GET['id'];
    $_SESSION['id'] = $_GET['id'];
    $table = $_GET['table'];
    $_SESSION['table'] = $_GET['table'];
    $username = $_SESSION['uname'];
    $_SESSION['values'] = $_POST;

    if(!is_numeric($id))
    {
        $_SESSION['error'] = "There was an error. Please try again.";
        header("Location: search.php");
        exit(0);
    }

    //The username and credits are in different tables in a different database
    $membership = "Accounts.dbo.Membership";
    $members = "Accounts.dbo.Members";
    //Get the number of credits
    $memberqry = "SELECT Credit FROM $membership JOIN $members ON $membership.MemberNum = $members.MemberNum WHERE $members.Username = ?";
    $stmt = sqlsrv_query($conn, $memberqry, array($username), array("Scrollable" => "static"));
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    $credits = 0;
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))
        $credits = $row['Credit'];

    //Check that the user has enough credits to purchase the record
    if($credits < 4)
    {
        $_SESSION['error'] = "You don't have enough credits to purchase this record.";
        header("Location: /member/");
        exit(0);
    }

    //Get the record ID from the proper table
    $qry = "SELECT ID FROM $table WHERE ID = ?";

    $stmt = sqlsrv_query( $conn, 
                        $qry, 
                        array($id), 
                        array( "Scrollable" => 'static' ));
   
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    $recordID = 0;
    while($row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
        $recordID = $row['ID'];

    //Get the member id of the logged in user
    $qry = "SELECT MemberNum FROM $members WHERE Username = ?";
    $stmt = sqlsrv_query($conn, $qry, array($username), array("Scrollable" => "static"));
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    $memberID = 0;
    while($row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
        $memberID = $row['MemberNum'];

    $values = array();
    $values[] = $memberID;
    $values[] = $recordID;
    $values[] = $table;
    $values[] = $_GET['fileName'];
    if(isset($_GET['pageNum'])){
        $values[] = $_GET['pageNum'];
        $and = "AND COLUMN_NAME NOT IN('ID', 'StatusCode')";
        $columns = retrieveColumns('Purchases', $and, $conn);
        $cols = implode(", ", $columns);
        //Insert the purchase into the Purchases table
        $qry = "INSERT INTO Purchases ($cols) VALUES (?, ?, ?, ?, ?)";
        $stmt = sqlsrv_query($conn, $qry, $values, array("Scrollable" => "static"));
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    } else{
        $and = "AND COLUMN_NAME NOT IN('ID', 'PageNum', 'StatusCode')";
        $columns = retrieveColumns('Purchases', $and, $conn);
        $cols = implode(", ", $columns);
        //Insert the purchase into the Purchases table
        $qry = "INSERT INTO Purchases ($cols) VALUES (?, ?, ?, ?)";
        $stmt = sqlsrv_query($conn, $qry, $values, array("Scrollable" => "static"));
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    }

    //Update the logged in users Credits to the new value
    $qry = "UPDATE $membership SET Credit = Credit - 4 WHERE $membership.MemberNum = ?";
    $stmt = sqlsrv_query($conn, $qry, array($memberID), array("Scrollable" => "static"));
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    $_SESSION['fileName'] = $_GET['fileName'];
    $_SESSION['pageNum'] = isset($_GET['pageNum']) ? $_GET['pageNum'] : "";
?>
<script type="text/javascript">
    //Once the queries above run, send the user to payperview.php
    //Doing it this way will stop the user from being able to return to this page by hitting the back button on the browser
    location.replace("payperview.php");
</script>