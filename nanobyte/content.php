<?php
/*
 * Post page logic
 * Display a single post
 * Since 8/2008
 */
function Content($args){
	Core::SetMessage('Called Content');
	global $smarty; // Get the smarty Global
	global $ajax;
	if(!$args[1]){
		ContentController::View($args[0]);
		$content = $smarty->fetch('post.tpl');
	}elseif($args[1]=='comments'){
			switch($args[2]){
				case 'add':
					CommentsController::CommentsForm($args[0]);
					$smarty->fetch('form.tpl');
					break;
				case 'view':
					break;
			}
	}
	if(!$ajax){
		BaseController::DisplayMessages();
		BaseController::GetHTMLIncludes(); // Get style and JS
		$smarty->display('index.tpl'); //Display the page
	}else{
		$jsonArray['content'] = $content;
		$jsonArray['messages'] = BaseController::DisplayMessages();
		print json_encode($jsonArray);
	}
}
?>
