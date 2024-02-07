<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

include("../eamsjetfiler/datacon.php");
include("../eamsjetfiler/functions.php");

$query = "SELECT  `user_id`, `password`, `pwd` FROM tbl_user";
$result = DB::runOrDie($query);

while ($row = $result->fetch()) {
	$user_id = $row->user_id;
	$password = $row->password;
	$pwd = $row->pwd;

	//if ($pwd=="") {
		$pwd = encrypt($password, $crypt_key);
	//}
	DB::runOrDie("UPDATE tbl_user SET `pwd` = '" . $pwd . "' WHERE user_id = '" . $user_id . "'");
}
echo "done";
exit();
