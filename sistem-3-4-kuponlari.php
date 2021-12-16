<?php

require_once('inc/globals.php');

$dayTitle = DateDayTitle();

$site['title'] = "Sistem 3-4 Kuponları {$dayTitle} - Sistem 3-4 İddaa Kuponu";
$site['description'] = "";
$site['url_path'] = "/sistem-3-4-kuponlari";
$site['table_title'] = "SİSTEM 3-4 KUPONLARI";

$result = getCoupon(SISTEM_3_4_KUPON);

$count = 0;
$coupon_table = "";

foreach ($result as $row) {

	$closesttime = 0;

	$coupon_rate = 1;
	$coupon_row = "";

	if (count($row["coupon_games"]) < 4) continue;

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

require_once('inc/footer.php');

?>