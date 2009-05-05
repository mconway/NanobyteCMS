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
				$tabs = array(Core::l('Site Statistics','admin/stats/list'));
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
}

?>