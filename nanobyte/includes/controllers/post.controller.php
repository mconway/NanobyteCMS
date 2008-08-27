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
			$filename = $data['image']['name'];
			$error = BaseController::VerifyFile($data['image']);
			if ($error == false){
				if (Core::FileUpload($data['image']) == true){
					Core::SetMessage('Your file upload was successful, view the file <a href="' . UPLOAD_PATH . $filename . '" title="Your File">here</a>','info');
				}else{
					Core::SetMessage('There was an error during the file upload.  Please try again.','error');
				}
			}else{
				Core::SetMessage($error, 'error');
			}
			$image = '<img src="'.UPLOAD_PATH.$filename.'" width="80" height="80"/>';
		}
		//Update the Post, Do not create a new one.
		if ($data['pid']){
			$post = new Post($data['pid']);
			$post->title = $data['title'];
			$post->body = $image.$data['body'];
			$post->modified = time();
			$post->published = $data['published'] ? 1 : 0;
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

	public static function ListPosts($smarty){
		//create the list
		$list = Post::Read(); //array of objects
		$theList = array();
		$options['image'] = true;
		foreach ($list as $post){
			$theList[] = array(
				'id'=>$post->pid, 
				'title'=>$post->title, 
				//'body'=>$post->body, 
				'created'=>date('Y-M-D',$post->created),
				'author'=>$post->author,
				'published'=>$post->published,
				'actions'=>Core::l('info','posts/'.$post->pid,$options).' | '.Core::l('edit','admin/posts/edit/'.$post->pid,$options)
				);
		}
		//create the actions options
		//$actions['General Actions'] = array('newPost'=>'Create New Post');
		$actions= array('' => '--With Selected--', 'delete' => 'Delete', 'publish'=>'Publish', 'unpublish'=>'Unpublish');
		$extra = '{html_options name=actions options=$actions}<input type="submit" name="submitaction" value="Go!"/>';
		// bind the params to smarty
		$smarty->assign('self','admin/posts');
		$smarty->assign('actions',$actions);
		$smarty->assign('extra', $extra);
		$smarty->assign('list', $theList);
		return $smarty;
	}
	
	public static function EditPost($id){
		$post = new Post($id);
		self::PostForm($post);
	}
	public static function DisplayPosts($smarty){
		$posts = Post::Read('1');
		foreach ($posts as $post){
			$theList[] = array( 
				'title'=>$post->title, 
				'body'=>$post->body, 
				'created'=>date('M jS',$post->created),
				'author'=>$post->author,
			);
		}
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
		}
		//create form elements
		$form->addElement('header','',$header);
		$form->addElement('text', 'title', 'Title', array('size'=>80, 'maxlength'=>80));
		$form->addElement('textarea','body','Body',array('rows'=>20,'cols'=>60));
		
		$form->addElement('header','','Image Functions');
		$form->addElement('file','image','Add Image');
		$form->addElement('text', 'ititle', 'Title', array('size'=>25, 'maxlength'=>15));
		$form->addElement('text', 'ialt', 'Alt Text', array('size'=>25, 'maxlength'=>15));
		
		$form->addElement('header','','');
		$form->addElement('text', 'tags', 'Tags', array('size'=>25, 'maxlength'=>15));
		$form->addElement('checkbox','published','Publish');
		
		$form->addElement('hidden','pid');
		$form->addElement('hidden','author', unserialize($_SESSION['user'])->name);
		$form->addElement('hidden','created',time());
		
		$form->addElement('submit', 'save', 'Save');
		//apply form prefilters
		$form->applyFilter('__ALL__', 'trim');
		$form->applyFilter('__ALL__', 'nl2br');

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
	}
	public static function DeletePostRequest(){
		if(isset($_SESSION['hash'])){
		$user = unserialize($_SESSION['user']);
		if ($_SESSION['hash'] == $user->sessionHash($user->name)){
		 	if (Core::AuthUser($user, 'admin')){
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
		 		}else
		 			Core::SetMessage('You must choose a post(s) to delete!', 'error');
		 		}
			}
		}
	}
}
?>
