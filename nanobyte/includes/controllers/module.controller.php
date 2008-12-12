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
	
	public static function ListModules(){
		global $smarty;
		//create list
		$modsList = self::GetAll(); //array of objects
		$list = array();
		foreach ($modsList as $module){
			$module->status == 1 ? $s = 'Disable' : $s = 'Enable';
			$options['image'] = '16';
			$list[] = array(
				//'author'=>$module->conf->author, 
				//'aurl'=> $module->conf->author->attributes()->url,
				//'aemail'=>$module->conf->author->attributes()->email,
				'title'=>$module->conf->title, 
				'version'=>$module->conf->version.'-'.$module->conf->status, 
				//'status'=>$module->conf->status,
				//'site'=>$module->conf->site,
				'description' => $module->conf->description,
				'enabled'=>$module->status,
				'actions'=> Core::l($s,'admin/modules/'.strtolower($s).'/'.$module->name,$options).' | '.Core::l('Info','admin/modules/details/'.$module->name,$options)
				);
		}
		//create the actions options
		//$actions['General Actions'] = array();
		//$actions['With Selected'] = array( 'editUser'=>'Edit User (1)','delete' => 'Delete User(s)');
		
		// bind the params to smarty
		//$smarty->assign('actions', $actions);
		$smarty->assign('list', $list);
		return $smarty;
	}
	
	public static function UpdateStatus($mod, $action){
		$module = new Module($mod);
		$module->Commit();
		Core::SetMessage(strtoupper($module->name). ' has been '.$action.'d.','info');
		if ($action =="enable"){
			require_once($module->modpath.$module->name.'.php');
			call_user_func(array('Mod_'.$mod, 'Install'));
		}
		UserController::Redirect();
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
	
	public static function ListBlocks(){
		global $smarty;
		$blocks = Module::GetBlocks();
		$smarty->assign('list',$blocks);
		$smarty->assign('self','admin/blocks');
	}
 }
