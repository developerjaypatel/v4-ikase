<?php
include("../../eamsjetfiler/datacon.php");
include("../../eamsjetfiler/functions.php");

$cus_id = passed_var("cus_id");
$administrator = passed_var("administrator");

$query = "DELETE FROM customer_administrator WHERE customer_id = " . $cus_id;
DB::runOrDie($query);
if ($administrator!="") {
	$query = "INSERT INTO customer_administrator (`customer_id`, `administrator_id`, `attribute`)
	VALUES ('" . $cus_id . "','" . $administrator . "','main')";
	DB::runOrDie($query);
echo "marketer set";
} else {
	echo "marketer unset";
}
die();
