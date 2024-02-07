<?php
include("../../eamsjetfiler/datacon.php");
include("../../eamsjetfiler/functions.php");

$search_term = passed_var("query");
$type = passed_var("type");
if (strlen($search_term) < 3) { //FIXME: probably used wrong variable ($query), so I fixed
	die("||||||||");
}
$resultall = DB::runOrDie("SELECT `eams_ref_number`, `firm_name`, `street_1`, `street_2`, ecarr.`city`, ecarr.`state`, ecarr.`zip_code`, `phone`, `service_method`, `last_update`
	FROM `tbl_eams_" . $type . "` ecarr
	WHERE 1 AND firm_name LIKE '%" . $search_term . "%'
	OR `eams_ref_number` LIKE '%" . $search_term . "%'
	OR `street_1` LIKE '%" . $search_term . "%'
	OR `street_2` LIKE '%" . $search_term . "%'
	OR `zip_code` = '" . $search_term . "'
	OR `phone` LIKE '%" . $search_term . "%'");

$arrRows = [];
while ($row = $resultall->fetch()) {
    $arrRows[] = "{$row->eams_ref_number}|{$row->firm_name}|{$row->street_1}|{$row->street_2}|{$row->city}|{$row->state}|{$row->zip_code}|{$row->phone}";
}
echo implode("\n", $arrRows);
exit();
