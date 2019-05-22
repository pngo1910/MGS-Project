<?php
    header('X-UA-Compatible: IE=edge,chrome=1');
    require('../db/memberConnection.php');
    require('../db/loginCheck.php');
    require('../bootstrap.php');
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
    
    if($confirm === "true"  && $result == ""){
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

        //Get the memberNum
        $memberNum = $_SESSION['memberNum'];
        //Get the associates (if any)
        $associates = isset($_SESSION['associates']) ? $_SESSION['associates'] : "";
        //Get the branches (if any)
        $branches = isset($_SESSION['branches']) ? $_SESSION['branches'] : "";
        //Get the branchesForAssociate (if any)
        $branchesForAssociate = isset($_SESSION['branchesForAssociate']) ? $_SESSION['branchesForAssociate'] : "";
        //Get the payment info as an array
        $info = $payment->toArray();

        $descriptions = array();
        $names = array();
        $prices = array();
        //Retrieve the descriptions, names and prices
        for($i = 0; $i < count($info['transactions'][0]['item_list']['items']); $i++){
            $descriptions[] = $info['transactions'][0]['item_list']['items'][$i]['description'];
            $names[] = $info['transactions'][0]['item_list']['items'][$i]['name'];
            $prices[] = $info['transactions'][0]['item_list']['items'][$i]['price'];
        }
        
        //Index for associates
        $i = 0;
        //Index for branches
        $j = 0;
        //Index for branchesForAssociate
        $k = 0;

        foreach($descriptions as $key => $value){
            if($value === "Individual Renewal" || $value === "Associate Renewal"){
                //Get the expiry date
                $qry = "SELECT Expiry FROM Membership WHERE MemberNum = ?";
                //If it's an individual renewal use the memberNum, else use the associate id
                $stmt = $value === "Individual Renewal" ? sqlsrv_query($userConn, $qry, array($memberNum)) : sqlsrv_query($userConn, $qry, array($associates[$i]));

                if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
                
                

                //$date = sqlsrv_fetch_array($stmt)['Expiry']->format("Y-m-d");
                $expiry = strtotime(sqlsrv_fetch_array($stmt)['Expiry']->format("Y-m-d"));
                //$date = new DateTime();
                $date = time();
                $date = $expiry < $date ? $date :  $expiry;
                //Set the new date a year from the day of the expiry date
                $newDate = date('Y-m-d', strtotime("+1 year", $date));

                $sql = "UPDATE members SET verified = 1 WHERE membernum = ?";
                $stmt = sqlsrv_query($userConn, $sql, array($memberNum));

               

                //Update the expiry date to the new one
                $qry = "UPDATE Membership SET Expiry = ? WHERE MemberNum = ?";
                //If it's an individual renewal use the memberNum, else use the associate id
                $stmt = $value === "Individual Renewal" ? sqlsrv_query($userConn, $qry, array($newDate, $memberNum)) : sqlsrv_query($userConn, $qry, array($newDate, $associates[$i]));

                if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

                

                //Increment the index only if the value is Associate Renewal
                if($value === "Associate Renewal") $i++;
            } elseif($value === "Branch Purchase/Renewal"){
                //Get the expiry date using the memberNum and branch id
                $qry = "SELECT Expiry FROM BranchMembership WHERE MemberID = ? AND BranchID = ?";
                $stmt = sqlsrv_query($userConn, $qry, array($memberNum, $branches[$j]));
                if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
                
                $expiry = sqlsrv_fetch_array($stmt)['Expiry'];
                if($expiry != ""){
                    //error here
                    //$date = $expiry < date("Y-m-d") ? date("Y-m-d") : $expiry;
                    $date = new DateTime();
                    $date = $expiry < $date ? $date : $expiry;
                    $date = $date->format("Y-m-t");
                    //Set the new date a year from the day of the expiry date
                    $newDate = date('Y-m-t', strtotime("+1 year", $date));




                    //Update the expiry date to the new one
                    $qry = "UPDATE BranchMembership SET Expiry = ? WHERE MemberID = ? AND BranchID = ?";
                    $stmt = sqlsrv_query($userConn, $qry, array($newDate, $memberNum, $branches[$j]));

                    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
                } else {
                    $qry = "INSERT INTO BranchMembership VALUES(?, ?, ?)";
                    $newDate = $branches[$j] === 3? date('Y')."-12-31":
                        date('Y-m-t', strtotime("+1 year", strtotime(date('Y-m-t'))));

                    $stmt = sqlsrv_query($userConn, $qry, array($branches[$j], $memberNum, $newDate));
                    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
                }
                //Increment the index
                $j++;
            }
            elseif($value === "Associate Branch Purchase/Renewal"){

                $associateMemberNum = explode(" ", $branchesForAssociate[$k])[0];
                $branchesIdForAssociate = explode(" ", $branchesForAssociate[$k])[1];
                //Get the expiry date using the memberNum and branch id
                $qry = "SELECT Expiry FROM BranchMembership WHERE MemberID = ? AND BranchID = ?";
                
                $stmt = sqlsrv_query($userConn, $qry, array($associateMemberNum, $branchesIdForAssociate));
                if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
                
                $expiry = sqlsrv_fetch_array($stmt)['Expiry'];
                if($expiry != ""){
                    //error here
                    $date = new DateTime();
                    $date = $expiry < $date ? $date : $expiry;
                    $date = $date->format("Y-m-t");
                    //Set the new date a year from the day of the expiry date
                    $newDate = date('Y-m-t', strtotime("+1 year", strtotime($date)));

                    //Update the expiry date to the new one
                    $qry = "UPDATE BranchMembership SET Expiry = ? WHERE MemberID = ? AND BranchID = ?";
                    $stmt = sqlsrv_query($userConn, $qry, array($newDate, $associateMemberNum, $branchesIdForAssociate));

                    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
                } else {
                    $qry = "INSERT INTO BranchMembership VALUES(?, ?, ?)";
                    $newDate = $branchesIdForAssociate === 3? date('Y')."-12-31":
                        date('Y-m-t', strtotime("+1 year", strtotime(date('Y-m-t'))));

                    $stmt = sqlsrv_query($userConn, $qry, array($branchesIdForAssociate, $associateMemberNum, $newDate));
                    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
                }
                //Increment the index
                $k++;
            }
        }


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
    header("location: completeRenewal.php?confirm=$confirm");
?>