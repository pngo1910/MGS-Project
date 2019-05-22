<?php
	require_once('../db/storeConnection.php');
	require_once('../retrieveColumns.php');
	require_once '../bootstrap.php';
	require_once('../errorReporter.php');
	//add for sending the full details on the person placing the order as well as full details about the order to estore@mbgenealogy.com
	require_once('../config_mail.php');
	require_once('../db/memberConnection.php');

	$name = isset($_GET['name'])? $_GET['name']: 'store';
    switch($name){
    	case 'login':
            require_once('../db/loginCheck.php');
            break;
        default:
            session_name($name);
            session_start();
    }

	use PayPal\Api\ExecutePayment;
	use PayPal\Api\Payment;
	use PayPal\Api\PaymentExecution;

	$qry = "SELECT TransactionID FROM PayPalTransactions WHERE Hash = ?";
    $stmt = sqlsrv_query($userConn, $qry, array($_SESSION['Paypal_hash']));
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    $result = sqlsrv_fetch_array($stmt)['TransactionID'];

    if (!isset($_SESSION['error'])) $_SESSION['error'] = '';
    if(!isset($_SESSION['message']))  $_SESSION['message'] = "";
    
    $confirm = $_GET['confirm'];
    
    if($confirm == "true" && $result == ""){
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
	        $_SESSION['message'] = "There was a problem executing the payment. Please try again.";
	        header("location: /Store/store.php?name=$name");
	        exit(0);
	    }

	    //Get the payment as an array
		$info = $payment->toArray();

		//Retrieve the user's information
		$email = $info['payer']['payer_info']['email'];
		$phone = !isset($info['payer']['payer_info']['phone']) ? NULL : $info['payer']['payer_info']['phone'];
		$firstname = $info['payer']['payer_info']['first_name'];
		$lastname = $info['payer']['payer_info']['last_name'];
		$address = $info['payer']['payer_info']['shipping_address']['line1'];
		$city = $info['payer']['payer_info']['shipping_address']['city'];
		$province = $info['payer']['payer_info']['shipping_address']['state'];
		$postalCode = $info['payer']['payer_info']['shipping_address']['postal_code'];
		$country = $info['payer']['payer_info']['shipping_address']['country_code'];
		
		//Get the details of the transaction
		$shipping = $info['transactions'][0]['amount']['details']['shipping'];
		// $subTotal = $info['transactions'][0]['amount']['details']['subtotal'];
		$total = $info['transactions'][0]['amount']['total'];
		$created = date('Y-m-d', strtotime($info['create_time']));

		$totalItems = 0;
		$ids = array();
		$names = array();
		//add for email detail
		$descriptions = array();
		$categories = array();

		$prices = array();
		$quantities = array();
		foreach($info['transactions'][0]['item_list']['items'] as $key => $value){
		    $totalItems += $value['quantity'];
		    $ids[] = $value['sku'];
		    $names[] = $value['name'];
		    //
		    $descriptions[] = (explode(" - ",$value['description']))[1];
		    $categories[] = (explode(" - ",$value['description']))[0];

		    $prices[] = $value['price'];
		    $quantities[] = $value['quantity'];
		}

		$memberNum = NULL;
		$memberTable = "";
		$customerFirstName = $customerLastName = $customerAddress = $customerCity = $customerProvince = $customerCountryCode = $customerPhone = $customerEmail = "";
		if(isset($_GET['name']) && $_GET['name'] == "login"){
			$qry = "SELECT MemberNum FROM Members WHERE Username = ?";
			$stmt = sqlsrv_query($userConn, $qry, array($_SESSION['uname']));
			if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
			$memberNum = sqlsrv_fetch_array($stmt)['MemberNum'];
			$memberTable = "MemberInfo";
			$sql = "SELECT * FROM MemberInfo WHERE MemberNum = ?";
	        $stmt = sqlsrv_query($userConn, $sql, array($memberNum));
	        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	        $row = sqlsrv_fetch_array($stmt);

	        $customerFirstName = $row['FirstName'];
	        $customerLastName = $row['LastName'];
	        $customerAddress = $row['Address'];
	        $customerCity = $row['City'];
	        $customerProvince = $row['Province'];
	        $customerCountryCode = $row['CountryCode'];
	        $customerPhone = $row['Phone'];
	        $customerEmail = $row['Email'];
		}
		elseif (isset($_GET['name']) && $_GET['name'] == "store") {
			//get the highest member number
			$sqlMax = "SELECT MAX(MemberNum) FROM UnloggedInUserInfo";
			$stmtMax = sqlsrv_query($userConn, $sqlMax, array());

			// fetch the row from the executed query
			if (sqlsrv_fetch($stmtMax) === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

			//incremet the highest member number by 1 to get a new member number
			$memberNum = (int)sqlsrv_get_field($stmtMax, 0);
			$memberNum ++;
			$memberTable = "UnloggedInUserInfo";
			$UnloginUser = explode("<>", $_SESSION['UnloggedInUserInfo']);

			$customerFirstName = $UnloginUser[0];
	        $customerLastName = $UnloginUser[1];
	        $customerAddress = $UnloginUser[2];
	        $customerCity = $UnloginUser[3];
	        $customerProvince = $UnloginUser[4];
	        $customerCountryCode = $UnloginUser[5];
	        $customerPhone = $UnloginUser[6];
	        $customerEmail = $UnloginUser[7];
	        
	        $and = "AND COLUMN_NAME NOT IN('MemberNum')";
			$cols = retrieveColumns('UnloggedInUserInfo', $and, $userConn);
	        $cols = implode(",", $cols);

			$sql = "INSERT INTO UnloggedInUserInfo ($cols) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
	        $stmt = sqlsrv_query($userConn, $sql, $UnloginUser);
	        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
		}
		//Get the transaction id
		$transactionID = $info['transactions'][0]['related_resources'][0]['sale']['id'];
        $hash = $_SESSION['Paypal_hash'];
        //Update the PayPalTransactions table, adding the payerID and setting complete to 1
        $qry = "UPDATE PayPalTransactions SET PayerID = ?, TransactionID = ?, Complete = ? WHERE Hash = ?";
        $params = array($payerId, $transactionID, 1, $hash);
        $stmt = sqlsrv_query($storeConn, $qry, $params);
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

        //Get the id of the PayPal transaction
        $qry = "SELECT ID FROM PayPalTransactions WHERE Hash = ?";
        $stmt = sqlsrv_query($storeConn, $qry, array($hash));
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
        $paypaltransID = sqlsrv_fetch_array($stmt)['ID'];

        //Get the column names except ID
        $and = "AND COLUMN_NAME NOT IN('ID')";
        $cols = retrieveColumns('Transactions', $and, $storeConn);
        $cols = implode(",", $cols);

        $shipped = $shipping == 0.00 ? true : false;
        $checked = $shipped;
        //Insert the values into Transactions
        $qry = "INSERT INTO Transactions ($cols) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = sqlsrv_query($storeConn, $qry, array($paypaltransID, $totalItems, $shipping, $total, $shipped, $memberTable, $memberNum, $customerFirstName, $customerLastName, $customerAddress, $customerCity, $customerProvince, $customerCountryCode, $customerPhone, $customerEmail, $created, $checked));
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

        //Get the id of the transaction
        $qry = "SELECT ID FROM Transactions WHERE TransactionID = ?";
        $stmt = sqlsrv_query($storeConn, $qry, array($paypaltransID));
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
        $transID = sqlsrv_fetch_array($stmt)['ID'];

        //Get the column names except ID
        $cols = retrieveColumns('PayerDetails', $and, $storeConn);
        $cols = implode(",", $cols);

        //Insert the values into PayerDetails
        $qry = "INSERT INTO PayerDetails ($cols) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = sqlsrv_query($storeConn, $qry, array($transID, $firstname, $lastname, $address, $city, $province, $country, $postalCode, $email, $phone));
        if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

        //Get the column names except ID
        $cols = retrieveColumns('TransactionDetails', $and, $storeConn);
        $cols = implode(",", $cols);

        //add for email details
        
        $purchaseDetails = "<table border='1'>";
        // $purchaseDetails = "<p>" . implode(" ",$info['transactions'][0]['item_list']['items']) . "</p>"
        $purchaseDetails .= "<tr>
        						<th>Product Name</th>
        						<th>Description</th>
								<th>Price</th> 
								<th>Category</th>
								<th>Quantity</th>
							</tr>";
        //Insert the values into TransactionDetails
        for($i = 0; $i < count($ids); $i++){
            $qry = "INSERT INTO TransactionDetails ($cols) VALUES(?, ?, ?, ?)";
            $stmt = sqlsrv_query($storeConn, $qry, array($transID, $ids[$i], $prices[$i], $quantities[$i]));
            if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
            //
            $purchaseDetails .= "<tr>
									<td>" . $names[$i] . "</td>
									<td>" . $descriptions[$i] . "</td>
									<td>$" . $prices[$i] . "</td> 
									<td>" . $categories[$i] . "</td> 
									<td>" . $quantities[$i] . "</td>
								</tr>";
        }

        $purchaseDetails .= "</table>";
        //send email 
        $email = 'estore@mbgenealogy.com';
       //$email = 'qichen.z@hotmail.com';

		global $mailer;

			//add user information
			$customerInfo = "<table border='1'>";

	        //set customerInfo
	        $customerInfo .= "<tr><td><strong>FirstName</strong></td><td>". $customerFirstName . "</td></tr>";
	        $customerInfo .= "<tr><td><strong>LastName</strong></td><td>". $customerLastName . "</td></tr>";
	        $customerInfo .= "<tr><td><strong>Address</strong></td><td>". $customerAddress . "</td></tr>";
	        $customerInfo .= "<tr><td><strong>City</strong></td><td>". $customerCity . "</td></tr>";
	        $customerInfo .= "<tr><td><strong>Province</strong></td><td>". $customerProvince . "</td></tr>";
	        $customerInfo .= "<tr><td><strong>CountryCode</strong></td><td>". $customerCountryCode . "</td></tr>";
	        $customerInfo .= "<tr><td><strong>Phone</strong></td><td>". $customerPhone . "</td></tr>";
	        $customerInfo .= "<tr><td><strong>Email</strong></td><td>". $customerEmail . "</td></tr>";
	        $customerInfo .= "</table>";

        	// subject
			$subject = 'A purchase has been made';
			// message body
			$body = "
			<html>
				<head>
					<meta charset='utf-8'>
				  <title>Purchase</title>
				</head>
				<body>
					<p>".$firstname.' '.$lastname." has just made a purchase from the store.</p>
					<p>Please log in and go to 'Store Management' and click on 'Search Transactions'. Enter the following transaction id to get the details of this transaction, such as which products need to be shipped and where to ship them to.</p>
					".$purchaseDetails."
					<p>Transaction ID: ".$transactionID."</p>
					<p>userConn: ".$userConn."</p>
					<p>Shipping Info:</p>
					".$customerInfo."
				</body>
			</html>
			";

			// To send HTML mail, the Content-type header must be set
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

			// Additional headers
			$headers .= 'To: '.$customerFirstName.' '.$customerLastName.'<'.$customerEmail.'>' . "\r\n";
			
			$headers .= 'From: Manitoba Genealogical Society <mani@mbgenealogy.com>' . "\r\n";

			// Mail it
			// mail('contact@mbgenealogy.com', $subject, $message, $headers);
			$message = Swift_Message
				::newInstance($subject, $body)
				->setFrom('noreply@mbgenealogy.com')
				->setTo($email)
				->setContentType('text/html')
			;

			$result = $mailer->send($message);
		//}
		header("location: completePayment.php?confirm=$confirm&name=$name");
	}
	else
	{
		header("location: shoppingCart.php?name=$name");
	}
?>