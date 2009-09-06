<?php
/**
 * 
 */

class Mod_Gallery{	
	/**
	 * @var
	 */
	private $dbh;
	public $template;
	/**
	 * 
	 * @return 
	 * @param object $id[optional]
	 */
	public function __construct(){
		$this->dbh = DBCreator::GetDbObject();
		$this->template = '../../modules/gallery/templates/gallery.tpl';
		$Core = BaseController::getCore();
		$Core->saveSettings('templates/NanobyteBlue/images','thumbs_list');
	}
	
	public function display(){
		$Core = BaseController::getCore();
		$Core->smarty->assign('thumbs_list',$this->read());
		var_dump($this->read());
		return $Core->smarty->display($this->template);	
	}
	
	/**
	 * 
	 * @return 
	 */
	public static function install(){
		//Module::regBlock(array('name'=>'Menu', 'module'=>'Menu', 'options'=>''));
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
		$Core = BaseController::getCore();
		$thumbs_dir = $Core->getSettings('thumbs_dir');
		$thumbs_list = glob('templates/NanobyteBlue/images/*');
		return $thumbs_list;
	}
	
	/**
	 * 
	 * @return 
	 */
	public static function uninstall(){
	}
	
	public static function gallery_block(){
		$block = new Block_Menu();
		return $block;
	}
	
}

class Block_Gallery extends Mod_Gallery{
	
	function __construct(){
		global $Core;
		BaseController::AddJs('modules/posts/js/posts.js');
		$this->template = '../../modules/menu/menu.tpl';
		$Core->smarty->assign('menusblock',$this->buildMenus());
	}
	
	public function buildMenus(){
		global $Core;
		
		$p = new Mod_Menu();
		$menus = $p->read();
		
		$menuArray = array();
		foreach($menus as $menu){
			if(strpos($menu['viewableby'],$Core->user->group)!==false){
				if(!isset($menu['linktext'])){
					$menuArray[$menu['name']]['settings'] = $menu;
				}
				$menuArray[$menu['name']][] = $menu;
			}
		}
		
		return $menuArray;
	}
	
}

class GalleryController{
	public static function Display(){
		$tmp = new Mod_Gallery();
		return $tmp->display();
	}	
}
?>