<?php
class Mod_Content{
	
	private $dbh;
	
	public function __construct($id=null){
		$this->dbh = DBCreator::GetDbObject();
		$this->types = array();
		if($id){
			$result = $this->dbh->prepare("SELECT pid, title, body, created, author, published, modified FROM ".DB_PREFIX."_content WHERE pid=:id");
			try{
				$result->execute(array(':id'=>$id));
				$row = $result->fetch();
				list($this->pid,$this->title,$this->body,$this->created,$this->author,$this->published,$this->modified) = $row;
//				$this->comments = new ModComments($this->pid);
			}catch(PDOException $e){
				Core::SetMessage($e->getMessage(), 'error');
			}
		}
	}
	
	public function Create($params){
		//take params and write post to DB.
		$insert = $this->dbh->prepare("insert into ".DB_PREFIX."_content (title, body, created, author, published) values (:ti,:b,:c,:a,:p)");
		//$insert->bindParam(':ta', $params['tags']);
		try{
			$insert->execute(array(':ti'=>$params['title'],':b'=>$params['body'],':c'=>$params['created'],':a'=>$params['author'],':p'=>$params['published']));
		}catch(PDOException $e){
			Core::SetMessage($e->getMessage(), 'error');
		}
		if($insert->rowCount() == 1){
			return true;
		}else{
			return false;
		}
	}
	
	public function Read($type, $published=null,$limit=15,$start=0){
		if($published){
			$where = "WHERE `published`={$published} AND type={$type}";
		}else{
			$where = "WHERE type={$type}";
		}
		try{
			$this->items = array();
			$query = "SELECT SQL_CALC_FOUND_ROWS * FROM ".DB_PREFIX."_content {$where} ORDER BY created DESC LIMIT {$start},{$limit}";
			$this->items['content'] = $this->dbh->query($query)->fetchAll(PDO::FETCH_ASSOC);
			$cntRows = $this->dbh->query('SELECT found_rows() AS rows')->fetch(PDO::FETCH_OBJ);
			$this->items['final'] = $cntRows->rows >($start+$limit) ? $start+$limit : $cntRows->rows;
			$this->items['limit'] = $limit;
			$this->items['nbItems'] = $cntRows->rows;
			//print('<pre>'.print_r($this->items['content']).'</pre>');
		}catch (PDOException $e){
			Core::SetMessage($e->getMessage(), 'error');
		}
	}
	
	public function Commit(){
		$sql = $this->dbh->prepare("update ".DB_PREFIX."_content set `title`=:t, `body`=:b, `modified`=:m, `published`=:p where `pid`=:pid");
		$sql->execute(array(':p'=>$this->published,':t'=>$this->title,':b'=>$this->body,':m'=>$this->modified,':pid'=>$this->pid));
		if ($sql->rowCount() == 1){
			return true;
		}else{
			return false;
		}
	}
	
	public function GetTypes(){
		foreach($this->dbh->query("SELECT id,name FROM ".DB_PREFIX."_content_types") as $row){
			array_push($this->types,$row['name']);
		}
	}
	
	public function RegisterContentType($type){
		$insert = $this->dbh->prepare("INSERT INTO ".DB_PREFIX."_content_types SET name=:name");
		$insert->execute(array(':name'=>$type));
		if($insert->rowCount()==1){
			return true;	
		}else{
			return false;
		}
	}
	
	public function UnregisterContentType($type){
		$delete = $this->dbh->prepare("DELETE FROM ".DB_PREFIX."_content_types WHERE name=:name");
		$delete->execute(array(':name'=>$type));
		if($delete->rowCount()==1){
			return true;	
		}else{
			return false;
		}
	}
	
	public static function Install(){
		// register latest addtions block
		//Add Page Content type
		$content = new self();
		$content->RegisterContentType('Page');
		//register Content Menu Item
		$menu = new Menu('admin');
		$menu->data = array('linkpath'=>'admin/content');
		$menu->Commit();
	}
	
	public static function Uninstall(){
		$content = new self();
		$content->UnregisterContentType('Page');
	}
	
	public static function Admin(&$argsArray){
		ContentController::Admin($argsArray);
	}
	
	public static function Display(&$argsArray){
		ContentController::Display($argsArray);
	}

}

class ContentController extends BaseController{
	
	public static function View($pid){
		global $smarty;
		$post = self::GetContent($pid);
//		$num = count($post->comments->all);
//		$comments = array();
		$data = array( 
			'url'=>'content/'.$post->pid, 
			'title'=>$post->title, 
			'body'=>$post->body, 
			'created'=>date('M jS',$post->created),
			'author'=>$post->author,
//			'numcomments'=>$num != 1 ? $num.' comments' : $num.' comment'
		);
//		foreach($post->comments->all as $comment){
//			$smarty->assign('post',$comment);
//			array_push($comments,$smarty->fetch('post.tpl'));
//		}
////		CommentsController::CommentsForm($pid);
//		array_push($comments,$smarty->fetch('form.tpl'));
		$smarty->assign('post', $data);
//		$smarty->assign('comments', $comments);
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
			$content = new Mod_Content($data['pid']);
			$content->title = $data['title'];
			$content->body = $image.$data['body'];
			$content->modified = time();
			$content->published = $data['published'] ? '1' : '0';
			$content->type = $data['type'];
			$content->Commit();
			Core::SetMessage('Your changes have been saved!','info');
		
		}else{ //Create a new Post
			$content = new Mod_Content();
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
		$content = new Mod_Content();
		//create the list
		$start = BaseController::GetStart($page,10);
		$content->Read($type, null,10,$start); //array of objects with a limit of 15 per page.
		$list = array();
		$options['image'] = '16';
		$options['class'] = 'action-link-tab';
		foreach ($content->items['content'] as $post){
			$options['title'] = "Viewing-".$post['title'];
			$actions = Core::l('info','content/'.$post['pid'],$options).' | ';
			$options['title'] = "Edit-".$post['title'];
			$actions .=
			array_push($list, array(
				'id'=>$post['pid'], 
				'title'=>$post['title'], 
				'created'=>date('Y-M-D',$post['created']),
				'author'=>$post['author'],
				'published'=>$post['published'],
				'actions'=>Core::l('info','content/'.$post['pid'],$options).' | '.Core::l('edit','admin/content/edit/'.$post['pid'],$options)
				));
		}

		$options['image'] = '24';
		$options['class'] = 'action-link-tab';
		//create the actions options and bind the params to smarty
		$smartyVars = array(
			'pager'=>BaseController::Paginate($content->items['limit'], $content->items['nbItems'], 'admin/content/'.$type.'/', $page),
			'sublinks'=>array('header'=>'Actions: ','add'=>Core::l('add','admin/content/add',$options)),
			'cb'=>true,
			'formAction'=>'admin/content',
			'actions'=>array('delete' => 'Delete', 'publish'=>'Publish', 'unpublish'=>'Unpublish'),
			'extra'=>'With Selected: {html_options name=actions options=$actions}<input type="submit" name="submitaction" value="Go!"/>',
			'list'=>$list
		);
		$smarty->assign($smartyVars);
	}
	
	public static function Edit($id){
		$content = new Mod_Content($id);
		self::Form($content);
	}
	
	public static function Display(&$argsArray){
		list($args,$ajax,$smarty,$user,$jsonObj) = $argsArray;
		
		if(empty($args)){
			self::DisplayContent(0,1,$smarty);
		}elseif(!$args[1]){
			self::View($args[0]);
			$content = $smarty->fetch('post.tpl');
		}elseif($args[1]=='comments'){
			switch($args[2]){
				case 'add':
					self::CommentsForm($args[0]);
					$smarty->fetch('form.tpl');
					break;
				case 'view':
					break;
			}
		}
		if(!$ajax){
			parent::DisplayMessages();
			parent::GetHTMLIncludes(); // Get style and JS
			$smarty->display('index.tpl'); //Display the page
		}else{
			$jsonObj->content = $content;
			$jsonObj->messages = parent::DisplayMessages();
			print json_encode($jsonObj);
		}
	}
	
	public static function DisplayContent($type,$page,&$smarty){
		$theList = array();
		$content = new Mod_Content();
		$content->Read($type,'1',5);
		foreach ($content->items['content'] as $p){
			$post = new Mod_Content($p['pid']);
//			$num = count($post->comments->all);
			array_push($theList, array( 
				'url'=>'content/'.$post->pid, 
				'title'=>$post->title, 
				'body'=>$post->body, 
				'created'=>date('M jS',$post->created),
				'author'=>$post->author,
//				'numcomments'=>$num != 1 ? $num.' comments' : $num.' comment'
			));
		}
		//$smarty->assign('pager',BaseController::Paginate($posts['limit'], $posts['nbItems'], '', $page));
		$smarty->assign('posts', $theList);
	}
	
	public static function GetContent($id){
		$content = new Mod_Content($id);
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
			$content = new Mod_Content();
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
	
	public function Form_Settings_AddType(){
		$form = new HTML_QuickForm('content-settings','post','admin/content/settings');
		$form->setdefaults(array());
		
		$form->addElement('header','','Add Content Type');
		$form->addElement('text', 'name', 'Name', array('size'=>25, 'maxlength'=>15));
		$form->addElement('submit', 'submit', 'Submit');
		
		if($form->validate()){
			$content = new Mod_Content();
			$form->process(array($content,'AddType'));
			BaseController::Redirect('admin/content');
			exit;
		}
		return $form->toArray(); 
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

	public static function Admin(&$argsArray){
		list($args,$ajax,$smarty,$user,$jsonObj) = $argsArray;
		
		$contents = new Mod_Content();
		$contents->GetTypes();
		$tabs = array();
		foreach($contents->types as $key=>$tab){
			array_push($tabs, Core::l($tab,'admin/content/'.$key));
		}
		array_push($tabs,Core::l('Settings','admin/content/settings'));
		if(is_numeric($args[1])){
			self::GetList($args[1],$args[2]);
		}else{
			switch($args[1]){
				case 'comments':
					CommentsController::GetList($args[2]); // should be passing page #
					break;
				case 'delete':
					self::Delete();
					break;
				case 'add':
					$jsonObj->callback = 'Dialog';
					$jsonObj->title = 'Add Content';
					self::Form();
					$content = $smarty->fetch('form.tpl');
					break;
				case 'edit':
					if(!$args[2]){
						Core::SetMessage('You did not specify content to edit!','error');
						BaseController::Redirect('admin/posts');
					}else{
						$jsonObj->callback = 'Dialog';
						$jsonObj->title = 'Edit Content';
						self::Edit($args[2]);
						$content = $smarty->fetch('form.tpl');
					}
					break;	
				case 'settings':
					$content = '';
					if($args[2]=='addtype'){
						
						$smarty->assign('form',ContentController::Form_Settings_AddType());
						$content .= $smarty->fetch('form.tpl');
					}
					$options['id'] = 'addtype';
					$content .= Core::l('Add Content Type', 'admin/content/settings/addtype', $options);
					break;
			}
		}
		//If File is not set, get the post list and display
		$smarty->assign('tabs',$tabs);
		if($ajax){$jsonObj->tabs = $smarty->fetch('tabs.tpl');}
		if (!$content){ 
			$content =  $smarty->fetch('list.tpl');
		}
		$jsonObj->content = $content;
	}

}

?>