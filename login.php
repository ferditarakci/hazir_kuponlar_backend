<?php

require_once('inc/globals.php');

require_once(__ROOT__ . '/inc/facebook/autoload.php');

$fb = new Facebook\Facebook([
	'app_id' => FB_APP_ID,
	'app_secret' => FB_APP_SECRET,
	'default_graph_version' => 'v5.0',
]);

$helper = $fb->getRedirectLoginHelper();

$permissions = ['email'];
$loginUrl = $helper->getLoginUrl(SITE_URL. '/fb-callback.php', $permissions);

$url = htmlspecialchars($loginUrl);

if (isset($_GET['a'])) {
	if ($_GET['a'] == "1") {
		header("Location: $url", false, 302);
		exit;
	}
}
else {
	echo '<a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a>';
}