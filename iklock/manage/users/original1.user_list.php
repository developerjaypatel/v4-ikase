<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("../../api/manage_session.php");
session_write_close();

include("../customers/sec.php");

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

include ("../../api/connection.php");

$cus_id = passed_var("cus_id", "GET");
//die($cus_id);
$query = "SELECT  `user_id`,`user_name`, `user_email`, `nickname`, `pwd`, `level`, `job`, `user_logon`, `deleted`, `activated`
FROM `iklock`.user 
WHERE 1 AND deleted = 'N' AND customer_id = '" . $cus_id . "'";
//die($query);
try {
	$db = getConnection();
	$stmt = $db->prepare($query);  
	//$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$users = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	die();
}

//die(print_r($users));

$arrRows = array();
foreach($users as $user) {
	$user_id = $user->user_id;
	$user_name = $user->user_name;
	$activated = $user->activated;
	$user_email = $user->user_email;
	$nickname = $user->nickname;
	if ($nickname=="" && $user_id !="") {
		$nickname = firstLetters($user_name);
		$queryupdate = "UPDATE cse_user SET `nickname` = '" . $nickname . "' WHERE user_id = " . $user_id;
		$resultupdate = mysql_query($queryupdate, $r_link) or die("unable to run query<br />" .$queryupdate . "<br>" .  mysql_error());
	}
	$user_name = ucwords(strtolower($user_name));
	$user_logon = $user->user_logon;
	$pwd = $user->pwd;
	$level = $user->level;
	$job = $user->job;
	$deleted = $user->deleted;
	$password = "";
	$the_row = $user_id . "|". $user_name . "|" . $user_email . "|" . $password . "|" . $level . "|" . $job . "|" . $user_logon . "|" . $pwd . "|" . $nickname . "|" . $deleted . "|" . $activated;
	
	$the_row = "
	<tr>
		<td align='left' valign='top'>
			<a id='edit_user_" . $user_id . "' class='edit_user'>" . $user_name . "</a>
		</td>
		<td align='left' valign='top'>
			<a id='login_user_" . $user_id . "' class='login_user'>Login</a>
		</td>
	</tr>
	";
	$arrRows[] = $the_row;
}
$maincontent = implode("\n", $arrRows);
echo "
<table>
	<tr>
		<th align='left' valign='top'>User</th>
	</tr>
	" . $maincontent . "
</table>";
exit();
?>