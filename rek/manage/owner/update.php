<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>
<?php
include ("../../../text_editor/ed/functions.php");
include ("../../../text_editor/ed/datacon_rek.php");

$suid = passed_var("suid");

$admin_id = passed_var("admin_id");
$user_logon = passed_var("admin_client");
$name = passed_var("name");
$owner_email = passed_var("owner_email");
$admin_client = passed_var("admin_client");
$password = passed_var("password");
$pwd = "";
if ($password!="") {
	$pwd = encrypt($password, $crypt_key);
}

if ($admin_id=="") {
	$query = "INSERT INTO rek_owner (`admin_client`, `name`, `owner_email`, `url`, `password`, `pwd`) VALUES ('" . $user_logon . "', '" . $name . "', '" . $owner_email . "', 'rek.org', '', '" .  $pwd . "')";
	$result = mysql_query($query, $r_link) or die("unable to insert customer<br>" . mysql_error());
	$admin_id = mysql_insert_id($r_link);
} else {
	$query = "UPDATE rek_owner
	SET admin_client = '" . $admin_client . "',
	name = '" . addslashes($name) . "',
	owner_email= '" . addslashes($owner_email) . "'";
	if($pwd!="") {
		$query .= ", `pwd` = '" . addslashes($pwd) . "'";
	}
	$query .= " WHERE owner_id = " . $admin_id;
	$result = mysql_query($query, $r_link) or die("unable to update customer<br>" . $query);
}

//die($query);
//default for now
$blnReturnEditor = true;

if ($blnReturnEditor) {
	header("location:editor.php?admin_client=" . $admin_client . "&admin_id=" . $admin_id. "&suid=" . $suid);
} else {
	header("location:index.php?admin_client=" . $admin_client . "&suid=" . $suid);
}
?>