<?php

class ContentController{
	public static function View($pid){
		global $smarty;
		$post = self::GetContent($pid);
		$num = count($post->comments->all);
		$data = array( 
			'url'=>'content/'.$post->pid, 
			'title'=>$post->title, 
			'body'=>$post->body, 
			'created'=>date('M jS',$post->created),
			'author'=>$post->author,
			'numcomments'=>$num != 1 ? $num.' comments' : $num.' comment'
		);
		foreach($post->comments->all as $comment){
			$smarty->assign('post',$comment);
			array_push($comments,$smarty->fetch('post.tpl'));
		}
		CommentsController::CommentsForm($pid);
		array_push($comments,$smarty->fetch('form.tpl'));
		$smarty->assign('post', $data);
		$smarty->assign('comments', $comments);
	}
	
	public static function Save($data){ 
		//fields: Title | Body | Created | Modified | Author | Published | Tags
		//upload files if needed
		if(!empty($data['image']['name'])){
			$image = BaseController::HandleImage($data['image'],'80');
		}
		$codestr = substr($data['body'],strpos($data['body'],'<code>'),strpos($data['body'],'</code>'));
		$codestr = str_replace('<code>','',$codestr);
		$codestr = str_replace('</code>','',$codestr);
		$geshi = new GeSHi($codestr, 'php');
		$code = $geshi->parse_code();
		$pattern = '#(<)(code)((\s+[^>]*)*)(>)(.*?)(</\2\s*>|$)#s';

		$data['body'] = preg_replace($pattern, $code, $data['body']);
	
		//Update the Post, Do not create a new one.
		if ($data['pid']){
			$content = new Content($data['pid']);
			$content->title = $data['title'];
			$content->body = $image.$data['body'];
			$content->modified = time();
			$content->published = $data['published'] ? '1' : '0';
			$content->type = $data['type'];
			$content->Commit();
			Core::SetMessage('Your changes have been saved!','info');
		
		}else{ //Create a new Post
			$content = new Content();
			$data['published'] ? 1 : 0;
			$data['body'] = $image.$data['body'];
			$saved = $content->Create($data);
			if ($saved == true){
				Core::SetMessage('Your content has been successfully saved','info');
			}else{
				Core::SetMessage('Unable to save content. Please try again later.','error');
			}
		}
		//UserController::Redirect('admin/posts');
	}

	public static function GetList($type, $page=1){
		global $smarty;
		$content = new Content();
		//create the list
		$start = BaseController::GetStart($page,15);
		$content->Read($type, null,15,$start); //array of objects with a limit of 15 per page.
		$theList = array();
		$options['image'] = '16';
		foreach ($content->items['content'] as $post){
			array_push($theList, array(
				'id'=>$post['pid'], 
				'title'=>$post['title'], 
				'created'=>date('Y-M-D',$post['created']),
				'author'=>$post['author'],
				'published'=>$post['published'],
				'actions'=>Core::l('info','Content/'.$post['pid'],$options).' | '.Core::l('edit','admin/content/edit/'.$post['pid'],$options)
				));
		}

		$options['image'] = '24';
		$options['class'] = 'action-link';
		//create the actions options and bind the params to smarty
		$smarty->assign('pager',BaseController::Paginate($content->items['limit'], $content->items['nbItems'], 'admin/content/', $page));
		$smarty->assign('sublinks',array('header'=>'Actions: ','add'=>Core::l('add','admin/content/add',$options)));
		$smarty->assign('cb',true);
		$smarty->assign('self','admin/content');
		$smarty->assign('actions', array('delete' => 'Delete', 'publish'=>'Publish', 'unpublish'=>'Unpublish'));
		$smarty->assign('extra', 'With Selected: {html_options name=actions options=$actions}<input type="submit" name="submitaction" value="Go!"/>');
		$smarty->assign('list', $theList);
	}
	
	public static function Edit($id){
		$content = new Content($id);
		self::Form($content);
	}
	
	public static function Display($type,$page){
		global $smarty;
		$theList = array();
		$content = new Content();
		$content->Read($type,'1');
		foreach ($content->items['content'] as $p){
			$post = new Content($p['pid']);
			$num = count($post->comments->all);
			array_push($theList, array( 
				'url'=>'posts/'.$post->pid, 
				'title'=>$post->title, 
				'body'=>$post->body, 
				'created'=>date('M jS',$post->created),
				'author'=>$post->author,
				'numcomments'=>$num != 1 ? $num.' comments' : $num.' comment'
			));
		}
		//$smarty->assign('pager',BaseController::Paginate($posts['limit'], $posts['nbItems'], '', $page));
		$smarty->assign('posts', $theList);
	}
	
	public static function GetContent($id){
		$content = new Content($id);
		return $content;
	}
	
	public static function Form($content=null){
		global $smarty;
		global $user;
		$func = $content ? 'edit/'.$content->pid : 'add';
		$tablinks = array('Main','Image Functions','Publishing Options');
		//Create the form object
		$form = new HTML_QuickForm('edituser','post','admin/content/'.$func);
		//set form default values

		if($content){
			$form->setdefaults(array(
				'pid'=>$content->pid, 
				'title'=>$content->title, 
				'body'=> preg_replace('/<br \/>/','',$content->body),
				'published'=>$content->published == 1 ? true : false
			));
			$header = 'Edit Content';
		}else{
			$form->setdefaults(array(
				'published'=>true
			));
			$content = new Content();
			$content->GetTypes();
		}
		//create form elements
		$form->addElement('header','','Create Content');
		$form->addElement('text', 'title', 'Title', array('size'=>80, 'maxlength'=>80));
		$form->addElement('textarea','body','Body',array('rows'=>20,'cols'=>60));
		
		$form->addElement('header','','Image Functions');
		$form->addElement('file','image','Add Image');
		$form->addElement('text', 'ititle', 'Title', array('size'=>25, 'maxlength'=>15));
		$form->addElement('text', 'ialt', 'Alt Text', array('size'=>25, 'maxlength'=>15));
		
		$form->addElement('header','','Publishing Options');
		$form->addElement('text', 'tags', 'Tags', array('size'=>25, 'maxlength'=>15));
		$form->addElement('select', 'type', 'Content Type', $content->types);
		$form->addElement('checkbox','published','Publish');
		
		$form->addElement('hidden','pid');
		$form->addElement('hidden','author', $user->name);
		$form->addElement('hidden','created',time());
				
		$form->addElement('submit', 'save', 'Save');
		//apply form prefilters
		$form->applyFilter('__ALL__', 'trim');
		//$form->applyFilter('__ALL__', 'nl2br');

		//add form rules
		$form->addRule('title', 'A Title is required.', 'required');
		$form->addRule('body', 'Body text is required.', 'required');
		//If the form has already been submitted - validate the data
		if($form->validate()){
				$form->process(array('ContentController','Save'));
				BaseController::Redirect('admin/content');
				exit;
		}
		//send the form to smarty
		$smarty->assign('form', $form->toArray()); 
		$smarty->assign('tabbed',$tablinks);
	}
	
	public static function Delete(){
 		if(isset($_POST['posts'])){
 			$del = $_POST['posts'];
 		}
 		if(isset($del)){
	 		foreach($del as $delete){
 				$deleted = Admin::DeleteObject('content', 'pid', $delete);
					if ($deleted === true){
					Core::SetMessage('Content '.$delete.' has been deleted!', 'info');
				} else {
					Core::SetMessage('Unable to delete content '.$delete.' , an error has occurred.', 'error');
				}
 			}	
 		}else{
 			Core::SetMessage('You must choose at least 1 item to delete!', 'error');
 		}
	}

}
?>
