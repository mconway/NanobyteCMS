<?php
class Comments extends Post{
	private $dbh;
	public function __construct($pid=null){
		$this->dbh = DBCreator::GetDBObject();
		if (isset($pid)){
			$query = $this->dbh->prepare("SELECT comment.cid, comment.title, comment.body, user.username, comment.date FROM ".DB_PREFIX."_comments AS comment LEFT JOIN ".DB_PREFIX."_user AS user ON comment.author=user.uid WHERE comment.pid=:pid");
			$query->bindParam(':pid',$pid);
			$query->execute();
			$this->all = $query->fetchAll(PDO::FETCH_ASSOC);
		}
	}
	
	public function ReadMe($limit=15,$start=0){ //Replaces GetPostLst
		$result = $this->dbh->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM ".DB_PREFIX."_comments ORDER BY date DESC LIMIT {$start},{$limit}");
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
			Core::SetMessage($e->getMessage(), 'error');
		}
		return $output;
	}
	
	public function Commit($params){
		global $user;
		$sql = $this->dbh->prepare("INSERT INTO ".DB_PREFIX."_comments set `pid`=:p, `title`=:t, `body`=:b, `author`=:a, `date`=:d");
		$sql->bindParam(':t', $params['title']);
		$sql->bindParam(':b', $params['body']);
		$sql->bindParam(':p', $params['pid']);
		$sql->bindParam(':a', $user->uid);
		$sql->bindparam(':d', time());
		$sql->execute();
		if ($sql->rowCount() == 1){
			return true;
		}else{
			return false;
		}
	}
}
?>
