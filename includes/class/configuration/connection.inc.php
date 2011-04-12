<?php
class Connection{
	public $type;
	public $server;
	public $database;
	public $userId;
	public $password;
	
	public function __construct(SimpleXMLElement $conn){
		$this->name = (string)$conn['name'];
		$this->type = (string)$conn['type'];
		$this->server = (string)$conn->server;
		$this->database = (string)$conn->database;
		$this->userId = (string)$conn->userid;
		$this->password = (string)$conn->password;
	}
}