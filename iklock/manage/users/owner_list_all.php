<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

include("../eamsjetfiler/datacon.php");
include("../eamsjetfiler/functions.php");

$query = "SELECT  `owner_id`, `password`, `pwd` FROM tbl_owner";
$result = DB::runOrDie($query);

while ($row = $result->fetch()) {
	$owner_id = $row->owner_id;
	$password = $row->password;
	$pwd = $row->pwd;

	//if ($pwd=="") {
		$pwd = encrypt($password, $crypt_key);
	//}
	DB::runOrDie("UPDATE tbl_owner SET `pwd` = '" . $pwd . "' WHERE owner_id = '" . $owner_id . "'");
}
echo "done";
exit();
