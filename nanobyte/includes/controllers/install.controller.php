<?php
	class InstallController extends BaseController{
		
		const SCRIPT = 'var i = 25;
				$(document).ready(function(){
					$("#main").prepend(\'<br/><div id="progressbar"><div id="pb-text"><span>0</span><span>/</span><span>4</span></div></div><br/>\');
					$("#progressbar").progressbar();
					$("form input[type=submit]").livequery(function(){
						$(this).click(increaseProgressBar)
					})
					$("#pb-text").css("position","absolute").css("left",$("#progressbar").offset().left+390+"px");
					$("#createdb").live("click",function(){
						nanobyte.submitForm($(this).parent("form"),$(this));
						increaseProgressBar();
						
					})
				});
				function increaseProgressBar(){
					var step = parseInt($("#pb-text").children("span:first-child").text())+1;
					if(step <= parseInt($("#pb-text").children("span:last-child").text())){
						$("#pb-text").children("span:first-child").text(step);
						$("#progressbar").progressbar("option", "value", i);
						i += 25;
					}
				}
				function updateBlock(title,body){
					$(".block_title").children("h2").text(title);
					$(".block_body").text(body);
				}
				';
		
		public static function Display(){
			$Core = parent::getCore();
			

			
			$Install = new Install();
			if(isset($Core->args[0])){
				$content = call_user_func(array('self',$Core->args[0]),new Install());
			}else{
				$content = self::license();
			}
			//Set block elements
			$Core->smarty->assign(array(
				'block_title'=>'Licensing',
				'block_body'=>'Please read and agree to the terms of the software license.'
			));
			$Core->smarty->assign(array(
				'extraScript'=>self::SCRIPT,
				'content'=>$content,
				//We need to create an empty block. instead of the riggamaroll of trying to add it to a the database, 
				//and use module controller, we are going to force it manually.
				'blocks'=> array('rightsidebar'=>$Core->smarty->fetch('block.tpl'))
			));
//			$jsonObj->content = $smarty->fetch('index.tpl');
			if(!$Core->ajax){
				parent::DisplayMessages(); // Get any messages
				parent::GetHTMLIncludes(); // Get CSS and Script Files
				$Core->smarty->display('index.tpl'); // Display the Page
			}else{
				$Core->json_obj->content = $content;
				$Core->json_obj->messages = parent::displayMessages();
				print json_encode($Core->json_obj);
			}
		}
		
		public static function license(){
			$Core = parent::getCore();
			$Core->smarty->assign('self','Beginning Nanobyte Installation');
			$Core->SetMessage('Beginning Installation','info');
			$content = <<<EOF
			The Nanobyte CMS has not been installed on this domain yet. You have been redirected to this page to begin the installation!
			Please begin by accepting the license agreement.<br/><br/><form name='license' method='post' action='install/step1'><textarea cols="80" rows="25" readonly>
EOF;
			$fh = fopen('license', 'r');
			$content.= fread($fh, filesize('license'));
			fclose($fh);
			$content .= "</textarea><br/>
			<label>I Agree</label> <input type='radio' name='agree' value='1' />  
			<label>I Disagree</label> <input type='radio' name='agree' value='0' /> <br/>
			<input type='submit' name='continue' value='Continue'/>";
			return $content;
		}
		
		public static function step1(&$Install){
			$Core = parent::getCore();
			if(array_key_exists('submit',$_POST)){
				if(array_key_exists('agree',$_POST) && $_POST['agree']==0){
					if($Core->ajax){
						$Core->json_obj->callback = 'nanobyte.redirect';
						$Core->json_obj->args = 'install/cancel';
						break;
					}else{
						BaseController::Redirect('install/cancel');
						break;
					}
				}
			}
			$Core->smarty->assign('formAction',"install/step2");

			$Install->CheckRequirements();
			if($Install->continue === true){
				$Core->smarty->assign('extra','<input type="submit" name="next" value="Next" />');
			}
			$Core->smarty->assign('list',$Install->requirements);
			$content = $Core->smarty->fetch('list.tpl');
			$Core->json_obj->block_title = 'Step 1';
			$Core->json_obj->block_body = 'Checking Requirements.. You may continue if there are no errors here.';
			return $content;
		}
	
		public static function step2(&$Install){
			$Core = parent::getCore();
			$Core->smarty->assign('self',"Step 2 - Database Settings");
			
			AdminController::ShowConfig('install/step3');

			$content = $Core->smarty->fetch('form.tpl');
			
			$Core->json_obj->block_title = 'Step 2';
			$Core->json_obj->block_body = 'Generate Configuration: Please be sure to enter as much information as possible about your setup. Database configuration and Global Settings are required!';
			
			return $content;
		}
	
		public static function step3(&$Install){
			$Core = parent::getCore();
			if(AdminController::ShowConfig('')!==true){ //save the posted data
				$Core->setMessage("An error occurred saving your settings, please try again.","error");
				return;
			}
			$content = "We will now create the database and required tables. Click the create database button to continue.<br /><form action='install/createDatabase'><input type='button' id='createdb' value='Create Database'/></form>";
			
			$Core->json_obj->block_title = 'Step 3';
			$Core->json_obj->block_body = 'Generate the database. Click the Create database button to proceed. WARNING: This will drop all existing tables with the same db_prefix as specified in step 2!';
			
			return $content;
		}
	
		public static function step4(){
			$Core = parent::getCore();
			
			$ret_val = UserController::regForm('install/step4');
			if(is_array($ret_val)){
				$Core->json_obj->block_title = 'Step 4';
				$Core->json_obj->block_body = 'Create admin user: Please enter credentials used by the site admin. This will be the first account created and will be granted FULL ACCESS to the site.';
				$Core->smarty->assign('form',$ret_val);
				return $Core->smarty->fetch('form.tpl');
			}elseif($ret_val === true){
				$Core->json_obj->block_title = 'Installation Complete!';
				$Core->json_obj->block_body = 'Installation of the Nanobyte CMS has been completed.';
				$Core->SetMessage('Your user account has been created!','info');
				Admin::toggleCMSInstalled(true);
				$user = new User(1);
				$user->permissions->addUserToGroup($user->uid,'1');
				return "Installation is complete! Click <a href='".SITE_DOMAIN."/".PATH."'>here</a> to go to your site!";
			}
			
		}
	
		public static function createDatabase(&$Install){
			$Core = parent::getCore();
			if($Install->installDB()){
				$Core->setMessage("Database Installed!","info");
				return self::step4();
			}
		}
	}
?>