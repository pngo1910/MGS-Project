<?php
	session_name('register');
	session_start();

	require('../bootstrap.php');
	require('../db/memberConnection.php');
	require('../errorReporter.php');

	use PayPal\Api\Payer;
	use PayPal\Api\Details;
	use PayPal\Api\Amount;
	use PayPal\Api\Item;
	use PayPal\Api\ItemList;
	use PayPal\Api\Transaction;
	use PayPal\Api\Payment;
	use PayPal\Api\RedirectUrls;
	use PayPal\Exception\PayPalConnectionException;
	use PayPal\Converter\FormatConverter;
	use PayPal\Api\CartBase;

	$payer = new Payer();
	$details = new Details();
	$amount = new Amount();
	$transaction = new Transaction();
	$payment = new Payment();
	$redirectUrls = new RedirectUrls();
	$format = new FormatConverter();
	$cartBase = new CartBase();
	$itemList = new ItemList();

	$subTotal = 0;
	
	if(isset($_SESSION['branches']) && !empty($_SESSION['branches'])){
		$branches = $_SESSION['branches'];
		$queries = array();
		for($i = 0; $i < count($branches); $i++){
			//Retrieve the necessary values from the Branch table to build the items
			$queries[] = "SELECT ID, Name, Price FROM Branch WHERE ID = ?";
		}
		$qry = implode(" UNION ", $queries);
		$stmt = sqlsrv_query($userConn, $qry, $branches, array("Scrollable" => "static"));
		if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
		
		while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
			$item = new Item();
			//3 is SouthWest Branch (if with associate they get a discount)
			if($associatedWith != false && $row['ID'] === 3){
				//Create the items and add to the subtotal
				$item->setQuantity(1)
					 ->setName($row['Name'])
					 ->setPrice(8.00)
					 ->setCurrency("CAD");
				$subTotal += 8.00;
			} else{
				//Create the items and add to the subtotal
				$item->setQuantity(1)
					 ->setName($row['Name'])
					 ->setPrice($row['Price'])
					 ->setCurrency("CAD");
				$subTotal += $row['Price'];
			}
			//Add the items to the list
			$itemList->addItem($item);
		}
	}
	
	$typeOfMember = $_SESSION['type'];
	
	//Retrieve the price for the type of member the user is registering for
	$qry = "SELECT Price FROM TypeOfMember WHERE ID = ?";
	$stmt = sqlsrv_query($userConn, $qry, array($typeOfMember), array("Scrollable" => "static"));
	if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	$registerPrice = sqlsrv_fetch_array($stmt)['Price'];
	//Add the price to the subtotal
	$subTotal += $registerPrice;

	$item = new Item();
	//The user is registering as an individual
	if($typeOfMember == 1)
		$item->setName("Individual Register");
	else
		$item->setName("Associate Register");

	//Create the item
	$item->setQuantity(1)
		 ->setPrice($registerPrice)
		 ->setCurrency("CAD");

	//Add the item to the list
	$itemList->addItem($item);

	//Set the tax and subtotal
	$details->setTax('0.00')
	        ->setSubtotal($format->formatToNumber($subTotal, 2));

	//Set the currency, total and details
	$amount->setCurrency('CAD')
	       ->setTotal($format->formatToNumber($subTotal, 2))
	       ->setDetails($details);

	//Create the transaction
	$transaction->setAmount($amount)
	            ->setDescription('Membership Register')
	            ->setItemList($itemList);

	//Get the base url
	$url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	preg_match_all('/\//', $url,$matches, PREG_OFFSET_CAPTURE);
	$length = $matches[0][count($matches[0])-1][1];
	$baseUrl = substr($url, 0, $length);

	$redirectUrls->setReturnUrl("$baseUrl/completeRegisterPayment.php?confirm=true")
		    	 ->setCancelUrl("$baseUrl/completeRegisterPayment.php?confirm=false");

    //Set the payment method as paypal
	$payer->setPaymentMethod('paypal');

	//Set the intent, payer, transaction(s), and redirect urls
	$payment->setIntent('sale')
	        ->setPayer($payer)
	        ->setTransactions(array($transaction))
			->setRedirectUrls($redirectUrls);

	try{
		//Create the payment
		$payment->create($apiContext);
		//Generate a hash
		$hash = md5($payment->getId());
		$_SESSION['Paypal_hash'] = $hash;

		//Prepare transaction storage
		$getPaymentID = $payment->getId();

		//Will change to 1 only if the user completes the payment
		$notCompleted = 0;

		//Insert the values into Transactions
		$query = "INSERT INTO PayPalTransactions (PaymentID, Hash, Complete) VALUES (?, ?, ?)";
		$params = array($getPaymentID, $hash, $notCompleted);
		$statement = sqlsrv_query($userConn, $query, $params);
		if ($statement === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	} catch(PayPalConnectionException $e) {
		//Echo out the exception
		echo $e->getData();
		//Kill the script
		exit(0);
	}

	foreach($payment->getLinks() as $link) {
		//Retrieve the approval url to send the user to paypal
		if($link->getRel() == 'approval_url'){
			$redirectUrl = $link->getHref();
		}
	}

	//Redirect to paypal
	header('location: ' . $redirectUrl);
 ?>