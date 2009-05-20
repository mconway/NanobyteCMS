<?php
	class InstallController extends BaseController{
		
		public static function Display(&$argsArray){
			list($args,$ajax,$smarty,$jsonObj,$core) = $argsArray;
			
			$Install = new Install();
			switch($args[0]){
				case 'cancel':
					break;
				case 'step1':
					if(array_key_exists('submit',$_POST)){
						if(array_key_exists('agree',$_POST) && $_POST['agree']==0){
							if($ajax){
								$jsonObj->callback = 'nanobyte.redirect';
								$jsonObj->args = 'install/cancel';
								break;
							}else{
								BaseController::Redirect('install/cancel');
								break;
							}
						}
					}
					$smarty->assign('self',"install/step2");

					$Install->CheckRequirements();
					if($Install->continue === true){
						$smarty->assign('extra','<input type="submit" name="submit" value="Next" />');
					}
					$smarty->assign('list',$Install->requirements);
					$content = $smarty->fetch('list.tpl');
					
					break;
				case 'step2':
					$smarty->assign('self',"Step 2 - Database Settings");
					
					require_once('HTML/QuickForm.php');
					
					$form = new HTML_Quickform('newuser','post','admin/settings');
					
					$form->setdefaults(array(
						'dbuser'=>$core->DecodeConfParams(DB_USER),
						'dbpass'=>$core->DecodeConfParams(DB_PASS),
						'dbhost'=>DB_HOST,
						'dbname'=>DB_NAME,
						'dbprefix'=>DB_PREFIX
					));
					
					$form->addElement('header','','Database Settings');
					$form->addElement('text', 'dbuser', 'DB Username', array('size'=>25, 'maxlength'=>60));
					$form->addElement('password', 'dbpass', 'DB Password', array('size'=>25, 'maxlength'=>60));
					$form->addElement('text', 'dbhost', 'DB Host', array('size'=>25, 'maxlength'=>60));
					$form->addElement('text', 'dbname', 'DB Name', array('size'=>25, 'maxlength'=>60));
					$form->addElement('text', 'dbprefix', 'DB Prefix', array('size'=>25, 'maxlength'=>60));
					
					$form->addElement('submit', 'submit', 'Submit');
					//apply form prefilters
					$form->applyFilter('__ALL__', 'trim');
					$form->applyFilter('__ALL__', 'strip_tags');
					//If the form has already been submitted - validate the data
					if(array_key_exists('dbhost',$_POST)){
						if($form->validate()){
							$form->process(array('AdminController','EditConfig'));
							Core::SetMessage('Settings have been saved successfully.','info');
						}						
					}
					//send the form to smarty
					$smarty->assign('form', $form->toArray());
					$content = $smarty->fetch('form.tpl');
					break;
				default:
					$smarty->assign('self','Beginning Nanobyte Installation');
					$core->SetMessage('Beginning Installation','info');
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
					<input type='submit' name='submit' value='Continue'/>";
					break;
			}
			$smarty->assign('content',$content);
//			$jsonObj->content = $smarty->fetch('index.tpl');
			if(!$ajax){
				parent::DisplayMessages(); // Get any messages
				parent::GetHTMLIncludes(); // Get CSS and Script Files
				$smarty->display('index.tpl'); // Display the Page
			}else{
				$jsonObj->content = $content;
				$jsonObj->messages = parent::DisplayMessages();
				print json_encode($jsonObj);
			}
			
		}
		
		public static function DisplayStep1(&$Install){
			$Install->CheckRequirements();
			$smarty->assign('list',$Install->requirements);
			return $smarty->fetch('list.tpl');
		} 
		
	}
?>