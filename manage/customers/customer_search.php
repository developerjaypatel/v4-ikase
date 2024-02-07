<?php
require_once('../../shared/legacy_session.php');
session_write_close();

include("sec.php");

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

include ("../../text_editor/ed/functions.php");
include ("../../text_editor/ed/datacon.php");

$customer = passed_var("keyword");

$query = "SELECT  cus.`customer_id`, cus.`cus_name`
FROM `ikase`.cse_customer cus

WHERE 1 AND cus.cus_name LIKE '%" . $customer . "%'
 AND cus.deleted = 'N'";
$query .= " ORDER BY `cus_name`";
$result = DB::runOrDie($query);

$arrRows = array();
while ($row = $result->fetch()) {
	$cus_id = $row->customer_id;
	$cus_name = $row->cus_name;
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
echo "<table>" . implode("\n", $arrRows) . "</table>";
exit();
