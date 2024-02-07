<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

include ("../../text_editor/ed/functions.php");
include ("../../text_editor/ed/datacon.php");

function firstLetters($string) {
	$words = explode(" ", $string);
	//die(print_r($words));
	$acronym = "";
	$codes = "";

	foreach ($words as $w) {
		$letter = substr($w, 0, 1);
		$start = 1;
		while (ord($letter)==194 || ord($letter)==160) {
			$letter = substr($w, $start, 1);
			$start++;
		}
		$acronym .= $letter;
	}
	return $acronym;
}

$cus_id = passed_var("cus_id");
//die($cus_id);
$result = DB::runOrDie("SELECT  `user_id`,`user_name`, `user_email`, `nickname`, `pwd`, `level`, `job`, `user_logon`, `deleted`, `activated`
FROM `ikase`.cse_user 
WHERE deleted = 'N' AND customer_id = $cus_id", [$cus_id]);

$arrRows = [];
while ($row = $result->fetch()) {
	$nickname = $row->nickname;
	$user_name = ucwords(strtolower($row->user_name));
	$password = "";

	if ($nickname=="" && $row->user_id !="") {
		$nickname = firstLetters($user_name);
		DB::runOrDie("UPDATE cse_user SET `nickname` = '" . $nickname . "' WHERE user_id = " .$row->user_id);
	}

	$arrRows[] =
		"{$row->user_id}|{$user_name}|{$row->user_email}|{$password}|{$row->level}|{$row->job}|{$row->user_logon}|{$row->pwd}|{$nickname}|{$row->deleted}|{$row->activated}";
}
echo implode("\n", $arrRows);
exit();
