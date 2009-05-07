<?php
class Mod_Comments{
	
	private $dbh;
	
	public function Install(){
		//Add Page Content type
		$content = new $Mod_Content();
		$content->RegisterContentType('Comments');
	}
	
	public static function Uninstall(){
		$content = new self();
		$content->UnregisterContentType('Comments');
	}
	
	public function __construct($pid=null){
		$this->dbh = DBCreator::GetDBObject();
		if (isset($pid)){
			$query = $this->dbh->prepare("SELECT comment.cid, comment.title, comment.body, user.username, comment.date FROM ".DB_PREFIX."_comments AS comment LEFT JOIN ".DB_PREFIX."_user AS user ON comment.author=user.uid WHERE comment.pid=:pid");
			$query->bindParam(':pid',$pid);
			$query->execute();
			$this->all = $query->fetchAll(PDO::FETCH_ASSOC);
		}
	}
	
	public function Read($limit=15,$start=0){ //Replaces GetPostLst
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

class CommentsController extends BaseController{
	
	public static function GetList($page=1){
		global $smarty;
		//create the list
		$start = BaseController::GetStart($page,15);
		$comments = new Comments();
		$list = $comments->ReadMe(15,$start); //array of objects with a limit of 15 per page.
		$options['image'] = '16';
		foreach ($list['content'] as $comment){
			$theList[] = array(
				'id'=>$comment['cid'],
				'title'=>$comment['title'], 
				'created'=>date('Y-M-D',$comment['date']),
				'author'=>$comment['author'],
				'actions'=>Core::l('view','posts/'.$comment['pid'],$options).' | '.Core::l('delete','admin/content/cdelete/'.$comment['pid'],$options)
				);
		}
		$options['image'] = '24';
		//create the actions options and bind the params to smarty
		$smarty->assign('pager',BaseController::Paginate($list['limit'], $list['nbItems'], 'admin/content/', $page));
		$smarty->assign('cb',true);
		$smarty->assign('self','admin/content');
		$smarty->assign('actions', array('delete' => 'Delete'));
		$smarty->assign('extra', 'With Selected: {html_options name=actions options=$actions}<input type="submit" name="submitaction" value="Go!"/>');
		$smarty->assign('list', $theList);
	}
	
	public static function CommentsForm($pid){
		global $smarty;
		//Create the form object
		$form = new HTML_QuickForm('commentform','commentform','posts/'.$pid.'/comments/add');
		//set form default values
		$form->setdefaults(array(
			'pid'=>$pid
		));
		//create form elements
		$form->addElement('header','','Post a new comment');
		$form->addElement('text', 'title', 'Title', array('size'=>80, 'maxlength'=>80));
		$form->addElement('textarea','body','Body',array('rows'=>20,'cols'=>60));
		
		$form->addElement('hidden','pid');
		$form->addElement('submit', 'save', 'Save');
		//apply form prefilters
		$form->applyFilter('__ALL__', 'trim');
		//$form->applyFilter('__ALL__', 'nl2br');

		//add form rules
		$form->addRule('title', 'A Title is required.', 'required');
		$form->addRule('body', 'Body text is required.', 'required');
		//If the form has already been submitted - validate the data
		if($form->validate()){
				$comments = new Comments();
				$form->process(array($comments,'Commit'));
				BaseController::Redirect();
				exit;
		}
		//send the form to smarty
		$smarty->assign('form', $form->toArray()); 
	}
}

?>
