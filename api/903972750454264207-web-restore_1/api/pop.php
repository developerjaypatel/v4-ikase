<?php
/*
 * File: example.php
 * Description: Received Mail Example
 * Created: 01-03-2006
 * Author: Mitul Koradia
 * Email: mitulkoradia@gmail.com
 * Cell : +91 9825273322
 */
 
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', '1');

$yourEmail = "nick.giszpenc@gmail.com";
$yourEmailPassword = "G00gles1";

 //connexion     
    $hostname = '{pop.gmail.com:995/pop3/ssl}INBOX';
    $username = $yourEmail; 
    $password = $yourEmailPassword;

    //trying to connect -- works fine
    $inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());
	
	$headers=imap_headers($inbox);
	//die(print_r($headers));
		
    //retrieve UNSEEN emails only
    //$emails = imap_search($inbox,'UNSEEN'); 
	
	//die(print_r($emails));
	
    $nbEmails = count($headers); //find the number of emails
	echo "found:" . $nbEmails . "<br />\n";
	
	foreach ($headers as $val) {
        echo $val . "<br />\n";
    }
	
    imap_close($inbox); //close the connexion   

die();
?>