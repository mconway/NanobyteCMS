<?php
	class GroupController extends BaseController{
		
		public static function add(){
			$Core = parent::getCore();
			$Core->smarty->assign('form',self::AddForm());
			return $Core->smarty->fetch('form.tpl');
		}
	
		public static function addForm(){
			//create the form object 
			$element_array = array('name'=>'newgroup','method'=>'post','action'=>'admin/group/add');
			
			//create form elements
			$element_array['elements'] = array(
				array('header','','Add New Permissions Group'),
				array('text', 'name', 'Group Name', array('size'=>25, 'maxlength'=>60)),
				array('text', 'comments', 'Comments', array('size'=>25, 'maxlength'=>60)),
				array('submit', 'submit', 'Submit')
			);
			
			//apply form prefilters
			//$form->applyFilter('__ALL__', 'trim');
			//$form->applyFilter('__ALL__', 'strip_tags');
			
			// Add required Fields
			//$form->addRule('name', 'A Group Name is required.', 'required');
			
			//If the form has already been submitted - validate the data
			$element_array['callback'] = array(new Perms(),'addGroup');
			
			if(isset($_POST['submit']) && $form->validate()){
				$Core = parent::getCore();
				$Core->setMessage('Your group has been created successfully.','info');
				$Core->json_obj->callback = 'nanobyte.closeParentTab';
				$Core->json_obj->args = 'input[name=submit][value=Submit]';
			}
			//send the form to smarty
			return parent::generateForm($element_array); 
		}
		
	    public static function admin(){
			$Core = parent::getCore();
			$content = '';
			$perms = new Perms();
			if(isset($Core->args[1])){
				if($Core->args[1] == 'list'){
					$perms->getAllGroups();
					$Core->smarty->assign(self::listGroups($perms));
					$content = $Core->smarty->fetch('list.tpl');
				}elseif(method_exists('GroupController',$Core->args[1])){
					$content = call_user_func(array('GroupController',$Core->args[1]));
				}
			}else{
				$tabs = array($Core->l('Users','admin/user/list'),$Core->l('Groups','admin/group/list'));
				$Core->smarty->assign('tabs',$tabs);
				if($Core->ajax){$Core->json_obj->tabs = $Core->smarty->fetch('tabs.tpl');}
			}
			$Core->json_obj->content = $content;
		}
	
		public static function delete(){
			$Core = parent::getCore();
			if(isset($_POST['group'])){
	 			$del = $_POST['group'];
				$retArray = array();
		 		foreach($del as $delete){
	 				$deleted = Admin::DeleteObject('groups', 'gid', $delete);
					if ($deleted === true){
						array_push($retArray,$delete);
						$Core->setMessage('Group ID '.$delete.' has been deleted!', 'info');
					}else{
						$Core->setMessage('Unable to delete Group ID'.$delete.' , an error has occurred.', 'error');
					}
				}
				return $retArray;
	 		}else{
	 			$Core->setMessage('You must choose one or more groups to delete!', 'error');
	 		}
			//parent::Redirect('admin/perms');
			exit;
		}	
		
		public static function edit(){
			$Core = parent::getCore();
			$perms = new Perms();
			if(isset($_POST['submit'])){
		 		self::Write($perms);
				$Core->json_obj->callback = 'nanobyte.closeParentTab';
				$Core->json_obj->args = 'input[name=submit][value=Submit]';
			}else{					
				$Core->smarty->assign(self::editForm($perms));
				$Core->json_obj->callback = 'Dialog';
				return $Core->smarty->fetch('list.tpl');
			}
		}
		
		public static function editForm(&$perms){
			$perms_list = $perms->GetPermissionsList();
			$perms->GetAllGroups();
			$list = array();
			$i = 0;
			foreach($perms_list as $p){
				$list[$i] = array('description'=>$p->description);
				foreach($perms->groups as $group){
					$list[$i][$group['name']] = "<input type='checkbox' name='{$group['name']}[]' value='{$p->id}'/>";
					if(!isset($group_perms[$group['gid']])){
						$group_perms[$group['gid']] = $perms->getPermissionsForGroup($group['gid']);
					}
					if(empty($group_perms[$group['gid']])){
						$list[$i][$group['name']] = "<input type='checkbox' name='{$group['name']}[]' value='{$p->id}'/>";
						continue;
					}
					foreach($group_perms[$group['gid']] as $gp){
						if($gp->description == $p->description){
							$list[$i][$group['name']] = "<input type='checkbox' name='{$group['name']}[]' value='{$p->id}' checked />";
							break;
						}
					}
				}
				$i++;
			}
			return array(
				'formAction'=>'admin/group/edit',
				'list'=>$list,
				'extra'=>'<input type="submit" value="Submit" name="submit"/>'
			);
		} 

		public static function listGroups($perms){
			//create list
			$list = array();
			foreach($perms->groups as $group){
				array_push($list,array(
					'id'=>$group['gid'],
					'name'=>$group['name'],
					'comments'=>$group['comments']	
				));
			}
			//create the actions options
			$actions = array('delete' => 'Delete');
			$extra = 'With Selected: {html_options name=actions options=$actions}<input type="submit" name="submit" value="Go!"/>';
			$options = array(
				'title' => "Add new group",
				'image' => "24",
				'class' => "action-link-tab"
			);
			$links = array('add'=>Core::l('add','admin/group/add',$options));
			$options['title'] = 'Edit Group Permissions';
			$links['edit'] = Core::l('perms','admin/group/edit',$options);
			// bind the params to smarty
			return array(
				'cb'=>true,
				'sublinks'=>$links,
				'formAction'=>'admin/group/select',
				'actions'=>$actions,
				'extra'=>$extra,
				'list'=>$list
			);
		}
		
		public static function select(){
			$Core = parent::getCore();
			switch($Core->args[2]){
				case 'delete':
					$Core->json_obj->callback = 'nanobyte.deleteRows';
					$Core->json_obj->args = implode('|',self::delete());
					break;
				default: 
					break;
			}
		}
		
		public static function write($perms){
			$perms->data = $_POST;
			unset($perms->data['submit']);
			$perms->commit();
		}
		
	}
?>