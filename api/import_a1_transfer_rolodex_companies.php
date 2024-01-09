<?php
require_once('../shared/legacy_session.php');

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");	
	
    DB::delete("ikase_{$data_source}.cse_corporation", ['last_update_user' => 'import']);

	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation` (
    `corporation_uuid`, `parent_corporation_uuid`, `full_name`, `company_name`, `type`, `first_name`, `last_name`, `aka`, `preferred_name`, 
	`employee_phone`, `employee_cell`, `employee_fax`, `employee_email`, 
	`full_address`, `additional_addresses`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, 
	`company_site`, `phone`, `email`, `fax`, `ssn`, `dob`, `salutation`, `copying_instructions`, `last_updated_date`, `last_update_user`, 
	`deleted`, `customer_id`, `phone_ext`, `comments`, `fee`, `report_number`, `officer`, `date`, `party_type_option`, `party_representing_id`, `party_representing_name`, `party_defendant_option`, `kai_info`
	)
	SELECT dc.`corporation_uuid`, dc.`parent_corporation_uuid`, dc.`full_name`, dc.`company_name`, dc.`type`, dc.`first_name`, dc.`last_name`, dc.`aka`, dc.`preferred_name`, dc.`employee_phone`, dc.`employee_cell`, dc.`employee_fax`, dc.`employee_email`, dc.`full_address`, dc.`additional_addresses`, dc.`longitude`, dc.`latitude`, dc.`street`, dc.`city`, dc.`state`, dc.`zip`, dc.`suite`, dc.`company_site`, dc.`phone`, dc.`email`, dc.`fax`, dc.`ssn`, dc.`dob`, dc.`salutation`, dc.`copying_instructions`, dc.`last_updated_date`, dc.`last_update_user`, dc.`deleted`, dc.`customer_id`, dc.`phone_ext`, dc.`comments`, dc.`fee`, dc.`report_number`, dc.`officer`, dc.`date`, dc.`party_type_option`, dc.`party_representing_id`, dc.`party_representing_name`, dc.`party_defendant_option`, dc.`kai_info`
	FROM " . $data_source . "." . $data_source . "_corporation dc
	LEFT OUTER JOIN " . $data_source . "." . $data_source . "_case_corporation dcc
	ON dc.corporation_uuid = dcc.corporation_uuid
	LEFT OUTER JOIN ikase_" . $data_source . ".cse_corporation cc
	ON dc.corporation_uuid = cc.corporation_uuid
	WHERE 1
	AND dcc.case_uuid IS NULL
	AND cc.corporation_uuid IS NULL
	AND dc.corporation_uuid = dc.parent_corporation_uuid
	AND dc.last_update_user = 'import'
	ORDER BY dc.corporation_id DESC";
	DB::run($sql);

	$sql = "UPDATE ikase_" . $data_source . ".cse_corporation
	SET company_name = full_name
	WHERE last_update_user = 'import'
	AND company_name = ''
	AND full_name != ''";
    DB::run($sql);

    //FIXME: those jsons written out of nowhere doesn't make much sense with the <script> below?
    echo json_encode(["success" => ["text" => "done @".date("H:i:s")]]);
}
catch (PDOException $e) {
    echo json_encode(["error" => ["text" => $e->getMessage()]]);
}
include("cls_logging.php");
?>
<script language="javascript">
parent.setFeedback("partie types transfer completed");
</script>
