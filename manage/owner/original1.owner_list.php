<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>
<?php
include ("../../text_editor/ed/functions.php");
include ("../../text_editor/ed/datacon.php");

$admin_client = passed_var("admin_client");
$owner_id = passed_var("owner_id");

$query = "SELECT `owner_id`, `admin_client`, `name`, `owner_email`, `url`, `password`, `pwd`, `session_id`, `dateandtime`
FROM cse_owner own
WHERE 1 
AND `admin_client` != 'nick'
ORDER BY `name`";

$result = mysql_query($query, $r_link) or die("unable to run query<br />" .$sql . "<br>" .  mysql_error());
$numbs = mysql_numrows($result);

for ($int=0;$int<$numbs;$int++) {
	$owner_id = mysql_result($result, $int, "owner_id");
	$admin_client = mysql_result($result, $int, "admin_client");
	$name = mysql_result($result, $int, "name");
	$owner_email = mysql_result($result, $int, "owner_email");
	$dateandtime = mysql_result($result, $int, "dateandtime");
	$the_row = $owner_id . "|". $admin_client . "|". $name . "|" . $owner_email . "|" . date("m/d/y h:iA", strtotime($dateandtime));
	
	//die($the_row);
	//$the_row = str_replace(" ", "&nbsp;", $the_row);
	$arrRows[] = $the_row;
}
mysql_close($r_link);
$maincontent = implode("\n", $arrRows);
echo $maincontent;
exit();
?>