<?php

class PostController{
	public static function View($args){
		global $smarty;
		$post = PostController::GetPost($args[0]);
		$data = array( 
					'title'=>$post->title, 
					'body'=>$post->body, 
					'created'=>date('M jS',$post->created),
					'author'=>$post->author,
				);
		$smarty->assign('post', $data);
		$smarty->display('index.tpl'); //this needs to be changed!
	}
	public static function SavePost($data){ //accept assoc array of data from Quickform
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
			$post = new Post($data['pid']);
			$post->title = $data['title'];
			$post->body = $image.$data['body'];
			$post->modified = time();
			$post->published = $data['published'] ? '1' : '0';
			$post->Commit();
			Core::SetMessage('Your changes have been saved!','info');
		
		}else{ //Create a new Post
			$data['published'] ? 1 : 0;
			$data['body'] = $image.$data['body'];
			$saved = Post::CreatePost($data);
			if ($saved == true){
				Core::SetMessage('Your post has been successfully saved','info');
			}else{
				Core::SetMessage('Unable to save post. Please try again later.','error');
			}
		}
		//UserController::Redirect('admin/posts');
	}

	public static function ListPosts($page){
		global $smarty;
		//create the list
		$start = BaseController::GetStart($page,15);
		$list = Post::Read(null,15,$start); //array of objects with a limit of 15 per page.
		$theList = array();
		$options['image'] = '16';
		foreach ($list['content'] as $post){
			$theList[] = array(
				'id'=>$post->pid, 
				'title'=>$post->title, 
				'created'=>date('Y-M-D',$post->created),
				'author'=>$post->author,
				'published'=>$post->published,
				'actions'=>Core::l('info','posts/'.$post->pid,$options).' | '.Core::l('edit','admin/posts/edit/'.$post->pid,$options)
				);
		}
		$options['image'] = '24';
		//create the actions options and bind the params to smarty
		$smarty->assign('pager',BaseController::Paginate($list['limit'], $list['nbItems'], 'admin/posts/', $page));
		$smarty->assign('sublinks',array('header'=>'Actions: ','add'=>Core::l('add','admin/posts/add',$options)));
		$smarty->assign('cb',true);
		$smarty->assign('self','admin/posts');
		$smarty->assign('actions', array('delete' => 'Delete', 'publish'=>'Publish', 'unpublish'=>'Unpublish'));
		$smarty->assign('extra', 'With Selected: {html_options name=actions options=$actions}<input type="submit" name="submitaction" value="Go!"/>');
		$smarty->assign('list', $theList);
		return $smarty;
	}
	
	public static function EditPost($id){
		$post = new Post($id);
		self::PostForm($post);
	}
	public static function DisplayPosts($page){
		global $smarty;
		$posts = Post::Read('1');
		foreach ($posts['content'] as $post){
			$theList[] = array( 
				'title'=>$post->title, 
				'body'=>$post->body, 
				'created'=>date('M jS',$post->created),
				'author'=>$post->author,
			);
		}
		$smarty->assign('pager',BaseController::Paginate($posts['limit'], $posts['nbItems'], '', $page));
		$smarty->assign('posts', $theList);
	}
	public static function GetPost($id){
		$post = new Post($id);
		return $post;
	}
	public static function PostForm($post=null){
		global $smarty;
		$func = $post ? 'edit/'.$post->pid : 'add';
		$header = 'Create a new Post';
		$tablinks = array('Main','Image Functions','Publishing Options');
		//Create the form object
		$form = new HTML_QuickForm('edituser','post','admin/posts/'.$func);
		//set form default values

		if($post){
			$form->setdefaults(array(
				'pid'=>$post->pid, 
				'title'=>$post->title, 
				'body'=> preg_replace('/<br \/>/','',$post->body),
				'published'=>$post->published == 1 ? true : false
			));
			$header = 'Edit Post';
		}else{
			$form->setdefaults(array(
				'published'=>true
			));
		}
		//create form elements
		$form->addElement('header','',$header);
		$form->addElement('text', 'title', 'Title', array('size'=>80, 'maxlength'=>80));
		$form->addElement('textarea','body','Body',array('rows'=>20,'cols'=>60));
		
		$form->addElement('header','','Image Functions');
		$form->addElement('file','image','Add Image');
		$form->addElement('text', 'ititle', 'Title', array('size'=>25, 'maxlength'=>15));
		$form->addElement('text', 'ialt', 'Alt Text', array('size'=>25, 'maxlength'=>15));
		
		$form->addElement('header','','Publishing Options');
		$form->addElement('text', 'tags', 'Tags', array('size'=>25, 'maxlength'=>15));
		$form->addElement('checkbox','published','Publish');
		
		$form->addElement('hidden','pid');
		$form->addElement('hidden','author', unserialize($_SESSION['user'])->name);
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
				$form->process(array('PostController','SavePost'));
				BaseController::Redirect('admin/posts');
				exit;
		}
		//send the form to smarty
		$smarty->assign('form', $form->toArray()); 
		$smarty->assign('tabbed',$tablinks);
	}
	public static function DeletePostRequest(){
 		if(isset($_POST['posts'])){
 			$delPost = $_POST['posts'];
 		}
 		if(isset($delPost)){
	 		foreach($delPost as $delete){
 				$deleted = Admin::DeleteObject('posts', 'pid', $delete);
					if ($deleted === true){
					Core::SetMessage('Post '.$delete.' has been deleted!', 'info');
				} else {
					Core::SetMessage('Unable to delete post '.$delete.' , an error has occurred.', 'error');
				}
 			}	
 		}else{
 			Core::SetMessage('You must choose a post(s) to delete!', 'error');
 		}
	}

}
?>
