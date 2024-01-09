<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_WARNING);
ini_set('display_errors', '1');

function kToF($temperature) {
	if (!is_numeric($temperature)) {
		return '';
	}
	return number_format($temperature * (9/5) - 459.67, 0);
}

$city = $_REQUEST["city"];
$state = $_REQUEST["state"];

$api_key = 'df1541ce125a5d09c43fb60161ce0951';
$api_2 = 'http://api.openweathermap.org/data/2.5/weather?q=' . urlencode($city) . "," . urlencode($state) . '&appid=' . $api_key;
$weather = file_get_contents($api_2);
$weather = json_decode($weather);

$blnNoData = false;
if (isset($weather->message)) {
	if ($weather->message=="Error: Not found city") {
		$blnNoData = true;
		//die($api_2);
	}
}
//echo $api_2;
//echo "cod:" . $weather->cod . "\r\n";
//die(print_r($weather));
$city_code = $weather->cod;


$arrWeather = $weather->weather;
//die(print_r($arrWeather));

$conditions_icon = "";	//"&nbsp;<img src='https://openweathermap.org/img/w/" . $arrWeather[0]->icon . ".png' />";
$arrConditions = array();
foreach($arrWeather as $the_weather) {
	array_push($arrConditions, $the_weather->main);
}
//T(°F) = T(K) × 9/5 - 459.67
$temperature = $weather->main->temp;
$today_max_temperature = kToF($weather->main->temp_max);
$humidity = $weather->main->humidity;
$temperature = kToF($temperature);
$conditions = implode("/", $arrConditions);

//echo $temperature . " -- " . $humidity . "<br />";

//$api_3 = 'http://api.openweathermap.org/data/2.5/forecast?lat=' . $point[0] . '&lon=' . $point[1] . '&appid=' . $api_key;
//$api_3 = 'http://api.openweathermap.org/data/2.5/forecast?zip=' . $zip . '&appid=' . $api_key;
$api_3 = 'http://api.openweathermap.org/data/2.5/forecast?q=' . urlencode($city) . "," . urlencode($state) . '&appid=' . $api_key;	// . "&mode=xml";
//$api_3 = 'http://api.openweathermap.org/data/2.5/forecast?id=' . $city_code . '&appid=' . $api_key;
//die($api_3);
$forecast = file_get_contents($api_3);
//die($forecast);
$forecast = json_decode($forecast);
//die(print_r($forecast));

$today = $forecast->list[0];

//echo $today->main->temp_max . " - " . $today_max_temperature . "<br />";
$today_date = date("m/d/Y");
$tomorrow_date = date("m/d/Y", mktime(0, 0, 0, date("m")  , date("d")+1, date("Y")));
//echo $tomorrow_date . "<br />";
$two_date = date("m/d/Y", mktime(0, 0, 0, date("m")  , date("d")+2, date("Y")));
$min_temperature = 500;
$max_temperature = 0;
$tomorrow_humidity = 0;
//die(print_r($forecast->list));
if (count($forecast->list) > 0) { 
	for($i = 1; $i < count($forecast->list); $i++) {
		
		if (date("m/d/Y", $forecast->list[$i]->dt) == date("m/d/Y")) {
			$forecast_max = kToF($forecast->list[$i]->main->temp_max);
			if ($today_max_temperature < $forecast_max) {
				$today_max_temperature = $forecast_max;
			}
			//echo $today_conditions . "<br />";
			$today_conditions = ucwords($forecast->list[$i]->weather[0]->description);
		}
		//tommorrow forecast
		//
		if (date("m/d/Y", $forecast->list[$i]->dt) == $tomorrow_date && date("g", $forecast->list[$i]->dt) > 2) {
			$forecast_min = kToF($forecast->list[$i]->main->temp_min);
			$forecast_max = kToF($forecast->list[$i]->main->temp_max);
			//echo date("m/d/Y H:iA", $forecast->list[$i]->dt) . " - ";
			//echo $max_temperature . " - > " . $forecast_max . "<br />";
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
	$early_conditions = $forecast->list[0]->weather[0]->main;
	$tomorrow_conditions = $tomorrow->weather[0]->main;
} else {
	$early_conditions = "";
	$tomorrow_conditions = "";
}
echo "
<div id='weather_panel'>
	<br />";
if (!$blnNoData) {
	echo "<span class='weather_header'>Today's Weather:</span><br />";
	echo "<div style='float:right'>" . $conditions_icon . "</div>";
	echo $temperature . "&#176;F";
	if ($today_max_temperature - $temperature > 2) {
		echo " rising to " . $today_max_temperature . "&#176;F";
	}
	echo "<br />";
	echo $humidity . "% RH<br />";
	
	echo $conditions;
	if ($conditions!=$today_conditions && $today_conditions!="") {
		echo ", later " . $today_conditions;
	}
}
if ($early_conditions!=$tomorrow_conditions) {
	$tomorrow_conditions = $early_conditions . " changing to " . $tomorrow_conditions . "<br />";
	$tomorrow_temps = ucfirst($tomorrow_temps);
} else {
	if ($tomorrow_conditions!="") {
		$tomorrow_conditions .=  ", ";
	}
}
if ($tomorrow_conditions!="") {
	echo "<br /><br /><span class='weather_header'>Tomorrow:</span><br />" . $tomorrow_conditions . $tomorrow_temps . "<br />" . $tomorrow_humid . "% RH<br />";
}
die("</div>");
?>
