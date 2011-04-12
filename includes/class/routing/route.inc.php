<?php
class Route{
	private $_args;
	private $_controller;
	private $_page;
	
	public function __construct(){	}
	
	public function setArgs(array $args){
		$this->_args = $args;
	}
	
	public function setController($controller){
		$this->_controller = $controller;
	}
	
	public function setPage($page){
		$this->_page = $page;
	}
}