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

class Mod_Downloads{
	
	/**
	 * @var
	 */
	private $dbh;
	private $root_folder;
	public $file_path;
	public $tmp_path;
	
	/**
	 * 
	 * @return 
	 * @param object $id[optional]
	 */
	public function __construct($id=null){
		$this->dbh = DBCreator::GetDbObject();
		$this->root_folder = 'downloads/';
		$this->file_path = 'downloads/files/';
		$this->tmp_path = 'downloads/tmp/';
		$this->setup = array(
			'category_types'=>array('Downloads'),
			'folders'=>array($this->root_folder,$this->tmp_path,$this->file_path),
			'menus'=>array(
				'menu'=>'admin',
				'linkpath'=>'admin/downloads', //path
				'linktext'=>'Downloads', //text
				'viewableby'=>array('admin'), //set default permissions for the menu item
				'styleid'=>'a-downloads', //html id
				'class'=>'' //html class
			),
			'permissions'=>array('Access Downloads', 'Add Download Files'),
			'tables'=>array(
				'downloads' => array(
					array('download_id','int','11',true,true),
					array('filename','varchar','255',true),
					array('name','varchar','50'),
					array('description','varchar','255'),
					array('category','int','11'),
					array('date_added','varchar','12'),
					array('counter','int'),
					'key'=>'download_id'
				)
			)
		);
	}

	/**
	 * 
	 * @return 
	 */
	public function addFile($file_info){
		$sql = $this->dbh->prepare("INSERT INTO ".DB_PREFIX."_downloads (filename,name,description,category,date_added,counter) VALUES (?,?,?,?,?,?)");
		$sql->execute(array(
			0=>$file_info['filename'],
			1=>$file_info['name'],
			2=>$file_info['description'],
			3=>$file_info['category'],
			4=>time(),
			5=>0
		));
		if ($sql->rowCount() == 1){
			return true;
		}else{
			return false;
		}
	}
	
	public function handleFile(){
		$Core = BaseController::getCore();
		$file_name = uniqid().substr($_FILES['file']['name'],strrpos($_FILES['file']['name'],'.'));
		move_uploaded_file($_FILES['file']['tmp_name'],UPLOAD_PATH.'downloads/files/'.$file_name);
		$Core->json_obj->callback = 'updateFile';
		$Core->json_obj->args = $file_name;
	}
	
	/**
	 * 
	 * @return 
	 */
	public static function install(){
	}

	/**
	 * 
	 * @return 
	 * @param object $type
	 * @param object $published[optional]
	 * @param object $limit[optional]
	 * @param object $start[optional]
	 */
	public function read($type, $limit=LIMIT, $start=0){
		$Core = BaseController::getCore();
		try{
			$this->items = array();
			if(!is_numeric($type)){
				throw new Exception("Type must be numeric!");
			}
			$query = "SELECT SQL_CALC_FOUND_ROWS * FROM ".DB_PREFIX."_downloads WHERE category={$type} ORDER BY download_id DESC LIMIT {$start},{$limit}";
			$this->items['content'] = $this->dbh->query($query)->fetchAll(PDO::FETCH_ASSOC);
			$cntRows = $this->dbh->query('SELECT found_rows() AS rows')->fetch(PDO::FETCH_OBJ);
			$this->items['final'] = $cntRows->rows >($start+$limit) ? $start+$limit : $cntRows->rows;
			$this->items['limit'] = $limit;
			$this->items['nbItems'] = $cntRows->rows;
			//print('<pre>'.print_r($this->items['content']).'</pre>');
		}catch (PDOException $e){
			$Core->SetMessage($e->getMessage(), 'error');
		}catch(Exception $e){
			$Core->SetMessage($e->getMessage(), 'error');
		}
	}

	/**
	 * 
	 * @return 
	 */
	public static function uninstall(){

	}
	
}

/**
 * 
 */
class DownloadsController extends BaseController{
	
	public static function addCategory(){
		$form = self::categoryForm();
		$Core = parent::getCore();
		if(is_object($form['form'])){
			$Core->smarty->assign($form);
			return $Core->smarty->fetch('form.tpl');
		}else{
			if($form['form'] === true){
				$Core->json_obj->callback = 'nanobyte.closeParentTab';
				$Core->json_obj->args = 'input[name=submit][value=Save]';
			}
		}
	}
	
	public static function addFile(){
		$Core = parent::getCore();
		if($Core->ajax == true && !empty($_FILES) && !isset($_POST['submit'])){
			$downloads = new Mod_Downloads();
			$downloads->handleFile();
		}else{
			$form = self::addFileForm();
			if(is_bool($form) && $form === true){
				$Core->setMessage("Your file was added successfully!","info");
				$Core->json_obj->callback = "nanobyte.clearForm";
				$Core->json_obj->args = null;
			}
		}
	}
	
	public static function addFileForm($action = 'admin/downloads/files/add'){
			$Core = parent::getCore();
			$categories = $Core->getCategories('Downloads');
			foreach($categories as $cat){
				$category_list[$cat['category_id']] = $cat['name'];
			}
			
			$element_array = array('name'=>'files','method'=>'post','action'=>'admin/downloads/files/add');
			$element_array['elements'] = array(
				array('type'=>'header','name'=>'','label'=>'Add New File'),
				array('type'=>'text', 'name'=>'name', 'label'=>'Name', 'options'=>array('size'=>62, 'maxlength'=>80)),
				array('type'=>'text', 'name'=>'description', 'label'=>'Description', 'options'=>array('size'=>62, 'maxlength'=>80)),
				array('type'=>'file','name'=>'file', 'label'=>'File', 'options'=>array('id'=>'file')),
				array('type'=>'select', 'name'=>'category', 'label'=>'Content Type', 'list'=>$category_list,'group'=>'2'),
				array('type'=>'submit', 'name'=>'submit', 'value'=>'Save'),
				array('type'=>'hidden', 'name'=>'filename', 'options'=>array('id'=>'filename'))
			);
			$element_array['callback'] = array(new Mod_Downloads(),'addFile');
			return self::generateForm($element_array);
	}
	
	public static function admin(){
		$Core = parent::getCore();
		$content = '';
		if(isset($Core->args[1])){
			if($Core->args[1] == 'list'){
				$Core->smarty->assign(self::listCategories());
				$content = $Core->smarty->fetch('list.tpl'); 
			}elseif(method_exists('DownloadsController',$Core->args[1])){
				$content = call_user_func(array('DownloadsController',$Core->args[1]));
			}
		}else{
			//,$Core->l('Uncategorized Files','admin/downloads/categories/0')
			$tabs = array($Core->l('Categories','admin/downloads/list'),$Core->l('Add File','admin/downloads/files'));
			$Core->smarty->assign('tabs',$tabs);
			if($Core->ajax){$Core->json_obj->tabs = $Core->smarty->fetch('tabs.tpl');}
		}
		$Core->json_obj->content = $content;
		if(!isset($Core->args[1])){
			$Core->json_obj->callback = 'nanobyte.getScript';
			$Core->json_obj->args ='modules/downloads/mod_downloads.js';
		}

	}
		
	public static function categories(){
		$Core = parent::getCore();
		$callback = $Core->args[2].'category';
		if($Core->args[2] == 'edit'){
			$callback = 'addcategory';
		}
		if(is_numeric($Core->args[2])){
			$callback = 'getFilesForCategory';
		}
		if(method_exists('DownloadsController',$callback)){
			return call_user_func(array('DownloadsController',$callback));
		}
	}	
	
	public static function categoryForm(){
		$Core = parent::getCore();
		//Create the form object
		$element_array = array('name'=>'addcategory','method'=>'post','action'=>'admin/downloads/categories/add');
		$callback = 'createCategory';
		//set form default values
	
		if(isset($Core->args[2]) && $Core->args[2] == 'edit'){
			$category = $Core->getCategories('Downloads',$Core->args[3]);
			$element_array['defaults']=array(
				'name'=>$category[0]['name'],
				'description' => $category[0]['description']
			);
			$element_array['action'] = 'admin/downloads/categories/edit/'.$Core->args[3];
			$callback = 'updatecategory';
		}
		//create form elements
		$element_array['elements'] = array(
			array('type'=>'header','name'=>'','label'=>'Add Category'),
			array('type'=>'text', 'name'=>'name', 'label'=>'Name', 'options'=>array('size'=>62, 'maxlength'=>80)),
			array('type'=>'text','name'=>'description','label'=>'Description', 'options'=>array('size'=>62, 'maxlength'=>80)),
			
			array('type'=>'submit', 'name'=>'submit', 'value'=>'Save')
		);
		
		$element_array['filters'] = array(array("__ALL__","trim"));
		
		$element_array['callback'] = array('DownloadsController',$callback);
		//apply form prefilters

		//add form rules
		$element_array['rules'] = array(
			array('required','name'),
			array('required','description')
		);
		
		//If the form has already been submitted - validate the data

		//send the form to smarty
		return array(
			'form'=>self::generateForm($element_array),
		);
	}
	
	public static function createCategory($cat_info){
		$Core = BaseController::getCore();
		$Core->addCategory($cat_info['name'],$cat_info['description'],'Downloads');
	}
	
	public static function display(){
		$Core = BaseController::getCore();
		if($Core->authUser('Access Downloads')){
			$downloads = new Mod_Downloads();
			
			if(isset($Core->args[0]) && isset($Core->args[1]) &&  $Core->args[0] == 'categories'){
				$downloads->read($Core->args[1]);
				$category_info = $Core->getCategories('Downloads',$Core->args[1]);
				
				$file_list = array();
				foreach($downloads->items['content'] as $file){
					array_push($file_list, array(
						'id'=>$file['download_id'],
						'name'=>$file['name'],
						'description'=>$file['description'],
						'downloads'=>$file['counter'],
						'actions'=>$Core->l('Download',UPLOAD_PATH.$downloads->file_path.$file['filename'])
					));
				}
				$Core->smarty->assign(array(
					'list'=>$file_list,
					'list_title'=>$Core->l($category_info[0]['name'],'downloads/categories')
				));
			}else{
				$categories = $Core->getCategories('Downloads');
				$cat_list = array();
				foreach($categories as $category){
					array_push($cat_list,array('name'=>$Core->l($category['name'],'downloads/categories/'.$category['category_id'])));
				}
				$Core->smarty->assign(array(
					'list'=>$cat_list,
					'list_title'=>"Select a download category"
				));
			}
			
			$Core->smarty->assign('content',$Core->smarty->fetch('list.tpl'));
		}else{
			$Core->setMessage('You do not have permission to access the Gallery.','error');
			BaseController::Redirect('home');
		}
		BaseController::getHTMLIncludes();
		return $Core->smarty->display('index.tpl');
	}	
	
	public static function files(){
		$Core = parent::getCore();
		
		if(isset($Core->args[2]) && !empty($Core->args[2])){
			$callback = $Core->args[2].'file';
			if(method_exists('DownloadsController',$callback)){
				return call_user_func(array('DownloadsController',$callback));
			}
		}else{
			if($Core->authUser('Add Download Files')){
				$Core->smarty->assign('form',self::addFileForm('downloads/files/add'));
				return $Core->smarty->fetch('form.tpl');
			}
		}
	}
	
	public static function getFilesForCategory(){
		$Core = parent::getCore();
		$downloads = new Mod_Downloads();
		$downloads->read($Core->args[2]);
		$file_list = $downloads->items['content'];
		$list = array();
		foreach($file_list as $file){
			array_push($list,array(
				'id'=>$file['download_id'],
				'file'=>'<a href="'.UPLOAD_PATH.$downloads->file_path.$file['filename'].'">'.$file['name'].'</a>',
				'description'=>$file['description'],
				'Times Downloaded' => $file['counter']
			));
		}
		$category_list = $Core->getCategories('Downloads');
		$categories = array();
		foreach($category_list as $category){
			$categories[$category['category_id']] = $category['name'];
		}
		$Core->smarty->assign(array(
			'list'=>$list,
			'cb'=>true,
			'categories'=>$categories,
			'extra'=>'Move to: {html_options name=actions options=$categories}<input type="submit" name="submit" value="Go!"/>',
			'formAction'=>'admin/downloads/files/moveto'
		));
		return $Core->smarty->fetch('list.tpl');
	}
	
	public static function listCategories(){
		$Core = BaseController::getCore();
		$categories = $Core->getCategories('Downloads');
		$cat_list = array();
		foreach($categories as $cat){
			array_push($cat_list, array(
				'id'=>$cat['category_id'],
				'name'=>$cat['name'],
				'description'=>$cat['description'],
				'actions'=> $Core->l('Edit','admin/downloads/categories/edit/'.$cat['category_id'], array('image'=>16,'class'=>'action-link-tab','title'=>'Edit Category'))
							 . " | " . 
							 $Core->l('Info','admin/downloads/categories/'.$cat['category_id'], array('image'=>16,'class'=>'action-link-tab','title'=>'View File List')) 
							 . " | " . 
							 $Core->l('Delete','admin/downloads/categories/delete/'.$cat['category_id'],array('image'=>16,'class'=>'action-link','title'=>'Delete Category'))
			));
		}
		return array(
			'list'=>$cat_list,
			'sublinks'=>array(
				'add'=>$Core->l('add','admin/downloads/categories/add',array('image'=>'24', 'class'=>'action-link-tab', 'title' => 'Add Category'))
			)
		);
		
	}

	public function updateCategory($cat_info){
		$Core = BaseController::getCore();
		$Core->updateCategory($Core->args[3],$cat_info['name'],$cat_info['description']);
	}
}

?>