<?php

require_once('inc/globals.php');

$fb = new Facebook\Facebook([
	'app_id' => FB_APP_ID,
	'app_secret' => FB_APP_SECRET,
	'default_graph_version' => 'v5.0',
]);

$helper = $fb->getRedirectLoginHelper();

if (isset($_GET['state'])) {
	$helper->getPersistentDataHandler()->set('state', $_GET['state']);
}

try {
	$accessToken = $helper->getAccessToken();
} catch (Facebook\Exceptions\FacebookResponseException $e) {
	// When Graph returns an error
	echo 'Graph returned an error: ' . $e->getMessage();
	exit;
} catch (Facebook\Exceptions\FacebookSDKException $e) {
	if ($_GET['tkattempt'] != 2) {
		$permissions = ['email']; // optional
		$loginUr2l = $helper->getLoginUrl(SITE_URL . '/login.php', $permissions);
		$loginUr2l .= "&tkattempt=2";
		header("location: " . $loginUr2l);
		exit;
	} else {
		header("location: " . SITE_URL . "/hata.php?errcode=fb");
		exit;
	}
}

if (!isset($accessToken)) {
	if ($helper->getError()) {
		header('HTTP/1.0 401 Unauthorized');
		header("Refresh: 3; url=" . SITE_URL . "/");
		echo "Error: " . $helper->getError() . "<br>";
		echo "Error Code: " . $helper->getErrorCode() . "<br>";
		echo "Error Reason: " . $helper->getErrorReason() . "<br>";
		echo "Error Description: " . $helper->getErrorDescription() . "<br>";
		exit;
	} else {
		header('HTTP/1.0 400 Bad Request');
		echo 'Bad request';
	}
	exit;
}

// The OAuth 2.0 client handler helps us manage access tokens
$oAuth2Client = $fb->getOAuth2Client();

// Get the access token metadata from /debug_token
$tokenMetadata = $oAuth2Client->debugToken($accessToken);

// Validation (these will throw FacebookSDKException's when they fail)
$tokenMetadata->validateAppId(FB_APP_ID); // Replace {app-id} with your app id
// If you know the user ID this access token belongs to, you can validate it here

$tokenMetadata->validateExpiration();

if (!$accessToken->isLongLived()) {
	// Exchanges a short-lived access token for a long-lived one
	try {
		$accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
	} catch (Facebook\Exceptions\FacebookSDKException $e) {
		echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
		exit;
	}
}

$_SESSION['facebook_access_token'] = (string)$accessToken;
$_SESSION['fb_access_token'] = (string)$accessToken;

try {
	// Get your UserNode object with fields name and hometown, replace {access-token} with your token
	$response = $fb->get('/me?fields=first_name,last_name,name,hometown,email,short_name', $accessToken);
} catch (\Facebook\Exceptions\FacebookResponseException $e) {
	// Returns Graph API errors when they occur
	echo 'Graph returned an error: ' . $e->getMessage();
	exit;
} catch (\Facebook\Exceptions\FacebookSDKException $e) {
	// Returns SDK errors when validation fails or other local issues
	echo 'Facebook SDK returned an error: ' . $e->getMessage();
	exit;
}

$me = $response->getGraphUser();
$me = json_decode($me);

$time = time();

$db = openDB();

$banned = 0;

$rs = $db->prepare("SELECT id, first_name, last_name, username, email_address, banned FROM tk_users WHERE (email_address=:email_address OR facebook_id=:facebook_id) LIMIT 1");
$rs->bindValue(':email_address', $me->email, PDO::PARAM_STR);
$rs->bindValue(':facebook_id', $me->id, PDO::PARAM_STR);
$rs->execute();

$userCount = $rs->rowCount();

// print_r($userCount);

if ($userCount > 0) {

	$user = $rs->fetch(PDO::FETCH_ASSOC);

	if ($user["banned"] == 1) {
		$banned = 1;
	} else {

		$_SESSION['user'] = array(
			'id'          => $user["id"],
			'first_name'  => $user["first_name"],
			'last_name'   => $user["last_name"],
			'username'    => $user["username"],
			'email'       => $user["email_address"]
		);

		$st = $db->prepare("UPDATE tk_users SET last_login_date=:last_login_date, last_login_ip=:last_login_ip WHERE banned = 0 AND id=:id");
		$st->bindValue(':id', $user["id"], PDO::PARAM_INT);
		$st->bindValue(':last_login_date', $time, PDO::PARAM_INT);
		$st->bindValue(':last_login_ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
		$st->execute();
	}
} else {

	$username = seflink($me->first_name . ' ' . $me->last_name);

	$st = $db->prepare("REPLACE INTO tk_users (first_name, last_name, email_address, username, password, facebook_id, registration_date, last_login_date, last_login_ip, activation_status, activation_date, atb_last_update_time) VALUES (:first_name, :last_name, :email_address, :username, :password, :facebook_id, :registration_date, :last_login_date, :last_login_ip, :activation_status, :activation_date, :atb_last_update_time)");
	$st->bindValue(':first_name', $me->first_name, PDO::PARAM_STR);
	$st->bindValue(':last_name', $me->last_name, PDO::PARAM_STR);
	$st->bindValue(':email_address', $me->email, PDO::PARAM_STR);
	$st->bindValue(':username', $username, PDO::PARAM_STR);
	$st->bindValue(':password', '', PDO::PARAM_STR);
	$st->bindValue(':facebook_id', $me->id, PDO::PARAM_STR);
	$st->bindValue(':registration_date', $time, PDO::PARAM_INT);
	$st->bindValue(':last_login_date', $time, PDO::PARAM_INT);
	$st->bindValue(':last_login_ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
	$st->bindValue(':activation_status', 1, PDO::PARAM_INT);
	$st->bindValue(':activation_date', $time, PDO::PARAM_INT);
	$st->bindValue(':atb_last_update_time', $time, PDO::PARAM_INT);
	$st->execute();

	//$user = $st->fetch(PDO::FETCH_ASSOC);

	$_SESSION['user'] = array(
		'id'          => $db->lastInsertId(),
		'first_name'  => $me->first_name,
		'last_name'   => $me->last_name,
		'username'    => $username,
		'email'       => $me->email
	);
}

//header("Refresh: 0; url=" . $_SERVER['HTTP_REFERER']);

$referer = @$_SERVER['HTTP_REFERER'];

if ($banned == 1) {
	header("Refresh: 0; url=" . SITE_URL . "/#banned=1");
	exit;
}

if (preg_match("/hazirkuponlar/i", $referer)) {
	header("Refresh: 0; url=" . str_replace("#_=_", "", $referer));
} else {
	header("Refresh: 0; url=" . str_replace("#_=_", "", SITE_URL));
}