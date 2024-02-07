<?php
include("../../../api/manage_session.php");
session_write_close();

//include("sec.php");

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

include ("../../../text_editor/ed/functions.php");
include ("../../../text_editor/ed/datacon_rek.php");

$customer = passed_var("keyword");

$query = "SELECT  cus.`customer_id`, cus.`cus_name`
FROM `rek`.rek_customer cus

WHERE 1 AND cus.cus_name LIKE '%" . $customer . "%'
 AND cus.deleted = 'N'";
$query .= " ORDER BY `cus_name`";
//
$result = mysql_query($query, $r_link) or die("unable to run query<br />" .$query . "<br>" .  mysql_error());
$numbs = mysql_numrows($result);
//die($query . "<br />" . $numbs);

$arrRows = array();
for ($int=0;$int<$numbs;$int++) {
	$cus_id = mysql_result($result, $int, "customer_id");
	$cus_name = mysql_result($result, $int, "cus_name");
	$cus_name = strtolower($cus_name);
	$cus_name = ucwords($cus_name);
	$cus_name = str_replace(" ", "&nbsp;", $cus_name);
	
	$the_row = "
	<tr>
		<td align='left' valign='top'><a href='editor.php?cus_id=" . $cus_id . "'>". $cus_name . "</td>
		<td>&nbsp;|&nbsp;</td>
		<td align='left' valign='top'><a href='../users/index.php?session_id=" . $_SESSION["user"] . "&cus_id=" . $cus_id . "'>users</td>
	</tr>";
	$arrRows[] = $the_row;
}
mysql_close($r_link);
$maincontent = "<table>" . implode("\n", $arrRows) . "</table>";
echo $maincontent;
exit();
?>