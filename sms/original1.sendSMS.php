<?php
$app->post('/event/send', 'sendSMS');

function sendSMS(){
    $id = passed_var("id", "post");
    $cellphone = passed_var("cellphone", "post");
    $message = passed_var("message", "post");

    $url = "https://rest.nexmo.com/sms/json?api_key=1623e20b&api_secret=4aad68ee8c2ca1d4&from=12046743938&to=" . $cellphone . "&text=" . $message;
    $response = get_data($url);

    die(json_encode(array("success"=>"true", "response"=>$response)));
}
?>