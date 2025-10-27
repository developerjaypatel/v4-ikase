<?php
include("connection.php");

$lat = passed_var("lat", "post");
$long = passed_var("long", "post");

$url = "http://api.geonames.org/findNearbyPostalCodesJSON?username=kustomweb&lat=" . $lat .  "&lng=" . $long;

$content = file_get_contents($url);

die($content);
?>