<?php
/*
$sql = "SELECT notes_uuid FROM ikase" . $schema . ".cse_notes_track
WHERE dateandtime  > '2017-11-05'
ORDER BY notes_track_id DESC";
*/
$arrSQL = array();

$sql = "INSERT INTO `ikase" . $schema . "3`.`cse_notes`
(
`notes_uuid`, `type`, `subject`, `note`, `title`, `attachments`, `entered_by`, `status`, `dateandtime`, `callback_date`, `verified`, `deleted`, `customer_id`)

SELECT cse_notes.`notes_uuid`, cse_notes.`type`, cse_notes.`subject`, cse_notes.`note`, cse_notes.`title`, cse_notes.`attachments`, cse_notes.`entered_by`, cse_notes.`status`, cse_notes.`dateandtime`, cse_notes.`callback_date`, cse_notes.`verified`, cse_notes.`deleted`, cse_notes.`customer_id` 
FROM ikase" . $schema_from . ".cse_notes
INNER JOIN ikase" . $schema_from . ".cse_notes_track
ON cse_notes.notes_uuid = cse_notes_track.notes_uuid
WHERE cse_notes.dateandtime  > '2017-11-05'";
$arrSQL[] = $sql;

$sql = "INSERT INTO `ikase" . $schema . "3`.`cse_case_notes`
(`case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)

SELECT cse_case_notes.`case_notes_uuid`, cse_case_notes.`case_uuid`, cse_case_notes.`notes_uuid`, cse_case_notes.`attribute`, cse_case_notes.`last_updated_date`, cse_case_notes.`last_update_user`, cse_case_notes.`deleted`, cse_case_notes.`customer_id` 
FROM ikase" . $schema_from . ".cse_case_notes
INNER JOIN ikase" . $schema_from . ".cse_notes_track
ON cse_case_notes.notes_uuid = cse_notes_track.notes_uuid
WHERE cse_notes_track.dateandtime  > '2017-11-05'";
$arrSQL[] = $sql;

$sql = "INSERT INTO `ikase" . $schema . "3`.`cse_activity`
(
`activity_uuid`, `activity`, `activity_category`, `activity_date`, `hours`, `timekeeper`, `initials`, `attorney`, `activity_user_id`, `customer_id`, `deleted`, `activity_status`, `billing_rate`, `billing_date`)

SELECT `activity_uuid`, `activity`, `activity_category`, `activity_date`, `hours`, `timekeeper`, `initials`, `attorney`, `activity_user_id`, `customer_id`, `deleted`, `activity_status`, `billing_rate`, `billing_date` FROM ikase" . $schema_from . ".cse_activity
WHERE activity_date > '2017-11-05'";
$arrSQL[] = $sql;

$sql = "INSERT INTO `ikase" . $schema . "3`.`cse_case_activity`
(
`case_activity_uuid`, `case_uuid`, `activity_uuid`, `attribute`, `case_track_id`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `case_activity_uuid`, `case_uuid`, cse_case_activity.`activity_uuid`, `attribute`, `case_track_id`, `last_updated_date`, `last_update_user`, cse_case_activity.`deleted`, cse_case_activity.`customer_id` 
FROM ikase" . $schema_from . ".cse_case_activity
INNER JOIN ikase" . $schema_from . ".cse_activity
ON cse_case_activity.activity_uuid = cse_activity.activity_uuid
AND cse_activity.activity_date > '2017-11-05'";
$arrSQL[] = $sql;

$sql = "INSERT INTO `ikase" . $schema . "3`.`cse_task`
(
`task_uuid`, `task_name`, `from`, `task_date`, `task_description`, `task_first_name`, `task_last_name`, `task_dateandtime`, `task_end_time`, `full_address`, `assignee`, `cc`, `task_title`, `attachments`, `task_email`, `task_hour`, `task_type`, `task_from`, `task_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed`, `color`, `customer_id`, `deleted`)
SELECT cse_task.`task_uuid`, cse_task.`task_name`, cse_task.`from`, cse_task.`task_date`, cse_task.`task_description`, cse_task.`task_first_name`, cse_task.`task_last_name`, cse_task.`task_dateandtime`, cse_task.`task_end_time`, cse_task.`full_address`, cse_task.`assignee`, cse_task.`cc`, cse_task.`task_title`, cse_task.`attachments`, cse_task.`task_email`, cse_task.`task_hour`, cse_task.`task_type`, cse_task.`task_from`, cse_task.`task_priority`, cse_task.`end_date`, cse_task.`completed_date`, cse_task.`callback_date`, cse_task.`callback_completed`, cse_task.`color`, cse_task.`customer_id`, cse_task.`deleted` 
FROM ikase" . $schema_from . ".cse_task
INNER JOIN ikase" . $schema_from . ".cse_task_track
ON cse_task.task_uuid = cse_task_track.task_uuid
WHERE cse_task_track.time_stamp  > '2017-11-05'";

$sql = "INSERT INTO ikase" . $schema . "3.cse_task (`task_uuid`, `task_name`, `from`, `task_date`, `task_description`, `task_first_name`, `task_last_name`, `task_dateandtime`, `task_end_time`, `full_address`, `assignee`, `cc`, `task_title`, `attachments`, `task_email`, `task_hour`, `task_type`, `task_from`, `task_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed`, `color`, `customer_id`, `deleted`)
SELECT `task_uuid`, `task_name`, `from`, `task_date`, `task_description`, `task_first_name`, `task_last_name`, `task_dateandtime`, `task_end_time`, `full_address`, `assignee`, `cc`, `task_title`, `attachments`, `task_email`, `task_hour`, `task_type`, `task_from`, `task_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed`, `color`, `customer_id`, `deleted` 
FROM ikase" . $schema_from . ".cse_task
WHERE task_uuid NOT IN (SELECT task_uuid FROM ikase" . $schema . "3.cse_task)";

$arrSQL[] = $sql;

$sql = "INSERT INTO `ikase" . $schema . "3`.`cse_case_task`
(
`case_task_uuid`, `case_uuid`, `task_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `case_task_uuid`, `case_uuid`, `task_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id` 
FROM ikase" . $schema_from . ".cse_case_task
WHERE task_uuid IN (SELECT task_uuid 
FROM ikase" . $schema_from . ".cse_task_track)";
$arrSQL[] = $sql;

$sql = "INSERT INTO `ikase" . $schema . "3`.`cse_document`
(
`document_uuid`, `parent_document_uuid`, `document_name`, `document_date`, `document_filename`, `document_extension`, `thumbnail_folder`, `description`, `description_html`, `source`, `received_date`, `type`, `verified`, `customer_id`, `deleted`)

SELECT `document_uuid`, `parent_document_uuid`, `document_name`, `document_date`, `document_filename`, `document_extension`, `thumbnail_folder`, `description`, `description_html`, `source`, `received_date`, `type`, `verified`, `customer_id`, `deleted` 
FROM ikase" . $schema_from . ".cse_document
WHERE document_date > '2017-11-05'";
$arrSQL[] = $sql;

$sql = "INSERT INTO `ikase" . $schema . "3`.`cse_case_document`
(
`case_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `case_document_uuid`, `case_uuid`,
cse_document.`document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`,
cse_document.`deleted`,
cse_document.`customer_id`
FROM ikase" . $schema_from . ".cse_case_document
INNER JOIN ikase" . $schema_from . ".cse_document
ON cse_case_document.document_uuid = cse_document.document_uuid
WHERE cse_document.document_date > '2017-11-05'";
$arrSQL[] = $sql;

$sql = "INSERT INTO `ikase_" . $schema . "`.`cse_message`
(
`message_uuid`, `message_type`, `dateandtime`, `from`, `message_to`, `message_cc`, `message_bcc`, `message`, `subject`, `snippet`, `attachments`, `priority`, `callback_date`, `customer_id`, `status`, `deleted`)
SELECT `message_uuid`, `message_type`, `dateandtime`, `from`, `message_to`, `message_cc`, `message_bcc`, `message`, `subject`, `snippet`, `attachments`, `priority`, `callback_date`, `customer_id`, `status`, `deleted` FROM ikase" . $schema_from . ".cse_message
WHERE dateandtime > '2017-11-05'";
$arrSQL[] = $sql;

$sql = "INSERT INTO `ikase" . $schema . "`.`cse_case_message`
(
`case_message_uuid`, `case_uuid`, `message_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)

SELECT `case_message_uuid`, `case_uuid`, `message_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id` 
FROM ikase" . $schema_from . ".cse_case_message
WHERE message_uuid IN (
	SELECT message_uuid 
    FROM ikase" . $schema_from . ".cse_message
	WHERE dateandtime > '2017-11-05'";
$arrSQL[] = $sql;

$sql = "INSERT INTO `ikase" . $schema . "`.`cse_message_user`
(
`message_user_uuid`, `message_uuid`, `user_uuid`, `type`, `thread_uuid`, `read_status`, `read_date`, `action`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`, `user_type`)
SELECT `message_user_uuid`, `message_uuid`, `user_uuid`, `type`, `thread_uuid`, `read_status`, `read_date`, `action`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`, `user_type` 
FROM ikase" . $schema_from . ".cse_message_user
WHERE last_updated_date > '2017-11-05'";
$arrSQL[] = $sql;

$sql = "INSERT INTO `ikase" . $schema . "3`.`cse_event`
(
`event_uuid`, `event_name`, `event_date`, `event_duration`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `full_address`, `judge`, `assignee`, `event_title`, `event_email`, `event_hour`, `event_type`, `event_type_abbr`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed`, `color`, `off_calendar`, `customer_id`, `deleted`)


SELECT `event_uuid`, `event_name`, `event_date`, `event_duration`, `event_description`, `event_first_name`, `event_last_name`, `event_dateandtime`, `event_end_time`, `full_address`, `judge`, `assignee`, `event_title`, `event_email`, `event_hour`, `event_type`, `event_type_abbr`, `event_from`, `event_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed`, `color`, `off_calendar`, `customer_id`, `deleted` 
FROM ikasee" . $schema_from . ".cse_event
WHERE event_uuid NOT IN (SELECT event_uuid FROM ikase" . $schema . "3.cse_event)";
$arrSQL[] = $sql;

$sql = "INSERT INTO `ikase" . $schema . "3`.`cse_case_event`
(
`case_event_uuid`, `case_uuid`, `event_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT 
    `cse_case_event`.`case_event_uuid`,  `cse_case_event`.`case_uuid`,  `cse_case_event`.`event_uuid`,  `cse_case_event`.`attribute`,  `cse_case_event`.`last_updated_date`,  `cse_case_event`.`last_update_user`,  `cse_case_event`.`deleted`,  `cse_case_event`.`customer_id`
FROM `ikase" . $schema_from . "`.`cse_case_event`
LEFT OUTER JOIN `ikase" . $schema . "3`.`cse_case_event` cce3
ON `cse_case_event`.`case_event_uuid` = `cce3`.`case_event_uuid`
WHERE `cce3`.`case_event_uuid` IS NULL";
$arrSQL[] = $sql;

$sql = "INSERT INTO `ikase" . $schema . "3`.`cse_event_user`
(
`event_user_uuid`, `event_uuid`, `thread_uuid`, `user_uuid`, `type`, `read_status`, `read_date`, `action`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)

SELECT `event_user_uuid`, `event_uuid`, `thread_uuid`, `user_uuid`, `type`, `read_status`, `read_date`, `action`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id` 
FROM ikase" . $schema_from . ".cse_event_user
WHERE last_updated_date > '2017-11-05'";
$arrSQL[] = $sql;
/*
$sql = "INSERT INTO `ikase" . $schema . "3`.`cse_jetfile`
(
`injury_uuid`, `info`, `jetfile_case_id`, `app_filing_id`, `app_filing_date`, `app_status`, `app_status_number`, `dor_info`, `jetfile_dor_id`, `dor_filing_id`, `dor_filing_date`, `dore_info`, `jetfile_dore_id`, `dore_filing_id`, `dore_filing_date`, `lien_info`, `jetfile_lien_id`, `lien_filing_id`, `lien_filing_date`, `unstruc_info`, `customer_id`, `last_update_date`, `deleted`)

SELECT `injury_uuid`, `info`, `jetfile_case_id`, `app_filing_id`, `app_filing_date`, `app_status`, `app_status_number`, `dor_info`, `jetfile_dor_id`, `dor_filing_id`, `dor_filing_date`, `dore_info`, `jetfile_dore_id`, `dore_filing_id`, `dore_filing_date`, `lien_info`, `jetfile_lien_id`, `lien_filing_id`, `lien_filing_date`, `unstruc_info`, `customer_id`, `last_update_date`, `deleted` FROM ikase" . $schema_from . ".cse_jetfile
WHERE last_update_date > '2017-11-05'";
$arrSQL[] = $sql;
*/
$sql = "INSERT INTO `ikase" . $schema . "3`.`cse_injury`
(
`injury_uuid`, `injury_number`, `adj_number`, `type`, `injury_status`, `occupation`, `occupation_group`, `start_date`, `end_date`, `ct_dates_note`, `body_parts`, `statute_limitation`, `statute_interval`, `explanation`, `deu`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `customer_id`, `deleted`)

SELECT ci.`injury_uuid`, ci.`injury_number`, ci.`adj_number`, ci.`type`, ci.`injury_status`, ci.`occupation`, ci.`occupation_group`, ci.`start_date`, ci.`end_date`, ci.`ct_dates_note`, ci.`body_parts`, ci.`statute_limitation`, ci.`statute_interval`, ci.`explanation`, ci.`deu`, ci.`full_address`, ci.`street`, ci.`city`, ci.`state`, ci.`zip`, ci.`suite`, ci.`customer_id`, ci.`deleted` 
FROM ikase" . $schema_from . ".cse_injury ci
INNER JOIN ikase" . $schema_from . ".cse_injury_track cit
ON ci.injury_uuid = cit.injury_uuid AND operation = 'insert'
WHERE time_stamp > '2017-11-05'";
$arrSQL[] = $sql;

$sql = "INSERT INTO `ikase" . $schema . "3`.`cse_case_injury`
(
`case_injury_uuid`, `case_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)


SELECT cci1.`case_injury_uuid`, cci1.`case_uuid`, cci1.`injury_uuid`, cci1.`attribute`, cci1.`last_updated_date`, cci1.`last_update_user`, cci1.`deleted`, cci1.`customer_id`
FROM ikase" . $schema_from . ".cse_case_injury cci1
LEFT OUTER JOIN ikase" . $schema . ".cse_case_injury cci2
ON cci1.case_injury_uuid = cci2.case_injury_uuid
WHERE cci2.case_injury_uuid IS NULL";
$arrSQL[] = $sql;

$sql = "INSERT INTO `ikase" . $schema . "3`.`cse_corporation`
(
`corporation_uuid`, `parent_corporation_uuid`, `full_name`, `company_name`, `type`, `first_name`, `last_name`, `aka`, `preferred_name`, `employee_phone`, `employee_fax`, `employee_email`, `full_address`, `additional_addresses`, `longitude`, `latitude`, `street`, `city`, `state`, `zip`, `suite`, `company_site`, `phone`, `email`, `fax`, `ssn`, `dob`, `salutation`, `copying_instructions`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`, `phone_ext`, `comments`, `fee`, `report_number`, `officer`, `date`, `party_type_option`, `party_representing_id`, `party_representing_name`, `party_defendant_option`, `kai_info`)

SELECT cc1.`corporation_uuid`, cc1.`parent_corporation_uuid`, cc1.`full_name`, cc1.`company_name`, cc1.`type`, cc1.`first_name`, cc1.`last_name`, cc1.`aka`, cc1.`preferred_name`, cc1.`employee_phone`, cc1.`employee_fax`, cc1.`employee_email`, cc1.`full_address`, cc1.`additional_addresses`, cc1.`longitude`, cc1.`latitude`, cc1.`street`, cc1.`city`, cc1.`state`, cc1.`zip`, cc1.`suite`, cc1.`company_site`, cc1.`phone`, cc1.`email`, cc1.`fax`, cc1.`ssn`, cc1.`dob`, cc1.`salutation`, cc1.`copying_instructions`, cc1.`last_updated_date`, cc1.`last_update_user`, cc1.`deleted`, cc1.`customer_id`, cc1.`phone_ext`, cc1.`comments`, cc1.`fee`, cc1.`report_number`, cc1.`officer`, cc1.`date`, cc1.`party_type_option`, cc1.`party_representing_id`, cc1.`party_representing_name`, cc1.`party_defendant_option`, cc1.`kai_info`
FROM ikase" . $schema_from . ".cse_corporation cc1
LEFT OUTER JOIN ikase" . $schema . "3.cse_corporation cc2
ON cc1.corporation_uuid = cc2.corporation_uuid
WHERE cc2.corporation_uuid IS NULL";
$arrSQL[] = $sql;

$sql = "INSERT INTO `ikase" . $schema . "3`.`cse_case_corporation`
(
`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
SELECT `case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`
FROM ikase" . $schema_from . ".cse_case_corporation 
WHERE last_updated_date > '2017-11-05'";
$arrSQL[] = $sql;

$sql = "INSERT INTO `ikase" . $schema . "3`.`cse_case`
(`case_uuid`, `case_number`, `file_number`, `cpointer`, `case_name`, `source`, `adj_number`, `case_date`, `terminated_date`, `case_type`, `injury_type`, `venue`, `dois`, `case_status`, `case_substatus`, `case_subsubstatus`, `rating`, `submittedOn`, `supervising_attorney`, `attorney`, `worker`, `medical`, `td`, `rehab`, `edd`, `claims`, `interpreter_needed`, `case_language`, `lien_filed`, `sub_in`, `special_instructions`, `case_description`, `customer_id`, `closed`, `deleted`)
SELECT cc1.`case_uuid`, cc1.`case_number`, cc1.`file_number`, cc1.`cpointer`, cc1.`case_name`, cc1.`source`, cc1.`adj_number`, cc1.`case_date`, cc1.`terminated_date`, cc1.`case_type`, cc1.`injury_type`, cc1.`venue`, cc1.`dois`, cc1.`case_status`, cc1.`case_substatus`, cc1.`case_subsubstatus`, cc1.`rating`, cc1.`submittedOn`, cc1.`supervising_attorney`, cc1.`attorney`, cc1.`worker`, cc1.`medical`, cc1.`td`, cc1.`rehab`, cc1.`edd`, cc1.`claims`, cc1.`interpreter_needed`, cc1.`case_language`, cc1.`lien_filed`, cc1.`sub_in`, cc1.`special_instructions`, cc1.`case_description`, cc1.`customer_id`, cc1.`closed`, cc1.`deleted`
FROM ikase" . $schema_from . ".cse_case cc1
LEFT OUTER JOIN ikase" . $schema . "3.cse_case cc2
ON cc1.case_uuid = cc2.case_uuid
WHERE cc2.case_uuid IS NULL";
$arrSQL[] = $sql;

$sql = "INSERT INTO `ikase" . $schema . "3`.`cse_thread`
(
`thread_uuid`,
`dateandtime`,
`from`,
`subject`,
`customer_id`,
`deleted`)
SELECT `thread_uuid`,
`dateandtime`,
`from`,
`subject`,
`customer_id`,
`deleted` FROM ikase" . $schema_from . ".cse_thread
WHERE CAST(dateandtime AS DATE) = '2017-11-06'";
$arrSQL[] = $sql;

$sql = "INSERT INTO `ikase" . $schema . "3`.`cse_thread_message`
(
`thread_message_uuid`,
`thread_uuid`,
`message_uuid`,
`attribute`,
`last_updated_date`,
`last_update_user`,
`deleted`,
`customer_id`)


SELECT `thread_message_uuid`,
`thread_uuid`,
`message_uuid`,
`attribute`,
`last_updated_date`,
`last_update_user`,
`deleted`,
`customer_id` FROM ikase" . $schema_from . ".cse_thread_message
WHERE CAST(last_updated_date AS DATE) = '2017-11-06'";

$sql = "INSERT INTO `ikase" . $schema . "3`.`cse_case_message`
(
`case_message_uuid`,
`case_uuid`,
`message_uuid`,
`attribute`,
`last_updated_date`,
`last_update_user`,
`deleted`,
`customer_id`)


SELECT `case_message_uuid`,
`case_uuid`,
`message_uuid`,
`attribute`,
`last_updated_date`,
`last_update_user`,
`deleted`,
`customer_id` FROM ikase" . $schema_from . ".cse_case_message
WHERE CAST(last_updated_date AS DATE) = '2017-11-06'";
$arrSQL[] = $sql;
?>