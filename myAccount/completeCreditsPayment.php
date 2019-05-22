<?php
    header('X-UA-Compatible: IE=edge,chrome=1');
    require('../db/loginCheck.php');
    require('../db/memberConnection.php');
    require('../errorReporter.php');
    require('../retrieveColumns.php');

    $confirm = $_GET['confirm'];
    $completed = $_POST['payment_status'];
    $qry = "SELECT TransactionID FROM PayPalTransactions WHERE TransactionID = ?";
    $stmt = sqlsrv_query($userConn, $qry, array($_POST['txn_id']));
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    $result = sqlsrv_fetch_array($stmt)['TransactionID'];

    //We don't want the user to be able to go back and once again arrive on this page to get more credits so if a transaction id
    //is found in the database and it's the same as in the posted variable, then the if statement is skipped and the user is sent
    //back to the completePayment page
    if($confirm === "true" && $completed === 'Completed' && $result != $_POST['txn_id']){
        
        $credit = $_POST['option_selection1'];
        //Explode the credit into an array
        $credits = explode(" ", $credit);
        //The first option of the array is the number of credits, convert it to an int
        $credit = (int)$credits[0];
        //Keep track of the credits for the completePayment page
        $_SESSION['numCredits'] = $credit;
        //Get the memberNum
        $qry = "SELECT MemberNum FROM Members WHERE Username = ?";
        $stmt = sqlsrv_query($userConn, $qry, array($_SESSION['uname']), array("Scrollable"=>"static"));
        if($stmt === false){
            $_SESSION['error'] = "There was an error adding the credits. Please contact MGS to receive your credits.";
            header("location: ../index.php");
        }
        $memberNum = sqlsrv_fetch_array($stmt)['MemberNum'];

        //Update the users credits
        $qry = "UPDATE Membership SET Credit = Credit + $credit WHERE MemberNum = ?";
        $stmt = sqlsrv_query($userConn, $qry, array($memberNum));
        if($stmt === false){
            $_SESSION['error'] = "There was an error adding the credit. Please contact MGS to receive your credits.";
            header("location: ../index.php");
        }

        //Get the payer id
        $payerId = $_POST['payer_id'];
        $transactionID = $_POST['txn_id'];
        //Insert the values into PayPalTransactions table
        $qry = "INSERT INTO PayPalTransactions (PayerID, PaymentID, TransactionID, Hash, Complete) VALUES(?, ?, ?, ?, ?)";
        $params = array($payerId, NULL, $transactionID, NULL, 1);
        $stmt = sqlsrv_query($userConn, $qry, $params);
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

        //Get the id of the PayPal transaction
        $qry = "SELECT ID FROM PayPalTransactions WHERE TransactionID = ?";
        $stmt = sqlsrv_query($userConn, $qry, array($transactionID));
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
        $transID = sqlsrv_fetch_array($stmt)['ID'];

        if($memberNum === -1){
            //Get the column names except ID
            $and = "AND COLUMN_NAME NOT IN('MemberNum')";
            $cols = retrieveColumns('UnloggedInUserInfo', $and, $userConn);

            //Insert the values into UnloggedInUserInfo
            $qry = "INSERT INTO UnloggedInUserInfo (" . implode(",", $cols) . ") VALUES(?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = sqlsrv_query($userConn, $qry, $_SESSION['UnloggedInUserInfo'], array("Scrollable"=>"static"));

            if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

            //Get the memberNum
            $qry = "SELECT TOP 1 * FROM Accounts.dbo.UnloggedInUserInfo ORDER BY MemberNum DESC";
            $stmt = sqlsrv_query($userConn, $qry, array(), array( "Scrollable" => 'static' ));
            if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
            $memberNum = sqlsrv_fetch_array($stmt)['MemberNum'];
            $memberTable = 'UnloggedInUserInfo';

        } else{
            $memberTable = 'MemberInfo';
        }


        //Get the column names except ID
        $and = "AND COLUMN_NAME NOT IN('ID')";
        $cols = retrieveColumns('Transactions', $and, $userConn);
        $cols = implode(",", $cols);
        $description = $_POST['item_name'];
        $total = $_POST['mc_gross'];
        $created = date('Y-m-d', strtotime($_POST['payment_date']));

        //Insert the values into Transactions
        $qry = "INSERT INTO Transactions ($cols) VALUES(?, ?, ?, ?, ?, ?)";
        $stmt = sqlsrv_query($userConn, $qry, array($transID, $memberNum, $description, $total, $created, $memberTable));
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

        //Get the column names except ID
        $cols = retrieveColumns('TransactionDetails', $and, $userConn);
        $cols = implode(",", $cols);
        //Get the id from transactions
        $qry = "SELECT ID FROM Transactions WHERE TransactionID = ?";
        $stmt = sqlsrv_query($userConn, $qry, array($transID));
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
        $id = sqlsrv_fetch_array($stmt)['ID'];

        //Insert the Transaction deatils into TransactionDetails
        $qry = "INSERT INTO TransactionDetails ($cols) VALUES(?, ?, ?)";
        $stmt = sqlsrv_query($userConn, $qry, array($id, $description, $total));
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    }
    header("location: completePayment.php?confirm=$confirm");
?>