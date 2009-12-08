<?php
class Mod_UrlAlias{
	
	private $dbh;
	
	public function __construct($id=null){
		$this->dbh = DBCreator::GetDbObject();
		$this->id = $id;
		if(isset($id)){
			$query = $this->dbh->prepare("SELECT `alias`,`path` FROM ".DB_PREFIX."_url_alias WHERE id=:id");
			$query->execute(array(':id'=>$this->id));
			list($this->alias,$this->path) = $query->fetch();
		}
		$this->setup = array(
			'menus'=>array(
				'menu'=>'admin',
				'linkpath'=>'admin/urlalias', //path
				'linktext'=>'URL Alias', //text
				'viewableby'=>array('admin'), //set default permissions for the menu item
				'styleid'=>'a-alias', //html id
				'class'=>'' //html class
			),
			'permissions'=>array('View Aliases','Add Alias')
		);
	}
	
	public function checkAlias($alias){
		$query = $this->dbh->prepare("SELECT `path` FROM ".DB_PREFIX."_url_alias WHERE `alias`=?");
		$query->execute(array(0=>$alias));
		$result = $query->fetch();
		if ($query->rowCount() == 1){
			return $result[0];
		}else{
			$query = $this->dbh->prepare("SELECT `path` FROM ".DB_PREFIX."_url_alias WHERE `alias` LIKE ? LIMIT 1");
			$query->execute(array(0=>$alias."%"));
			return false;
		}
	}
	
	public function commit(){
		$params = array(':alias'=>$this->alias,':path'=>$this->path);
		if(!isset($this->id)){
			$query = $this->dbh->prepare("INSERT INTO ".DB_PREFIX."_url_alias (`alias`,`path`) VALUES(:alias,:path)");
		}else{
			$query = $this->dbh->prepare("UPDATE ".DB_PREFIX."_url_alias SET `alias`=:alias, `path`=:path WHERE id=:id");
			$params[':id'] = $this->id;
		}
		$query->execute($params);
	}
	
	public function delete(){
		$query = $this->dbh->prepare("DELETE FROM ".DB_PREFIX."_url_alias WHERE id=:id");
		$query->execute(array(':id'=>$this->id));
		if($query->rowCount()==1){
			return true;
		}
		return false;
	}
	
	public static function Install(){
		//register Menu Item
//		$menu = new Menu('admin');
//		$menu->data = array(array('linkpath'=>'admin/urlalias','linktext'=>'Url Alias','styleid'=>'a-alias','viewableby'=>'admin'));
//		$menu->Commit(2);
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
	
	public static function add(){
		$Core = parent::getCore();
		$Core->smarty->assign('form',self::form());
		return $Core->smarty->fetch('form.tpl');
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
		$Core = parent::getCore();
		foreach($_POST['urlalias'] as $id){
			$alias = new Mod_UrlAlias($id);
			if($alias->delete()===true){
				$Core->json_obj->args .= $id.'|';
			}
		}
		$Core->json_obj->callback = 'nanobyte.deleteRows';
	}

	public static function edit(){
		$Core = parent::getCore();
		$Core->smarty->assign('form',self::form($Core->args[2]));
		return $Core->smarty->fetch('form.tpl');
	}

	public static function form($id=null){
		$Core = parent::getCore();
		$func = isset($id) ? 'edit' : 'add';
		$element_array = array('name'=>'urlalias','method'=>'post','action'=>'admin/urlalias/'.$func);
		if(isset($id)){
			$alias = new Mod_UrlAlias($id);
			$element_array['defaults'] = array(
				'alias'=>$alias->alias,
				'realpath'=>$alias->path,
				'alias_id'=>$alias->id
			);
		}
		$header = $func == 'add' ? 'Create Alias' : 'Edit Alias';
		$element_array['elements'] = array(
			array('type'=>'header','name'=>'','label'=>$header),
			array('type'=>'text', 'name'=>'alias', 'label'=>'Alias', 'options'=>array('size'=>62, 'maxlength'=>80)),
			array('type'=>'text', 'name'=>'realpath', 'label'=>'Real Path', 'options'=>array('size'=>62, 'maxlength'=>80)),
			array('type'=>'hidden', 'name'=>'alias_id', 'label'=>'', 'options'=>array('size'=>62, 'maxlength'=>80)),
			array('type'=>'submit','name'=>'submit','value'=>'Submit')
		);
		//add form rules
		$element_array['rules'] = array(
			array('required','alias'),
			array('required','path')
		);
		
		//apply form prefilters
		$element_array['filters'] = array(
			array("__ALL__","trim"),
			array("__ALL__","strip_tags")
		);
		$element_array['callback'] = array('UrlAliasController','Save');

		return parent::generateForm($element_array);
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
		$links = array('add'=>Core::l('add','admin/urlalias/add',$options));
		// bind the params to smarty
		$smartyArray = array(
			'pager'=>BaseController::Paginate($alias->all['limit'], $alias->all['nbItems'], 'admin/urlalias/list/', $page),
			'sublinks'=>$links,
			'cb'=>true,
			'formAction'=>'admin/urlalias',
			'actions'=>$actions,
			'extra'=>$extra,
			'list'=>$list
		);
		$Core->smarty->assign($smartyArray);
		return $Core->smarty->fetch('list.tpl');
	}

	public static function save($params){
		$Core = parent::getCore();
		if(isset($params['alias_id']) && !empty($params['alias_id'])){
			$alias = new Mod_UrlAlias($params['alias_id']);
		}else{
			$alias = new Mod_UrlAlias();
		}
		
		$alias->alias = $params['alias'];
		$alias->path = $params['realpath'];
		$alias->commit();
		 
		$Core->json_obj->callback = 'nanobyte.closeParentTab';
		$Core->json_obj->args = 'input[name=submit][value=Submit]';

		return;
	}

}

?>