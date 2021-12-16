<?php

date_default_timezone_set('Europe/Istanbul');
require_once('MySQLClass.php');

class gamesClass extends mysqlClass
{
	private $timeOver = 2400, //zaman aşımı süresi(saniye türünden 15dk)
		$loginAtAnotherPC = false,
		$username, $password, $userid, $userType, $uemail, $typeEnd, $sesstime = 0, $suspended = 0, $sid = -1, $sessionId, $actionTime = 0, $currentChips = 0, $totalChips = 0, $userip, $browserHash, $oneIp, $tempPass; //session bilgileri
	public $fileName = '';
	public $betLimit;
	private $myTime, $dayStart, $monthStart;

	public function getBlog($link_name)
	{
		$blog_result = $this->query("select * from tk_blog where link_name='" . $link_name . "'");
		$blog_rs = $this->fetchRow($blog_result, MYSQL_ASSOC);
		$blog_rs["date"] = date("d.m.Y H:i", $blog_rs["date"]);
		$blog_rs["story"] = str_replace("\r\n", "<br>", $blog_rs["story"]);
		$blog_rs["story"] = str_replace("\n", "<br>", $blog_rs["story"]);
		$blog_rs["story"] = str_replace("\r", "<br>", $blog_rs["story"]);
		return $blog_rs;
	}

	public function listBlog()
	{
		$blog_result = $this->query("select * from tk_blog order by id desc");
		$i = 0;
		while ($blog_row = $this->fetchRow($blog_result, MYSQL_ASSOC)) {
			$blog[$i]["subject"] = $blog_row["subject"];
			$blog[$i]["date"] = date("d.m.Y H:i", $blog_row["date"]);
			$blog[$i]["link_name"] = $blog_row["link_name"];
			$blog_row["story"] = substr($blog_row["story"], 0, 300);
			$blog[$i]["story"] = str_replace("\r\n", "<br>", $blog_row["story"]);
			$blog[$i]["story"] = str_replace("\n", "<br>", $blog[$i]["story"]);
			$blog[$i]["story"] = str_replace("\r", "<br>", $blog[$i]["story"]);

			$i++;
		}
		return $blog;
	}

	public function lastBlog()
	{
		$blog_result = $this->query("select * from tk_blog order by id desc limit 1");
		$blog_rs = $this->fetchRow($blog_result, MYSQL_ASSOC);
		$blog_rs["date"] = date("d.m.Y H:i", $blog_rs["date"]);
		$blog_rs["story"] = substr($blog_rs["story"], 0, 300);
		$blog_rs["story"] = str_replace("\r\n", "<br>", $blog_rs["story"]);
		$blog_rs["story"] = str_replace("\n", "<br>", $blog_rs["story"]);
		$blog_rs["story"] = str_replace("\r", "<br>", $blog_rs["story"]);
		return $blog_rs;
	}

	public function commentBanners()
	{
		$comment_banner_result = $this->query("select * from tk_banners where active=1 and banner_name like 'comment%' AND name!='' order by banner_name");
		$comment_banners = "";
		$cb = 0;
		while ($row = $this->fetchRow($comment_banner_result, MYSQL_ASSOC)) {
			$comment_banners[$cb] = "<a class=\"oranlar\" href=\"/ad.php?id=" . ($row["id"] + 1000) . "\" target=\"_blank\"><img src=\"/img/affiliate/" . $row["picture"] . "\"></a>";
			$cb++;
		}

		return $comment_banners;
	}

	public function couponResult()
	{
		if (date("H") >= 17)
			$startOfDay = mktime(17, 0, 0, date("m"), date("d"), date("Y"));
		else
			$startOfDay = mktime(0, 0, 0, date("m"), date("d"), date("Y"));

		$game_code_criteria = "";
		$count = 0;
		for ($i = 0; $i < 2; $i++) {
			$query = "select g.date,g.leag,g.code AS game_code,g.title,g.link_name AS game_link,g.puid AS game_id,bg.tr_code AS bg_code,bt.description AS bt_code,ub.rate
			from tk_userbets ub
			JOIN tk_games g on ub.game_id=g.puid
			JOIN tk_bettypes bt on ub.bettype_id=bt.id
			JOIN tk_betgroups bg on ub.betgroup_id=bg.id
			where ub.result=0
			and g.datetime>=" . $this->myTime . "
			and g.datetime<" . ($startOfDay + (30 * 3600)) . "
			and ub.ratetype_id=1 ";

			if ($game_code_criteria != "") {
				$query .= "and g.code NOT IN (" . $game_code_criteria . ") ";
			}

			$query .= "order by ub.probability desc limit 1";


			$result = $this->query($query);
			$rows = $this->numRows($result);



			while ($row = $this->fetchRow($result, MYSQL_ASSOC)) {
				$coupon["B"][$count]["game_date"] = substr($row["date"], 8, 2) . "." . substr($row["date"], 5, 2) . "." . substr($row["date"], 0, 4);
				$coupon["B"][$count]["game_code"] = $row["game_code"];
				$coupon["B"][$count]["league"] = $row["leag"];
				$coupon["B"][$count]["game_title"] = $this->toLatin1UpperCase($row["title"]);
				$coupon["B"][$count]["title_url"] = substr($row["date"], 8, 2) . "-" . substr($row["date"], 5, 2) . "-" . substr($row["date"], 0, 4) . "-" . $row["game_code"] . "-" . $row["game_link"] . "-" . $row["game_id"];
				$coupon["B"][$count]["bg_code"] = $row["bg_code"];

				if ($coupon["B"][$count]["bg_code"] == "AÜ")
					$coupon["B"][$count]["bg_code"] = "2,5";
				else if ($coupon["B"][$count]["bg_code"] == "1,5 AÜ")
					$coupon["B"][$count]["bg_code"] = "1,5";
				else if ($coupon["B"][$count]["bg_code"] == "3,5 AÜ")
					$coupon["B"][$count]["bg_code"] = "3,5";
				else if ($coupon["B"][$count]["bg_code"] == "İY 1,5 AÜ")
					$coupon["B"][$count]["bg_code"] = "İY 1,5";

				$coupon["B"][$count]["bt_code"] = $row["bt_code"];
				$coupon["B"][$count]["rate"] = $row["rate"];

				if ($game_code_criteria != "")
					$game_code_criteria .= ",";
				$game_code_criteria .= "'" . $row["game_code"] . "'";

				$count++;
			}
		}


		$limit = (4 - $count);
		for ($i = 0; $i < $limit; $i++) {
			$query = "select g.date,g.leag,g.code AS game_code,g.title,g.link_name AS game_link,g.puid AS game_id,bg.tr_code AS bg_code,bt.description AS bt_code,ub.rate
			from tk_userbets ub
			JOIN tk_games g on ub.game_id=g.puid
			JOIN tk_bettypes bt on ub.bettype_id=bt.id
			JOIN tk_betgroups bg on ub.betgroup_id=bg.id
			where ub.result=0
			and g.datetime>=" . $this->myTime . "
			and g.datetime<" . ($startOfDay + (30 * 3600)) . "
			and ub.ratetype_id=2 ";

			if ($game_code_criteria != "") {
				$query .= "and g.code NOT IN (" . $game_code_criteria . ") ";
			}

			$query .= "order by ub.probability desc limit 1";

			$result = $this->query($query);
			$rows = $this->numRows($result);



			while ($row = $this->fetchRow($result, MYSQL_ASSOC)) {
				$coupon["B"][$count]["game_date"] = substr($row["date"], 8, 2) . "." . substr($row["date"], 5, 2) . "." . substr($row["date"], 0, 4);
				$coupon["B"][$count]["game_code"] = $row["game_code"];
				$coupon["B"][$count]["league"] = $row["leag"];
				$coupon["B"][$count]["game_title"] = $this->toLatin1UpperCase($row["title"]);
				$coupon["B"][$count]["title_url"] = substr($row["date"], 8, 2) . "-" . substr($row["date"], 5, 2) . "-" . substr($row["date"], 0, 4) . "-" . $row["game_code"] . "-" . $row["game_link"] . "-" . $row["game_id"];
				$coupon["B"][$count]["bg_code"] = $row["bg_code"];

				if ($coupon["B"][$count]["bg_code"] == "AÜ")
					$coupon["B"][$count]["bg_code"] = "2,5";
				else if ($coupon["B"][$count]["bg_code"] == "1,5 AÜ")
					$coupon["B"][$count]["bg_code"] = "1,5";
				else if ($coupon["B"][$count]["bg_code"] == "3,5 AÜ")
					$coupon["B"][$count]["bg_code"] = "3,5";
				else if ($coupon["B"][$count]["bg_code"] == "İY 1,5 AÜ")
					$coupon["B"][$count]["bg_code"] = "İY 1,5";

				$coupon["B"][$count]["bt_code"] = $row["bt_code"];
				$coupon["B"][$count]["rate"] = $row["rate"];

				if ($game_code_criteria != "")
					$game_code_criteria .= ",";
				$game_code_criteria .= "'" . $row["game_code"] . "'";

				$count++;
			}
		}

		$coupon_b_rate = 1;
		for ($i = 0; $i < sizeof($coupon["B"]); $i++) {
			$coupon_b_table .= "<div class=\"current-bet-line line-s\">
				<div class=\"box1st box-item talign-center\">" . $coupon["B"][$i]["game_date"] . "</div>
				<div class=\"box2nd box-item talign-center\" title=\"İNGİLTERE LİG KUPASI\"><a href=\"/lig/ingiltere-lig-kupasi\">" . $coupon["B"][$i]["league"] . "</a></div>
				<div class=\"box3rd box-item talign-center\">" . $coupon["B"][$i]["game_code"] . "</div>
				<div class=\"box4th box-item\"><a href=\"/iddaa/" . $coupon["B"][$i]["title_url"] . "\">" . $coupon["B"][$i]["game_title"] . "</a></div>
				<div class=\"box11th box-item talign-center\">" . $coupon["B"][$i]["bg_code"] . " " . $coupon["B"][$i]["bt_code"] . "</div>
				<div class=\"box9th box-item talign-center\">" . $coupon["B"][$i]["rate"] . "</div>
			</div>";
			$coupon_b_rate = $coupon_b_rate * $coupon["B"][$i]["rate"];
		}

		$coupon_result["B"]["table"] = $coupon["B"];
		$coupon_result["B"]["rate"] = $coupon_b_rate;

		//===================================================================================================================================================
		//===================================================================================================================================================
		//===================================================================================================================================================



		$game_code_criteria = "";
		$count = 0;
		for ($i = 0; $i < 2; $i++) {
			$query = "select g.date,g.leag,g.code AS game_code,g.title,g.link_name AS game_link,g.puid AS game_id,bg.tr_code AS bg_code,bt.description AS bt_code,ub.rate
			from tk_userbets ub
			JOIN tk_games g on ub.game_id=g.puid
			JOIN tk_bettypes bt on ub.bettype_id=bt.id
			JOIN tk_betgroups bg on ub.betgroup_id=bg.id
			where ub.result=0
			and g.datetime>=" . $this->myTime . "
			and g.datetime<" . ($startOfDay + (30 * 3600)) . "
			and ub.ratetype_id=3 ";

			if ($game_code_criteria != "") {
				$query .= "and g.code NOT IN (" . $game_code_criteria . ") ";
			}

			$query .= "order by ub.probability desc limit 1";

			$result = $this->query($query);
			$rows = $this->numRows($result);


			while ($row = $this->fetchRow($result, MYSQL_ASSOC)) {
				$coupon["C"][$count]["game_date"] = substr($row["date"], 8, 2) . "." . substr($row["date"], 5, 2) . "." . substr($row["date"], 0, 4);
				$coupon["C"][$count]["game_code"] = $row["game_code"];
				$coupon["C"][$count]["league"] = $row["leag"];
				$coupon["C"][$count]["game_title"] = $this->toLatin1UpperCase($row["title"]);
				$coupon["C"][$count]["title_url"] = substr($row["date"], 8, 2) . "-" . substr($row["date"], 5, 2) . "-" . substr($row["date"], 0, 4) . "-" . $row["game_code"] . "-" . $row["game_link"] . "-" . $row["game_id"];
				$coupon["C"][$count]["bg_code"] = $row["bg_code"];

				if ($coupon["C"][$count]["bg_code"] == "AÜ")
					$coupon["C"][$count]["bg_code"] = "2,5";
				else if ($coupon["C"][$count]["bg_code"] == "1,5 AÜ")
					$coupon["C"][$count]["bg_code"] = "1,5";
				else if ($coupon["C"][$count]["bg_code"] == "3,5 AÜ")
					$coupon["C"][$count]["bg_code"] = "3,5";
				else if ($coupon["C"][$count]["bg_code"] == "İY 1,5 AÜ")
					$coupon["C"][$count]["bg_code"] = "İY 1,5";

				$coupon["C"][$count]["bt_code"] = $row["bt_code"];
				$coupon["C"][$count]["rate"] = $row["rate"];

				if ($game_code_criteria != "")
					$game_code_criteria .= ",";
				$game_code_criteria .= "'" . $row["game_code"] . "'";

				$count++;
			}
		}

		$limit = (4 - $count);
		for ($i = 0; $i < $limit; $i++) {

			$query = "select g.date,g.leag,g.code AS game_code,g.title,g.link_name AS game_link,g.puid AS game_id,bg.tr_code AS bg_code,bt.description AS bt_code,ub.rate
			from tk_userbets ub
			JOIN tk_games g on ub.game_id=g.puid
			JOIN tk_bettypes bt on ub.bettype_id=bt.id
			JOIN tk_betgroups bg on ub.betgroup_id=bg.id
			where ub.result=0
			and g.datetime>=" . $this->myTime . "
			and g.datetime<" . ($startOfDay + (30 * 3600)) . "
			and ub.ratetype_id=4 ";

			if ($game_code_criteria != "") {
				$query .= "and g.code NOT IN (" . $game_code_criteria . ") ";
			}

			$query .= "order by ub.probability desc limit 1";

			$result = $this->query($query);
			$rows = $this->numRows($result);




			while ($row = $this->fetchRow($result, MYSQL_ASSOC)) {
				$coupon["C"][$count]["game_date"] = substr($row["date"], 8, 2) . "." . substr($row["date"], 5, 2) . "." . substr($row["date"], 0, 4);
				$coupon["C"][$count]["game_code"] = $row["game_code"];
				$coupon["C"][$count]["league"] = $row["leag"];
				$coupon["C"][$count]["game_title"] = $this->toLatin1UpperCase($row["title"]);
				$coupon["C"][$count]["title_url"] = substr($row["date"], 8, 2) . "-" . substr($row["date"], 5, 2) . "-" . substr($row["date"], 0, 4) . "-" . $row["game_code"] . "-" . $row["game_link"] . "-" . $row["game_id"];
				$coupon["C"][$count]["bg_code"] = $row["bg_code"];

				if ($coupon["C"][$count]["bg_code"] == "AÜ")
					$coupon["C"][$count]["bg_code"] = "2,5";
				else if ($coupon["C"][$count]["bg_code"] == "1,5 AÜ")
					$coupon["C"][$count]["bg_code"] = "1,5";
				else if ($coupon["C"][$count]["bg_code"] == "3,5 AÜ")
					$coupon["C"][$count]["bg_code"] = "3,5";
				else if ($coupon["C"][$count]["bg_code"] == "İY 1,5 AÜ")
					$coupon["C"][$count]["bg_code"] = "İY 1,5";

				$coupon["C"][$count]["bt_code"] = $row["bt_code"];
				$coupon["C"][$count]["rate"] = $row["rate"];

				if ($game_code_criteria != "")
					$game_code_criteria .= ",";
				$game_code_criteria .= "'" . $row["game_code"] . "'";

				$count++;
			}
		}

		$coupon_s_rate = 1;
		for ($i = 0; $i < sizeof($coupon["C"]); $i++) {
			$coupon_s_table .= "<div class=\"current-bet-line line-s\">
				<div class=\"box1st box-item talign-center\">" . $coupon["C"][$i]["game_date"] . "</div>
				<div class=\"box2nd box-item talign-center\" title=\"İNGİLTERE LİG KUPASI\"><a href=\"/lig/ingiltere-lig-kupasi\">" . $coupon["C"][$i]["league"] . "</a></div>
				<div class=\"box3rd box-item talign-center\">" . $coupon["C"][$i]["game_code"] . "</div>
				<div class=\"box4th box-item\"><a href=\"/iddaa/" . $coupon["C"][$i]["title_url"] . "\">" . $coupon["C"][$i]["game_title"] . "</a></div>
				<div class=\"box11th box-item talign-center\">" . $coupon["C"][$i]["bg_code"] . " " . $coupon["C"][$i]["bt_code"] . "</div>
				<div class=\"box9th box-item talign-center\">" . $coupon["C"][$i]["rate"] . "</div>
			</div>";
			$coupon_s_rate = $coupon_s_rate * $coupon["C"][$i]["rate"];
		}

		$coupon_result["S"]["table"] = $coupon["C"];
		$coupon_result["S"]["rate"] = $coupon_s_rate;

		return $coupon_result;
	}

	public function convertToURLText($text)
	{
		$turkish 	= array("İ", "ı", "ğ", "ü", "ş", "ö", "ç", " "); //turkish letters
		$english   	= array("i", "i", "g", "u", "s", "o", "c", "-"); //english cooridinators letters

		$text = strtolower($text);
		$text = trim(str_replace($turkish, $english, $text));
		$text = str_replace("----", "-", $text);
		$text = str_replace("---", "-", $text);
		$text = str_replace("--", "-", $text);

		return $text;
	}

	public function getTop10ForLeague($params)
	{
		$result = $this->query("SELECT u.username,u.id AS user_id,usbl.score,usbl.bets,usbl.success_rate,u.user_type
			FROM tk_userscoresbyleague usbl
			JOIN tk_users u on usbl.user_id=u.id
			WHERE usbl.league_id=" . @$params['league_id'] . "
			AND usbl.bf_id=" . @$params['category_id'] . "
			AND usbl.rt_id=" . @$params['ratetype_id'] . "
			ORDER BY score DESC
			limit 10");
		$i = 0;
		$userInList = false;
		while ($row = $this->fetchRow($result, MYSQL_ASSOC)) {
			if (is_numeric($params["user_id"]) && $params["user_id"] > 0 && $row["user_id"] == $params["user_id"]) {
				$userInList = true;
				$Top10List[$i]["labelFontColor"]	= "#FF0000";
				$Top10List[$i]["labelFontBold"]	= "1";
			}
			$user_type						= $row["user_type"];
			$Top10List[$i]["label"] 		= $row["username"];
			if ($user_type == "E")
				$Top10List[$i]["label"] 	.= " (E)";

			$Top10List[$i]["labelLink"] 		= "/" . $row["username"];
			$Top10List[$i]["value"] 			= number_format($row["score"], 2, '.', '');
			$Top10List[$i]["displayValue"] 		= $row["bets"] . " / %" . $row["success_rate"] . " / " . number_format($row["score"], 2, '.', '') . "";
			$i++;
		}

		if (is_numeric($params["user_id"]) && $params["user_id"] > 0 && $userInList == false) {
			$result = $this->query("SELECT u.username,u.id AS user_id,usbl.score,usbl.bets,usbl.success_rate,u.user_type
				FROM tk_userscoresbyleague usbl
				JOIN tk_users u on usbl.user_id=u.id
				WHERE usbl.league_id=" . @$params['league_id'] . "
				AND usbl.bf_id=" . @$params['category_id'] . "
				AND usbl.rt_id=" . @$params['ratetype_id'] . "
				AND u.id=" . @$params["user_id"] . "
				ORDER BY score DESC");
			while ($row = $this->fetchRow($result, MYSQL_ASSOC)) {
				$user_type						= $row["user_type"];
				$Top10List[$i]["label"] 		= $row["username"];
				if ($user_type == "E")
					$Top10List[$i]["label"] 	.= " (E)";

				$Top10List[$i]["labelLink"] 		= "/" . $row["username"];
				$Top10List[$i]["value"] 			= number_format($row["score"], 2, '.', '');
				$Top10List[$i]["displayValue"] 		= $row["bets"] . " / %" . $row["success_rate"] . " / " . number_format($row["score"], 2, '.', '') . "";
				$Top10List[$i]["labelFontColor"]	= "#FF0000";
				$Top10List[$i]["labelFontBold"]	= "1";
				$i++;
			}
		}


		return $Top10List;
	}

	public function getLeagueInfo($link_name)
	{
		$result = $this->query("select * from tk_leagues where link_name='" . $link_name . "'");
		$rows = $this->numRows($result);
		$row = $this->fetchRow($result, MYSQL_ASSOC);

		if ($row["league_image"] == "") {
			$row["image"] = "/img/tahminkrali_iddaa_tahmin_256.png";
		} else {
			if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/img/lig/" . $row["league_image"]))
				$row["image"] = "/img/lig/" . $row["league_image"];
			else
				$row["image"] = "/img/tahminkrali_iddaa_tahmin_256.png";
		}

		$row["upper_name"] = $this->toLatin1UpperCase($row["name"]);
		return $row;
	}

	public function latestLiveRankingDate()
	{
		$result = $this->query("select MAX(update_time) as last_update from tk_liveranking");
		$row = $this->fetchRow($result, MYSQL_ASSOC);
		return $row["last_update"];
	}

	public function currentBetDates()
	{
		$result = $this->query("SELECT distinct g.date 
			FROM `tk_userbets` ub
			JOIN tk_games g on ub.game_id=g.puid
			where g.datetime>=" . $this->myTime);
		$i = 0;
		while ($row = $this->fetchRow($result, MYSQL_ASSOC)) {
			$date = $row["date"];
			$DateList[$i] = $date;
			$i++;
		}
		return $DateList;
	}

	public function currentBetLeagues()
	{
		$result = $this->query("SELECT distinct ub.league_code 
			FROM `tk_userbets` ub
			JOIN tk_games g on ub.game_id=g.puid
			where g.datetime>=" . $this->myTime . "
			order by ub.league_code");
		$i = 0;
		while ($row = $this->fetchRow($result, MYSQL_ASSOC)) {
			$league_code = $row["league_code"];
			$LeagueList[$i] = $league_code;
			$i++;
		}
		return $LeagueList;
	}

	public function currentBetGames($league_code = null)
	{
		$query = "SELECT distinct g.code,g.title,g.puid		
			FROM `tk_userbets` ub
			JOIN tk_games g on ub.game_id=g.puid
			where 1=1";
		if ($league_code != "")
			$query .= " AND g.leag='" . $league_code . "'";
		$query .= " AND g.datetime>=" . $this->myTime . "";
		$result = $this->query($query);
		$i = 0;
		while ($row = $this->fetchRow($result, MYSQL_ASSOC)) {
			$game_code = $row["code"];
			$game_title = $this->toLatin1UpperCase($row["title"]);
			$game_id = $row["puid"];
			$GameCodeList[$i] = $game_code;
			$GameTitleList[$game_id] = $game_title;
			$i++;
		}
		$ReturnList['codeList'] = $GameCodeList;
		$ReturnList['titleList'] = $GameTitleList;
		return $ReturnList;
	}


	public function deleteBet($user_id, $bet_id)
	{
		$result = $this->query("select g.datetime,ub.id 
			from tk_userbets ub
			join tk_games g on ub.game_id=g.puid
			where ub.user_id='" . $user_id . "' 
			and ub.id='" . $bet_id . "'");
		$rows = $this->numRows($result);
		while ($row = $this->fetchRow($result, MYSQL_ASSOC)) {
			$datetime 	= $row["datetime"];
			$bet_id 	= $row["id"];

			if ($datetime > $this->myTime) {
				$this->query("delete from tk_userbets where user_id='" . $user_id . "' and id='" . $bet_id . "'");
			}
		}
		//return $rows."-".$datetime."-".$bet_id;

	}

	public function userRankingCount($current = true)
	{
		if ($current == true) {
			$query = "select id from tk_liveranking lr";
		} else {
			$pre_month = date("m", strtotime("-1 month"));
			$pre_year  = date("Y", strtotime("-1 month"));
			$query = "select id from tk_monthlyranking lr where year='" . $pre_year . "' and month='" . $pre_month . "'";
		}
		$result = $this->query($query);
		$rows = $this->numRows($result);
		return $rows;
	}

	public function userRanking($page, $userPerPage, $userid, $month = "current")
	{
		$start = (($page - 1) * $userPerPage) + 1;
		$end = $page * $userPerPage;

		if ($month == "current") {
			if ($userid != "") {
				$user_rank_query = "select rank from tk_liveranking where user_id='" . $userid . "'";
				$user_rank_result = $this->query($user_rank_query);
				while ($user_rank_row = $this->fetchRow($user_rank_result, MYSQL_ASSOC)) {
					$user_rank = $user_rank_row["rank"];
				}
			}


			$query = "select lr.*,l.name,l.link_name
				from tk_liveranking lr
				JOIN tk_leagues l on lr.best_league=l.code
				where (rank>=" . $start . " AND rank<=" . $end . ") order by rank";

			$result = $this->query($query);
			$rows = $this->numRows($result);
			$i = 0;

			$userExistsInList = false;

			while ($row = $this->fetchRow($result, MYSQL_ASSOC)) {
				$UserRankingList[$i]["order"]				= $row["rank"];
				$UserRankingList[$i]["userid"]				= $row["user_id"];
				$UserRankingList[$i]["current_user"]		= false;
				$UserRankingList[$i]["username"]			= htmlentities($row["username"]);
				$UserRankingList[$i]["total_bets"]			= $row["total_bets"];
				$UserRankingList[$i]["total_score"]			= number_format($row["total_score"], 2, '.', '');
				$UserRankingList[$i]["avg_rate"]			= number_format($row["avg_rate"], 2, '.', '');
				$UserRankingList[$i]["best_bf"]				= $row["best_bf"];
				$UserRankingList[$i]["best_league"]			= $row["best_league"];
				$UserRankingList[$i]["best_league_name"]	= $row["name"];
				$UserRankingList[$i]["best_league_link_name"]	= $row["link_name"];
				$UserRankingList[$i]["success_rate"] 	 	= number_format($row["success_rate"], 1, '.', '');
				if ($UserRankingList[$i]["userid"] == $userid && $userExistsInList == false) {
					$userExistsInList = true;
					$UserRankingList[$i]["current_user"]	= true;
				}
				$i++;
			}

			if ($user_rank > $end) {
				$user_query = "select lr.*,l.name,l.link_name
					from tk_liveranking lr
					JOIN tk_leagues l on lr.best_league=l.code
					where user_id='" . $userid . "' order by rank";
				$user_result = $this->query($user_query);
				while ($row = $this->fetchRow($user_result, MYSQL_ASSOC)) {
					$UserRanking[0]["order"]				= $row["rank"];
					$UserRanking[0]["userid"]				= $row["user_id"];
					$UserRanking[0]["current_user"]			= false;
					$UserRanking[0]["username"]				= $row["username"];
					$UserRanking[0]["total_bets"]			= $row["total_bets"];
					$UserRanking[0]["total_score"]			= number_format($row["total_score"], 2, '.', '');
					$UserRanking[0]["avg_rate"]				= number_format($row["avg_rate"], 2, '.', '');
					$UserRanking[0]["best_bf"]				= $row["best_bf"];
					$UserRanking[0]["best_league"]			= $row["best_league"];
					$UserRanking[0]["best_league_name"]		= $row["name"];
					$UserRanking[0]["best_league_link_name"]	= $row["link_name"];
					$UserRanking[0]["success_rate"] 	 	= number_format($row["success_rate"], 1, '.', '');
					$UserRanking[0]["current_user"]			= true;
				}
			}

			$returnList["UserRankingList"] 	= $UserRankingList;
			$returnList["UserRanking"] 		= $UserRanking;
		} else {
			$pre_month = date("m", strtotime("-1 month"));
			$pre_year  = date("Y", strtotime("-1 month"));

			if ($userid != "") {
				$user_rank_query = "select rank from tk_monthlyranking where year='" . $pre_year . "' and month='" . $pre_month . "' and user_id='" . $userid . "'";
				$user_rank_result = $this->query($user_rank_query);
				while ($user_rank_row = $this->fetchRow($user_rank_result, MYSQL_ASSOC)) {
					$user_rank = $user_rank_row["rank"];
				}
			}

			$query = "select lr.*,l.name,l.link_name
				from tk_monthlyranking lr
				JOIN tk_leagues l on lr.best_league=l.code
				where year='" . $pre_year . "' and month='" . $pre_month . "' and 
				(rank>=" . $start . " AND rank<=" . $end . ") order by rank";

			$result = $this->query($query);
			$rows = $this->numRows($result);
			$i = 0;

			$userExistsInList = false;

			while ($row = $this->fetchRow($result, MYSQL_ASSOC)) {
				$UserRankingList[$i]["order"]				= $row["rank"];
				$UserRankingList[$i]["userid"]				= $row["user_id"];
				$UserRankingList[$i]["current_user"]		= false;
				$UserRankingList[$i]["username"]			= $row["username"];
				$UserRankingList[$i]["total_bets"]			= $row["total_bets"];
				$UserRankingList[$i]["total_score"]			= number_format($row["total_score"], 2, '.', '');
				$UserRankingList[$i]["avg_rate"]			= number_format($row["avg_rate"], 2, '.', '');
				$UserRankingList[$i]["best_bf"]				= $row["best_bf"];
				$UserRankingList[$i]["best_league"]			= $row["best_league"];
				$UserRankingList[$i]["best_league_name"]	= $row["name"];
				$UserRankingList[$i]["best_league_link_name"]	= $row["link_name"];
				$UserRankingList[$i]["success_rate"] 	 	= number_format($row["success_rate"], 1, '.', '');
				if ($UserRankingList[$i]["userid"] == $userid && $userExistsInList == false) {
					$userExistsInList = true;
					$UserRankingList[$i]["current_user"]	= true;
				}
				$i++;
			}

			if ($user_rank > $end) {
				$user_query = "select lr.*,l.name,l.link_name
					from tk_monthlyranking lr
					JOIN tk_leagues l on lr.best_league=l.code
					where year='" . $pre_year . "' and month='" . $pre_month . "' and user_id='" . $userid . "' order by rank";
				$user_result = $this->query($user_query);
				while ($row = $this->fetchRow($user_result, MYSQL_ASSOC)) {
					$UserRanking[0]["order"]				= $row["rank"];
					$UserRanking[0]["userid"]				= $row["user_id"];
					$UserRanking[0]["current_user"]			= false;
					$UserRanking[0]["username"]				= $row["username"];
					$UserRanking[0]["total_bets"]			= $row["total_bets"];
					$UserRanking[0]["total_score"]			= number_format($row["total_score"], 2, '.', '');
					$UserRanking[0]["avg_rate"]				= number_format($row["avg_rate"], 2, '.', '');
					$UserRanking[0]["best_bf"]				= $row["best_bf"];
					$UserRanking[0]["best_league"]			= $row["best_league"];
					$UserRanking[0]["best_league_name"]		= $row["name"];
					$UserRanking[0]["best_league_link_name"]	= $row["link_name"];
					$UserRanking[0]["success_rate"] 	 	= number_format($row["success_rate"], 1, '.', '');
					$UserRanking[0]["current_user"]			= true;
				}
			}

			$returnList["UserRankingList"] 	= $UserRankingList;
			$returnList["UserRanking"] 		= $UserRanking;
		}

		return $returnList;
	}


	public function listLeagues($filters = null)
	{

		$query = "select * 
			from tk_leagues 
			where 1=1 
			AND name!='' ";

		if ($filters["has1000Bets"] != "")
			$query .= "AND has1000Bets=true ";

		$query .= "order by name";

		$result = $this->query($query);
		$i = 0;
		while ($row = $this->fetchRow($result, MYSQL_ASSOC)) {
			$leagues[$i]["id"] 			= $row["id"];
			$leagues[$i]["code"]		= $row["code"];
			$leagues[$i]["name"]		= $row["name"];
			$leagues[$i]["link_name"]	= $row["link_name"];
			$leagues[$i]["country"]		= $row["country"];
			$i++;
		}
		return $leagues;
	}

	public function listActiveGames($userid)
	{
		if (isset($userid) && is_numeric($userid)) {
			$result = $this->query("select g.code,g.title,b.rate,bg.description AS bg_name,bt.description AS bt_name,bf.code AS bf_code,b.id AS basket_id,bt.code AS bt_code,bg.code AS bg_code
			from tk_basket b
			JOIN tk_games g on b.game_id=g.puid
			JOIN tk_bettypes bt on b.bet_code=bt.code
			JOIN tk_betgroups bg on bt.group_id=bg.id
			JOIN tk_bettypefamily bf on bg.family_id=bf.id
			where b.user_id='" . $userid . "'
			and g.datetime>='" . $this->myTime . "'
			order by b.id");
			if ($result) {
				$i = 0;
				while ($row = $this->fetchRow($result)) {
					$UserBasketBets[$i]["game_code"] = $row[0];
					$UserBasketBets[$i]["game_title"] = $row[1];
					$UserBasketBets[$i]["bet_rate"] = $row[2];
					$UserBasketBets[$i]["bg_name"] = $row[3];
					$UserBasketBets[$i]["bt_name"] = $row[4];
					$UserBasketBets[$i]["bf_code"] = $row[5];
					$UserBasketBets[$i]["basket_id"] = $row[6];
					$UserBasketBets[$i]["bt_code"] = $row[7];
					$UserBasketBets[$i]["bg_code"] = $row[8];
					$i++;
				}
			}

			$result = $this->query("select g.code,g.title,b.rate,bg.description AS bg_name,bt.description AS bt_name,bf.code AS bf_code,b.id AS basket_id,bt.code AS bt_code,bg.code AS bg_code
			from tk_userbets b
			JOIN tk_games g on b.game_id=g.puid
			JOIN tk_bettypes bt on b.bet_code=bt.code
			JOIN tk_betgroups bg on bt.group_id=bg.id
			JOIN tk_bettypefamily bf on bg.family_id=bf.id
			where b.user_id='" . $userid . "'
			and g.datetime>='" . $this->myTime . "'
			order by b.id");
			if ($result) {
				$i = 0;
				while ($row = $this->fetchRow($result)) {
					$UserActiveBets[$i]["game_code"] 	= $row[0];
					$UserActiveBets[$i]["game_title"] 	= $row[1];
					$UserActiveBets[$i]["bet_rate"] 	= $row[2];
					$UserActiveBets[$i]["bg_name"] 		= $row[3];
					$UserActiveBets[$i]["bt_name"] 		= $row[4];
					$UserActiveBets[$i]["bf_code"] 		= $row[5];
					$UserActiveBets[$i]["basket_id"]	= $row[6];
					$UserActiveBets[$i]["bt_code"] 		= $row[7];
					$UserActiveBets[$i]["bg_code"] 		= $row[8];
					$i++;
				}
			}
		}

		$query = "select g.code AS game_code,g.date,g.leag,g.title,g.hteam,g.ateam,l.name,l.country,g.datetime,g.puid,g.fth,g.fta,l.link_name
			from tk_games g
			JOIN tk_leagues l on g.leag=l.code
			where datetime>" . $this->myTime . "
			order by g.datetime,g.code";
		$result = $this->query($query);
		$rowsCount = $this->numRows($result);
		$i = 0;
		$j = 0;
		$prev_date = "";
		while ($row = $this->fetchRow($result, MYSQL_ASSOC)) {
			$date 									= $row["date"];
			if ($prev_date != $date)
				$j = 0;
			$game_date 									= ($row["datetime"] >= 1445731200 && $row["datetime"] < 1490486400) ? $this->toLatin1UpperCase(strftime("%d.%m.%Y %A", $row["datetime"] + 3600)) : $this->toLatin1UpperCase(strftime("%d.%m.%Y %A", $row["datetime"]));
			//$game_date 									= $this->toLatin1UpperCase(strftime("%d.%m.%Y %A",$row["datetime"]));


			$activeGames[$game_date][$j]["game_id"] 	= $row["puid"];
			$activeGames[$game_date][$j]["game_code"] 	= $row["game_code"];
			$activeGames[$game_date][$j]["game_league"] = $row["leag"];
			$activeGames[$game_date][$j]["league_link"] = $row["link_name"];
			$activeGames[$game_date][$j]["game_title"] 	= $row["title"];
			$activeGames[$game_date][$j]["game_time"] = ($row["datetime"] >= 1445731200 && $row["datetime"] < 1490486400) ? strftime("%H:%M", ($row["datetime"] + 3600)) : strftime("%H:%M", ($row["datetime"]));
			//$activeGames[$game_date][$j]["game_time"] 	= strftime("%H:%M",($row["datetime"]));
			$activeGames[$game_date][$j]["game_date"] 	= $row["date"];
			$activeGames[$game_date][$j]["game_puid"] 	= $row["puid"];
			$activeGames[$game_date][$j]["fth"] 		= $row["fth"];
			$activeGames[$game_date][$j]["fta"] 		= $row["fta"];

			$bets_query = "select bg.code bg_code,bt.code bt_code,br.rate
				from tk_betrates br
				JOIN tk_bettypes bt on br.bet_type=bt.id
				JOIN tk_betgroups bg on bt.group_id=bg.id
				where br.game_id=" . $row["puid"];
			$bets_result = $this->query($bets_query);
			while ($bets_row = $this->fetchRow($bets_result, MYSQL_ASSOC)) {
				$bg_code 	= $bets_row["bg_code"];
				$bt_code 	= $bets_row["bt_code"];
				$rate 		= $bets_row["rate"];

				//$activeGames[$game_date][$j][$bg_code][$bt_code] = $rate;
				$activeGames[$game_date][$j][$bg_code][$bt_code]['rate'] = $rate;
				$activeGames[$game_date][$j][$bg_code][$bt_code]['selected'] = false;

				if (isset($userid) && is_numeric($userid) && isset($UserBasketBets)) {
					foreach ($UserBasketBets as $key => $val) {
						if (($val['bt_code'] == $bt_code) && ($val['bg_code'] == $bg_code) && ($val['game_code'] == ($activeGames[$game_date][$j]["game_code"]))) {
							$activeGames[$game_date][$j][$bg_code][$bt_code]['selected'] = true;
						}
					}
				}

				if (isset($userid) && is_numeric($userid) && isset($UserActiveBets)) {
					foreach ($UserActiveBets as $key => $val) {
						if (($val['bt_code'] == $bt_code) && ($val['bg_code'] == $bg_code) && ($val['game_code'] == ($activeGames[$game_date][$j]["game_code"]))) {
							$activeGames[$game_date][$j][$bg_code][$bt_code]['bet'] = true;
						}
					}
				}
			}

			$activeGames[$game_date][$j]["other_bets"] 	= sizeof($activeGames[$game_date][$j]["F15"])
				+ sizeof($activeGames[$game_date][$j]["F35"])
				+ sizeof($activeGames[$game_date][$j]["H15"])
				+ sizeof($activeGames[$game_date][$j]["SF"])
				+ sizeof($activeGames[$game_date][$j]["S"])
				+ sizeof($activeGames[$game_date][$j]["GS"]);


			$prev_date = $date;
			$i++;
			$j++;
		}
		return $activeGames;
	}

	public function listActiveGamesForEngin($userid)
	{
		if (isset($userid) && is_numeric($userid)) {
			$result = $this->query("select g.code,g.title,b.rate,bg.description AS bg_name,bt.description AS bt_name,bf.code AS bf_code,b.id AS basket_id,bt.code AS bt_code,bg.code AS bg_code
			from tk_basket b
			JOIN tk_games g on b.game_id=g.puid
			JOIN tk_bettypes bt on b.bet_code=bt.code
			JOIN tk_betgroups bg on bt.group_id=bg.id
			JOIN tk_bettypefamily bf on bg.family_id=bf.id
			where b.user_id='" . $userid . "'
			and g.datetime>='" . $this->myTime . "'
			order by b.id");
			if ($result) {
				$i = 0;
				while ($row = $this->fetchRow($result)) {
					$UserBasketBets[$i]["game_code"] = $row[0];
					$UserBasketBets[$i]["game_title"] = $row[1];
					$UserBasketBets[$i]["bet_rate"] = $row[2];
					$UserBasketBets[$i]["bg_name"] = $row[3];
					$UserBasketBets[$i]["bt_name"] = $row[4];
					$UserBasketBets[$i]["bf_code"] = $row[5];
					$UserBasketBets[$i]["basket_id"] = $row[6];
					$UserBasketBets[$i]["bt_code"] = $row[7];
					$UserBasketBets[$i]["bg_code"] = $row[8];
					$i++;
				}
			}

			$result = $this->query("select g.code,g.title,b.rate,bg.description AS bg_name,bt.description AS bt_name,bf.code AS bf_code,b.id AS basket_id,bt.code AS bt_code,bg.code AS bg_code
			from tk_userbets b
			JOIN tk_games g on b.game_id=g.puid
			JOIN tk_bettypes bt on b.bet_code=bt.code
			JOIN tk_betgroups bg on bt.group_id=bg.id
			JOIN tk_bettypefamily bf on bg.family_id=bf.id
			where b.user_id='" . $userid . "'
			and g.datetime>='" . $this->myTime . "'
			order by b.id");
			if ($result) {
				$i = 0;
				while ($row = $this->fetchRow($result)) {
					$UserActiveBets[$i]["game_code"] 	= $row[0];
					$UserActiveBets[$i]["game_title"] 	= $row[1];
					$UserActiveBets[$i]["bet_rate"] 	= $row[2];
					$UserActiveBets[$i]["bg_name"] 		= $row[3];
					$UserActiveBets[$i]["bt_name"] 		= $row[4];
					$UserActiveBets[$i]["bf_code"] 		= $row[5];
					$UserActiveBets[$i]["basket_id"]	= $row[6];
					$UserActiveBets[$i]["bt_code"] 		= $row[7];
					$UserActiveBets[$i]["bg_code"] 		= $row[8];
					$i++;
				}
			}
		}

		$query = "select g.code AS game_code,g.date,g.leag,g.title,g.hteam,g.ateam,l.name,l.country,g.datetime,g.puid,g.fth,g.fta,l.link_name
			from tk_games g
			JOIN tk_leagues l on g.leag=l.code
			where datetime>" . $this->myTime . "
			order by g.code";
		$result = $this->query($query);
		$rowsCount = $this->numRows($result);
		$i = 0;
		$j = 0;
		$prev_date = "";
		while ($row = $this->fetchRow($result, MYSQL_ASSOC)) {
			$date 									= $row["date"];

			//$game_date 									= ($row["datetime"] >= 1445731200) ? $this->toLatin1UpperCase(strftime("%d.%m.%Y %A",$row["datetime"]+3600)) : $this->toLatin1UpperCase(strftime("%d.%m.%Y %A",$row["datetime"]));
			$game_date 									= $this->toLatin1UpperCase(strftime("%d.%m.%Y %A", $row["datetime"]));


			$activeGames[0][$j]["game_id"] 	= $row["puid"];
			$activeGames[0][$j]["game_code"] 	= $row["game_code"];
			$activeGames[0][$j]["game_league"] = $row["leag"];
			$activeGames[0][$j]["league_link"] = $row["link_name"];
			$activeGames[0][$j]["game_title"] 	= $row["title"];
			//$activeGames[0][$j]["game_time"] = ($row["datetime"] >= 1445731200) ? strftime("%H:%M",($row["datetime"]+3600)) : strftime("%H:%M",($row["datetime"]));
			$activeGames[0][$j]["game_time"] 	= strftime("%H:%M", ($row["datetime"]));
			$activeGames[0][$j]["game_date"] 	= $row["date"];
			$activeGames[0][$j]["game_puid"] 	= $row["puid"];
			$activeGames[0][$j]["fth"] 		= $row["fth"];
			$activeGames[0][$j]["fta"] 		= $row["fta"];

			$bets_query = "select bg.code bg_code,bt.code bt_code,br.rate
				from tk_betrates br
				JOIN tk_bettypes bt on br.bet_type=bt.id
				JOIN tk_betgroups bg on bt.group_id=bg.id
				where br.game_id=" . $row["puid"];
			$bets_result = $this->query($bets_query);
			while ($bets_row = $this->fetchRow($bets_result, MYSQL_ASSOC)) {
				$bg_code 	= $bets_row["bg_code"];
				$bt_code 	= $bets_row["bt_code"];
				$rate 		= $bets_row["rate"];

				//$activeGames[0][$j][$bg_code][$bt_code] = $rate;
				$activeGames[0][$j][$bg_code][$bt_code]['rate'] = $rate;
				$activeGames[0][$j][$bg_code][$bt_code]['selected'] = false;

				if (isset($userid) && is_numeric($userid) && isset($UserBasketBets)) {
					foreach ($UserBasketBets as $key => $val) {
						if (($val['bt_code'] == $bt_code) && ($val['bg_code'] == $bg_code) && ($val['game_code'] == ($activeGames[0][$j]["game_code"]))) {
							$activeGames[0][$j][$bg_code][$bt_code]['selected'] = true;
						}
					}
				}

				if (isset($userid) && is_numeric($userid) && isset($UserActiveBets)) {
					foreach ($UserActiveBets as $key => $val) {
						if (($val['bt_code'] == $bt_code) && ($val['bg_code'] == $bg_code) && ($val['game_code'] == ($activeGames[0][$j]["game_code"]))) {
							$activeGames[0][$j][$bg_code][$bt_code]['bet'] = true;
						}
					}
				}
			}

			$activeGames[0][$j]["other_bets"] 	= sizeof($activeGames[0][$j]["F15"])
				+ sizeof($activeGames[0][$j]["F35"])
				+ sizeof($activeGames[0][$j]["H15"])
				+ sizeof($activeGames[0][$j]["SF"])
				+ sizeof($activeGames[0][$j]["S"])
				+ sizeof($activeGames[0][$j]["GS"]);


			$prev_date = $date;
			$i++;
			$j++;
		}
		return $activeGames;
	}

	//public function listBets($user_id = 0, $isCurrent = false, $page, $filter = null)
	public function listBets($filters, $orderBy = null)
	{
		$rows = isset($filters["rows"]) ? $filters["rows"] : 10;
		$start = ($filters["page"] - 1) * $rows;
		$query = "select g.date,g.datetime,g.leag,g.code,g.title,bg.tr_code AS bg_code,bt.description AS bet_type,ub.rate,u.username,
			l.name,l.country,rt.name AS rate_type,bf.code AS bf_code,bf.name AS bf_name,ub.result,gr.firsthalf,gr.fulltime,ub.score,bg.description AS bg_description,ub.probability,ub.id,l.link_name,g.puid AS game_id,g.link_name AS game_link,g.has_comment ";

		$query .= "
			from tk_userbets ub
			JOIN tk_games g on ub.game_id=g.puid
			JOIN tk_bettypes bt on ub.bet_code=bt.code
			JOIN tk_betgroups bg on bt.group_id=bg.id
			JOIN tk_leagues l on g.leag=l.code
			JOIN tk_users u on ub.user_id=u.id
			JOIN tk_ratetypes rt on rt.high>=ub.rate AND rt.low<=ub.rate
			JOIN tk_bettypefamily bf on bg.family_id=bf.id
			LEFT OUTER JOIN tk_gameresults gr on (g.puid=gr.puid)
			where 1=1 ";

		if ($filters["user_id"] > 0)
			$query .= "AND ub.user_id='" . $filters["user_id"] . "' ";

		if ($filters["is_current"])
			$query .= "AND g.datetime>" . $this->myTime . " ";

		if ($filters["league_codes"] != "") {
			$leagueCodeList = split(",", $filters["league_codes"]);
			if (sizeof($leagueCodeList) > 0) {
				$query .= " AND l.id IN (";
				for ($i = 0; $i < sizeof($leagueCodeList); $i++) {
					$query .= "'" . $leagueCodeList[$i] . "'";
					if ($i < (sizeof($leagueCodeList) - 1))
						$query .= ",";
				}
				$query .= ") ";
			}
		}

		if ($filters["bet_groups"] != "") {
			$betGroupList = split(",", $filters["bet_groups"]);
			if (sizeof($betGroupList) > 0) {
				$query .= " AND bf.id IN (";
				for ($i = 0; $i < sizeof($betGroupList); $i++) {
					$query .= "" . $betGroupList[$i] . "";
					if ($i < (sizeof($betGroupList) - 1))
						$query .= ",";
				}
				$query .= ") ";
			}
		}

		if ($filters["rate_types"] != "") {
			$rateTypeList = split(",", $filters["rate_types"]);
			if (sizeof($rateTypeList) > 0) {
				$query .= " AND rt.id IN (";
				for ($i = 0; $i < sizeof($rateTypeList); $i++) {
					$query .= "" . $rateTypeList[$i] . "";
					if ($i < (sizeof($rateTypeList) - 1))
						$query .= ",";
				}
				$query .= ") ";
			}
		}

		if ($filters["game_titles"] != "") {
			$gameTitleList = split(",", $filters["game_titles"]);
			if (sizeof($gameTitleList) > 0) {
				$query .= " AND g.puid IN (";
				for ($i = 0; $i < sizeof($gameTitleList); $i++) {
					$query .= "" . $gameTitleList[$i] . "";
					if ($i < (sizeof($gameTitleList) - 1))
						$query .= ",";
				}
				$query .= ") ";
			}
		}

		//$query .= "order by g.datetime desc,g.code asc ";

		if ($orderBy["order"] == "")
			$query .= "order by g.datetime desc,g.code asc ";
		else
			$query .= $orderBy["order"];


		$query .= "limit " . $start . ", " . $rows . "";

		//print $query."<br>";

		$result = $this->query($query);
		$rowsCount = $this->numRows($result);
		$i = 0;
		while ($row = $this->fetchRow($result, MYSQL_ASSOC)) {
			//mb_strtoupper(strftime("%d.%m.%Y %A",strtotime($date)),"UTF-8");
			$userBets[$i]["game_date"] 		= strftime("%d.%m.%Y", $row["datetime"]);
			$userBets[$i]["league"] 		= $row["leag"];
			$userBets[$i]["l_name"] 		= $this->toLatin1UpperCase($row["name"]);
			$userBets[$i]["l_link_name"] 	= $row["link_name"];
			$userBets[$i]["l_country"] 		= $this->toLatin1UpperCase($row["country"]);
			$userBets[$i]["username"] 		= $row["username"];
			$userBets[$i]["game_code"] 		= $row["code"];
			$userBets[$i]["title"] 			= $this->toLatin1UpperCase($row["title"]);
			$userBets[$i]["title_url"] 		= substr($row["date"], 8, 2) . "-" . substr($row["date"], 5, 2) . "-" . substr($row["date"], 0, 4) . "-" . $userBets[$i]["game_code"] . "-" . $row["game_link"] . "-" . $row["game_id"];
			$userBets[$i]["bg_code"]		= $row["bg_code"];
			$userBets[$i]["bet_type"]		= mb_strtoupper($row["bet_type"], "UTF-8");
			$userBets[$i]["bet_code"]		= $row["bet_code"];
			$userBets[$i]["rate"]			= $row["rate"];
			$userBets[$i]["rate_type"]		= $this->toLatin1UpperCase($row["rate_type"]);
			$userBets[$i]["bf_code"]		= $row["bf_code"];
			$userBets[$i]["bf_name"]		= $row["bf_name"];
			$userBets[$i]["result_class"]	= ($row["result"] == 1) ? "correct" : (($row["result"] == 2) ? "wrong" : "");
			$userBets[$i]["game_result"]	= ($row["firsthalf"] == "") ? "" : ($row["firsthalf"] . " / " . $row["fulltime"]);
			$userBets[$i]["user_score"]		= $row["score"];
			$userBets[$i]["bg_description"]	= $row["bg_description"];
			$userBets[$i]["possibility"] 	= number_format($row["probability"], 1, '.', '');
			$userBets[$i]["id"]				= $row["id"];
			$userBets[$i]["has_comment"]	= $row["has_comment"];

			$i++;
		}
		return $userBets;
	}

	public function listCurrentBets($page, $rows = 10, $orderby, $sortby, $gameDates = "", $leagueCodes = "", $gameCodes = "", $gameTitles = "", $betGroups = "", $betTypes = "", $rateTypes = "", $user_id = "", $is_current = 1)
	{
		$start = ($page - 1) * $rows;
		/*
		$query = "select g.date,g.datetime,g.leag,g.code,g.title,bg.tr_code AS bg_code,bt.description AS bet_type,ub.rate,u.username,
			l.name,l.country,rt.name AS rate_type,bf.code AS bf_code,bf.name AS bf_name,bg.description AS bg_description,
			(select COUNT(id) from tk_userbets where result=1 and betfamily_id=bf.id and ratetype_id=rt.id and user_id=u.id) AS success_bets,
			(select COUNT(id) from tk_userbets where result=2 and betfamily_id=bf.id and ratetype_id=rt.id and user_id=u.id) AS failed_bets
			from tk_userbets ub
			JOIN tk_games g on ub.game_id=g.puid
			JOIN tk_bettypes bt on ub.bet_code=bt.code
			JOIN tk_betgroups bg on bt.group_id=bg.id
			JOIN tk_leagues l on g.leag=l.code
			JOIN tk_users u on ub.user_id=u.id
			JOIN tk_ratetypes rt on rt.high>=ub.rate AND rt.low<=ub.rate
			JOIN tk_bettypefamily bf on bg.family_id=bf.id
			where g.datetime>".$this->myTime." 
			and (select count(id) from tk_userbets b where result>0 AND b.user_id=u.id and b.ratetype_id=rt.id and b.betfamily_id=bf.id)>=".$this->betLimit." ";
		
		$query .= "order by (select COUNT(id) from tk_userbets where result=1 and betfamily_id=bf.id and ratetype_id=rt.id and user_id=u.id)/((select COUNT(id) from tk_userbets where result=1 and betfamily_id=bf.id and ratetype_id=rt.id and user_id=u.id)+(select COUNT(id) from tk_userbets where result=2 and ratetype_id=rt.id and betfamily_id=bf.id and user_id=u.id)) desc ";
		*/
		$query = "select 
			g.date,
			g.datetime,
			g.leag,
			g.code,
			g.title,
			bg.tr_code AS bg_code,
			bt.description AS bet_type,
			ub.rate,
			u.id AS user_id,
			u.username,
			l.name,
			l.country,
			rt.name AS rate_type,
			bf.code AS bf_code,
			bf.name AS bf_name,
			bg.description AS bg_description,
			ub.probability,
			l.link_name,
			ub.result,
			g.link_name AS game_link,
			g.puid,
			ub.comment,
			ub.id AS bet_id,
			g.has_comment
			from tk_userbets ub
			JOIN tk_games g on ub.game_id=g.puid
			JOIN tk_bettypes bt on ub.bet_code=bt.code
			JOIN tk_betgroups bg on bt.group_id=bg.id
			JOIN tk_leagues l on g.leag=l.code
			JOIN tk_users u on ub.user_id=u.id
			JOIN tk_ratetypes rt on rt.high>=ub.rate AND rt.low<=ub.rate
			JOIN tk_bettypefamily bf on bg.family_id=bf.id
			where 1=1";

		if ($is_current == 1)
			$query .= " AND g.datetime>" . $this->myTime . " ";

		if ($gameDates != "") {
			$gameDateList = split(",", $gameDates);
			if (sizeof($gameDateList) > 0) {
				$query .= " AND g.date IN (";

				for ($i = 0; $i < sizeof($gameDateList); $i++) {
					$query .= "'" . $gameDateList[$i] . "'";
					if ($i < (sizeof($gameDateList) - 1))
						$query .= ",";
				}

				$query .= ") ";
			}
		}

		if ($leagueCodes != "") {
			$leagueCodeList = split(",", $leagueCodes);
			if (sizeof($leagueCodeList) > 0) {
				$query .= " AND g.leag IN (";
				for ($i = 0; $i < sizeof($leagueCodeList); $i++) {
					$query .= "'" . $leagueCodeList[$i] . "'";
					if ($i < (sizeof($leagueCodeList) - 1))
						$query .= ",";
				}
				$query .= ") ";
			}
		}

		if ($gameCodes != "") {
			$gameCodeList = split(",", $gameCodes);
			if (sizeof($gameCodeList) > 0) {
				$query .= " AND g.code IN (";
				for ($i = 0; $i < sizeof($gameCodeList); $i++) {
					$query .= "'" . $gameCodeList[$i] . "'";
					if ($i < (sizeof($gameCodeList) - 1))
						$query .= ",";
				}
				$query .= ") ";
			}
		}

		if ($gameTitles != "") {
			$gameTitleList = split(",", $gameTitles);
			if (sizeof($gameTitleList) > 0) {
				$query .= " AND g.puid IN (";
				for ($i = 0; $i < sizeof($gameTitleList); $i++) {
					$query .= "" . $gameTitleList[$i] . "";
					if ($i < (sizeof($gameTitleList) - 1))
						$query .= ",";
				}
				$query .= ") ";
			}
		}

		if ($betGroups != "") {
			$betGroupList = split(",", $betGroups);
			if (sizeof($betGroupList) > 0) {
				$query .= " AND bf.id IN (";
				for ($i = 0; $i < sizeof($betGroupList); $i++) {
					$query .= "" . $betGroupList[$i] . "";
					if ($i < (sizeof($betGroupList) - 1))
						$query .= ",";
				}
				$query .= ") ";
			}
		}

		if ($betTypes != "") {
			$betTypeList = split(",", $betTypes);
			if (sizeof($betTypeList) > 0) {
				$query .= " AND bg.id IN (";
				for ($i = 0; $i < sizeof($betTypeList); $i++) {
					$query .= "" . $betTypeList[$i] . "";
					if ($i < (sizeof($betTypeList) - 1))
						$query .= ",";
				}
				$query .= ") ";
			}
		}

		if ($rateTypes != "") {
			$rateTypeList = split(",", $rateTypes);
			if (sizeof($rateTypeList) > 0) {
				$query .= " AND rt.id IN (";
				for ($i = 0; $i < sizeof($rateTypeList); $i++) {
					$query .= "" . $rateTypeList[$i] . "";
					if ($i < (sizeof($rateTypeList) - 1))
						$query .= ",";
				}
				$query .= ") ";
			}
		}

		if ($user_id != "") {
			$following_query = "";
			if (is_numeric($user_id)) {
				$following_result = $this->query("select user_id from tk_followers where followed_by='" . $user_id . "'");
				while ($following_row = $this->fetchRow($following_result, MYSQL_ASSOC)) {
					$following_query .= $following_row["user_id"];
					$following_query .= ",";
				}
				$following_query = substr($following_query, 0, -1);
			}
			if ($following_query != "") {
				$query .= "and ub.user_id IN (" . $following_query . ") ";
			} else {
				$query .= "and ub.user_id=0 ";
			}
		}

		$query .= "and ub.probability is not null order by $orderby $sortby ";
		$query .= "limit $start, $rows";

		//print $query;

		$result = $this->query($query);
		$rowsCount = $this->numRows($result);
		$i = 0;
		while ($row = $this->fetchRow($result, MYSQL_ASSOC)) {
			//mb_strtoupper(strftime("%d.%m.%Y %A",strtotime($date)),"UTF-8");
			$userBets[$i]["game_date"] 		= strftime("%d.%m.%Y", $row["datetime"]);
			$userBets[$i]["league"] 		= $row["leag"];
			$userBets[$i]["link_name"] 		= $row["link_name"];
			$userBets[$i]["l_name"] 		= $this->toLatin1UpperCase($row["name"]);
			$userBets[$i]["l_country"] 		= $this->toLatin1UpperCase($row["country"]);
			$userBets[$i]["username"] 		= $row["username"];
			$userBets[$i]["user_id"] 		= $row["user_id"];
			$userBets[$i]["game_code"] 		= $row["code"];
			$userBets[$i]["title"] 			= $this->toLatin1UpperCase($row["title"]);
			$userBets[$i]["title_url"] 		= substr($row["date"], 8, 2) . "-" . substr($row["date"], 5, 2) . "-" . substr($row["date"], 0, 4) . "-" . $userBets[$i]["game_code"] . "-" . $row["game_link"] . "-" . $row["puid"];
			$userBets[$i]["bg_code"]		= $row["bg_code"];
			$userBets[$i]["bet_type"]		= mb_strtoupper($row["bet_type"], "UTF-8");
			$userBets[$i]["bet_code"]		= $row["bet_code"];
			$userBets[$i]["rate"]			= $row["rate"];
			$userBets[$i]["rate_type"]		= $this->toLatin1UpperCase($row["rate_type"]);
			$userBets[$i]["bf_code"]		= $row["bf_code"];
			$userBets[$i]["bf_name"]		= $row["bf_name"];
			$userBets[$i]["bg_description"]	= $row["bg_description"];
			$userBets[$i]["possibility"] = number_format($row["probability"], 1, '.', '');
			$userBets[$i]["result"]			= $row["result"];
			$userBets[$i]["result_class"]	= ($row["result"] == 1) ? "bet-type-green" : (($row["result"] == 2) ? "bet-type-red" : "");
			$userBets[$i]["comment"]		= $row["comment"];
			$userBets[$i]["bet_id"]			= $row["bet_id"];
			$userBets[$i]["has_comment"]	= $row["has_comment"];
			$i++;
		}
		return $userBets;
	}


	public function betsForMainPage()
	{
		$list = array("MS", "TG", "KG");
		foreach ($list as $bet) {
			$query = "select mpb.*,l.link_name,g.link_name AS game_link,g.has_comment
				from tk_mainpagebets mpb
				JOIN tk_games g ON mpb.game_id=g.puid
				JOIN tk_leagues l on mpb.league_code=l.code
				where g.datetime>=" . $this->myTime . "
				and bf_code='" . $bet . "'
				order by probability desc";
			//"order by (probability*(IF(rate>5,5,rate))) desc";

			$result = $this->query($query);
			$rowsCount = $this->numRows($result);
			$i = 0;
			while ($row = $this->fetchRow($result, MYSQL_ASSOC)) {
				$userBets[$bet][$i]["bet_id"] 			= $row["bet_id"];
				$userBets[$bet][$i]["game_date"] 		= strftime("%d.%m.%Y", $row["datetime"]);
				$userBets[$bet][$i]["league"] 			= $row["league_code"];
				$userBets[$bet][$i]["league_link"]		= $row["link_name"];
				$userBets[$bet][$i]["l_name"] 			= $this->toLatin1UpperCase($row["league_name"]);
				$userBets[$bet][$i]["l_country"] 		= "";
				$userBets[$bet][$i]["username"] 		= $row["username"];
				$userBets[$bet][$i]["user_id"] 			= $row["user_id"];
				$userBets[$bet][$i]["game_code"] 		= $row["game_code"];
				$userBets[$bet][$i]["title"] 			= $this->toLatin1UpperCase($row["title"]);
				$userBets[$bet][$i]["title_url"] 		= substr($row["date"], 8, 2) . "-" . substr($row["date"], 5, 2) . "-" . substr($row["date"], 0, 4) . "-" . $userBets[$bet][$i]["game_code"] . "-" . $row["game_link"] . "-" . $row["game_id"];
				$userBets[$bet][$i]["bg_code"]			= $row["bg_code"];
				$userBets[$bet][$i]["bet_type"]			= mb_strtoupper($row["bet_type"], "UTF-8");
				$userBets[$bet][$i]["bet_code"]			= "";
				$userBets[$bet][$i]["rate"]				= $row["rate"];
				$userBets[$bet][$i]["rate_type"]		= $this->toLatin1UpperCase($row["rate_type"]);
				$userBets[$bet][$i]["bf_code"]			= $row["bf_code"];
				$userBets[$bet][$i]["bf_name"]			= "";
				$userBets[$bet][$i]["bg_description"]	= $row["bg_name"];
				$userBets[$bet][$i]["possibility"] 		= number_format($row["probability"], 1, '.', '');
				$userBets[$bet][$i]["comment"]			= $row["comment"];
				$userBets[$bet][$i]["has_comment"]		= $row["has_comment"];

				$i++;
			}
		}
		return $userBets;
	}

	public function currentBetsCount($gameDates = "", $leagueCodes = "", $gameCodes = "", $gameTitles = "", $betGroups = "", $betTypes = "", $rateTypes = "", $user_id = "", $is_current = 1)
	{
		$query = "select ub.id
			from tk_userbets ub
			JOIN tk_games g on ub.game_id=g.puid
			JOIN tk_leagues l on g.leag=l.code
			JOIN tk_users u on ub.user_id=u.id
			where 1=1";

		if ($is_current == 1)
			$query .= " AND g.datetime>" . $this->myTime . " ";

		if ($gameDates != "") {
			$gameDateList = split(",", $gameDates);
			if (sizeof($gameDateList) > 0) {
				$query .= " AND g.date IN (";

				for ($i = 0; $i < sizeof($gameDateList); $i++) {
					$query .= "'" . $gameDateList[$i] . "'";
					if ($i < (sizeof($gameDateList) - 1))
						$query .= ",";
				}

				$query .= ") ";
			}
		}

		if ($leagueCodes != "") {
			$leagueCodeList = split(",", $leagueCodes);
			if (sizeof($leagueCodeList) > 0) {
				$query .= " AND g.leag IN (";
				for ($i = 0; $i < sizeof($leagueCodeList); $i++) {
					$query .= "'" . $leagueCodeList[$i] . "'";
					if ($i < (sizeof($leagueCodeList) - 1))
						$query .= ",";
				}
				$query .= ") ";
			}
		}

		if ($gameCodes != "") {
			$gameCodeList = split(",", $gameCodes);
			if (sizeof($gameCodeList) > 0) {
				$query .= " AND g.code IN (";
				for ($i = 0; $i < sizeof($gameCodeList); $i++) {
					$query .= "'" . $gameCodeList[$i] . "'";
					if ($i < (sizeof($gameCodeList) - 1))
						$query .= ",";
				}
				$query .= ") ";
			}
		}

		if ($gameTitles != "") {
			$gameTitleList = split(",", $gameTitles);
			if (sizeof($gameTitleList) > 0) {
				$query .= " AND g.puid IN (";
				for ($i = 0; $i < sizeof($gameTitleList); $i++) {
					$query .= "" . $gameTitleList[$i] . "";
					if ($i < (sizeof($gameTitleList) - 1))
						$query .= ",";
				}
				$query .= ") ";
			}
		}

		if ($betGroups != "") {
			$betGroupList = split(",", $betGroups);
			if (sizeof($betGroupList) > 0) {
				$query .= " AND ub.betfamily_id IN (";
				for ($i = 0; $i < sizeof($betGroupList); $i++) {
					$query .= "" . $betGroupList[$i] . "";
					if ($i < (sizeof($betGroupList) - 1))
						$query .= ",";
				}
				$query .= ") ";
			}
		}

		if ($betTypes != "") {
			$betTypeList = split(",", $betTypes);
			if (sizeof($betTypeList) > 0) {
				$query .= " AND ub.betgroup_id IN (";
				for ($i = 0; $i < sizeof($betTypeList); $i++) {
					$query .= "" . $betTypeList[$i] . "";
					if ($i < (sizeof($betTypeList) - 1))
						$query .= ",";
				}
				$query .= ") ";
			}
		}

		if ($rateTypes != "") {
			$rateTypeList = split(",", $rateTypes);
			if (sizeof($rateTypeList) > 0) {
				$query .= " AND ub.ratetype_id IN (";
				for ($i = 0; $i < sizeof($rateTypeList); $i++) {
					$query .= "" . $rateTypeList[$i] . "";
					if ($i < (sizeof($rateTypeList) - 1))
						$query .= ",";
				}
				$query .= ") ";
			}
		}

		if ($user_id != "") {
			$following_query = "";
			if (is_numeric($user_id)) {
				$following_result = $this->query("select user_id from tk_followers where followed_by='" . $user_id . "'");
				while ($following_row = $this->fetchRow($following_result, MYSQL_ASSOC)) {
					$following_query .= $following_row["user_id"];
					$following_query .= ",";
				}
				$following_query = substr($following_query, 0, -1);
			}
			if ($following_query != "") {
				$query .= "and ub.user_id IN (" . $following_query . ") ";
			} else {
				$query .= "and ub.user_id=0 ";
			}
		}

		$result = $this->query($query);
		$rowsCount = $this->numRows($result);

		return $rowsCount;
	}


	//public function userBetsCount($user_id = 0, $isCurrent = false, $league_id = 0)
	public function userBetsCount($filters)
	{
		$rows = isset($filters["rows"]) ? $filters["rows"] : 10;
		$start = ($filters["page"] - 1) * $rows;
		$query = "select ub.id
			from tk_userbets ub
			JOIN tk_games g on ub.game_id=g.puid
			JOIN tk_leagues l on g.leag=l.code
			JOIN tk_users u on ub.user_id=u.id
			JOIN tk_bettypefamily bf on ub.betfamily_id=bf.id
			JOIN tk_ratetypes rt on rt.high>=ub.rate AND rt.low<=ub.rate
			where 1=1 ";
		if ($filters["user_id"] > 0)
			$query .= "AND ub.user_id='" . $filters["user_id"] . "' ";
		if ($filters["is_current"] == true)
			$query .= "AND g.datetime>" . $this->myTime . " ";

		if ($filters["league_codes"] != "") {
			$leagueCodeList = split(",", $filters["league_codes"]);
			if (sizeof($leagueCodeList) > 0) {
				$query .= " AND l.id IN (";
				for ($i = 0; $i < sizeof($leagueCodeList); $i++) {
					$query .= "'" . $leagueCodeList[$i] . "'";
					if ($i < (sizeof($leagueCodeList) - 1))
						$query .= ",";
				}
				$query .= ") ";
			}
		}

		if ($filters["bet_groups"] != "") {
			$betGroupList = split(",", $filters["bet_groups"]);
			if (sizeof($betGroupList) > 0) {
				$query .= " AND bf.id IN (";
				for ($i = 0; $i < sizeof($betGroupList); $i++) {
					$query .= "" . $betGroupList[$i] . "";
					if ($i < (sizeof($betGroupList) - 1))
						$query .= ",";
				}
				$query .= ") ";
			}
		}

		if ($filters["rate_types"] != "") {
			$rateTypeList = split(",", $filters["rate_types"]);
			if (sizeof($rateTypeList) > 0) {
				$query .= " AND rt.id IN (";
				for ($i = 0; $i < sizeof($rateTypeList); $i++) {
					$query .= "" . $rateTypeList[$i] . "";
					if ($i < (sizeof($rateTypeList) - 1))
						$query .= ",";
				}
				$query .= ") ";
			}
		}



		$result = $this->query($query);
		$rowsCount = $this->numRows($result);

		return $rowsCount;
	}


	public function toLatin1LowerCase($string)
	{
		$dizi = array('ğ' => 'g', 'ü' => 'u', 'ş' => 's', 'ö' => 'o', 'ç' => 'c');
		return mb_strtolower(strtr($string, $dizi), "UTF-8");
	}

	public function toLatin1UpperCase($string)
	{
		$dizi = array('i' => 'İ', 'ü' => 'Ü', 'ğ' => 'Ğ', 'ü' => 'Ü', 'ç' => 'Ç', 'ö' => 'Ö', 'ş' => 'Ş');
		return mb_strtoupper(strtr($string, $dizi), "UTF-8");
	}

	public function listLeaguesOfMissingPlayers()
	{
		$result = $this->query("SELECT distinct mp.league_code 
			FROM `tk_missingplayers` mp
			order by mp.league_code");
		$i = 0;
		while ($row = $this->fetchRow($result, MYSQL_ASSOC)) {
			$league_code = $row["league_code"];
			$LeagueList[$i] = $league_code;
			$i++;
		}
		return $LeagueList;
	}

	public function listMissingPlayers($filters)
	{
		if (!isset($filters["rows"]))
			$filters["rows"] = 50;

		if (!isset($filters["page"]))
			$filters["page"] = 1;

		$start = ($filters["page"] - 1) * $filters["rows"];

		$query = "select mp.*,l.name AS league_name ,tv.value AS team_value,l.link_name
            from tk_missingplayers mp
            JOIN tk_leagues l ON mp.league_code=l.code
            LEFT OUTER JOIN tk_teamvalues tv ON (l.code=tv.league_code AND mp.team_name_url=tv.team_name_url) 
            WHERE 1=1 ";

		if ($filters["leagues"] != "") {
			$leagueCodeList = split(",", $filters["leagues"]);
			if (sizeof($leagueCodeList) > 0) {
				$query .= " AND l.code IN (";
				for ($i = 0; $i < sizeof($leagueCodeList); $i++) {
					$query .= "'" . $leagueCodeList[$i] . "'";
					if ($i < (sizeof($leagueCodeList) - 1))
						$query .= ",";
				}
				$query .= ") ";
			}
		}

		$query .= " ORDER BY mp.value/tv.value desc";
		$query .= " limit $start," . $filters["rows"];


		$result = $this->query($query);
		$rowsCount = $this->numRows($result);
		$i = 0;
		while ($row = $this->fetchRow($result, MYSQL_ASSOC)) {
			$missingPlayers[$i]["player_name"] 		= $row["player_name"];
			$missingPlayers[$i]["player_name_url"]	= $row["player_name_url"];
			$missingPlayers[$i]["team_name"] 		= $row["team_name"];
			$missingPlayers[$i]["team_name_url"]	= $row["team_name_url"];
			$missingPlayers[$i]["start"]            = $row["start_date"];
			$missingPlayers[$i]["return"]           = $row["return_date"];
			$missingPlayers[$i]["position"]         = $row["position"];
			$missingPlayers[$i]["reason"]           = $row["reason"];
			$missingPlayers[$i]["value"]            = $row["value"];
			$missingPlayers[$i]["number_of_games"]  = $row["number_of_games"];
			$missingPlayers[$i]["league_code"]      = $row["league_code"];
			$missingPlayers[$i]["link_name"]        = $row["link_name"];
			$missingPlayers[$i]["is_suspended"]     = $row["is_suspended"];
			$missingPlayers[$i]["league_name"]      = $row["league_name"];
			$missingPlayers[$i]["team_value"]       = iif(!is_numeric($row["team_value"]), 0, $row["team_value"]);

			$return = split(' ', $missingPlayers[$i]["return"]);
			if (strlen($return[0]) == 1)
				$return[0] = "0" . $return[0];
			if ($return[1] == "Oca")
				$return[1] = "01";
			else if ($return[1] == "Oca")
				$return[1] = "01";
			else if ($return[1] == "Şub")
				$return[1] = "02";
			else if ($return[1] == "Mar")
				$return[1] = "03";
			else if ($return[1] == "Nis")
				$return[1] = "04";
			else if ($return[1] == "May")
				$return[1] = "05";
			else if ($return[1] == "Haz")
				$return[1] = "06";
			else if ($return[1] == "Tem")
				$return[1] = "07";
			else if ($return[1] == "Ağu")
				$return[1] = "08";
			else if ($return[1] == "Eyl")
				$return[1] = "09";
			else if ($return[1] == "Eki")
				$return[1] = "10";
			else if ($return[1] == "Kas")
				$return[1] = "11";
			else if ($return[1] == "Ara")
				$return[1] = "12";

			$missingPlayers[$i]["return"] = $return[0] . "." . $return[1] . "." . $return[2];


			if ($missingPlayers[$i]["team_value"] == 0)
				$missingPlayers[$i]["pvr"] = "Bilinmiyor";
			else {
				$pv = iif(!is_numeric($row["value"]), 0, $row["value"]);
				if ($pv == 0) {
					$missingPlayers[$i]["pvr"] = "Bilinmiyor";
				} else {
					$missingPlayers[$i]["pvr"] = round((100 * ($pv / $missingPlayers[$i]["team_value"])), 1);
					$missingPlayers[$i]["pvr"] = "%" . number_format($missingPlayers[$i]["pvr"], 1, '.', '');
				}
			}


			if ($missingPlayers[$i]["team_value"] == 0) {
				$missingPlayers[$i]["player_value_rate"] = "Bilinmiyor";
			} else {
				$missingPlayers[$i]["player_value_rate"] = 100 * ($row["value"] / ($missingPlayers[$i]["team_value"]));
			}

			if ($row["value"] > 1000000) {
				$missingPlayers[$i]["value"] = number_format(round($row["value"] / 1000000, 1), 1, '.', '') . " Mio €";
			} else if ($row["value"] > 1000) {
				$missingPlayers[$i]["value"] = number_format(round($row["value"] / 1000), 0, '.', '') . " Bin €";
			} else if ($row["value"] > 0) {
				$missingPlayers[$i]["value"] = $row["value"] . " €";
			} else {
				$missingPlayers[$i]["value"] = $row["value"];
			}


			if ($row["team_value"] > 1000000) {
				$missingPlayers[$i]["team_value"] = number_format(round($row["team_value"] / 1000000, 1), 1, '.', '') . " Mio €";
			} else if ($row["team_value"] > 1000) {
				$missingPlayers[$i]["team_value"] = number_format(round($row["team_value"] / 1000), 0, '.', '') . " Bin €";
			} else if ($row["team_value"] > 0) {
				$missingPlayers[$i]["team_value"] = $row["valteam_valueue"] . " €";
			} else {
				$missingPlayers[$i]["team_value"] = $row["team_value"];
			}


			$i++;
		}

		return $missingPlayers;
	}

	public function __construct()
	{
		mysqlClass::__construct();

		//TR +3 te kaldığı sürece
		//$this->myTime = time()+(3*3600); //Kış saatinde
		$this->myTime = time(); //yaz saatinde

		$this->betLimit = 7;

		$this->dayStart = mktime(0, 0, 0);

		$this->monthStart = mktime(0, 0, 0, date("m"), 1, date("Y"));

		/*
		$this->getFileName();
		
		if($this->fileName=='index.php')
			$haveToLogin = false;
			
		if(isset($_GET['r']))
			$_SESSION['newUserReference'] = htmlspecialchars($_GET['r']);
		elseif(isset($_SERVER['HTTP_REFERER']) && stripos($_SERVER['HTTP_REFERER'], 'google.com')!==false)
			$_SESSION['newUserReference'] = 'GoogleOrganic';
		elseif(isset($_SERVER['HTTP_REFERER']) && stripos($_SERVER['HTTP_REFERER'], 'facebook.com')!==false)
			$_SESSION['newUserReference'] = 'FacebookBar';

		$online=true;
		
		if($haveToLogin)
		{
		
			$online = $this->isOnline();
		
			if($updateSession && $online)
			
				$updated = $this->updateUserSiteOnline();
		}

		
		if(isset($_GET['ref']))
			if($_GET['ref']=='timeout')
				header('ajaxerror: true');
			elseif($_GET['ref']=='loginelse')
				header('loginelse: true');
		
		if(isset($_SESSION['performer']) && $this->fileName=='index.php')
		{
			header('location: onlineusers.php'); exit;
		}
		elseif(isset($_SESSION['user']) && $this->fileName=='index.php')
		{
			header('location: onlineperformers.php'); exit;
		}
		elseif($haveToLogin==true && $online==false && $this->fileName!='index.php')
		{
			if($this->loginAtAnotherPC)
				header('location: http://'.$_SERVER['HTTP_HOST'].'/index.php?ref=loginelse');
			else
				header('location: http://'.$_SERVER['HTTP_HOST'].'/index.php?ref=timeout');
			
			exit;
		}

//echo "<pre style='background:#FFF; font-size:20px;'>"; print_r($_SERVER); echo "</pre>";
		*/
	}
}
