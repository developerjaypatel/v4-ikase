<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

include("../eamsjetfiler/datacon.php");
include("../eamsjetfiler/functions.php");

$query = "SELECT  `cus_id`, `password`, `pwd` FROM tbl_customer";
$result = DB::runOrDie($query);

while ($row = $result->fetch()) {
	$cus_id = $row->cus_id;
	$password = $row->password;
	$pwd = $row->pwd;

	if ($pwd=="") {
		$pwd = encrypt($password, $crypt_key);
	}
	$queryupdate = "UPDATE tbl_customer SET `pwd` = '" . $pwd . "' WHERE cus_id = '" . $cus_id . "'";
	DB::runOrDie($queryupdate);
}
echo "done";
exit();
