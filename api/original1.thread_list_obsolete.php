SELECT DISTINCT thr.thread_id `id`, thr.thread_uuid `uuid`, 
	msg.customer_id, msg.`message_type` `thread_type`,
	thread_counts.message_count, REPLACE(thread_counts.thread_attachments, ',', '|') thread_attachments,
	thread_counts.thread_message_ids,
	IFNULL(`user`.`user_name`, `msg`.`from`) `sender`, IFNULL(`cc`.`case_id`, -1) `case_id`,
	msg.`subject`, IFNULL(CAST(CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`)  AS CHAR (10000) CHARACTER SET UTF8), '') `case_name`, 
    msg.attachments, 
	IF(msg.snippet = '', msg.message, msg.snippet) `snippet`,
	thread_counts.max_message_id, msgmax.dateandtime, IFNULL(cmu_max.read_status, 'N') read_status
FROM cse_thread thr	
	
INNER JOIN (
	SELECT ct.thread_uuid
	FROM cse_thread ct
	INNER JOIN cse_thread_message ctm
	ON ct.thread_uuid = ctm.thread_uuid
	INNER JOIN cse_message_user cmut
	ON ctm.message_uuid = cmut.message_uuid AND cmut.user_uuid = 'weruifga'
) my_threads
ON thr.thread_uuid = my_threads.thread_uuid

INNER JOIN (
	SELECT thread_uuid, COUNT(DISTINCT cse_thread_message.message_uuid) message_count,  
	MIN(DISTINCT message_id) min_message_id, MAX(DISTINCT message_id) max_message_id,
	GROUP_CONCAT(DISTINCT message_id) thread_message_ids,
	GROUP_CONCAT(attachments) thread_attachments
	FROM cse_thread_message
	INNER JOIN cse_message ON cse_thread_message.message_uuid = cse_message.message_uuid
	WHERE cse_message.customer_id = '1033'
	GROUP BY thread_uuid
) thread_counts
ON thr.thread_uuid = thread_counts.thread_uuid

INNER JOIN cse_message msg
ON thread_counts.min_message_id = msg.message_id

LEFT OUTER JOIN (
	SELECT cm.message_id
	FROM cse_message cm
	INNER JOIN cse_message_user cmu
	ON cm.message_uuid = cmu.message_uuid
	INNER JOIN cse_thread_message ctm
	ON cm.message_uuid = ctm.message_uuid
	INNER JOIN (
		SELECT 
			thread_uuid,
				COUNT(DISTINCT cse_thread_message.message_uuid) message_count
		FROM
			cse_thread_message
		INNER JOIN cse_message ON cse_thread_message.message_uuid = cse_message.message_uuid
		GROUP BY thread_uuid
	) thread_counts 
	ON ctm.thread_uuid = thread_counts.thread_uuid
	WHERE 1 
	AND cmu.user_uuid = 'weruifga'
	AND cmu.`type` = 'from'
	AND message_count = 1
) forbidden_messages
ON msg.message_id = forbidden_messages.message_id

LEFT OUTER JOIN `cse_case_message` ccm
ON msg.message_uuid = ccm.message_uuid

LEFT OUTER JOIN `cse_case` cc
ON ccm.case_uuid = cc.case_uuid
LEFT OUTER JOIN cse_case_person ccapp ON cc.case_uuid = ccapp.case_uuid

LEFT OUTER JOIN (
	SELECT 
	pers.`personx_id` `person_id`,
	pers.`personx_uuid` `person_uuid`,
	pers.`parent_personx_uuid` `parent_person_uuid`,
	CAST(AES_DECRYPT(pers.`full_name`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `full_name`,
	CAST(AES_DECRYPT(pers.`company_name`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `company_name`,
	CAST(AES_DECRYPT(pers.`first_name`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `first_name`,
	CAST(AES_DECRYPT(pers.`middle_name`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `middle_name`,
	CAST(AES_DECRYPT(pers.`last_name`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `last_name`,
	CAST(AES_DECRYPT(pers.`aka`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `aka`,
	CAST(AES_DECRYPT(pers.`preferred_name`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `preferred_name`,
	CAST(AES_DECRYPT(pers.`full_address`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `full_address`,
	pers.`longitude`,
	pers.`latitude`,
	CAST(AES_DECRYPT(pers.`street`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `street`,
	pers.`city`,
	pers.`state`,
	pers.`zip`,
	CAST(AES_DECRYPT(pers.`suite`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `suite`,
	CAST(AES_DECRYPT(pers.`phone`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `phone`,
	CAST(AES_DECRYPT(pers.`email`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `email`,
	CAST(AES_DECRYPT(pers.`fax`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `fax`,
	CAST(AES_DECRYPT(pers.`work_phone`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `work_phone`,
	CAST(AES_DECRYPT(pers.`cell_phone`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `cell_phone`,
	CAST(AES_DECRYPT(pers.`other_phone`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `other_phone`,
	CAST(AES_DECRYPT(pers.`work_email`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `work_email`,
	CAST(AES_DECRYPT(pers.`ssn`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `ssn`,
	CAST(AES_DECRYPT(pers.`ssn_last_four`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `ssn_last_four`,
	CAST(AES_DECRYPT(pers.`dob`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `dob`,
	CAST(AES_DECRYPT(pers.`license_number`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `license_number`,
	pers.`title`,
	CAST(AES_DECRYPT(pers.`ref_source`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `ref_source`,
	CAST(AES_DECRYPT(pers.`salutation`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `salutation`,
	pers.`age`,
	pers.`priority_flag`,
	pers.`gender`,
	pers.`language`,
	CAST(AES_DECRYPT(pers.`birth_state`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `birth_state`,
	CAST(AES_DECRYPT(pers.`birth_city`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `birth_city`,
	pers.`marital_status`,
	pers.`legal_status`,
	CAST(AES_DECRYPT(pers.`spouse`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `spouse`,
	CAST(AES_DECRYPT(pers.`spouse_contact`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `spouse_contact`,
	CAST(AES_DECRYPT(pers.`emergency`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `emergency`,
	CAST(AES_DECRYPT(pers.`emergency_contact`, 'something123someone')  AS CHAR(10000) CHARACTER SET utf8) `emergency_contact`,
	pers.`last_updated_date`,
	pers.`last_update_user`,
	pers.`deleted`,
	pers.`customer_id`,
	pers.personx_id id, pers.personx_uuid uuid
	  
	FROM `cse_personx` pers 
	WHERE pers.deleted = 'N'
	AND pers.customer_id = 1033
	ORDER by pers.personx_id
) app ON ccapp.person_uuid = app.person_uuid

LEFT OUTER JOIN `cse_case_corporation` ccorp
ON (cc.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
LEFT OUTER JOIN `cse_corporation` employer
ON ccorp.corporation_uuid = employer.corporation_uuid	

INNER JOIN cse_message msgmax
ON thread_counts.max_message_id = msgmax.message_id

LEFT OUTER JOIN `cse_message_user` cmu_max
ON msgmax.message_uuid = cmu_max.message_uuid AND (cmu_max.type = 'to' OR cmu_max.type = 'cc' OR cmu_max.type = 'bcc') AND cmu_max.user_uuid = 'weruifga'

LEFT OUTER JOIN `cse_message_user` cmu_from
ON (msg.message_uuid = cmu_from.message_uuid AND cmu_from.type = 'from')
LEFT OUTER JOIN ikase.`cse_user` `user`
ON cmu_from.user_uuid = `user`.`user_uuid`

WHERE 1 
AND ccm.deleted = 'N'
AND thr.deleted = 'N'
AND (CONCAT(msg.message_to, ';', msg.message_cc, ';', msg.message_bcc) LIKE '%TS%'  OR msg.message_to LIKE '%thomas@kustomweb.com%')
AND thr.customer_id = '1033'
AND forbidden_messages.message_id IS NULL
ORDER BY msgmax.dateandtime DESC