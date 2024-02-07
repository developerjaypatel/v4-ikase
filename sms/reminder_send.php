<?php
// error_reporting(E_ALL);
// ini_set('display_errors', '1');

include("../api/connection.php");
require_once 'plivo.php';
$trackname = "sending.txt";
// if(isset($_GET["called"])){
//     $fp = fopen($trackname, "a+");
//     fwrite($fp, "sending called: - " . date("m/d/Y H:i:s") . "\r\n");
//     fclose($fp);
// }
// die();
$require = "sendit/vendor/autoload.php";
// purchased 
$api_key = "9bf77d58";
$api_secret = "9f3642052847f430";
$from = "12133959868";

$query_date_now = date("Y-m-d H:i");
$query_date_before = date("Y-m-d H:i", strtotime("-15 minutes"));

//$query_date = "2017-03-06 14:50";
$sql = "SELECT `schema_name`
FROM `information_schema`.schemata 
WHERE schema_name LIKE 'ikase%'";

try {
	$schemas = DB::select($sql);
} catch(PDOException $e) {
    $error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
}	
die(print_r($schemas));
// $fp = fopen($trackname, "a+");
// fwrite($fp, "got schemas: - " . count($schemas) . " - " . date("m/d/Y H:i:s") . "\r\n");
// fclose($fp);	
foreach($schemas as $schema) {
    // $fp = fopen($trackname, "a+");
    // fwrite($fp, "got inside the foreach - " . date("m/d/Y H:i:s") . "\r\n");
    // fclose($fp);
    if ($schema->schema_name=="ikase_glauber" || $schema->schema_name=="ikase_glauber2") {
        continue;
    }
		
    $sql = "SELECT cr.*, crm.`message_uuid`, cm.`message_to`, cm.`message`, cm.`message_id`, crb.* 
            FROM `" . $schema->schema_name . "`.`cse_reminder` cr
            LEFT OUTER JOIN `" . $schema->schema_name . "`.`cse_remindersent` crs
            ON cr.reminder_uuid = crs.reminder_uuid
            LEFT OUTER JOIN `" . $schema->schema_name . "`.`cse_reminder_message` crm
            ON cr.`reminder_uuid` = crm.`reminder_uuid`
            LEFT OUTER JOIN `" . $schema->schema_name . "`.`cse_message` cm
            ON crm.`message_uuid` = cm.`message_uuid`
            INNER JOIN `" . $schema->schema_name . "`.`cse_reminderbuffer` crb
            ON cr.`reminder_uuid` = crb.`reminder_uuid`
            WHERE 1 
            AND DATE_FORMAT(cr.reminder_datetime, '%Y-%m-%d %H:%i') between '" . $query_date_before . "' and '" . $query_date_now . "'
            AND cr.deleted = 'N'
			AND cr.buffered = 'Y'
			AND cr.`sent` = 'N' 
			AND cr.reminder_type != 'interoffice'
			AND cr.reminder_type != 'popup'
            AND crs.remindersent_id IS NULL
			ORDER BY cr.reminder_datetime ASC
            LIMIT 0, 1";
        
            //die($sql);
            // echo $sql . "\r\n";
            // $fp = fopen($trackname, "a+");
            // fwrite($fp, "sql: " . $sql . "\r\n");
            // fclose($fp);
    try {
        $buffers = DB::select($sql);
        // $buffer = $stmt->fetchObject();
        // die(print_r($buffers) . "\r\n");
        // $fp = fopen($trackname, "a+");
        // fwrite($fp, "got reminders buffered from schema(" . $schema->schema_name . "): - " . count($buffers) . " - " . date("m/d/Y H:i:s") . "\r\n");
        // fclose($fp);	
		if (count($buffers)==0) {
			continue;
		}
		
        foreach ($buffers as $key => $buffer) {
            $fp = fopen($trackname, "a+");
            fwrite($fp, "found a buffered reminder for " . $schema->schema_name . " : - " . date("m/d/Y H:i:s") . "\r\n");
            fclose($fp);
            //empty the buffer, one at a time
            $customer_id = $buffer->customer_id;
            $reminderbuffer_id = $buffer->reminderbuffer_id;
            $cellphone = "1" . $buffer->message_to;
            $message = $buffer->message;
            $message_id = $buffer->message_id;
            $reminder_uuid = $buffer->reminder_uuid;
            $reminder_id = $buffer->reminder_id;
            // $buffer_id = $buffer->buffer_id;    
            $message_uuid = $buffer->message_uuid;
            $reminder_type = $buffer->reminder_type;

            if($reminder_type == "text"){
                // echo $cellphone . "\r\n";
                $url = "https://rest.nexmo.com/sms/json?api_key=" . $api_key . "&api_secret=" . $api_secret . "&from=" . $from . "&to=" . $cellphone . "&text=" . urlencode($message);
                // die($url . "\r\n");

                $response_json = file_get_contents($url);
                $response = json_decode($response_json);
                // echo print_r($response);
                // die(print_r($response));
                $arrResponse = $response->messages; 
                $status = $arrResponse[0]->status;
                if($status == "0") { 
                    // die("yes entered");
/*                    
                    $fp = fopen($trackname, "a+");
                    fwrite($fp, "sent the SMS @ " . date("m/d/Y H:i:s") . " for reminder_id: " . $reminder_id . "\r\n" . $response . "\r\n");
                    fclose($fp);
*/
                    $query = "INSERT INTO `" . $schema->schema_name . "`.`cse_remindersent` (`reminderbuffer_id`, `recipients`, `subject`, `message`, `message_uuid`, `reminder_uuid`, `customer_id`)
                                    VALUES (" . $reminderbuffer_id . ", '" . $cellphone . "', 'event text message sent' , '" . addslashes($message) . "', '" . $message_uuid . "', '" . $reminder_uuid . "', '" . $customer_id . "')";
                    // echo $query . ";\r\n";
                    // die();
                    $stmt = DB::run($query);

                    $fp = fopen($trackname, "a+");
                    fwrite($fp, "SMS sent & inserted remindersent_id @ " . date("m/d/Y H:i:s") . " and query: " . $query . "\r\n");
                    fclose($fp);
                }
            } elseif($reminder_type == "email"){
	            // die("text_message:" . $text_message);
            	$buffer->message = str_replace("\r\n", "<br />", $buffer->message);
				$buffer->message = str_replace("\n", "<br />", $buffer->message);
				$buffer->message = str_replace(chr(13), "<br />", $buffer->message);
                $url = "https://www.matrixdocuments.com/dis/sendit.php";
                $fields = array("from_name"=>$buffer->from, 
                                "from_address"=>$buffer->from_address, 
                                "to_name"=>$buffer->to, 
                                "cc_name"=>$buffer->cc, 
                                "bcc_name"=>$buffer->bcc, 
                                "html_message"=>urlencode($buffer->message), 
                                "subject"=>urlencode($buffer->subject)
                                );
                // die(print_r($fields));
                $fields_string = "";
                foreach($fields as $key=>$value) { 
                    $fields_string .= $key.'='.$value.'&'; 
                }
                rtrim($fields_string, '&');
                $timeout = 5;
                //open connection
                $ch = curl_init();
                        
                //set the url, number of POST vars, POST data
                curl_setopt($ch, CURLOPT_URL,$url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
                curl_setopt($ch, CURLOPT_HEADER, false); 
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                curl_setopt($ch, CURLOPT_POST, count($fields_string));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                //curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
                
                //execute post
                $result = curl_exec($ch);
                // die($result);
/*                    
                $fp = fopen($trackname, "a+");
                fwrite($fp, "sent the email @ " . date("m/d/Y H:i:s") . " for reminder_id: " . $reminder_id . "\r\n");
                fclose($fp);
*/
                $blnSendEmail = ($result=="sent");
                if($blnSendEmail) {
                    $query = "INSERT INTO `" . $schema->schema_name . "`.`cse_remindersent` (`reminderbuffer_id`, `recipients`, `subject`, `message`, `message_uuid`, `reminder_uuid`, `customer_id`)
                            VALUES (" . $reminderbuffer_id . ", '" . $cellphone . "', 'event email message sent' , '" . addslashes($message) . "', '" . $message_uuid . "', '" . $reminder_uuid . "', '" . $customer_id . "')";
                    // echo $query . ";\r\n";
                    
                    $stmt = DB::run($query);

                    $fp = fopen($trackname, "a+");
                    fwrite($fp, "Email sent & inserted remindersent_id @ " . date("m/d/Y H:i:s") . " and query: " . $query . "\r\n");
                    fclose($fp);
                }            
            } elseif ($reminder_type == "voice") {  
                // First set the default paths and variables
                $threeLoops = array ('loop' => 3,);
                $linguatec_url = "http://kustomweb.xyz/ikase_voice/spoken/ikase/" . $customer_id . "/mp3/output_reminder_" . $reminder_id . "_" . $message_id . ".mp3";
                $answer_url = 'speak/' . $customer_id . '/' . $batch_id . '/speak_reminder_' . $reminder_id . "_" . $message_id . '.xml';
                // Then make the components for the XML
                $getdigitattributes = array ("action"=> $linguatec_url);  
                $r = new Response();
                $g = $r->addGetDigits($getdigitattributes);
				$g->addPlay($linguatec_url,$threeLoops);
                $xml_response = $r->toXML();
				//die($xml_response);
                //Make the directories that are neccessary
                if (!file_exists('speak')) {
					mkdir('speak', 0777);
				}
                if (!file_exists('speak/' . $customer_id . '/')) {
					mkdir('speak/' . $customer_id . '/', 0777);
				}  
                //create the xml                          
				$fp = fopen($answer_url, 'w'); 
                //fwrite the response to xml file               
				fwrite($fp, $xml_response);
				fclose($fp);
				//Make an outbound call
                // die("auth id: " . $auth_id . " auth token:" . $auth_token);
                $p = new RestAPI($auth_id, $auth_token);
                $params = array(
                    'to' => "+" . $cellphone, # The phone numer to which the all has to be placed
                    'from' => $auth_phone, # The phone number to be used as the caller id
                    'answer_url' => "https://ikase.org/sms/" . $answer_url, # The URL invoked by Plivo when the outbound call is answered
                    //'answer_url' => 'http://rcsclientpage.com/developer/api/plivo/answer.php',
                    'answer_method' => "GET", # The method used to call the answer_url
                    // Example for Asynchrnous request
                    //'callback_url' : "https://glacial-harbor-8656.herokuapp.com/callback/", # The URL notified by the API response is available and to which the response is sent.
                    //'callback_method' : "GET" # The method used to notify the callback_url.
                    // machine_detection is set to "hangup", the call hangs up immediately
                    //'hangup_url' => 'http://rcsclientpage.com/developer/api/plivo/hangup.php',
                );
                // die(print_r($params));
                
                $response = $p->make_call($params);                
                // die(print_r($response));
                if($response["status"] == "201"){
                    $query = "INSERT INTO `" . $schema->schema_name . "`.`cse_remindersent` (`reminderbuffer_id`, `recipients`, `subject`, `message`, `message_uuid`, `reminder_uuid`, `customer_id`)
                            VALUES (" . $reminderbuffer_id . ", '" . $cellphone . "', 'event voice message sent' , '" . addslashes($message) . "', '" . $message_uuid . "', '" . $reminder_uuid . "', '" . $customer_id . "')";
                    // echo $query . ";\r\n";
                    
                    $stmt = DB::run($query);
                    
                    $fp = fopen($trackname, "a+");
                    fwrite($fp, "Voice sent & inserted remindersent_id @ " . date("m/d/Y H:i:s") . " and query: " . $query . "\r\n");
                    fclose($fp);                    
                }
            }
			
			$strSQL = "UPDATE `" . $schema->schema_name . "`.`cse_reminder` 
			SET `sent` = 'Y' 
			WHERE `reminder_id` = '" . $reminder_id . "'";
            $stmt = DB::run($strSQL);
			
            //nexmo needs a little bit of time between requests
            $fp = fopen($trackname, "a+");
            fwrite($fp, "finished sending : - " . date("m/d/Y H:i:s") . "\r\n");
            fclose($fp);
            sleep(5);
        }
        // $fp = fopen($trackname, "a+");
        // fwrite($fp, "out of the loop : - " . date("m/d/Y H:i:s") . "\r\n");
        // fclose($fp);
        $params = array();
        curl_post_async("https://" . $_SERVER['HTTP_HOST'] . "/sms/reminder_send.php", $params);

    // die(json_encode(array("success"=>"true")));
    } catch(PDOException $e) {
        $error = array("error"=> array("text"=>$e->getMessage()));
            echo json_encode($error);
    }
die(json_encode(array("success"=>"true")));
}
?>
