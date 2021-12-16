<?php

require_once('inc/globals.php');

$dayTitle = DateDayTitle();

// if ($superAdmin):
// 	$site['title'] = "İddaa Tahminleri ve Banko Kuponlar {$dayTitle} - Günün Banko Kuponu";
// else:
// 	$site['title'] = "Sürpriz Kuponlar";
// 	$site['title'] .= " - " . SITE_TITLE;
// endif;

$site['title'] = "Sürpriz Kuponlar {$dayTitle} - Sürpriz İddaa Kuponu";

$site['description'] = "";
$site['url_path'] = "/surpriz-kuponlar";
$site['table_title'] = "SÜRPRİZ KUPONLAR";

// print_r($now . " - " . $startOfDay);

$result = getCoupon(SURPRIZ_KUPON);
// print_r($result); exit;
$count = 0;
$coupon_table = "";

foreach($result as $row) {

	$closesttime = 0;

	$coupon_rate = 1;
	$coupon_row = "";

	if (count($row["coupon_games"]) < 3) continue;

	foreach($row["coupon_games"] as $srow) {
		$srow["bg_code"] = bg_code($srow["bg_code"]);
		// print_r($result);
		// if ($srow["has_comment"] == 0)
		// 	$game_url = $srow["game_title"];
		// else
		// 	$game_url = "<a href=\"/iddaa/" . $srow["title_url"] . "\">" . $srow["game_title"] . "</a>";
		
		// print_r($closesttime . "\n");
		if ($closesttime > $srow["game_datetime"] OR $closesttime == 0) $closesttime = $srow["game_datetime"];

		$srow["game_url"] = $srow["game_title"];

		//$leag_url = "<a href=\"/lig/{$srow["league_link"]}/guncel-tahminler\" title=\"{$srow["league_name"]}\">{$srow["league"]}</a>";
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
// print_r($coupon_table);






require_once('inc/header.php');

?>




					<section id="contents" class="clearfix">
						<div class="container_12">
<?php echo $coupon_table ?>

							<!-- <div class="grid_12 bets-btns">
								<a href="#">Banko kuponları görmek için tıklayınız</a>
							</div> -->

							<!-- <div class="grid_12 bets-note">
								* Kuponlardaki maçlar başladığında otomatik olarak henüz başlamamış maçlar ile kuponlarımız güncellenmektedir.
							</div> -->

						</div>
					</section>
					<!-- // #contents -->




<?php


require_once('inc/footer.php');


?>