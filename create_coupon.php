<?php

require_once('inc/globals.php');

$getType = 0;

if (isset($_GET['type'])) {
	if ($_GET['type'] == "1") $getType = 1;
}

if ($getType == 1) $br = "<br>";
else $br = "\n";

$arr = array();


// BANKO KUPONLAR
$arr[] = array('couponType' => 1, 'rateType' => 1, 'limit' => 3, 'empty' => 1);
$arr[] = array('couponType' => 1, 'rateType' => 2, 'limit' => 4, 'empty' => 0);

$arr[] = array('couponType' => 1, 'rateType' => 1, 'limit' => 3, 'empty' => 2);
$arr[] = array('couponType' => 1, 'rateType' => 2, 'limit' => 4, 'empty' => 0);


// GOLCU KUPONLAR
$arr[] = array('couponType' => 2, 'rateType' => 1, 'limit' => 1, 'empty' => 1);
$arr[] = array('couponType' => 2, 'rateType' => 2, 'limit' => 4, 'empty' => 0);

$arr[] = array('couponType' => 2, 'rateType' => 1, 'limit' => 1, 'empty' => 2);
$arr[] = array('couponType' => 2, 'rateType' => 2, 'limit' => 4, 'empty' => 0);


// SURPRIZ KUPONLAR
$arr[] = array('couponType' => 3, 'rateType' => 3, 'limit' => 3, 'empty' => 1);
$arr[] = array('couponType' => 3, 'rateType' => 4, 'limit' => 4, 'empty' => 0);

$arr[] = array('couponType' => 3, 'rateType' => 3, 'limit' => 3, 'empty' => 2);
$arr[] = array('couponType' => 3, 'rateType' => 4, 'limit' => 4, 'empty' => 0);


// SISTEM 3-4 KUPONLAR
$arr[] = array('couponType' => 4, 'rateType' => 4, 'limit' => 2, 'empty' => 1);
$arr[] = array('couponType' => 4, 'rateType' => 5, 'limit' => 4, 'empty' => 0);

$arr[] = array('couponType' => 4, 'rateType' => 4, 'limit' => 2, 'empty' => 2);
$arr[] = array('couponType' => 4, 'rateType' => 5, 'limit' => 4, 'empty' => 0);


// SISTEM 4-5-6 KUPONLAR
$arr[] = array('couponType' => 5, 'rateType' => 3, 'limit' => 2, 'empty' => 1);
$arr[] = array('couponType' => 5, 'rateType' => 4, 'limit' => 4, 'empty' => 0);
$arr[] = array('couponType' => 5, 'rateType' => 5, 'limit' => 6, 'empty' => 0);

$arr[] = array('couponType' => 5, 'rateType' => 3, 'limit' => 2, 'empty' => 2);
$arr[] = array('couponType' => 5, 'rateType' => 4, 'limit' => 5, 'empty' => 0);
$arr[] = array('couponType' => 5, 'rateType' => 5, 'limit' => 6, 'empty' => 0);


$db = openDB();

$notGameCode = array();
$queryAll = "";
$couponID = 0;


foreach ($arr as $vars) {

	if ($vars["empty"] > 0) {
		$queryAll = "";
		$couponID = 0;
		$i = 0;
	}

	if ($vars["empty"] == 1) {
		$couponType = 0;
		$notGameCode = array();
	}

	if ($vars["empty"] == 0) $vars["limit"] = $vars["limit"] - $i;

	$groupTitle = "";
	$betgroup_id = "";

	if ($vars["couponType"] == 1) {
		$groupTitle = "BANKO KUPONLAR________$br";
	} elseif ($vars["couponType"] == 2) {
		$groupTitle = "GOLCU KUPONLAR________$br";
		$betgroup_id = "2, 7, 8, 11";
	} elseif ($vars["couponType"] == 3) {
		$groupTitle = "SURPRIZ KUPONLAR________$br";
		$betgroup_id = "1, 10";
	} elseif ($vars["couponType"] == 4) {
		$groupTitle = "SISTEM 3-4 KUPONLAR________$br";
		$betgroup_id = "10";
	} elseif ($vars["couponType"] == 5) {
		$groupTitle = "SISTEM 4-5-6 KUPONLAR________$br";
		$betgroup_id = "1, 10";
	}

	$query = "SELECT id FROM tk_coupons WHERE coupon_date = '{$today}' AND coupon_type = {$vars["couponType"]} ORDER BY id ASC";
	$rs = $db->query($query);
	$coupons = $rs->fetchAll(PDO::FETCH_FUNC, function ($id) {
		return $id;
	});
	$couponCount = $rs->rowCount();

	$couponType = $vars["couponType"];

	$query = "
		SELECT cb.bet_id
		FROM tk_coupons c
		JOIN tk_couponbets cb ON cb.coupon_id = c.id
		WHERE c.result = 0
		AND c.coupon_time >= {$now}
		AND c.coupon_time < {$startOfDay}
		AND c.coupon_type = {$vars["couponType"]}
	";

	$coupon_ids = $db->query($query)->fetchAll(PDO::FETCH_FUNC, function ($bet_id) {
		return $bet_id;
	});
	$coupon_ids = implode(', ', $coupon_ids);

	$query = "
		SELECT ub.id as bet_id, g.date, g.leag, g.code AS game_code, g.title, g.link_name AS game_link, g.puid AS game_id,
		bg.tr_code AS bg_code, bt.description AS bt_code, ub.rate, ub.id AS userbets_id, l.link_name AS league_link,
		l.name AS league_name, g.has_comment
		FROM tk_userbets ub
		JOIN tk_games g ON ub.game_id = g.puid
		#JOIN tk_coupons c ON c.coupon_type = 2
		#JOIN tk_couponbets cb ON cb.coupon_id = c.id
		JOIN tk_bettypes bt ON ub.bettype_id = bt.id
		JOIN tk_betgroups bg ON ub.betgroup_id = bg.id
		JOIN tk_leagues l ON g.leag = l.code
		WHERE ub.result = 0
		AND g.datetime >= {$now}
		AND g.datetime < {$startOfDay}
		AND ub.ratetype_id = {$vars["rateType"]}
		#AND ub.id != cb.bet_id
		#AND ub.game_id != cb.game_id
		#AND NOT ub.probability = 0
		AND ub.id NOT IN ((SELECT cb.bet_id FROM tk_couponbets cb WHERE cb.game_id = ub.game_id AND cb.bet_id = ub.id))
	";

	if (!empty($betgroup_id)) $query .= "\n	AND bg.id IN (" . $betgroup_id . ")";

	if (!empty($coupon_ids) and $vars["empty"] != 1) $query .= "\n	AND ub.id NOT IN ({$coupon_ids})";
	if (count($notGameCode) and $vars["empty"] == 0) $query .= "\n	AND g.code NOT IN (" . implode(', ', array_unique($notGameCode)) . ")";

	$query .= "\n	GROUP BY g.code";
	$query .= "\n ORDER BY ub.probability DESC, ub.rate DESC";
	if (isset($vars["limit"])) $query .= "\n	LIMIT " . $vars["limit"];
	$query .= ";";
	print_r("<pre>$query\n\n</pre>");
	print_r("\n\n");

	$querystr = $query;

	$rs = $db->query($query);
	$result = $rs->fetchAll(PDO::FETCH_ASSOC);

	$resultCount = $rs->rowCount();

	print_r("$resultCount$br$br");

	$coupon = array();

	if ($resultCount > 0 and $couponID == 0) {
		$st = $db->prepare("INSERT INTO tk_coupons (league_id, coupon_date, coupon_time, shown_until, coupon_type, coupon_rate, result) VALUES (0, :coupon_date, :coupon_time, :shown_until, :coupon_type, :coupon_rate, :result)");
		$st->bindValue(':coupon_date', $today, PDO::PARAM_STR);
		$st->bindValue(':coupon_time', $startOfDay, PDO::PARAM_INT);
		$st->bindValue(':shown_until', 0, PDO::PARAM_INT);
		$st->bindValue(':coupon_type', $vars["couponType"], PDO::PARAM_INT);
		$st->bindValue(':coupon_rate', 0, PDO::PARAM_STR);
		$st->bindValue(':result', 0, PDO::PARAM_INT);
		$st->execute();
		$couponID = $db->lastInsertId();
		print_r("$groupTitle #$couponID nolu kupon eklendi.$br");
	}

	print_r("<pre>$querystr\n\n</pre>");
	print_r("\n\n");

	foreach ($result as $row) {
		$notGameCode[] = $row["game_id"];
		$i++;

		$st = $db->prepare("INSERT INTO tk_couponbets (coupon_id, bet_id, game_id, sequence) VALUES (:coupon_id, :bet_id, :game_id, :sequence)");
		$st->bindValue(':coupon_id', $couponID, PDO::PARAM_INT);
		$st->bindValue(':bet_id', $row["bet_id"], PDO::PARAM_INT);
		$st->bindValue(':game_id', $row["game_id"], PDO::PARAM_INT);
		$st->bindValue(':sequence', $i, PDO::PARAM_INT);
		$st->execute();
	}
} // foreach end


$query = "SELECT
	c.id,
	IFNULL((SELECT COUNT(cb.id) FROM tk_couponbets cb WHERE cb.coupon_id = c.id), 0) as allCounter,
	IFNULL((SELECT COUNT(cb.id) FROM tk_couponbets cb JOIN tk_userbets ub ON cb.bet_id = ub.id WHERE cb.coupon_id = c.id AND ub.result = 1 GROUP BY cb.coupon_id), 0) as keptCounter,
	IFNULL((SELECT COUNT(cb.id) FROM tk_couponbets cb JOIN tk_userbets ub ON cb.bet_id = ub.id WHERE cb.coupon_id = c.id AND ub.result = 2 GROUP BY cb.coupon_id), 0) as notKeptCounter
	FROM tk_coupons c
	WHERE c.result = 0
	#AND c.coupon_time >= 1583442000
";

$rs = $db->query($query);
$result = $rs->fetchAll(PDO::FETCH_ASSOC);

foreach ($result as $row) {
	if ($row['allCounter'] == $row['keptCounter']) {
		$st = $db->prepare("UPDATE tk_coupons SET result=1 WHERE id=:cid");
		$st->bindValue(':cid', $row['id'], PDO::PARAM_INT);
		$st->execute();
	} elseif ($row['allCounter'] == ($row['keptCounter'] + $row['notKeptCounter'])) {
		$st = $db->prepare("UPDATE tk_coupons SET result=2 WHERE id=:cid");
		$st->bindValue(':cid', $row['id'], PDO::PARAM_INT);
		$st->execute();
	}
}