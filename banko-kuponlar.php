<?php

require_once('inc/globals.php');

$dayTitle = DateDayTitle();

$site['title'] = "Banko Kuponlar {$dayTitle} - Banko İddaa Kuponu";
$site['description'] = "";
// $site['url_path'] = "/banko-kuponlar";
$site['url_path'] = "/";
$site['table_title'] = "BANKO KUPONLAR";

$result = getCoupon(BANKO_KUPON);

$count = 0;
$coupon_table = "";

foreach ($result as $row) {

	$closesttime = 0;

	$coupon_rate = 1;
	$coupon_row = "";

	if (count($row["coupon_games"]) < 3) continue;

	foreach ($row["coupon_games"] as $srow) {
		$srow["bg_code"] = bg_code($srow["bg_code"]);

		if ($closesttime > $srow["game_datetime"] or $closesttime == 0) $closesttime = $srow["game_datetime"];

		$srow["game_url"] = $srow["game_title"];

		$srow["leag_url"] = "<span title=\"{$srow["league_name"]}\">{$srow["league"]}</span>";

		$coupon_row .= html_row($srow);

		$coupon_rate = $coupon_rate * $srow["rate"];
	}

	$grid = "grid_a";

	if (($count % 2) == 1) $grid = "grid_b";

	$count++;

	$coupon_rate = number_format($coupon_rate, 2, '.', '');

	if ($coupon_row == "") $coupon_rate = 0;

	$coupon_table .= html_coupon($row["coupon_id"], $row["coupon_date"], $row["coupon_time"], $site["table_title"], $closesttime, $coupon_row, $coupon_rate, $grid);
}

require_once('inc/header.php');

?>

<section id="contents" class="clearfix">
	<div class="container_12">
		<?php echo $coupon_table ?>
	</div>
</section>
<!-- // #contents -->

<?php
if ($superAdmin) :
?>
	<section id="pages" class="sub-page blog-list clearfix">
		<?php

		$db = openDB();

		$rs = $db->query("SELECT * FROM tk_blog WHERE type = 2 ORDER BY id DESC LIMIT 1");

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
		<?php } ?>
		<div class="container">
			<h2 class="page-title"><a href="blog_detay.html">Lorem ipsum dolor sit amet</a></h2>
			<article class="page-text">
				<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt . Ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
				<p>Ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt . Ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
			</article>
		</div>
	</section>
	<!-- // #pages -->
<?php

endif;

require_once('inc/footer.php');

?>