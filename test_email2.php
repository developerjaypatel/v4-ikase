<?php
//Set no caching
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_WARNING);
ini_set('display_errors', '1');

include("api/connection.php");

function kToF($temperature) {
	if (!is_numeric($temperature)) {
		return '';
	}
	return number_format($temperature * (9/5) - 459.67, 0);
}

$ip = $_SERVER['REMOTE_ADDR'];

$sql = "SELECT * FROM ikase.cse_ip_location
WHERE ip_address = '" . $ip . "'";

try {
	$db = getConnection();
		
	$stmt = $db->query($sql);
	$ip_record = $stmt->fetchObject();
	
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	$error = array("error1"=> array("text"=>$e->getMessage()));
	die(json_encode($error));
}

echo "<style>
#weather_panel {
	font-weight:normal;
	font-size:0.9em;
}
.weather_header {
	font-weight:bold;
	font-size:1em;
}
</style>";
# Part 1 (get latitude & longitude)

/*
$api_1 = 'https://ipapi.co/' . $ip . '/latlong/';

$location = file_get_contents($api_1);
$point = explode(",", $location);
*/
$api_key = 'df1541ce125a5d09c43fb60161ce0951';
# Part 2 (get weather forecast)
/*
$api_0 = "http://definitive-ip-search.org/lookup.jsp?ip=" . $ip;
$city = file_get_contents($api_0);

$doc = new DOMDocument();
@$html = $doc->loadHTML($city);

$ths = $doc->getElementsByTagName("td");
$arrFields1 = array();
foreach($ths as $cell_index=>$th) {
	if (trim($th->nodeValue) != "") {		
		//echo trim($th->nodeValue) . "<br />\r\n";
		$arrFields1[] = trim($th->nodeValue);
	}
}

$city = $arrFields1[10];
$zip = $arrFields1[12];
*/

if ($ip_record->city=="" || $ip_record->city=="Not Available") {
	$api_0 = "https://www.iplocation.net?query=" . $ip;
	$city = file_get_contents($api_0);
	//die($city);
	$doc = new DOMDocument();
	@$html = $doc->loadHTML($city);
	// die(print_r($doc));
	
	$ths = $doc->getElementsByTagName("td");
	$arrFields0 = array();
	foreach($ths as $cell_index=>$th) {
		if (trim($th->nodeValue) != "") {		
			//echo trim($th->nodeValue) . "<br />\r\n";
			$arrFields0[] = trim($th->nodeValue);
		}
	}
	$state = $arrFields0[count($arrFields0)-6];
	$city = $arrFields0[count($arrFields0)-5];
	
	$sql_delete = "";
	if ($city=="Not Available") {
		$sql_delete = "DELETE FROM `ikase`.`cse_ip_location` WHERE ip_address = '" . $ip . "'";
		$state = $arrFields0[2];
		$city = $arrFields0[3];
	}
	$api_1 = 'https://ipapi.co/' . $ip . '/latlong/';

	$location = file_get_contents($api_1);
	$point = explode(",", $location);

	$sql = "INSERT INTO `ikase`.`cse_ip_location` (ip_address, city, state, latitude, longitude)
	SELECT '" . $ip . "', '" . addslashes($city) . "', '" . addslashes($state) . "', '" . $point[0] . "', '" . $point[1] . "'
				FROM dual
				WHERE NOT EXISTS (
					SELECT * 
					FROM `ikase`.`cse_ip_location` 
					WHERE ip_address = '" . $ip . "'
				)";
	try {
		$db = getConnection();
			
		if ($sql_delete!="") {
			$stmt = $db->query($sql_delete);
			$stmt->execute();
		}
		$stmt = $db->query($sql);
		$stmt->execute();
		$stmt = null; $db = null;
	} catch(PDOException $e) {
		$error = array("error2"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}
} else {
	$city = $ip_record->city;
	$state = $ip_record->state;
}

//get the zip
$sql = "SELECT `zip_code` 
FROM `ikase`.`zip_code`
WHERE `city` = '" . addslashes($city) . "'
AND `state_prefix` = '" . $state . "'
LIMIT 0, 1";

//die($sql);

try {
	$db = getConnection();
		
	$stmt = $db->query($sql);
	$zip = $stmt->fetchObject();
	//die(print_r($zip));
	
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	$error = array("error1"=> array("text"=>$e->getMessage()));
	die(json_encode($error));
}
//$api_2 = 'http://api.openweathermap.org/data/2.5/weather?lat=' . $point[0] . '&lon=' . $point[1] . '&appid=' . $api_key;
$api_2 = 'http://api.openweathermap.org/data/2.5/weather?zip=' . $zip->zip_code . '&appid=' . $api_key;
//$weather = file_get_contents($api_2);
//die(print_r(json_decode($weather)));

//$api_2 = 'http://api.openweathermap.org/data/2.5/weather?q=' . urlencode($city) . "," . urlencode($state) . '&appid=' . $api_key;
//die($api_2);
$weather = file_get_contents($api_2);
$weather = json_decode($weather);

$arrWeather = $weather->weather;
$arrConditions = array();
foreach($arrWeather as $the_weather) {
	array_push($arrConditions, $the_weather->main);
}
//T(°F) = T(K) × 9/5 - 459.67
$temperature = $weather->main->temp;
$humidity = $weather->main->humidity;
$temperature = number_format($temperature * (9/5) - 459.67, 0);
$conditions = implode("/", $arrConditions);

//$api_3 = 'http://api.openweathermap.org/data/2.5/forecast?lat=' . $point[0] . '&lon=' . $point[1] . '&appid=' . $api_key;
//$api_3 = 'http://api.openweathermap.org/data/2.5/forecast?zip=' . $zip . '&appid=' . $api_key;
$api_3 = 'http://api.openweathermap.org/data/2.5/forecast?q=' . urlencode($city) . "," . urlencode($state) . '&appid=' . $api_key;	// . "&mode=xml";

$forecast = file_get_contents($api_3);
//die($forecast);
$forecast = json_decode($forecast);
//die(print_r($forecast));

$today = $forecast->list[0];
$today_conditions = $today->weather[0]->main;
$today_max_temperature = $today->main->temp_max;
//$today_max_temperature = number_format($today_max_temperature * (9/5) - 459.67, 0);
$today_max_temperature = kToF($today_max_temperature);

$today_date = date("m/d/Y");
$tomorrow_date = date("m/d/Y", mktime(0, 0, 0, date("m")  , date("d")+1, date("Y")));
//echo $tomorrow_date . "<br />";
$two_date = date("m/d/Y", mktime(0, 0, 0, date("m")  , date("d")+2, date("Y")));
$min_temperature = 500;
$max_temperature = 0;
$tomorrow_humidity = 0;
for($i = 1; $i < count($forecast->list); $i++) {
	
	//today maxes
	if (date("m/d/Y", $forecast->list[$i]->dt) == $today_date) {
		$forecast_max = kToF($forecast->list[$i]->main->temp_max);

		if ($today_max_temperature < $forecast_max) {
			$today_max_temperature = $forecast_max;
		}
	}
	//tommorrow forecast
	if (date("m/d/Y", $forecast->list[$i]->dt) == $tomorrow_date && date("g", $forecast->list[$i]->dt) > 2) {
		$forecast_min = kToF($forecast->list[$i]->main->temp_min);
		$forecast_max = kToF($forecast->list[$i]->main->temp_max);
		$forecast_humidity = $forecast->list[$i]->main->humidity;
		if ($min_temperature > $forecast_min) {
			$min_temperature = $forecast_min;
		}
		if ($max_temperature < $forecast_max) {
			$max_temperature = $forecast_max;
		}
		if ($tomorrow_humidity < $forecast_humidity) {
			$tomorrow_humidity = $forecast_humidity;
		}
	}
	if (date("m/d/Y", $forecast->list[$i]->dt) == $two_date) {
		break;
	}
}
$i--;
//echo $i . "<br />";
$tomorrow = $forecast->list[$i];
//die($i . "<br />"  . print_r($tomorrow));
$tomorrow_min_temperature = $min_temperature;
$tomorrow_max_temperature = $max_temperature;
$tomorrow_temps = "low of " . $tomorrow_min_temperature . "&#176;F rising to " . $tomorrow_max_temperature . "&#176;F";
if ($tomorrow_min_temperature == $tomorrow_max_temperature) {
	$tomorrow_temps = $tomorrow_min_temperature . "&#176;F";
}
$tomorrow_humid = $tomorrow_humidity;
$tomorrow_conditions = $tomorrow->weather[0]->main;
$early_conditions = $forecast->list[0]->weather[0]->main;

echo "<div id='weather_panel'><br />";
echo "<span class='weather_header'>Weather Conditions for " . $city . ":</span><br />";
echo $temperature . "&#176;F";
if ($temperature!=$today_max_temperature) {
	echo ", rising to " . $today_max_temperature . "&#176;F<br />";
} else {
	echo ", ";
}
echo $humidity . "% RH<br />";
echo $conditions;
if ($conditions!=$today_conditions) {
	echo ", later " . $today_conditions;
}

if ($early_conditions!=$tomorrow_conditions) {
	$tomorrow_conditions = $early_conditions . " changing to " . $tomorrow_conditions . "<br />";
	$tomorrow_temps = ucfirst($tomorrow_temps);
} else {
	$tomorrow_conditions .=  ", ";
}

echo "<br /><br /><span class='weather_header'>Tomorrow:</span><br />" . $tomorrow_conditions . $tomorrow_temps . "<br />" . $tomorrow_humid . "% RH<br />";
die("</div>");
?>