<?php 
include("../api/connection.php");

define("TOKEN", "d3742f8b42d10712288448eeb02e7fc57c8facbb75e6fae7ee3b9ba14199acd4");
$db1 = getConnection();
$sql1 = "SELECT v.*, ROUND(TIME_TO_SEC(timediff(NOW(),v.token_date))/60) as minutes 
    FROM ikase.cse_gmail as v where ROUND(TIME_TO_SEC(timediff(NOW(),v.token_date))/60) >= 45 and gmail_id = 332 ";

$sql1 = "SELECT v.*, NOW(),ROUND(TIME_TO_SEC(timediff(NOW(),v.token_date))/60) as minutes 
    FROM ikase.cse_gmail as v where gmail_id = '332'";
$stmt1 = $db1->prepare($sql1); 
$stmt1->execute();
$emailArr = $stmt1->fetch(PDO::FETCH_ASSOC);
var_dump($emailArr);die;
if(isset($emailArr) && is_array($emailArr) && count($emailArr) > 0) {
    foreach ($emailArr as $email) {
        $emailType = $email['email_type'];
        switch ($emailType) {
            case "gmail":
                $ch = curl_init();
                
                $data = array(
                    'TOKEN' => TOKEN,
                    'GMAIL_REFRESH_TOKEN' => '1//06vohwIoLqWq_CgYIARAAGAYSNwF-L9IrAp0zSZCT6UZxGEOivLQ-2PqFDbcsMrQKZj_HvtEchZXyFzgFUpqzkUX_hYU_cIXJLig',
                );
                    
                $curlConfig = array(
                    CURLOPT_URL            => "https://www.ikase.xyz/ikase/gmail/ui/refresh_token_by_cron.php",
                    CURLOPT_POST           => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POSTFIELDS     => http_build_query($data)
                );
                curl_setopt_array($ch, $curlConfig);
                $result = curl_exec($ch);               
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                    //echo "cURL Error #:" . $err;
                } else {
                    echo "<textarea>" . $response . "</textarea>";

                }

                break;
            case "outlook":


                break;

            default:
                break;
        }
        $token = "";
        $refreshToken = "";
        
        
        $date = date("Y-m-d H:i:s");
        $sql2 = "UPDATE ikase.cse_gmail 
            SET token = :token, 
            token_date = :token_date,
            WHERE gmail_id = :gmail_id";
		$db1 = getConnection();
		$stmt2 = $db->prepare($sql2);
		$stmt2->bindParam("gmail_id", $email['gmail_id']);
		$stmt2->bindParam("token", $token);
		$stmt2->bindParam("token_date",$date);
		$stmt2->execute();
    }
}
?>