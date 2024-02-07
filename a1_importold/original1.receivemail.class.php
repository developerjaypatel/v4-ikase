<?php
// Main ReciveMail Class File - Version 1.1 (02-06-2009)
/*
 * File: recivemail.class.php
 * Description: Reciving mail With Attechment
 * Version: 1.1
 * Created: 01-03-2006
 * Modified: 02-06-2009
 * Author: Mitul Koradia
 * Email: mitulkoradia@gmail.com
 * Cell : +91 9825273322
 */
 
/***************** Changes *********************
*
* 1) Added feature to retrive embedded attachment - Changes provided by. Antti <anttiantti83@gmail.com>
* 2) Added SSL Supported mailbox.
*
**************************************************/

class receiveMail
{
	var $server='';
	var $username='';
	var $password='';
	
	var $marubox='';					
	var $output = '';
	var $email='';			
	
	function receiveMail($username,$password,$EmailAddress,$mailserver='localhost',$servertype='pop3',$port='110',$ssl = false, $output = '', $certificate = "N" ) { //Construction
	
		if($servertype=='imap') {
			//$imap = imap_open("{outlook.office365.com:993/imap/ssl}", "ayk@workinjurygroup.com", "Workinjury777");
			if($port=='') {
				$port='143'; 
			}
			$strConnect='{'.$mailserver.':'.$port. "/imap" . ($ssl ? "/ssl" : ""). '}INBOX'; 
			$this->username			=	$EmailAddress;
		} else {
			$strConnect='{'.$mailserver.':'.$port. '/pop3'.($ssl ? "/ssl" : "").'}INBOX'; 
			
			if ($certificate == "Y") {
				$strConnect='{'.$mailserver.':'.$port. '/pop3/novalidate-cert'.($ssl ? "/ssl" : "").'}INBOX'; 
			}
			$this->username			=	$username;
		}
		
		
		$this->server			=	$strConnect;
		$this->password			=	$password;
		$this->email			=	$EmailAddress;
		$this->output			=	$output;
		
		if ($_SERVER['REMOTE_ADDR']=='71.119.40.148') {
		//	die(print_r($this));
		}
	}
	function connect() //Connect To the Mail Box
	{
		$this->marubox=@imap_open($this->server,$this->username,$this->password);
		
		//try no cert
		/*
		if(!$this->marubox) {
			//die("no cert");
			$this->server = str_replace("pop3}INBOX", "pop3/novalidate-cert}", $this->server);
			$this->marubox=@imap_open($this->server,$this->username,$this->password);
		}
		*/
		if ($this->marubox) {
            // call this to avoid the mailbox is empty error message
            if (imap_num_msg($this->marubox) == 0) {
                $errors = imap_errors();
			}
            return TRUE;
        }
        // imap_errors() will contain the list of real errors
        return FALSE;
		//if failed altogether
		if(!$this->marubox) {
			if ($this->output=="") {
				//echo "Error: Connecting to mail server";
				echo '{"error":{"text":"Error: Connecting to mail server"}}'; 
				exit;
			} else {
				echo '{"error":{"text":"Error: Connecting to mail server"}}'; 
				exit;
			}
		}
		
		//die("connected");
	}
	function getHeaders($mid, $attach = "") // Get Header info
	{
		$mail_details = array();
		
		if(!$this->marubox) {
			return $mail_details;
		}
		$mail_header=imap_header($this->marubox,$mid);
		
		$errs = imap_errors();
		
		
		$sender=$mail_header->from[0];
		$sender_replyto = "";
		if (isset($mail_header->reply_to[0])) {
			$sender_replyto = $mail_header->reply_to[0];
		}
		
		if (!isset($mail_header->message_id)) {
			return $mail_details;
		}
		
		//if(strtolower($sender->mailbox)!='mailer-daemon' && strtolower($sender->mailbox)!='postmaster') {
		$to_name_oth = "";
		if (isset($sender_replyto->personal)) {
			$to_name_oth = $sender_replyto->personal;
			if ($to_name_oth!="") {
				$elements = imap_mime_header_decode($to_name_oth);
				if (count($elements) > 0) {
					$to_name_oth = $elements[0]->text;
				} 
			}
		}
		//die(strtolower($sender->mailbox).'@'.$sender->host);
		$from_mail_details = strtolower($sender->mailbox).'@'.$sender->host;
		if (isset($sender->personal)) {
			$fromName_mail_details = $to_name_oth;
		} else {
			$fromName_mail_details = "";
		}
		
		if(isset($sender_replyto->mailbox) && isset($sender_replyto->host)) {
			$toOth_mail_details = strtolower($sender_replyto->mailbox).'@'.$sender_replyto->host;
		} else {
			$toOth_mail_details = "";
		}
		$date_mail_details = $mail_header->date;
		
		$subject_mail_details = "";
		$subject_mail_details = "";
		if (isset($mail_header->subject)) {
			$subject = $mail_header->subject;
			if ($subject!="") {
				$elements = imap_mime_header_decode($subject);
				if (count($elements) > 0) {
					$subject_mail_details = $elements[0]->text;
				} 
			}
			//$subject_mail_details = $mail_header->subject;
		}
		 
		$to_mail_details = "";
		if (isset($mail_header->toaddress)) {
			$to_mail_details = strtolower($mail_header->toaddress);
			if ($to_mail_details!="") {
				$elements = imap_mime_header_decode($to_mail_details);
				
				if (count($elements) > 0) {
					$to_mail_details = $elements[count($elements) - 1]->text;
				}
				$to_mail_details = str_replace("<", "", $to_mail_details);
				$to_mail_details = str_replace(">", "", $to_mail_details);
			}
		} 
		//$to_mail_details = strtolower($mail_header->toaddress);
		$message_id_mail_details = $mail_header->message_id; 
		$result = imap_fetch_overview($this->marubox,$mid . ":" . $mid,0);
		
		$mail_details = array(
				'from'=>$from_mail_details,
				'fromName'=>$fromName_mail_details,
				'toOth'=>$toOth_mail_details,
				'to_name_othOth'=>$to_name_oth,
				'date'=>$date_mail_details,
				'subject'=>$subject_mail_details,
				'to'=>$to_mail_details,
				'attachments'=>$attach,
				'id'=>$mid,
				'message_id'=>$message_id_mail_details,
				'size'=>$result[0]->size
		);
		/*
		if ($mail_header->message_id == '<9277892604.SIM_F197084F01A1@kustomweb.com>') {
			//die(print_r($mail_header));
			die(print_r($mail_details));
		}
		*/
		//}
		return $mail_details;
	}
	function get_mime_type(&$structure) //Get Mime type Internal Private Use
	{ 
		$primary_mime_type = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER"); 
		
		if($structure->subtype) { 
			return $primary_mime_type[(int) $structure->type] . '/' . $structure->subtype; 
		} 
		return "TEXT/PLAIN"; 
	} 
	function get_part($stream, $msg_number, $mime_type, $structure = false, $part_number = false) //Get Part Of Message Internal Private Use
	{ 
		if(!$structure) { 
			$structure = imap_fetchstructure($stream, $msg_number); 
		} 
		if($structure) { 
			if($mime_type == $this->get_mime_type($structure))
			{ 
				if(!$part_number) 
				{ 
					$part_number = "1"; 
				} 
				$text = imap_fetchbody($stream, $msg_number, $part_number); 
				if($structure->encoding == 3) 
				{ 
					return imap_base64($text); 
				} 
				else if($structure->encoding == 4) 
				{ 
					return imap_qprint($text); 
				} 
				else
				{ 
					return $text; 
				} 
			} 
			if($structure->type == 1) /* multipart */ 
			{ 
				while(list($index, $sub_structure) = each($structure->parts))
				{ 
					$prefix = "";
					if($part_number)
					{ 
						$prefix = $part_number . '.'; 
					} 
					$data = $this->get_part($stream, $msg_number, $mime_type, $sub_structure, $prefix . ($index + 1)); 
					if($data)
					{ 
						return $data; 
					} 
				} 
			} 
		} 
		return false; 
	} 
	function getTotalMails() //Get Total Number off Unread Email In Mailbox
	{
		if(!$this->marubox) {
			return false;
		}
		$message_count = imap_num_msg($this->marubox);
		//$headers=imap_headers($this->marubox);
		//$errs = imap_errors();
		//return count($headers);
		return $message_count;
	}
	function ListAttach($mid, $customer_id, $arrAcceptable = array(), $blnDownload = false, $specific_file = "") // Get Atteced File from Mail
	{
		if(!$this->marubox)
			return false;
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$attach_time = $time;
		
		$struckture = imap_fetchstructure($this->marubox,$mid);
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$attached_time = $time;
		$total_attach_time = round(($attached_time - $attach_time), 4);
		
		//die(print_r($struckture));
		//die("imap_fetchstructure: " . $total_attach_time );
		$ar = "";
		if(isset($struckture->parts)) {
			//die(print_r($struckture->parts));
			foreach($struckture->parts as $key => $value) {
				$enc = $struckture->parts[$key]->encoding;
				if($struckture->parts[$key]->ifdparameters) {	
					//die(print_r($struckture->parts[$key]));
					$name = $struckture->parts[$key]->dparameters[0]->value;
					
					$arrFilename = explode(".", $name);
					$extension = $arrFilename[count($arrFilename) - 1];
					$extension = strtolower($extension);
					
					if (count($arrAcceptable) > 0) {
						if (!in_array($extension, $arrAcceptable)) {
							//not acceptable, get out
							continue;
						}
					}
					$ar = $ar . $name . ", ";
				}
				// Support for embedded attachments starts here
				//if($struckture->parts[$key]->parts)
				if(isset($struckture->parts[$key]->parts)) {
					foreach($struckture->parts[$key]->parts as $keyb => $valueb) {
						$enc = $struckture->parts[$key]->parts[$keyb]->encoding;
						if($struckture->parts[$key]->parts[$keyb]->ifdparameters) {
							$name = $struckture->parts[$key]->parts[$keyb]->dparameters[0]->value;
							$ar = $ar . $name . ", ";
						}
					}
				}				
			}
		}
		$ar = substr($ar,0,(strlen($ar)-1));
		
		$errs = imap_errors();
		
		return $ar;
	}
	function GetAttach($mid,$path,$arrAcceptable = array(), $blnDownload = false, $specific_file = "") // Get Atteced File from Mail
	{
		if(!$this->marubox)
			return false;
		/*
		$struct = new stdClass();
		$i = 1;
		while( imap_fetchbody( $this->marubox, $mid, $i ) ){
		   $struct->parts[$i-1] = imap_bodystruct( $this->marubox, $mid, $i );
		   $i++;
		}
		
		print_r($struct);
		die();
		*/
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$attach_time = $time;
		
		$struckture = imap_fetchstructure($this->marubox,$mid);
		/*
		$struct = new stdClass();
		$i = 1;
		while( imap_fetchbody( $this->marubox, $mid, $i ) ){
		   $struct->parts[$i-1] = imap_bodystruct( $this->marubox, $mid, $i );
		   $i++;
		}
		*/
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$attached_time = $time;
		$total_attach_time = round(($attached_time - $attach_time), 4);
		
		
		//die("imap_fetchstructure: " . $total_attach_time );
		$ar="";
		if(isset($struckture->parts))
        {
			//die(print_r($struckture->parts));
			foreach($struckture->parts as $key => $value)
			{
				$enc=$struckture->parts[$key]->encoding;
				if($struckture->parts[$key]->ifdparameters) {
					
					//die(print_r($struct));
					
					$name=$struckture->parts[$key]->dparameters[0]->value;
					
					$arrFilename = explode(".", $name);
					$extension = $arrFilename[count($arrFilename) - 1];
					$extension = strtolower($extension);
					
					if (count($arrAcceptable) > 0) {
						if (!in_array($extension, $arrAcceptable)) {
							//not acceptable, get out
							continue;
						}
					}
					
					if ($blnDownload) {
						//die("down");
						if($specific_file!="") {
							if ($name != $specific_file) {
								//this is not the file we requested
								continue;
							}
						}
						
						$message = imap_fetchbody($this->marubox,$mid,$key+1);
						if ($enc == 0)
							$message = imap_8bit($message);
						if ($enc == 1)
							$message = imap_8bit ($message);
						if ($enc == 2)
							$message = imap_binary ($message);
						if ($enc == 3)
							$message = imap_base64 ($message); 
						if ($enc == 4)
							$message = quoted_printable_decode($message);
						if ($enc == 5)
							$message = $message;
					
						//die($path);
						//make sure the path exists
						
						if (!is_dir($path)) {
							mkdir($path, 0777, true);
						} else {
							chmod($path, 0777);
						}
						
						$fp=fopen($path.$name,"w");
						fwrite($fp,$message);
						fclose($fp);
						
					}
					$ar=$ar.$name.",";
				}
				// Support for embedded attachments starts here
				//if($struckture->parts[$key]->parts)
				if(isset($struckture->parts[$key]->parts))
				{
					foreach($struckture->parts[$key]->parts as $keyb => $valueb)
					{
						$enc=$struckture->parts[$key]->parts[$keyb]->encoding;
						if($struckture->parts[$key]->parts[$keyb]->ifdparameters)
						{
							$name=$struckture->parts[$key]->parts[$keyb]->dparameters[0]->value;
							if ($blnDownload) {
								//die("down");
								if($specific_file!="") {
									if ($name != $specific_file) {
										//this is not the file we requested
										continue;
									}
								}
								$partnro = ($key+1).".".($keyb+1);
								$message = imap_fetchbody($this->marubox,$mid,$partnro);
								if ($enc == 0)
									   $message = imap_8bit($message);
								if ($enc == 1)
									   $message = imap_8bit ($message);
								if ($enc == 2)
									   $message = imap_binary ($message);
								if ($enc == 3)
									   $message = imap_base64 ($message);
								if ($enc == 4)
									   $message = quoted_printable_decode($message);
								if ($enc == 5)
									   $message = $message;
							
							
								//make sure the path exists
								
								if (!is_dir($path)) {
									mkdir($path, 0777, true);
								} else {
									chmod($path, 0777);
								}
								
								$fp = fopen($path.$name,"w");
								fwrite($fp,$message);
								fclose($fp);
							}
							$ar = $ar.$name.",";
						}
					}
				}				
			}
		}
		$ar=substr($ar,0,(strlen($ar)-1));
		
		$errs = imap_errors();
		
		return $ar;
	}
	function getBody($mid) // Get Message Body
	{
		if(!$this->marubox)
			return false;

		$body = $this->get_part($this->marubox, $mid, "TEXT/HTML");
		if ($body == "")
			$body = $this->get_part($this->marubox, $mid, "TEXT/PLAIN");
		if ($body == "") { 
			return "";
		}
		return $body;
	}
	function deleteMails($mid) // Delete That Mail
	{
		if(!$this->marubox) {
			//die("no marubox");
			return false;
		}
		
		imap_delete($this->marubox,$mid);
		
		imap_expunge($this->marubox); 
	}
	function close_mailbox() //Close Mail Box
	{
		if(!$this->marubox)
			return false;

		imap_close($this->marubox,CL_EXPUNGE);
	}
}
?>