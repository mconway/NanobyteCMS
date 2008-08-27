<?php
/*
 * Post page logic
 * Display a single post
 * Since 8/2008
 */
function Posts($args){
	global $smarty; // Get the smarty Global
	$post = PostController::GetPost($args[0]); //Get the single post to view
	$data = array( // Set the post data to display
				'title'=>$post->title, 
				'body'=>$post->body, 
				'created'=>date('M jS',$post->created),
				'author'=>$post->author,
			);
	$smarty->assign('post', $data); //Assign the data to Smarty
	BaseController::GetHTMLIncludes(); // Get style and JS
	$smarty->display('index.tpl'); //Display the page
}
