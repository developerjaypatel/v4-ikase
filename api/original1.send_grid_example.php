<?php

//
// SendGrid PHP Library Example
//
// This example shows how to send email through SendGrid
// using the SendGrid PHP Library.  For more information
// on the SendGrid PHP Library, visit:
//
//     https://github.com/sendgrid/sendgrid-php
//

require '../sendit/vendor/autoload.php';


/* USER CREDENTIALS
/  Fill in the variables below with your SendGrid
/  username and password.
====================================================*/
$sg_username = "kustomweb";
$sg_password = "sendgrid1";


/* CREATE THE SENDGRID MAIL OBJECT
====================================================*/
$sendgrid = new SendGrid( $sg_username, $sg_password );
$mail = new SendGrid\Email();



/* ADD THE ATTACHMENT
/  For the purposes of this demo, the file being
/  attached resides in the same folder as this
/  example.php file
====================================================*/
$mail->
addAttachment( dirname( __FILE__ )."/sendgrid_logo.jpg" );



/* SMTP API
====================================================*/
// ADD THE CATEGORIES
$categories = array (
    "New Category1",
    "New Category2"
);
foreach($categories as $category) {
    $mail->addCategory($category);
}

// ADD THE SUBSTITUTIONS
$subs = array (
    "%replacement_tag%" => array (
        "Value 1"
    ),
    "%replacement_tag2%" => array (
        "Value 2"
    )
);
foreach($subs as $tag => $replacements) {
    $mail->addSubstitution($tag, $replacements);
}


/* SEND MAIL
/  Replace the the address(es) in the setTo/setTos
/  function with the address(es) you're sending to.
====================================================*/

$arrDestinations = array();
$arrDestinations[] = "nick@kustomweb.com";
$arrDestinations[] = "nick.giszpenc@gmail.com";
//die(print_r($arrDestinations));
$subject = "Test send " . date("H:i:s");
try {
    $mail->
    setFrom( "donotreply@ikase.org" )->
    setTos($arrDestinations)->
    setSubject( $subject )->
    setText( "Hello,\n\nThis is a test message from SendGrid.    We have sent this to you because you requested a test message be sent from your account.\n\nThis is a link to google.com: http://www.google.com\nThis is a link to apple.com: http://www.apple.com\nThis is a link to sendgrid.com: http://www.sendgrid.com\n\nThank you for reading this test message.\n\nLove,\nYour friends at SendGrid" )->
    setHtml( "<table style=\"border: solid 1px #000; background-color: #666; font-family: verdana, tahoma, sans-serif; color: #fff;\"> <tr> <td> <h2>Hello,</h2> <p>This is a test message from SendGrid.    We have sent this to you because you requested a test message be sent from your account.</p> <a href=\"http://www.google.com\" target=\"_blank\">This is a link to google.com</a> <p> <a href=\"http://www.apple.com\" target=\"_blank\">This is a link to apple.com</a> <p> <a href=\"http://www.sendgrid.com\" target=\"_blank\">This is a link to sendgrid.com</a> </p> <p>Thank you for reading this test message.</p> Love,<br/> Your friends at SendGrid</p> <p> <img src=\"http://cdn1.sendgrid.com/images/sendgrid-logo.png\" alt=\"SendGrid!\" /> </td> </tr> </table>" );
    
    $response = $sendgrid->send( $mail );

    if (!$response) {
        throw new Exception("Did not receive response.");
    } else if ($response->message && $response->message == "error") {
        throw new Exception("Received error: ".join(", ", $response->errors));
    } else {
        print_r($response);
    }
} catch ( Exception $e ) {
    var_export($e);
}


?>
