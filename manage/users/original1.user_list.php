<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>
<?php
include ("../../text_editor/ed/functions.php");
include ("../../text_editor/ed/datacon.php");

$cus_id = passed_var("cus_id");
//die($cus_id);
$query = "SELECT  `user_id`,`user_name`, `user_email`, `nickname`, `pwd`, `level`, `job`, `user_logon`, `deleted`, `activated`
FROM `ikase`.cse_user 
WHERE 1 AND deleted = 'N' AND customer_id = '" . $cus_id . "'";
//die($query);
$result = mysql_query($query, $r_link) or die("unable to run query<br />" .$query . "<br>" .  mysql_error());
$numbs = mysql_numrows($result);

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
for ($int=0;$int<$numbs;$int++) {
	$user_id = mysql_result($result, $int, "user_id");
	$user_name = mysql_result($result, $int, "user_name");
	$activated = mysql_result($result, $int, "activated");
	$user_email = mysql_result($result, $int, "user_email");
	$nickname = mysql_result($result, $int, "nickname");
	if ($nickname=="" && $user_id !="") {
		$nickname = firstLetters($user_name);
		$queryupdate = "UPDATE cse_user SET `nickname` = '" . $nickname . "' WHERE user_id = " . $user_id;
		$resultupdate = mysql_query($queryupdate, $r_link) or die("unable to run query<br />" .$queryupdate . "<br>" .  mysql_error());
	}
	$user_name = ucwords(strtolower($user_name));
	$user_logon = mysql_result($result, $int, "user_logon");
	$pwd = mysql_result($result, $int, "pwd");
	$level = mysql_result($result, $int, "level");
	$job = mysql_result($result, $int, "job");
	$deleted = mysql_result($result, $int, "deleted");
	$password = "";
	$the_row = $user_id . "|". $user_name . "|" . $user_email . "|" . $password . "|" . $level . "|" . $job . "|" . $user_logon . "|" . $pwd . "|" . $nickname . "|" . $deleted . "|" . $activated;
	
	//die($the_row);
	//$the_row = str_replace(" ", "&nbsp;", $the_row);
	$arrRows[] = $the_row;
}
mysql_close($r_link);
$maincontent = implode("\n", $arrRows);
echo $maincontent;
exit();
?>