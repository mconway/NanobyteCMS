<?php
class Mod_UrlAlias{
	
	private $dbh;
	
	public function __construct($id=null){
		$this->dbh = DBCreator::GetDbObject();
	}
	
	public static function Install(){
		//register Menu Item
		$menu = new Menu('admin');
		$menu->data = array(array('linkpath'=>'admin/urlalias','linktext'=>'Url Alias','styleid'=>'a-alias','viewableby'=>'admin'));
		$menu->Commit(2);
	}
	
	public function Read($start, $limit){
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM ".DB_PREFIX."_url_alias ORDER BY id DESC LIMIT {$start},{$limit}";
		$this->all = array();
		$this->all['items'] = $this->dbh->query($query)->fetchAll();
		//get the row count
		$cntRows = $this->dbh->query('SELECT found_rows() AS rows')->fetch(PDO::FETCH_OBJ)->rows;
		$this->all['final'] = ($cntRows >($start+$limit)) ? $start+$limit : $cntRows;
		$this->all['limit'] = $limit;
		$this->all['nbItems'] = $cntRows;
	}
	
	public static function Uninstall(){
	}
	
	public static function Admin(&$argsArray){
//		ContentController::Admin($argsArray);
	}
	
	public static function Display(&$argsArray){
//		ContentController::Display($argsArray);
	}

}

class UrlAliasController extends BaseController{
	
	public static function Admin(&$argsArray){
		list($args,$ajax,$smarty,$user,$jsonObj,$core) = $argsArray;
		if(array_key_exists(1,$args)){
			switch($args[1]){
				case 'list':
					$smarty->assign(self::GetList($args[2]));
					$content = $smarty->fetch('list.tpl'); 
					break;
				case 'add':
					$smarty->assign('form', self::AddAlias());
					$content = $smarty->fetch('form.tpl'); 
					break;
			}
		}else{
			$tabs = array(Core::l('Aliases','admin/urlalias/list'));
			$smarty->assign('tabs',$tabs);
			if($ajax){$jsonObj->tabs = $smarty->fetch('tabs.tpl');}
		}
		$jsonObj->content = $content;
	}

	public static function GetList($page){
		$alias = new Mod_UrlAlias();
		$alias->Read(parent::GetStart($page,10), 10); //array of objects
		$list = array();
		$options = array(
			'image' => '16',
			'class' => 'action-link-tab',
			'title' => 'Editing Alias'
		);
		foreach ($alias->all['items'] as $key=>$a){
			$actions .= Core::l('info','admin/user/edit/'.$a['id'],$options);
			array_push($list, array(
				'id'=>$a['id'], 
				'alias'=>$a['alias'], 
				'real path'=>$a['path'], 
				'actions'=>Core::l('edit','admin/urlalias/'.$a['id'],$options)
			));
		}
		//create the actions options
		$actions = array(
			'delete' => 'Delete',
		);
		$extra = 'With Selected: {html_options name=actions options=$actions}<input type="submit" name="submit" value="Go!"/>';
		$options = array(
			'image' => '24',
			'class' => 'action-link-tab',
			'title' => 'Add New Alias'
		);
		$links = array('header'=>'Actions: ','add'=>Core::l('add','admin/urlalias/add',$options));
		// bind the params to smarty
		$smartyArray = array(
			'pager'=>BaseController::Paginate($alias->all['limit'], $alias->all['nbItems'], 'admin/urlalias/list/', $page),
			'sublinks'=>$links,
			'cb'=>true,
			'self'=>'admin/alias/select',
			'actions'=>$actions,
			'extra'=>$extra,
			'list'=>$list
		);
		return $smartyArray;
	}

	public static function AddAlias(){
		$form = new HTML_QuickForm('newuser','post','user/register/');
		//create form elements
		$form->addElement('header','','Create New Alias');
		$form->addElement('text', 'alias', 'Alias', array('size'=>25, 'maxlength'=>15));
		$form->addElement('text', 'path', 'Actual Path', array('size'=>25, 'maxlength'=>50));
		$form->addElement('submit', 'submit', 'Submit');
		//apply form prefilters
		$form->applyFilter('__ALL__', 'trim');
		$form->applyFilter('__ALL__', 'strip_tags');
		//add form rules
		$form->addRule('alias', 'A valid alias is required.', 'required');
		$form->addRule('path', 'A valid path is required.', 'required');
		//If the form has already been submitted - validate the data
		if($_POST['submit']){
			if($form->validate()){
			
			}
		}

		//send the form to smarty
		return $form->toArray(); 
	}
}

?>