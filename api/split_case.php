<?php
die();
$sql = "INSERT INTO `ikase_pag`.`cse_case`
(
`case_uuid`, `case_number`, `file_number`, `cpointer`, `case_name`, `source`, `adj_number`, `case_date`, `terminated_date`, `case_type`, `injury_type`, `venue`, `dois`, `case_status`, `case_substatus`, `case_subsubstatus`, `rating`, `submittedOn`, `supervising_attorney`, `attorney`, `worker`, `medical`, `td`, `rehab`, `edd`, `claims`, `interpreter_needed`, `file_location`, `case_language`, `lien_filed`, `sub_in`, `special_instructions`, `case_description`, `customer_id`, `closed`, `deleted`)

SELECT 
   REPLACE(`case_uuid`, 'KS', 'DP'),
    '4227B', '4227B', `cse_case`.`cpointer`, CONCAT(`cse_case`.`case_name`, ' B') `case_name`, `cse_case`.`source`, `cse_case`.`adj_number`, `cse_case`.`case_date`, `cse_case`.`terminated_date`, `cse_case`.`case_type`, `cse_case`.`injury_type`, `cse_case`.`venue`, `cse_case`.`dois`, `cse_case`.`case_status`, `cse_case`.`case_substatus`, `cse_case`.`case_subsubstatus`, `cse_case`.`rating`, `cse_case`.`submittedOn`, `cse_case`.`supervising_attorney`, `cse_case`.`attorney`, `cse_case`.`worker`, `cse_case`.`medical`, `cse_case`.`td`, `cse_case`.`rehab`, `cse_case`.`edd`, `cse_case`.`claims`, `cse_case`.`interpreter_needed`, `cse_case`.`file_location`, `cse_case`.`case_language`, `cse_case`.`lien_filed`, `cse_case`.`sub_in`, `cse_case`.`special_instructions`, `cse_case`.`case_description`, `cse_case`.`customer_id`, `cse_case`.`closed`, `cse_case`.`deleted`
FROM ikase_pag.cse_case
WHERE case_id = 3548;";

echo $sql . "\r\n";

$sql = "UPDATE ikase_pag.cse_case_injury
SET case_uuid = REPLACE(`case_uuid`, 'KS', 'DP')
WHERE injury_uuid = 'KS599f0690cb8a7'";
echo $sql . "\r\n";

$arrTables = array("activity", "document", "letter", "message", "notes", "person", "task", "venue", "event");

foreach ($arrTables as $table) {
	$sql = "INSERT INTO `ikase_pag`.`cse_case_" . $table . "`
	(
	`case_" . $table . "_uuid`, `case_uuid`, `" . $table . "_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
	SELECT REPLACE(`case_" . $table . "_uuid`, 'KS', 'DP'), 
	REPLACE(`case_uuid`, 'KS', 'DP'),
	`" . $table . "_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`
	FROM ikase_pag.cse_case_" . $table . "
	WHERE case_uuid = 'KS58f1883640327';";
	echo $sql . "\r\n";
}

