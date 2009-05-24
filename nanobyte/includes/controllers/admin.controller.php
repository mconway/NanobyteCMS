<?php

class AdminController extends BaseController{
	
	function __construct(){
		//make construct check for perms, hash and then make object.
	}
	
	public static function Display(&$argsArray){ //passed by call_user_func
		list($args,$ajax,$smarty,$user,$jsonObj,$core) = $argsArray;
		// Check user permissions
	 	if (array_key_exists('hash',$_SESSION) && $_SESSION['hash'] == $user->SessionHash() && Core::AuthUser($user, 'access admin pages')){
	 		$smarty->assign('page', array_key_exists(0,$args) ? $args[0] : 'Administration'); //Set Page Name
	 		$smarty->assign('links', MenuController::GetMenu('admin',$user->group));  // Get the Admin Menu
			if(!empty($args[0])){
				if($core->autoload($args[0].'Controller',false)){
					$class = $args[0].'Controller';
				}elseif($core->autoload('Mod_'.$args[0],false)){
					$class = 'Mod_'.$args[0];
				}else{
					$alias = $core->CheckAlias($args[0]);
					$class = $alias."Controller";
//					Core::SetMessage($alias." ".$class);
				}
				call_user_func(array($class, 'Admin'),$argsArray);
			}else{
				if (isset($_POST['save'])){
					PostController::SavePost();
				}
//				$smarty->assign('users',Mod_Users::NewUsers());
//				Mod_Stats::BrowserGraph();
				$jsonObj->content = $smarty->fetch('admin.main.tpl');
			}
			if(!$ajax){
				parent::AddJs(THEME_PATH.'/js/admin.js');
				parent::DisplayMessages(); // Get any messages
				parent::GetHTMLIncludes(); // Get CSS and Script Files
				$smarty->assign('content',$argsArray[4]->content);
				$smarty->display('admin.tpl'); // Display the Admin Page
			}else{
				$jsonObj->messages = BaseController::DisplayMessages();
				print json_encode($argsArray[4]);
			}
		}else{
			Core::SetMessage('You do not have access to view this page!','error');
			parent::Redirect('home',$ajax);
		}
	}
	
	public static function Admin(&$argsArray){
		list($args,$ajax,$smarty,$user,$jsonObj) = $argsArray;
		
 		self::ShowConfig();
		$jsonObj->content =  $smarty->fetch('form.tpl');
	}
	
	public static function EditConfig($params){
		$params['dbuser'] = Admin::EncodeConfParams($params['dbuser']);
		$params['dbpass'] = Admin::EncodeConfParams($params['dbpass']);
		$params['smtp_user'] = Admin::EncodeConfParams($params['smtp_user']);
		$params['smtp_pass'] = Admin::EncodeConfParams($params['smtp_pass']);
		Admin::WriteConfig($params);
	}
	
	public static function ShowConfig(){
		global $smarty, $core;
		$perms = new Perms();
		$perms->GetNames();
		//create the tabs menu
		$tablinks = array('Global Settings','DB Settings','File Settings', 'Email Settings', 'Theme Settings', 'User Settings', 'License');
		//create the form object 
		$form = new HTML_QuickForm('newuser','post','admin/settings');
		
		//get the site license
		$fh = fopen('license', 'r');
		$license = fread($fh, filesize('license'));
		fclose($fh);
		
		//set form defaults
		$form->setdefaults(array(
			'dbuser'=>$core->DecodeConfParams(DB_USER),
			'dbpass'=>$core->DecodeConfParams(DB_PASS),
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
			'cleanurl'=>CLEANURL,
			'themepath'=>str_replace('templates/','',THEME_PATH),
			'defaultgroup'=>DEFAULT_GROUP,
			'sessttl'=>SESS_TTL,
			'compress'=>COMPRESS,
			'home'=>HOME,
			'limit'=>LIMIT,
			'license'=>$license,
			'from_name'=>EMAIL_FROM,
			'subject'=>EMAIL_SUBJECT,
			'use_html'=>EMAIL_IS_HTML,
			'smtp_auth'=>SMTP_AUTH,
			'smtp_host'=>SMTP_SERVER,
			'smtp_port'=>SMTP_PORT,
			'smtp_user'=>$core->DecodeConfParams(SMTP_USER),
			'smtp_pass'=>$core->DecodeConfParams(SMTP_PASS)
		));
		//create form elements
		$form->addElement('header','','Global Site Settings');
		$form->addElement('text', 'path', 'Site Path', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'pearpath', 'Pear Include Path', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'sitename', 'Site Name', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'siteslogan', 'Site Slogan', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'sitedomain', 'Domain', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'limit', 'Default Limit on Table Lists', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'home', 'Default Home Page', array('size'=>25, 'maxlength'=>60));
		$form->addElement('checkbox', 'cleanurl' ,'Enable Clean URLs');
		$form->addElement('checkbox', 'compress' ,'Enable Javascript and CSS Compression');
		
		$form->addElement('header','','DB Settings');
		$form->addElement('text', 'dbuser', 'DB Username', array('size'=>25, 'maxlength'=>60));
		$form->addElement('password', 'dbpass', 'DB Password', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'dbhost', 'DB Host', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'dbname', 'DB Name', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'dbprefix', 'DB Prefix', array('size'=>25, 'maxlength'=>60));
		
		$form->addElement('header','','File Settings');
		$form->addElement('text', 'uploadpath', 'Upload Path', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'filesize', 'Max File Size', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'filetypes', 'Allowed File Types', array('size'=>25, 'maxlength'=>60));
		
		$form->addElement('header','','Email Settings');
		$form->addElement('text', 'smtp_host', 'SMTP Host', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'smtp_port', 'SMTP Port (25)', array('size'=>25, 'maxlength'=>60));
		$form->addElement('checkbox', 'smtp_auth' ,'SMTP Server Uses Authentication');
		$form->addElement('text', 'smtp_user', 'SMTP Username', array('size'=>25, 'maxlength'=>60));
		$form->addElement('password', 'smtp_pass', 'SMTP Password', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'from_name', 'Default FROM Name', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'subject', 'Default Subject', array('size'=>25, 'maxlength'=>60));
		$form->addElement('checkbox', 'use_html' ,'Compile Emails in HTML');
		
		$form->addElement('header','','Theme Settings');
		$form->addElement('select', 'themepath', 'Select Theme', BaseController::GetThemeList());
		
		$form->addElement('header', '', 'User Settings');
		$form->addElement('select', 'defaultgroup', 'Choose Default group for new Users', $perms->names);
		$form->addElement('text', 'sessttl', 'Time before users\' sessions expire (in seconds)',array('size'=>10, 'maxlength'=>10));
		
		$form->addElement('header','','License');
		$form->addElement('textarea','license','',array('rows'=>20,'cols'=>70,'readonly','disabled'));
		
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

}



?>