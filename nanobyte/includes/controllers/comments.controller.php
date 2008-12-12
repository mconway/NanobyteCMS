<?php
class CommentsController extends ContentController{
	public static function GetList($page=1){
		global $smarty;
		//create the list
		$start = BaseController::GetStart($page,15);
		$comments = new Comments();
		$list = $comments->ReadMe(15,$start); //array of objects with a limit of 15 per page.
		$options['image'] = '16';
		foreach ($list['content'] as $comment){
			$theList[] = array(
				'id'=>$comment['cid'],
				'title'=>$comment['title'], 
				'created'=>date('Y-M-D',$comment['date']),
				'author'=>$comment['author'],
				'actions'=>Core::l('view','posts/'.$comment['pid'],$options).' | '.Core::l('delete','admin/content/cdelete/'.$comment['pid'],$options)
				);
		}
		$options['image'] = '24';
		//create the actions options and bind the params to smarty
		$smarty->assign('pager',BaseController::Paginate($list['limit'], $list['nbItems'], 'admin/content/', $page));
		$smarty->assign('cb',true);
		$smarty->assign('self','admin/content');
		$smarty->assign('actions', array('delete' => 'Delete'));
		$smarty->assign('extra', 'With Selected: {html_options name=actions options=$actions}<input type="submit" name="submitaction" value="Go!"/>');
		$smarty->assign('list', $theList);
	}
	public static function CommentsForm($pid){
		global $smarty;
		//Create the form object
		$form = new HTML_QuickForm('commentform','commentform','posts/'.$pid.'/comments/add');
		//set form default values
		$form->setdefaults(array(
			'pid'=>$pid
		));
		//create form elements
		$form->addElement('header','','Post a new comment');
		$form->addElement('text', 'title', 'Title', array('size'=>80, 'maxlength'=>80));
		$form->addElement('textarea','body','Body',array('rows'=>20,'cols'=>60));
		
		$form->addElement('hidden','pid');
		$form->addElement('submit', 'save', 'Save');
		//apply form prefilters
		$form->applyFilter('__ALL__', 'trim');
		//$form->applyFilter('__ALL__', 'nl2br');

		//add form rules
		$form->addRule('title', 'A Title is required.', 'required');
		$form->addRule('body', 'Body text is required.', 'required');
		//If the form has already been submitted - validate the data
		if($form->validate()){
				$comments = new Comments();
				$form->process(array($comments,'Commit'));
				BaseController::Redirect();
				exit;
		}
		//send the form to smarty
		$smarty->assign('form', $form->toArray()); 
	}
}
?>
