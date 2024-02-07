<?php
include("plivo.php");
$require = "sendit/vendor/autoload.php";

if(isset($_GET["cellphone"])){
    sendSMS();
}
if(isset($_GET["email"])){
    sendEmail();
}

function sendSMS() {


curl "https://rest.nexmo.com/sms/json?api_key=1623e20b&api_secret=4aad68ee8c2ca1d4&from=12046743938&to=[18054685888]&text=Welcome+to+Nexmo";

    /*
    // die("Cell");
    // $phone = $_GET["cellphone"];
	// if (substr($phone, 0, 1)!="1") {
    //     $phone = "1" . $phone;
    // }

    
    //rcs
    $auth_id = "MAMDMYODIZNWNLMZI4ZT";
    $auth_token = "ZTEzNmRhNDU2OTg4YTVkMTMwNGQxZDRmNDVlMjEz";            

    $to = "+18054685888"; //$_POST['To'];
    $auth_phone = "+17472251107";
    $from = $auth_phone;
    $text = "did you get the text";	//$_POST['Text'];
    
    // Send a message
    $params = array(
            'src' => $from,
            'dst' => $to,
            'text' => $text,
            'type' => 'sms',
        );
    // die(print_r($params));	
	// user neal@bapats.com
	// pass norm1066
    // $auth_id = "MAMTVMNJFKMDLHYJLHYW";
    // $auth_token = "NTMxZDFiMjIxMTViNTFjMjUxZGM2ZjU4NzUwNzIy";   

    // die($auth_id . "    " . $auth_token);
    $p = new RestAPI($auth_id, $auth_token);
    $response = $p->send_message($params);
    echo print_r($response);
    if (array_shift(array_values($response)) == "202") {
        //echo "<br/><br/>Message status: Sent";
        die(json_encode(array("success" => "true")));
    } else {
        die("<br/><br/>Error: Please ensure that From or To number is a valid and sms feature enabled Plivo DID number");
    }

    */
}

function sendEmail() {
    die("email");
    $email = $_GET["email"];
    /* USER CREDENTIALS
    /  Fill in the variables below with your SendGrid
    /  username and password.
    ====================================================*/
    $sg_username = "kustomweb";
    $sg_password = "sendgrid1!";
    
    /* CREATE THE SENDGRID MAIL OBJECT
    ====================================================*/
    $sendgrid = new SendGrid( $sg_username, $sg_password );
    $mail = new SendGrid\Email();
    
    /* SMTP API
    ====================================================*/
    // ADD THE CATEGORIES
    /*
    $categories = array (
        "Email"
    );
    */
    $mail->
    setFrom( "billing@doctor.com" )->
    setTos( "nick@kustomweb.com" )->
    setSubject( "Sending an Email by Send Grid" )->
    setText( "did you receive it?" );
    // die(print_r($mail));
    $response = $sendgrid->send($mail);
    // echo "hi";
    // die(json_encode($response));
    if (!$response) {
        throw new Exception("Did not receive response.");
    } else if ($response->message && $response->message == "error") {
        throw new Exception("Received error: ".join(", ", $response->errors));
    } else {
        // echo json_encode($response);
        // print_r($response);
        $blnSent = true;
    }    
}
?>