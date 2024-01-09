<?php
include("connection.php");

try{
	$sql = "SELECT DISTINCT *
	FROM `cse_customer` csc
		WHERE customer_id != 1033
		ORDER BY csc.customer_id ASC";

	$allcus = DB::select($sql);
	foreach($allcus as $cus) {
		$lasttwo = substr($cus->customer_id, 2, 2);
		$sql = "INSERT INTO `cse_calendar` (`calendar_uuid`, `calendar`, `sort_order`, `customer_id`, `mandatory`, `active`, `deleted`)
		SELECT REPLACE(`calendar_uuid`, 'KS', '" . $lasttwo . "') `calendar_uuid`, `calendar`, `sort_order`, " . $cus->customer_id . ", `mandatory`, `active`, `deleted` FROM `cse_calendar` WHERE customer_id = 1033 AND mandatory = 'Y'";
		echo $sql . "<br />";
		DB::run($sql);
	}
} catch(PDOException $e) {
    echo json_encode(["error" => ["text" => $e->getMessage()]]);
}

