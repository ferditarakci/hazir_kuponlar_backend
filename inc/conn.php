<?php

function openDB() {

	$db_user = "tahminkrali2";
	$db_pass = "IEL02EnAyUt";
	$db_name = "tahminkr_maclar";
	$db_host = "localhost";

	try {
		$conn = new PDO('mysql:host=' . $db_host . ';dbname=' . $db_name . ';charset=utf8', $db_user, $db_pass, array(
			PDO::ATTR_TIMEOUT => 360
		));
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$conn->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES utf8");
		return $conn;
	}
	catch (PDOException $ex) {
		var_dump($ex->getCode());
		var_dump($ex->getMessage());
		var_dump($ex->errorInfo);
	}
}

function handle_sql_errors($query, $error_message) {
	echo '<pre>';
	echo $query;
	echo '</pre>';
	echo $error_message;
	die;
}

function upper($string, $country) {
	if ($country == 'Türkiye') {
		$dizi = array('i' => 'İ', 'ı' => 'I', 'ü' => 'Ü', 'ğ' => 'Ğ', 'ü' => 'Ü', 'ç' => 'Ç', 'ö' => 'Ö', 'ş' => 'Ş');
		return mb_strtoupper(strtr($string, $dizi), "UTF-8");
	} else {
		return mb_strtoupper($string, "UTF-8");
	}
}

function getCoupon($betgroup) {
	global $today, $time, $now, $startOfDay, $couponDayLimit;
	$db = openDB();
	$coupon = array();

	$sqlEk = " AND c.coupon_time >= {$time} AND c.coupon_type = {$betgroup}";

	$startTime = ($time - (60 * 60 * 24 * 30));

	if ($betgroup == TUTAN_KUPON) {
		$sqlEk = " AND c.result = 1 AND c.coupon_time >= " . $startTime;
	}

	if ($betgroup == TUTAN_KUPON and isset($_GET["type"])) {
		if ($_GET["type"] == "tutmayan") {
			$sqlEk = " AND c.result = 2 AND c.coupon_time >= " . $startTime;
		} elseif ($_GET["type"] == "hepsi") {
			$sqlEk = " AND c.coupon_time >= " . $startTime;
		}
	}

	$query = "SELECT
			c.id as cid, cb.id As cbid, cb.game_id, cb.sequence, c.coupon_date, c.coupon_time, c.coupon_type, c.result,
			ub.id as bet_id, g.date, g.datetime, g.leag, g.code AS game_code, g.title, g.link_name AS game_link, g.puid AS game_id,
			bg.tr_code AS bg_code, bt.description AS bt_code, ub.rate, ub.id AS userbets_id, l.link_name AS league_link,
			l.name AS league_name, l.country, g.has_comment
			FROM tk_coupons c
			JOIN tk_couponbets cb ON cb.coupon_id = c.id
			JOIN tk_userbets ub ON cb.game_id = ub.game_id
			JOIN tk_games g ON ub.game_id = g.puid
			JOIN tk_bettypes bt ON ub.bettype_id = bt.id
			JOIN tk_betgroups bg ON ub.betgroup_id = bg.id
			JOIN tk_leagues l ON g.leag = l.code
			WHERE 
			ub.id = cb.bet_id
            AND ub.game_id = cb.game_id
			{$sqlEk}

			GROUP BY cb.id
			ORDER BY c.id DESC, cb.sequence ASC, ub.probability DESC
			";

	$rs = $db->query($query);
	$result = $rs->fetchAll(PDO::FETCH_ASSOC);
	$count = 0;

	foreach ($result as $row) {
		$cid = $row["cid"];

		$coupon[$cid]["coupon_id"] = $cid;
		$coupon[$cid]["coupon_date"] = turkishDate('d/m/Y', strtotime($row["coupon_date"]));
		$coupon[$cid]["coupon_time"] = turkishDate('d F Y, H:i', $row["coupon_time"]);
		$coupon[$cid]["coupon_type"] = $row["coupon_type"];
		$coupon[$cid]["result"] = $row["result"];
		$coupon[$cid]["coupon_games"][$count]["game_date"] = date('d.m', $row["datetime"]);
		$coupon[$cid]["coupon_games"][$count]["game_date2"] = turkishDate('d F Y, H:i', $row["datetime"]);
		$coupon[$cid]["coupon_games"][$count]["game_datetime"] = $row["datetime"];
		$coupon[$cid]["coupon_games"][$count]["game_code"] = $row["game_code"];
		$coupon[$cid]["coupon_games"][$count]["league"] = $row["leag"];
		$coupon[$cid]["coupon_games"][$count]["league_link"] = $row["league_link"];
		$coupon[$cid]["coupon_games"][$count]["league_name"] = upper($row["league_name"], $row["country"]);
		$coupon[$cid]["coupon_games"][$count]["game_title"] = upper($row["title"], $row["country"]);
		$coupon[$cid]["coupon_games"][$count]["title_url"] = substr($row["date"], 8, 2) . "-" . substr($row["date"], 5, 2) . "-" . substr($row["date"], 0, 4) . "-" . $row["game_code"] . "-" . $row["game_link"] . "-" . $row["game_id"];
		$coupon[$cid]["coupon_games"][$count]["bg_code"] = $row["bg_code"];
		$coupon[$cid]["coupon_games"][$count]["bt_code"] = $row["bt_code"];
		$coupon[$cid]["coupon_games"][$count]["rate"] = $row["rate"];
		$coupon[$cid]["coupon_games"][$count]["has_comment"] = $row["has_comment"];

		$count++;
	}

	return $coupon;
}


function bg_code($a) {
	$arr = array(
		"2,5" => "AÜ",
		"1,5" => "1,5 AÜ",
		"3,5" => "3,5 AÜ",
		"İY 1,5" => "İY 1,5 AÜ",
	);

	$b = array_search($a, $arr);
	if ($b) $a = $b;

	return $a;
}



$total_count = 0;
function html_coupon($coupon_id, $date, $coupon_time, $title, $closesttime, $body, $coupon_rate, $grid) {
	global $superAdmin, $total_count;

	$notStarted = "";
	if ($closesttime > time()) $notStarted = "<div class=\"not-started\">Henüz başlamadı</div>";

	$html = "
	<div class=\"grid_6 {$grid}\">

	<div class=\"bets-table\" data-coupon_id=\"{$coupon_id}\" data-endtime=\"{$coupon_time}\">
		<div class=\"bets-header\">
			<div class=\"col\">
				<h2 class=\"bets-table-title\">{$title} <span style=\"font-weight: 400; opacity: 0.6\">#{$coupon_id}</span></h2>
				<div class=\"rc\">
					{$notStarted}
					<div class=\"last-update\">
					{$date}
					</div>
				</div>
			</div>
		</div>
		<div class=\"bets-body-header\">
			<div class=\"col c1\">
				<div class=\"col-inner\">
					<div class=\"col-title\">TARİH</div>
				</div>
			</div>
			<div class=\"col c2\">
				<div class=\"col-inner\">
					<div class=\"col-title\">LİG</div>
				</div>
			</div>
			<div class=\"col c3\">
				<div class=\"col-inner\">
					<div class=\"col-title\">KOD</div>
				</div>
			</div>
			<div class=\"col c4 textleft\">
				<div class=\"col-inner\">
					<div class=\"col-title\">MAÇ</div>
				</div>
			</div>
			<div class=\"col c5\">
				<div class=\"col-inner\">
					<div class=\"col-title\">TAHMİN</div>
				</div>
			</div>
			<div class=\"col c6\">
				<div class=\"col-inner\">
					<div class=\"col-title\">ORAN</div>
				</div>
			</div>
		</div>

		<div class=\"bets-body\">
			{$body}
		</div>
		<div class=\"bets-footer\">
			<div class=\"col c1\">

			</div>
			<div class=\"col c2\">
				<div class=\"col-inner\">
					<span class=\"total\">
						TOPLAM ORAN:
						<span>{$coupon_rate}</span>
					</span>
				</div>
			</div>
		</div>
		";

	// COMMENTS
	$query = "
		SELECT
			c.user_id, c.comment, u.first_name, u.last_name
		FROM tk_comments c
			JOIN tk_users u ON c.user_id = u.id
		WHERE c.result = 1
			AND c.parent_id = 0
			AND c.coupon_id = :coupon_id
		ORDER BY c.id ASC
		LIMIT 3
	";

	$db = openDB();
	$rs = $db->prepare($query);
	$rs->bindValue(':coupon_id', $coupon_id, PDO::PARAM_INT);
	$rs->execute();

	$result = $rs->fetchAll(PDO::FETCH_ASSOC);

	$count = 0;
	$comment_1 = "";
	$comment_2 = "";
	foreach ($result as $row) {
		$user_name = trim($row["first_name"] . " " . mb_substr($row["last_name"], 0, 1)) . ".";
		if ($superAdmin) $user_name .= ' <i>#' . $row["user_id"] . '</i>';
		$comment = $row["comment"];

		if ($count == 0) {
			$comment_1 .= "
						<div>
							<h3 class=\"user\">{$user_name}</h3>
							<p class=\"comment\">{$comment}</p>
						</div>
			";
		} elseif ($count > 0) {
			$comment_2 = "
						<div>
							<h3 class=\"user\">{$user_name}</h3>
							<p class=\"comment\">{$comment}</p>
						</div>
			" . $comment_2;
		}

		$count++;
	}

	if ($grid == "grid_a") {
		$total_count = $count;
	} elseif ($grid == "grid_b") {
		$total_count = 0;
	}

	$html .= "
		<div class=\"bets-comments multi\">
			<div class=\"col\">
				<div class=\"comments\">
					{$comment_2}
				</div>
			</div>
		</div>
	";

	$html .= "
		<div class=\"bets-comments single" . (empty($comment_1) ? " nocomment" : "") . "\">
			<div class=\"col c1\">
				<div class=\"comments\">
					" . (!empty($comment_1) ? $comment_1 : "<p class=\"comment\">Bu kupona ilk yorumu sen yap!</p>") . "
				</div>
			</div>
			<div class=\"col c2\">
				<a class=\"open-comments\" data-coupon_id=\"{$coupon_id}\">YORUM YAP</a>
			</div>
		</div>
	</div>
	";

	$html .= "</div>";
	return $html;
}


function html_row($arr) {
	$arr = "
			<div class=\"row\">
				<div class=\"col c1\"><div class=\"col-inner\" title=\"{$arr["game_date2"]}\">{$arr["game_date"]}</div></div>
				<div class=\"col c2\"><div class=\"col-inner\">{$arr["leag_url"]}</div></div>
				<div class=\"col c3\"><div class=\"col-inner\">{$arr["game_code"]}</div></div>
				<div class=\"col c4\"><div class=\"col-inner col-title textleft\">{$arr["game_url"]}</div></div>
				<div class=\"col c5\"><div class=\"col-inner\">{$arr["bg_code"]} {$arr["bt_code"]}</div></div>
				<div class=\"col c6\"><div class=\"col-inner\">{$arr["rate"]}</div></div>
			</div>
	";
	return $arr;
}