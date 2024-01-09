<?php 
$cronToken = !empty($_GET['CRON_TOKEN']) ? $_GET['CRON_TOKEN'] : 1;

if('bd6fe1d0be6347b8ef2427fa629c04485d4f6169fc5fe71d0a1a76cbe6a274c6e$' === $cronToken) {
    include("api/connection.php");

    define("TOKEN", "d3742f8b42d10712288448eeb02e7fc57c8facbb75e6fae7ee3b9ba14199acd4$");
    $db1 = getConnection();
    $sql1 = "SELECT v.*, ROUND(TIME_TO_SEC(timediff(NOW(),v.token_date))/60) as minutes 
        FROM ikase.cse_gmail as v where ROUND(TIME_TO_SEC(timediff(NOW(),v.token_date))/60) >= 45 ";

    // $sql1 = "SELECT v.*, NOW(),ROUND(TIME_TO_SEC(timediff(NOW(),v.token_date))/60) as minutes 
    //     FROM ikase.cse_gmail as v where gmail_id = '333'";
    //echo $sql1;die;
    $stmt1 = $db1->prepare($sql1); 
    $stmt1->execute();
    $emailArr = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    if(isset($emailArr) && is_array($emailArr) && count($emailArr) > 0) {
        foreach ($emailArr as $email) {
            if(!empty($email['refresh_token'])) {
                $result = NULL;
                $decodeResults = [];
                $emailType = $email['email_type'];
                $gmailId = $email['gmail_id'];
                switch ($emailType) {
                    case "gmail":
                        $url = "https://www.ikase.xyz/ikase/gmail/ui/refresh_token_by_cron.php";
                        $params = array(
                            'TOKEN' => TOKEN,
                            'GMAIL_REFRESH_TOKEN' => $email['refresh_token'],
                            'user_id' => $email['user_id'],
                        );
                        $result = post_curl($url, $params);
                        $decodeResults = json_decode($result, true);
                        //var_dump($decodeResults);die;
                        if(isset($decodeResults['status']) && $decodeResults['status'] && !empty($decodeResults['token'])) {
                            $sql2 = "update ikase.cse_gmail 
                            set token = '".$decodeResults['token']."' , 
                            token_date = '".date('Y-m-d H:i:s')."' where gmail_id = '".$gmailId."' ";
                            //echo $sql2;die;
                            $stmt2 = $db1->prepare($sql2); 
                            $stmt2->execute();
                        }
                        break;
                    case "outlook":
                        $url = "https://www.ikase.xyz/ikase/outlook/refresh_token_by_cron.php";
                        $params = array(
                            'TOKEN' => TOKEN,
                            'OUTLOOK_REFRESH_TOKEN' => $email['refresh_token'],
                        );
                        //var_dump($params);die;
                        $result = post_curl($url, $params);
                        //var_dump( $result);die;
                        $decodeResults = json_decode($result, true);
                        //var_dump($decodeResults);die;
                        if(isset($decodeResults['status']) && $decodeResults['status'] && !empty($decodeResults['refresh_token'])  && !empty($decodeResults['access_token'])) {
                            $sql2 = "update ikase.cse_gmail set 
                            token = '".$decodeResults['access_token']."' , token_date = '".date('Y-m-d H:i:s')."' ,
                            refresh_token = '".$decodeResults['refresh_token']."' , refresh_token_at = '".date('Y-m-d H:i:s')."' 
                            where gmail_id = '".$gmailId."' ";
                            //echo $sql2;die;
                            $stmt2 = $db1->prepare($sql2); 
                            $stmt2->execute();
                        }

                        break;

                    default:
                        break;
                }
            }
        }
    }
} else {
    header("location:index.php?cusid=-1");
	die();
}
?>