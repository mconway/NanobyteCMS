<?php
/**
 *@author Mike Conway
 *@copyright 2008, Mike Conway
 *@since 01-May-2008
 */
class MenuController extends BaseController{
	
	public static function addMenu(){
		$form = new HTML_QuickForm('new-menu','post','admin/menu/add');
		$form->addElement('header','','Create new Account');
		$form->addElement('text', 'name', 'Menu Name', array('size'=>25, 'maxlength'=>50));
		$form->addElement('submit', 'submit', 'Submit');
		//If the form has already been submitted - validate the data
		if(isset($_POST['submit']) && $form->validate()){
			$menu = new Menu();
			$form->process(array($menu,'Create'));
			Core::SetMessage('Your user account has been created!','info');
			return true;
			//BaseController::Redirect();
			//exit;
		}
		//send the form to smarty
		return array('form'=>$form->toArray()); 
	}
	
	public static function addMenuItemForm($menuID){
		$perms = new Perms();
		$perms->GetNames();
		$menu = new Menu('main');
		$fields = array('path'=>'linkpath','title'=>'linktext','class'=>'class','id'=>'styleid');
		for($i=0; $i<5; $i++){
			foreach($fields as $field=>$text){
				$items[$i][$field] = '<input type="text" size="15" value="" name="tb_'.$i.'_'.$text.'"/>';
			}
			foreach($perms->names as $pname){
				$items[$i][$pname] = '<input type="checkbox" name="cb_'.$i.'_'.$pname.'[]" value="'.$pname.'"/>';
			}
		}	
		$options['image'] = '24';
//		$links = array('back'=>Core::l('back','admin/menu',$options)); 'sublinks'=>$links,
		return array('list'=>$items,'extra'=>'<input type="submit" value="Submit" name="submit"/>','formAction'=>'admin/menu/add/'.$menuID);
	}
	
	public static function admin(&$argsArray){
		list($args,$ajax,$smarty,$user,$jsonObj,$core) = $argsArray;
		
		$tabs = array($core->l('Menus','admin/menu/list'));
		$smarty->assign('tabs',$tabs);
		$smarty->assign('showID',true);
		if($ajax){$jsonObj->tabs = $smarty->fetch('tabs.tpl');}
		
		$content = '';
		
		if(isset($args[1])){
			switch($args[1]){
				case 'add':
					if(isset($_POST['submit']) && !isset($args[2])){
						if(self::addMenu()){
							$core->setMessage('Your changes have been saved.','info');
							$jsonObj->callback = 'nanobyte.closeParentTab';
							$jsonObj->args = 'input[name=submit][value=Submit]';
						}
					}elseif(isset($args[2]) && is_numeric($args[2])){
						if (isset($_POST['submit'])){
							if(self::writeMenu($args[2],$_POST)){
								$core->setMessage('Your changes have been saved.','info');
								$jsonObj->callback = 'nanobyte.closeParentTab';
								$jsonObj->args = 'input[name=submit][value=Submit]';
								break;
							}
						}
						$smarty->assign(self::addMenuItemForm($args[2]));
						$content = $smarty->fetch('list.tpl');
					}else{ //no menu is specified, so lets create a new one
						$smarty->assign(self::addMenu());
						$content = $smarty->fetch('form.tpl');
					}
					break;
				case 'delete':
					if($args[2] && $args[3]){
						if(Admin::deleteObject('menu_links','id',$args[3])){
							$jsonObj->callback = 'nanobyte.deleteRows';
							$jsonObj->args = $args[3];
						}
	//					parent::Redirect();
					}elseif($args[2]){
						$menu = new Menu($args[2]);
						$menu->delete();
						$jsonObj->callback = 'nanobyte.deleteRows';
						$jsonObj->args = $menu->menu[0]['mid'].'|';
					}
					break;
				case 'edit':
					if(isset($args[2])){
						if (isset($_POST['submit'])){
							if(self::writeMenu($args[2],$_POST)){
								$core->setMessage('Your changes have been saved.','info');
								$jsonObj->callback = 'nanobyte.closeParentTab';
								$jsonObj->args = 'input[name=submit][value=Submit]';
							}
						}else{
							$smarty->assign(self::listMenuItems($args[2]));
							$content = $smarty->fetch('list.tpl');
						}
					}else{
						$core->setMessage('You must specify a menu!', 'error');
					}
					break;
				case 'list':
					$smarty->assign(self::listMenus());
					$content = $smarty->fetch('list.tpl');
			}
		}

		$jsonObj->content = $content;
	}
	
	public static function getMenu($name, $permission){
		global $smarty;
		$premenu = new Menu($name);
		$menu = array();
		foreach ($premenu->menu as $preitem){
			if(strpos($preitem['viewableby'],$permission)!==false){
				$options = array('id'=>$preitem['styleid'],'class'=>$preitem['class']);
				array_push($menu,Core::l($preitem['linktext'],$preitem['linkpath'],$options));
			}
		}
		return $menu;
	}
	
	public static function listMenus(){
		$menus = new Menu();
		$menus->GetAll();
		$options['image'] = '16';
		$list = array();
		//create list
		foreach($menus->all as $menu){
			$options['class'] = 'action-link-tab';
			$options['title'] = "Edit menu";
			array_push($list, array(
//				'id'=>$menu['mid'],
				'menu name'=>ucwords($menu['name']),
//				'parent id'=>$menu['parent_id'],
				'actions'=>Core::l('edit','admin/menu/edit/'.$menu['mid'],$options)
			));
			if($menu['canDelete']==true){
				$options['class'] = 'action-link';
				$options['title'] = "Delete";
				$list[count($list)-1]['actions'] .= " | ".Core::l('delete','admin/menu/delete/'.$menu['name'],$options);
			}
		}
		//create the actions options
		$options['image'] = '24';
		$options['title'] = "Add menu";
		$options['class'] = 'action-link-tab';
		$links = array('add'=>Core::l('add','admin/menu/add',$options));
		// bind the params to smarty
		return array(
			'self'=>'admin/menu',
			'list'=>$list,
			'sublinks'=>$links
		);
	}
	
	public static function listMenuItems($mid){
		$menu = new Menu();
		$menu->GetMenuName($mid);
		$perms = new Perms();
		$perms->GetNames();
		$i = 0;
		$menuItems = new Menu($menu->name);
		$options['image']='16';
		foreach($menuItems->menu as $item){
			$items[$i] = array(
				'path'=>'<input type="text" size="15" value="'.$item['linkpath'].'" name="tb_'.$item['id'].'_linkpath"/>',
				'title'=>'<input type="text" size="15" value="'.$item['linktext'].'" name="tb_'.$item['id'].'_linktext"/>',
				'class'=>'<input type="text" size="10" value="'.$item['class'].'" name="tb_'.$item['id'].'_class"/>',
				'id'=>'<input type="text" size="10" value="'.$item['styleid'].'" name="tb_'.$item['id'].'_styleid"/>',
			);
			foreach($perms->names as $pname){
				$checked = strpos($item['viewableby'],$pname) !== false ? 'checked="checked"' : '';
				$items[$i][$pname] = '<input type="checkbox" name="cb_'.$item['id'].'_'.$pname.'[]" value="'.$pname.'" '.$checked.'/>';
			}
			$items[$i]['actions'] = Core::l('delete','admin/menu/delete/'.$mid.'/'.$item['id'],$options);
			$i++;
		}		
		$options['image'] = '24';
		$options['class'] = 'action-link';
		$links = array('add'=>Core::l('add','admin/menu/add/'.$mid,array('image'=>'24','class'=>'action-link-tab','title'=>'Add Links to menu')));
		return array(
			'sublinks'=>$links,
			'list'=>$items,
			'extra'=>'<input type="submit" value="Submit" name="submit"/>',
			'formAction'=>'admin/menu/edit/'.$mid
		);
	}
	
	public static function writeMenu($id,$data){
		unset($data['submit']);
		foreach($data as $key=>$item){
			$tmp = explode('_',$key); //$key is type_id_name
			if ($tmp[0] == 'cb'){ //check to see if the item is a checkbox or a textbox
				$array[$tmp[1]]['viewableby'][] = $tmp[2]; //add checkboxes to the viewableby array
			}else{
				if(!empty($item)){ //add all else to its own array bucket
					$array[$tmp[1]][$tmp[2]] = $item;
				}
			}
		}
		if(isset($array)){
			$menu = new Menu();
			$menu->data = $array;
			$menu->commit($id);
			return true;
		}else{
			return false;
		}
	}
	
}

?>