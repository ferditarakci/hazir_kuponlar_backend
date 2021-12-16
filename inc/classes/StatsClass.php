<?PHP
date_default_timezone_set('Europe/Istanbul');

class statisticsClass extends adminClass
{
	private $yesterdayStart, $todayStart, $monthStart, $now, $dayStart, $dayOver, $previousDayStart;
	public $recentStats, $dailyStats, $monthlyStats, $generalStats, $memberStats;
		
	private function dailyStatistics()
	{
		$result = $this->query("SELECT COUNT(id) FROM tk_users WHERE registration_date BETWEEN ".$this->dayStart." AND ".$this->dayOver);
		if($result)
		{
			$row = $this->fetchRow($result);
			$this->dailyStats['todaySignedUpUsers'] = $row[0];
		}
		else
		{
			$this->dailyStats['todaySignedUpUsers'] = 0;
		}
		
		$result = $this->query("SELECT COUNT(id) FROM tk_userbets WHERE bet_date BETWEEN ".$this->dayStart." AND ".$this->dayOver);
		if($result)
		{
			$row = $this->fetchRow($result);
			$this->dailyStats['todayRegisteredBets'] = $row[0];
		}		



	}
	
	private function monthlyStatistics()
	{
		$result = $this->query("SELECT COUNT(id) FROM tk_users WHERE registration_date BETWEEN ".$this->monthStart." AND ".$this->dayOver);
		if($result)
		{
			$row = $this->fetchRow($result);
			$this->monthlyStats['signedUpUsers'] = $row[0];
		}

	}
	
	private function recentStatistics()
	{

		$result = $this->query("SELECT COUNT(id) FROM tk_users where is_online=1");
		if($result)
		{
			$row = $this->fetchRow($result);
			$this->recentStats['totalUser']	= number_format($row[0],0, '', '.');
		}
		
		$result = $this->query("SELECT COUNT(code) FROM tk_games where datetime>=".$this->now." AND datetime<".$this->dayOver);
		if($result)
		{
			$row = $this->fetchRow($result);
			$this->recentStats['totalGames']	= number_format($row[0],0, '', '.');
		}		

	}
	
	private function generalStatistics()
	{
		
		
//		$this->query("SELECT COUNT(id) FROM users");
//		$this->query("SELECT (SELECT SUM(earnedChips*cost) FROM session)-(SELECT SUM(amount) FROM payments)");
//		
//		$this->query("SELECT SUM(chips), (SUM(chips)*30) FROM userActive");// biriken kullanıcı süresi ve biriken kullanıcı süresinin tl karşılığı
//		
//		$this->query("SELECT COUNT(a.id), COUNT(a.id)-SUM(u.suspended) FROM userActive AS a LEFT JOIN users AS u ON u.id=a.Uid WHERE a.chips>0");
//		$this->query("SELECT COUNT(a.id), COUNT(a.id)-SUM(u.suspended) FROM userActive AS a LEFT JOIN users AS u ON u.id=a.Uid");
//		
//		$this->query("SELECT COUNT(id), SUM(earnedChips) FROM session WHERE sessType=2");//hediye adedi dakikası
		
		$result = $this->query("SELECT COUNT(id) FROM tk_users");
		if($result)
		{
			$row = $this->fetchRow($result);
			$this->generalStats['totalUser']	= number_format($row[0],0, '', '.');
		}
		
		$result = $this->query("SELECT COUNT(id) FROM tk_userbets");
		if($result)
		{
			$row = $this->fetchRow($result);
			$this->generalStats['totalBets']	= number_format($row[0],0, '', '.');
		}
		
		$result = $this->query("SELECT COUNT(date) FROM tk_games");
		if($result)
		{
			$row = $this->fetchRow($result);
			$this->generalStats['totalGames']	= number_format($row[0],0, '', '.');
		}		

		

	}
	
	private function memberStatistics()
	{
		$result = $this->query("SELECT COUNT(id) FROM tk_users");
		if($result)
		{
			$row = $this->fetchRow($result);
			$this->memberStats['allUsers'] = $row[0];
		}
		
		$result = $this->query("SELECT COUNT(id) FROM tk_users where user_type='U'");
		if($result)
		{
			$row = $this->fetchRow($result);
			$this->memberStats['realUsers'] = $row[0];
		}
		
		$result = $this->query("SELECT COUNT(id) FROM tk_users where user_type='U' and activation_status=1");
		if($result)
		{
			$row = $this->fetchRow($result);
			$this->memberStats['activatedUsers'] = $row[0];
		}		
		
		$result = $this->query("SELECT COUNT(id) FROM tk_users WHERE registration_date BETWEEN ".$this->dayStart." AND ".$this->dayOver);
		if($result)
		{
			$row = $this->fetchRow($result);
			$this->memberStats['todayRegisteredUsers'] = $row[0];
		}		
		
		$result = $this->query("SELECT COUNT(id) FROM tk_users WHERE last_login_date BETWEEN ".$this->dayStart." AND ".$this->dayOver);
		if($result)
		{
			$row = $this->fetchRow($result);
			$this->memberStats['todayLoginUsers'] = $row[0];
		}
		
		$result = $this->query("SELECT COUNT(distinct u.id) FROM tk_userbets b
			JOIN tk_users u on u.id=b.user_id
			WHERE u.registration_date BETWEEN ".$this->dayStart." AND ".$this->dayOver);
		if($result)
		{
			$row = $this->fetchRow($result);
			$this->memberStats['todayBetUsers'] = $row[0];
		}
		
		$result = $this->query("SELECT COUNT(distinct user_id) FROM tk_userbets");
		if($result)
		{
			$row = $this->fetchRow($result);
			$this->memberStats['allBetUsers'] = $row[0];
		}		
		
	}
	

	
	public function __construct($date=false)
	{
		adminClass::__construct();
		
		//$this->myTime = time()+(3*3600);
		$this->myTime = time();
		
		if($date!=false)
		{
			list($month, $day, $year) = explode('/', $date);
			
			$this->previousDayStart	= mktime(0,0,0, $month, $day-1, $year);
			$this->dayStart			= mktime(0,0,0, $month, $day, $year);
			$this->dayOver			= mktime(0,0,-1, $month, $day+1, $year);
			$this->monthStart		= mktime(0,0,0,date("n"), 1);
			//$this->now				= time()+(8*3600);
			$this->now				= time();
		}
		else
		{
			$this->previousDayStart	= mktime(0,0,0,date("n"), date("j")-1);
			$this->dayStart			= mktime(0,0,0);
			$this->dayOver			= mktime(0,0,-1, date("n"), date("j")+1);
			$this->monthStart		= mktime(0,0,0,date("n"), 1);
			//$this->now				= time()+(8*3600);
			$this->now				= time();
		}
		
		
		
		if($_SESSION['admin']['authorization']['showDailyStats'])
			$this->dailyStatistics();
			
		if($date==false)
		{
			if($_SESSION['admin']['authorization']['showGeneralStats'])
				$this->generalStatistics();
	
			if($_SESSION['admin']['authorization']['showMonthlyStats'])
				$this->monthlyStatistics();
	
			if($_SESSION['admin']['authorization']['showRecentStats'])
				$this->recentStatistics();
		}
		
		$this->memberStatistics();

	}
	
}






?>