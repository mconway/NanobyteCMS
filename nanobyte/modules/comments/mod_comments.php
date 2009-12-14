<?php
class Mod_Comments{
	
	private $dbh;
	
	public function install(){
		//Add Page Content type
		$content = new Mod_Content();
		$content->RegisterContentType('Comments');
	}
	
	public static function uninstall(){
		$content = new Mod_Content();
		$content->UnregisterContentType('Comments');
	}
	
	public function __construct($pid=null){
		$this->dbh = DBCreator::GetDBObject();
		if (isset($pid)){
			$query = $this->dbh->prepare("SELECT pid, title, body, username, created FROM ".DB_PREFIX."_content LEFT JOIN ".DB_PREFIX."_user AS user ON author=uid LEFT JOIN ".DB_PREFIX."_content_types ON type=id WHERE parent=:pid");
			$query->execute(array(':pid'=>$pid));
			$this->all = $query->fetchAll(PDO::FETCH_ASSOC);
		}
	}
	
	public function read($limit=15,$start=0){ //Replaces GetPostLst
		$Core = BaseController::getCore();
		$result = $this->dbh->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM ".DB_PREFIX."_content LEFT JOIN ".DB_PREFIX."_content_types ON type=id WHERE name = 'Comments' ORDER BY created DESC LIMIT {$start},{$limit}");
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
		$sql = $this->dbh->prepare("INSERT INTO ".DB_PREFIX."_content SET title=:t, body=:b, author=:a, created=:d, parent=:p, type=(SELECT id FROM ".DB_PREFIX."_content_types WHERE name = 'Comments')");
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
 
class CommentsController extends BaseController{
	
	public static function getList($page=1){
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
	
	public static function commentsForm($pid){
		$Core = BaseController::getCore();
		//Create the form object
		$element_array = array('name'=>'commentform','method'=>'post','action'=>'content/'.$pid.'/comments/add');
		//set form default values
		$element_array['defaults'] = array(
			'pid'=>$pid
		);
		//create form elements
		$element_array['elements'] = array(
			array('type'=>'header','name'=>'','label'=>'Post a new comment'),
			array('type'=>'text', 'name'=>'title', 'label'=>'Title', array('size'=>80, 'maxlength'=>80)),
			array('type'=>'textarea','name'=>'body','label'=>'Body','options'=>array('rows'=>5,'cols'=>60)),
			array('type'=>'hidden','name'=>'pid'),
			array('type'=>'submit', 'name'=>'submit', 'value'=>'Save')
		);
		//apply form prefilters
		$element_array['filters'] = array(
			array('__ALL__', 'trim')
		);
		//$form->applyFilter('__ALL__', 'nl2br');

		//add form rules
		$element_array['rules'] = array(
			array('required','title'),
			array('required','body')
		);
		//If the form has already been submitted - validate the data
		$element_array['callback'] = array(new Mod_Comments(),'Commit');
//		if($form->validate()){
//				$comments = new Comments();
//				$form->process(array($comments,'Commit'));
//				BaseController::Redirect();
//				exit;
//		}
		//send the form to smarty
		$Core->smarty->assign(array(
			'form'=>BaseController::generateForm($element_array),
			'page'=>'comment'
		)); 
	}

	public static function showComments(&$comments,$pid){
		$Core = BaseController::getCore();
		$tmp_array = array();
		foreach($comments->all as $comment){
			$comment['created'] = date('M jS',$comment['created']);
			$Core->smarty->assign('comment',$comment);
			array_push($tmp_array,$Core->smarty->fetch('comment.tpl'));
		}
		CommentsController::CommentsForm($pid);
		array_push($tmp_array,$Core->smarty->fetch('form.tpl'));
		$Core->smarty->assign('comments', $tmp_array);
	}
}

?>
