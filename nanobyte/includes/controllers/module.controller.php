<?php

/**
 * Created Aug 6th
 */
 
 class ModuleController extends BaseController{

	public static function admin(){
		$Core = parent::getCore();
		
		$tabs = array();
		$content = '';
		array_push($tabs, Core::l('Modules','admin/module/list'));
		array_push($tabs, Core::l('Blocks','admin/block/list'));
		$Core->smarty->assign('tabs',$tabs);
		if($Core->ajax){$Core->json_obj->tabs = $Core->smarty->fetch('tabs.tpl');}
		if(isset($Core->args[1])){
			switch($Core->args[1]){ 
				//We call the same function for Disable and Enable.
				case 'enable':  
				case 'disable':
					self::UpdateStatus($Core->args,$Core->json_obj);
					break;
				// Default is to display the module list
				case 'list':
					$func = 'List'.$Core->args[0];
					$Core->smarty->assign(self::$func($Core->args[1]));
					$content = $Core->smarty->fetch('list.tpl');
					break;
				case 'details':
					$module = new Module($Core->args[2]);
					$content = <<<EOF
					Author: {$module->conf->author}<br/>
					URL: {$module->conf->author->attributes()->url}<br/>
					Email: {$module->conf->author->attributes()->email}<br/>
EOF;
					$Core->json_obj->callback = 'Dialog';
					$Core->json_obj->title = 'Module Information for: '.ucfirst($Core->args[2]);
					break;
				case 'down':
				case 'up':
				//$Core->args[1] = up/down $Core->args[2] = id $Core->args[3] = weight
				$module = new Module();
				if($module->moveBlock($Core->args)){
					$Core->json_obj->callback = 'nanobyte.moveRow';
					$Core->json_obj->args = 'block_'.$Core->args[2].'|'.$Core->args[1];
				}
			}
			$Core->json_obj->content = $content;
		}
	}

	public static function getAll(){
		$dirs = glob('modules/*', GLOB_ONLYDIR);
		foreach($dirs as $dir){
			$mod[] = new Module($dir); 
		}
		return $mod;
	}
	
	public static function getBlocks($filter=null){
		$Core = parent::getCore();
		$enabled = Module::GetBlocks($filter);
		foreach($enabled as $block){
			$position = explode("_",$block['position']);
			$module = 'Mod_'.$block['providedby'];
			$tmp = new $module;
			$blockobj = call_user_func(array('Mod_'.$block['providedby'], $block['name'].'_Block'));
			$Core->smarty->assign(array(
				'block_title' => $blockobj->title,
				'block_body' => $Core->smarty->fetch($blockobj->template)
			));
			if (isset($blocks[$position[0]])){
				$blocks[$position[0]] .= $Core->smarty->fetch('block.tpl');
			}else{
				$blocks[$position[0]] = $Core->smarty->fetch('block.tpl');
			}
		}
		$Core->smarty->assign('blocks', $blocks);
	}
	
	public static function installModule(&$module){
		$Core = parent::getCore();
		require_once($module->modpath."Mod_".$module->name.'.php');
		$mod_class = 'Mod_'.$module->name;
		$m = new $mod_class($Core);
		$m->install();

		if(isset($m->setup['menus'])){
			if(!isset($m->setup['menus']['menu'])){
				foreach($m->setup['menus'] as $menu){
					$menu_obj = new Menu($menu['menu']);
					$menu_obj->data = array($menu);
					$menu_obj->commit($menu_obj->menu[0]['mid']);
				}
			}else{
				$menu_obj = new Menu($m->setup['menus']['menu']);
				$menu_obj->data = array($m->setup['menus']);
				$menu_obj->commit($menu_obj->menu[0]['mid']);
			}
		}
		
		if(isset($m->setup['permissions'])){
			$perms = new Perms();
			$perms->createPerm($m->setup['permissions'],$module->name);
		}
	}
	
	public static function listBlock(){
		$Core = parent::getCore();
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
		
		$Core->smartyVars = array(
			'list'=>$blocks,
			'formAction'=>'admin/blocks',
			'tableclass' => 'sortable'
		);
		return $Core->smartyVars;
	}
	
	public static function listModule($page){
		//create list
		$modsList = self::GetAll(); //array of objects
		$list = array();
		foreach ($modsList as $module){
			$s = $module->status == 1 ?  'Disable' : 'Enable';
			$options = array(
				'image' => '16',
				'class' => 'action-link noloader',
				'id' => 'mod_'.$module->name,
				'title' => $s
			);
			$list[] = array(
				'title'=>$module->conf->title, 
				'version'=>$module->conf->version.'-'.$module->conf->status, 
				'description' => $module->conf->description,
				'enabled'=>"<center><img src='".THEME_PATH."/images/{$module->status}-25.png'/></center>",
				'actions'=> Core::l($s,'admin/module/'.strtolower($s).'/'.$module->name,$options).' | '
			);
			$list[count($list)-1]['actions'] .= Core::l('Info','admin/module/details/'.$module->name,array('id'=>'','title'=>'Info', 'class'=>'action-link','image'=>'16'));
		}
		$smarty_vars = array(
			'pager'=>parent::Paginate(LIMIT, count($modsList), 'admin/module/list/', $page),
			'list'=>$list,
		);
		
		return $smarty_vars;
	}
	
	public static function uninstallModule(&$module){
		$module->disableBlocks();
		$mod_class = 'Mod_'.$module->name;
		$m = new $mod_class($Core);
		$m->uninstall();
	}
	
	public static function updateStatus(){
		$Core = parent::getCore();
		if($Core->args[0]=='block'){
			$module = new Module();
			if($module->updateBlockStatus($Core->args[2])){
				$Core->json_obj->callback = 'nanobyte.changeLink';
				$str = $Core->args[1] == 'enable' ? 'disable' : 'enable';
				$Core->json_obj->args = $Core->args[1]."|".$str."|a_block_".$Core->args[2]."|status";
			}
		}else{
			$module = new Module($Core->args[2]);
			if($module->Commit()){
				$Core->enabledMods();
				$Core->SetMessage(strtoupper($module->name). ' has been '.$Core->args[1].'d.','info');
				if ($Core->args[1] == "enable"){
					self::InstallModule($module);
				}else{
					self::UninstallModule($module);
					call_user_func(array('Mod_'.$module->name, 'Uninstall'));
				}
				$Core->json_obj->callback = 'nanobyte.changeLink';
				$str = $Core->args[1] == 'enable' ? 'disable' : 'enable';
				$Core->json_obj->args = $Core->args[1]."|".$str."|mod_".$module->name."|enabled";
			}else{
				$Core->SetMessage("An Error was encountered while trying to {$Core->args[1]}".strtoupper($module->name),'error');
			}
		}
//		UserController::Redirect();
	}
	
 }
