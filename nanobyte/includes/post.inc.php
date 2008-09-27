<?php

class Post{
//@params  - an assosiative array of the post data - title, body, published, tags
private $DB;
	function __construct($id){
		$this->DB = DBCreator::GetDbObject();
		$result = $this->DB->prepare("select * from ".DB_PREFIX."_posts where `pid`=:id");
		$result->bindParam(':id', $id);
		try{
			$result->execute();
			$row = $result->fetch(PDO::FETCH_ASSOC);
			$this->pid = $row['pid'];
			$this->title = $row['title'];
			$this->body = $row['body'];
			$this->created = $row['created'];
			$this->author = $row['author'];
			$this->published = $row['published'];
		}catch(PDOException $e){
			Core::SetMessage($e->getMessage(), 'error');
		}
	}
	function CreatePost($params){
		//take params and write post to DB.
		$DB = DBCreator::GetDbObject();
		$insert = $DB->prepare("insert into ".DB_PREFIX."_posts (title, body, created, author, published) values (:ti,:b,:c,:a,:p)");
		$insert->bindParam(':ti', $params['title']);
		$insert->bindParam(':b', $params['body']);
		$insert->bindParam(':c', $params['created']);
		$insert->bindParam(':a', $params['author']);
		$insert->bindParam(':p', $params['published']);
		//$insert->bindParam(':ta', $params['tags']);
		try{
			$insert->execute();
		}catch(PDOException $e){
			Core::SetMessage($e->getMessage(), 'error');
		}
		if($insert->rowCount() == 1){
			return true;
		}else{
			return false;
		}
	}
	
	public static function Read($published=null,$limit=15,$start=0){ //Replaces GetPostLst
		$DB = DBCreator::GetDbObject();
		if($published){
			$where = "where `published`=".$published;
		}
		$result = $DB->prepare("SELECT SQL_CALC_FOUND_ROWS `pid` FROM ".DB_PREFIX."_posts {$where} ORDER BY created DESC LIMIT {$start},{$limit}");
		//get the row count
		$cRows = $DB->prepare('SELECT found_rows() AS rows');
		try{
			$result->execute();
			$output = array();
			while ($row = $result->fetch(PDO::FETCH_ASSOC)){
				$output['content'][$row['pid']] = new Post($row['pid']); //Create an array of objects
			}
			$cRows->execute();
	        $nbItems = $cRows->fetch(PDO::FETCH_OBJ)->rows;
			if ($nbItems>($start+$limit)) $output['final'] = $start+$limit;
			else $output['final'] = $nbItems;
			$output['limit'] = $limit;
			$output['nbItems'] = $nbItems;
		}catch (PDOException $e){
			Core::SetMessage($e->getMessage(), 'error');
		}
		return $output;
	}
	
	public function Commit(){
		$sql = $this->DB->prepare("update ".DB_PREFIX."_posts set `title`=:t, `body`=:b, `modified`=:m, `published`=:p where `pid`=:pid");
		$sql->bindParam(':p', $this->published);
		$sql->bindParam(':t', $this->title);
		$sql->bindParam(':b', $this->body);
		$sql->bindparam(':m', $this->modified);
		$sql->bindParam(':pid', $this->pid);
		$sql->execute();
		if ($sql->rowCount() == 1){
			return true;
		}else{
			return false;
		}
	}
}