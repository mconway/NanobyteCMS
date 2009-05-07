<?php

class Mod_Stats
{
	public static function ListStats($page){
		global $smarty;
		$stats = new Stats();
		$start = BaseController::GetStart($page,15);
		$statsArray = $stats->Read($start, $_POST['Date_Day'], $_POST['Date_Month'], $_POST['Date_Year']);
		
		$smarty->assign('list',$statsArray['items']);
		$smarty->assign('pager',BaseController::Paginate($statsArray['limit'], $statsArray['nbItems'], 'admin/stats/', $page));
		$hits = $stats->UniqueHits();	
		
		$formTop = '<div><form id="daterange" name="daterange" method="post" action="http://beta.wiredbyte.com/WiredCMS/admin/stats">{html_select_date}<input type="submit" name="Submit" value="Submit" /></form></div><div id="hits">Hits Today: '.$hits['day'].' | Hits Total: '.$hits['total'].'</div>';
		$smarty->assign('extra', $formTop);
	}
	
	public static function Admin(&$argsArray){
	    	
		list($args,$ajax,$smarty,$user,$jsonObj) = $argsArray;
			
		switch($args[1]){
			case 'list':
				unset($args[array_search('ajax',$args)]);
				self::ListStats($args[2]); // Get the stats list
				$content = $smarty->fetch('list.tpl'); // Display the list
				break;
			default:
global $core;
				$tabs = array($core->l('Site Statistics','admin/stats/list'));
				$smarty->assign('tabs',$tabs);
				if($ajax){$jsonObj->tabs = $smarty->fetch('tabs.tpl');}
				break;
		}
		$jsonObj->content = $content;
	}

	public static function BrowserGraph(){
		require_once 'includes/contrib/phplot/phplot.php';
		$stats = new Stats(false);
		$array = array_count_values($stats->GetStats('browser','WEEK'));
		$leg = array_keys($array);
		array_unshift($array, '');
		$data = array($array,array());
		$plot = new PHPlot(300,200);
		$plot->setTransparentColor('white');
		$plot->SetTextColor('black');
		$plot->SetLabelScalePosition(0.32);
		$plot->SetTitle('Weekly requests by Browser');
		$plot->SetTitleColor('black');
		$plot->SetOutputFile('files/browsergraph.png');
		$plot->SetIsInline(true);
		$plot->SetDataType('text-data');
		$plot->SetDataValues($data);
		$plot->SetLegend($leg);
		$plot->SetPlotType('pie');
		$plot->DrawGraph();
	}	
	
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

?>