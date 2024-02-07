<?php
$filename = $_GET["image_path"];
$batchscan_id = $_GET["batchscan_id"];
$page = $_GET["page"];

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "separator_multithread.php?page=" . $page . "&batchscan_id=" . $batchscan_id . "&filename=" . $filename);
curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1);

curl_exec($ch);
curl_close($ch);
?>