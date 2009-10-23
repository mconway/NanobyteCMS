<?php
	/*
	*Copyright (c) 2009, Michael Conway
	*All rights reserved.
	*Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
    *Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
   	*Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
	*Neither the name of the Nanobyte CMS nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
	*THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
	*/
	
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




