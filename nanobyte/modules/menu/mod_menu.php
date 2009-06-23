<?php
/**
 * 
 */

class Mod_Menu{	
	/**
	 * @var
	 */
	private $dbh;
	
	/**
	 * 
	 * @return 
	 * @param object $id[optional]
	 */
	public function __construct(){
		$this->dbh = DBCreator::GetDbObject();
	}
	
	/**
	 * 
	 * @return 
	 */
	public static function install(){
		Module::regBlock(array('name'=>'Menu', 'module'=>'Menu', 'options'=>''));
	}
	
	/**
	 * 
	 * @return 
	 * @param object $type
	 * @param object $published[optional]
	 * @param object $limit[optional]
	 * @param object $start[optional]
	 */
	public function read(){
		$query = $this->dbh->prepare("SELECT * FROM ".DB_PREFIX."_menus LEFT JOIN ".DB_PREFIX."_menu_links ON mid=menu WHERE 1");
		try{
			$query->execute();
			return $query->fetchAll(PDO::FETCH_ASSOC);
		}catch (PDOException $e){
			Core::SetMessage($e->getMessage(), 'error');
		}
	}
	
	/**
	 * 
	 * @return 
	 */
	public static function uninstall(){
	}
	
	public static function menu_block(){
		$block = new Block_Menu();
		return $block;
	}
	
}

class Block_Menu extends Mod_Menu{
	
	function __construct(){
		global $smarty;
		BaseController::AddJs('modules/posts/js/posts.js');
		$this->template = '../../modules/menu/menu.tpl';
		$smarty->assign('menusblock',$this->buildMenus());
	}
	
	public function buildMenus(){
		global $user;
		
		$p = new Mod_Menu();
		$menus = $p->read();
		
		$menuArray = array();
		foreach($menus as $menu){
			if(strpos($menu['viewableby'],$user->group)!==false){
				if(!isset($menu['linktext'])){
					$menuArray[$menu['name']]['settings'] = $menu;
				}
				$menuArray[$menu['name']][] = $menu;
			}
		}
		
		return $menuArray;
	}
	
}

?>