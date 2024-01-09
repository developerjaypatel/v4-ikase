<?php
include("connection.php");

$key = "AiLZNujKEOV1WIFx4_n0XExGSDIVpeYL0KLKmZnpm-sElaB_FdaLj8qDrk0gJc3q";

//$url = "http://dev.virtualearth.net/REST/v1/Locations/US/WA/98052/Redmond/1%20Microsoft%20Way?o=&key=" . $key;

$url = "http://dev.virtualearth.net/REST/v1/Locations/13343%20Wingo?o=&key=" . $key;
$results = file_get_contents($url);

$arrResults = json_decode($results);
$objects = $arrResults->resourceSets;

//print_r($objects);
//die();
$arrNames = array();
foreach($objects as $result) {
	$resources = $result->resources;
	//$arrNames[] = $resources;
	
	foreach($resources as $resource) {
		die(print_r($resource));
		$name = $resource->name;
		$address =$resource->address;
		$arrNames[] = array(
						"name"=>$name,
						"address" => $address
						);						
	}
}
//die(print_r($arrNames));
die(json_encode($arrNames));
?>