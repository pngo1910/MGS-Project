<?php
    header('X-UA-Compatible: IE=edge,chrome=1');
    session_name('register');
    session_start();

    require('../db/memberConnection.php');
    require('../bootstrap.php');
    require('../addBranches.php');
    require('../errorReporter.php');
    require('../retrieveColumns.php');
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
        //Get the returned payment id
        $paymentId = $_GET['paymentId'];
        //Get the created payment
        $payment = Payment::get($paymentId, $apiContext);

        //Get the returned payer id
        $payerId = $_GET['PayerID'];
        //Create a new payment execution
        $paymentExecution = new PaymentExecution();
        //Set the execution's payer id
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

        //Convert the payment object to an array
        $info = $payment->toArray();
        
        $names = array();
        $prices = array();
        //Get the names and prices
        for($i = 0; $i < count($info['transactions'][0]['item_list']['items']); $i++){
            $names[] = $info['transactions'][0]['item_list']['items'][$i]['name'];
            $prices[] = $info['transactions'][0]['item_list']['items'][$i]['price'];
        }

        //Retrieve the values stored in session
        $associatedWith = $_SESSION['associatedWith'];
        $memberNum = $_SESSION['memberNum'];
        $generations = $_SESSION['generations'];
        $type = $_SESSION['type'];
        $joinDate = $_SESSION['joinDate'];
        $expiry = $_SESSION['expiry'];
        $username = $_SESSION['username'];
        $password = $_SESSION['encryptpw'];

        $fname = $info['payer']['payer_info']['first_name'];
        $lname = $info['payer']['payer_info']['last_name'];
        $address = $info['payer']['payer_info']['shipping_address']['line1'];
        $city = $info['payer']['payer_info']['shipping_address']['city'];
        $province = $info['payer']['payer_info']['shipping_address']['state'];
        $postalCode = $info['payer']['payer_info']['shipping_address']['postal_code'];
        $phone = isset($info['payer']['payer_info']['phone']) ? $info['payer']['payer_info']['phone'] : NULL;
        $email = $info['payer']['payer_info']['email'];

        // insert all the field values in database table
        $sql = "INSERT INTO Membership (MemberNum, Generations, TypeOfMember, YearJoined, Expiry, Credit)
                VALUES (?, ?, ?, ?, ?, ?)
                INSERT INTO memberInfo (MemberNum, FirstName, LastName, Address, City, Province, CountryCode, Phone, Email)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                INSERT INTO Members (MemberNum, Username, Password, AccessLevel, Verified)
                VALUES (?, ?, ?, ?, ?);";

        $values = array($memberNum, $generations, $type, $joinDate, $expiry, 0,
                        $memberNum, $fname, $lname, $address, $city, $province, $postalCode, $phone, $email,
                        $memberNum, $username, $password, 1, 1);
        
        // execute the query
        $stmt = sqlsrv_query($userConn, $sql, $values);

        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__); 
        
        //If an associate then enter the ids in to AssociateMembers
        if(isset($associatedWith) && !empty($associatedWith) && $associatedWith != ""){
          $sql = "INSERT INTO AssociateMembers (AssociateMemberID, IndividualMemberID) VALUES (?, ?)";
          $stmt = sqlsrv_query($userConn, $sql, array($memberNum, $associatedWith));
          if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
        }

        //Be sure there is something in the branches session before continuing
        if(isset($_SESSION['branches']) && !empty($_SESSION['branches']) && $_SESSION['branches'] != ""){
            $branches = $_SESSION['branches'];
            //add the branches to the database
            addBranches($memberNum, $branches, $userConn);
        }

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
        $description = $info['transactions'][0]['description'];
        $total = $info['transactions'][0]['amount']['total'];
        $created = date('Y-m-d', strtotime($info['create_time']));

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
        for($i = 0; $i < count($names); $i++){
            $qry = "INSERT INTO TransactionDetails ($cols) VALUES(?, ?, ?)";
            $stmt = sqlsrv_query($userConn, $qry, array($id, $names[$i], $prices[$i]));
            if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
        }
    }
    header("location: completePayment.php?confirm=$confirm");
?>