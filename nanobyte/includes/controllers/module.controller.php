<?php

/**
 * Created Aug 6th
 */
 
 class ModuleController{

	public static function admin(&$argsArray){
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
				$smarty->assign(self::$func($args[1]));
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
			case 'down':
			case 'up':
			//$args[1] = up/down $args[2] = id $args[3] = weight
			$module = new Module();
			if($module->moveBlock($args)){
				$jsonObj->callback = 'nanobyte.moveRow';
				$jsonObj->args = 'block_'.$args[2].'|'.$args[1];
			}
		}
		$jsonObj->content = $content;
	}

	public static function getAll(){
		$dirs = glob('modules/*', GLOB_ONLYDIR);
		foreach($dirs as $dir){
			$mod[] = new Module($dir); 
		}
		return $mod;
	}
	
	public static function getBlocks($filter=null){
		global $smarty;
		$enabled = Module::GetBlocks($filter);
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
	
	public static function installModule($mod){
		$module = new Module($mod);
		call_user_func(array('Mod_'.$mod, 'Install'));
	}
	
	public static function listBlock(){
		$blocks = Module::GetBlocks();
		
		$options['image'] = '16';
		$options['class'] = 'action-link noloader';
		foreach($blocks as &$block){
			$options['id'] = 'a_block_'.$block['id'];
			$s = $block['status'] == 1 ?  'Disable' : 'Enable';
			$options['title'] = $s;
			$block['actions'] = Core::l($s,'admin/block/'.strtolower($s).'/'.$block['id'],$options)
				." | ".Core::l('Up','admin/block/up/'.$block['id']."/".($block['weight']-1) ,array_merge($options,array('id'=>'','title'=>'Move up')))
				." | ".Core::l('Down','admin/block/down/'.$block['id']."/".($block['weight']+1) ,array_merge($options,array('id'=>'','title'=>'Move down')));
			$block['status'] = "<center><img src='".THEME_PATH."/images/{$block['status']}-25.png'/></center>";
		}
		
		$smartyVars = array(
			'list'=>$blocks,
			'formAction'=>'admin/blocks',
			'tableclass' => 'sortable'
		);
		return $smartyVars;
	}
	
	public static function listModule($page){
		//create list
		$modsList = self::GetAll(); //array of objects
		$list = array();
		foreach ($modsList as $module){
			$s = $module->status == 1 ?  'Disable' : 'Enable';
			$options = array();
			$options['image'] = '16';
			$options['class'] = 'action-link';
			$options['id'] = 'mod_'.$module->name;
			$options['title'] = $s;
			$list[] = array(
				'title'=>$module->conf->title, 
				'version'=>$module->conf->version.'-'.$module->conf->status, 
				'description' => $module->conf->description,
				'enabled'=>"<center><img src='".THEME_PATH."/images/{$module->status}-25.png'/></center>",
				'actions'=> Core::l($s,'admin/module/'.strtolower($s).'/'.$module->name,$options).' | '
			);
			$options['id'] = "";
			$options['title'] = 'Info';
			$list[count($list)-1]['actions'] .= Core::l('Info','admin/module/details/'.$module->name,$options);
		}
		$smartyVars = array(
			'pager'=>BaseController::Paginate(LIMIT, count($modsList), 'admin/module/list/', $page),
			'list'=>$list,
		);
		
		return $smartyVars;
	}
	
	public static function updateStatus($args,&$jsonObj){
		global $core;
		if($args[0]=='block'){
			$module = new Module();
			if($module->updateBlockStatus($args[2])){
				$jsonObj->callback = 'nanobyte.changeLink';
				$str = $args[1] == 'enable' ? 'disable' : 'enable';
				$jsonObj->args = $args[1]."|".$str."|a_block_".$args[2]."|status";
			}
		}else{
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
				$jsonObj->args = $args[1]."|".$str."|mod_".$module->name."|enabled";
			}else{
				$core->SetMessage("An Error was encountered while trying to {$args[1]}".strtoupper($module->name),'error');
			}
		}
//		UserController::Redirect();
	}
	
 }
