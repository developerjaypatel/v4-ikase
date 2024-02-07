<?php
require("lib/SendGrid.php");
//require("lib/SendGrid/Email.php");

$sendgrid_username = "kustomweb";
$sendgrid_password = "sendgrid1";
$to                = "nick@kustomweb.com";

$sendgrid = new SendGrid($sendgrid_username, $sendgrid_password, array("turn_off_ssl_verification" => true));
$email    = new SendGrid\Email();
$email->addTo($to)->
       setFrom($to)->
       setSubject('[sendgrid-php-example] Owl named %yourname%')->
       setText('Owl are you doing?')->
       setHtml('<strong>%how% are you doing?</strong>')->
       addSubstitution("%yourname%", array("Mr. Owl"))->
       addSubstitution("%how%", array("Owl"))->
       addHeader('X-Sent-Using', 'SendGrid-API')->
       addHeader('X-Transport', 'web')->
       addAttachment('test/gif.gif');

$response = $sendgrid->send($email);
var_dump($response);

?>