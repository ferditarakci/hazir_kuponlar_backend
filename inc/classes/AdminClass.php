<?PHP
date_default_timezone_set('Europe/Istanbul');
session_name('TKSSID');
session_set_cookie_params(0, '/', '.tahminkrali2.com');
@session_start();

require_once 'MySQLClass.php';

class adminClass extends mysqlClass
{
	public $fileName;
	public $myTime;
	private $timeOver = 3600, $lastAction = 0, $isSignedIn=0;

	private $Aid, 
			$uname, 
			$name, 
			$surname, 
			$email, 
			$phone, 
			$suspended, 
			
			$authChangeUsername, 
			$authShowUserMail, 
			$authChangeUserMail, 
			$authChangeUserPassword, 
			$authShowUserSuspend, 
			$authChangeUserSuspend, 
			$authShowUserReferer, 
			$authChangeUserReferer,
			$authShowUserVIP, 
			$authChangeUserVIP,
			$authChangeUserShopType,


			$authChangePerformerUname, 
			$authChangePerformerNickname, 
			$authChangePerformerPassword, 
			$authChangePerformerPicture, 
			$authChangePerformerStatus, 
			$authChangePerformerCosts, 

			$authShowPerformerMail, 
			$authShowPerformerUname, 
			$authShowPerformerBankInfo, 
			$authChangePerformerBankInfo, 
			$authChangePerformerFMS,
			$authPerformerCommision, 
			$authShowPerformerBanList, 
			$authShowPerformerWarns, 
			$authShowPerformerEarningList,

			$authShowPrvRoom, 
			$authShowDailyStats, 
			$authShowRecentStats, 
			$authShowMonthlyStats, 
			$authShowGeneralStats, 
			$authPermitPaymentAll, 
			$authPermitPaymentOne,
			
			$authShowPerformersPage,
			$authShowReportsPage,
			$authShowLogsPage,
			$authShowAnnouncesPage,
			$authShowCampaignsPage,
			$authShowMailingPage,
			$authShowPaymentsPage,
			$authShowFakesTab,
			$authChangeSiteShopType,
			$authChangeSiteFms,
			$authCampaignLevel,
			
			$authShowChipSaleHistory,
			$authShowVolumeControl,
			$userVolumeLevel;
	
			
	private function getAdminInformations($uname, $pass)
	{
		$pass = md5($pass);
		$result = $this->query("SELECT id,first_name,last_name,username,is_suspended,authorization FROM tk_users WHERE username='$uname' AND password='$pass' and authorization>0");
		if($result===false || $this->numRows($result)==0)
			return false;
		$row = $this->fetchRow($result);
		
		$this->Aid							= $row[0];
		$this->name							= $row[1];
		$this->surname						= $row[2];
		$this->uname						= $row[3];
		$this->suspended					= $row[4];
		$this->authShowDailyStats			= true;
		$this->authChangeUsername			= true;
		$this->authShowMail					= true;
		$this->authChangeMail				= true;
		$this->authChangeUserPassword		= true;
		$this->authShowUserSuspend			= true;
		$this->authChangeUserSuspend		= true;
		$this->authShowReferer				= true;
		$this->authChangeReferer			= true;
		$this->authShowUserVIP				= true;
		$this->authChangeUserVIP			= true;
		$this->authChangeUserShopType		= true;
		$this->authChangePerformerUname		= true;
		$this->authChangePerformerNickname	= true;
		$this->authChangePerformerPassword	= true;
		$this->authChangePerformerPicture	= true;
		$this->authChangePerformerStatus	= true;
		$this->authChangePerformerCosts		= true;
		$this->authShowPerformerMail		= true;
		$this->authShowPerformerUname		= true;
		$this->authShowPerformerBankInfo	= true;
		$this->authChangePerformerBankInfo	= true;
		$this->authChangePerformerFMS		= true;
		$this->authPerformerCommision		= true;
		$this->authShowPerformerBanList		= true;
		$this->authShowPerformerWarns		= true;

		$this->authShowPerformerEarningList	= true;

		$this->authShowPrvRoom				= true;
		$this->authShowDailyStats			= true;
		$this->authShowRecentStats			= true;
		$this->authShowMonthlyStats			= true;
		$this->authShowGeneralStats			= true;
		$this->authPermitPaymentAll			= true;
		$this->authPermitPaymentOne			= true;

		$this->authShowPerformersPage		= true;
		$this->authShowReportsPage			= true;
		$this->authShowLogsPage				= true;
		$this->authShowAnnouncesPage		= true;
		$this->authShowCampaignsPage		= true;
		$this->authShowMailingPage			= true;
		$this->authShowPaymentsPage			= true;
		$this->authShowFakesTab				= true;
		$this->authChangeSiteShopType		= true;
		$this->authChangeSiteFms			= true;
		$this->authShowChipSaleHistory		= true;

		$this->authShowVolumeControl		= true;
				
		return true;
	}

	public function login($uname, $pass)
	{
		if(empty($uname) || empty($pass))
			throw new Exception('Kullanıcı adı yada şifre girmediniz!');
		
		$uname	= get_magic_quotes_gpc()==false ? addslashes(htmlspecialchars($uname))	: htmlspecialchars($uname);
		$pass	= get_magic_quotes_gpc()==false ? addslashes(htmlspecialchars($pass))	: htmlspecialchars($pass);
		
		$time = $this->myTime;

		
		if($this->getAdminInformations($uname, $pass)==false)
			throw new Exception('Kullanıcı adı yada şifre hatalı!');
		elseif($this->suspended==1)
			throw new Exception('Hesabınız yönetici tarafından engellenmiş!');
		elseif($this->isOnline==1 && $this->lastAction>($time-$this->timeOver))
			throw new Exception('Zaten oturum açmış görünüyorsunuz!');
		else
		{
			$this->lastAction = $time;
			$_SESSION['admin'] = array(
									'aid'					=> $this->Aid,
									'name'					=> $this->name,
									'uname'					=> $this->uname,
									'surname'				=> $this->surname,
									'email'					=> $this->email,
									'phone'					=> $this->phone,
									'loginIp'				=> $_SERVER['REMOTE_ADDR'],
									'loginDate'				=> $this->lastAction,
									'lastAction'			=> $this->lastAction,
									'volumeLevel'			=> $this->userVolumeLevel,
									'authorization'			=> array(
																	'changeUserUname'			=> $this->authChangeUsername,
																	'showUserMail'				=> $this->authShowMail,
																	'changeUserMail'			=> $this->authChangeMail,
																	'changeUserPassword'		=> $this->authChangeUserPassword,
																	'showUserSuspend'			=> $this->authShowUserSuspend,
																	'changeUserSuspend'			=> $this->authChangeUserSuspend,
																	'showUserReferer'			=> $this->authShowReferer,
																	'changeUserReferer'			=> $this->authChangeReferer,
																	'showUserVIP'				=> $this->authShowUserVIP,
																	'changeUserVIP'				=> $this->authChangeUserVIP,
																	'changeUserShopType'		=> $this->authChangeUserShopType,
																	'changePerformerUname'		=> $this->authChangePerformerUname,
																	'changePerformerNickname'	=> $this->authChangePerformerNickname,
																	'changePerformerPassword'	=> $this->authChangePerformerPassword,
																	'changePerformerPicture'	=> $this->authChangePerformerPicture,
																	'changePerformerStatus'		=> $this->authChangePerformerStatus,
																	'changePerformerCosts'		=> $this->authChangePerformerCosts,
																	'showPerformerMail'			=> $this->authShowPerformerMail,
																	'showPerformerUname'		=> $this->authShowPerformerUname,
																	'showPerformerBankInfo'		=> $this->authShowPerformerBankInfo,
																	'changePerformerBankInfo'	=> $this->authChangePerformerBankInfo,
																	'changePerformerFMS'		=> $this->authChangePerformerFMS,
																	'performerCommision'		=> $this->authPerformerCommision,
																	'showPerformerBanList'		=> $this->authShowPerformerBanList,
																	'showPerformerWarns'		=> $this->authShowPerformerWarns,
																	'showPerformerEarningList'	=> $this->authShowPerformerEarningList,
																	'showPrvRoom'				=> $this->authShowPrvRoom,
																	'permitPaymentAll'			=> $this->authPermitPaymentAll,
																	'permitPaymentOne'			=> $this->authPermitPaymentOne,
																	'showDailyStats'			=> $this->authShowDailyStats,
																	'showRecentStats'			=> $this->authShowRecentStats,
																	'showMonthlyStats'			=> $this->authShowMonthlyStats,
																	'showGeneralStats'			=> $this->authShowGeneralStats,
																	'showPerformersPage'		=> $this->authShowPerformersPage,
																	'showReportsPage'			=> $this->authShowReportsPage,
																	'showLogsPage'				=> $this->authShowLogsPage,
																	'showAnnouncesPage'			=> $this->authShowAnnouncesPage,
																	'showCampaignsPage'			=> $this->authShowCampaignsPage,
																	'showMailingPage'			=> $this->authShowMailingPage,
																	'showPaymentsPage'			=> $this->authShowPaymentsPage,
																	'showFakesTab'				=> $this->authShowFakesTab,
																	'changeSiteShopType'		=> $this->authChangeSiteShopType,
																	'changeSiteFms'				=> $this->authChangeSiteFms,
																	'showChipSaleHistory'		=> $this->authShowChipSaleHistory,
																	'campaignLevel'				=> $this->authCampaignLevel,
																	'showVolumeControl'			=> $this->authShowVolumeControl
									)									
									);
			
			/*			
			if($this->addAdminToLogs()==false)
			{
				$this->logout();
				throw new Exception('Giriş başarısız!');
			}
			*/
			
			$this->updateLastAction($time);

			/* BURASI ENABLE EDILECEK
			$this->query("INSERT INTO administratorActionLogs(Aid, Uid, Pid, action, date, ip, actionLevel) VALUES(".$this->Aid.", 0, 0, 'Oturum açtı! [".$this->uname."]', $time, '".addslashes(htmlspecialchars($_SERVER['REMOTE_ADDR']))."', 5)");
			*/
		}
	}
	
	private function checkRemoteAccess(){
		
		if(empty($this->Aid) && isset($_SESSION['admin']['aid']))
			$this->Aid = $_SESSION['admin']['aid'];
			
		$adminIp = $this->fetchRow($this->query("SELECT value FROM generalConfig WHERE name='adminIp'"));
		if($_SERVER['REMOTE_ADDR']!=$adminIp[0] && !in_array($this->Aid, array(1,6,8)))
			return false;
		
		return true;
	}
	
	private function addAdminToLogs()
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		$browser = $_SERVER['HTTP_USER_AGENT'];
		$time = $this->myTime;
		
		$result = $this->query("INSERT INTO administratorLoginLogs(Aid, ip, browser, date) VALUES($this->Aid, '$ip', '$browser', $time)");
		if($this->affectedRows($result)==0)
			return false;
		else
			return true;
	}
	
	private function changeOnlineStatus($status=0)
	{
		$result = $this->query("UPDATE administratorUsers SET is_online=$status WHERE id=".$_SESSION['admin']['aid']);
		if($this->affectedRows($result)==0)
			return false;
		else
			return true;
		
	}
	
	private function updateLastAction($lastAction=0)
	{
		$result = $this->query("UPDATE tk_users SET last_action=$lastAction WHERE id=".$_SESSION['admin']['aid']);
		if($this->affectedRows($result)==0)
			return false;
		else
		{
			$_SESSION['admin']['lastAction'] = $this->myTime;
			return true;
		}
	}
	
	
	public function logout()
	{
		if(isset($_SESSION['admin']['aid']))
		{
			$time = $this->myTime;
			//$this->query("INSERT INTO administratorActionLogs(Aid, Uid, Pid, action, date, ip, actionLevel) VALUES(".$_SESSION['admin']['aid'].", 0, 0, 'Oturum sonlandı! [".$_SESSION['admin']['uname']."]', $time, '".addslashes(htmlspecialchars($_SERVER['REMOTE_ADDR']))."', 5)");
		}

		$this->Aid					= false;
		$this->name					= false;
		$this->surname				= false;
		$this->email				= false;
		$this->phone				= false;
		$this->lastAction			= 0;
		$this->authShowMail			= false;
		$this->authShowPrvRoom		= false;
		$this->authshowDailyStats	= false;
		$this->authshowRecentStats	= false;
		$this->authshowMonthlyStats	= false;
		$this->authshowGeneralStats	= false;
		$this->suspended			= false;


		unset($_SESSION['admin']);
		return true;
	}
	
	

	private function isOnline()
	{
		$time = $this->myTime;

		if(!isset($_SESSION['admin']) || ($time - $_SESSION['admin']['lastAction']) > $this->timeOver)
		{
			$this->logout();
			return false;
		}
		else
		{
			$result = $this->query("SELECT is_online, last_action FROM tk_users WHERE id='".$_SESSION['admin']['aid']."'");
			if($this->numRows($result)==0)
			{
				$this->logout();
				return false;
			}
			else
			{
				$row = $this->fetchRow($result);
				if($row===false || $row[0]===0 || $row[1]<($time-$this->timeOver))
				{
					$this->logout();
					return false;
				}
				else
					return true;
			}
		}
		
	}
	
	
	private function getFileName()
	{
		$scriptfilename = explode('/', $_SERVER['SCRIPT_NAME']);
		$this->fileName = end($scriptfilename);
	}
		
	public function __construct($haveToLogin=true, $updateSession=true)
	{
		mysqlClass::__construct();
		$this->getFileName();

		//$this->myTime = time()+(3*3600);
		$this->myTime = time();
		
		$online=true;
		//$time = time()+(8*3600);
		$time = time();
		
		if($haveToLogin)
		{
		
			$online = $this->isOnline();
		
			if($updateSession && $online)
			
				$updated = $this->updateLastAction($time);
		}

		
		if(isset($_GET['ref']) && $_GET['ref']=='timeout')
			header('ajaxerror: true');
		
		/*
		if($this->checkRemoteAccess()==false)
			$this->logout();
		*/
		
		if(isset($_SESSION['admin']) && $this->fileName=='index.php')
		{
			header('location: https://'.$_SERVER['HTTP_HOST'].'/panel/default.php'); exit;
		}
		elseif($haveToLogin==true && $online==false && $this->fileName!='index.php')
		{
			//header('location: https://'.$_SERVER['HTTP_HOST'].'/panel/'); exit;
		}

//echo "<pre style='background:#FFF; font-size:20px;'>"; print_r($_SERVER); echo "</pre>";
		

	}
	
	
	
}

?>