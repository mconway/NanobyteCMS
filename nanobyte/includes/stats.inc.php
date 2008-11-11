<?php
/*
 * CREATE TABLE `visitors_table` (
`ID` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`visitor_ip` VARCHAR( 32 ) NULL ,
`visitor_browser` VARCHAR( 255 ) NULL ,
`visitor_hour` SMALLINT( 2 ) NOT NULL DEFAULT '00',
`visitor_minute` SMALLINT( 2 ) NOT NULL DEFAULT '00',
`visitor_date` TIMESTAMP( 32 ) NOT NULL DEFAULT CURRENT_TIMESTAMP ,
`visitor_day` SMALLINT( 2 ) NOT NULL ,
`visitor_month` SMALLINT( 2 ) NOT NULL ,
`visitor_year` SMALLINT( 4 ) NOT NULL ,
`visitor_refferer` VARCHAR( 255 ) NULL ,
`visitor_page` VARCHAR( 255 ) NULL
) TYPE = MYISAM ;
 */
 
class Stats{
	function __construct($referrer=true){
		$this->DB = DbCreator::GetDBObject();
		
		if($referrer){
			$this->DB = DbCreator::GetDBObject();
			$this->visitorIp = $_SERVER['REMOTE_ADDR'];
			$this->visitorBrowser = $this->GetBrowserType();
			$this->visitorHour = date("h");
			$this->visitorMinute = date("i");
			$this->visitorDay = date("d");
			$this->visitorMonth = date("m");
			$this->visitorYear = date("Y");
			$this->visitorReferrer = $_SERVER['HTTP_REFERER'];
			$this->visitorPage = $this->SelfURL();
		}
	}
	public function GetBrowserType () {
		if (!empty($_SERVER['HTTP_USER_AGENT'])) 
		{ 
		   $HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT']; 
		} 
		else if (!isset($HTTP_USER_AGENT)) 
		{ 
		   $HTTP_USER_AGENT = ''; 
		} 
		if (ereg('Opera(/| )([0-9].[0-9]{1,2})', $HTTP_USER_AGENT, $log_version)) 
		{ 
		   $browser_version = $log_version[2]; 
		   $browser_agent = 'opera'; 
		} 
		else if (ereg('MSIE ([0-9].[0-9]{1,2})', $HTTP_USER_AGENT, $log_version)) 
		{ 
		   $browser_version = $log_version[1]; 
		   $browser_agent = 'ie'; 
		} 
		else if (ereg('OmniWeb/([0-9].[0-9]{1,2})', $HTTP_USER_AGENT, $log_version)) 
		{ 
		   $browser_version = $log_version[1]; 
		   $browser_agent = 'omniweb'; 
		} 
		else if (ereg('Netscape([0-9]{1})', $HTTP_USER_AGENT, $log_version)) 
		{ 
		   $browser_version = $log_version[1]; 
		   $browser_agent = 'netscape'; 
		} 
		else if (ereg('Mozilla/([0-9].[0-9]{1,2})', $HTTP_USER_AGENT, $log_version)) 
		{ 
		   $browser_version = $log_version[1]; 
		   $browser_agent = 'mozilla'; 
		} 
		else if (ereg('Konqueror/([0-9].[0-9]{1,2})', $HTTP_USER_AGENT, $log_version)) 
		{ 
		   $browser_version = $log_version[1]; 
		   $browser_agent = 'konqueror'; 
		} 
		else 
		{ 
		   $browser_version = 0; 
		   $browser_agent = 'other'; 
		}
		return $browser_agent;
	}

	public function SelfURL() { 
		//$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
		//$protocol = BaseController::strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s; 
		//$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]); 
		//return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI']; 
		return str_replace('/'.PATH.'/','',$_SERVER['REQUEST_URI']);
	}
	public function CheckReferrer(){
		$referrer = parse_url($this->visitorReferrer);
		if ($referrer['scheme']."://".$referrer['host'] == SITE_DOMAIN){
			return false;
		}else{
			return true;
		}
	}
	public function Read($start,$day=null,$month=null,$year=null){
		//set up the query
		$limit = 15;
		if (!$day) {
			$day = date("d");
		}
		if (!$month) {
			$month = date("m");
		}
		if (!$year) {
			$year = date("Y");
		}
		//prepare and execute
		$query = $this->DB->prepare("SELECT SQL_CALC_FOUND_ROWS `ip`, `browser`, `date`, `refferer`, `page` FROM cms_stats WHERE day=? AND month=? AND year=? ORDER BY date DESC LIMIT {$start},{$limit}");
		$query->bindParam(1,$day);
		$query->bindParam(2,$month);
		$query->bindParam(3,$year);
		$query->execute();
		//get the row count
		$rows = $this->DB->prepare('SELECT found_rows() AS rows');
        $rows->execute();
        $nbItems = $rows->fetch(PDO::FETCH_OBJ)->rows;
		//populate and return data
		$row['items'] = $query->fetchAll(PDO::FETCH_ASSOC);
		if ($nbItems>($start+$limit)) $row['final'] = $start+$limit;
		else $row['final'] = $nbItems;
		$row['limit'] = $limit;
		$row['nbItems'] = $nbItems;
		return $row;
	}
	public function Commit(){
		if ($this->CheckReferrer() == true){
			$query = $this->DB->prepare("INSERT INTO cms_stats (ip, browser, hour, minute, date, day, month, year, refferer, page) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
 			$query->bindParam(1,$this->visitorIp);
			$query->bindParam(2,$this->visitorBrowser);
			$query->bindParam(3,$this->visitorHour);
			$query->bindParam(4,$this->visitorMinute);
			$query->bindParam(5,$this->visitorDate);
			$query->bindParam(6,$this->visitorDay);
			$query->bindParam(7,$this->visitorMonth);
			$query->bindParam(8,$this->visitorYear);
			$query->bindParam(9,$this->visitorReferrer);
			$query->bindParam(10,$this->visitorPage);
			try{
				$query->execute();
			}catch(PDOException $e){
				die($e->getMessage());
			}
		}
	}
	public function UniqueHits(){
		$query = $this->DB->query("SELECT DISTINCT ip FROM cms_stats WHERE day=".date("d")." AND month=".date("m")." AND year=".date("Y"));
		$query->execute();
		$result['day'] = $query->rowCount();
		
		$queryTotal = $this->DB->query("SELECT DISTINCT ip FROM cms_stats");
		$queryTotal->execute();
		$result['total'] = $queryTotal->rowCount();
		
		return $result;
	}
	
	public function GetStats($column, $interval){
		$query = $this->DB->query("SELECT $column FROM cms_stats WHERE date > DATE_SUB(NOW(), INTERVAL 1 $interval);");
		$query->execute();
		$result = $query->fetchAll(PDO::FETCH_COLUMN,0);
		return $result;
	}
}
