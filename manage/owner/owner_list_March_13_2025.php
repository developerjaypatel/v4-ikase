<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

include ("../../text_editor/ed/functions.php");
include ("../../text_editor/ed/datacon.php");

$admin_client = passed_var("admin_client");
$owner_id = passed_var("owner_id");

$query = "SELECT `owner_id`, `admin_client`, `name`, `owner_email`, `url`, `password`, `pwd`, `session_id`, `dateandtime`
FROM cse_owner own
WHERE 1 
AND `admin_client` != 'nick'
ORDER BY `name`";

while ($row = DB::runOrDie($query)->fetch()) { print_r($row);
	$arrRows[] = "{$row->owner_id}|{$row->admin_client}|{$row->name}|{$row->owner_email}|".date("m/d/y h:iA", strtotime($row->dateandtime));
}
echo implode("\n", $arrRows);
exit();
