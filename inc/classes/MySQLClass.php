<?php

if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
	$_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
}

//header('Content-Type: text/html; charset=utf-8');
//die("<h3>Bakım çalışması nedeniyle kısa bir süre hizmet vermemekteyiz! Hemen döneceğiz...</h3>");

class mysqlClass
{
	//	private $dbLink=false, $mysqli=false, $host='37.187.25.60', $user='db@fg2bpm61u', $pass='lQsw2jfWwq', $database='fg2bpm61u';
	//	private $dbLink=false, $mysqli=false, $host='37.187.131.74', $user='cc_db_user_1', $pass='xU6%sYm1+zoG5&u3', $database='fg2bpm61u';
	private $dbLink = false, $mysqli = false, $host = 'localhost', $user = 'tahminkrali2', $pass = 'IEL02EnAyUt', $database = 'tahminkr_maclar';
	//private $dbLink=false, $mysqli=false, $host='localhost', $user='elestirm_dbuser', $pass='IEL02EnAyUt', $database='elestirm_maclar';


	private function connectDB($server, $user, $pass, $database)
	{
		$connect = mysqli_connect($server, $user, $pass, $database);
		if (!$connect)
			exit;

		$this->dbLink = $connect;

		// @$this->query('SET NAMES utf8; SET CHARACTER SET utf8; SET COLLATION_CONNECTION = "utf8_general_ci"');
		@$this->query('SET NAMES utf8');
		@$this->query('SET CHARACTER SET utf8');
		@$this->query('SET COLLATION_CONNECTION = "utf8_general_ci"');
	}

	private function selectDB($dbname)
	{
		mysqli_select_db($this->dbLink, $dbname);
	}

	public function query($query)
	{
		// $startTime = microtime(true);
		$i = 0;
		do {
			$result = mysqli_query($this->dbLink, $query);
			if ($i)
				sleep(1);
		} while (mysqli_errno($this->dbLink) == 1213 && $i++ < 2);
		// $queryTime = microtime(true)-$startTime;

		if (mysqli_errno($this->dbLink)) {
			print mysqli_errno($this->dbLink);
			// @mysqli_query($this->dbLink, "INSERT INTO gecici(hata) VALUES('errorNo ".mysqli_errno($this->dbLink)." ".$_SERVER['REQUEST_URI']." ".addslashes(htmlspecialchars($query))."')");
			die("Veritabanı Bağlantı Hatası!");
		}

		return $result;
	}

	public function numRows($result)
	{
		if ($result === false)
			return 0;
		return mysqli_num_rows($result);
	}

	public function affectedRows()
	{
		return mysqli_affected_rows($this->dbLink);
	}

	public function insertId()
	{
		return mysqli_insert_id($this->dbLink);
	}

	public function fetchRow($result, $fetchType = MYSQL_NUM)
	{
		if ($result === false)
			return null;
		return mysqli_fetch_array($result, $fetchType);
	}

	public function fetchArray($result, $fetchType = MYSQL_ASSOC)
	{
		$return = array();
		while ($row = $this->fetchRow($result, $fetchType))
			$return[] = $row;
		return $return;
	}

	private function closeDB()
	{
		if ($this->dbLink != false && gettype($this->dbLink) != "Object") {

			mysqli_close($this->dbLink);
			$this->dbLink = false;
		}
	}

	public function __construct()
	{
		// floodControllerClass::__construct();
		if ($this->dbLink == false) {
			$this->connectDB($this->host, $this->user, $this->pass, $this->database);
			$this->selectDB($this->database);
		}
	}

	public function __destruct()
	{
		$this->closeDB();
	}
}
