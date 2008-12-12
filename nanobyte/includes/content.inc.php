<?php

class Content{
//@params  - an assosiative array of the post data - title, body, published, tags
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
				$this->comments = new Comments($this->pid);
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
}