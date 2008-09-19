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
		//$options['image'] = '24';
		//$links = array('header'=>'Actions: ','add'=>Core::l('add','admin/menus/add',$options));
		// bind the params to smarty
		//$smarty->assign('sublinks',$links);
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
		$options['image']='16';
		foreach($menuItems->menu as $item){
			$items[$i] = array(
				'path'=>'<input type="text" size="15" value="'.$item['linkpath'].'" name="'.$item['id'].'_linkpath"/>',
				'title'=>'<input type="text" size="15" value="'.$item['linktext'].'" name="'.$item['id'].'_linktext"/>',
			);
			foreach($perms->names as $pname){
				$checked = strpos($item['viewableby'],$pname) !== false ? 'checked="checked"' : '';
				$items[$i][$pname] = '<input type="checkbox" name="'.$item['id'].'_'.$pname.'[]" value="'.$pname.'" '.$checked.'/>';
			}
			$items[$i]['actions'] = Core::l('delete','admin/menus/delete/'.$mid.'/'.$item['id'],$options);
			$i++;
		}		
		$options['image'] = '24';
		$links = array('back'=>Core::l('back','admin/menus',$options),'add'=>Core::l('add','admin/menus/add/'.$mid,$options));
		$smarty->assign('sublinks',$links);
		$smarty->assign('list',$items);
		$smarty->assign('extra', '<input type="submit" value="Submit" name="submit"/>');
	}
	public static function WriteMenu($id=null){
		unset($_POST['submit']);
		foreach($_POST as $key=>$item){
			$tmp = explode('_',$key);
			if ($tmp[1] != 'linkpath' && $tmp[1] != 'linktext' && $tmp[1] != ''){
				$array[$tmp[0]]['viewableby'][] = $tmp[1];
			}else{
				if($item != '' || $item != null){
					$array[$tmp[0]][$tmp[1]] = $item;
				}
			}
		}
		$menu = new Menu();
		$menu->data = $array;
		$menu->commit($id);
	}
	public static function AddMenuItem(){
		global $smarty;
		$perms = new Perms();
		$perms->GetNames();
		for($i=0; $i<5; $i++){
			$items[$i] = array(
				'path'=>'<input type="text" size="15" value="" name="'.$i.'_linkpath"/>',
				'title'=>'<input type="text" size="15" value="" name="'.$i.'_linktext"/>',
			);
			foreach($perms->names as $pname){
				$items[$i][$pname] = '<input type="checkbox" name="'.$i.'_'.$pname.'[]" value="'.$pname.'" '.$checked.'/>';
			}
		}	
		$options['image'] = '24';
		$links = array('back'=>Core::l('back','admin/menus',$options));
		$smarty->assign('sublinks',$links);
		$smarty->assign('list',$items);
		$smarty->assign('extra', '<input type="submit" value="Submit" name="submit"/>');
	}
}

 ?>