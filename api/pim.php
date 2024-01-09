<?php

die("&#132;&Aring;&brvbar;&Aring;");

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('../shared/legacy_session.php');
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;
$last_updated_date = date("Y-m-d H:i:s");

include("connection.php");

//venues
	
try {
	$db = getConnection();
		
	$sql = "SELECT *
	FROM ikase_pimentel.cse_notes
	ORDER BY notes_id DESC
	LIMIT 0, 500";
	//echo $sql . "\r\n<br>";

	$sql = "SELECT * FROM ikase_pimentel.cse_task
	ORDER BY task_id DESC
	LIMIT 0, 500";
	$notes = DB::select($sql);
	
	$arrRows = array();
	foreach($notes as $note) {
		//$arrRows[] = "<tr><td valign='top'>" . $note->notes_id .  "</td><td>" . $note->note . "</td></tr>";
		$arrRows[] = "<tr><td valign='top'>" . $note->task_id .  "</td><td>" . $note->task_description . "</td></tr>";
	}
	
	echo "<table border='1'>" . implode("\r\n", $arrRows) . "</table>";
} catch(PDOException $e) {
	echo $sql . "\r\n<br>";
	$error = array("error"=> array("text"=>$e->getMessage()));
	die( json_encode($error));
}

die();

$arr = array("&Atilde;", "&#131;", "&AElig;", "&#146;", "&Atilde;", "&#134;", "&acirc;", "&#128;", "&#153;", "&Atilde;", "&#131;", "&Acirc;", "&cent;", "&Atilde;", "&cent;", "&acirc;", "&#128;", "&#154;", "&Acirc;", "&not;", "&Atilde;", "&#133;", "&Acirc;", "&iexcl;", "&Atilde;", "&#131;", "&acirc;", "&#128;", "&#154;", "&Atilde;", "&#130;");

$arr = array_unique($arr);

echo '"' . implode('","', $arr) . '"';
