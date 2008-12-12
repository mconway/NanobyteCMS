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
		$menu = array();
		foreach ($premenu->menu as $preitem){
			if(strpos($preitem['viewableby'],$permission)!==false){
				$options = array('id'=>$preitem['styleid'],'class'=>$preitem['class']);
				array_push($menu,Core::l($preitem['linktext'],$preitem['linkpath'],$options));
			}
		}
		$smarty->assign('menu',$menu);
	}
	
	public static function ListMenus(){
		global $smarty;
		$menus = new Menu();
		$menus->GetAll();
		$options['image'] = '16';
		$list = array();
		//create list
		foreach($menus->all as $menu){
			array_push($list, array(
				'id'=>$menu['mid'],
				'menu name'=>$menu['name'],
				'actions'=>Core::l('edit','admin/menus/edit/'.$menu['mid'],$options)
			));
		}
		//create the actions options
		//$options['image'] = '24';
		//$links = array('header'=>'Actions: ','add'=>Core::l('add','admin/menus/add',$options));
		// bind the params to smarty
		//$smarty->assign('sublinks',$links);
		$smarty->assign(array('self'=>'admin/perms','list'=>$list));
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
				'class'=>'<input type="text" size="10" value="'.$item['class'].'" name="'.$item['id'].'_class"/>',
				'id'=>'<input type="text" size="10" value="'.$item['styleid'].'" name="'.$item['id'].'_styleid"/>',
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
		$smarty->assign(array('sublinks'=>$links,'list'=>$items,'extra'=>'<input type="submit" value="Submit" name="submit"/>'));
	}
	
	public static function WriteMenu($id=null){
		unset($_POST['submit']);
		foreach($_POST as $key=>$item){
			$tmp = explode('_',$key); //$key is id_name
			if ($tmp[1] != 'linkpath' && $tmp[1] != 'linktext' && $tmp[1] != ''){ //check to see if the item is a checkbox or a textbox
				$array[$tmp[0]]['viewableby'][] = $tmp[1]; //add checkboxes to the viewableby array
			}else{
				if($item != '' || $item != null){ //add all else to its own array bucket
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
		$smarty->assign(array('sublinks'=>$links,'list'=>$items,'extra'=>'<input type="submit" value="Submit" name="submit"/>'));
	}
}

 ?>