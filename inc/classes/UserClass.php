<?PHP
date_default_timezone_set('Europe/Istanbul');
include_once 'AdminClass.php';

class userClass extends adminClass
{
	public $userId;
	public $myTime;

	public function getCommentForBet($bet_id)
	{
		
		$c_result = $this->query("select comment from tk_userbets where id='".$bet_id."'");
		$rows = $this->numRows($c_result);
		while($c_row = $this->fetchRow($c_result))
		{				
			$comment = $c_row[0];			
		}		
		
		return $comment;
	}	
	
	public function deleteCommentForBet($bet_id)
	{
		$this->query("UPDATE tk_userbets SET comment='' where id=".$bet_id."");
		$this->query("UPDATE tk_mainpagebets SET comment='' where bet_id=".$bet_id."");	
		
		
		$game_result = $this->query("select id from tk_userbets where comment!='' AND game_id=(select game_id from tk_userbets where id=".$bet_id.")");
		$game_rows = $this->fetchRow($game_result);
		if ($game_rows == 0)
		{
			$this->query("UPDATE tk_games SET has_comment=0 where puid=(select game_id from tk_userbets where id=".$bet_id.")");
		}		
	}
	
	public function addCommentForBet($comment,$bet_id)
	{
		$this->query("UPDATE tk_userbets SET comment='".$comment."' where id=".$bet_id."");
		$this->query("UPDATE tk_mainpagebets SET comment='".$comment."' where bet_id=".$bet_id."");
		$this->query("UPDATE tk_games SET has_comment=1 where puid=(select game_id from tk_userbets where id=".$bet_id.")");
	}
	
	public function userFollowers($login_user_id)
	{
		$login_f_list[] = null;
		if ($login_user_id != "")
		{
			$f_result = $this->query("select user_id from tk_followers where followed_by='".$login_user_id."'");
			while ($f_row = $this->fetchRow($f_result))
			{				
				$login_f_list[] = $f_row[0];
			}
		}
		
		$result = $this->query("select u.id,u.username,u.profile_pic 
			from tk_followers f
			JOIN tk_users u on f.followed_by=u.id
			where f.user_id='".$this->userId."'");
		$i = 0;
		while($row = $this->fetchRow($result))
		{
			$FollowerList[$i]["id"]				= $row[0];
			$FollowerList[$i]["username"]		= $row[1];
			$FollowerList[$i]["profile_pic"]	= ($row[2] == "") ? "tahminkrali.png" : $row[2];
			
			if ($login_user_id == "")
				$FollowerList[$i]["login_f_status"] = "user";
			else if ($login_user_id == $row[0])
				$FollowerList[$i]["login_f_status"] = "user";
			else
				$FollowerList[$i]["login_f_status"]	= (in_array($row[0],$login_f_list) == true) ? "following" : "not";
			$i++;
		}
		return $FollowerList;
	}
	
	public function userFollowing($login_user_id)
	{
		$login_f_list[] = null;
		if ($login_user_id != "")
		{
			$f_result = $this->query("select user_id from tk_followers where followed_by='".$login_user_id."'");
			while ($f_row = $this->fetchRow($f_result))
			{				
				$login_f_list[] = $f_row[0];
			}
		}
		
		$result = $this->query("select u.id,u.username,u.profile_pic 
			from tk_followers f
			JOIN tk_users u on f.user_id=u.id
			where f.followed_by='".$this->userId."'");
		$i = 0;
		while($row = $this->fetchRow($result))
		{
			$FollowingList[$i]["id"]				= $row[0];
			$FollowingList[$i]["username"]			= $row[1];
			$FollowingList[$i]["profile_pic"]		= ($row[2] == "") ? "tahminkrali.png" : $row[2];
			
			if ($login_user_id == "")
				$FollowingList[$i]["login_f_status"] = "user";
			else if ($login_user_id == $row[0])
				$FollowingList[$i]["login_f_status"] = "user";
			else
				$FollowingList[$i]["login_f_status"] = (in_array($row[0],$login_f_list) == true) ? "following" : "not";
			$i++;
		}
		
		return $FollowingList;
	}	
	
	public function followUser($follow)
	{
		$this->query("REPLACE INTO tk_followers (user_id,followed_by,creation_date) VALUES ('".$follow."','".$this->userId."','".time()."')");
	}
	
	public function unfollowUser($follow)
	{
		$this->query("DELETE FROM tk_followers where user_id='".$follow."' and followed_by='".$this->userId."'");
	}	
	
	public function unsubscribeMailings()
	{
		$this->query("UPDATE tk_users SET allow_mail=0 where id=".$this->userId."");
	}
	
	public function banUser()
	{
		$this->query("UPDATE tk_users SET is_suspended=1 where id=".$this->userId."");
	}
	
	public function permitUser()
	{
		$this->query("UPDATE tk_users SET is_suspended=0 where id=".$this->userId."");
	}		
	
	public function userRatesByBetFamilyRateType()
	{
		$query = "select bf_id,rt_id,rate,bf.name AS bf_name 
			from tk_userbetfamilyratetypescoresrates ubfrtsr
			JOIN tk_bettypefamily bf ON ubfrtsr.bf_id=bf.id
			where user_id='".$this->userId."' order by bf_id,rt_id";
		$result = $this->query($query);
		$i = 0;
		while($row = $this->fetchRow($result))
		{
			$bf_id		= $row[0];
			$rt_id		= $row[1];
			$rate		= $row[2];
			$bf_name	= $row[3];
			$UserRatesByBetFamilyRateType[$bf_id][$rt_id]["rate"] 		= "%".number_format($rate,1,'.','');
			$UserRatesByBetFamilyRateType[$bf_id][$rt_id]["bf_name"] 	= mb_strtoupper($bf_name, "UTF-8");
			$i++;
		}
		return $UserRatesByBetFamilyRateType;
	
	}		
	
	public function userScoresByBetFamilyRateType()
	{
		$query = "select bf_id,rt_id,score,bf.name AS bf_name 
			from tk_userbetfamilyratetypescoresrates ubfrtsr
			JOIN tk_bettypefamily bf ON ubfrtsr.bf_id=bf.id
			where user_id='".$this->userId."' order by bf_id,rt_id";
		$result = $this->query($query);
		$i = 0;
		while($row = $this->fetchRow($result))
		{
			$bf_id		= $row[0];
			$rt_id		= $row[1];
			$score		= $row[2];
			$bf_name	= $row[3];
			$UserScoresByBetFamilyRateType[$bf_id][$rt_id]["score"] 	= $score;
			$UserScoresByBetFamilyRateType[$bf_id][$rt_id]["bf_name"] 	= mb_strtoupper($bf_name, "UTF-8");
			$i++;
		}
		return $UserScoresByBetFamilyRateType;
	
	}	
	
	public function userScoresRatesBySelectedLeague($league_id)
	{
		$query = "select bf_id,rt_id,usbl.score,usbl.success_rate,usbl.bets
			from tk_userscoresbyleague usbl
			where usbl.bf_id!=0
			and usbl.user_id='".$this->userId."'
			and usbl.league_id='".$league_id."'
			ORDER BY bf_id,rt_id";	
		$result = $this->query($query);
		$i = 0;		
		while($row = $this->fetchRow($result))
		{
			$bf_id	= $row[0];
			$rt_id	= $row[1];
			$UserScoresRatesBySelectedLeague[$bf_id][$rt_id]["score"]	= $row[2];
			$UserScoresRatesBySelectedLeague[$bf_id][$rt_id]["rate"]	= number_format($row[3],1,'.','');
			$UserScoresRatesBySelectedLeague[$bf_id][$rt_id]["bets"]	= $row[4];
		}
		
		for ($i=1; $i<4; $i++)
		{
			for ($j=1; $j<6; $j++)
			{
				if ($UserScoresRatesBySelectedLeague[$i][$j]["score"] == "")
				{
					$UserScoresRatesBySelectedLeague[$i][$j]["score"] = 0;
					$UserScoresRatesBySelectedLeague[$i][$j]["rate"] = number_format(0,1,'.','');
					$UserScoresRatesBySelectedLeague[$i][$j]["bets"]  = 0;
				}
			}
		}
		
		
		return $UserScoresRatesBySelectedLeague;
	}
	
	
	public function userScoresByLeague($filters)
	{
		$rows = isset($filters["rows"]) ? $filters["rows"] : 10;
		$start = ($filters["page"]-1)*$rows;	
		
		$r_query = "select usbl.update_time
			from tk_userscoresbyleague usbl
			where usbl.user_id=".$this->userId."
			and usbl.bf_id!=0
			and bets>1
			and usbl.rt_id IN (2,3)";
		$r_result = $this->query($r_query);
		$r_rows = $this->numRows($r_result);
	
		$query = "select l.name,l.id,l.code,usbl.score,usbl.avg_rate,usbl.success_rate,usbl.bets,usbl.update_time,bf.name AS bf_name,rt.name AS rt_name,l.link_name
			from tk_userscoresbyleague usbl
			LEFT OUTER JOIN tk_leagues l on usbl.league_id=l.id
			LEFT OUTER JOIN tk_bettypefamily bf on usbl.bf_id=bf.id
			LEFT OUTER JOIN tk_ratetypes rt on usbl.rt_id=rt.id
			where usbl.user_id=".$this->userId."
			and usbl.bf_id!=0
			and usbl.rt_id IN (2,3)
			and usbl.bets>1
			order by score desc
			limit ".$start.", ".$rows."";
			
		$result = $this->query($query);
		$i = 0;
		while($row = $this->fetchRow($result))
		{
			$UserScoresByBetLeague[$i]["league_name"]	= mb_strtoupper(str_replace("i","İ",$row[0]), "UTF-8");
			$UserScoresByBetLeague[$i]["link_name"]		= $row[10];
			$UserScoresByBetLeague[$i]["league_id"]		= $row[1];
			$UserScoresByBetLeague[$i]["league_code"]	= $row[2];
			$UserScoresByBetLeague[$i]["total_score"] 	= $row[3];
			$UserScoresByBetLeague[$i]["rate_avg"] 		= $row[4];
			$UserScoresByBetLeague[$i]["success_rate"] 	= number_format($row[5],1,'.','');
			$UserScoresByBetLeague[$i]["total_bets"]	= $row[6];
			$UserScoresByBetLeague[$i]["update_time"]	= $row[7];			
			$UserScoresByBetLeague[$i]["bf_name"]	= mb_strtoupper(str_replace("i","İ",$row[8]), "UTF-8");
			$UserScoresByBetLeague[$i]["rt_name"]	= mb_strtoupper(str_replace("i","İ",$row[9]), "UTF-8");
			$i++;
		}
		
		$return["UserScoresByBetLeague"] 	= $UserScoresByBetLeague;
		$return["rows"] 					= $r_rows;

		return $return;	
	}
	
	public function userScoresByBetFamily()
	{
		$query = "select bf.name,usbf.score,usbf.avg_rate,usbf.success_rate,usbf.bets,
			usbf.score_month,usbf.bets_month,usbf.score_year,usbf.update_time,bf.code		
			from tk_userscoresbybetfamily usbf
			LEFT OUTER JOIN tk_bettypefamily bf on usbf.betfamily_id=bf.id
			where usbf.user_id=".$this->userId."
			order by score desc";
		$result = $this->query($query);
		$i = 0;
		while($row = $this->fetchRow($result))
		{
			$UserScoresByBetFamily[$i]["bf_name"] 		= mb_strtoupper($row[0], "UTF-8");
			$UserScoresByBetFamily[$i]["total_score"] 	= $row[1];
			$UserScoresByBetFamily[$i]["rate_avg"] 		= $row[2];
			$UserScoresByBetFamily[$i]["success_rate"] 	= number_format($row[3],1,'.','');
			$UserScoresByBetFamily[$i]["total_bets"]	= $row[4];
			$UserScoresByBetFamily[$i]["score_month"]	= $row[5];
			$UserScoresByBetFamily[$i]["bets_month"]	= $row[6];
			$UserScoresByBetFamily[$i]["score_year"]	= $row[7];
			$UserScoresByBetFamily[$i]["update_time"]	= $row[8];
			$UserScoresByBetFamily[$i]["bf_code"]		= strtolower($row[9]);
			$i++;
		}


		return $UserScoresByBetFamily;
	
	}
	
	public function userScoresByRateType()
	{
		$query = "select rt.name,usrt.score,usrt.avg_rate,usrt.success_rate,usrt.bets,
			usrt.score_month,usrt.bets_month,usrt.score_year,usrt.update_time
			from tk_userscoresbyratetype usrt
			JOIN tk_ratetypes rt on usrt.ratetype_id=rt.id
			where usrt.user_id=".$this->userId."
			order by score desc";
		$result = $this->query($query);
		$i = 0;
		while($row = $this->fetchRow($result))
		{
			$UserScoresByBetFamily[$i]["rt_name"] 		= mb_strtoupper($row[0], "UTF-8");
			$UserScoresByBetFamily[$i]["total_score"] 	= $row[1];
			$UserScoresByBetFamily[$i]["rate_avg"] 		= $row[2];
			$UserScoresByBetFamily[$i]["success_rate"] 	= number_format($row[3],1,'.','');
			$UserScoresByBetFamily[$i]["total_bets"]	= $row[4];
			$UserScoresByBetFamily[$i]["score_month"]	= $row[5];
			$UserScoresByBetFamily[$i]["bets_month"]	= $row[6];
			$UserScoresByBetFamily[$i]["score_year"]	= $row[7];
			$UserScoresByBetFamily[$i]["update_time"]	= $row[8];
			$i++;
		}


		return $UserScoresByBetFamily;
	
	}	
	
	
	public function updateUser($UpdateUserInfo)
	{
		$userInfo = $this->userInfo();
		
		if ($userInfo["first_name"] != $UpdateUserInfo["first_name"])
			$query .= "first_name='".$UpdateUserInfo["first_name"]."',";
			
		if ($userInfo["last_name"] != $UpdateUserInfo["last_name"])
			$query .= "last_name='".$UpdateUserInfo["last_name"]."',";
			
		if ($userInfo["username"] != $UpdateUserInfo["username"])
			$query .= "username='".$UpdateUserInfo["username"]."',";
			
		if ($userInfo["email_address"] != $UpdateUserInfo["email"])
			$query .= "email_address='".$UpdateUserInfo["email"]."',";
			
		if ($userInfo["mobile_number"] != $UpdateUserInfo["gsm_phone"])
			$query .= "mobile_number='".$UpdateUserInfo["gsm_phone"]."',";
			
		if ($userInfo["tckn"] != $UpdateUserInfo["tckn"])
			$query .= "tckn='".$UpdateUserInfo["tckn"]."',";
			
		if ($userInfo["birth_date"] != $UpdateUserInfo["birth_date"])
			$query .= "birth_date='".$UpdateUserInfo["birth_date"]."',";
			
		if ($userInfo["tckn_validated"] != $UpdateUserInfo["tckn_validated"])
			$query .= "tckn_validated='".$UpdateUserInfo["tckn_validated"]."',";
			
		if ($userInfo["show_name"] != $UpdateUserInfo["show_name"])
			$query .= "show_name='".$UpdateUserInfo["show_name"]."',";

		if ($userInfo["allow_mail"] != $UpdateUserInfo["allow_mail"])
			$query .= "allow_mail='".$UpdateUserInfo["allow_mail"]."',";

		if ($userInfo["allow_sms"] != $UpdateUserInfo["allow_sms"])
			$query .= "allow_sms='".$UpdateUserInfo["allow_sms"]."',";			
		
		if ($query != "")
		{
			$query = substr($query,0,strlen($query)-1);			
			$query = "UPDATE tk_users SET ".$query." where id='".$UpdateUserInfo["user_id"]."'";
			$result = $this->query($query);
			if($this->affectedRows($result) == 1)
			{
				return true;
			}
			else
			{
				return false;
			}			
		}
		else
		{
			return true;
		}
		
	}
	
	public function changePassword($oldPass,$newPass)
	{		
		$query = "select * from tk_users where id='".$this->userId."' and password=md5('".$oldPass."')";
		$result = $this->query($query);
		$rows = $this->numRows($result);
		
		if (strlen($newPass) < 6)
		{
			$changePasswordResult['result'] = false;
			$changePasswordResult['errMsg'] = "Şifreniz 6 karakterden kısa olamaz.";		
		}
		else
		{
			if ($rows == 1)
			{
				$update_result = $this->query("UPDATE tk_users SET password=MD5('".$newPass."') where id='".$this->userId."'");
				if($this->affectedRows($update_result) == 1)
				{
					$changePasswordResult['result'] = true;
					$changePasswordResult['errMsg'] = "Şifreniz değiştirilmiştir.";
				}
				else
				{
					$changePasswordResult['result'] = false;
					$changePasswordResult['errMsg'] = "Bir hata oluştu, şifre güncellenemedi.";
				}
			}
			else
			{
				$changePasswordResult['result'] = false;
				$changePasswordResult['errMsg'] = " Mevcut şifre hatalı, tekrar kontrol ediniz.";
			}
		}
			
		return $changePasswordResult;
	}
	
	public function userInfo()
	{
		$result = $this->query("SELECT *,
			(select count(id) from tk_userbets where user_id=u.id) AS user_bets,
		 	(select count(id) from tk_userbets where user_id=u.id and result=1) AS user_success_bets,
			(select count(id) from tk_userbets where user_id=u.id and result=2) AS user_failed_bets,
			IFNULL(profile_pic,'tahminkrali.png') AS profile_picture
			from tk_users u where u.id=".$this->userId);
		if($result===false || $this->numRows($result)!=1)
			return false;
		
		return $this->fetchRow($result, MYSQL_ASSOC);
	}
	
	public function userBetLeagues()
	{
		$result = $this->query("select l.name,l.code,COUNT(b.id) 
		from tk_userbets b
		JOIN tk_users u on b.user_id=u.id
		JOIN tk_games g on g.puid=b.game_id
		JOIN tk_leagues l on g.leag=l.code
		where u.id=".$this->userId."
		GROUP BY l.name,l.code
		ORDER BY COUNT(b.id) desc
		limit 3");
		if($result)
		{
			$i=0;
			while($row = $this->fetchRow($result))
			{
				$UserBetLeagues[$i]["league_name"] = $row[0];
				$UserBetLeagues[$i]["league_code"] = $row[1];
				$UserBetLeagues[$i]["bet_count"] = $row[2];
				$i++;
			}
		
		}
		return $UserBetLeagues;
	}
	
	public function userPopularBets()
	{
		$result = $this->query("select bf.name,bf.code,COUNT(b.id)
		from tk_userbets b
		JOIN tk_users u on b.user_id=u.id
		JOIN tk_bettypes bt on b.bet_code=bt.code
		JOIN tk_betgroups bg on bg.id=bt.group_id
		JOIN tk_bettypefamily bf on bg.family_id=bf.id		
		where u.id=".$this->userId."
		GROUP BY bf.name,bf.code
		ORDER BY COUNT(b.id) desc
		limit 3");
		if($result)
		{
			$i=0;
			while($row = $this->fetchRow($result))
			{
				$UserPopularBets[$i]["bet_name"] = $row[0];
				$UserPopularBets[$i]["bet_code"] = $row[1];
				$UserPopularBets[$i]["bet_count"] = $row[2];
				$i++;
			}
		
		}
		return $UserPopularBets;
	}

	public function userBetsByLeague()
	{
		$result = $this->query("select l.name,l.code,bf.code,COUNT(b.id)
		from tk_userbets b
		JOIN tk_games g on b.game_id=g.puid
		JOIN tk_leagues l on g.leag=l.code
		JOIN tk_users u on b.user_id=u.id
		JOIN tk_bettypes bt on b.bet_code=bt.code
		JOIN tk_betgroups bg on bg.id=bt.group_id
		JOIN tk_bettypefamily bf on bg.family_id=bf.id		
		where u.id=".$this->userId."
		GROUP BY l.name,l.code,bf.code
		ORDER BY l.name");
		if($result)
		{
			$i=0;
			while($row = $this->fetchRow($result))
			{
				$bf_code = $row[2];
				$UserBetsByLeague[$i]["leag"] 					= $row[0];
				//$UserBetsByLeague[$i]["leag_code"] 				= $row[1];
				$UserBetsByLeague[$i]["leag_code"][$bf_code] 	= $row[3];
				$i++;
			}
		
		}
		return $UserBetsByLeague;
	}		
	
	public function suspendUser()
	{
		$result = $this->query("UPDATE users SET suspended=1 WHERE id=".$this->userId);
		if($result===false)
			return false;
		else
		{
			$time = $this->myTime;
			$this->query("INSERT INTO administratorActionLogs(Aid, Uid, Pid, action, date, ip, actionLevel) VALUES(".$_SESSION['admin']['aid'].", ".$this->userId.", 0, 'Üye Suspend edildi!', $time, '".addslashes(htmlspecialchars($_SERVER['REMOTE_ADDR']))."', 5)");
			return true;
		}
	}
	
	public function unsuspendUser()
	{
		$result = $this->query("UPDATE users SET suspended=0 WHERE id=".$this->userId);
		if($result===false)
			return false;
		else
			return true;
	}
	
	
	public function changeUserType($type)
	{
		$type = (int) $type;
		$time = $this->myTime;
		
		if($this->userHadBoughtChips()===false)
			throw new Exception("Bu üye daha önce süre almadığından istediğiniz değişiklik yapılamaz!");
			
		$result = $this->query("UPDATE userActive SET userType=$type, endDate=IF(endDate<$time, $time, endDate) WHERE Uid=".$this->userId);
		if($this->affectedRows($result)==1)
			return true;
		else
			return false;
	}
	
	
	private function findId($userName)
	{
		$userName = get_magic_quotes_gpc()==false ? addslashes(htmlspecialchars($userName)) : htmlspecialchars($userName);
		$result = $this->fetchRow($this->query("SELECT id FROM users WHERE uniqueUserName='$userName'"));
		return $result[0];
	}


	public function userBetBoard()
	{
		$result = $this->query("select l.name,bf.code,COUNT(b.id) 
			from tk_userbets b
			JOIN tk_users u ON b.user_id=u.id
			JOIN tk_games g on b.game_id=g.puid
			JOIN tk_leagues l on g.leag=l.code
			JOIN tk_bettypes bt on b.bet_code=bt.code
			JOIN tk_betgroups bg on bt.group_id=bg.id
			JOIN tk_bettypefamily bf on bg.family_id=bf.id
			where u.id=".$this->userId."
			GROUP BY l.name,bf.code
			ORDER BY l.name
			");
		if($result)
		{
			$i=0;
			while($row = $this->fetchRow($result))
			{
				$userBetBoard[$i]["league_name"] 	= $row[0];
				$userBetBoard[$i][$row[1]] 			= $row[2];
				//$userBetBoard[$i]["bet_type"]		= $row[1];
				//$userBetBoard[$i]["bet_count"] 		= $row[2];
				$i++;
			}
		
		}
		return $userBetBoard;
	}
	
	
	public function listBetsInBasket()
	{
		if (isset($this->userId))
		{
			/*
			$this->query("delete from tk_basket b
				JOIN tk_games g on g.puid=b.game_id
				where g.datetime<".$this->myTime."
				AND b.user_id='".$this->userId."'");
			*/
		}
	
		$result = $this->query("select g.code,g.title,b.rate,bg.description AS bg_name,bt.description AS bt_name,bf.code AS bf_code,b.id AS basket_id,bt.code AS bt_code,bg.code AS bg_code,bg.tr_code 
			from tk_basket b
			JOIN tk_games g on b.game_id=g.puid
			JOIN tk_bettypes bt on b.bet_code=bt.code
			JOIN tk_betgroups bg on bt.group_id=bg.id
			JOIN tk_bettypefamily bf on bg.family_id=bf.id
			where b.user_id='".$this->userId."'
			and g.datetime>='".$this->myTime."'
			order by b.id");
		if($result)
		{
			$i=0;
			while($row = $this->fetchRow($result))
			{
				$UserBasketBets[$i]["game_code"] = $row[0];
				$UserBasketBets[$i]["game_title"] = $row[1];
				$UserBasketBets[$i]["bet_rate"] = $row[2];
				$UserBasketBets[$i]["bg_name"] = $row[3];
				$UserBasketBets[$i]["bt_name"] = $row[4];
				$UserBasketBets[$i]["bf_code"] = $row[5];
				$UserBasketBets[$i]["basket_id"] = $row[6];
				$UserBasketBets[$i]["bt_code"] = $row[7];
				$UserBasketBets[$i]["bg_code"] = $row[8];
				$UserBasketBets[$i]["bg_trcode"] = $row[9];
				$i++;
			}
		
		}
		return $UserBasketBets;		
	}
	
	public function addToBasket($game)
	{
		$values = split('_',$game);
		$game_puid 			= $values[0];
		$game_code 			= $values[1];
		$bet_group_code 	= $values[2];
		$bet_type_code 		= $values[3];
		
		
		//sepette var mı kontrol edilir
		$result = $this->query("select id from tk_basket where game_id='".$game_puid."' and user_id='".$this->userId."' and bet_code='".$bet_type_code."'");
		if ($this->numRows($result) == 0)
		{		
			//daha önceden tahöin yapılmış mı kontrol edilir
			$bets_result = $this->query("select id from tk_userbets where game_id='".$game_puid."' and user_id='".$this->userId."' and bet_code='".$bet_type_code."'");
			if ($this->numRows($bets_result) == 0)
			{				
				
				//tahmin daha önceden yoksa sepete eklenir, ancak önce güncel tahmin oranı çekilir
				$result = $this->fetchRow($this->query("select br.rate from tk_betrates br
					JOIN tk_bettypes bt on br.bet_type=bt.id 
					where game_id='".$game_puid."' and bt.code='".$bet_type_code."'"));
	
				$bet_rate = $result[0];						
				$this->query("INSERT INTO tk_basket (game_id,user_id,bet_code,rate) VALUES ('".$game_puid."','".$this->userId."','".$bet_type_code."','".$bet_rate."')");				
				return true;
			}
			else			
				return false;			
		}
		else
			return false;
	}
	
	public function deleteFromBasket($basket_id)
	{
		$result = $this->query("select g.puid,g.code,b.bet_code,bg.code AS bg_code
			from tk_basket b
			JOIN tk_games g ON g.puid=b.game_id
			JOIN tk_bettypes bt ON b.bet_code=bt.code
			JOIN tk_betgroups bg on bt.group_id=bg.id
			where b.id='".$basket_id."'");//4470
		if ($this->numRows($result) == 1)
		{
			while ($row = $this->fetchRow($result))
			{
				$puid = $row[0];
				$code = $row[1];
				$bg_code = $row[3];
				$bet_type = $row[2];
			}
			$game_key = $puid."_".$code."_".$bg_code."_".$bet_type;
		}
		$this->query("DELETE FROM tk_basket where user_id='".$this->userId."' AND id='".$basket_id."'");
		return $game_key;
	}	
	
	public function deleteFromBasketWithGameID($game_puid,$bet_code)
	{
		$this->query("DELETE FROM tk_basket where game_id='".$game_puid."' AND bet_code='".$bet_code."'");
	}		
	
	public function cleanBasket()
	{
		$this->query("DELETE FROM tk_basket where user_id='".$this->userId."'");
	}		
	
	
	public function addBasketItemsToBets()
	{
		$result = $this->query("select b.game_id,b.bet_code,br.rate,bt.id AS bt_id,
			l.code AS league_code,l.country,bg.id AS bg_id,bf.id AS bf_id,rt.id AS rt_id
			from tk_basket b
			JOIN tk_games g on b.game_id=g.puid
			JOIN tk_leagues l on g.leag=l.code
			JOIN tk_bettypes bt on b.bet_code=bt.code
			JOIN tk_betgroups bg on bt.group_id=bg.id
			JOIN tk_betrates br on br.game_id=g.puid AND br.bet_type=bt.id
			JOIN tk_bettypefamily bf on bg.family_id=bf.id
			JOIN tk_ratetypes rt on rt.high>=b.rate AND rt.low<=b.rate
			where b.user_id='".$this->userId."'
			AND g.datetime>='".$this->myTime."'");
			while($row = $this->fetchRow($result))			
		{
			$game_puid 		= $row[0];
			$bet_code 		= $row[1];
			$bet_rate 		= $row[2];
			$bt_id	 		= $row[3];
			$league_code	= $row[4];
			$country		= $row[5];
			$bg_id			= $row[6];
			$bf_id			= $row[7];
			$rt_id			= $row[8];
			
			
			$this->query("INSERT INTO tk_userbets 
			(game_id,user_id,bet_code,rate,bet_date,result,
			league_code,country,bettype_id,betgroup_id,betfamily_id,ratetype_id)
			VALUES ('".$game_puid."','".$this->userId."','".$bet_code."','".$bet_rate."','".$this->myTime."',0,
			'".$league_code."','".$country."','".$bt_id."','".$bg_id."','".$bf_id."','".$rt_id."')");
		}
		
		$this->cleanBasket();
	}			
	
	public function getStats()
	{
		$result = $this->query("select rank,total_bets,total_score from tk_liveranking where user_id='".$this->userId."'");
		if($result)
		{
			$i=0;
			while($row = $this->fetchRow($result))
			{
				$UserStats["rank"] = $row[0];
				$UserStats["total_bets"] = $row[1];
				$UserStats["total_score"] = $row[2];
				$i++;
			}
		
		}
		
		
		$result = $this->query("select atb_league,atb_category_id,atb_ratetype_id,atb_bet_count,atb_success_rate,atb_score,atb_last_update_time,rt.name,bf.name,l.link_name
			from tk_users u 
			LEFT OUTER JOIN tk_leagues l ON l.code=atb_league
			LEFT OUTER JOIN tk_bettypefamily bf ON bf.id=u.atb_category_id
			LEFT OUTER JOIN tk_ratetypes rt ON rt.id=u.atb_ratetype_id
			where u.id='".$this->userId."'");
		if($result)
		{
			$i=0;
			while($row = $this->fetchRow($result))
			{
				$UserStats["atb_league"] 			= $row[0];
				$UserStats["atb_league_link"]		= $row[9];
				$UserStats["atb_category_id"] 		= $row[8];
				$UserStats["atb_ratetype_id"] 		= $row[7];
				$UserStats["atb_bet_count"] 		= $row[3];
				$UserStats["atb_success_rate"] 		= number_format($row[4],1,'.','');
				$UserStats["atb_score"] 			= number_format($row[5],2,'.','');				
				$UserStats["atb_last_update_time"] 	= $row[6];				
				$i++;
			}
		
		}		
		return $UserStats;	
	
	}
	
	public function __construct($key)
	{
		adminClass::__construct();
		
		if(is_numeric($key))
			$this->userId = (int) $key;
		elseif(!empty($key))
			$this->userId = $this->findId($key);
		else
			$this->userId = 0;
			
		//$this->myTime = time()+(3*3600);
		$this->myTime = time();
	}
	
	
	
	
/*	public function __construct($userid)
	{
		adminClass::__construct();
		$this->userId = intval($userid);
	}

*/
}


?>