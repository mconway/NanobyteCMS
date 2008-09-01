<?php
/**
 *@author Mike Conway
 *@copyright 2008, Mike Conway
 *@since 01-May-2008
 */
class MenuController{
	public static function GetMenu($name, $permission){
		global $smarty;
		$premenu = new Menu($name);
		foreach ($premenu->menu as $preitem){
			if(strpos($preitem['viewableby'],$permission)!==false){
				$menu[] = Core::l($preitem['linktext'],$preitem['linkpath']);
			}
		}
		$smarty->assign('menu',$menu);
	}
	public static function ListMenus(){
		global $smarty;
		$menus = new Menu();
		$menus->GetAll();
		$options['image'] = '16';
		//create list
		foreach($menus->all as $menu){
			$list[] = array(
				'id'=>$menu['mid'],
				'menu name'=>$menu['name'],
				'actions'=>Core::l('edit','admin/menus/edit/'.$menu['mid'],$options)
			);
		}
		//create the actions options
		$options['image'] = '24';
		$links = array('header'=>'Actions: ','add'=>Core::l('add','admin/perms/add',$options), 'edit'=>Core::l('edit','admin/perms/edit',$options));
		// bind the params to smarty
		$smarty->assign('sublinks',$links);
		$smarty->assign('self','admin/perms');
		$smarty->assign('list', $list);
	}
	public static function ListMenuItems($mid){
		global $smarty;
		$menu = new Menu();
		$menu->GetMenuName($mid);
		$perms = new Perms();
		$perms->GetNames();
		$i = 0;
		$menuItems = new Menu($menu->name);
		foreach($menuItems->menu as $item){
			$items[$i] = array(
				'path'=>$item['linkpath'],
				'title'=>$item['linktext'],
				//'viewable By'=>$item['viewableby']
			);
			foreach($perms->names as $pname){
				$checked = strpos($item['viewableby'],$pname) !== false ? 'checked="checked"' : '';
				$items[$i][$pname] = '<input type="checkbox" name="'.$pname.'[]" value="'.$item['name'].'" '.$checked.'/>';
			}
			$i++;
		}		
		$options['image'] = '24';
		$links = array('back'=>Core::l('back','admin/menus',$options),'add'=>Core::l('add','admin/perms/add',$options));
		$smarty->assign('sublinks',$links);
		$smarty->assign('list',$items);
		$smarty->assign('extra', '<input type="submit" value="Submit" name="submit"/>');
	}
}

 ?>