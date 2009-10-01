<?php

class AdminController extends BaseController{
	
	function __construct(){
		//make construct check for perms, hash and then make object.
	}
	
	public static function admin(){
		$Core = parent::getCore();
 		if(is_array(self::ShowConfig())){
 			$Core->json_obj->content =  $Core->smarty->fetch('form.tpl');
 		}else{
 			$Core->SetMessage('Settings have been saved successfully.','info');
 		}
	}
	
	public static function display(){ //passed by call_user_func
		$Core = parent::getCore();
		// Check user permissions
	 	if (array_key_exists('hash',$_SESSION) && $_SESSION['hash'] == $Core->user->SessionHash() && $Core->authUser('access admin pages')){
	 		$Core->smarty->assign('page', isset($Core->args[0]) ? $Core->args[0] : 'Administration'); //Set Page Name
	 		$Core->smarty->assign('links', MenuController::getMenu('admin',$Core->user->group));  // Get the Admin Menu
			if(!empty($Core->args[0])){
				if(parent::autoload($Core->args[0].'Controller',false)){
					$class = $Core->args[0].'Controller';
				}elseif(parent::autoload('Mod_'.$Core->args[0],false)){
					$class = 'Mod_'.$Core->args[0];
				}else{
					$alias = $Core->checkAlias($Core->args[0]);
					$class = $alias."Controller";
//					Core::SetMessage($alias." ".$class);
				}
				call_user_func(array($class, 'Admin'));
			}else{
				if (isset($_POST['save'])){
					PostController::SavePost();
				}
//				$smarty->assign('users',Mod_Users::NewUsers());
//				Mod_Stats::BrowserGraph();
				$Core->json_obj->content = $Core->smarty->fetch('admin.main.tpl');
			}
			if(!$Core->ajax){
				parent::AddJs(THEME_PATH.'/js/admin.js');
				parent::DisplayMessages(); // Get any messages
				parent::GetHTMLIncludes(); // Get CSS and Script Files
				$Core->smarty->assign('content',$Core->json_obj->content);
				$Core->smarty->display('admin.tpl'); // Display the Admin Page
			}else{
				$Core->json_obj->messages = parent::displayMessages();
				print json_encode($Core->json_obj);
			}
		}else{
			$Core->setMessage('You do not have access to view this page!','error');
			parent::Redirect('home',$Core->ajax);
		}
	}
	
	public static function editConfig($params){
		$params['dbuser'] = Admin::encodeConfParams($params['dbuser']);
		$params['dbpass'] = Admin::encodeConfParams($params['dbpass']);
		$params['smtp_user'] = Admin::encodeConfParams($params['smtp_user']);
		$params['smtp_pass'] = Admin::encodeConfParams($params['smtp_pass']);
		Admin::writeConfig($params);
	}
	
	public static function showConfig($form_action='admin/settings'){
		$Core = parent::getCore();
		if(CMS_INSTALLED){
			$perms = new Perms();
			$perms->GetNames();
		}
		//create the tabs menu
		$tablinks = array('Global Settings','DB Settings','File Settings', 'Email Settings', 'Theme Settings', 'User Settings', 'License');
		//create the form object 
		$form = new HTML_QuickForm('settings','post',$form_action);
		
		//get the site license
		$license = file_get_contents('license');
		
		//set form defaults
		$form->setdefaults(array(
			'dbuser'=>$Core->DecodeConfParams(DB_USER),
			'dbpass'=>$Core->DecodeConfParams(DB_PASS),
			'dbhost'=>DB_HOST,
			'dbname'=>DB_NAME,
			'dbprefix'=>DB_PREFIX,
			'path'=>PATH,
			'sitename'=>SITE_NAME,
			'siteslogan'=>SITE_SLOGAN,
			'sitedomain'=>SITE_DOMAIN,
			'sitelogo'=>SITE_LOGO,
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
			'smtp_user'=>$Core->DecodeConfParams(SMTP_USER),
			'smtp_pass'=>$Core->DecodeConfParams(SMTP_PASS),
			'allowed_html_tags'=>ALLOWED_HTML_TAGS,
			'cms_installed'=>CMS_INSTALLED
		));
		//create form elements
		$form->addElement('header','','Global Site Settings');
		$form->addElement('text', 'path', 'Site Path', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'pearpath', 'Pear Include Path', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'sitename', 'Site Name', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'sitelogo', 'Site Logo', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'siteslogan', 'Site Slogan', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'sitedomain', 'Domain', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'limit', 'Default Limit on Table Lists', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'home', 'Default Home Page', array('size'=>25, 'maxlength'=>60));
		$form->addElement('text', 'allowed_html_tags', 'Allowed HTML Tags', array('size'=>25, 'maxlength'=>60));
		$form->addElement('hidden', 'cms_installed', '', array('size'=>25, 'maxlength'=>60));
		$form->addElement('checkbox', 'cleanurl' ,'Enable Clean URLs');
//		$form->addElement('checkbox', 'compress' ,'Enable Javascript and CSS Compression');
		
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
		$form->addElement('select', 'themepath', 'Select Theme', parent::GetThemeList());
		
		$form->addElement('header', '', 'User Settings');
		$form->addElement('select', 'defaultgroup', 'Choose Default group for new Users', $perms->names);
		$form->addElement('text', 'sessttl', 'Time before users\' sessions expire (in seconds)',array('size'=>10, 'maxlength'=>10));
		
		$form->addElement('header','','License');
		$form->addElement('textarea','license','',array('rows'=>20,'cols'=>70,'readonly','disabled'));
		
		$form->addElement('submit', 'submit', 'Submit');
		//apply form prefilters
		$form->applyFilter('__ALL__', 'trim');
//		$form->applyFilter('__ALL__', 'strip_tags');
		//If the form has already been submitted - validate the data
		if(isset($_POST['submit'])&&$form->validate()){
			$form->process(array('AdminController','EditConfig'));
			return true;
//			parent::redirect('admin');
//			exit;
		}
		//send the form to smarty
		return array(
			'form'=>$form->toArray(),
			'tabbed'=>$tablinks
		);
	}

}



?>