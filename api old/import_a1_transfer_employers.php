<?php

$sql = "INSERT INTO ikase_barsoum.cse_corporation 
(
`corporation_uuid`,
`parent_corporation_uuid`,
`full_name`,
`company_name`,
`type`,
`first_name`,
`last_name`,
`aka`,
`preferred_name`,
`employee_phone`,
`employee_cell`,
`employee_fax`,
`employee_email`,
`full_address`,
`additional_addresses`,
`longitude`,
`latitude`,
`street`,
`city`,
`state`,
`zip`,
`suite`,
`company_site`,
`phone`,
`email`,
`fax`,
`ssn`,
`dob`,
`salutation`,
`copying_instructions`,
`last_updated_date`,
`last_update_user`,
`deleted`,
`customer_id`,
`phone_ext`,
`comments`,
`fee`,
`report_number`,
`officer`,
`date`,
`party_type_option`,
`party_representing_id`,
`party_representing_name`,
`party_defendant_option`,
`kai_info`)

SELECT 
bcorp.`corporation_uuid`,
bcorp.`parent_corporation_uuid`,
bcorp.`full_name`,
bcorp.`company_name`,
bcorp.`type`,
bcorp.`first_name`,
bcorp.`last_name`,
bcorp.`aka`,
bcorp.`preferred_name`,
bcorp.`employee_phone`,
bcorp.`employee_cell`,
bcorp.`employee_fax`,
bcorp.`employee_email`,
bcorp.`full_address`,
bcorp.`additional_addresses`,
bcorp.`longitude`,
bcorp.`latitude`,
bcorp.`street`,
bcorp.`city`,
bcorp.`state`,
bcorp.`zip`,
bcorp.`suite`,
bcorp.`company_site`,
bcorp.`phone`,
bcorp.`email`,
bcorp.`fax`,
bcorp.`ssn`,
bcorp.`dob`,
bcorp.`salutation`,
bcorp.`copying_instructions`,
bcorp.`last_updated_date`,
bcorp.`last_update_user`,
bcorp.`deleted`,
bcorp.`customer_id`,
bcorp.`phone_ext`,
bcorp.`comments`,
bcorp.`fee`,
bcorp.`report_number`,
bcorp.`officer`,
bcorp.`date`,
bcorp.`party_type_option`,
bcorp.`party_representing_id`,
bcorp.`party_representing_name`,
bcorp.`party_defendant_option`,
bcorp.`kai_info`
FROM barsoum.barsoum_corporation bcorp
LEFT OUTER JOIN ikase_barsoum.cse_corporation corp
ON bcorp.corporation_uuid = corp.corporation_uuid
WHERE bcorp.parent_corporation_uuid LIKE 'ED%'
AND corp.corporation_uuid IS NULL;

INSERT INTO `ikase_barsoum`.`cse_case_corporation`
(
`case_corporation_uuid`,
`case_uuid`,
`corporation_uuid`,
`injury_uuid`,
`attribute`,
`last_updated_date`,
`last_update_user`,
`deleted`,
`customer_id`)
SELECT 
bcorp.`case_corporation_uuid`,
bcorp.`case_uuid`,
bcorp.`corporation_uuid`,
bcorp.`injury_uuid`,
bcorp.`attribute`,
bcorp.`last_updated_date`,
bcorp.`last_update_user`,
bcorp.`deleted`,
bcorp.`customer_id`
FROM barsoum.barsoum_case_corporation bcorp
LEFT OUTER JOIN ikase_barsoum.cse_case_corporation corp
ON bcorp.corporation_uuid = corp.corporation_uuid AND bcorp.case_uuid = corp.case_uuid
WHERE bcorp.corporation_uuid LIKE 'EM%'
AND corp.case_corporation_uuid IS NULL

";
