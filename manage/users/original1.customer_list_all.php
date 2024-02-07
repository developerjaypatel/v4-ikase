<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>
<?php
include("../eamsjetfiler/datacon.php");
include("../eamsjetfiler/functions.php");

$query = "SELECT  `cus_id`, `password`, `pwd` FROM tbl_customer";
$result = mysql_query($query, $r_link) or die("unable to run query<br />" .$query . "<br>" .  mysql_error());
$numbs = mysql_numrows($result);

for ($int=0;$int<$numbs;$int++) {
	$cus_id = mysql_result($result, $int, "cus_id");
	$password = mysql_result($result, $int, "password");
	$pwd = mysql_result($result, $int, "pwd");

	if ($pwd=="") {
		$pwd = encrypt($password, $crypt_key);
	}
	$queryupdate = "UPDATE tbl_customer SET `pwd` = '" . $pwd . "' WHERE cus_id = '" . $cus_id . "'";
	$resultupdate = mysql_query($queryupdate, $r_link) or die("unable to run query<br />" .$queryupdate . "<br>" .  mysql_error());
	//die($query);
}
mysql_close($r_link);
echo "done";
exit();
?>