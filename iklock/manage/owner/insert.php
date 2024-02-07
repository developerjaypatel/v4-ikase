<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>
<?php
include("../../eamsjetfiler/datacon.php");
include("../../eamsjetfiler/functions.php");

die(encrypt('Access1118#', $crypt_key));
die(encrypt('Tommy1!', $crypt_key));

die(md5('Nick10!'));
die(encrypt('Andrea10#', $crypt_key));
die(encrypt('Nick10!', $crypt_key));
die(encrypt('Matt202!', $crypt_key));

if ($owner_id =="") {
	$query = "INSERT INTO tbl_owner (`admin_client`, `name`, `owner_email`, `url`, `password`, `pwd`) VALUES ('" . $user_logon . "', '" . $user_name . "', '" . $user_email . "', 'dmsroi.com', '', '" .  encrypt($user_logon, $crypt_key) . "')";
} else {
	$query = "UPDATE tbl_owner";
}
die($query);

DB::runOrDie($query);
echo "done";
exit();
