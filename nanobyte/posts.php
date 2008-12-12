<?php
/*
 * Post page logic
 * Display a single post
 * Since 8/2008
 */
function Posts($args){
	global $smarty; // Get the smarty Global
	if(!$args[1]){
		ContentController::View($args[0]);
	}elseif($args[1]=='comments'){
			switch($args[2]){
				case 'add':
					CommentsController::CommentsForm($args[0]);
					$smarty->assign('file','form.tpl');
					break;
				case 'view':
					break;
			}
	}
	BaseController::GetHTMLIncludes(); // Get style and JS
	$smarty->display('index.tpl'); //Display the page
}
