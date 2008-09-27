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
	
	public static function ListMods(){
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
		//UserController::Redirect();
	}
	
 }
