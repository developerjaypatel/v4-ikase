use ikase_pag_copy;
SELECT DISTINCT * FROM (
	
SELECT  
			inj.injury_id id, ccase.case_id, ccase.lien_filed, ccase.special_instructions,ccase.case_description, inj.injury_number, ccase.case_uuid uuid, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,ccase.source, inj.adj_number, ccase.rating, 
			IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) `case_date`, 
			IF (DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y')) `terminated_date`,
ccase.case_type, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims,
			venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue.address1 venue_street, venue.address2 venue_suite, venue.city venue_city, venue.zip venue_zip, venue_abbr, ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, 
			IFNULL(superatt.nickname, '') as supervising_attorney_name, IFNULL(superatt.user_name, '') as supervising_attorney_full_name,
			ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
			app.person_id applicant_id, app.person_uuid applicant_uuid, IFNULL(app.salutation, '') applicant_salutation,
			IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`, IFNULL(app.language, '') language,
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, app.dob app_dob, 
			IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn, IFNULL(app.email, '') applicant_email, IFNULL(app.phone, '') applicant_phone,IFNULL(app.full_address, '') applicant_full_address,
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.`corporation_uuid` employer_uuid, IFNULL(employer.`company_name`, '') employer, employer.`full_address` employer_full_address, employer.`suite` `employer_suite`,
			IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, IF (DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y')) statute_limitation, 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, inj.ct_dates_note,
			IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
			CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, ''),' - ', 
            REPLACE(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), '00/00/0000', '')) `name`, ccase.case_name, att.user_id attorney_id, user.user_id, 
			IFNULL(att.nickname, '') as attorney_name, IFNULL(att.user_name, '') as attorney_full_name, 
			IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name,
			IFNULL(lien.lien_id, -1) lien_id, 
			IFNULL(settlement.settlement_id, IFNULL(settlementsheet.settlementsheet_id, -1)) settlement_id,
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id, ccase.injury_type, ccase.sub_in,
			IFNULL(jet.jetfile_id, '-1') jetfile_id,
			IFNULL(jet.jetfile_case_id, '-1') jetfile_case_id,
			IFNULL(jet.jetfile_dor_id, '-1') jetfile_dor_id,
			IFNULL(jet.jetfile_dore_id, '-1') jetfile_dore_id,
			IFNULL(jet.jetfile_lien_id, '-1') jetfile_lien_id,
			IFNULL(jet.app_filing_id, '-1') app_filing_id,
			IFNULL(jet.dor_filing_id, '-1') dor_filing_id,
			IFNULL(jet.dore_filing_id, '-1') dore_filing_id,
			IFNULL(jet.lien_filing_id, '-1') lien_filing_id,
			IFNULL(pinj.personal_injury_date, '') personal_injury_date
			FROM cse_case ccase  
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
			LEFT OUTER JOIN cse_personal_injury pinj ON ccase.case_id = pinj.case_id AND pinj.deleted = 'N'
			LEFT OUTER JOIN cse_person app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` `employer`
			ON ccorp.corporation_uuid = employer.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` ccorp2
			ON (ccase.case_uuid = ccorp2.case_uuid  AND ccorp2.attribute = 'carrier' AND ccorp2.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` `carrier`
			ON ccorp2.corporation_uuid = carrier.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` ccorp3
			ON (ccase.case_uuid = ccorp3.case_uuid  AND ccorp3.attribute = 'defense' AND ccorp3.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` `defense`
			ON ccorp3.corporation_uuid = defense.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` ccorp4
			ON (ccase.case_uuid = ccorp4.case_uuid  AND ccorp4.attribute = 'client' AND ccorp4.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` `client`
			ON ccorp4.corporation_uuid = client.corporation_uuid
			INNER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid AND cinj.deleted = 'N' AND cinj.`attribute` = 'main'
			INNER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
			 LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			LEFT OUTER JOIN `cse_settlementsheet` settlementsheet
			ON cis.settlement_uuid = settlementsheet.settlementsheet_uuid
			
			LEFT OUTER JOIN `cse_jetfile` jet
			ON inj.injury_uuid = jet.injury_uuid
			
			LEFT OUTER JOIN ikase.`cse_user` superatt
			ON ccase.supervising_attorney = superatt.user_id
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
			WHERE ccase.deleted ='N' 
			AND IF (inj.deleted IS NULL, 'N' , inj.deleted) = 'N' 
			AND ccase.case_status NOT LIKE '%CLOSE%' 
	AND ccase.customer_id = 1064 
	AND (inj.customer_id = 1064 OR inj.customer_id IS NULL)  AND (
				app.first_name LIKE '%Robert%'
				OR app.last_name LIKE '%Robert%'
			OR app.aka LIKE '%Robert%'
			OR app.full_name LIKE '%Robert%'
			OR app.phone LIKE '%Robert%'
			OR app.work_phone LIKE '%Robert%'
			OR app.cell_phone LIKE '%Robert%'
			OR app.email LIKE '%Robert%'
			OR app.work_email LIKE '%Robert%'
			OR app.full_address LIKE '%Robert%'
			/*
            OR employer.`company_name` LIKE '%Robert%'
			OR defense.`company_name` LIKE '%Robert%'
            */
			OR inj.`occupation` LIKE '%Robert%' 
			OR ccase.case_number LIKE '%Robert%'
			OR ccase.case_name LIKE '%Robert%'
			OR ccase.file_number LIKE '%Robert%'
				OR inj.adj_number LIKE '%Robert%'
			) 

UNION
	
SELECT  
			inj.injury_id id, ccase.case_id, ccase.lien_filed, ccase.special_instructions,ccase.case_description, inj.injury_number, ccase.case_uuid uuid, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,ccase.source, inj.adj_number, ccase.rating, 
			IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) `case_date`, 
			IF (DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y')) `terminated_date`,
ccase.case_type, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims,
			venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue.address1 venue_street, venue.address2 venue_suite, venue.city venue_city, venue.zip venue_zip, venue_abbr, ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, 
			IFNULL(superatt.nickname, '') as supervising_attorney_name, IFNULL(superatt.user_name, '') as supervising_attorney_full_name,
			ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
			app.person_id applicant_id, app.person_uuid applicant_uuid, IFNULL(app.salutation, '') applicant_salutation,
			IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`, IFNULL(app.language, '') language,
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, app.dob app_dob, 
			IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn, IFNULL(app.email, '') applicant_email, IFNULL(app.phone, '') applicant_phone,IFNULL(app.full_address, '') applicant_full_address,
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.`corporation_uuid` employer_uuid, IFNULL(employer.`company_name`, '') employer, employer.`full_address` employer_full_address, employer.`suite` `employer_suite`,
			IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, IF (DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y')) statute_limitation, 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, inj.ct_dates_note,
			IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
			CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, ''),' - ', 
            REPLACE(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), '00/00/0000', '')) `name`, ccase.case_name, att.user_id attorney_id, user.user_id, 
			IFNULL(att.nickname, '') as attorney_name, IFNULL(att.user_name, '') as attorney_full_name, 
			IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name,
			IFNULL(lien.lien_id, -1) lien_id, 
			IFNULL(settlement.settlement_id, IFNULL(settlementsheet.settlementsheet_id, -1)) settlement_id,
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id, ccase.injury_type, ccase.sub_in,
			IFNULL(jet.jetfile_id, '-1') jetfile_id,
			IFNULL(jet.jetfile_case_id, '-1') jetfile_case_id,
			IFNULL(jet.jetfile_dor_id, '-1') jetfile_dor_id,
			IFNULL(jet.jetfile_dore_id, '-1') jetfile_dore_id,
			IFNULL(jet.jetfile_lien_id, '-1') jetfile_lien_id,
			IFNULL(jet.app_filing_id, '-1') app_filing_id,
			IFNULL(jet.dor_filing_id, '-1') dor_filing_id,
			IFNULL(jet.dore_filing_id, '-1') dore_filing_id,
			IFNULL(jet.lien_filing_id, '-1') lien_filing_id,
			IFNULL(pinj.personal_injury_date, '') personal_injury_date
			FROM cse_case ccase  
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N' 
			LEFT OUTER JOIN cse_personal_injury pinj ON ccase.case_id = pinj.case_id AND pinj.deleted = 'N' 
			LEFT OUTER JOIN cse_person app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			INNER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			INNER JOIN `cse_corporation` `employer`
			ON ccorp.corporation_uuid = employer.corporation_uuid
			
			INNER JOIN (
				
				SELECT corporation_id
				
				FROM `cse_corporation` 
				
				WHERE 1 AND INSTR(`company_name`, 'Robert') > 0
  
          			AND `type` = 'employer'
			) emps
            
			ON employer.corporation_id = emps.corporation_id
			
			INNER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid AND cinj.deleted = 'N' AND cinj.`attribute` = 'main'
			INNER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
			 LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			LEFT OUTER JOIN `cse_settlementsheet` settlementsheet
			ON cis.settlement_uuid = settlementsheet.settlementsheet_uuid
			
			LEFT OUTER JOIN `cse_jetfile` jet
			ON inj.injury_uuid = jet.injury_uuid
			
			LEFT OUTER JOIN ikase.`cse_user` superatt
			ON ccase.supervising_attorney = superatt.user_id
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
			
            WHERE ccase.deleted ='N' 
			AND IF (inj.deleted IS NULL, 'N' , inj.deleted) = 'N' 
			AND ccase.case_status NOT LIKE '%CLOSE%' 
	AND ccase.customer_id = 1064 

UNION


SELECT  
			inj.injury_id id, ccase.case_id, ccase.lien_filed, ccase.special_instructions,ccase.case_description, inj.injury_number, ccase.case_uuid uuid, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,ccase.source, inj.adj_number, ccase.rating, 
			IF (DATE_FORMAT(ccase.case_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.case_date, '%m/%d/%Y')) `case_date`, 
			IF (DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(ccase.terminated_date, '%m/%d/%Y')) `terminated_date`,
ccase.case_type, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims,
			venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue.address1 venue_street, venue.address2 venue_suite, venue.city venue_city, venue.zip venue_zip, venue_abbr, ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, 
			IFNULL(superatt.nickname, '') as supervising_attorney_name, IFNULL(superatt.user_name, '') as supervising_attorney_full_name,
			ccase.worker, ccase.interpreter_needed, ccase.file_location, ccase.case_language `case_language`, 
			app.person_id applicant_id, app.person_uuid applicant_uuid, IFNULL(app.salutation, '') applicant_salutation,
			IF (app.first_name IS NULL, '', TRIM(app.first_name)) first_name , IF(app.last_name IS NULL, '', app.last_name) last_name, IFNULL(app.full_name, '') `full_name`, IFNULL(app.language, '') language,
			IF (DATE_FORMAT(app.dob, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(app.dob, '%m/%d/%Y')) dob, app.dob app_dob, 
			IF (app.ssn = 'XXXXXXXXX', '', app.ssn) ssn, IFNULL(app.email, '') applicant_email, IFNULL(app.phone, '') applicant_phone,IFNULL(app.full_address, '') applicant_full_address,
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.`corporation_uuid` employer_uuid, IFNULL(employer.`company_name`, '') employer, employer.`full_address` employer_full_address, employer.`suite` `employer_suite`,
			IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')) start_date, IF (DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.statute_limitation, '%m/%d/%Y')) statute_limitation, 
			IF (DATE_FORMAT(inj.end_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.end_date, '%m/%d/%Y')) end_date, inj.ct_dates_note,
			IF (inj.occupation IS NULL, '' , inj.occupation) occupation,
			CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, ''),' - ', 
            REPLACE(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), '00/00/0000', '')) `name`, ccase.case_name, att.user_id attorney_id, user.user_id, 
			IFNULL(att.nickname, '') as attorney_name, IFNULL(att.user_name, '') as attorney_full_name, 
			IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name,
			IFNULL(lien.lien_id, -1) lien_id, 
			IFNULL(settlement.settlement_id, IFNULL(settlementsheet.settlementsheet_id, -1)) settlement_id,
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id, ccase.injury_type, ccase.sub_in,
			IFNULL(jet.jetfile_id, '-1') jetfile_id,
			IFNULL(jet.jetfile_case_id, '-1') jetfile_case_id,
			IFNULL(jet.jetfile_dor_id, '-1') jetfile_dor_id,
			IFNULL(jet.jetfile_dore_id, '-1') jetfile_dore_id,
			IFNULL(jet.jetfile_lien_id, '-1') jetfile_lien_id,
			IFNULL(jet.app_filing_id, '-1') app_filing_id,
			IFNULL(jet.dor_filing_id, '-1') dor_filing_id,
			IFNULL(jet.dore_filing_id, '-1') dore_filing_id,
			IFNULL(jet.lien_filing_id, '-1') lien_filing_id,
			IFNULL(pinj.personal_injury_date, '') personal_injury_date
			FROM cse_case ccase  
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
			LEFT OUTER JOIN cse_personal_injury pinj ON ccase.case_id = pinj.case_id AND pinj.deleted = 'N' 
			LEFT OUTER JOIN cse_person app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
            
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` `employer`
			ON ccorp.corporation_uuid = employer.corporation_uuid
            
            INNER JOIN `cse_case_corporation` ccorp3
			ON (ccase.case_uuid = ccorp3.case_uuid  AND ccorp3.attribute = 'defense' AND ccorp3.deleted = 'N')
			INNER JOIN `cse_corporation` `defense`
			ON ccorp3.corporation_uuid = defense.corporation_uuid
			
			
			INNER JOIN (
				
				SELECT corporation_id
				
				FROM `cse_corporation` 
				
				WHERE 1 AND INSTR(`company_name`, 'Robert') > 0
  
          			AND `type` = 'defense'
			) defs
            
			ON defense.corporation_id = defs.corporation_id
			
			INNER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid AND cinj.deleted = 'N' AND cinj.`attribute` = 'main'
			INNER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
			 LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			LEFT OUTER JOIN `cse_settlementsheet` settlementsheet
			ON cis.settlement_uuid = settlementsheet.settlementsheet_uuid
			
			LEFT OUTER JOIN `cse_jetfile` jet
			ON inj.injury_uuid = jet.injury_uuid
			
			LEFT OUTER JOIN ikase.`cse_user` superatt
			ON ccase.supervising_attorney = superatt.user_id
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
			
            WHERE ccase.deleted ='N' 
			AND IF (inj.deleted IS NULL, 'N' , inj.deleted) = 'N' 
			AND ccase.case_status NOT LIKE '%CLOSE%' 
	AND ccase.customer_id = 1064 
		
    ORDER BY 
				TRIM(IFNULL(
					CONCAT(first_name,
					' ',
					last_name,
					' vs ',
					IFNULL(employer, ''),
					' - ',
					REPLACE(IF(DATE_FORMAT(start_date, '%m/%d/%Y') IS NULL,
							'',
							DATE_FORMAT(start_date, '%m/%d/%Y')),
						'00/00/0000',
						'')),
					case_name)), 
				case_id, injury_number
) results;
use ikase_patel2;
SELECT DISTINCT cd.`document_id`, cd.`document_uuid`, cd.`parent_document_uuid`, `document_name`, IF (DATE_FORMAT(document_date, '%m/%d/%y %h:%i %p') IS NULL, '', DATE_FORMAT(document_date, '%m/%d/%y %h:%i %p')) `document_date`, `document_filename`, `document_extension`, `thumbnail_folder`, `description`, `description_html`, 
	cd.`source`, IF (DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p') IS NULL, '', DATE_FORMAT(received_date, '%m/%d/%Y %l:%i%p')) `received_date`,
	`type`, `verified`, cd.`deleted`, `document_id` id, cd.`document_uuid` uuid, REPLACE(cb.filename, '.pdf', '') filename, cb.time_stamp, cd.customer_id, IFNULL(`case_id`, '') `case_id`, IFNULL(`case_name`, '') `case_name`,
	'' notification_date, cn.read_date, 
	IF(cn.`notifier`='', cu.nickname,  cn.`notifier`) `notifier`, cn.instructions,
	usr.nickname `uploader_nickname`,
	cbt.user_logon `uploader`, cbt.dateandtime `upload_time`
   
	FROM `cse_document` cd
    
    INNER JOIN (
		SELECT cn.document_uuid, COUNT(cn.notification_id) the_count, GROUP_CONCAT(cn.notification)

		FROM cse_notification cn

		INNER JOIN `cse_document` cd
		ON cn.document_uuid = cd.document_uuid AND cd.deleted = 'N'

		INNER JOIN `cse_batchscan` cb
		ON cd.parent_document_uuid = cb.batchscan_id

		INNER JOIN cse_batchscan_track cbt
		ON cb.batchscan_id = cbt.batchscan_id
			
		WHERE 1
		AND cbt.operation = 'insert' AND cbt.user_uuid = 'TS5f74055921a03'
        AND cd.customer_id = '1042'
		  
		GROUP BY cn.document_uuid
		HAVING COUNT(cn.notification_id) = 1 AND GROUP_CONCAT(cn.notification) != 'completed'
    ) new_scans
    ON cd.document_uuid = new_scans.document_uuid
    
    INNER JOIN `cse_batchscan` cb
    ON cd.parent_document_uuid = cb.batchscan_id
    
    INNER JOIN cse_batchscan_track cbt
	ON cb.batchscan_id = cbt.batchscan_id AND cbt.user_uuid = 'TS5f74055921a03' AND cbt.operation = 'insert'
    
    INNER JOIN ikase.cse_user usr
    ON cbt.user_uuid = usr.user_uuid AND usr.user_uuid = 'TS5f74055921a03'
    
	LEFT OUTER JOIN `cse_notification` cn
	ON cd.document_uuid = cn.document_uuid
	
    LEFT OUTER JOIN ikase.cse_user cu
    ON cn.user_uuid = cu.user_uuid
    
    LEFT OUTER JOIN `cse_case_document` cdoc
	ON cd.document_uuid = cdoc.document_uuid AND cdoc.case_uuid != ''
	LEFT OUTER JOIN `cse_case` ccase
	ON cdoc.case_uuid = ccase.case_uuid AND cdoc.deleted = 'N'
	
	WHERE cd.document_date > '2022-01-11';
use ikase_pag_copy;
SELECT ccase.case_id id, ccase.case_uuid uuid, ccase.lien_filed, ccase.case_number, ccase.cpointer,
		inj.injury_id, inj.adj_number, inj.occupation, inj.start_date, inj.end_date, inj.full_address, inj.street, inj.city, inj.state, inj.zip,
		ccase.case_date, ccase.case_type, venue.venue_uuid, ccase.rating, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims, ccase.injury_type,
		
		venue_corporation.corporation_id venue_id, venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue_abbr, 
		venue_corporation.street venue_street, venue_corporation.city venue_city, 
		venue_corporation.state venue_state, venue_corporation.zip venue_zip,
		
		ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
ccase.attorney, ccase.worker, ccase.interpreter_needed, ccase.case_language `case_language`, 
		app.deleted applicant_deleted, app.person_id applicant_id, app.person_uuid applicant_uuid, app.salutation applicant_salutation, IFNULL(app.full_name, '') `full_name`, app.first_name, app.last_name, app.middle_name, app.`aka`, 
		app.dob, app.gender, app.ssn, app.full_address applicant_full_address, app.street applicant_street, app.suite applicant_suite, app.city applicant_city, app.state applicant_state, app.zip applicant_zip, app.phone applicant_phone, app.fax applicant_fax, app.cell_phone applicant_cell, app.age applicant_age,
		
		IFNULL(employer.`corporation_id`,-1) employer_id, employer.company_name employer, employer.full_name employer_full_name, employer.full_address employer_full_address, employer.street employer_street, employer.city employer_city,
		employer.state employer_state, employer.zip employer_zip,
		
		IFNULL(defendant.`corporation_id`,-1) defendant_id, defendant.company_name defendant, defendant.full_name defendant_full_name, defendant.street defendant_street, defendant.city defendant_city,
		defendant.state defendant_state, defendant.zip defendant_zip,
		
		CONCAT(app.first_name,' ', IF(app.middle_name='','',CONCAT(app.middle_name,' ')),app.last_name,' vs ', IFNULL(employer.`company_name`, '')) `name`, 
		
		IFNULL(att.user_id, '') as attorney_id, 
		IFNULL(att.nickname, '') as attorney_name, 
		IFNULL(att.user_first_name, '') as attorney_first_name, 
		IFNULL(att.user_last_name, '') as attorney_last_name, 
		IFNULL(att.user_name, '') as attorney_full_name, 
		IFNULL(att.user_email, '') as attorney_email, 
		IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name, IFNULL(user.user_email, '') as worker_email,
		IFNULL(lien.lien_id, -1) lien_id, 
		IFNULL(settlement.settlement_id, -1) settlement_id,
		IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id,
		job.job_id worker_job_id, job.job_uuid worker_job_uuid, if(job.job IS NULL, '', job.job) worker_job,
		IFNULL(jfile.jetfile_id, '') jetfile_id, 
		IFNULL(jfile.jetfile_case_id, '') jetfile_case_id, 
		IFNULL(jfile.app_filing_id, '') app_filing_id, 
		IFNULL(jfile.info, '') jetfile_info, 
		IFNULL(jfile.dor_info, '') dor_info, 
		IFNULL(jfile.jetfile_dor_id, '') jetfile_dor_id, 
		IFNULL(jfile.dor_filing_id, '') dor_filing_id, 
		IFNULL(jfile.dore_info, '') dore_info, 
		IFNULL(jfile.jetfile_dore_id, '') jetfile_dore_id, 
		IFNULL(jfile.dore_filing_id, '') dore_filing_id, 
		IFNULL(jfile.lien_info, '') lien_info,
		IFNULL(jfile.jetfile_lien_id, '') jetfile_lien_id,
		IFNULL(jfile.lien_filing_id, '') lien_filing_id,
		IFNULL(jfile.unstruc_info, '') unstruc_info,
		IFNULL(uploads.document_count, 0) uploads_count
		FROM cse_case ccase  
		LEFT OUTER JOIN cse_case_person ccapp ON (ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N')
		LEFT OUTER JOIN cse_person app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN `cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` dcorp
			ON (ccase.case_uuid = dcorp.case_uuid AND ccorp.attribute = 'defendant' AND dcorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` defendant
			ON dcorp.corporation_uuid = defendant.corporation_uuid
			
			LEFT OUTER JOIN `cse_case_corporation` ccorp_venue
			ON (ccase.case_uuid = ccorp_venue.case_uuid AND ccorp_venue.attribute = 'venue' AND ccorp_venue.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` venue_corporation
			ON ccorp_venue.corporation_uuid = venue_corporation.corporation_uuid
			
			INNER JOIN `cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid
			INNER JOIN `cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid
			
			LEFT OUTER JOIN `cse_jetfile` jfile
			ON inj.injury_uuid = jfile.injury_uuid
			
			LEFT OUTER JOIN `cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN `cse_injury_fee` cif ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN `cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN `cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N' AND cis.`attribute` = 'main'
			LEFT OUTER JOIN `cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
			
			LEFT OUTER JOIN ikase.`cse_user_job` cjob
			ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
			LEFT OUTER JOIN ikase.`cse_job` job
			ON cjob.job_uuid = job.job_uuid
			
			LEFT OUTER JOIN (
				SELECT ccase.case_id, COUNT(document_id) document_count
				FROM cse_document cd
				INNER JOIN cse_case_document ccd
				ON cd.document_uuid = ccd.document_uuid
				INNER JOIN cse_case ccase
				ON ccd.case_uuid = ccase.case_uuid
				WHERE 1
				AND `attribute_1` = 'jetfiler'
				AND cd.deleted = 'N'
				AND ccase.case_id = '8353'
				AND ccase.customer_id = '1064'
			) uploads
			ON ccase.case_id = uploads.case_id
			
			WHERE 1
			AND inj.injury_id='8874'
			AND ccase.case_id='8353'
			AND ccase.customer_id = '1064';