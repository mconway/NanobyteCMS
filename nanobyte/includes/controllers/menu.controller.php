<?php
	/*
	*Copyright (c) 2009, Michael Conway
	*All rights reserved.
	*Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
    *Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
   	*Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
	*Neither the name of the Nanobyte CMS nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
	*THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
	*/
	
class MenuController extends BaseController{
	
	public static function add(){
		$Core = parent::getCore();
		if(isset($_POST['submit']) && !isset($Core->args[2])){
			if(self::addMenu()){
				$Core->json_obj->callback = 'nanobyte.closeParentTab';
				$Core->json_obj->args = 'input[name=submit][value=Submit]';
			}
		}elseif(isset($Core->args[2]) && is_numeric($Core->args[2])){
			if (isset($_POST['submit'])){
				if(self::writeMenu($Core->args[2],$_POST)){
					$Core->setMessage('Your changes have been saved.','info');
					$Core->json_obj->callback = 'nanobyte.closeParentTab';
					$Core->json_obj->args = 'input[name=submit][value=Save]';
					return;
				}
			}
			$Core->smarty->assign(self::addMenuItemForm($Core->args[2]));
			$content = $Core->smarty->fetch('list.tpl');
		}else{ //no menu is specified, so lets create a new one
			$Core->smarty->assign(self::addMenu());
			$content = $Core->smarty->fetch('form.tpl');
		}
		return $content;
	}
	
	public static function addMenu(){
		$Core = parent::getCore();
		$element_array = array('name'=>'new-menu','method'=>'post','action'=>'admin/menu/add');
		$element_array['elements'] = array(
			array('type'=>'header','name'=>'','label'=>'Create new Account'),
			array('type'=>'text', 'name'=>'name', 'label'=>'Menu Name', array('size'=>25, 'maxlength'=>50)),
			array('type'=>'submit', 'name'=>'submit', 'value'=>'Submit')
		);
		$element_array['callback'] = array(new Menu(),'Create');
		//If the form has already been submitted - validate the data
//		if(isset($_POST['submit']) && $form->validate()){
//			$menu = new Menu();
//			$form->process(array($menu,'Create'));
//			return true;
//			//parent::Redirect();
//			//exit;
//		}
		//send the form to smarty
		return array('form'=>parent::generateForm($element_array)); 
	}
	
	public static function addMenuItemForm($menuID){
		$perms = new Perms();
		$perms->GetNames();
		$menu = new Menu('main');
		$fields = array('path'=>'linkpath','title'=>'linktext','class'=>'class','styleid'=>'styleid');
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
		return array('list'=>$items,'extra'=>'<input type="submit" value="Save" name="submit"/>','formAction'=>'admin/menu/add/'.$menuID);
	}
	
	public static function admin(){
		$Core = parent::getCore();
		
		$tabs = array($Core->l('Menus','admin/menu/list'));
		$Core->smarty->assign(array('tabs'=>$tabs,'showID'=>true));
		if($Core->ajax){$Core->json_obj->tabs = $Core->smarty->fetch('tabs.tpl');}
		
		$content = '';
		
		if(isset($Core->args[1])){
			if($Core->args[1]=='list'){
				$Core->smarty->assign(self::listMenus());
				$content = $Core->smarty->fetch('list.tpl');
			}else{
				$content = call_user_func(array("MenuController",$Core->args[1]));
			}
		}

		$Core->json_obj->content = $content;
	}
	
	public static function delete(){
		$Core = parent::getCore();
		if($Core->args[2] && $Core->args[3]){
			if(Admin::deleteObject('menu_links','id',$Core->args[3])){
				$Core->json_obj->callback = 'nanobyte.deleteRows';
				$Core->json_obj->args = $Core->args[3];
			}
//			parent::Redirect();
		}elseif($Core->args[2]){
			$menu = new Menu($Core->args[2]);
			$menu->delete();
			$Core->json_obj->callback = 'nanobyte.deleteRows';
			$Core->json_obj->args = $menu->menu[0]->mid.'|';
		}
	}

	public static function edit(){
		$Core = parent::getCore();
		if(isset($Core->args[2])){
			if (isset($_POST['submit'])){
				if(self::writeMenu($Core->args[2],$_POST)){
					$Core->setMessage('Your changes have been saved.','info');
					$Core->json_obj->callback = 'nanobyte.closeParentTab';
					$Core->json_obj->args = 'input[name=submit][value=Submit]';
				}
			}else{
				$Core->smarty->assign(self::listMenuItems($Core->args[2]));
				return $Core->smarty->fetch('list.tpl');
			}
		}else{
			$Core->setMessage('You must specify a menu!', 'error');
		}
		return;
	}
	
	public static function getMenu($name, $permission){
		$Core = parent::getCore();
		$premenu = new Menu($name);
		$menu = array();
		foreach ($premenu->menu as $preitem){
			if(strpos($preitem->viewableby,$permission)!==false){
				$options = array('id'=>$preitem->styleid,'class'=>$preitem->class);
				if(file_exists(THEME_PATH."/images/".strtolower($preitem->linktext)."-menu.png")){
					$options['image'] = 'menu';
				}
				array_push($menu,Core::l(strtolower($preitem->linktext),$preitem->linkpath,$options));
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
				'id'=>$menu['mid'],
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
			'sublinks'=>$links,
			'showID'=>false
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
		$options['class'] = 'action-link';
		foreach($menuItems->menu as $item){
			if(isset($item->id)){
				$items[$i] = array(
					'id'=>$item->id,
					'path'=>'<input type="text" size="15" value="'.$item->linkpath.'" name="tb_'.$item->id.'_linkpath"/>',
					'title'=>'<input type="text" size="15" value="'.$item->linktext.'" name="tb_'.$item->id.'_linktext"/>',
					'class'=>'<input type="text" size="10" value="'.$item->class.'" name="tb_'.$item->id.'_class"/>',
					'element id'=>'<input type="text" size="10" value="'.$item->styleid.'" name="tb_'.$item->id.'_styleid"/>'
				);
				foreach($perms->names as $pname){
					$checked = strpos($item->viewableby,$pname) !== false ? 'checked="checked"' : '';
					$items[$i][$pname] = '<input type="checkbox" name="cb_'.$item->id.'_'.$pname.'[]" value="'.$pname.'" '.$checked.'/>';
				}
				$items[$i]['actions'] = Core::l('delete','admin/menu/delete/'.$mid.'/'.$item->id,$options);
				$i++;
			}else{
				$items[0] = array(''=>"There are no links added to this menu.");
			}
		}		
		$options['image'] = '24';
		$options['class'] = 'action-link';
		$links = array('add'=>Core::l('add','admin/menu/add/'.$mid,array('image'=>'24','class'=>'action-link-tab','title'=>'Add Links to menu')));
		return array(
			'sublinks'=>$links,
			'list'=>$items,
			'extra'=>'<input type="submit" value="Submit" name="submit"/>',
			'formAction'=>'admin/menu/edit/'.$mid,
			'showID'=>false
		);
	}
	
	public static function writeMenu($id,$data){
		$Core = parent::getCore();
		$func = $Core->args[1] =='add' ? true : false;
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
			$menu->commit($id,$func);
			return true;
		}else{
			return false;
		}
	}
	
}

?>