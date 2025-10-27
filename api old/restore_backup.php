


UPDATE cse_corporation corp
INNER JOIN cse_corporation_ks track
ON corp.corporation_uuid = track.corporation_uuid
SET corp.`full_name` = track.`full_name`,
    corp.`company_name` = track.`company_name`,
    corp.`type` = track.`type`,
    corp.`first_name` = track.`first_name`,
    corp.`last_name` = track.`last_name`,
    corp.`aka` = track.`aka`,
    corp.`preferred_name` = track.`preferred_name`,
    corp.`employee_phone` = track.`employee_phone`,
    corp.`employee_fax` = track.`employee_fax`,
    corp.`employee_email` = track.`employee_email`,
	corp.`full_address` = track.`full_address`,
    corp.`longitude` = track.`longitude`,
    corp.`latitude` = track.`latitude`,
    corp.`street` = track.`street`,
    corp.`city` = track.`city`,
    corp.`state` = track.`state`,
    corp.`zip` = track.`zip`,
    corp.`suite` = track.`suite`,
    corp.`company_site` = track.`company_site`,
    corp.`phone` = track.`phone`,
    corp.`email` = track.`email`,
    corp.`fax` = track.`fax`,
    corp.`ssn` = track.`ssn`,
    corp.`dob` = track.`dob`,
    corp.`salutation` = track.`salutation`,
    corp.`copying_instructions` = track.`copying_instructions`,
    corp.`last_updated_date` = track.`last_updated_date`,
    corp.`last_update_user` = track.`last_update_user`
  WHERE 