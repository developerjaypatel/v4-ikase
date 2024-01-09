<?php
/*
 * File: example.php
 * Description: Received Mail Example
 * Created: 01-03-2006
 * Author: Mitul Koradia
 * Email: mitulkoradia@gmail.com
 * Cell : +91 9825273322
 */
function mail_get_parts($imap,$mid,$part,$prefix) {    
    $attachments=array(); 
    $attachments[$prefix]=mail_decode_part($imap,$mid,$part,$prefix); 
    if (isset($part->parts)) // multipart 
    { 
        $prefix = ($prefix == "0")?"":"$prefix."; 
        foreach ($part->parts as $number=>$subpart) 
            $attachments=array_merge($attachments, mail_get_parts($imap,$mid,$subpart,$prefix.($number+1))); 
    } 
    return $attachments; 
} 
error_reporting(E_ALL);
ini_set('display_errors', '1');

$yourEmail = "nick.giszpenc@gmail.com";
$yourEmailPassword = "G00gles1";
//$yourEmail = "thekons23@gmail.com";
//$yourEmailPassword = "pcmg_pnk";

/*
Gmail {imap.gmail.com:993/imap/ssl}INBOX
Yahoo {imap.mail.yahoo.com:993/imap/ssl}INBOX
AOL {imap.aol.com:993/imap/ssl}INBOX
*/
 //connexion     
   	$hostname = '{pop.gmail.com:995/pop3/ssl}INBOX';
	//$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
	
    $username = $yourEmail; 
    $password = $yourEmailPassword;

    //trying to connect -- works fine
    $mbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());
	
    //retrieve UNSEEN emails only
    $emails = imap_search($mbox,'UNSEEN'); 
	
	//die(print_r($emails));
	
    $nbEmails = count($emails); //find the number of emails
	echo "found:" . $nbEmails . "<br />";
	die();
	$headers = imap_headers($mbox);
	$tot = count($headers);
	$tot = 1;
	$arrHeaders = array();
	$arrAcceptable = array("jpg", "png", "pdf", "doc", "docx");
	for($i=$tot;$i>0;$i--) {
		$mail_header=imap_header($mbox,$i);
		$struckture = imap_fetchstructure($mbox, $i);
		if ($struckture->type==1){
		   $body = imap_fetchbody($mbox,$i,"1"); ## GET THE BODY OF MULTI-PART MESSAGE
	  	} else {
		   $body = imap_body($mbox, $i);
	  	}
		$arrMessages = array();
		if(isset($struckture->parts)) {
			foreach($struckture->parts as $key => $value) {
				$enc=$struckture->parts[$key]->encoding;
				//echo "encode:" . $enc;
				$message = imap_fetchbody($mbox,$i,$key);
				}
				if ($enc == 0) {
					$message = imap_8bit($message);
				}
				if ($enc == 1) {
					$message = imap_8bit ($message);
				}
				if ($enc == 2) {
					$message = imap_binary ($message);
				}
				if ($enc == 3) {
					$message = imap_base64 ($message); 
				if ($enc == 4) {
					$message = quoted_printable_decode($message);
				}
				if ($enc == 5) {
					$message = $message;
				}
				$arrMessages[] = $message;
			}
		} 
		
		$arrHeaders[] = array("header"=>$mail_header,"body"=>$body, "structure"=>$struckture, "messages"=>$arrMessages);	//
	}
		
    imap_close($mbox); //close the connexion   

die(json_encode($arrHeaders));

include("receivemail.class.php");
$id = 2;
// Creating a object of reciveMail Class
$obj= new receiveMail('nick@kustomweb.com','12345','nick@kustomweb.com','maserati.websitewelcome.com','pop3','110',false);

//Connect to the Mail Box
$obj->connect();         //If connection fails give error message and exit

// Get Total Number of Unread Email in mail box
$tot=$obj->getTotalMails(); //Total Mails in Inbox Return integer value

//echo "Total Mails:: $tot<br>";

$result = array();
for($i=$tot;$i>0;$i--)
{
	$str=$obj->GetAttach($i,"./"); 
	$attach = 0;
	if ($str!="") {
		//echo $i . " -- " . $str . "\r\n";
		$ar=explode(",",$str);
		$attach = count($ar);
	}
	$head=$obj->getHeaders($i, $attach);  // Get Header Info Return Array Of Headers **Array Keys are (subject,to,toOth,toNameOth,from,fromName)
	//die(print_r($obj));
	//if ($id==$i) {
	/*
	echo "ID :: ".$head['message_id']."<br>";
	echo "Subjects :: ".$head['subject']."<br>";
	echo "TO :: ".$head['to']."<br>";
	echo "To Other :: ".$head['toOth']."<br>";
	echo "ToName Other :: ".$head['toNameOth']."<br>";
	echo "From :: ".$head['from']."<br>";
	echo "FromName :: ".$head['fromName']."<br>";
	echo "<br><a onClick='readMessage(" . $i . ")'>Read " . $i . "</a><br>";
	echo "<br>*******************************************************************************************<BR>";
	echo "<div id='body_" . $i . "' style='display:'>" . $obj->getBody($i) . "</div>";  // Get Body Of Mail number Return String Get Mail id in interger
	*/
	
	/*
	foreach($ar as $key=>$value)
		echo ($value=="")?"":"Atteched File :: ".$value."<br>";
	echo "<br>------------------------------------------------------------------------------------------<BR>";
	//}
	//$obj->deleteMails($i); // Delete Mail from Mail box
	*/
	//$head->attachments = count($ar);
	$result[$i] = $head;
}
$obj->close_mailbox();   //Close Mail Box

die(json_encode($result));
?>
<script language="javascript">
function readMessage(id) {
	document.getElementById("body_" + id).style.display = "";
}
</script>