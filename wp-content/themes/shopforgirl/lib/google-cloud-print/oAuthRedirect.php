<?php

require_once 'Config.php';
require_once 'GoogleCloudPrint.php';

if (isset($_GET['op'])) {

	if ($_GET['op']=="getauth") {
		header("Location: ".$urlconfig['authorization_url']."?".http_build_query($redirectConfig));
		exit;
	}
	else if ($_GET['op']=="offline") {
		header("Location: ".$urlconfig['authorization_url']."?".http_build_query(array_merge($redirectConfig,$offlineAccessConfig)));
		exit;
	}
}


// Google redirected back with code in query string.
if(isset($_GET['code']) && !empty($_GET['code'])) {

    $code = $_GET['code'];
    $authConfig['code'] = $code;

    // Create object
    $gcp = new GoogleCloudPrint();
    $responseObj = $gcp->getAccessToken($urlconfig['accesstoken_url'],$authConfig);

    $accessToken = $responseObj->access_token;

    // We requested offline access
    /*if (isset($responseObj->refresh_token)) {
	header("Location: offlineToken.php?offlinetoken=".$responseObj->refresh_token);
	exit;
    }*/
    $_SESSION['accessToken'] = $accessToken;
  //  header("Location: example.php");
}
