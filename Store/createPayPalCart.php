<?php
	$name = isset($_GET['name'])? $_GET['name']: 'store';
    switch($name){
        case 'login':
            require('../db/loginCheck.php');
            break;
        default:
            session_name($name);
            session_start();
    }
	require('../db/storeConnection.php');
	//require '../sandbox/bootstrap.php';
	require '../bootstrap.php';
	require('../errorReporter.php');
	use PayPal\Api\Amount;
	use PayPal\Api\Details;
	use PayPal\Converter\FormatConverter;
	use PayPal\Api\Item;
	use PayPal\Api\ItemList;
	use PayPal\Api\Payer;
	use PayPal\Api\Payment;
	use PayPal\Api\RedirectUrls;
	use PayPal\Api\Transaction;
	use PayPal\Api\Transactions;
	use PayPal\Api\CartBase;

	$amount = new Amount();
	$details = new Details();
	$format = new FormatConverter();
	$itemList = new ItemList();
	$payer = new Payer();
	$payment = new Payment();
	$redirectUrls = new RedirectUrls();
	$transaction = new Transaction();
	$transactions = new Transactions();
	$cartBase = new CartBase();

	//Get the items in the session
	$items = $_SESSION['items'];

	print_r($items);
	//Set the payment method to paypal
	$payer->setPaymentMethod("paypal");

	$subTotal = 0;
	$shippingTotal = 0;

	//Loop through the items
	foreach($items as $key => $value){
		//Create a new item
		$item = new Item();
		$price = floatval($value['Price']);
		$price = $format->formatToNumber($price, 2);
		$quantity = (int)$value['Quantity'];
		$description = isset($value['Category']) ? $value['Category'] . " - " . $value['Description'] : $value['Description'];
		//Set the values
		$item->setSku((int)$value['ID'])
			 ->setName($value['Name'])
			 ->setDescription($description)
			 ->setQuantity($quantity)
			 ->setPrice($price)
			 ->setCurrency("CAD");
		//Add the item to the list
		$itemList->addItem($item);
		//Multiply the quantity and price then add it to the subtotal
		$subTotal += $quantity * $price;
		//Format the shipping and add it to the shipping total
		// 
		$shipping = floatval($value['Shipping']);
		$shipping = $shipping * $quantity;

		$shippingTotal += $format->formatToNumber($shipping, 2);
	}

	$shippingTotal = $shippingTotal>20 ? 20: $shippingTotal;
	//Set the shipping and subtotal
	$details->setShipping($format->formatToNumber($shippingTotal, 2))
	    	->setSubtotal($format->formatToNumber($subTotal, 2));

	//Create the final total
	$total = ($subTotal + $shippingTotal);
	//Set the currency, total and details
	$amount->setCurrency("CAD")
	       ->setTotal($format->formatToNumber($total, 2))
	       ->setDetails($details);

	//Get the base url
	$url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	preg_match_all('/\//', $url,$matches, PREG_OFFSET_CAPTURE);
	$length = $matches[0][count($matches[0])-1][1];
	$baseUrl = substr($url, 0, $length);

	//Set the redirect urls
	$redirectUrls->setReturnUrl("$baseUrl/completeStorePayment.php?confirm=true&name=$name")
	    		 ->setCancelUrl("$baseUrl/completeStorePayment.php?confirm=false&name=$name");

	//Set the amount, description and itemlist
	$transaction->setAmount($amount)
				->setDescription("Store")
				->setItemList($itemList);

    //Set the intent, payer, transactions and redirect urls
	$payment->setIntent("sale")
			->setPayer($payer)
			->setTransactions(array($transaction))
			->setRedirectUrls($redirectUrls);

	try{
		//Create the payment
		$payment->create($apiContext);

		//Create a hash
		$hash = md5($payment->getId());
		//Store the hash in a session
		$_SESSION['Paypal_hash'] = $hash;

		//perpare transaction storage
		$getPaymentID = $payment->getId();

		$notCompleted = 0;

		//Insert the values into the table
		$query = "INSERT INTO PayPalTransactions (PaymentID, Hash, Complete) VALUES (?, ?, ?)";
		$params = array($getPaymentID, $hash, $notCompleted);
		$statement = sqlsrv_query($storeConn, $query, $params);

		if( $statement === false ) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
	} catch (Exception $ex) {
		//Print the error
		print_r($ex);
		$_SESSION['error'] = "There was an error with PayPal. Please try again.";
		header('location: /Store/store.php');
	}

	
	foreach($payment->getLinks() as $link) {
		//Get the approval url
		if($link->getRel() == 'approval_url'){
			$redirectUrl = $link->getHref();
		}
	}

	//check user login info
	$name = $_POST['name'];
	if ($name == 'store') {
		$firstName = filter_var($_POST['firstName'], FILTER_SANITIZE_STRING);
		$lastName = filter_var($_POST['lastName'], FILTER_SANITIZE_STRING);
		$address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
		$city = filter_var($_POST['city'], FILTER_SANITIZE_STRING);
		$province = filter_var($_POST['province'], FILTER_SANITIZE_STRING);
		$countryCode = filter_var($_POST['countryCode'], FILTER_SANITIZE_STRING);
		$phone = $_POST['phone'];
		$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

		if(empty($firstName) || $firstName==="" || empty($lastName) || empty($address) || empty($city) || empty($province) || empty($countryCode) || empty($phone) || empty($email) || filter_var($email, FILTER_VALIDATE_EMAIL) === false)
		{
			//header('location: shoppingCart.php?name=store');
			header('Location: ' . $_SERVER['HTTP_REFERER']);
		}
		else
		{
			header('location: ' . $redirectUrl);
		}

		$_SESSION['UnloggedInUserInfo'] = implode("<>", array($firstName, $lastName, $address, $city, $province, $countryCode, $email, $phone));
	}
	else
	{
		header('location: ' . $redirectUrl);
	}
?>