<?php

if (!isset($_POST) || !isset($_POST['ajax'])) {
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: ./");
	exit();
}

ob_start();
session_start();
ini_set('display_error', 0);
date_default_timezone_set('Europe/Istanbul');

require_once('inc/form/mail/class.phpmailer.php');

$cform["txtFirstName"]		= get_magic_quotes_gpc() == false ? addslashes(htmlspecialchars($_POST['ad'])) : htmlspecialchars($_POST['ad']);
$cform["txtLastName"]		= get_magic_quotes_gpc() == false ? addslashes(htmlspecialchars($_POST['soyad'])) : htmlspecialchars($_POST['soyad']);
$cform["txtEmailAddress"]	= get_magic_quotes_gpc() == false ? addslashes(htmlspecialchars($_POST['eposta'])) : htmlspecialchars($_POST['eposta']);
$cform["txtPhone"]			= get_magic_quotes_gpc() == false ? addslashes(htmlspecialchars($_POST['tel'])) : htmlspecialchars($_POST['tel']);
$cform["txtMessage"]		= get_magic_quotes_gpc() == false ? addslashes(htmlspecialchars($_POST['mesaj'])) : htmlspecialchars($_POST['mesaj']);

if ($cform["txtFirstName"] == "") {
	$error['txtFirstName'] = "Lütfen adınızı giriniz.";
}
if ($cform["txtLastName"] == "") {
	$error['txtLastName'] = "Lütfen soyadınızı giriniz.";
}
if ($cform["txtEmailAddress"] == "") {
	$error['txtEmailAddress'] = "Lütfen email adresinzi giriniz.";
}
if ($cform["txtPhone"] == "") {
	$error['txtPhone'] = "Lütfen telefon numaranızı.";
}
if ($cform["txtMessage"] == "") {
	$error['txtMessage'] = "Lütfen iletmek istediğiniz mesajı yazınız..";
}

if (!filter_var($cform["txtEmailAddress"], FILTER_VALIDATE_EMAIL)) {
	$error['txtEmailAddress'] = "Email adresiniz uygun formatta değildir. Lütfen kontrol edin.";
}

if (sizeof($error) > 0) {
	$json['status'] = false;
	$json['error'] = $error;
	$json['message'] = "Lütfen tüm alanları doğru ve eksiksiz bir şekilde doldurun.";
	echo json_encode($json);
	exit;
}

try {

	$contents = "<b>İsim : </b>" . $cform["txtFirstName"] . "<br>\r\n";
	$contents .= "<b>Soyisim : </b>" . $cform["txtLastName"] . "<br>\r\n";
	$contents .= "<b>Telefon : </b>" . $cform["txtPhone"] . "<br>\r\n";
	$contents .= "<b>Email : </b>" . $cform["txtEmailAddress"] . "<br>\r\n";
	$contents .= "<b>Mesaj : </b>" . $cform["txtMessage"] . "<br>\r\n";

	$mail = new PHPMailer(true);
	$mail->IsSMTP();
	$mail->SMTPAuth = true;
	$mail->Host = 'smtp.hazirkuponlar.net';
	$mail->Port = 465;
	$mail->SMTPSecure = 'ssl';
	$mail->Username = 'info@hazirkuponlar.net';
	$mail->Password = 'Password';
	$mail->SetFrom("info@hazirkuponlar.net");
	$mail->CharSet = 'UTF-8';
	$mail->AddAddress("info@hazirkuponlar.net");
	$mail->AddReplyTo($cform["txtEmailAddress"]);
	$mail->Subject = 'HazırKuponlar İletişim Formu';
	$mail->MsgHTML($contents);
	$mail->Send();

	$json['status'] = true;
	$json['message'] = "Mailiniz ekibimize ulaşmıştır. En kısa sürede dönüş yapılacaktır.";
} catch (phpmailerException $e) {
	$json['status'] = false;
	$json['message'] = "Mail gönderiminde bir hata oluştu. Bilgilerinizi kontrol edip tekrar deneyin.";

} catch (Exception $e) {
	$json['status'] = false;
	$json['message'] = "Mail gönderiminde bir hata oluştu. Bilgilerinizi kontrol edip tekrar deneyin.";
}

echo json_encode($json);
