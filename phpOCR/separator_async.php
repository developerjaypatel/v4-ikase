<?php
session_start();
session_write_close();

include_once("functions.php");

//echo PHP_BINDIR . "<br />";
//die(print_r($_SERVER));
//die(phpinfo());

$image_path = $_GET["image_path"];
$batchscan_id = $_GET["batchscan_id"];
$page = $_GET["page"];

$msg = "multithread request start async " . $batchscan_id;
include("../api/cls_logging.php");

$url = "https://www.ikase.website/phpOCR/separator_multithread.php";
$params = array('page'=>$page,'batchscan_id'=>$batchscan_id,'image_path'=>$image_path,'customer_id'=>$_SESSION['user_customer_id']);

$msg = $url . "?page=" . $page . "&batchscan_id=" . $batchscan_id ."&image_path=" . $image_path . "&customer_id=" . $_SESSION['user_customer_id'];
$log->lwrite($msg);

curl_post_async($url, $params);

$msg = "multithread requested from async " . $batchscan_id;
$log->lwrite($msg);
/*
function curl_post_async($url, $params)
{
    foreach ($params as $key => &$val) {
      if (is_array($val)) $val = implode(',', $val);
        $post_params[] = $key.'='.urlencode($val);
    }
    $post_string = implode('&', $post_params);

    $parts=parse_url($url);

    $fp = fsockopen($parts['host'],
        isset($parts['port'])?$parts['port']:80,
        $errno, $errstr, 300);

    $out = "POST ".$parts['path']." HTTP/1.1\r\n";
    $out.= "Host: ".$parts['host']."\r\n";
    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
    $out.= "Content-Length: ".strlen($post_string)."\r\n";
    $out.= "Connection: Close\r\n\r\n";
    if (isset($post_string)) $out.= $post_string;

    fwrite($fp, $out);
    fclose($fp);
}
*/
/*
$cmd = 'nohup nice -n 10 ' . PHP_BINDIR . ' -c /opt/php54/lib/php.ini -f /home/cstmwb/public_html/autho/web/phpOCR/' . $url . ' action=generate page=' . $page . ' batchscan_id=' . $batchscan_id . ' image_path=' . $image_path . '  >> /home/cstmwb/public_html/autho/web/phpOCR/log.txt';
echo $cmd . "<br />";
$pid = shell_exec($cmd);
die("pid:" . $pid);
*/
//header("location:separator_multithread.php?page=" . $page . "&batchscan_id=" . $batchscan_id . "&image_path=" . $image_path);
?>