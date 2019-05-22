<?php
	require('../bootstrap.php');
	require('../db/loginCheck.php');
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

	//Get the type of member
	$typeOfMember = $_SESSION['membertype'];
	$subTotal = 0;
	
	$individual = array();
	$associates = array();
	$branches = array();
	$branchesForAssociate = array(); 
	//The $_POST superglobal has an array of key/value pairs as well as an array inside of that array
	foreach($_POST as $key => $value){
		if($key != "submit"){
			//Determine if the value is an array
		 	if(is_array($value)){
		 		//Get the branches
		 		if($key === "branch"){
			 		for($i = 0; $i < count($value); $i++){
			 			$branches[] = $value[$i];
			 		}
			 	//Get the associates
			 	}elseif($key === "associate"){
			 		for($i = 0; $i < count($value); $i++){
			 			$associates[] = $value[$i];
			 		}
			 	}elseif($key === "branchesForAssociate"){
			 		for($i = 0; $i < count($value); $i++){
			 			$branchesForAssociate[] = $value[$i];
			 		}
			 	}
		 	} else{
		 		//Get the individual
		 		$individual[$key] = $value;
		 	}
		}
	}

	if(isset($_POST["donation"])){
		$donation = filter_input(INPUT_POST, 'donation' ,FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
		
	}

	//This is just for the transaction description.
	//There could be mulitiple purchases/renewals going on (Associate Renewal, Individual Renewal, and Branch Purchases/Renewals)
	$renewalName = array();

	//Check if there are any associates
	if(count($associates) != 0){
		$associate = 2;
		$renewalName[] = "Associate Renewal";
		//Add the associates to a session
		$_SESSION['associates'] = $associates;
		//Get the price for the associate member
		$qry = "SELECT Price FROM TypeOfMember WHERE ID = ?";
		$stmt = sqlsrv_query($userConn, $qry, array($associate), array("Scrollable"=>"static"));
		if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

		$associatePrice = sqlsrv_fetch_array($stmt)['Price'];

		//Loop through the associates
		for($i = 0; $i < count($associates); $i++){
			//Get the first and last names of the associate
			$qry = "SELECT FirstName, LastName FROM MemberInfo WHERE MemberNum = ?";
			$stmt = sqlsrv_query($userConn, $qry, array($associates[$i]), array("Scrollable"=>"static"));
			if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

			//This is just so the user knows which user they're paying for when they get to paypal
			$name = "Associate: ";
			while($row = sqlsrv_fetch_array($stmt))
				$name .= $row['FirstName'] . " " . $row['LastName'];

			$item = new Item();
			//Create the item
			$item->setQuantity(1)
				 ->setName($name)
				 ->setDescription("Associate Renewal")
				 ->setPrice($associatePrice)
				 ->setCurrency("CAD");
			//Add the item to the list
			$itemList->addItem($item);
			//Add the price to the subtotal
			$subTotal += $associatePrice;
		}
	}

	//Check if there are any branches
	if(count($branches) != 0){
		$renewalName[] = "Branch Purchase/Renewal";
		//Add the branches to  a session
		$_SESSION['branches'] = $branches;
		$queries = array();
		for($i = 0; $i < count($branches); $i++){
			//Get the values from the Branch Table
			$queries[] = "SELECT ID, Name, Price FROM Branch WHERE ID = ?";
		}
		$qry = implode(" UNION ", $queries);
		$stmt = sqlsrv_query($userConn, $qry, $branches, array("Scrollable" => "static"));
		if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
		
		while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
			$item = new Item();
			//3 is SouthWest Branch (if associate they get a discount)
			if($typeOfMember === 2 && $row['ID'] === 3){
				//Create the item
				$item->setQuantity(1)
					 ->setName($row['Name'])
					 ->setDescription("Branch Purchase/Renewal")
					 ->setPrice(8.00)
					 ->setCurrency("CAD");
				$subTotal += 8.00;
			} else{
				//Create the item
				$item->setQuantity(1)
					 ->setName($row['Name'])
					 ->setDescription("Branch Purchase/Renewal")
					 ->setPrice($row['Price'])
					 // ->setPrice(0.01)
					 ->setCurrency("CAD");
				$subTotal += $row['Price'];
				// $subTotal += 0.01;
			}
			//Add the item to the list
			$itemList->addItem($item);
		}
	}

	//Check if there are any branchesForAssociate
	if(count($branchesForAssociate) != 0){
		$renewalName[] = "Associate Branch B Purchase/Renewal";
		//Add the branchesForAssociate to a session
		$_SESSION['branchesForAssociate'] = $branchesForAssociate;
		$queries = array();
		//create new array to store all the branches without the Associate member number
		$branchesForAssociateVoutNum = array();
		for($i = 0; $i < count($branchesForAssociate); $i++){
			//Get the values from the Branch Table
			$queries[] = "SELECT ID, Name, Price FROM Branch WHERE ID = ?";
			$branchesForAssociateVoutNum[] = explode(" ", $branchesForAssociate[$i])[1];
		}
		$qry = implode(" UNION ", $queries);
		$stmt = sqlsrv_query($userConn, $qry, $branchesForAssociateVoutNum, array("Scrollable" => "static"));
		if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
		
		while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
			$item = new Item();
			//3 is SouthWest Branch (if associate they get a discount)
			if($typeOfMember === 2 && $row['ID'] === 3){
				//Create the item
				$item->setQuantity(1)
					 ->setName($row['Name'])
					 ->setDescription("Associate Branch Purchase/Renewal")
					 ->setPrice(8.00)
					 ->setCurrency("CAD");
				$subTotal += 8.00;
			} else{
				//Create the item
				$item->setQuantity(1)
					 ->setName($row['Name'])
					 ->setDescription("Associate Branch Purchase/Renewal")
					 ->setPrice($row['Price'])
					 // ->setPrice(0.01)
					 ->setCurrency("CAD");
				$subTotal += $row['Price'];
					 // $subTotal += 0.01;
			}
			//Add the item to the list
			$itemList->addItem($item);
		}
	}
	
	//Check if the user is renewing their own membership
	if(count($individual) != 0){
		$renewalName[] = "Individual Renewal";
		//Get the price for whichever type of member they are
		$qry = "SELECT Price FROM TypeOfMember WHERE ID = ?";
		$stmt = sqlsrv_query($userConn, $qry, array($typeOfMember), array("Scrollable" => "static"));
		if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
		$renewPrice = sqlsrv_fetch_array($stmt)['Price'];
		//Add the price to the subtotal
		$subTotal += $renewPrice;

		$item = new Item();
		//Create the item
		$item->setName("Renewal")
			 ->setDescription("Individual Renewal")
			 ->setQuantity(1)
			 ->setPrice($renewPrice)
			 ->setCurrency("CAD");
	    //Add the item to the list
		$itemList->addItem($item);
	}


	if ($donation != 0) {
			//This is just so the user knows which user they're paying for when they get to paypal
			$name = "Donation: ";
			$item = new Item();
			//Create the item
			$item->setQuantity(1)
				 ->setName($name)
				 ->setDescription("Donation")
				 ->setPrice($donation)
				 ->setCurrency("CAD");
			//Add the item to the list
			$itemList->addItem($item);
			//Add the price to the subtotal
			$subTotal += $donation;
	}

	//Set the tax as 0 and set the subtotal
	$details->setTax('0.00')
	        ->setSubtotal($format->formatToNumber($subTotal, 2));

	//Set the currency, total and details
	$amount->setCurrency('CAD')
	       ->setTotal($format->formatToNumber($subTotal, 2))
	       ->setDetails($details);

	//Implode the $renewalName into a string
	$renewalName = implode(", ", $renewalName);
	//Set the amount, description and item list
	$transaction->setAmount($amount)
	            ->setDescription($renewalName)
	            ->setItemList($itemList);

	$url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	preg_match_all('/\//', $url,$matches, PREG_OFFSET_CAPTURE);
	$length = $matches[0][count($matches[0])-1][1];
	$baseUrl = substr($url, 0, $length);
	//Create the redirect urls
	$redirectUrls->setReturnUrl("$baseUrl/completeRenewalPayment.php?confirm=true")
		    	 ->setCancelUrl("$baseUrl/completeRenewalPayment.php?confirm=false");
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