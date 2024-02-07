<?php
include("../../eamsjetfiler/datacon.php");
include("../../eamsjetfiler/functions.php");

$cus_id = passed_var("cus_id");
$administrator = passed_var("administrator");

$query = "DELETE FROM customer_administrator 
WHERE customer_id = " . $cus_id;
$result = mysql_query($query, $r_link) or die("unable to clear customer marketer<br>" . $query);

if ($administrator!="") {
	$query = "INSERT INTO customer_administrator (`customer_id`, `administrator_id`, `attribute`)
	VALUES ('" . $cus_id . "','" . $administrator . "','main')";
	$result = mysql_query($query, $r_link) or die("unable to clear customer marketer<br>" . $query);
	echo "marketer set";
} else {
	echo "marketer unset";
}
mysql_close($r_link);
//die($query);
die();
?>