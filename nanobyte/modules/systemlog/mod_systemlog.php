<?php
/**
 * 
 */

class Mod_SystemLog{	
	/**
	 * @var
	 */
	public $files;
	public $setup;
	public $contents;
	
	const INFO = 1;
	const ERROR = 0;
	const NOTICE = 2;
	
	/**
	 * 
	 * @return 
	 * @param object $id[optional]
	 */
	public function __construct(&$Core){
		$this->files = explode('|',$Core->getSettings('systemLogFiles'));
		$this->setup = array(
			'menus'=>array(
				'menu'=>'admin',
				'linkpath'=>'admin/systemlog', //path
				'linktext'=>'System Logs', //text
				'viewableby'=>array('admin'), //set default permissions for the menu item
				'styleid'=>'syslog', //html id
				'class'=>'syslog' //html class
			),
			'permissions'=>array('View Logs','Add Log File')
		);
	}
	
	public function buildForm(){
		$form = new HTML_QuickForm('syslog','post','');
		//set form default values

		$form->setdefaults(array(
			'file'=>$this->contents
		));
		
		$form->addElement('header','','View System Log: '.$this->current_file);
		$form->addElement('textarea','file','',array('rows'=>20,'cols'=>70,'readonly','disabled'));
		
		return $form->toArray();
	}
	
	/**
	 * 
	 * @return 
	 */
	public static function install(){
//		$menu = new Menu('admin');
//		$menu->data = array('linkpath'=>'admin/syslog');
//		$menu->Commit();
	}
	
	/**
	 * 
	 * @return 
	 * @param object $type
	 * @param object $published[optional]
	 * @param object $limit[optional]
	 * @param object $start[optional]
	 */
	public function read($file_key){
		$this->current_file = $this->files[$file_key];
		if(is_readable($this->current_file)){
			$this->contents = file_get_contents($this->current_file);
		}else{
			BaseController::getCore()->setMessage("Unable to read ".$this->current_file.": Permission denied");
		}
	}
	
	/**
	 * 
	 * @return 
	 */
	public static function uninstall(){
		
	}
	
}

class SystemlogController extends BaseController{
	
	public static function admin(){
		$Core = parent::getCore();
		$content = '';
		$log = new Mod_SystemLog($Core);
		
		if(isset($Core->args[1])){
			$log->read($Core->args[1]);
			$rows = array();
			$entries = explode("\n",$log->contents);
			foreach($entries as $entry){
				preg_match("/\[(\s*.*\s*\d+:\d+:\d+\s*\d+)\] \[([a-z]*[A-Z]*)\] (.*)/", $entry, $matches);
				if(!empty($matches) && count($matches >= 4)){
					$tmp = array('Time'=>$matches[1],'Severity'=>'','Message'=>$matches[3]);
					$matches[2] = ucfirst($matches[2]);
					switch(strtoupper($matches[2])){
						case 'NOTICE':
							$tmp['Severity'] = "<center><a title='{$matches[2]}'><img src='".THEME_PATH."/images/".Mod_SystemLog::NOTICE."-25.png' alt='{$matches[2]}'/></a></center>";
							break;
						case 'ERROR':
							$tmp['Severity'] = "<center><a title='{$matches[2]}'><img src='".THEME_PATH."/images/".Mod_SystemLog::ERROR."-25.png' alt='{$matches[2]}'/></a></center>";
							break;
						case 'INFO':
						default:
							$tmp['Severity'] = "<center><a title='{$matches[2]}'><img src='".THEME_PATH."/images/".Mod_SystemLog::INFO."-25.png' alt='{$matches[2]}'/></a></center>";
							break;
					}
					array_push($rows,$tmp);
				}
			}
			if(!empty($rows)){
				$page = isset($Core->args[2]) && !empty($Core->args[2]) ? $Core->args[2] : 1;
				$start = parent::getStart($page,LIMIT);
				$smarty_vars = array(
					'pager'=>parent::Paginate(LIMIT, count($rows), "admin/systemlog/{$Core->args[1]}/", $page),
					'list'=>array_splice(array_reverse($rows),$start,LIMIT)
				);
				$Core->smarty->assign($smarty_vars);
				$content =  $Core->smarty->fetch('list.tpl');
			}else{
				$Core->smarty->assign('form',$log->buildForm());
				$content =  $Core->smarty->fetch('form.tpl');
			}
		}
		$files = array_filter($log->files,array('self','removeEmpty'));
		$tabs = array();
		foreach($files as $key=>$tab){
			if(!empty($tab)){
				$path = explode('\\',$tab);
				array_push($tabs, $Core->l(ucfirst(end($path)),'admin/systemlog/'.$key));
			}
		}
		$Core->smarty->assign('tabs',$tabs);
		if($Core->ajax){$Core->json_obj->tabs = $Core->smarty->fetch('tabs.tpl');}
		$Core->json_obj->content = $content;
	}
	
	public static function removeEmpty(&$item){
		return !empty($item);
	}
	
}

?>