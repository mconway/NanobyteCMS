<?php

class Error{
	public $error_code;
	public $explanation;
	public $redirect_to;
	function __construct($status,$page=null){
		$this->page_redirected_from = $page;  // this is especially useful with error 404 to indicate the missing page.
		$this->server_url = "http://" . $_SERVER["SERVER_NAME"] . "/";
		$this->redirect_url = 'index.php';
		$this->redirect_url_array = parse_url($this->redirect_url);
		$this->end_of_path = strrchr($this->redirect_url_array["path"], "/");
		switch($status)
		{
			# "400 - Bad Request"
			case 400:
			$this->error_code = "400 - Bad Request";
			$this->explanation = "The syntax of the URL submitted by your browser could not be understood.  Please verify the address and try again.";
			$this->redirect_to = "";
			break;
		
			# "401 - Unauthorized"
			case 401:
			$this->error_code = "401 - Unauthorized";
			$this->explanation = "This section requires a password or is otherwise protected.  If you feel you have reached this page in error, please return to the login page and try again, or contact the webmaster if you continue to have problems.";
			$this->redirect_to = "";
			break;
		
			# "403 - Forbidden"
			case 403:
			$this->error_code = "403 - Forbidden";
			$this->explanation = "This section requires a password or is otherwise protected.  If you feel you have reached this page in error, please return to the login page and try again, or contact the webmaster if you continue to have problems.";
			$this->redirect_to = "";
			break;
		
			# "404 - Not Found"
			case 404:
			$this->error_code = "404 - Not Found";
			$this->explanation = "The requested resource '" . $this->page_redirected_from . "' could not be found on this server.  Please verify the address and try again.";
			$this->redirect_to = $this->server_url . $this->end_of_path;
			break;
		
			# "500 - Internal Server Error"
			case 500:
			$this->error_code = "500 - Internal Server Error";
			$this->explanation = "The server experienced an unexpected error.  Please verify the address and try again.";
			$this->redirect_to = "";
			break;
		}
	}
}




