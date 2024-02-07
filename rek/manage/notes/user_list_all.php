<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>
<?php
include("../eamsjetfiler/datacon.php");
include("../eamsjetfiler/functions.php");

$query = "SELECT  `user_id`, `password`, `pwd`
FROM tbl_user";
$result = mysql_query($query, $r_link) or die("unable to run query<br />" .$query . "<br>" .  mysql_error());
$numbs = mysql_numrows($result);

for ($int=0;$int<$numbs;$int++) {
	$user_id = mysql_result($result, $int, "user_id");
	$password = mysql_result($result, $int, "password");
	$pwd = mysql_result($result, $int, "pwd");

	//if ($pwd=="") {
		$pwd = encrypt($password, $crypt_key);
	//}
	$queryupdate = "UPDATE tbl_user SET `pwd` = '" . $pwd . "' WHERE user_id = '" . $user_id . "'";
	$resultupdate = mysql_query($queryupdate, $r_link) or die("unable to run query<br />" .$queryupdate . "<br>" .  mysql_error());
	//die($query);
}
mysql_close($r_link);
echo "done";
exit();
?>