<?php

require_once('inc/globals.php');

$site['title'] = "İletişim Bilgileri";
$site['description'] = "";
$site['url_path'] = "/iletisim";

$site['title'] .= " - " . SITE_TITLE;

require_once('inc/header.php');

?>

<section id="pages" class="sub-page contact clearfix">
	<div class="container">
		<h1 class="page-title green">İletişim</h1>
		<div class="page-text form">
			<form action="iletisim_mailsend.php" method="post" class="ftAjaxForm">
				<div class="label-div a">
					<label>Adınız <i>*</i></label>
					<div><input name="ad" type="text" /></div>
				</div>
				<div class="label-div b">
					<label>Soyadınız <i>*</i></label>
					<div><input name="soyad" type="text" /></div>
				</div>
				<div class="label-div a">
					<label>Telefon Numaranız</label>
					<div><input name="tel" type="text" /></div>
				</div>
				<div class="label-div b">
					<label>E-Posta Adresiniz <i>*</i></label>
					<div><input name="eposta" type="text" /></div>
				</div>
				<div class="label-div">
					<label>Mesajınız <i>*</i></label>
					<div><textarea name="mesaj" rows="10" cols="20"></textarea></div>
				</div>
				<div class="clr"></div>
				<button class="submit-button" type="submit">
					<span>GÖNDER</span>
				</button>
				<div class="clr"></div>
			</form>
			<div class="clr"></div>
		</div>
	</div>
</section>
<!-- // #pages -->

<?php

require_once('inc/footer.php');

?>