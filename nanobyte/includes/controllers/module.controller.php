<?php

/*
 * Created Aug 6th
 */
 
 class ModuleController{

	public static function GetAll(){
		$dirs = glob('modules/*', GLOB_ONLYDIR);
		foreach($dirs as $dir){
			$mod[] = new Module($dir); 
		}
		return $mod;
	}
	
	public static function ListModule(){
		global $smarty;
		//create list
		$modsList = self::GetAll(); //array of objects
		$list = array();
		foreach ($modsList as $module){
			$s = $module->status == 1 ?  'Disable' : 'Enable';
			$options = array();
			$options['image'] = '16';
			$options['class'] = 'action-link';
			$options['id'] = 'mod_'.$module->name;
			$list[] = array(
				//'author'=>$module->conf->author, 
				//'aurl'=> $module->conf->author->attributes()->url,
				//'aemail'=>$module->conf->author->attributes()->email,
				'title'=>$module->conf->title, 
				'version'=>$module->conf->version.'-'.$module->conf->status, 
				'description' => $module->conf->description,
				'enabled'=>"<center><img src='".THEME_PATH."/images/{$module->status}-25.png'/></center>",
				'actions'=> Core::l($s,'admin/module/'.strtolower($s).'/'.$module->name,$options).' | '
				);
				$options['id'] = "";
				$list[count($list)-1]['actions'] .= Core::l('Info','admin/module/details/'.$module->name,$options);
		}
		//create the actions options
		//$actions['General Actions'] = array();
		//$actions['With Selected'] = array( 'editUser'=>'Edit User (1)','delete' => 'Delete User(s)');
		
		// bind the params to smarty
		//$smarty->assign('actions', $actions);
		$smarty->assign('list', $list);
		return $smarty;
	}
	
	public static function UpdateStatus($args,&$jsonObj){
		global $core;
		$module = new Module($args[2]);
		if($module->Commit()){
			$core->EnabledMods();
			$core->SetMessage(strtoupper($module->name). ' has been '.$args[1].'d.','info');
			if ($args[1] == "enable"){
				require_once($module->modpath."Mod_".$module->name.'.php');
				call_user_func(array('Mod_'.$module->name, 'Install'));
			}else{
				call_user_func(array('Mod_'.$module->name, 'Uninstall'));
			}
			$jsonObj->callback = 'nanobyte.changeLink';
			$str = $args[1] == 'enable' ? 'disable' : 'enable';
			$jsonObj->args = $args[1]."|".$str."|mod_".$module->name;
		}else{
			$core->SetMessage("An Error was encountered while trying to {$args[1]}".strtoupper($module->name),'error');
		}
//		UserController::Redirect();
	}
	
	public static function InstallModule($mod){
		$module = new Module($mod);
		call_user_func(array('Mod_'.$mod, 'Install'));
	}
	
	public static function GetBlocks(){
		global $smarty;
		$enabled = Module::GetBlocks(true);
		foreach($enabled as $block){
			$position = explode("_",$block['position']);
			$blockobj = call_user_func(array('Mod_'.$block['providedby'], $block['name'].'_Block'));
			if (isset($blocks[$position[0]])){
				$blocks[$position[0]] .= $smarty->fetch($blockobj->template);
			}else{
				$blocks[$position[0]] = $smarty->fetch($blockobj->template);
			}
		}
		$smarty->assign('blocks', $blocks);
	}
	
	public static function ListBlock(){
		global $smarty;
		$blocks = Module::GetBlocks();
		$smarty->assign('list',$blocks);
		$smarty->assign('self','admin/blocks');
	}
	
	public static function Admin(&$argsArray){
		list($args,$ajax,$smarty,$user,$jsonObj) = $argsArray;
		$tabs = array();
		array_push($tabs, Core::l('Modules','admin/module/list'));
		array_push($tabs, Core::l('Blocks','admin/block/list'));
		$smarty->assign('tabs',$tabs);
		if($ajax){$jsonObj->tabs = $smarty->fetch('tabs.tpl');}
		switch($args[1]){ 
			//We call the same function for Disable and Enable.
			case 'enable':  
			case 'disable':
				self::UpdateStatus($args,$jsonObj);
				break;
			// Default is to display the module list
			case 'list':
				$func = 'List'.$args[0];
				self::$func();
				$content = $smarty->fetch('list.tpl');
				break;
			case 'details':
				$module = new Module($args[2]);
				$content = <<<EOF
				Author: {$module->conf->author}<br/>
				URL: {$module->conf->author->attributes()->url}<br/>
				Email: {$module->conf->author->attributes()->email}<br/>
EOF;
				$jsonObj->callback = 'Dialog';
				$jsonObj->title = 'Module Information for: '.ucfirst($args[2]);
				break;

		}
		$jsonObj->content = $content;
	}
 
 }
