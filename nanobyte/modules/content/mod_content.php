<?php
	/*
	*Copyright (c) 2009, Michael Conway
	*All rights reserved.
	*Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
    *Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
   	*Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
	*Neither the name of the Nanobyte CMS nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
	*THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
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
		$Core = BaseController::getCore();
		if($id){
			$result = $this->dbh->prepare("SELECT pid, title, body, images, created, author, published, modified,type FROM ".DB_PREFIX."_content WHERE pid=:id");
			try{
				$result->execute(array(':id'=>$id));
				$row = $result->fetch();
				list($this->pid,$this->title,$this->body,$this->images,$this->created,$this->author,$this->published,$this->modified,$this->type) = $row;
				if($Core->isEnabled('Comments')){
					$this->comments = new Mod_Comments($this->pid);
					$Core = BaseController::getCore();
				}
			}catch(PDOException $e){
				$Core = BaseController::getCore();
				$Core->SetMessage($e->getMessage(), 'error');
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
		$sql = $this->dbh->prepare("update ".DB_PREFIX."_content set title=:t, body=:b, images=:i, modified=:m, published=:p, type=:type where `pid`=:pid");
		$sql->execute(array(':p'=>$this->published,':t'=>$this->title,':b'=>$this->body,':i'=>$this->images,':m'=>$this->modified,':pid'=>$this->pid,':type'=>$this->type));
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
		$insert = $this->dbh->prepare("insert into ".DB_PREFIX."_content (title, body, images, created, author, published, type) values (:ti,:b,:i,:c,:a,:p,:t)");
		//$insert->bindParam(':ta', $params['tags']);
		try{
			$insert->execute(array(':ti'=>$params['title'],':b'=>$params['body'],':i'=>isset($params['imagelist'])?$params['imagelist']:'',':c'=>$params['created'],':a'=>$params['author'],':p'=>isset($params['published']) ? $params['published'] : '0', ':t'=>$params['type']));
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
	public static function display(&$arg_array){
		ContentController::Display($arg_array);
	}
	
	/**
	 * 
	 * @return 
	 */
	public function getDefaultType(){
		$row = $this->dbh->query("SELECT setting, value FROM ".DB_PREFIX."_settings WHERE setting='defaultContentType'")->fetch();
		return $row['value'];
	}
	
	/**
	 * 
	 * @return 
	 */
	public function getTypes(){
		foreach($this->dbh->query("SELECT id,name FROM ".DB_PREFIX."_content_types") as $row){
			if($row['name'] == 'Orphaned'){
				$tmp[] = $row['id'];
				$tmp[] = $row['name'];
				continue;
			}
			$this->types[$row['id']] = $row['name'];
		}
		asort($this->types);
		$this->types[$tmp[0]] = $tmp[1];
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
		$delete = $this->dbh->prepare("UPDATE ".DB_PREFIX."_content SET type=0 WHERE type=(SELECT id FROM ".DB_PREFIX."_content_types WHERE name=?); DELETE FROM ".DB_PREFIX."_content_types WHERE name=?");
		$delete->execute(array($type,$type));
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
	public static function admin(){
		$Core = parent::getCore();
		
		$contents = new Mod_Content();
		$contents->GetTypes();
		$tabs = array();
		foreach($contents->types as $key=>$tab){
			if(!empty($key)){
				array_push($tabs, $Core->l($tab,'admin/content/'.$key));
			}
		}
		if(isset($Core->args[1])){
			if(is_numeric($Core->args[1])){
				$Core->smarty->assign(self::getList($Core->args[1],$Core->args[2]));
			}else{
				switch($Core->args[1]){
					case 'delete':
						self::Delete();
						$Core->json_obj->callback = 'nanobyte.deleteRows';
						$Core->json_obj->args = implode('|',$_POST['content']);
						break;
					case 'add':
						if(isset($Core->args[2]) && $Core->args[2]=='image'){
							$Core->json_obj->args = parent::handleImage($_FILES[key($_FILES)],'80');
							$content = '';
							print json_encode($Core->json_obj);
							exit;
						}elseif(isset($_POST['submit'])){
							if(self::form()==true){
								$Core->json_obj->callback = 'nanobyte.closeParentTab';
								$Core->json_obj->args = 'input[name=submit][value=Save]';
							}
						}else{
							$Core->smarty->assign(self::form());
							$content = $Core->smarty->fetch('form.tpl');
						}
						break;
					case 'edit':
						if(!isset($Core->args[2])){
							Core::SetMessage('You did not specify content to edit!','error');
//							BaseController::Redirect('admin/posts');
						}elseif(isset($Core->args[3]) && $Core->args[3]=='image'){
							$Core->json_obj->args = parent::handleImage($_FILES[key($_FILES)],'80');
							$content = '';
							print json_encode($Core->json_obj); exit;
						}else{
							if(isset($_POST['submit'])){
								$Core->json_obj->callback = 'nanobyte.closeParentTab';
								$Core->json_obj->args = 'input[name=submit][value=Save]';
							}
							self::Edit($Core->args[2]);
							$content = $Core->smarty->fetch('form.tpl');
						}
						break;	
					case 'addtype':
						$content = '';
						if($Core->args[2]=='addtype'){
							$Core->smarty->assign('form',ContentController::Form_Settings_AddType());
							$content .= $Core->smarty->fetch('form.tpl');
						}
						$options['id'] = 'addtype';
						$content .= $Core->l('Add Content Type', 'admin/content/settings/addtype', $options);
						break;
					case 'settings':
						$Core->smarty->assign('form',self::formSettings());
						$content = $Core->smarty->fetch('form.tpl');
						break;
					case 'enable':
					case 'disable':
						$content = new Mod_Content($Core->args[2]);
						$Core->json_obj->callback = 'nanobyte.changeLink';
						$Core->json_obj->args = $Core->args[1]."|".($Core->args[1]=='disable'?'enable':'disable')."|c_".$content->pid."|published";
						if($content->toggleStatus()){
							
						}
						break;
				}
			}
		}
		//If File is not set, get the post list and display
		$Core->smarty->assign('tabs',$tabs);
		if($Core->ajax){$Core->json_obj->tabs = $Core->smarty->fetch('tabs.tpl');}
		if (!isset($content)){ 
			$content =  $Core->smarty->fetch('list.tpl');
		}
		
		$Core->json_obj->content = $content;
		
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
	public static function display(){
		$Core = parent::getCore();
		if(empty($Core->args)){
			$Core->smarty->assign('posts',self::displayContent(1));
		}elseif(!isset($Core->args[1]) || empty($Core->args[1])){
			self::View($Core->args[0]);
			$content = $Core->smarty->fetch('post.tpl');
		}elseif($Core->args[1]=='comments'){
			switch($Core->args[2]){
				case 'add':
					CommentsController::CommentsForm($Core->args[0]);
					$Core->smarty->fetch('form.tpl');
					break;
				case 'view':
					break;
			}
		}
		if(!$Core->ajax){
			parent::DisplayMessages();
			parent::GetHTMLIncludes(); // Get style and JS
			$Core->smarty->display('index.tpl'); //Display the page
		}else{
			$Core->json_obj->content = $content;
			$Core->json_obj->messages = parent::DisplayMessages();
			print json_encode($Core->json_obj);
		}
	}
	
	/**
	 * 
	 * @return 
	 * @param object $type
	 * @param object $page
	 * @param object $smarty
	 */
	public static function displayContent($page){
		$theList = array();
		$content = new Mod_Content();
		$content->Read($content->getDefaultType(),'1',5);
		if(!empty($content->items['content'])){
			foreach ($content->items['content'] as $p){
				$post = new Mod_Content($p['pid']);
				if(isset($post->comments)){
					$num = count($post->comments->all);
				}
				if(!empty($post->images)){
					$images = explode(';',$post->images);
					array_walk($images,array('BaseController','split'),'|');
				}
				$tmp_user = new User($post->author);
				array_push($theList, array( 
					'url'=>'content/'.$post->pid, 
					'title'=>$post->title, 
					'body'=>$post->body, 
					'images'=>isset($images) ? $images : null,
					'created'=>date('M jS',$post->created),
					'author'=>$tmp_user->name,
					'numcomments'=>isset($num) ? ($num != 1 ? $num.' comments' : $num.' comment') : null
				));
				unset($images,$tmp_user);
			}
		}else{
			array_push($theList, array( 
				'url'=>'', 
				'title'=>'No Content to Display', 
				'body'=>'There is currently no published content to display.', 
				'created'=>date('M jS'),
				'author'=>'System',
			));
		}
		//$smarty->assign('pager',BaseController::Paginate($posts['limit'], $posts['nbItems'], '', $page));
		return $theList;
	}
	
	/**
	 * 
	 * @return 
	 * @param object $id
	 */
	public static function edit($id){
		global $Core;
		$content = new Mod_Content($id);
		$Core->smarty->assign(self::Form($content));
	}
	
	/**
	 * 
	 * @return 
	 * @param object $content[optional]
	 */
	public static function form(&$content=null){
		$Core = parent::getCore();
		$func = $content ? 'edit/'.$content->pid : 'add';
		$tablinks = array('Main','Image Functions','Publishing Options');
		//Create the form object
		$element_array = array('name'=>'content','method'=>'post','action'=>'admin/content/'.$func);
		//set form default values
	
		if(isset($content)){
			$element_array['defaults']=array(
				'pid'=>$content->pid, 
				'title'=>$content->title, 
				'published'=>$content->published,
				'body'=> preg_replace('/<br \/>/','',$content->body),
				'imagelist'=>$content->images,
				'type'=>isset($content->type) ? $content->type : $content->getDefaultType()
			);
			$header = 'Edit Content';
		}else{
			$element_array['defaults']=array(
				'published'=>'1'
			);
			$content = new Mod_Content();
		}
		
		$content->GetTypes();
		//create form elements
		$element_array['elements'] = array(
			array('type'=>'header','name'=>'','label'=>'Create Content'),
			array('type'=>'text', 'name'=>'title', 'label'=>'Title', 'options'=>array('size'=>62, 'maxlength'=>80)),
			array('type'=>'textarea','name'=>'body','label'=>'Body','options'=>array('rows'=>20,'cols'=>60,'id'=>'ckeditor')),
			
			array('type'=>'header','name'=>'','label'=>'Image Functions','group'=>'1'),
			array('type'=>'file','name'=>'image','label'=>'Add Image', 'options'=>array('id'=>'image'),'group'=>'1'),
	//		if(!empty($content->images)){
	//			array('hidden','imagelist','', array('id'=>'imagelist')),
	//		}
	//		$form->addElement('text', 'ititle', 'Title', array('size'=>25, 'maxlength'=>15));
	//		$form->addElement('text', 'ialt', 'Alt Text', array('size'=>25, 'maxlength'=>15));
			
			array('type'=>'header','name'=>'','label'=>'Publishing Options','group'=>'2'),
	//		$form->addElement('text', 'tags', 'Tags', array('size'=>25, 'maxlength'=>15));
			array('type'=>'select', 'name'=>'type', 'label'=>'Content Type', 'list'=>$content->types,'group'=>'2'),
			array('type'=>'checkbox','name'=>'published','label'=>'Publish','group'=>'2'),
			
			array('type'=>'hidden','name'=>'pid'),
			array('type'=>'hidden','name'=>'author', 'value'=>$Core->user->uid),
			array('type'=>'hidden','name'=>'created','value'=>time()),
					
			array('type'=>'submit', 'name'=>'submit', 'value'=>'Save')
		);
		
		$element_array['filters'] = array(array("__ALL__","trim"));
		
		$element_array['callback'] = array('ContentController','Save');
		//apply form prefilters

		//add form rules
		$element_array['rules'] = array(
			array('required','title'),
			array('required','body')
		);
		
		//If the form has already been submitted - validate the data

		//send the form to smarty
		return array(
			'form'=>self::generateForm($element_array),
			'tabbed'=>$tablinks
		);
	}
	
	/**
	 * 
	 * @return 
	 */
	public static function formSettings(){
		$content = new Mod_Content();
		$Core = parent::getCore();
		//Create the form object
		$elements_array = array('name'=>'settings','method'=>'post','action'=>'admin/content/settings');
		//set form default values

		$elements_array['defaults'] = array(
			'type'=>$content->getDefaultType() 
		);
	
		$content->GetTypes();
		
		//create form elements
		$elements_array['elements'] = array(
			array('type'=>'header','name'=>'','label'=>'Content Settings'),
			array('type'=>'select', 'name'=>'type', 'label'=>'Default Content Type', 'list'=>$content->types),
			array('type'=>'submit', 'name'=>'submit', 'value'=>'Save')
		);
		//apply form prefilters
		$elements_array['filters']= array(array('__ALL__'=>'trim'));
		$elements_array['callback'] = array($Core,'saveSettings');
		//If the form has already been submitted - validate the data
//		if(isset($_POST['submit']) && $form->validate()){
//			Core = parent::getCore();
//			foreach($form->exportValues() as $val){
//				$core->saveSettings($val,$setting);
//			}
//			if($Core->saveSettings($form->exportValue('type'),'defaultContentType')){
//				$Core->setMessage("Settings saved Successfully!", 'info');
//			}else{
//				$Core->setMessage("Unable to save settings.","error");
//			}
		
		//send the form to smarty
		$form = parent::generateForm($elements_array);
		
		if(isset($_POST['submit'])){
			$form->exportValues();
		}
		
		return parent::generateForm($elements_array);
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
			$options['class'] = 'action-link noloader';
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
			
			$modified = !empty($post['modified']) ? date('m-d-Y',$post['modified']) : 'N/A';
			
			array_push($list, array(
				'id'=>$post['pid'], 
				'title'=>$post['title'], 
				'created'=>date('m-d-Y',$post['created']),
				'modified'=>$modified,
				'author'=>ucfirst($post['author']),
				'published'=>"<center><img src='".THEME_PATH."/images/{$post['published']}-25.png'/></center>",
				'actions'=>$actions
				));
		}
		$options = array('image'=>'24', 'class'=>'action-link-tab', 'title' => 'Add Content');
		//create the actions options and bind the params to smarty
		return array(
			'pager'=>BaseController::Paginate($content->items['limit'], $content->items['nbItems'], 'admin/content/'.$type.'/', $page),
			'sublinks'=>array(
				'add'=>Core::l('add','admin/content/add',$options),
				'settings'=>Core::l('settings','admin/content/settings',array('image'=>'24', 'class'=>'action-link-tab', 'title'=>'Content Settings')),
				//'addtype'=>Core::l('addtype','admin/content/addtype',array('image'=>'24', 'class'=>'action-link-tab', 'title'=>'Add Content Type'))
			),
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
	 * @param object $data
	 */
	public static function save($data){ 
		//fields: Title | Body | Created | Modified | Author | Published | Tags
		//upload files if needed
		//var_dump($data);
		$image = '';
		if(isset($data['image']['name'])){
			$image = parent::HandleImage($data['image'],'80');
			$data['imagelist'] = $image['thumb'].'|'.$image['orig'].";";
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
			$content->body = $data['body'];
			$content->images = isset($data['imagelist']) ? $data['imagelist'] : '';
			$content->modified = time();
			$content->published = isset($data['published']) ? '1' : '0';
			$content->type = $data['type'];
			$content->Commit();
			Core::SetMessage('Your changes have been saved!','info');
		}else{ //Create a new Post
			$content = new Mod_Content();
			isset($data['published']) ? '1' : '0';
			$data['body'] = $data['body'];
			if ($content->create($data) == true){
				Core::SetMessage('Your content has been successfully saved','info');
			}else{
				Core::SetMessage('Unable to save content. Please try again later.','error');
				return false;
			}
		}
		return true;
		//UserController::Redirect('admin/posts');
	}
	
	/**
	 * 
	 * @return 
	 * @param object $pid
	 */
	public static function view($pid){
		$Core = BaseController::getCore();
		$post = self::GetContent($pid);
		if(isset($post->comments)){
			$num = count($post->comments->all);
		}

		if(!empty($post->images)){
			$images = explode(';',$post->images);
			array_walk($images,array('BaseController','split'),'|');
		}
		$tmp_user = new User($post->author);
		$data = array( 
			'url'=>'content/'.$post->pid, 
			'title'=>$post->title, 
			'body'=>$post->body,
			'images'=>isset($images) ? $images : null,
			'created'=>date('M jS',$post->created),
			'author'=>$tmp_user->name,
			'numcomments'=>isset($num) ? ($num != 1 ? $num.' comments' : $num.' comment') : null
		);
		$Core->smarty->assign('post', $data);
		if($Core->isEnabled('Comments')){
			CommentsController::showComments($post->comments,$post->pid);
		}
		
		unset($tmp_user);
	}

}

?>