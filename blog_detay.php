<?php

require_once('inc/globals.php');

$link_name = get_magic_quotes_gpc() == false ? addslashes(htmlspecialchars($_GET['link_name'])) : htmlspecialchars($_GET['link_name']);

$db = openDB();

$rs = $db->query("SELECT * FROM tk_blog WHERE type = 2 AND link_name = '" . $link_name . "';");

$result = $rs->fetch(PDO::FETCH_ASSOC);

$blog["subject"] = $result["subject"];
$blog["date"] = date("d.m.Y H:i", $result["date"]);
$blog["link_name"] = $result["link_name"];
$blog["story"] = $result["story"];
$blog["story"] = str_replace("\r\n", "<br>", $blog["story"]);
$blog["story"] = str_replace("\n", "<br>", $blog["story"]);
$blog["story"] = str_replace("\r", "<br>", $blog["story"]);

$site['title'] = $blog["subject"];
$site['description'] = "";
$site['url_path'] = "/blog" . "/" . $blog["link_name"];

$site['title'] .= " - " . SITE_TITLE;

require_once('inc/header.php');

?>

<section id="pages" class="sub-page clearfix">
	<div class="container">
		<h2 class="page-title"><a href="<?php echo SITE_URL ?>/blog/<?= $blog["link_name"] ?>"><?= $blog["subject"] . " / " . $blog["date"] ?></a></h2>
		<article class="page-text">
			<p><?= htmlspecialchars_decode($blog["story"]) ?></p>
		</article>
	</div>
</section>
<!-- // #pages -->

<?php

require_once('inc/footer.php');

?>