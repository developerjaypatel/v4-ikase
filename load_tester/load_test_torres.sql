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