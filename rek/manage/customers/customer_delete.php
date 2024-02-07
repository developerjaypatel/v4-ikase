<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>
<?php
include ("../../../text_editor/ed/functions.php");
include ("../../../text_editor/ed/datacon_rek.php");

$cus_id = passed_var("cus_id", "post");

$query = "UPDATE rek_customer 
SET deleted = 'Y'
WHERE customer_id = " . $cus_id;
//die($query);
$result = mysql_query($query, $r_link) or die("unable to clear cus");
echo $cus_id . " deleted";
exit(); 
?>