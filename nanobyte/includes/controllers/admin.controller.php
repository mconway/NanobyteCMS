<?php

class AdminController extends BaseController{
	function __construct(){
			//make construct check for perms, hash and then make object.
	}
	public function DeleteUserRequest(){
 		if (isset($_GET['uid'])){
 			$delUser[] = $_GET['uid'];
 		}elseif(isset($_POST['users'])){
 			$delUser = $_POST['users'];
 		}
 		if(isset($delUser)){
	 		foreach($delUser as $delete){
	 			if ($user->uid != $delete){
 					if (Admin::DeleteObject('user', 'uid', $delete) === true && Admin::DeleteObject('user_profiles', 'uid', $delete)){
						Core::SetMessage('User '.$delete.' has been deleted!', 'info');
					} else {
						Core::SetMessage('Unable to delete user '.$delete.' , an error has occurred.', 'error');
					}
 				}else{
 					Core::SetMessage('You are not allowed to delete yourself!', 'error');
	 			}
 			}	
 		}else{
 			Core::SetMessage('You must choose a user(s) to delete!', 'error');
 		}
	}
	public static function EditConfig($params){
		$params['dbuser'] = Admin::EncodeConfParams($params['dbuser']);
		$params['dbpass'] = Admin::EncodeConfParams($params['dbpass']);
		Admin::WriteConfig($params);
	}
	public static function ShowConfig(){
		global $smarty;
		//create the tabs menu
		$tablinks = array('DB Settings','Global Settings','File Settings');
		//create the form object 
		$form = new HTML_QuickForm('newuser','post','admin/settings');
		//set form defaults
		$form->setdefaults(array(
			'dbuser'=>DB_USER,
			'dbpass'=>DB_PASS,
			'dbhost'=>DB_HOST,
			'dbname'=>DB_NAME,
			'dbprefix'=>DB_PREFIX,
			'path'=>PATH,
			'sitename'=>SITE_NAME,
			'siteslogan'=>SITE_SLOGAN,
			'sitedomain'=>SITE_DOMAIN,
			'uploadpath'=>UPLOAD_PATH,
			'filesize'=>FILE_SIZE,
			'filetypes'=>FILE_TYPES,
			'cleanurl'=>CLEANURL === 'true' ? true : false
		));
		//create form elements
		$form->addElement('header','','DB Settings');
		$form->addElement('text', 'dbuser', 'DB Username', array('size'=>25, 'maxlength'=>60));
		$form->addElement('password', 'dbpass', 'DB Password', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'dbhost', 'DB Host', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'dbname', 'DB Name', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'dbprefix', 'DB Prefix', array('size'=>25, 'maxlength'=>60));
		
		$form->addElement('header','','Global Site Settings');
		$form->addElement('text', 'path', 'Site Path', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'sitename', 'Site Name', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'siteslogan', 'Site Slogan', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'sitedomain', 'Domain', array('size'=>25, 'maxlength'=>60));
		$form->addElement('checkbox', 'cleanurl' ,'Enable Clean URLs');
		
		$form->addElement('header','','File Settings');
		$form->addElement('text', 'uploadpath', 'Upload Path', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'filesize', 'Max File Size', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'filetypes', 'Allowed File Types', array('size'=>25, 'maxlength'=>60));
		
		
		
		$form->addElement('submit', 'submit', 'Submit');
		//apply form prefilters
		$form->applyFilter('__ALL__', 'trim');
		$form->applyFilter('__ALL__', 'strip_tags');
		//If the form has already been submitted - validate the data
		if($form->validate()){
			$form->process(array('AdminController','EditConfig'));
			Core::SetMessage('Settings have been saved successfully.','info');
			BaseController::Redirect('admin');
			exit;
		}
		//send the form to smarty
		$smarty->assign('form', $form->toArray());
		$smarty->assign('tabbed',$tablinks);

	}
	public static function GetAdminMenu(){
		$array = array('users','posts','modules','blocks','menus','stats','perms','settings','logs');
		foreach ($array as $link){
			$links[$link] = Core::Url('admin/'.$link);
		}
		return $links;
	}
	public static function ListStats($page){
		global $smarty;
		$stats = new Stats();
		$start = BaseController::GetStart($page,15);
		$statsArray = $stats->Read($start, $_POST['Date_Day'], $_POST['Date_Month'], $_POST['Date_Year']);
		
		$smarty->assign('list',$statsArray['items']);
		$smarty->assign('pager',BaseController::Paginate($statsArray['limit'], $statsArray['nbItems'], 'admin/stats/', $page));
		$hits = $stats->UniqueHits();	
		
		$formTop = '<div><form id="daterange" name="daterange" method="post" action="http://beta.wiredbyte.com/WiredCMS/admin/stats">
	{html_select_date}
	<input type="submit" name="Submit" value="Submit" />
</form></div><div id="hits">Hits Today: '.$hits['day'].' | Hits Total: '.$hits['total'].'</div>';
	$smarty->assign('extra', $formTop);
	}
	
	public static function ListPerms($perms){
		global $smarty;
		//create list
		foreach($perms->all as $group){
			$list[] = array(
				'id'=>$group['gid'],
				'group name'=>$group['name'],
				'comments'=>$group['comments']	
			);
		}
		//create the actions options
		$actions = array('delete' => 'Delete');
		$extra = 'With Selected: {html_options name=actions options=$actions}<input type="submit" name="submitaction" value="Go!"/>';
		$options['image'] = '24';
		$links = array('header'=>'Actions: ','add'=>Core::l('add','admin/perms/add',$options), 'edit'=>Core::l('edit','admin/perms/edit',$options));
		// bind the params to smarty
		$smarty->assign('cb',true);
		$smarty->assign('sublinks',$links);
		$smarty->assign('self','admin/perms');
		$smarty->assign('actions',$actions);
		$smarty->assign('extra', $extra);
		$smarty->assign('list', $list);
	}
	public static function EditGroups($perms){
		global $smarty;
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
		$smarty->assign('self'. 'admin/perms/edit');
		$smarty->assign('list',$list);
		$smarty->assign('extra', '<input type="submit" value="Submit" name="submit"/>');
	} 
	public static function WriteGroups($perms){
		$perms->data = $_POST;
		unset($perms->data['submit']);
		$perms->commit();
	}
	public static function AddGroup(){
		global $smarty;
		
		//create the form object 
		$form = new HTML_QuickForm('newgroup','post','admin/perms/add');
		
		//create form elements
		$form->addElement('header','','Add New Permissions Group');
		$form->addElement('text', 'name', 'Group Name', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'comments', 'Comments', array('size'=>25, 'maxlength'=>60));
		
		$form->addElement('submit', 'submit', 'Submit');
		//apply form prefilters
		
		$form->applyFilter('__ALL__', 'trim');
		$form->applyFilter('__ALL__', 'strip_tags');
		
		//If the form has already been submitted - validate the data
		if($form->validate()){
			$perms = new Perms();
			$form->process(array($perms,'AddGroup'));
			Core::SetMessage('Your group has been created successfully.','info');
			BaseController::Redirect('admin/perms');
			exit;
		}
		
		//send the form to smarty
		$smarty->assign('form', $form->toArray()); 

	}
	public static function DeleteGroup(){
		if(isset($_POST['perms'])){
 			$del = $_POST['perms'];
	 		foreach($del as $delete){
 				$deleted = Admin::DeleteObject('groups', 'gid', $delete);
				if ($deleted === true){
					Core::SetMessage('Group ID '.$delete.' has been deleted!', 'info');
				}else{
					Core::SetMessage('Unable to delete Group ID'.$delete.' , an error has occurred.', 'error');
				}
			}
 		}else{
 			Core::SetMessage('You must choose one or more groups to delete!', 'error');
 		}
		BaseController::Redirect('admin/perms');
		exit;
	}
	public static function BrowserGraph(){
		require_once 'includes/contrib/phplot/phplot.php';
		$stats = new Stats(false);
		$array = array_count_values($stats->GetStats('browser','WEEK'));
		$leg = array_keys($array);
		array_unshift($array, '');
		$data = array($array,array());
		$plot = new PHPlot(300,200);
		$plot->setTransparentColor('white');
		$plot->SetTextColor('snow');
		$plot->SetLabelScalePosition(0.32);
		$plot->SetTitle('Weekly requests by Browser');
		$plot->SetTitleColor('snow');
		$plot->SetOutputFile('files/browsergraph.png');
		$plot->SetIsInline(true);
		$plot->SetDataType('text-data');
		$plot->SetDataValues($data);
		$plot->SetLegend($leg);
		$plot->SetPlotType('pie');
		$plot->DrawGraph();

	}
}

?>