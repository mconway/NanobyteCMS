<?php
	class GroupController extends BaseController{
	
		public static function add(){
			//create the form object 
			$form = new HTML_QuickForm('newgroup','post','admin/group/add');
			//create form elements
			$form->addElement('header','','Add New Permissions Group');
			$form->addElement('text', 'name', 'Group Name', array('size'=>25, 'maxlength'=>60));
			$form->addElement('text', 'comments', 'Comments', array('size'=>25, 'maxlength'=>60));
			$form->addElement('submit', 'submit', 'Submit');
			//apply form prefilters
			$form->applyFilter('__ALL__', 'trim');
			$form->applyFilter('__ALL__', 'strip_tags');
			// Add required Fields
			$form->addRule('name', 'A Group Name is required.', 'required');
			//If the form has already been submitted - validate the data
			if(isset($_POST['submit']) && $form->validate()){
				$perms = new Perms();
				$form->process(array($perms,'AddGroup'));
				Core::SetMessage('Your group has been created successfully.','info');
			}
			//send the form to smarty
			return $form->toArray(); 
		}
		
	    public static function admin(){
			$Core = parent::getCore();
			
			$perms = new Perms();
			switch($Core->args[1]){
				case 'add':
					$Core->smarty->assign('form',self::Add());
					$content = $Core->smarty->fetch('form.tpl');
					break;
				case 'edit':
					if(isset($_POST['submit'])){
				 		self::Write($perms);
					}else{					
						$Core->smarty->assign(self::Edit($perms));
						$content = $Core->smarty->fetch('list.tpl');
						$Core->json_obj->callback = 'Dialog';
					}
					break;
				case 'list': 
					$perms->GetAll();
					$Core->smarty->assign(self::ListGroups($perms));
					$content = $Core->smarty->fetch('list.tpl');
					break;
				case 'select':
					switch($Core->args[2]){
						case 'delete':
							$Core->json_obj->callback = 'nanobyte.deleteRows';
							$Core->json_obj->args = implode('|',self::delete());
							break;
						default: 
							break;
					}
					break;
				default: //Need to set active tab
					$tabs = array($Core->l('Users','admin/user/list'),$Core->l('Groups','admin/group/list'));
					$Core->smarty->assign('tabs',$tabs);
					if($Core->ajax){$Core->json_obj->tabs = $Core->smarty->fetch('tabs.tpl');}
					break;
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
		
		public static function edit(&$perms){
			$permList = $perms->GetPermissionsList();
			$perms->GetAll();
			$i = 0;
			foreach($permList as $group){
				$list[$i] = array();
				$list[$i]['description'] = $group['description'];
				foreach($perms->all as $pset){
					$checked = strpos($pset['permissions'],$group['description']) !== false ? 'checked="checked"' : '';
					$list[$i][$pset['name']] = '<input type="checkbox" name="'.$pset['name'].'[]" value="'.$group['description'].'" '.$checked.'/>';
				}
				$i++;
			}
			return array(
				'self'=>'admin/group/edit',
				'list'=>$list,
				'extra'=>'<input type="submit" value="Submit" name="submit"/>'
			);
		} 

		public static function listGroups($perms){
			//create list
			$list = array();
			foreach($perms->all as $group){
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
		
		public static function write($perms){
			$perms->data = $_POST;
			unset($perms->data['submit']);
			$perms->commit();
		}
		
	}
?>