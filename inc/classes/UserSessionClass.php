<?PHP
date_default_timezone_set('Europe/Istanbul');
session_start();

class userSessionClass extends mysqlClass
{
	private $timeOver = 2400, //zaman aşımı süresi(saniye türünden 15dk)
			$loginAtAnotherPC = false,
			$username, $password, $userid, $firstname, $lastname, $userType, $uemail, $typeEnd, $sesstime=0, $suspended=0, $sid=-1, $sessionId, $actionTime=0, $userip, $browserHash, $oneIp, $tempPass, $score;//session bilgileri
	public $fileName='';
	public $myTime, $startOfMonth;
	
	
	public function unsubscribe($unsubscribe)
	{
		$result = $this->query("select id from tk_users where (username='".$unsubscribe["username"]."' OR email_address='".$unsubscribe["username"]."') AND password=md5('".$unsubscribe["password"]."')");
		if ($this->numRows($result) == 1)
		{
			$this->query("UPDATE tk_users SET allow_mail=0 where (username='".$unsubscribe["username"]."' OR email_address='".$unsubscribe["username"]."') AND password=md5('".$unsubscribe["password"]."')");
			$unsubscribeResult["result"] = true;
			$unsubscribeResult["errMsg"] = "Bilgilendirme mail listelerinden adresiniz çıkartılmıştır.";
		}
		else
		{
			$unsubscribeResult["result"] = false;
			$unsubscribeResult["errMsg"] = "Kullanıcı bulunamadı. Lütfen bilgilerinizi kontrol edin.";
		}
		
		return $unsubscribeResult;
	}

	public function contactForm($cform)
	{
		$this->query("INSERT INTO tk_contactforms 
			(user_id,form_date,first_name,last_name,
			email_address,phone_number,user_message,is_read)
			VALUES 
			('".$cform["hdnUserID"]."','".time()."','".$cform["txtFirstName"]."','".$cform["txtLastName"]."',
			'".$cform["txtEmailAddress"]."','".$cform["txtPhone"]."','".$cform["txtMessage"]."','0')");
		if ($this->insertId() > 0)
		{
			$contactFormResult['result'] = true;
			$contactFormResult['errMsg'] = "Mesajınız iletilmiştir. Teşekkür ederiz";
		}
		else
		{
			$contactFormResult['result'] = false;
			$contactFormResult['errMsg'] = "Bir hata oluştu ve mesajınız iletilemedi";
		}
		return $contactFormResult;
	}
	
	public function resetSetPassword($set)
	{
		$resetPasswordResult['result'] = false;
		$resetPasswordResult['errMsg'] = "Bir sorun oluştu, lütfen iletişim kısmından bize ulaşın.";
	
	
		$result 	= $this->query("select id,registration_date,password from tk_users where id='".$set["user-id"]."' and activation_status='1'");
		$rows 		= $this->numRows($result);
		if ($rows == 0)
		{
			$resetPasswordResult['result'] = false;
			$resetPasswordResult['errMsg'] = "Girilen bilgilerle aktif bir kullanıcı bulunamadı.";
		}
		else if ($rows == 1)
		{
			$row 		= $this->fetchRow($result);
			$user_id	= $row[0];
			$rdate		= $row[1];
			$pass		= $row[2];
			
			$hkey = md5($row[0]."-".$row[1]."-".$row[2]);
			if ($hkey != $set["reset-key"])
			{
				$resetPasswordResult['result'] = false;
				$resetPasswordResult['errMsg'] = "Gönderilen şifre değiştirme bilgisinde hata mevcut. Lütfen en baştan tekrar deneyin";			
			}
			else
			{
				$this->query("UPDATE tk_users SET password=MD5('".$set["pass"]."') where id='".$set["user-id"]."'");
				$resetPasswordResult['result'] = true;
				$resetPasswordResult['errMsg'] = "Şifreniz gücellenmiştir. Giriş yapabilirsiniz.";
			}
		}
		else
		{
			$resetPasswordResult['result'] = false;
			$resetPasswordResult['errMsg'] = "Bir sorun oluştu, lütfen iletişim kısmından bize ulaşın.";						
		}
		return $resetPasswordResult;		
		
	}
	
	public function resetPassword($search)
	{
		$result 	= $this->query("select id,registration_date,password,email_address from tk_users where (email_address='".$search["email"]."' OR username='".$search["usern"]."') and activation_status='1'");
		$rows 		= $this->numRows($result);
		$row 		= $this->fetchRow($result);
		if ($rows == 0)
		{
			$resetPasswordResult['status'] = false;
			$resetPasswordResult['message'] = "Girilen bilgilerle aktif bir kullanıcı bulunamadı.";		
		}
		else if ($rows == 1)
		{
			require_once $_SERVER["DOCUMENT_ROOT"].'/_incs/_classes/class.phpmailer.php';
			
		
			$fd = fopen( $_SERVER["DOCUMENT_ROOT"]."/html-includes/mail-resetpass.html", "r" );
			$contents = fread($fd,filesize ($_SERVER["DOCUMENT_ROOT"]."/html-includes/mail-resetpass.html"));
			fclose($fd);
			
			$activation_code = md5($row[0]."-".$row[1]."-".$row[2]);
			
			
			$contents = str_replace("@URL","https://www.tahminkrali2.com/sifremi-unuttum.php?user-id=".$row[0]."&reset-key=".$activation_code,$contents);
			
			
			try
			{
				$mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
				$mail->IsSMTP();
				$mail->SMTPAuth = true;
				$mail->Host = 'smtp.yandex.com';
				$mail->Port = 587;
				$mail->SMTPSecure = 'tls';
				$mail->Username = 'info@tahminkrali.net';
				$mail->Password = 'EnAyUtIEL02';
				$mail->SetFrom("info@tahminkrali.net");
				$mail->CharSet = 'UTF-8';	
				$mail->AddReplyTo("info@tahminkrali.net");
				$mail->AddAddress($row[3]);
				$mail->Subject = 'TahminKralı - Şifre Hatırlatma';
				$mail->MsgHTML($contents);
				$mail->Send();
				
				$resetPasswordResult['status'] = true;
				$resetPasswordResult['message'] = "Şifre yenilemeniz için lütfen mail hesabınızı <b>SPAM klasörü dahil</b> kontrol ediniz. ";	
			}
			catch (phpmailerException $e) 
			{
				//echo $e->errorMessage(); //Pretty error messages from PHPMailer
				$resetPasswordResult['status'] = false;
				$resetPasswordResult['message'] = "Mail gönderiminde bir hata oluştu. Lütfen bir süre sonra tekrar deneyin. ".$e->errorMessage();
			} 
			catch (Exception $e) 
			{
				//echo $e->getMessage(); //Boring error messages from anything else!
				$resetPasswordResult['status'] = false;
				$resetPasswordResult['message'] = "Mail gönderiminde bir hata oluştu. Lütfen bir süre sonra tekrar deneyin. ".$e->errorMessage();
			}

		}
		else
		{
			$resetPasswordResult['status'] = false;
			$resetPasswordResult['message'] = "Bir sorun oluştu, lütfen iletişim kısmından bize ulaşın.";						
		}
		return $resetPasswordResult;
	}
	
	public function findLoginUserScore()
	{
		$score_result 		= $this->query("select SUM(score) 
			from tk_userbets b
			JOIN tk_games g on b.game_id=g.puid
			where b.result>0 
			and b.user_id='".$this->userid."' 
			and b.betgroup_id!=10
			and g.datetime>='".$this->startOfMonth."'");
		$score_row 			= $this->fetchRow($score_result);		
		return number_format($score_row[0],2,'.','');
	}
	
	public function createUserViaFB($profile)
	{		
		$pre_username 		= trim(split("@",trim($profile["email"]))[0]);
		$pre_username		= str_replace(".","-",$pre_username);	
		
		if ($profile["email"] == "")
		{
			$retValues[0] = 0;
			return $retValues;
		}
		
		if ($pre_username == "")
		{
			$retValues[0] = 0;
			return $retValues;		
		}
		
		if (strlen($pre_username) > 14)
		{
			$pre_username = substr($pre_username,0,14);
		}
		
		$this->username		= $pre_username;
		$this->uemail		= trim($profile["email"]);
		$this->firstname	= trim($profile["first_name"]);
		$this->lastname		= trim($profile["last_name"]);
		$this->password		= md5($profile["id"].time()); 
		
		
		$i=1;
		$orig_username 		= $this->username;
		do
		{
			if ($i > 1)
				$this->username = $orig_username."".$i;
			$check_usern_result = $this->query("select id from tk_users where username='".$this->username."'");
			$check_usern_rows	= $this->numRows($check_usern_result);
			$i++;
		} while ($check_usern_rows > 0);
		
		
		$result = $this->query("INSERT INTO tk_users
			(username,password,email_address,first_name,last_name,facebook_id,
			mobile_number,tckn,registration_date,activation_date,activation_status,
			last_login_date,last_login_ip)
			VALUES ('".$this->username."','".$this->password."','".$this->uemail."','".$this->firstname."','".$this->lastname."','".$profile["id"]."',
			'','','".$this->myTime."','".$this->myTime."','1','".$this->myTime."','".$_SERVER['REMOTE_ADDR']."')");
		$this->userid		= $this->insertId();
		if($this->affectedRows($result) == 1)
		{
			$retValues[0] = 1;
			$_SESSION['user'] = array(
									'uid'			=> $this->userid, 
									'uname'			=> $this->username, 
									'uemail'		=> $this->uemail, 
									'firstname'		=> $this->firstname, 
									'lastname'		=> $this->lastname, 
									'upass'			=> $this->password, 
									'sid'			=> $this->sid, 
									'sessid'		=> $this->sessionId, 
									'suspended'		=> $this->suspended,
									'score'			=> $this->score
									);			
			
		}
		else
		{
			$retValues[0] = 0;
		}
		
		return $retValues;	
	}
	
	public function checkFbUser($profile)
	{		
		//FB Email ve ID ile kayıtlı user var mı?
		$result = $this->query("SELECT id,email_address,first_name,last_name,username 
			from tk_users 
			where /*activation_status=1 AND */email_address='".$profile["email"]."' and facebook_id='".$profile["id"]."'");
		$rows	= $this->numRows($result);
		if ($rows == 1)
		{			
			$row = $this->fetchRow($result);
			$this->userid		= $row[0];
			$this->username		= $row[4];
			$this->firstname	= $row[2];
			$this->lastname		= $row[3];
			$this->uemail		= $row[1];
			//$this->password		= $pass;
			$this->score		= $this->findLoginUserScore();

			$_SESSION['user'] = array(
									'uid'			=> $this->userid, 
									'uname'			=> $this->username, 
									'uemail'		=> $this->uemail, 
									'firstname'		=> $this->firstname, 
									'lastname'		=> $this->lastname, 
									'upass'			=> $this->password, 
									'sid'			=> $this->sid, 
									'sessid'		=> $this->sessionId, 
									'suspended'		=> $this->suspended,
									'score'			=> $this->score
									);
		
		
			$this->query("UPDATE tk_users SET activation_status=1,last_login_date='".$this->myTime."',last_login_ip='".$_SERVER['REMOTE_ADDR']."' where id='".$this->userid."'");		
			return true;		
		
		}
		else
		{
			//FB ID ile kayıtlı user var mı?
			$result = $this->query("SELECT id,email_address,first_name,last_name,username 
				from tk_users 
				where /*activation_status=1 AND*/ 
				facebook_id='".$profile["id"]."' 
				AND email_address!='".$profile["email"]."'");
			$rows	= $this->numRows($result);
			if ($rows == 1)
			{
				$row = $this->fetchRow($result);
				$this->userid		= $row[0];
				$this->username		= $row[4];
				$this->firstname	= $row[2];
				$this->lastname		= $row[3];
				$this->uemail		= $row[1];
				//$this->password		= $pass;
				$this->score		= $this->findLoginUserScore();
				
				$_SESSION['user'] = array(
									'uid'			=> $this->userid, 
									'uname'			=> $this->username, 
									'uemail'		=> $this->uemail, 
									'firstname'		=> $this->firstname, 
									'lastname'		=> $this->lastname, 
									'upass'			=> $this->password, 
									'sid'			=> $this->sid, 
									'sessid'		=> $this->sessionId, 
									'suspended'		=> $this->suspended,
									'score'			=> $this->score
									);				
				
				$this->query("UPDATE tk_users SET 
					facebook_id='".$profile["id"]."',
					activation_status=1,
					email_address='".$profile["email"]."',
					last_login_date='".$this->myTime."',
					last_login_ip='".$_SERVER['REMOTE_ADDR']."' 
					where id='".$this->userid."'") or die(mysql_error());
				return true;					
			}
			else
			{
				//FB Email ile kayıtlı user var mı?
				$result = $this->query("SELECT id,email_address,first_name,last_name,username 
					from tk_users where /*activation_status=1 
					AND*/ email_address='".$profile["email"]."' 
					AND IFNULL(facebook_id,'')=''");
				$rows	= $this->numRows($result);
				if ($rows == 1)
				{
					$row = $this->fetchRow($result);
					$this->userid		= $row[0];
					$this->username		= $row[4];
					$this->firstname	= $row[2];
					$this->lastname		= $row[3];
					$this->uemail		= $row[1];
					//$this->password		= $pass;
					$this->score		= $this->findLoginUserScore();

					$_SESSION['user'] = array(
											'uid'			=> $this->userid, 
											'uname'			=> $this->username, 
											'uemail'		=> $this->uemail, 
											'firstname'		=> $this->firstname, 
											'lastname'		=> $this->lastname, 
											'upass'			=> $this->password, 
											'sid'			=> $this->sid, 
											'sessid'		=> $this->sessionId, 
											'suspended'		=> $this->suspended,
											'score'			=> $this->score
											);
				
					
				
					$this->query("UPDATE tk_users SET 
						facebook_id='".$profile["id"]."',
						activation_status=1,
						last_login_date='".$this->myTime."',
						last_login_ip='".$_SERVER['REMOTE_ADDR']."' 
						where id='".$this->userid."'") or die(mysql_error());
					return true;				
				}
				else
				{
					return false;
				}			
			}
		}
	}
	
	public function registerUser($username, $password, $email, $tckn, $gsm_phone)
	{
		$check_username_result = $this->query("select * from tk_users where username='".$username."'");
		$check_username_rows = $this->numRows($check_username_result);
		if ($check_username_rows > 0)
		{
			$retValues[0] = 0;
			$retValues[1] = "Bu kullanıcı adı ile bir kayıt mevcut";
			return $retValues;
		}
		
		$check_email_result = $this->query("select * from tk_users where email_address='".$email."'");
		$check_email_rows = $this->numRows($check_email_result);
		if ($check_email_rows > 0)
		{
			$retValues[0] = 0;
			$retValues[1] = "Bu mail adresi ile bir kayıt mevcut";
			return $retValues;
		}		
		
		
		$result = $this->query("INSERT INTO tk_users
			(username,password,email_address,
			mobile_number,tckn,registration_date,activation_date,activation_status)
			VALUES ('".$username."','".md5($password)."','".$email."',
			'".$gsm_phone."','".$tckn."','".$this->myTime."','".(($this->myTime) + 86400)."','0')");
		if($this->affectedRows($result)==1)
		{
			$retValues[0] = 1;
			$retValues[1] = "Kullanıcı eklenmiştir";
			$retValues[2] = md5($email.(($this->myTime) + 86400).$username);
		}
		else
		{
			$retValues[0] = 0;
			$retValues[1] = "";
		}
		
		return $retValues;
	}
	
	private function checkUserInfo($uname, $pass)
	{
		$uname	= $this->toLatin1LowerCase($uname);
		$pass	= md5($pass);
		$result = $this->query("SELECT u.id, u.email_address, u.first_name, u.last_name, u.username
			FROM tk_users as u 
			WHERE u.is_suspended=0 
			and u.activation_status=1 
			AND (u.email_address='".$uname."' OR u.username='".$uname."') 
			AND u.password='".$pass."'");
		if($this->numRows($result)==1)
		{
			$row = $this->fetchRow($result);						
			
			$this->userid		= $row[0];
			$this->username		= $row[4];
			$this->firstname	= $row[2];
			$this->lastname		= $row[3];
			$this->uemail		= $row[1];
			$this->password		= $pass;
			
			/*
			$score_result 		= $this->query("select SUM(score) 
				from tk_userbets b
				JOIN tk_games g on b.game_id=g.puid
				where b.result>0 
				and b.user_id='".$this->userid."' 
				and g.datetime>='".$this->startOfMonth."'");
			$score_row 			= $this->fetchRow($score_result);						
			$this->score		= $score_row[0];
			*/
			$this->score		= $this->findLoginUserScore();
			
			return true;
		}
		else
			return false;
	}
	
	
	private function deleteFromUserSiteOnline($userid=false, $sessid=false)
	{
		if($userid!==false)
			$result = $this->query("DELETE FROM tk_useronline WHERE user_id=$userid");
		elseif($sessid!==false)
			$result = $this->query("DELETE FROM tk_useronline WHERE session_id='$sessid'");
		else
			return false;
		
		$this->sid = -1;
		$this->sessionId = false;
		
		if($this->affectedRows($result)==1)
			return true;
		else
			return false;
	}	
	

	public function login($uname, $pass)
	{
		if($this->checkUserInfo($uname, $pass)==false)
			throw new Exception("Kullanıcı adı yada şifre hatalı!", 9090);
		
//		if($this->isSignedIn())
//			throw new Exception("Şu an başka bir bilgisayarda oturum açmış görünüyorsunuz. Yeni bir oturum açmadan önce lütfen diğer oturumlarınızı kapatın!");
		
//		if($this->addtoUserSiteOnline()==false)
//			throw new Exception("Oturum açma hatası", 9091);
		
//		if($this->suspended==true)
//			throw new Exception("Üyeliğiniz kapatıldığı için giriş yapmanız engellenmiştir.", 9092);
		
//		if($this->checkOneIp())
//			throw new Exception("Sınırlı giriş özelliği açık olduğundan oturum açılamıyor.", 9094);

//		$this->addToLogs();
			
		$_SESSION['user'] = array(
								'uid'			=> $this->userid, 
								'uname'			=> $this->username, 
								'uemail'		=> $this->uemail, 
								'firstname'		=> $this->firstname, 
								'lastname'		=> $this->lastname, 
								'upass'			=> $this->password, 
								'sid'			=> $this->sid, 
								'sessid'		=> $this->sessionId, 
								'suspended'		=> $this->suspended,
								'score'			=> $this->score
								);
		
		$this->query("UPDATE tk_users SET last_login_date='".$this->myTime."',last_login_ip='".$_SERVER['REMOTE_ADDR']."' where id='".$this->userid."'");
		
		return true;
		
	}
	
	private function checkOneIp()
	{
		if($this->oneIp===NULL)
			return false;
		elseif($this->oneIp==htmlspecialchars($_SERVER['REMOTE_ADDR']))
			return false;
		else
			return true;
	}
	
	private function addToLogs()
	{
		$browser	= get_magic_quotes_gpc()==false ? addslashes(htmlspecialchars($_SERVER['HTTP_USER_AGENT']))	: htmlspecialchars($_SERVER['HTTP_USER_AGENT']);
		$ip			= get_magic_quotes_gpc()==false ? addslashes(htmlspecialchars($_SERVER['REMOTE_ADDR']))		: htmlspecialchars($_SERVER['REMOTE_ADDR']);
		$time		= $this->myTime;

		if(isset($_SESSION['newUserReference']))
			$referer	= get_magic_quotes_gpc()==false ? "'".addslashes($_SESSION['newUserReference'])."'"					: "'".$_SESSION['newUserReference']."'";
		else 
			$referer = "NULL";

		$this->query("INSERT INTO userLoginLogs(Uid, ip, browser, date, referer) VALUES(".$this->userid.", '$ip', '$browser', $time, $referer)");
	}
	
	public function toLatin1LowerCase($string)
	{
		$dizi = array('ğ'=>'g', 'ü'=>'u', 'ş'=>'s', 'ö'=>'o', 'ç'=>'c');
		return strtr(mb_strtolower($string, 'UTF-8'), $dizi);		
	}	
	
	public function checkUniqueInfo($field,$value)
	{
		$result = $this->query("SELECT id from tk_users where ".$field."='".$value."'");			
		if ($this->numRows($result) > 0)
			return 0;
		else
			return 1;
			
	}
	
	
	public function checkUsernameChars($value)
	{
		$allowed = "abcdefghijklmnopqrstuvwxyz0123456789.-_";
		$userChars = str_split($value);
		$allowedChars = str_split($allowed);
		for ($i=0; $i < sizeof($userChars); $i++)
		{			
			if (in_array($userChars[$i], $allowedChars) == false)
			{	
				return 0;
			}

		}
		return 1;		
			
	}	
	
	public function logout()
	{
		if(isset($_SESSION['user']['sessid']))
			//$this->deleteFromUserSiteOnline(false, $_SESSION['user']['sessid']);
		$this->userid	= false;
		$this->username	= false;
		$this->userType	= false;
		$this->password	= false;
		unset($_SESSION['user']);
		return true;
	}	
			
	public function __construct($haveToLogin=true, $updateSession=true)
	{
		mysqlClass::__construct();
		
		//$this->myTime = (time()+(3*3600));
		$this->myTime = time();
		
		$this->startOfMonth = mktime(0,0,0,date("m"),1,date("Y"));
		
		
	}
}
