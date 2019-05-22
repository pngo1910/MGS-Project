<?php
	require __DIR__ . '/PayPal-PHP-SDK-master/autoload.php';
	use PayPal\Rest\ApiContext;
	use PayPal\Auth\OAuthTokenCredential;

	if (!defined("PP_CONFIG_PATH")) {
        define("PP_CONFIG_PATH", dirname(__DIR__."\\mani\\"));
    }

	$apiContext = new ApiContext(new OAuthTokenCredential(
		'Ac0pXY9UaS2gCElbGqYEPKX_58u_Cp14GAmrOlcIgN3WqpsNpHNJUDVN2ChUXgtXvNyif043k5XN2Q0M',
		'EHZ_kUnShg9DTWJnFDX27nfiziLmBpnwjaZH-8nhmgsVyK_GMcLY36UUWlJXnCa8nV4MwlUVJReZPQo1'
	));

?>