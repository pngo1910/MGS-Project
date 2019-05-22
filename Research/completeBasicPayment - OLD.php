<?php
    header('X-UA-Compatible: IE=edge,chrome=1');
    require('../db/memberConnection.php');
    require('../errorReporter.php');
    require('../retrieveColumns.php');

    $parameters = "";
    require('../db/loginCheck.php');
    $parameters = "&name=login";
    // if($_POST['item_name'] === "Member Basic Research Package"){
    //     require('../db/loginCheck.php');
    //     $parameters = "&name=login";
    // } else{
    //     session_start();
    // }

    $confirm = $_GET['confirm'];
    //$completed = $_POST['payment_status'];
    $completed = $_GET['st'];
    $qry = "SELECT TransactionID FROM PayPalTransactions WHERE TransactionID = ?";
    $stmt = sqlsrv_query($userConn, $qry, array($_POST['txn_id']));
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    $result = sqlsrv_fetch_array($stmt)['TransactionID'];

    //We don't want the user to be able to go back and once again arrive on this page to get more credits so if a transaction id
    //is found in the database and it's the same as in the posted variable, then the if statement is skipped and the user is sent
    //back to the completePayment page
    if($confirm === "true" && $completed === 'Completed' && $result != $_POST['txn_id']){
    //if($confirm === "true" && $completed === 'Completed' && $result == ''){
        echo "enter the basic checkout if statement!";
        $memberNum = NULL;
        if($_POST['item_name'] === "Member Basic Research Package"){
            //Get the memberNum
            $qry = "SELECT MemberNum FROM Members WHERE Username = ?";
            $stmt = sqlsrv_query($userConn, $qry, array($_SESSION['uname']), array("Scrollable"=>"static"));
            if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
            $memberNum = sqlsrv_fetch_array($stmt)['MemberNum'];
        } else {
            //Retrieve the user's information
            $email = $_POST['payer_email'];
            $phone = empty($_POST['payer_phone']) ? NULL : $_POST['payer_phone'];
            $firstname = $_POST['first_name'];
            $lastname = $_POST['last_name'];
            $address = $_POST['address_street'];
            $city = $_POST['address_city'];
            $province = $_POST['address_state'];
            $postalCode = $_POST['address_zip'];
            $country = $_POST['address_country'];
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

        //Get the column names except ID
        $and = "AND COLUMN_NAME NOT IN('ID')";
        $cols = retrieveColumns('Transactions', $and, $userConn);
        $cols = implode(",", $cols);
        $description = $_POST['item_name'];
        $total = $_POST['mc_gross'];
        $created = date('Y-m-d', strtotime($_POST['payment_date']));

        //Insert the values into Transactions
        $qry = "INSERT INTO Transactions ($cols) VALUES(?, ?, ?, ?, ?)";
        $stmt = sqlsrv_query($userConn, $qry, array($transID, $memberNum, $description, $total, $created));
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

        //Get the column names except ID
        $cols = retrieveColumns('TransactionDetails', $and, $userConn);
        $cols = implode(",", $cols);
        //Get the id from transactions
        $qry = "SELECT ID FROM Transactions WHERE TransactionID = ?";
        $stmt = sqlsrv_query($userConn, $qry, array($transID));
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
        $id = sqlsrv_fetch_array($stmt)['ID'];

        if($_POST['item_name'] != "Member Basic Research Package"){
            //Get the column names except ID
            $cols = retrieveColumns('PayerDetails', $and, $userConn);
            $cols = implode(",", $cols);
            
            //Insert the values into PayerDetails
            $qry = "INSERT INTO PayerDetails ($cols) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = sqlsrv_query($userConn, $qry, array($id, $firstname, $lastname, $address, $city, $province, $country, $postalCode, $email, $phone));
            if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
        }

        //Get the column names except ID
        $cols = retrieveColumns('ResearchDetails', $and, $userConn);
        $cols = implode(",", $cols);
        $surname = $_SESSION['basicValues']['surname'];
        $name = $_SESSION['basicValues']['givenName'];
        $researchDescription = $_SESSION['basicValues']['description'];
        //Insert the Research details into ResearchDetails
        $qry = "INSERT INTO ResearchDetails ($cols) VALUES(?, ?, ?, ?, ?, ?)";
        $stmt = sqlsrv_query($userConn, $qry, array($id, $surname, $name, $researchDescription, NULL, 0));
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    }
    //add for sending the full details on the person placing the order as well as full details about the order to estore@mbgenealogy.com
    //all the code above these need to be redo
    if ($confirm === "true") {
        # code...
        require_once('confirmation_mail.php');
    }
    header("location: completePayment.php?confirm=$confirm$parameters");
?>