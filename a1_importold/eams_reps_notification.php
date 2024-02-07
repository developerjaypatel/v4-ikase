<?php
set_time_limit(300);
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("email_message.php");
include("connection.php");

$lastweek = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d") - 8, date("Y")));

//first let's check if it's already here
$query = "SELECT * 
FROM ikase.`cse_eams_reps` 
WHERE last_update > :lastweek
AND create_date > :lastweek
ORDER BY firm_name";

try {
	$db = getConnection();
	$stmt = $db->prepare($query);
	$stmt->bindParam("lastweek", $lastweek);
	$stmt->execute();
	$eams_companies = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}
$arrRows = array();
foreach($eams_companies as $comp) {
	$row = "
	<tr>
		<td align='left' valign='top'>" . $comp->firm_name . "
		</td>
		<td align='left' valign='top'>" . $comp->city . ", " . $comp->state . "
		</td>
		<td align='left' valign='top'>" . $comp->phone . "
		</td>
	</tr>
	";
	$row = "<div>" . $comp->firm_name . " :: " . $comp->city . ", " . $comp->state . " :: Phone:" .$comp->phone . "</div>";
	$arrRows[] = $row;
}
$text_message = "<p>
There were " . count($arrRows) . " new firm(s) added to EAMS as of " . date("m/d/Y", strtotime($lastweek));
$text_message .= "</p>";
$text_message .= "<p>&nbsp;</p>";
$text_message .= "\r\n";
$text_message .= implode("\r\n", $arrRows);

$html_message = $text_message; 
$text_message = str_replace("<p>", "", $text_message);
$text_message = str_replace("<div>", "", $text_message);
$text_message = str_replace("</p>", "\r\n\r\n", $text_message);
$text_message = str_replace("</div>", "\r\n", $text_message);
$text_message = str_replace("<br>", "\r\n", $text_message);
$text_message = str_replace("<br />", "\r\n", $text_message);
$text_message = str_replace("&nbsp;", " ", $text_message);
$text_message = strip_tags($text_message);
//die($text_message);
$attachments = "";
$from_name = "IKase System";
$from_address = "nick@kustomweb.com";

$subject = "Newly Added EAMS Report " . date("m/d/Y", strtotime($lastweek));
$to_name = "nick@kustomweb.com,latommy1@gmail.com";
//$to = "nick@kustomweb.com";
$cc_name = "";
$bcc_name = "";
//echo $subject . "\r\n";
//die($message);
$blnSendEmail = false;
try {
	include("send_test.php");
	$result = $email_result;

	$blnSendEmail = ($email_result=="sent");
	
	if ($blnSendEmail) {
		echo "mail sent";
	}
} catch ( Exception $e ) {
	die(print_r($e));
	//not sent	
}
?>
