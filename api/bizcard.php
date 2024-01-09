<?php

$arr["KustomWeb"]["name"] = "Nicolas Giszpenc";
$arr["KustomWeb"]["title"][1] = "Owner";
$arr["KustomWeb"]["title"][2] = "Developer";
//$arr["KustomWeb"]["address"] = "13343 Wingo Street, Arleta, CA 91331";
$arr["KustomWeb"]["contact"]["phone"] = "818.897.8750";
$arr["KustomWeb"]["contact"]["cell"] = "818.486.2869";
$arr["KustomWeb"]["contact"]["email"] = "nick@KustomWeb.com";
$arr["KustomWeb"]["what we do"][1] = "web apps";
/*
$arr["skills"]["languages"][1] = "javascript";
$arr["skills"]["languages"][2] = "php";
$arr["skills"]["languages"][3] = "sql";
*/
//die(print_r($arr));

echo json_encode($arr);
