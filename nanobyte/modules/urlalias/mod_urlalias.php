<?php
class Mod_UrlAlias{
	
	private $dbh;
	
	public function __construct($id=null){
		$this->dbh = DBCreator::GetDbObject();
		$this->id = $id;
	}
	
	public function checkAlias($alias){
		$dbh = DBCreator::GetDBObject();
		$query = $dbh->prepare("SELECT `path` FROM ".DB_PREFIX."_url_alias WHERE `alias`=?");
		$query->execute(array(0=>$alias));
		$result = $query->fetch();
		if ($query->rowCount() == 1){
			return $result[0];
		}else{
			$query = $dbh->prepare("SELECT `path` FROM ".DB_PREFIX."_url_alias WHERE `alias` LIKE ? LIMIT 1");
			$query->execute(array(0=>$alias."%"));
			return false;
		}
	}
	
	public function commit(){
		if(!isset($this->id)){
			$query = $this->dbh->prepare("INSERT INTO ".DB_PREFIX."_url_alias (`alias`,`path`) VALUES(:alias,:path)");
		}else{
			$query = $this->dbh->prepare("UPDATE ".DB_PREFIX."_url_alias SET `alias`=:alias, `path`=:path");	
		}
		$query->execute(array(':alias'=>$this->alias,':path'=>$this->path));
	}
	
	public function delete(){
		$query = $this->dbh->prepare("DELETE FROM ".DB_PREFIX."_url_alias WHERE id=:id");
		$query->execute(array(':id'=>$this->id));
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
	
	public static function addAlias(){
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
	
	public static function admin(){
		$content = '';
		$Core = BaseController::getCore();
		if(isset($Core->args[1]) && method_exists('UrlAliasController',$Core->args[1])){
			$content = call_user_func(array('self',$Core->args[1]));
		}else{
			$tabs = array(Core::l('Aliases','admin/urlalias/getList'));
			$Core->smarty->assign('tabs',$tabs);
			if($Core->ajax){$Core->json_obj->tabs = $Core->smarty->fetch('tabs.tpl');}
		}
		$Core->json_obj->content = $content;
	}

	public static function delete(){
		
	}

	public static function edit(){
		$Core = parent::getCore();
		$Core->smarty->assign('form',self::form('edit'));
		return $Core->smarty->fetch('form.tpl');
	}

	public static function form($func){
		$Core = parent::getCore();
		$form = new HTML_QuickForm('urlalias','post','admin/urlalias/'.$func);
		if($func == 'edit'){
			$form->setDefaults(array(
				'alias'=>'',
				'realpath'=>''
			));
		}
		$header = $func == 'add' ? 'Create Alias' : 'Edit Alias';
		$form->addElement('header','',$header);
		$form->addElement('text', 'alias', 'Alias', array('size'=>62, 'maxlength'=>80));
		$form->addElement('text', 'realpath', 'Real Path', array('size'=>62, 'maxlength'=>80));
		$form->addElement('submit','submit','Submit');
		if(isset($_POST['submit']) && $form->validate()){
			$form->process(array('UrlAliasController','Save'));
			return;
		}
		return $form->toArray();
	}

	public static function getList(){
		$Core = parent::getCore();
		$page = isset($Core->args[2]) ? $Core->args[2] : 1;
		$alias = new Mod_UrlAlias();
		$alias->Read(parent::GetStart($page,10), 10); //array of objects
		$list = array();
		$options = array(
			'image' => '16',
			'class' => 'action-link-tab',
			'title' => 'Editing Alias'
		);
		$actions = '';
		foreach ($alias->all['items'] as $key=>$a){
			$actions .= Core::l('info','admin/user/edit/'.$a['id'],$options);
			array_push($list, array(
				'id'=>$a['id'], 
				'alias'=>$a['alias'], 
				'real path'=>$a['path'], 
				'actions'=>Core::l('edit','admin/urlalias/edit/'.$a['id'],$options)
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
		$Core->smarty->assign($smartyArray);
		return $Core->smarty->fetch('list.tpl');
	}

	public static function save($params){
		$Core = parent::getCore();
		
		$alias = new Mod_UrlAlias();
		$alias->alias = $params['alias'];
		$alias->path = $params['realpath'];
		$alias->commit();
		 
		$Core->json_obj->callback = 'nanobyte.closeParentTab';
		$Core->json_obj->args = 'input[name=submit][value=Submit]';

		return;
	}

}

?>