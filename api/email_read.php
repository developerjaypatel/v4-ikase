<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', '1');

$imap = imap_open("{mail.kustomweb.com:110/pop3/novalidate-cert}", "nick@kustomweb.com", "12345");
//die(print_r(imap_errors()));
if( $imap ) {
	 //Check no.of.msgs
	 $num = imap_num_msg($imap);
	//echo "Messages:" . $num . "\r\n";
	//die("nick");
	 //if there is a message in your inbox
	 if( $num >0 ) {
		//read that mail recently arrived
		$header = imap_header($imap, $num); 
		die(print_r($header));
		$EmailDate = $header->Date;
		$EmailDate = date("Y-m-d H:i:s", strtotime($EmailDate));
		$EmailAddress = $header->to[0]->mailbox . "@" . $header->to[0]->host;
		$message = imap_qprint(imap_body($imap, $num));
		//die("messsage:" . $message);
		/*
		$client_id = 0;
		$blnBCC = ($EmailAddress!="incoming@vallejogallery.com");
		if($blnBCC) {
			//must be sent out by bcc, find out client
			$queryclient = "SELECT * 
			FROM `Clients` 
			WHERE `EMail` = '" . $EmailAddress . "'";
			//die($queryclient);
			$resultclient = mysql_query($queryclient, $link) or die("unable to get client<br />" . mysql_error());
			$numbclient = mysql_numrows($resultclient);
			if ($numbclient>0) {
				$client_id = mysql_result($resultclient, 0, "ID");
			}
			
			
			$SentDate = $EmailDate;
			$FromEmail = "info@vallejogallery.com";
			$Subject = $header->subject;
			$arrSubject = explode("::", $Subject);
			$Category = "none";
			if (count($arrSubject)>1) {
				$Category = $arrSubject[0];
				$Subject = str_replace($Category . "::", "",  $Subject);
			}
		} else {
			//die(print_r($header));
			$original_subject = $header->subject;
			//look for ::
			$arrSubject = explode("::", $original_subject);
			$Category = "none";
			if (count($arrSubject)>1) {
				$Category = $arrSubject[0];
			}
			
			//print_r($arrSubject);
			//echo $original_subject . "\r\n";
			//die($Category);
			//forwarded
			$strpos = strpos($message, "-----Original Message-----");
			$message = trim(substr($message, $strpos));
			//die($message);
			$arrLines = explode("\n", $message);
			//die(print_r($arrLines));
			$arrStuff = array("From:", "To:", "Sent:", "Date:","Subject:");
			$FromEmail = "";
			foreach($arrLines as $line) {
				//look for From, To, Sent, Subject
				foreach($arrStuff as $stuff) {
					//find the stuff
					$strpos = strpos($line, $stuff);
					if ($strpos!==false) {
						$item = substr($line, $strpos + strlen($stuff));
						switch($stuff) {
							case "From:":
								//die($item);
								if ($FromEmail=="") {
									$mailpos = strpos($item, "mailto:");
									if ($mailpos===false) {
										//clean up
										$item = str_replace("[", "<", $item);
										$item = str_replace("]", ">", $item);
										$arrFrom = explode("<", $item);
									} else {
										$arrFrom = explode("mailto:", $item);
									}
									//die(print_r($arrFrom));
									$FromName = trim(substr($arrFrom[0], 0, strlen($arrFrom[0])-1));
									$FromName = str_replace('"', '', $FromName);
									//die($FromName);
									//need 2 characters because of /n
									$FromEmail = trim(substr($arrFrom[1], 0, strlen($arrFrom[1])-2));
								}
								//echo "Email:" . $FromEmail . "\r\n";
								//die();
								break;
							case "Sent:":
								$SentDate = date("Y-m-d H:i:s", strtotime($item));
								//echo "Sent:" . $SentDate . "\r\n";
								break;
							case "Date:":
								$SentDate = date("Y-m-d H:i:s", strtotime($item));
								//echo "Sent:" . $SentDate . "\r\n";
								break;
							case "Subject:":
								$Subject = trim($item);
								//echo "Subject:" . $Subject . "\r\n";
								break;
						}
						//echo $stuff . " -> " . $item . "\r\n";
						//move on
						break;
					}
				}
			}
		}
		//get the message
		$startstring = "Content-Transfer-Encoding: 7bit";
		$startpos = strpos($message, $startstring);
		if ($startpos>-1) {
			//found beginning
			//look for end
			$endpos = strpos($message, "------=_NextPart", $startpos);
			$message = substr($message, $startpos + strlen($startstring), ($endpos - $startpos - strlen($startstring)));
			$message = str_replace("\r\n\r\n\r\n", "\r\n", $message);
		}
		//die($FromEmail);
		if ($FromEmail!="") {
			if ($client_id==0) {
				$queryclient = "SELECT * 
				FROM `Clients` 
				WHERE `EMail` = '" . $FromEmail . "'";
				//die($queryclient);
				$resultclient = mysql_query($queryclient, $link) or die("unable to get client<br />" . mysql_error());
				$numbclient = mysql_numrows($resultclient);
				if ($numbclient>0) {
					$client_id = mysql_result($resultclient, 0, "ID");
				}
			}
			//check if this email is in already, address+date is unique
			$query = "SELECT EmailID FROM Email WHERE 1 AND `FromEmail` = '" . $FromEmail . "'
			AND `Date` = '" . $EmailDate . "'";
			$result = mysql_query($query, $link) or die("unable to check on email");
			$numbs = mysql_numrows($result);
			if ($numbs==0) {
				 //now store the email
				 $query = "INSERT INTO `Email` (`FromEmail`, `FromName`, `Category`, `Subject`, `Message`, `Date`, `SentDate`, `ClientID`)
				VALUES ('" . $FromEmail . "', '" . $FromName . "', '" . $Category. "', '" . addslashes($Subject) . "', '" . addslashes($message) . "','". $EmailDate . "','" . $SentDate . "','" . $client_id . "')";
				//die($query);
				$result = mysql_query($query, $link) or die("unable to add email");
				$EmailID = mysql_insert_id();
				if($blnBCC) {
					//now add recipient record
					$query = "INSERT INTO  `Email-Recipients` (`EmailID`, `EmailAddress`, `ClientID`, `Sent`) 
					VALUES ('" . $EmailID . "', '" . $EmailAddress . "','" . $client_id . "','1')";
					$result = mysql_query($query, $link) or die("unable to add email recipient");
				}
			}
		}
		//die("no delete yet");
		 //delete the message
		 imap_delete($imap, $num);
		 echo "<br />Deleted<br />";
		 */
	 }
	 imap_expunge($imap);
	 //close the stream
	 imap_close($imap);
	 //die();
}

//report
/*
SELECT cli.ID, cli.First, cli.Last, em . *
FROM `Email` em
INNER JOIN Clients cli ON em.FromEmail = cli.Email
WHERE 1
*/
