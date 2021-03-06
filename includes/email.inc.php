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
	
    class Email{
    	private $html;
		private $body;
		private $dbh;
		
		public function __construct(){
			$this->dbh = DbCreator::GetDBObject();
			$this->headers = array();
			$this->from = EMAIL_FROM;
			$this->clearAllRecipients();
			$this->subject = EMAIL_SUBJECT;
			$this->body = "";
			$this->smtp_server = SMTP_SERVER;
			$this->smtp_port = SMTP_PORT;
			$this->smtp_auth = SMTP_AUTH;
			$this->isHTML = EMAIL_IS_HTML;
			$this->wordwrap = 70;
			$this->addHeader('X-Mailer: Nanobyte CMS Mailer');
		}
		
		public function addHeader($header){
			array_push($this->headers, $header);
		}
		
		public function setFrom($from){
			$this->from = $from;
		}
		
		public function addRecipient($recipient){
			array_push($this->recipients,$recipient);
		}
		
		public function addCC($cc){
			array_push($recipient,$this->cc);
		}
		
		public function addBCC($bcc){
			array_push($recipient,$this->bcc);
		}
		
		public function setBody($body){
			$this->body = wordwrap(trim($body),$this->wordwrap);
//			if($this->isHTML)
//				$this->body = nl2br($this->body);
		}
		
		public function clearAllRecipients(){
			$this->recipients = array();
			$this->cc = array();
			$this->bcc = array();
		}
		
		public function setSubject($subject){
			$this->subject = $subject;
		}
		
		public function sendMessage(){
			if($this->html){
				$this->addHeader("Content-type: text/html; charset=iso-8859-1");
			}else{
				$this->addHeader("Content-type: text/plain; charset=iso-8859-1");
			}
			$this->addHeader("From: ".EMAIL_FROM);
			if(!empty($this->cc)){
				$this->addHeader("CC: ".implode(', ',$this->cc));
			}
			if(!empty($this->bcc)){
				$this->addHeader("BCC: ".implode(', ',$this->bcc));
			}
			
			//maybe change this:
			$this->recipients = implode(', ',$this->recipients);
			$this->subject = trim($this->subject);
			$this->headers = implode("\r\n",$this->headers);
			
			$this->sendSMTP();
//			mail(implode(', ',$this->recipients), trim($this->subject), $this->body,implode("\r\n",$this->headers));
		}
		
		public function setHTML($html){
			$this->isHTML = $html;
		}
		
		private function sendSMTP(){ //todo : all failed messages to log if available
			// Open an SMTP connection
			$this->smtp_socket = fsockopen ($this->smtp_server, $this->smtp_port, $errno, $errstr, 1);
			if (!$this->smtp_socket){
				Core::SetMessage("Failed to even make an SMTP connection",'error');
				return false;
			}
			$res=fgets($this->smtp_socket,256);
			if(substr($res,0,3) != "220"){
				Core::SetMessage("Failed to connect",'error');
			return false;
			} 
			
			// Say hello...
			fputs($this->smtp_socket, "HELO ".$this->smtp_server."\r\n");
			$res=fgets($this->smtp_socket,256);
			if(substr($res,0,3) != "250"){
				Core::SetMessage("Failed to Introduce",'error');
				return false;
			} 
			
			if($this->smtp_auth=='1'){
				// perform authentication
				fputs($this->smtp_socket, "auth login\r\n");
				$res=fgets($this->smtp_socket,256);
				if(substr($res,0,3) != "334"){
					Core::SetMessage("Failed to Initiate Authentication");
					return false;
				} 
				
				fputs($this->smtp_socket, base64_encode(Core::DecodeConfParams(SMTP_USER))."\r\n");
				$res=fgets($this->smtp_socket,256);
				if(substr($res,0,3) != "334"){ 
					Core::SetMessage("Failed to Provide Username for Authentication");
				}
				
				fputs($this->smtp_socket, base64_encode(Core::DecodeConfParams(SMTP_PASS))."\r\n");
				$res=fgets($this->smtp_socket,256);
				if(substr($res,0,3) != "235"){
					Core::SetMessage("Failed to Authenticate");
				}
			}
			
			// Mail from...
			fputs($this->smtp_socket, "MAIL FROM: <$this->from>\r\n");
			$res=fgets($this->smtp_socket,256);
			if(substr($res,0,3) != "250"){
				Core::SetMessage("MAIL FROM failed");
			}
			
			// Rcpt to...
			fputs($this->smtp_socket, "RCPT TO: <$this->recipients>\r\n");
			$res=fgets($this->smtp_socket,256);
			if(substr($res,0,3) != "250"){
				Core::SetMessage("RCPT TO failed");
			}
			
			// Data...
			fputs($this->smtp_socket, "DATA\r\n");
			$res=fgets($this->smtp_socket,256);
			if(substr($res,0,3) != "354"){
				Core::SetMessage("DATA failed");
			}
			
			// Send To:, From:, Subject:, other headers, blank line, message, and finish
			// with a period on its own line (for end of message)
			fputs($this->smtp_socket, "To: $this->recipients\r\nFrom: $this->from\r\nSubject: $this->subject\r\n$this->headers\r\n\r\n$this->body\r\n.\r\n");
			$res=fgets($this->smtp_socket,256);
			if(substr($res,0,3) != "250"){
				Core::SetMessage("Message Body Failed");
			}
			
			// ...And time to quit...
			fputs($this->smtp_socket,"QUIT\r\n");
			$res=fgets($this->smtp_socket,256);
			if(substr($res,0,3) != "221"){
				Core::SetMessage("QUIT failed");
			}
		}
    
		private function useSMTP($smtp){
			$this->smtp = $smtp;
		}
	
		public function getEmailData($function){
			$query = $this->dbh->prepare("SELECT * FROM ".DB_PREFIX."_email WHERE function=:function");
			$query->execute(array(':function'=>$function));
			return $query->fetch(PDO::FETCH_ASSOC);
		}
	
		public function setEmailData($data){
			$bind = array(':sub'=>$data['subject'],':body'=>$data['body'],':func'=>$data['function']);
			if(isset($data['id']) && !empty($data['id'])){
				$query = $this->dbh->prepare('UPDATE '.DB_PREFIX.'_email SET subject=:sub, body=:body, function=:func WHERE id=:id');
				$bind[':id'] = $data['id'];
			}else{
				$query = $this->dbh->prepare('INSERT INTO '.DB_PREFIX.'_email SET subject=:sub, body=:body, function=:func');
			}
			$query->execute($bind);
			if($query->rowCount()==1){
				return true;
			}else{
				return false;
			}
		}
	
	}
?>