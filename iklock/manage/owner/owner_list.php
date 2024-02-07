<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>
<?php
include ("../api/connection.php");

$admin_client = passed_var("admin_client");
$owner_id = passed_var("owner_id");

$query = "SELECT `owner_id`, `admin_client`, `name`, `owner_email`, `url`, `password`, `pwd`, `session_id`, `dateandtime`
FROM cse_owner own
WHERE 1 
AND `admin_client` != 'nick'
ORDER BY `name`";

try {
	$owners = DB::select($sql);

	echo json_encode($debtors);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}

foreach($owners as $owner) {
	$owner_id = $owner->owner_id;
	$admin_client = $owner->admin_client;
	$name = $owner->name;
	$owner_email = $owner->owner_email;
	$dateandtime = $owner->dateandtime;
	$the_row = $owner_id . "|". $admin_client . "|". $name . "|" . $owner_email . "|" . date("m/d/y h:iA", strtotime($dateandtime));
	
	//die($the_row);
	//$the_row = str_replace(" ", "&nbsp;", $the_row);
	$arrRows[] = $the_row;
}
$maincontent = implode("\n", $arrRows);
echo $maincontent;
exit();
?>
