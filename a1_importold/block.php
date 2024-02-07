<?php
require('blockspring.php');
/*
$res = Blockspring::runParsed("weather-for-zip-code", array("zip_code" =>"91331"));
//, {"api_key":"68c6ec4279d66fa719341ada9f39e844"}
print_r($res);


$res = Blockspring::runParsed("interactive-google-map", array(
  "locations" => array(
    array("Latitude", "Longitude", "Tooltip"),
    array(37.4217542234, -122.100920271, "Somewhere"),
    array(41.895964876906, -87.632716978217, "Out"),
    array(28.58230778, 77.09399505, "There")
  )
));

print($res->params["map"]);

*/

$res = Blockspring::runParsed("summarize-text", array(
  "url" => "http://www.usatoday.com/story/life/music/2015/04/18/rock-hall-of-fame-2015-induction-ceremony/25913423/",
  "sentences" => 1
), array(
  "cache" => true,
  "expiry" => 3600,
  "api_key" => "68c6ec4279d66fa719341ada9f39e844"
));

print($res->params["summary"]);
?>