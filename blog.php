<?php

require_once('inc/globals.php');

$site['title'] = "Blog Yazıları";
$site['description'] = "";
$site['url_path'] = "/blog";

$site['title'] .= " - " . SITE_TITLE;

require_once('inc/header.php');

?>

<section id="pages" class="sub-page blog-list clearfix">
	<?php

	$db = openDB();

	$rs = $db->query("SELECT * FROM tk_blog WHERE type = 2 ORDER BY id DESC");

	$result = $rs->fetchAll(PDO::FETCH_ASSOC);

	foreach ($result as $row) {

		$blog["subject"] = $row["subject"];
		$blog["date"] = date("d.m.Y H:i", $row["date"]);
		$blog["link_name"] = $row["link_name"];
		$row["story"] = substr($row["story"], 0, 300);
		$blog["story"] = str_replace("\r\n", "<br>", $row["story"]);
		$blog["story"] = str_replace("\n", "<br>", $blog["story"]);
		$blog["story"] = str_replace("\r", "<br>", $blog["story"]);
	?>

		<div class="container">
			<h2 class="page-title"><a href="<?php echo SITE_URL ?>/blog/<?= $blog["link_name"] ?>"><?= $blog["subject"] . " / " . $blog["date"] ?></a></h2>
			<article class="page-text">
				<p><?= $blog["story"] ?>...</p>
				<a href="<?php echo SITE_URL ?>/blog/<?= $blog["link_name"] ?>" class="readmore">Devamı...</a>
			</article>
		</div>
	<?php

	}

	?>
</section>
<!-- // #pages -->

<?php

require_once('inc/footer.php');

?>