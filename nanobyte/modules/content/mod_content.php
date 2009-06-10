<?php
/**
 * 
 */

class Mod_Content{
	
	/**
	 * @var
	 */
	private $dbh;
	
	/**
	 * 
	 * @return 
	 * @param object $id[optional]
	 */
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
	
	/**
	 * 
	 * @return 
	 * @param object $argsArray
	 */
	public static function admin(&$argsArray){
		ContentController::Admin($argsArray);
	}
	
	/**
	 * 
	 * @return 
	 */
	public function commit(){
		$sql = $this->dbh->prepare("update ".DB_PREFIX."_content set `title`=:t, `body`=:b, `modified`=:m, `published`=:p where `pid`=:pid");
		$sql->execute(array(':p'=>$this->published,':t'=>$this->title,':b'=>$this->body,':m'=>$this->modified,':pid'=>$this->pid));
		if ($sql->rowCount() == 1){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * 
	 * @return 
	 * @param object $params
	 */
	public function create($params){
		//take params and write post to DB.
		$insert = $this->dbh->prepare("insert into ".DB_PREFIX."_content (title, body, created, author, published, type) values (:ti,:b,:c,:a,:p,:t)");
		//$insert->bindParam(':ta', $params['tags']);
		try{
			$insert->execute(array(':ti'=>$params['title'],':b'=>$params['body'],':c'=>$params['created'],':a'=>$params['author'],':p'=>isset($params['published']) ? $params['published'] : '0', ':t'=>$params['type']));
		}catch(PDOException $e){
			Core::SetMessage($e->getMessage(), 'error');
		}
		if($insert->rowCount() == 1){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * 
	 * @return 
	 * @param object $argsArray
	 */
	public static function display(&$argsArray){
		ContentController::Display($argsArray);
	}
	
	/**
	 * 
	 * @return 
	 */
	public function getTypes(){
		foreach($this->dbh->query("SELECT id,name FROM ".DB_PREFIX."_content_types") as $row){
			array_push($this->types,$row['name']);
		}
	}
	
	/**
	 * 
	 * @return 
	 */
	public static function install(){
		// register latest addtions block
		//Add Page Content type
		$content = new self();
		$content->RegisterContentType('Page');
		//register Content Menu Item
		$menu = new Menu('admin');
		$menu->data = array('linkpath'=>'admin/content');
		$menu->Commit();
	}
	
	/**
	 * 
	 * @return 
	 * @param object $type
	 * @param object $published[optional]
	 * @param object $limit[optional]
	 * @param object $start[optional]
	 */
	public function read($type, $published=null,$limit=15,$start=0){
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
	
	/**
	 * 
	 * @return 
	 * @param object $type
	 */
	public function registerContentType($type){
		$insert = $this->dbh->prepare("INSERT INTO ".DB_PREFIX."_content_types SET name=:name");
		$insert->execute(array(':name'=>$type));
		if($insert->rowCount()==1){
			return true;	
		}else{
			return false;
		}
	}
	
	/**
	 * 
	 * @return 
	 */
	public function toggleStatus(){
		$query = $this->dbh->prepare("UPDATE ".DB_PREFIX."_content set published=published XOR 1 WHERE `pid`=:id");
		$query->execute(array(':id'=>$this->pid));
		if($query->rowCount()==1){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * 
	 * @return 
	 */
	public static function uninstall(){
		$content = new self();
		$content->UnregisterContentType('Page');
	}
	
	/**
	 * 
	 * @return 
	 * @param object $type
	 */
	public function unregisterContentType($type){
		$delete = $this->dbh->prepare("DELETE FROM ".DB_PREFIX."_content_types WHERE name=:name");
		$delete->execute(array(':name'=>$type));
		if($delete->rowCount()==1){
			return true;	
		}else{
			return false;
		}
	}
	
}

/**
 * 
 */
class ContentController extends BaseController{
	
	/**
	 * 
	 * @return 
	 * @param object $argsArray
	 */
	public static function admin(&$argsArray){
		list($args,$ajax,$smarty,$user,$jsonObj,$core) = $argsArray;
		
		$contents = new Mod_Content();
		$contents->GetTypes();
		$tabs = array();
		foreach($contents->types as $key=>$tab){
			array_push($tabs, Core::l($tab,'admin/content/'.$key));
		}
		array_push($tabs,Core::l('Settings','admin/content/settings'));
		if(isset($args[1])){
			if(is_numeric($args[1])){
				$smarty->assign(self::GetList($args[1],$args[2]));
			}else{
				switch($args[1]){
					case 'delete':
						self::Delete();
						$jsonObj->callback = 'nanobyte.deleteRows';
						$jsonObj->args = implode('|',$_POST['content']);
						break;
					case 'add':
						$smarty->assign(self::Form());
						$content = $smarty->fetch('form.tpl');
						if(isset($_POST['submit'])){
							$jsonObj->callback = 'nanobyte.closeParentTab';
							$jsonObj->args = 'input[name=submit][value=Save]';
						}
						break;
					case 'edit':
						if(!isset($args[2])){
							Core::SetMessage('You did not specify content to edit!','error');
//							BaseController::Redirect('admin/posts');
						}else{
							if(isset($_POST['submit'])){
								$jsonObj->callback = 'nanobyte.closeParentTab';
								$jsonObj->args = 'input[name=submit][value=Save]';
							}
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
					case 'enable':
					case 'disable':
						$content = new Mod_Content($args[2]);
						$jsonObj->callback = 'nanobyte.changeLink';
						$jsonObj->args = $args[1]."|".($args[1]=='disable'?'enable':'disable')."|c_".$content->pid."|published";
						if($content->toggleStatus()){
							
						}
						break;
				}
			}
		}
		//If File is not set, get the post list and display
		$smarty->assign('tabs',$tabs);
		if($ajax){$jsonObj->tabs = $smarty->fetch('tabs.tpl');}
		if (!isset($content)){ 
			$content =  $smarty->fetch('list.tpl');
		}
		$jsonObj->content = $content;
	}
	
	/**
	 * 
	 * @return 
	 */
	public static function delete(){
 		if(isset($_POST['content'])){
	 		foreach($_POST['content'] as $delete){
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
	
	/**
	 * 
	 * @return 
	 * @param object $argsArray
	 */
	public static function display(&$argsArray){
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
	
	/**
	 * 
	 * @return 
	 * @param object $type
	 * @param object $page
	 * @param object $smarty
	 */
	public static function displayContent($type,$page,&$smarty){
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
	
	/**
	 * 
	 * @return 
	 * @param object $id
	 */
	public static function edit($id){
		$content = new Mod_Content($id);
		self::Form($content);
	}
	
	/**
	 * 
	 * @return 
	 * @param object $content[optional]
	 */
	public static function form(&$content=null){
		global $smarty;
		global $user;
		$func = $content ? 'edit/'.$content->pid : 'add';
		$tablinks = array('Main','Image Functions','Publishing Options');
		//Create the form object
		$form = new HTML_QuickForm('edituser','post','admin/content/'.$func);
		//set form default values

		if(isset($content)){
			$form->setdefaults(array(
				'pid'=>$content->pid, 
				'title'=>$content->title, 
				'body'=> preg_replace('/<br \/>/','',$content->body),
				'published'=>$content->published
			));
			$header = 'Edit Content';
		}else{
			$form->setdefaults(array(
				'published'=>true
			));
			$content = new Mod_Content();
		}
		$content->GetTypes();
		//create form elements
		$form->addElement('header','','Create Content');
		$form->addElement('text', 'title', 'Title', array('size'=>62, 'maxlength'=>80));
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
				
		$form->addElement('submit', 'submit', 'Save');
		//apply form prefilters
		$form->applyFilter('__ALL__', 'trim');
		//$form->applyFilter('__ALL__', 'nl2br');

		//add form rules
		$form->addRule('title', 'A Title is required.', 'required');
		$form->addRule('body', 'Body text is required.', 'required');
		//If the form has already been submitted - validate the data
		if(isset($_POST['submit']) && $form->validate()){
				$form->process(array('ContentController','Save'));
//				BaseController::Redirect('admin/content');
//				exit;
		}
		//send the form to smarty
		$smarty->assign('form', $form->toArray()); 
		$smarty->assign('tabbed',$tablinks);
	}
	
	/**
	 * 
	 * @return 
	 * @param object $type
	 * @param object $page[optional]
	 */
	public static function getList($type, $page=1){
		$content = new Mod_Content();
		//create the list
		$start = BaseController::GetStart($page,10);
		$content->Read($type, null,10,$start); //array of objects with a limit of 15 per page.
		$list = array();
		$options['image'] = '16';
		foreach ($content->items['content'] as $post){
			$options['class'] = 'action-link';
			$options['id'] = 'c_'.$post['pid'];
			$func = ($post['published']==1?'disable':'enable');
			$options['title'] = ucfirst($func)." this post";
			$actions = Core::l(ucfirst($func),"admin/content/{$func}/".$post['pid'],$options).' | ';
			
			$options['class'] = 'action-link-tab';
			$options['id'] = '';
			$options['title'] = "Viewing-".$post['title'];
			$actions .= Core::l('info','content/'.$post['pid'],$options).' | ';
			
			$options['title'] = "Edit-".$post['title'];
			$actions .= Core::l('edit','admin/content/edit/'.$post['pid'],$options);
			
			array_push($list, array(
				'id'=>$post['pid'], 
				'title'=>$post['title'], 
				'created'=>date('m-d-Y',$post['created']),
				'modified'=>date('m-d-Y',$post['modified']),
				'author'=>ucfirst($post['author']),
				'published'=>"<center><img src='".THEME_PATH."/images/{$post['published']}-25.png'/></center>",
				'actions'=>$actions
				));
		}
		$options = array('image'=>'24', 'class'=>'action-link-tab', 'title' => 'Add Content');
		//create the actions options and bind the params to smarty
		return array(
			'pager'=>BaseController::Paginate($content->items['limit'], $content->items['nbItems'], 'admin/content/'.$type.'/', $page),
			'sublinks'=>array('header'=>'Actions: ','add'=>Core::l('add','admin/content/add',$options)),
			'cb'=>true,
			'formAction'=>'admin/content',
			'actions'=>array('delete' => 'Delete'),
			'extra'=>'With Selected: {html_options name=actions options=$actions}<input type="submit" name="submit" value="Go!"/>',
			'list'=>$list
		);
	}
	
	/**
	 * 
	 * @return 
	 * @param object $id
	 */
	public static function getContent($id){
		$content = new Mod_Content($id);
		return $content;
	}
	
	/**
	 * 
	 * @return 
	 */
	public static function form_Settings_AddType(){
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
	
	/**
	 * 
	 * @return 
	 * @param object $data
	 */
	public static function save($data){ 
		//fields: Title | Body | Created | Modified | Author | Published | Tags
		//upload files if needed
		$image = '';
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
			isset($data['published']) ? 1 : 0;
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
	
	/**
	 * 
	 * @return 
	 * @param object $pid
	 */
	public static function view($pid){
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

}

?>