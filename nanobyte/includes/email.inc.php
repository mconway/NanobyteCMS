<?php
    class Email{
    	
		public function __construct(){
			$this->from = EMAIL_FROM;
			$this->clearAllRecipients();
			$this->subject = EMAIL_SUBJECT;
			$this->body = "";
			$this->smtp = EMAIL_USE_SMTP;
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
			mail(implode(', ',$this->recipients), trim($this->subject), $this->body,implode("\r\n",$this->headers));
		}
		
		public function setHTML($html){
			$this->isHTML = $html;
		}
		
		private function sendSMTP(){ //todo : all failed messages to log if available
			// Open an SMTP connection
			$cp = fsockopen (SMTP_SERVER, SMTP_PORT, &$errno, &$errstr, 1);
			if (!$cp){
				Core::SetMessage("Failed to even make an SMTP connection",'error');
			return false;
			}
			$res=fgets($cp,256);
			if(substr($res,0,3) != "220"){
				Core::SetMessage("Failed to connect",'error');
			return false;
			} 
			
			// Say hello...
			fputs($cp, "HELO ".SMTP_SERVER."\r\n");
			$res=fgets($cp,256);
			if(substr($res,0,3) != "250"){
				Core::SetMessage("Failed to Introduce",'error');
			return false;
			} 
			
			// perform authentication
			fputs($cp, "auth login\r\n");
			$res=fgets($cp,256);
			if(substr($res,0,3) != "334"){
				Core::SetMessage("Failed to Initiate Authentication");
			return false;
			} 
			
			fputs($cp, base64_encode(SMTP_USER)."\r\n");
			$res=fgets($cp,256);
			if(substr($res,0,3) != "334"){ 
				Core::SetMessage("Failed to Provide Username for Authentication");
			}
			
			fputs($cp, base64_encode(Core::ConfDecode(SMTP_PASS))."\r\n");
			$res=fgets($cp,256);
			if(substr($res,0,3) != "235"){
				Core::SetMessage("Failed to Authenticate");
			}
			
			// Mail from...
			fputs($cp, "MAIL FROM: <$from>\r\n");
			$res=fgets($cp,256);
			if(substr($res,0,3) != "250"){
				Core::SetMessage("MAIL FROM failed");
			}
			
			// Rcpt to...
			fputs($cp, "RCPT TO: <$to>\r\n");
			$res=fgets($cp,256);
			if(substr($res,0,3) != "250"){
				Core::SetMessage("RCPT TO failed");
			}
			
			// Data...
			fputs($cp, "DATA\r\n");
			$res=fgets($cp,256);
			if(substr($res,0,3) != "354"){
				Core::SetMessage("DATA failed");
			}
			
			// Send To:, From:, Subject:, other headers, blank line, message, and finish
			// with a period on its own line (for end of message)
			fputs($cp, "To: $to\r\nFrom: $from\r\nSubject: $subject\r\n$headers\r\n\r\n$message\r\n.\r\n");
			$res=fgets($cp,256);
			if(substr($res,0,3) != "250"){
				Core::SetMessage("Message Body Failed");
			}
			
			// ...And time to quit...
			fputs($cp,"QUIT\r\n");
			$res=fgets($cp,256);
			if(substr($res,0,3) != "221"){
				Core::SetMessage("QUIT failed");
			}
		}
    
		private function useSMTP($smtp){
			$this->smtp = $smtp;
		}
	}
?>