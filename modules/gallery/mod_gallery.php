<?php
/**
 * 
 */

class Mod_Gallery{
	
	/**
	 * @var
	 */
	private $dbh;
	public $template;
	public $image_folder_path;
	public $image_upload_path;
	private $tmp_path;
	
	/**
	 * 
	 * @return 
	 * @param object $id[optional]
	 */
	public function __construct(){
		$this->dbh = DBCreator::GetDbObject();
		$this->image_folder_path = UPLOAD_PATH.'gallery/images/';
		$this->image_upload_path = UPLOAD_PATH.'gallery/uploads/';
		$this->tmp_path = UPLOAD_PATH.'gallery/tmp/';
		$this->template = '../../modules/gallery/templates/gallery.tpl';
		$Core = BaseController::getCore();
		//$Core->saveSettings('templates/NanobyteBlue/images','thumbs_list');
		$this->thumbs_dir = $Core->getSettings('thumbs_dir');
		$this->setup = array(
			'category_types'=>array('Gallery'),
			'folders'=>array('gallery','gallery/uploads','gallery/tmp','gallery/images'),
			'menus'=>array(
				'menu'=>'admin',
				'linkpath'=>'admin/gallery', //path
				'linktext'=>'Gallery', //text
				'viewableby'=>array('admin'), //set default permissions for the menu item
				'styleid'=>'a-gallery', //html id
				'class'=>'' //html class
			),
			'permissions'=>array('View Gallery'),
			'tables'=>array(
				'gallery_images' => array(
					array('image_id','int','11',true,true),
					array('path','varchar','255',true),
					array('thumb','varchar','255',true),
					array('name','varchar','50'),
					array('caption','varchar','255'),
					array('album','int','11'),
					array('date_added','varchar','12'),
					'key'=>'image_id'
				)
			)
		);
	}
	
	public function addImage($path, $thumb, $name, $album=0){
		$query = $this->dbh->prepare("INSERT INTO ".DB_PREFIX."_gallery_images (path, thumb, name, album) VALUES (:p,:t, :n, :a)");
		$query->execute(array(':p'=>$path,':t'=>$thumb, ':n'=>$name , ':a'=>$album));
	}
	
	public function createAlbum($album_info){
		$Core = BaseController::getCore();
		$Core->addCategory($album_info['name'],$album_info['description'],'Gallery');
	}
	
	public function display(){
		Gallerycontroller::display();
	}
	
	public function getAlbums($id = null){
		$Core = BaseController::getCore();
		return $Core->getCategories('Gallery',$id);
	}
	
	public function importImages(){
		$Core = BaseController::getCore();
		$images = glob($this->image_upload_path.'*.*');
		if(count($images) <= 0){
			$Core->setMessage('There are no images available for import','status');
			return;
		}
		foreach($images as $image){
			$i = array();
			$i['path'] = $image;
			$i['type'] = 'image/'.substr($image,-3);
			$i['name'] = substr(strrchr($image,47),1);
			$img = BaseController::ResizeImage($i,100);
			$orig = uniqid().'.png';
			$thumb = uniqid().'.png';
			rename($img['orig'],$this->image_folder_path.$orig);
			rename($img['thumb'],$this->image_folder_path.$thumb);
			$this->addImage($orig,$thumb,substr(strrchr($img['orig'],'/'),1));
		}
	}
	
	/**
	 * 
	 * @return 
	 */
	public static function install(){
		//Module::regBlock(array('name'=>'Menu', 'module'=>'Menu', 'options'=>''));
	}
	
	/**
	 * 
	 * @return 
	 * @param object $type
	 * @param object $published[optional]
	 * @param object $limit[optional]
	 * @param object $start[optional]
	 */
	public function read($album_id = 0){
		$query = $this->dbh->prepare("SELECT * FROM ".DB_PREFIX."_gallery_images WHERE album=:a");
		$query->execute(array(':a'=>$album_id));
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}
	
	/**
	 * 
	 * @return 
	 */
	public static function uninstall(){
	}
	
	public function updateAlbum($album_info){
		$Core = BaseController::getCore();
		$Core->updateCategory($Core->args[3],$album_info['name'],$album_info['description']);
	}
	
	public function updateImage($update){
		$query_string = "UPDATE ".DB_PREFIX."_gallery_images SET ";
		if(isset($update['album'])){
			$query_string .= "album=:a ";
			$bind_params[':a'] = $update['album'];
		}
		$query_string .= " WHERE image_id IN ({$update['images']})";
		$query = $this->dbh->prepare($query_string);
		$query->execute($bind_params);
		
	}
	
}

class Block_Gallery extends Mod_Gallery{
	
	function __construct(){
		global $Core;
		BaseController::AddJs('modules/posts/js/posts.js');
		$this->template = '../../modules/menu/menu.tpl';
		$Core->smarty->assign('menusblock',$this->buildMenus());
	}
	
	public function buildMenus(){
		global $Core;
		
		$p = new Mod_Menu();
		$menus = $p->read();
		
		$menuArray = array();
		foreach($menus as $menu){
			if(strpos($menu['viewableby'],$Core->user->group)!==false){
				if(!isset($menu['linktext'])){
					$menuArray[$menu['name']]['settings'] = $menu;
				}
				$menuArray[$menu['name']][] = $menu;
			}
		}
		
		return $menuArray;
	}
	
}

class GalleryController extends BaseController{
	
	public static function admin(){
		$Core = parent::getCore();
		
		$content = '';
		if(isset($Core->args[1])){
			if($Core->args[1] == 'list'){
				$Core->smarty->assign(self::listAlbums());
				$content = $Core->smarty->fetch('list.tpl'); 
			}elseif(method_exists('GalleryController',$Core->args[1])){
				$content = call_user_func(array('GalleryController',$Core->args[1]));
			}
		}else{
			$tabs = array(Core::l('Albums','admin/gallery/list'),Core::l('Add Images','admin/gallery/images'),Core::l('Uncategorized Images','admin/gallery/albums/0'));
			$Core->smarty->assign('tabs',$tabs);
			if($Core->ajax){$Core->json_obj->tabs = $Core->smarty->fetch('tabs.tpl');}
		}
		$Core->json_obj->content = $content;
	}
	
	public static function addAlbum(){
		$form = self::albumForm();
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
	
	public static function albumForm(){
		$Core = parent::getCore();
		//Create the form object
		$element_array = array('name'=>'addalbum','method'=>'post','action'=>'admin/gallery/albums/add');
		$callback = 'createAlbum';
		//set form default values
	
		if(isset($Core->args[2]) && $Core->args[2] == 'edit'){
			$gallery = new Mod_Gallery();
			$album = $gallery->getAlbums($Core->args[3]);
			$element_array['defaults']=array(
				'name'=>$album[0]['name'],
				'description' => $album[0]['description']
			);
			$element_array['action'] = 'admin/gallery/albums/edit/'.$Core->args[3];
			$callback = 'updateAlbum';
		}
		//create form elements
		$element_array['elements'] = array(
			array('type'=>'header','name'=>'','label'=>'Add Album'),
			array('type'=>'text', 'name'=>'name', 'label'=>'Name', 'options'=>array('size'=>62, 'maxlength'=>80)),
			array('type'=>'text','name'=>'description','label'=>'Description', 'options'=>array('size'=>62, 'maxlength'=>80)),
			
			array('type'=>'submit', 'name'=>'submit', 'value'=>'Save')
		);
		
		$element_array['filters'] = array(array("__ALL__","trim"));
		
		$element_array['callback'] = array(new Mod_Gallery(),$callback);
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
	
	public static function albums(){
		$Core = parent::getCore();
		$callback = $Core->args[2].'album';
		if($Core->args[2] == 'edit'){
			$callback = 'addalbum';
		}
		if(is_numeric($Core->args[2])){
			$callback = 'getImagesforAlbum';
		}
		if(method_exists('GalleryController',$callback)){
			return call_user_func(array('GalleryController',$callback));
		}
	}
	
	public static function bulkImage(){
		$gallery = new Mod_Gallery();
		$gallery->importImages();
	}
	
	public static function deleteAlbum(){
		$Core = parent::getCore();
		if(Admin::deleteObject('category','category_id', $Core->args[3])===true){
			$Core->json_obj->callback = 'nanobyte.deleteRows';
			$Core->json_obj->args = $Core->args[3]."|";
		}else{
			$Core->setMessage("Unable to delete album","error");
		}
	}
	
	public static function display(){
		$Core = BaseController::getCore();
		if($Core->authUser('View Gallery')){
			$gallery = new Mod_Gallery();
			
			if(isset($Core->args[0]) && $Core->args[0] == 'albums'){
				$images = $gallery->read($Core->args[1]);
				$album_info = $gallery->getAlbums($Core->args[1]);
				
				$image_list = array();
				foreach($images as $image){
					$image_list[$image['image_id']] = array(
						'thumb'=>$gallery->image_folder_path.$image['thumb'],
						'orig'=>$gallery->image_folder_path.$image['path']
					);
				}
				$Core->smarty->assign(array(
					'image_list'=>$image_list,
					'album_title'=>$album_info[0]['name']
				));
			}else{
				$albums = $gallery->getAlbums();
				$album_list = array();
				foreach($albums as $album){
					$album_list[$album['album_id']] = $album['name'];
				}
				$Core->smarty->assign('album_list',$album_list);
			}
			
			$Core->smarty->assign('content',$Core->smarty->fetch($gallery->template));
		}else{
			$Core->setMessage('You do not have permission to access the Gallery.','error');
			BaseController::Redirect('home');
		}
		BaseController::getHTMLIncludes();
		return $Core->smarty->display('index.tpl');
	}	
	
	public static function getImagesforAlbum(){
		$Core = parent::getCore();
		$gallery = new Mod_Gallery();
		$image_list = $gallery->read($Core->args[2]);
		$list = array();
		foreach($image_list as $image){
			array_push($list,array(
				'id'=>$image['image_id'],
				'image'=>'<img src="'.$gallery->image_folder_path.$image['thumb'].'"/>',
				'name'=>$image['name']
			));
		}
		$album_list = $gallery->getAlbums();
		$albums = array();
		foreach($album_list as $album){
			$albums[$album['album_id']] = $album['name'];
		}
		$Core->smarty->assign(array(
			'list'=>$list,
			'cb'=>true,
			'albums'=>$albums,
			'extra'=>'Move to: {html_options name=actions options=$albums}<input type="submit" name="submit" value="Go!"/>',
			'formAction'=>'admin/gallery/albums/moveto'
		));
		return $Core->smarty->fetch('list.tpl');
	}
	
	public static function listAlbums(){
		$gallery = new Mod_Gallery();
		$Core = BaseController::getCore();
		$albums = $gallery->getAlbums();
		$album_list = array();
		foreach($albums as $album){
			array_push($album_list, array(
				'id'=>$album['category_id'],
				'album'=>$album['name'],
				'description'=>$album['description'],
				'actions'=> $Core->l('Edit','admin/gallery/albums/edit/'.$album['category_id'], array('image'=>16,'class'=>'action-link-tab','title'=>'Edit Album')) 
				. " | " . $Core->l('Info','admin/gallery/albums/'.$album['category_id'], array('image'=>16,'class'=>'action-link-tab','title'=>'Image List')) 
				. " | " . $Core->l('Delete','admin/gallery/albums/delete/'.$album['category_id'],array('image'=>16,'class'=>'action-link','title'=>'Delete Album'))
			));
		}
		return array(
			'list'=>$album_list,
			'sublinks'=>array(
				'add'=>Core::l('add','admin/gallery/albums/add',array('image'=>'24', 'class'=>'action-link-tab', 'title' => 'Add Album'))
			)
		);
	}
	
	public static function moveToAlbum(){
		$Core = parent::getCore();
		$gallery = new Mod_Gallery();
		$gallery->updateImage(array('album'=>$Core->args[3],'images'=>implode(',',$_POST['gallery'])));
	}
	
	public static function images(){
		$Core = parent::getCore();
		if(isset($Core->args[2]) && !empty($Core->args[2])){
			$callback = $Core->args[2].'image';
			if(method_exists('GalleryController',$callback)){
				return call_user_func(array('GalleryController',$callback));
			}
		}elseif(isset($_POST['bulk'])){
			self::bulkImage();
		}else{
			$element_array = array('name'=>'images','method'=>'post','action'=>'admin/gallery/images');
			$element_array['elements'] = array(
				array('type'=>'header','name'=>'','label'=>'Bulk Import Images'),
				array('type'=>'submit', 'name'=>'bulk', 'value'=>'Bulk Import')
			);
			$Core->smarty->assign('form',self::generateForm($element_array));
			return $Core->smarty->fetch('form.tpl');
		}
	}
}
?>