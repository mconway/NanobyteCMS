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
	
class AdminController extends BaseController{
	
	public static function admin(){
		$Core = parent::getCore();
		$data = self::ShowConfig();
 		if(is_array($data)){
 			$Core->smarty->assign($data);
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
			$Core->json_obj->messages = parent::displayMessages();
			print json_encode($Core->json_obj);
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
		
		//get the site license
		$license = file_get_contents('license');
		
		//begin defining the form with form attributes
		$element_array = array('name'=>'settings','method'=>'post','action'=>$form_action);
		
		//set form defaults
		if(is_object($Core)){
		$element_array['defaults']=array(
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
		);
		}
		//create all of the form elements
		$element_array['elements'] = array(	
			array('type'=>'header','name'=>'','label'=>'Global Site Settings','group'=>'0'),
			array('type'=>'text', 'name'=>'path', 'label'=>'Site Path', 'options'=>array('size'=>25, 'maxlength'=>60),'group'=>'0'),
			array('type'=>'text', 'name'=>'sitename', 'label'=>'Site Name', 'options'=>array('size'=>25, 'maxlength'=>60),'group'=>'0'),
			array('type'=>'text', 'name'=>'sitelogo', 'label'=>'Site Logo', 'options'=>array('size'=>25, 'maxlength'=>60),'group'=>'0'),
			array('type'=>'text', 'name'=>'siteslogan','label'=> 'Site Slogan', 'options'=>array('size'=>25, 'maxlength'=>60),'group'=>'0'),
			array('type'=>'text', 'name'=>'sitedomain', 'label'=>'Domain', 'options'=>array('size'=>25,'maxlength'=>60,'group'=>'0')),
			array('type'=>'text', 'name'=>'limit', 'label'=>'Default Limit on Table Lists', 'options'=>array('size'=>25, 'maxlength'=>60),'group'=>'0'),
			array('type'=>'text', 'name'=>'home', 'label'=>'Default Home Page', 'options'=>array('size'=>25,'maxlength'=>60),'group'=>'0'),
			array('type'=>'text', 'name'=>'allowed_html_tags', 'label'=>'Allowed HTML Tags', 'options'=>array('size'=>25, 'maxlength'=>60),'group'=>'0'),
			array('type'=>'hidden', 'name'=>'cms_installed', 'label'=>'', 'options'=>array('size'=>25, 'maxlength'=>60),'group'=>'0'),
			array('type'=>'checkbox', 'name'=>'cleanurl' ,'label'=>'Enable Clean URLs','group'=>'0'),
	//		array('checkbox', 'compress' ,'Enable Javascript and CSS Compression'),
	
			array('type'=>'header','name'=>'','label'=>'DB Settings','group'=>'1'),
			array('type'=>'text', 'name'=>'dbuser', 'label'=>'DB Username', 'options'=>array('size'=>25, 'maxlength'=>60),'group'=>'1'),
			array('type'=>'password', 'name'=>'dbpass', 'label'=>'DB Password', 'options'=>array('size'=>25, 'maxlength'=>60),'group'=>'1'),
			array('type'=>'text','name'=>'dbhost', 'label'=>'DB Host', 'options'=>array('size'=>25, 'maxlength'=>60),'group'=>'1'),
			array('type'=>'text', 'name'=>'dbname', 'label'=>'DB Name', 'options'=>array('size'=>25, 'maxlength'=>60),'group'=>'1'),
			array('type'=>'text', 'name'=>'dbprefix', 'label'=>'DB Prefix','options'=>array('size'=>25, 'maxlength'=>60),'group'=>'1'),
			
			array('type'=>'header','name'=>'','File Settings','group'=>'2'),
			array('type'=>'text', 'name'=>'uploadpath', 'label'=>'Upload Path', 'options'=>array('size'=>25, 'maxlength'=>60),'group'=>'2'),
			array('type'=>'text', 'name'=>'filesize','label'=> 'Max File Size', 'options'=>array('size'=>25, 'maxlength'=>60),'group'=>'2'),
			array('type'=>'text', 'name'=>'filetypes', 'label'=>'Allowed File Types', 'options'=>array('size'=>25, 'maxlength'=>60),'group'=>'2'),
			
			array('type'=>'header','name'=>'','label'=>'Email Settings','group'=>'3'),
			array('type'=>'text', 'name'=>'smtp_host', 'label'=>'SMTP Host', 'options'=>array('size'=>25, 'maxlength'=>60),'group'=>'3'),
			array('type'=>'text', 'name'=>'smtp_port', 'label'=>'SMTP Port (25)', 'options'=>array('size'=>25, 'maxlength'=>60),'group'=>'3'),
			array('type'=>'checkbox', 'name'=>'smtp_auth' ,'label'=>'SMTP Server Uses Authentication','group'=>'3'),
			array('type'=>'text', 'name'=>'smtp_user', 'label'=>'SMTP Username', 'options'=>array('size'=>25, 'maxlength'=>60),'group'=>'3'),
			array('type'=>'password', 'name'=>'smtp_pass', 'label'=>'SMTP Password', 'options'=>array('size'=>25, 'maxlength'=>60),'group'=>'3'),
			array('type'=>'text', 'name'=>'from_name', 'label'=>'Default FROM Name', 'options'=>array('size'=>25, 'maxlength'=>60),'group'=>'3'),
			array('type'=>'text', 'name'=>'subject', 'label'=>'Default Subject', 'options'=>array('size'=>25, 'maxlength'=>60),'group'=>'3'),
			array('type'=>'checkbox', 'name'=>'use_html' ,'label'=>'Compile Emails in HTML','group'=>'3'),
			
			array('type'=>'header','name'=>'','label'=>'Theme Settings','group'=>'4'),
			array('type'=>'select', 'name'=>'themepath', 'label'=>'Select Theme', 'list'=>parent::GetThemeList(),'group'=>'4'),
			
			array('type'=>'header', 'name'=>'', 'label'=>'User Settings','group'=>'5'),
			array('type'=>'select', 'name'=>'defaultgroup', 'label'=>'Choose Default group for new Users', 'list'=>$perms->names,'group'=>'5'),
			array('type'=>'text', 'name'=>'sessttl', 'label'=>'Time before users\' sessions expire (in seconds)','options'=>array('size'=>10, 'maxlength'=>10),'group'=>'5'),
			
			array('type'=>'header','name'=>'','label'=>'License','group'=>'6'),
			array('type'=>'textarea','name'=>'license','label'=>'','options'=>array('rows'=>20,'cols'=>70,'readonly'=>'readonly','disabled'=>'disabled'),'group'=>'6'),
			
			array('type'=>'submit', 'name'=>'submit', 'value'=>'Submit')
		);
		
		$element_array['callback'] = array('AdminController','EditConfig');
		//apply form prefilters
//		$form->applyFilter('__ALL__', 'trim');
//		$form->applyFilter('__ALL__', 'strip_tags');

		$form = self::generateForm($element_array);
	//	$Core = parent::getCore();
		if(is_bool($form)){
	//		$Core->setMessage("Your settings were saved successfully!",'info');
			return $form;
		}

		//send the form to smarty
		return array(
			'form'=>self::generateForm($element_array),
			'tabbed'=>$tablinks
		);
	}

}



?>
