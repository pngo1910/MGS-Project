<?php
	require dirname(__DIR__).'/PayPal-PHP-SDK-master/autoload.php';
	use PayPal\Rest\ApiContext;
	use PayPal\Auth\OAuthTokenCredential;

	if (!defined("PP_CONFIG_PATH")) {
        define("PP_CONFIG_PATH", dirname(__DIR__."\\mani\\"));
    }

	$apiContext = new ApiContext(new OAuthTokenCredential(
		'AQM2FsKdSqDsnkuFdXGpCUP2owXzTAS8cASITxlGcr8y88vHGjBzteBZz5tnU5C_XLCjCT3BoKPMvkbt',
		'EGcaNAQo6QeKMUzfCwOrAlErf0tCnf5wB5WW-iyFV6KdHR0Jf5peRPQkvg8Oz7Lyoq-aU9k4km-UPBrP'
	));

?>