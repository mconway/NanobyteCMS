<?php
class Configuration implements IConfiguration{
	private $_xml;
	public $connections = array();
	
	public function __construct($fileName){
		$this->_xml = simplexml_load_file($fileName);
		$this->setConnections();
	}
	
	public function __get($name){
		$settings = $this->_xml->xpath("settings/setting[@name='{$name}']");
		if($settings !== false){
			return (string)$settings[0];
		}
	}
	
	public function setConnections(){
		if(!class_exists("Connection"))
			throw new Exception("Unable to load connection class!");
		foreach($this->_xml->connections->connection as $conn)
			$this->connections[(string)$conn['name']] = new Connection($conn);
	}
	
	public function writeConfiguration(){
		throw new Exception("Not Implemented");
		file_put_contents('config.xml',$this->_xml->asXML());
	}
}