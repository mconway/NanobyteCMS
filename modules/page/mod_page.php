<?php
class Mod_Page{
	
	private $dbh;
	
	public function install(){
		//Add Page Content type
		$content = new Mod_Content();
		$content->RegisterContentType('Page');
	}
	
	public static function uninstall(){
		$content = new Mod_Content();
		$content->UnregisterContentType('Page');
	}
	
	public function __construct($pid=null){
		$this->dbh = DBCreator::GetDBObject();
		$this->setup = array('permissions'=>array('Edit Pages'));
		if (isset($pid)){
			$query = $this->dbh->prepare("SELECT pid, title, body, username, created FROM ".DB_PREFIX."_content LEFT JOIN ".DB_PREFIX."_user AS user ON author=uid LEFT JOIN ".DB_PREFIX."_content_types ON type=id WHERE parent=:pid");
			$query->execute(array(':pid'=>$pid));
			$this->all = $query->fetchAll(PDO::FETCH_ASSOC);
		}
	}
	
	public function read($limit=15,$start=0){ //Replaces GetPostLst
		$Core = BaseController::getCore();
		$result = $this->dbh->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM ".DB_PREFIX."_content LEFT JOIN ".DB_PREFIX."_content_types ON type=id WHERE name = 'Page' ORDER BY created DESC LIMIT {$start},{$limit}");
		//get the row count
		$cRows = $this->dbh->prepare('SELECT found_rows() AS rows');
		try{
			$result->execute();
			$output = array();
			$output['content'] = $result->fetchAll(PDO::FETCH_ASSOC);
			$cRows->execute();
	        $nbItems = $cRows->fetch(PDO::FETCH_OBJ)->rows;
			if ($nbItems>($start+$limit)) $output['final'] = $start+$limit;
			else $output['final'] = $nbItems;
			$output['limit'] = $limit;
			$output['nbItems'] = $nbItems;
		}catch (PDOException $e){
			$Core->SetMessage($e->getMessage(), 'error');
		}
		return $output;
	}
	
	public function commit($params){
		$Core= BaseController::getCore();
		$sql = $this->dbh->prepare("INSERT INTO ".DB_PREFIX."_content SET title=:t, body=:b, author=:a, created=:d, parent=:p, type=(SELECT category_id FROM ".DB_PREFIX."_category WHERE name = 'Page')");
		$sql->execute(array(
			':t'=> $params['title'],
			':b'=> $params['body'],
			':p'=> $params['pid'],
			':a'=> $Core->user->uid,
			':d'=> time()
		));
		if ($sql->rowCount() == 1){
			$Core->json_obj->callback = 'nanobyte.redirect';
			$Core->json_obj->args = $Core->url('content/'.$params['pid']);
			return true;
		}else{
			return false;
		}
	}

}
 
class PageController extends BaseController{
	public static function display($content){
		$core = parent::getCore();
		if(!empty($content->images)){
			$content->images = explode(';',$content->images);
			array_walk($content->images,array(parent,'split'),'|');
			array_pop($content->images);
		}
		$core->smarty->assign('page',$content);
		$fileName = "page" . uniqid();
		file_put_contents("modules/page/tmp/{$fileName}.tpl",$content->body);
		return $core->smarty->fetch("../../modules/page/tmp/{$fileName}.tpl");
	}
}

?>
