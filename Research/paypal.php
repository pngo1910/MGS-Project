<?php
	require('../bootstrap.php');
	require('../db/memberConnection.php');
	require('../errorReporter.php');
	$params = "";
	if(isset($_GET['name']) && $_GET['name'] === "login"){
		$params = "&name=login";
		require('../db/loginCheck.php');
	} else{
		session_start();
	}

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

	$values = $_SESSION['checkoutValues'];
	$locationPrice = 10.00;
	$memberPrice = 60.00;
	$nonMemberPrice = 75.00;
	//$shipping = 0.00;
	$subTotal = 0;

	$locations = array();
	$info = array();
	//The $values variable has an array of key/value pairs as well as an array inside of that array
	foreach($values as $key => $value){
		if($key != "formSubmit"){
			//Determine if the value is an array
		 	if(is_array($value)){
		 		for($i = 0; $i < count($value); $i++)
		 			$locations[] = $value[$i];
		 	} else{
		 		//Get the user information
		 		$info[$key] = $value;
		 	}
		}
	}
	/*
	$item = new Item();
	//Check if login is in the parameter
	if(isset($_GET['name']) && $_GET['name'] === "login"){
		//Create the item
		$item->setPrice($format->formatToNumber($memberPrice, 2));
		//Set the transaction description
		$transaction->setDescription("Member Custom Research Package");
		//Add the price to the subtotal
		$subTotal += $memberPrice;
	} else{
		//Create the item
		$item->setPrice($format->formatToNumber($nonMemberPrice, 2));
		//Set the transaction description
		$transaction->setDescription("Non-Member Custom Research Package");
		//Add the price to the subtotal
		$subTotal += $nonMemberPrice;
	}
	
	//Create the item
	$item->setQuantity(1)
		 ->setName($info['surname'] . " " . $info['givenName'])
		 ->setDescription($info['description'])
		 ->setCurrency("CAD");
	//Add the item to the list
	$itemList->addItem($item);
	*/
	//Loop through the locations
	for($i = 0; $i < count($locations); $i++){
		$item = new Item();
		//Create the item
		$item->setQuantity(1)
			 ->setName($locations[$i])
			 ->setPrice($format->formatToNumber($locationPrice, 2))
			 ->setCurrency("CAD");
		//Add the item to the list
		$itemList->addItem($item);
		//Add the price to the subtotal
		$subTotal += $locationPrice;
	}

	//Set the tax as 0, set the shipping and set the subtotal
	$details->setTax('0.00')
			//->setShipping($format->formatToNumber($shipping, 2))
	        ->setSubtotal($format->formatToNumber($subTotal, 2));

	//Create the final total
	//$total = $subTotal + $shipping;
	$total = $subTotal;

	//Set the currency, total and details
	$amount->setCurrency('CAD')
	       ->setTotal($format->formatToNumber($total, 2))
	       ->setDetails($details);

	//Set the amount and item list
	$transaction->setAmount($amount)
	            ->setItemList($itemList);

	$url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	preg_match_all('/\//', $url,$matches, PREG_OFFSET_CAPTURE);
	$length = $matches[0][count($matches[0])-1][1];
	$baseUrl = substr($url, 0, $length);

    //Create the redirect urls
	$redirectUrls->setReturnUrl("$baseUrl/completeCustomPayment.php?confirm=true$params")
		    	 ->setCancelUrl("$baseUrl/completeCustomPayment.php?confirm=false$params");

    //Set the payment method
	$payer->setPaymentMethod('paypal');
	//Set the intent, payer, transactions and redirectUrls
	$payment->setIntent('sale')
	        ->setPayer($payer)
	        ->setTransactions(array($transaction))
			->setRedirectUrls($redirectUrls);

	try{
		//Create the payment
		$payment->create($apiContext);
		//generate a hash
		$hash = md5($payment->getId());
		$_SESSION['Paypal_hash'] = $hash;

		//perpare transaction storage
		$getPaymentID = $payment->getId();

		$notCompleted = 0;
		//Insert the values into PayPalTransactions
		$query = "INSERT INTO PayPalTransactions (PaymentID, Hash, Complete) VALUES (?, ?, ?)";
		$params = array($getPaymentID, $hash, $notCompleted);
		$statement = sqlsrv_query($userConn, $query, $params);
		if ($statement === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	} catch(PayPalConnectionException $e) {
		//Echo the error
		echo $e->getData();
		//Kill the script
		exit(0);
	}

	foreach($payment->getLinks() as $link) {
		//Get the approval url
		if($link->getRel() == 'approval_url'){
			$redirectUrl = $link->getHref();
		}
	}

	//Redirect to paypal
	header('location: ' . $redirectUrl);
 ?>