<?php

session_start();
setlocale(LC_TIME, 'tr_TR.UTF-8');
date_default_timezone_set('Europe/Istanbul');

header('Content-Type: text/html; charset=utf-8');

set_time_limit(14400);
error_reporting(-1);
ini_set('display_errors', 1);
ini_set('memory_limit', '2048M');
ini_set('mysql.connect_timeout', 14400);
ini_set('default_socket_timeout', 14400);
ini_set('max_execution_time', 14400);
ini_set('wait_timeout', 14400);
ini_set('interactive_timeout', 14400);
ini_set('error_reporting', E_ALL);

// $version = time();
$version = 3;
$version = "?v=" . $version;

define('__ROOT__', dirname(dirname(__FILE__)));

define("SITE_TITLE", "Hazır Kuponlar");

define("SITE_ADDRESS", "www.hazirkuponlar.net");

define("SITE_URL", "https://" . SITE_ADDRESS);

define("FB_APP_ID", "497455214255222");
define("FB_APP_SECRET", "d1eb3b94cf39768aa12368e2e76ab43a");

define("TUTAN_KUPON", 0);
define("BANKO_KUPON", 1);
define("GOLCU_KUPON", 2);
define("SURPRIZ_KUPON", 3);
define("SISTEM_3_4_KUPON", 4);
define("SISTEM_4_5_6_KUPON", 5);

define("KUPON_TIPI_0", "TUTAN KUPONLAR");
define("KUPON_TIPI_1", "BANKO KUPON");
define("KUPON_TIPI_2", "GOLCÜ KUPON");
define("KUPON_TIPI_3", "SÜRPRİZ KUPON");
define("KUPON_TIPI_4", "SİSTEM 3-4 KUPON");
define("KUPON_TIPI_5", "SİSTEM 4-5-6 KUPON");

$couponMatchLimit = 4;
$couponDayLimit = 2;

$date = date('d/m/Y');
$today = date('Y-m-d');

$startOfDay = 1570037983;
$now = 1580504109 + 86400;

$time = time();
$now = time() + 3600;

$startOfDay = mktime(12, 0, 0, date("m"), date("d"), date("Y")) + (36 * 3600);

$site = array();

require_once('conn.php');


function DateDayTitle($today = 'today') {

    $today = date("Y-m-d", strtotime($today));

    $days = array(
        $today
        ,date("Y-m-d", strtotime("+1 days", strtotime($today)))
        ,date("Y-m-d", strtotime("+2 days", strtotime($today)))
    );

    $dates = array();
    $count = 0;
    $totalCount = sizeof($days) - 1;

    $title = "";

    foreach ($days As $day) {
        $day = strtotime($day);
        $d = date('j', $day);
        $m = date('m', $day);
        $F = turkishDate('F', $day);
        $Y = date('Y', $day);

        if ($count > 0 AND (@$dates[0] == $m))  $title .= ",";

        if (($count >= 1 OR $count < $totalCount) AND @$dates[0] != $m) {
            $title .= " " . @$dates[2] . " ";
            if (!($totalCount >= $count AND @$dates[1] != $Y)) $title .= " ve ";
        }

        if (($count == 1 OR $count <= $totalCount) AND @$dates[1] != $Y) {
            $title .= " " . @$dates[1] . " ";
            if (!(($count == 0) AND @$dates[1] != $Y)) $title .= " ve ";
        }

        $title .= $d;

        if ($count == $totalCount AND @$dates[0] == $m) {
            $title .= " " . $F;
            $title .= " " . $Y . " ";
        }

        elseif ($count == $totalCount AND @$dates[0] != $m) {
            $title .= " " . $F;
            $title .= " " . $Y . " ";
        }

        $dates[0] = $m;
        $dates[1] = $Y;
        $dates[2] = $F;
        $count++;
    }

    return trim($title);
}


function turkishDate($format, $datetime = 'now') {
	$z = date($format, $datetime);
	$gun_dizi = array(
		'Monday'    => 'Pazartesi',
		'Tuesday'   => 'Salı',
		'Wednesday' => 'Çarşamba',
		'Thursday'  => 'Perşembe',
		'Friday'    => 'Cuma',
		'Saturday'  => 'Cumartesi',
		'Sunday'    => 'Pazar',
		'January'   => 'Ocak',
		'February'  => 'Şubat',
		'March'     => 'Mart',
		'April'     => 'Nisan',
		'May'       => 'Mayıs',
		'June'      => 'Haziran',
		'July'      => 'Temmuz',
		'August'    => 'Ağustos',
		'September' => 'Eylül',
		'October'   => 'Ekim',
		'November'  => 'Kasım',
		'December'  => 'Aralık',
		'Mon'       => 'Pts',
		'Tue'       => 'Sal',
		'Wed'       => 'Çar',
		'Thu'       => 'Per',
		'Fri'       => 'Cum',
		'Sat'       => 'Cts',
		'Sun'       => 'Paz',
		'Jan'       => 'Oca',
		'Feb'       => 'Şub',
		'Mar'       => 'Mar',
		'Apr'       => 'Nis',
		'Jun'       => 'Haz',
		'Jul'       => 'Tem',
		'Aug'       => 'Ağu',
		'Sep'       => 'Eyl',
		'Oct'       => 'Eki',
		'Nov'       => 'Kas',
		'Dec'       => 'Ara',
	);

	foreach($gun_dizi as $en => $tr)
		$z = str_replace($en, $tr, $z);

	if (strpos($z, 'Mayıs') !== false && strpos($format, 'F') === false)
		$z = str_replace('Mayıs', 'May', $z);

	return $z;
}

function object2array($object) {
	return @json_decode(@json_encode($object), 1);
}

function xml_to_array($xml) {
    return json_decode(str_replace('{}', '""', json_encode(simplexml_load_string($xml))), TRUE);
}

function seflink($text)
{
	$find = array('Ç', 'Ş', 'Ğ', 'Ü', 'İ', 'Ö', 'ç', 'ş', 'ğ', 'ü', 'ö', 'ı', '+', '#');
	$replace = array('c', 's', 'g', 'u', 'i', 'o', 'c', 's', 'g', 'u', 'o', 'i', 'plus', 'sharp');
	$text = strtolower(str_replace($find, $replace, $text));
	$text = preg_replace("@[^A-Za-z0-9\-_\.\+]@i", ' ', $text);
	$text = trim(preg_replace('/\s+/', '', $text));
	return $text;
}

function security($data) {
	$data = trim($data);
	$data = strip_tags($data, '<br>');
	$data = stripslashes($data);
	return $data;
}

require_once(__ROOT__ . '/inc/facebook/autoload.php');

$user = array();
$loginUrl = "";
$logoutUrl = "./logout.php";

if (isset($_SESSION['user'])) {
	$user = $_SESSION['user'];
}
else {

	$fb = new Facebook\Facebook([
		'app_id' => FB_APP_ID,
		'app_secret' => FB_APP_SECRET,
		'default_graph_version' => 'v5.0',
	]);

	$helper = $fb->getRedirectLoginHelper();

	$permissions = ['email'];
	$loginUrl = $helper->getLoginUrl(SITE_URL . '/fb-callback.php', $permissions);

	$loginUrl = htmlspecialchars($loginUrl);
}

$superAdmins = array(10617, 10633, 49);
$superAdmin = 0;

if (isset($user['id'])) {
	$superAdmin = (array_search($user['id'], $superAdmins) !== false);
}