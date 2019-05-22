<?php
	$firstName = $lastName = $address = $city = $province = $countryCode = $phone = $email = "";
	if($memberNum > -1){
		$qry = "SELECT * FROM MemberInfo WHERE MemberNum = ?";
		$stmt = sqlsrv_query($userConn, $qry, array($memberNum));
		if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
		while($row = sqlsrv_fetch_array($stmt)){
			$firstName = $row['FirstName'];
			$lastName = $row['LastName'];
			$address = $row['Address'];
			$city = $row['City'];
			$province = $row['Province'];
			$countryCode = $row['CountryCode'];
			$phone = $row['Phone'];
			$email = $row['Email'];
		}
	}

	$disabled = (!empty($firstName))? "readonly" : "";

	echo '<h2>User Information:</h2>
			<input type="hidden" id="memberNum" name="memberNum" value="' .$memberNum. '"/>
			<label for="firstName">First Name:</label>
			<input type="text" id="firstName" name="firstName" required="true" value="' .$firstName. '" '.$disabled.' /> *
			<label for="lastName">Last Name:</label>
			<input type="text" id="lastName" name="lastName" required="true" value="' .$lastName. '" '.$disabled.'/> *
			<label for="address">Address:</label>
			<input type="text" id="address" name="address" required="true" value="' .$address. '" '.$disabled.' /> *
			<label for="city">City:</label>
			<input type="text" id="city" name="city" required="true" value="' .$city. '" '.$disabled.' /> *
			<label for="province">Province:</label>
			<input type="text" id="province" name="province" required="true" value="' .$province. '" '.$disabled.' /> *
			<label for="countryCode">Postal Code:</label>
			<input type="text" id="countryCode" name="countryCode" required="true" value="' .$countryCode. '" '.$disabled.' /> *
			<label for="phone">Phone:</label>
			<input type="text" id="phone" name="phone" required="true" value="' .$phone. '" '.$disabled.' /> *
			<label for="email">Email:</label>
			<input type="text" id="email" name="email" required="true" value="' .$email. '" '.$disabled.' /> *';
?>