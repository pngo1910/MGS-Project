<?php
    header('X-UA-Compatible: IE=edge,chrome=1');
    require('../db/memberConnection.php');
    require('../bootstrap.php');
    require('../errorReporter.php');
    require('../retrieveColumns.php');

    $parameters = "";
    if(isset($_GET['name']) && $_GET['name'] === "login"){
        $parameters = "&name=login";
        require('../db/loginCheck.php');
    } else{
        session_start();
    }
    use PayPal\Api\Payment;
    use PayPal\Api\ExecutePayment;
    use PayPal\Api\PaymentExecution;

    $qry = "SELECT TransactionID FROM PayPalTransactions WHERE Hash = ?";
    $stmt = sqlsrv_query($userConn, $qry, array($_SESSION['Paypal_hash']));
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    $result = sqlsrv_fetch_array($stmt)['TransactionID'];
    
    $confirm = $_GET['confirm'];
    
    if($confirm === "true" && $result == ""){
        //Create a new payment
        $payment = new Payment();
        //Get the payment id
        $paymentId = $_GET['paymentId'];
        //Get the payment
        $payment = Payment::get($paymentId, $apiContext);

        //Get the payer id
        $payerId = $_GET['PayerID'];
        //Create a new payment execution
        $paymentExecution = new PaymentExecution();
        //Set the payer id
        $paymentExecution->setPayerId($payerId);

        try{
            //Execute the payment
            $payment->execute($paymentExecution, $apiContext);
        } catch(Exception $ex){
            //Print the exception
            print_r($ex);
            //Kill the script
            exit(0);
        }

        //Get the payment info as an array
        $info = $payment->toArray();

        //For ResearchDetails
        $researchDescription = $info['transactions'][0]['item_list']['items'][0]['description'];
        $names = explode(" ", $info['transactions'][0]['item_list']['items'][0]['name']);
        $surname = $names[0];
        $givenname = $names[1];
        //For TransactionDetails
        $itemNames = array();
        $prices = array();
        //Retrieve the descriptions, names and prices
        for($i = 1; $i < count($info['transactions'][0]['item_list']['items']); $i++){
            $itemNames[] = $info['transactions'][0]['item_list']['items'][$i]['name'];
            $prices[] = $info['transactions'][0]['item_list']['items'][$i]['price'];
        }
        $searchLocations = implode(", ", $itemNames);

        $params = "";
        $memberNum = NULL;
        if(isset($_GET['name']) && $_GET['name'] === "login"){
            $params = "&name=login";
            //Get the memberNum
            $qry = "SELECT MemberNum FROM Members WHERE Username = ?";
            $stmt = sqlsrv_query($userConn, $qry, array($_SESSION['uname']), array("Scrollable"=>"static"));
            if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
            $memberNum = sqlsrv_fetch_array($stmt)['MemberNum'];
        } else{
            //Retrieve the user's information
            $email = $info['payer']['payer_info']['email'];
            $phone = empty($info['payer']['payer_info']['phone']) ? NULL : $info['payer']['payer_info']['phone'];
            $firstname = $info['payer']['payer_info']['first_name'];
            $lastname = $info['payer']['payer_info']['last_name'];
            $address = $info['payer']['payer_info']['shipping_address']['line1'];
            $city = $info['payer']['payer_info']['shipping_address']['city'];
            $province = $info['payer']['payer_info']['shipping_address']['state'];
            $postalCode = $info['payer']['payer_info']['shipping_address']['postal_code'];
            $country = $info['payer']['payer_info']['shipping_address']['country_code'];
        }

        //Get the transaction id
        $transactionID = $info['transactions'][0]['related_resources'][0]['sale']['id'];
        $hash = $_SESSION['Paypal_hash'];

        //Update the PayPalTransactions table, adding the payerID and setting complete to 1
        $qry = "UPDATE PayPalTransactions SET PayerID = ?, TransactionID = ?, Complete = ? WHERE Hash = ?";
        $params = array($payerId, $transactionID, 1, $hash);
        $stmt = sqlsrv_query($userConn, $qry, $params);
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

        //Get the id of the PayPal transaction
        $qry = "SELECT ID FROM PayPalTransactions WHERE Hash = ?";
        $stmt = sqlsrv_query($userConn, $qry, array($hash));
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
        $transID = sqlsrv_fetch_array($stmt)['ID'];

        //Get the column names except ID
        $and = "AND COLUMN_NAME NOT IN('ID')";
        $cols = retrieveColumns('Transactions', $and, $userConn);
        $cols = implode(",", $cols);

        //Get the details of the transaction
        $shipping = $info['transactions'][0]['amount']['details']['shipping'];
        $description = $info['transactions'][0]['description'];
        $total = $info['transactions'][0]['amount']['total'];
        $created = date('Y-m-d', strtotime($info['create_time']));
        
        //Insert the values into Transactions
        $qry = "INSERT INTO Transactions ($cols) VALUES(?, ?, ?, ?, ?)";
        $stmt = sqlsrv_query($userConn, $qry, array($transID, $memberNum, $description, $total, $created));
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

        //Get the id from transactions
        $qry = "SELECT ID FROM Transactions WHERE TransactionID = ?";
        $stmt = sqlsrv_query($userConn, $qry, array($transID));
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
        $id = sqlsrv_fetch_array($stmt)['ID'];

        if(!isset($_GET['name']) || isset($_GET['name']) != "login"){
            //Get the column names except ID
            $cols = retrieveColumns('PayerDetails', $and, $userConn);
            $cols = implode(",", $cols);

            //Insert the values into PayerDetails
            $qry = "INSERT INTO PayerDetails ($cols) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = sqlsrv_query($userConn, $qry, array($id, $firstname, $lastname, $address, $city, $province, $country, $postalCode, $email, $phone));
            if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
        }

        //Get the column names except ID
        $cols = retrieveColumns('TransactionDetails', $and, $userConn);
        $cols = implode(",", $cols);
        //Insert the Transaction details into TransactionDetails
        for($i = 0; $i < count($itemNames); $i++){
            $qry = "INSERT INTO TransactionDetails ($cols) VALUES(?, ?, ?)";
            $stmt = sqlsrv_query($userConn, $qry, array($id, $itemNames[$i], $prices[$i]));
            if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
        }
        
        //Get the column names except ID
        $cols = retrieveColumns('ResearchDetails', $and, $userConn);
        $cols = implode(",", $cols);

        //Insert the Research details into ResearchDetails
        $qry = "INSERT INTO ResearchDetails ($cols) VALUES(?, ?, ?, ?, ?, ?)";
        $stmt = sqlsrv_query($userConn, $qry, array($id, $surname, $givenname, $researchDescription, $searchLocations, 0));
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