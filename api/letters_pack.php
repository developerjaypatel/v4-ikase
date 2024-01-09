<?php
$app->group('/letter', function (\Slim\Routing\RouteCollectorProxy $app) {
	$app->get('/{id:\d+}', 'getLetter');

	$app->post('/delete', 'deleteLetter');
	$app->post('/add', 'addLetter');
	$app->post('/create', 'createLetter');
	$app->post('/pcreate', 'createLetterByPartieType');
	$app->post('/envelope', 'createLetterEnvelope');
	$app->post('/penvelope', 'createLetterEnvelopeByPartieType');
	$app->post('/updateheader', 'createHeader');

	$app->post('/ready', 'checkReadyLetter');

	$app->post('/customer', 'createCustomerLetter');
	$app->post('/customerclean', 'cleanCustomerLetter');
	$app->post('/update', 'updateLetter');
})->add(\Api\Middleware\Authorize::class);

$app->post('/letter/unlinkit', 'unlinkIt');

function checkReadyLetter() {
	session_write_close();
	$case_id =  passed_var("case_id", "post");
	$path =  passed_var("path", "post");
	$customer_id =  $_SESSION['user_customer_id'];
	
	$destination_folder = UPLOADS_PATH. $customer_id . DC . $case_id . "\\letters\\";
	
	$filepath = $destination_folder . $path . ".pdf";
	if (file_exists($filepath)) {
		$success = true;
	} else {
		$success = false;
	}
	
	echo json_encode(array("success"=>$success, "path"=>$filepath));
}
function createLetterEnvelope() {
	session_write_close();
	$arrReplace = array();
	$customer_id =  $_SESSION['user_customer_id'];
	$corporation_id =  passed_var("corporation_id", "post");
	$partie_type = passed_var("partie_type", "post");
	
	$additional = "";
	if (isset($_POST["additional"])) {
		$additional = passed_var("additional", "post");
	}
	if ($partie_type=="applicant") {
		if ($customer_id==1033) {
			$letter_partie = getPersonXInfo($corporation_id);
		} else {
			$letter_partie = getPersonInfo($corporation_id);
		}
	} else {
		$letter_partie = getCorporationInfo($corporation_id);
	}

	$customer = getCustomerInfo();
	$destination_folder = "../uploads/" . $customer_id . "/envelopes/";
	if (!is_dir($destination_folder)) {
		mkdir($destination_folder, 0755, true);
	}
	$destination = $destination_folder . 'envelope_' . $corporation_id;
	$customer_name = $customer->letter_name ?? $_SESSION['user_customer_name'];
	if ($_SESSION["user_customer_id"]==1042) {
		$customer_name = strtoupper($customer_name);
		$customer->cus_street = strtoupper($customer->cus_street);
		$customer->cus_city = strtoupper($customer->cus_city);
		$customer->cus_state = strtoupper($customer->cus_state);
		$customer->cus_zip = strtoupper($customer->cus_zip);
	}
	$arrReplace['FIRMNAME'] = str_replace("&", "&amp;", $customer_name);
	$arrReplace['FIRMADD1'] = $customer->cus_street;
	$arrReplace['FIRMCITY'] = $customer->cus_city;
	$arrReplace['FIRMSTATE'] = $customer->cus_state;
	$arrReplace['FIRMZIP'] = $customer->cus_zip;
	
	$letter_name = "";
	if ($partie_type!="applicant") {
		if ($letter_partie->type == "carrier" || $letter_partie->type == "defense" || $letter_partie->type == "prior_attorney") {
			$letter_name_info = getAdhocsInfo("", $corporation_id, "letter_name");

			if (count($letter_name_info) > 0) {
				$adhoc_value = trim($letter_name_info[0]->adhoc_value);
				if ($adhoc_value != "") {
					$letter_name = $adhoc_value;
				}
			}		
		}
	}
	
	//letter recipient
	$arrRecipient = array();
	if ($partie_type=="applicant") {
		if (trim($letter_partie->full_name)!="") {
			$arrRecipient[] = $letter_partie->full_name;
		}
	}
	if ($partie_type=="applicant") {
		if ($letter_partie->company_name!="") {
			$arrRecipient[] = $letter_partie->company_name;
		}
	} else {
		if ($letter_name=="") {
			$arrRecipient[] = $letter_partie->company_name;
		} else {
			$arrRecipient[] = $letter_name;
		}
		//echo $letter_name;
		//die(print_r($letter_partie));
	}
	if ($partie_type!="applicant") {
		if (trim($letter_partie->full_name)!="") {
			//PER JAZMIN 9/14/2018
			if ($customer_id=="1055") {
				$arrRecipient[] = "Attn: " . $letter_partie->full_name;
			} else {
				$arrRecipient[] = capWords($letter_partie->employee_title) . ": " . $letter_partie->full_name;
			}
		}
	}
	$arrRecipient[] = $letter_partie->street;
	if ($letter_partie->suite!="") {
		$arrRecipient[] = $letter_partie->suite;
	}
	$arrRecipient[] = $letter_partie->city . ", " . $letter_partie->state . " " . $letter_partie->zip;
	
	if ($additional=="y" && $letter_partie->additional_addresses!="") {
		$arrRecipient = array();
		if ($letter_name=="") {
			$arrRecipient[] = $letter_partie->company_name;
		} else {
			$arrRecipient[] = $letter_name;
		}
		$additional_addresses = $letter_partie->additional_addresses;
		$letter_partie = json_decode($additional_addresses);
		
		$arrRecipient[] = $letter_partie->address_2[2];
		if ($letter_partie->address_2[1]!="") {
			$arrRecipient[] = $letter_partie->address_2[1];
		}
		$arrRecipient[] = $letter_partie->address_2[3] . ", " . $letter_partie->address_2[4] . " " . $letter_partie->address_2[5];
	}
	
	$partie_info = implode("\\n ", $arrRecipient);
	/*
	$new_block = array(
		implode(",", $arrBlock)
	);
	
	$arrPartiesReturn[] = $new_block;
	*/
	if ($_SESSION["user_customer_id"]==1042) {
		$partie_info = strtoupper($partie_info);
		$partie_info = str_replace("\\N", "\\n", $partie_info);
	}
	$arrReplace['PARTIEINFORMATION'] = $partie_info;
	
	//die(print_r($arrReplace));
	$variables = $arrReplace;
	
	$template_path = '../uploads/envelope_info.docx';
	if ($_SESSION["user_customer_id"]==1042) {
		$template_path = '../uploads/envelope_info_bold.docx';
	}
	if ($_SESSION["user_customer_id"]==1134) {
		$template_path = '../uploads/envelope_info_1134.docx';
	}
	
	$docx = new CreateDocxFromTemplate($template_path);
    $options = ['parseLineBreaks' => true];
    $docx->replaceVariableByText($variables, $options);
	
	$docx->createDocx($destination); 
	//die("<a href='" . $destination . ".docx' target='_blank'>" . str_replace("../uploads/", "", $destination) . ".docx</a>");
    echo json_encode(["file" => $destination.".docx"]);
}

function createLetterEnvelopeByPartieType() {
	//Letters to all Defense Attorneys, Carriers, Applicants on open cases
	session_write_close();
	
	$customer_id =  $_SESSION['user_customer_id'];
	$partie_type = passed_var("partie_type", "post");
	$arrAcceptableTypes = array("defense", "carrier", "applicant", "plaintiff", "defendant");
	$additional = "y";
	
	//get all the parties
	if ($partie_type=="applicant") {
		$sql = "SELECT pers.person_id partie_id, pers.full_name, cases.case_id, cases.employer, adj_numbers 
		FROM ";
		//$sql .= "`cse_person`";
		
		if (($_SESSION['user_customer_id']==1033)) {
			$sql_encrypt = SQL_PERSONX;
			$sql_encrypt = str_replace("SET utf8)", "SET utf8) COLLATE utf8_general_ci", $sql_encrypt);
			$sql .= "(" . $sql_encrypt . ")";
		} else {
			$sql .= "`cse_person`";
		}
		$sql .= " pers
		INNER JOIN cse_case_person ccp
		ON pers.person_uuid = ccp.person_uuid AND ccp.deleted = 'N'
		INNER JOIN (
			SELECT kase.case_id, kase.case_uuid, IFNULL(employer.company_name, '') employer  
			FROM cse_case kase
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (kase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			WHERE kase.`case_status` NOT LIKE '%Close%'
			AND kase.`case_status`!= 'Dropped'
			AND kase.customer_id = :customer_id
		) cases
		ON ccp.case_uuid = cases.case_uuid
		INNER JOIN (
			SELECT ccase.case_uuid, GROUP_CONCAT(inj.adj_number) adj_numbers
			FROM cse_injury inj
			INNER JOIN cse_case_injury cci
			ON inj.injury_uuid = cci.injury_uuid
			INNER JOIN cse_case ccase
			ON cci.case_uuid = ccase.case_uuid
			WHERE 1
			AND ccase.customer_id = :customer_id
			AND cci.deleted = 'N'
			AND inj.deleted = 'N'
			GROUP BY ccase.case_uuid
		) adjs
		ON ccp.case_uuid = adjs.case_uuid
		WHERE pers.customer_id = :customer_id
		AND pers.deleted = 'N'
		ORDER BY pers.first_name, pers.last_name";
	} else {
		$sql = "SELECT corp.corporation_id partie_id, app.full_name, cases.case_id, cases.employer, adj_numbers 
		FROM cse_corporation corp
		INNER JOIN cse_case_corporation ccc
		ON corp.corporation_uuid = ccc.corporation_uuid AND ccc.deleted = 'N'
		INNER JOIN (
			SELECT kase.case_id, kase.case_uuid, IFNULL(employer.company_name, '') employer  
			FROM cse_case kase
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (kase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			WHERE kase.`case_status` NOT LIKE '%Close%'
			AND kase.`case_status`!= 'Dropped'
			AND kase.customer_id = :customer_id
		) cases
		ON ccc.case_uuid = cases.case_uuid
		INNER JOIN (
			SELECT ccase.case_uuid, GROUP_CONCAT(inj.adj_number) adj_numbers
			FROM cse_injury inj
			INNER JOIN cse_case_injury cci
			ON inj.injury_uuid = cci.injury_uuid
			INNER JOIN cse_case ccase
			ON cci.case_uuid = ccase.case_uuid
			WHERE 1
			AND ccase.customer_id = :customer_id
			AND cci.deleted = 'N'
			AND inj.deleted = 'N'
			GROUP BY ccase.case_uuid
		) adjs
		ON ccc.case_uuid = adjs.case_uuid";
		$sql .= " 
			LEFT OUTER JOIN cse_case_person ccapp 
			ON (cases.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N')
			LEFT OUTER JOIN ";
		if (($_SESSION['user_customer_id']==1033)) { 
			$sql .= "(" . SQL_PERSONX . ")";
		} else {
			$sql .= "cse_person";
		}
		$sql .= " app ON ccapp.person_uuid = app.person_uuid

		WHERE corp.`type` = :partie_type
		AND corp.customer_id = :customer_id
		AND corp.deleted = 'N'
		ORDER BY corp.company_name, corp.full_name";
	}
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("partie_type", $partie_type);
		$stmt->execute();
		$parties = $stmt->fetchAll(PDO::FETCH_OBJ);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
        	echo json_encode($error);
	}
	
	$customer = getCustomerInfo();
	$destination_folder = "../uploads/" . $customer_id . "/envelopes/";
	if (!is_dir($destination_folder)) {
		mkdir($destination_folder, 0755, true);
	}
	$arrEnvelopes = array();
	$arrCorporationIDs = array();
	$arrPartieInfos = array();
	$first_document = "";
	
	foreach($parties as $partie) {
		$corporation_id = $partie->partie_id;
		$arrCorporationIDs[] = $corporation_id;
		$arrReplace = array();
		if ($partie_type=="applicant") {
			if ($customer_id==1033) {
				$letter_partie = getPersonXInfo($corporation_id);
			} else {
				$letter_partie = getPersonInfo($corporation_id);
			}
		} else {
			$letter_partie = getCorporationInfo($corporation_id);
		}
		
		$destination = $destination_folder . 'envelope_' . $corporation_id;
		
		$arrReplace['FIRMNAME'] = str_replace("&", "&amp;", $_SESSION['user_customer_name']);
		$arrReplace['FIRMADD1'] = $customer->cus_street;
		$arrReplace['FIRMCITY'] = $customer->cus_city;
		$arrReplace['FIRMSTATE'] = $customer->cus_state;
		$arrReplace['FIRMZIP'] = $customer->cus_zip;
		$partie_name = "";
		if ($partie_type!="applicant") {
			$partie_name = $letter_partie->company_name;
			if ($letter_partie->type == "carrier" || $letter_partie->type == "defense" || $letter_partie->type == "prior_attorney") {
				$letter_name = getAdhocsInfo("", $corporation_id, "letter_name");
				if (count($letter_name) > 0) {
					$partie_name = $letter_name[0]->adhoc_value;
				}		
			}
		}
		
		//letter recipient
		$arrRecipient = array();
		if ($partie_type=="applicant") {
			if (trim($letter_partie->full_name)!="") {
				$partie_name = $letter_partie->full_name;
			}
		}
		if ($partie_type=="applicant") {
			if ($letter_partie->company_name!="") {
				$partie_name = $letter_partie->company_name;
			}
		}
		//$arrRecipient[] = noAmpersand($partie_name);
		$arrReplace['PARTIENAME'] = noAmpersand($partie_name);
		$arrRecipient[] = noAmpersand($letter_partie->street);
		if ($letter_partie->suite!="") {
			$arrRecipient[] = noAmpersand($letter_partie->suite);
		}
		$arrRecipient[] = noAmpersand($letter_partie->city) . ", " . $letter_partie->state . " " . $letter_partie->zip;
		if ($partie_type!="applicant") {
			$arrRecipient[] = "";
			if (trim($letter_partie->full_name)!="") {
				//$arrRecipient[] = "";
				$arrRecipient[] = noAmpersand($letter_partie->employee_title) . ": " . noAmpersand($letter_partie->full_name);
			}
			//show the applicant
			//$arrRecipient[] = "RE:" . noAmpersand($partie->full_name);
			$arrReplace['CASENAME'] = "RE:" . noAmpersand(trim($partie->full_name));
		} else {
			//$arrRecipient[] = "";
			//$arrRecipient[] = "RE:" . noAmpersand($partie->full_name . " vs " . $partie->employer);
			$arrReplace['CASENAME'] = "RE:" . noAmpersand(trim($partie->full_name . " vs " . $partie->employer));
		}
		
		if ($additional=="y" && $letter_partie->additional_addresses!="") {
			$arrRecipient = array();
			$arrRecipient[] = noAmpersand($letter_partie->company_name);
			$additional_addresses = $letter_partie->additional_addresses;
			$letter_partie = json_decode($additional_addresses);
			
			$arrRecipient[] = $letter_partie->address_2[2];
			if ($letter_partie->address_2[1]!="") {
				$arrRecipient[] = noAmpersand($letter_partie->address_2[1]);
			}
			$arrRecipient[] = noAmpersand($letter_partie->address_2[3]) . ", " . $letter_partie->address_2[4] . " " . $letter_partie->address_2[5];
			//$arrRecipient[] = "";
		}
		$partie_info = implode("\\n", $arrRecipient);
		
		if (!in_array($partie_info, $arrPartieInfos)) {
			$arrPartieInfos[] = $partie_info;
		} else {
			//already did that one, skip it
			continue;
		}
		/*
		$new_block = array(
			implode(",", $arrBlock)
		);
		
		$arrPartiesReturn[] = $new_block;
		*/
		$arrReplace['PARTIEINFORMATION'] = $partie_info;
		
		//die(print_r($arrReplace));
		$variables = $arrReplace;
		
		$docx = new CreateDocxFromTemplate('../uploads/envelope_parties.docx');
		$options = array('parseLineBreaks' =>true);
		$docx->replaceVariableByText($variables, $options);
		
		$docx->createDocx($destination); 
		
		
		if ($first_document == "") {
			$first_document = $destination . ".docx";
		} else {
			$arrEnvelopes[] = $destination . ".docx";
		}
	}
	if (count($arrEnvelopes)==0) {
		echo json_encode(array("success"=>false, "file"=>""));
		die();
	}
	$destination = $destination_folder . 'envelopes_' . $partie_type . ".docx";
	$merge = new MultiMerge();
	$merge->mergeDocx($first_document, $arrEnvelopes, $destination, array("mergeType"=>0));
	
	echo json_encode(array("success"=>true, "file"=>$destination));
	/*
	sleep(5);
	
	$destination_folder = UPLOADS_PATH. $customer_id . "\\envelopes\\";
	foreach($arrCorporationIDs as $corporation_id) {
		$destination = $destination_folder . 'envelope_' . $corporation_id . ".docx";
		
		if (file_exists($destination)) {
			unlink($destination);
		}
	}
	*/
}
function unlinkIt() {
	/*
	try {
		$filename = UPLOADS_PATH."1111\\envelopes\\envelope_1034.docx";
		unlink($filename);
		die($filename . " unlinked");
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
	*/
}
/*
require_once 'classes/MultiMerge.inc';

$merge = new MultiMerge();

$merge->mergeDocx('Text.docx', array('second.docx', 'SimpleExample.docx'), 'example_merge_docx.docx', array());
*/
function getLetters($case_id) {
	session_write_close();
    $sql = "SELECT DISTINCT csl.*, csl.letter_id id, csl.letter_uuid uuid
			FROM  `cse_letter` csl
			INNER JOIN `cse_case_letter` ccl 
			ON csl.letter_uuid = ccl.letter_uuid
			INNER JOIN `cse_case` cc
			ON ccl.case_uuid = cc.case_uuid
			WHERE `csl`.`deleted` = 'N'
			AND `ccl`.`deleted` = 'N'
			AND cc.case_id = :case_id
			AND `csl`.customer_id = " . $_SESSION['user_customer_id'] . "
			ORDER BY  `csl`.letter_id DESC ";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$letters = $stmt->fetchAll(PDO::FETCH_OBJ);

        // Include support for JSONP requests
         echo json_encode($letters);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getLetter($letter_id) {
	session_write_close();
	$sql = "SELECT `cse_letter`.*, `cse_letter`.`letter_id` `id`,  `cse_letter`.`letter_uuid` `uuid`
			FROM `cse_letter` 
			WHERE `cse_letter`.`deleted` = 'N'
			AND `cse_letter`.`letter_id` = :letter_id
			AND `cse_letter`.customer_id = " . $_SESSION['user_customer_id'];
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("letter_id", $letter_id);
		$stmt->execute();
		$note = $stmt->fetchObject();

        //TODO: Include support for JSONP requests
        echo json_encode($note);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getLetterInfo($letter_id) {
	session_write_close();
	$sql = "SELECT `cse_letter`.*, `cse_letter`.`letter_id` `id`,  `cse_letter`.`letter_uuid` `uuid`
			FROM `cse_letter` 
			WHERE `cse_letter`.`deleted` = 'N'
			AND `cse_letter`.`letter_id` = :letter_id
			AND `cse_letter`.customer_id = " . $_SESSION['user_customer_id'];
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($letter_id!="") {
			$stmt->bindParam("letter_id", $letter_id);
		}
		$stmt->execute();
		$note = $stmt->fetchObject();
		return $note;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getLettersByType($type, $case_id) {
	session_write_close();
	$sql = "SELECT `cse_letter`.`letter_id`, `cse_letter`.`letter_uuid`, `note`, 
	`cse_letter`.`title`, `subject`, 
	`entered_by`, `attachments`, `status`, `dateandtime`, `callback_date`, `verified`, `cse_letter`.`deleted`, `type`, `cse_letter`.`customer_id`, `cse_letter`.`letter_id` `id`,  `cse_letter`.`letter_uuid` `uuid`, `cse_case`.case_id
	FROM `cse_letter` 
	INNER JOIN  `cse_case_letters` ON  `cse_letter`.`letter_uuid` =  `cse_case_letters`.`letter_uuid` 
	INNER JOIN `cse_case` ON  (`cse_case_letters`.`case_uuid` = `cse_case`.`case_uuid`)
	WHERE `cse_letter`.`deleted` = 'N'
	AND `cse_case`.`case_id` = :case_id
	AND `cse_letter`.`type` = :type
	AND `cse_letter`.customer_id = " . $_SESSION['user_customer_id'] . "
	ORDER BY `cse_letter`.letter_id DESC
	";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("type", $type);
		$stmt->execute();
		$letters = $stmt->fetchAll(PDO::FETCH_OBJ);

        // Include support for JSONP requests
         echo json_encode($letters);        
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function deleteLetter() {
	$id = passed_var("id", "post");
	$sql = "UPDATE `cse_letter` 
			SET `deleted` = 'Y'
			WHERE `letter_id`=:id
			AND `cse_letter`.customer_id = " . $_SESSION['user_customer_id'];
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		echo json_encode(array("success"=>"letter marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	//trackDocument("delete", $id);
}
function createHeader() {
	session_write_close();
	
	$arrReplace = array();
	$arrReplace['DATE'] = date("m/d/Y");
	$arrReplace['PROVNAME1'] = "Nick G";
	$arrReplace['CASENAME'] = "Tommy vs Terriel";
	
	foreach($arrReplace as $replace_index=>$replace) {
		if (strpos($replace, "&amp;")===false && strpos($replace, "&")!==false) {
			$replace = str_replace("&", "&amp;", $replace);
			//die($replace_index . " = " . $replace);
			$arrReplace[$replace_index] = $replace;
		}
	}
	
	$variables = $arrReplace;
	//FIXME: wtf, isn't "uploads" only for user uploaded files? temporary stuff?
	$docx = new CreateDocxFromTemplate('../uploads/1042/New Letterhead_17064202823.docx');
	$destination = '../uploads/1042/headers/letterhead_' . date("YmdHis");
	/*
	if ($template->source!="no_letterhead" && $template->source!="clientname_letterhead") {
		$docx ->importHeadersAndFooters('../uploads/' . $customer_id . "/" . $letterhead->value);
		
	}
	*/
	$options = array('parseLineBreaks' =>true);
	
	$docx->replaceVariableByText($variables, $options);
	
	$wf = new WordFragment($docx, 'header');
	$text[] =
	array(
	'text' => 'nic nick nick',
	'color' => 'B70000'
	);
	$wf->addText($text);
	$docx->replaceVariableByWordFragment(array('CASENAME' => $wf), array('type' => 'block'));
	/*
		$headerText = new WordFragment($docx, 'defaultHeader');
		$textOptions = array(
		'bold' => true
		);
		$headerText->addText("DATE:" . date("m/d/Y"), $textOptions);
		$headerText->addText("CASENAME: Nick vs Terriel", $textOptions);
		$valuesTable = array(
			array(
				array('value' =>$headerText, 'vAlign' => 'left')
			),
		);
		$widthTableCols = array(
			7500,
			700,
			500
		);
		$paramsTable = array(
			'border' => 'nil',
			'columnWidths' => $widthTableCols,
		);
		$headerTable = new WordFragment($docx, 'defaultHeader');
		$headerTable->addTable($valuesTable, $paramsTable);
		
		//add some text to the body of the document
		$docx->addHeader(array('odd' => $headerTable));
	*/
	$docx->createDocx($destination); 
	
	die("<a href='" . $destination . ".docx' target='_blank'>" . $destination . "</a>");
}
function cleanCustomerLetter() {
	session_write_close();
	
	$customer_id = passed_var("customer_id", "post");;
	
	$dir = UPLOADS_PATH. $customer_id . "\\announce\\";
	
	$files = scandir($dir);
	$prefix =  date("Ymd");
	
	foreach($files as $file) {
		if (strpos($file, ".docx")!==false) {
			if (strpos($file, $prefix)===false) {
				unlink($dir . $file);
			}
		}
	}
	
	echo "done";
}
function createCustomerLetter() {
	session_write_close();
	
	$letter = passed_var("letter", "post");
	$customer_id = $_SESSION["user_customer_id"];
	$dir = UPLOADS_PATH. $customer_id . "\\announce\\";
	
	$customer = getCustomerInfo();
	
	$files = scandir($dir);
	$prefix =  date("Ymd");
	
	foreach($files as $file) {
		if (strpos($file, ".docx")!==false) {
			if (strpos($file, $prefix)===false) {
				unlink($dir . $file);
			}
		}
	}
	
	$sql = "SELECT doc.* 
	FROM cse_document doc
	WHERE doc.document_name LIKE '%" . $letter . "%.docx'
	AND doc.customer_id = :customer_id
	AND doc.`type` = 'template'
	AND doc.deleted = 'N'";
	
	//die($sql);
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	//$stmt->bindParam("document_name", $template_name);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$template_parents = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	$int = 0;
	$counter = 0;
	$max = 100;
	
	$rowcount = 1;
	while($rowcount > 0) {
		$counter++;
		
		//increment for the day for now
		$prefix =  date("Ymd") . "_" . $int;
		
		$sql = "SELECT DISTINCT pers.full_name, pers.first_name, pers.last_name, 
		pers.full_address, pers.street, pers.suite, pers.city, pers.state, pers.zip
		FROM `cse_person` pers 
		INNER JOIN `cse_case_person` cper
		ON pers.person_uuid = cper.person_uuid AND cper.deleted = 'N'
		INNER JOIN `cse_case` cse
		ON cper.case_uuid = cse.case_uuid
		WHERE pers.deleted = 'N'
		AND pers.customer_id = :customer_id
		AND pers.first_name != ''
		AND pers.first_name NOT LIKE '%[%'
		AND pers.first_name NOT LIKE '%(%'
		AND pers.last_name NOT LIKE '%[%'
		AND pers.last_name NOT LIKE '%(%'
		AND case_status != 'Closed'
		AND case_status != 'Dropped'
		ORDER BY TRIM(pers.full_name)
		LIMIT " . $int . ", " . $max;
		
		//echo $sql . "\r\n";
		
		$int += $max;
		echo $int  . "\r\n" . "\r\n";
		try {
			$db = getConnection();		
			$stmt = $db->prepare($sql);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			$applicants = $stmt->fetchAll(PDO::FETCH_OBJ);
			$rowcount = count($applicants);
			
			if ($rowcount == 0) {
				continue;
			}
			//die("found:"  . $rowcount);
			
			$options = array('parseLineBreaks' =>true);
			$arrDestination[$int] = array();
			$arrDestinationEnv[$int] = array();
			
			//die(print_r($template_parents));
			foreach($template_parents as $template) {
				$arrTemplate = explode("_", $template->document_name);
				$language = str_replace(".docx", "", $arrTemplate[2]);
				if (!isset($arrDestination[$int][$language])) {
					$arrDestination[$int][$language] = array();
					$arrDestinationEnv[$int][$language] = array();
				}
				foreach($applicants as $app) {
					$full_name =  capWords(strtolower($app->full_name));
					$applicant_from_address = "";
					
					if ($app->street!="") {
						$applicant_from_address = capWords(strtolower($app->street)) . "\\n" . capWords(strtolower($app->city)) . ", " . strtoupper($app->state) . " " . $app->zip;
					}
					$case_id = str_replace(" ", "_", strtolower($app->last_name));
					$filename = str_replace(".docx", "", $template->document_filename);
					
					$destination = '../uploads/' . $customer_id . '/announce/' . $case_id . '_' . $filename;
					$destination_local = $dir .  $case_id . "_" . $filename . ".docx";
					
					if (file_exists($destination)) {
						unlink($destination);
					}
					$arrReplace = array();
					
					$arrReplace['APPLICANTNAME'] = $full_name;
					if ($applicant_from_address!="") {
						$arrReplace['SENDTO'] = $full_name . "\\n" . $applicant_from_address;
					} else {
						$arrReplace['SENDTO'] = "";
					}
					
					$env_destination =  '../uploads/' . $customer_id . '/announce/envelope_' . $case_id . '_' . $filename;
					$destination_env = $dir .  'envelope_' . $case_id . '_' . $filename . '.docx';
					
					/*
					$arrReplace['FIRMNAME'] = str_replace("&", "&amp;", $_SESSION['user_customer_name']);
					$arrReplace['FIRMADD1'] = $customer->cus_street;
					$arrReplace['FIRMCITY'] = $customer->cus_city;
					$arrReplace['FIRMSTATE'] = $customer->cus_state;
					$arrReplace['FIRMZIP'] = $customer->cus_zip;
					*/
					$arrReplace['FIRMNAME'] = "";
					$arrReplace['FIRMADD1'] = "";
					$arrReplace['FIRMCITY'] = "";
					$arrReplace['FIRMSTATE'] = "";
					$arrReplace['FIRMZIP'] = "";
					
					
					$arrReplace['PARTIEINFORMATION'] = $full_name . "\\n" . $applicant_from_address;
					//die(print_r($arrReplace));
					$variables = $arrReplace;
					
					$docx = new CreateDocxFromTemplate('../uploads/' . $customer_id . '/templates/' . $template->document_filename);
					$docx->replaceVariableByText($variables, $options);
					
					
					$docx->createDocx($destination); 
					
					$arrDestination[$int][$language][] = $destination_local;
					
					
					$docx = new CreateDocxFromTemplate('../uploads/envelope_info_noheader.docx');
					$options = array('parseLineBreaks' =>true);
					$docx->replaceVariableByText($variables, $options);
					
					$docx->createDocx($env_destination); 
					
					$arrDestinationEnv[$int][$language][] = $destination_env;
					//die($destination);
				}
			}
			//die(print_r($arrDestination[$int]));
			echo "about " . $prefix . "\r\n";
			
			foreach($arrDestination[$int] as $language=>$destination) {		
				$file1 = $destination[0];
				unset($destination[0]);
				$final_destination =  $prefix . "_" . $letter . "_" . $language . ".docx";
				
				echo "merging " . $final_destination . "\r\n\r\n";
				
				$merge = new MultiMerge();
				$merge->mergeDocx($file1, $destination, '../uploads/' . $customer_id . '/announce/' . $final_destination, array());
			}
			
			foreach($arrDestinationEnv[$int] as $language=>$destination) {		
				$file1 = $destination[0];
				unset($destination[0]);
				$final_destination =  $prefix . "_" . $letter . "_env_" . $language . ".docx";
				
				echo "merging env " . $final_destination . "\r\n\r\n";
				
				$merge = new MultiMerge();
				$merge->mergeDocx($file1, $destination, '../uploads/' . $customer_id . '/announce/' . $final_destination, array());
			}
		
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
		}
	}
	
	$url = "https://www.ikase.org/api/letter/customerclean";
	$params = array("customer_id"=>$customer_id);
	
	curl_post_async($url, $params);
	
	die("done");
}
function createLetter() {
	session_write_close();

	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$header_start_time = $time;
	
	$letter = passed_var("letterInput", "post");
	$pages = passed_var("pages", "post");
	$rush = passed_var("rush", "post");
	$judge = passed_var("judge_dropdown", "post");
	$judge = str_replace("~", "'", $judge);
	
	if ($rush!="Y") {
		$rush = "";
	}
	$last_date = passed_var("last_date", "post");
	$deposition_party = passed_var("deposition_party", "post");
	$deposition_office = passed_var("deposition_office", "post");
	$deposition_location = passed_var("deposition_location", "post");
	$deposition_address = passed_var("deposition_address", "post");
	$deposition_county = passed_var("deposition_county", "post");
	$deposition_fee = passed_var("deposition_fee", "post");
	$deposition_court_order_date = passed_var("deposition_court_order_date", "post");
	
	if ($deposition_court_order_date!="") {
		$deposition_court_order_date = date("m/d/Y");
	}
	$arrDepoAdditional = array();
	//depo additional
	foreach($_POST as $fieldname=>$value) {
		if (strpos($fieldname, "depo_") === false) {
			continue;
		}
		
		$arrDepoAdditional[$fieldname] = $value;
	}
	$depo_additional = json_encode($arrDepoAdditional);
	
	$arrKInvoice = array();
	//depo additional
	foreach($_POST as $fieldname=>$value) {
		if (strpos($fieldname, "kinv_") === false) {
			continue;
		}
		$fieldname = str_replace("kinv_", "", $fieldname);
		$arrKInvoice[$fieldname] = $value;
	}
	$kinv_data = json_encode($arrKInvoice);
	if ($kinv_data=="[]") {
		$kinv_data = "";
	}
	$template = passed_var("table_id", "post");
	//die($template);
	$customer_id =  $_SESSION['user_customer_id'];
	$case_id =  passed_var("case_id", "post");
	$any_partie_id =  passed_var("any_id", "post");
	$user_id =  $_SESSION['user_id'];
	$doi_id = "";
	$adj_number = "";
	$arrDOIs = array();
	$arrDOIDates = array();
	$arrClaims = array();
	$blnClaimsFilled = false;
	//die(print_r($_POST) . " - doi");
	if (isset($_POST["doi"])){
		//if (is_array($_POST["doi"])) {
		$the_doi = $_POST["doi"];
		//die("doi:" . $the_doi);
		if (strpos($the_doi, "|") !== false) {
			//die(print_r($_POST["doi"]));
			$arrDOIs = explode("|", $the_doi);
			
			//now we want to get the adj numbers
			$sql = "SELECT inj.injury_id, inj.injury_uuid, inj.adj_number, inj.start_date, inj.end_date, 
			cin.alternate_policy_number claim_number 
			FROM cse_injury inj
            INNER JOIN cse_injury_injury_number ccin
            ON inj.injury_uuid = ccin.injury_uuid
            INNER JOIN cse_injury_number cin
            ON ccin.injury_number_uuid = cin.injury_number_uuid 
			WHERE inj.injury_id IN (" . implode(", ", $arrDOIs) . ")
			ORDER BY inj.start_date";
			
			try {
				$adj_injuries = DB::select($sql);
				
				$arrADJs = array();
				foreach($adj_injuries as $adj_injury) {
					$arrADJs[] = $adj_injury->adj_number;
					$arrDOIDates[] = array("start_date"=>$adj_injury->start_date, "end_date"=>$adj_injury->end_date);
					if ($adj_injury->claim_number!="") {
						$arrClaims[] = $adj_injury->claim_number;
					}
					//maybe a carrier level
					$sql = "SELECT cca.adhoc_value claim_number 
					FROM cse_corporation corp
					
					INNER JOIN cse_case_corporation ccc
					ON corp.corporation_uuid = ccc.corporation_uuid AND ccc.deleted = 'N'
					
					INNER JOIN cse_corporation_adhoc cca
					ON corp.corporation_uuid = cca.corporation_uuid AND cca.deleted = 'N'
					
					WHERE attribute = 'carrier'
					AND injury_uuid = :injury_uuid
					AND adhoc = 'claim_number'
					AND corp.customer_id = :customer_id
					LIMIT 0, 1";
					
					$the_injury_uuid = $adj_injury->injury_uuid;
					
					$db = getConnection();
					$stmt = $db->prepare($sql);
					$stmt->bindParam("customer_id", $customer_id);
					$stmt->bindParam("injury_uuid", $the_injury_uuid);
					
					$stmt->execute();
					$carrier = $stmt->fetchObject();
					
					if (is_object($carrier)) {
						if (!in_array($carrier->claim_number, $arrClaims)) {
							$arrClaims[] = $carrier->claim_number;
						}
					}
				}
				if (count($arrClaims) > 0) {
					$blnClaimsFilled = true;
				}
			} catch(PDOException $e) {
				$error = array("error"=> array("text"=>$e->getMessage()));
				echo json_encode($error);
			}
			//for now
			$doi_id = $arrDOIs[0];
		} else {
			$doi_id = passed_var("doi", "post");
		}
	}
	//die($doi_id . " - doi_id");
	if ($doi_id == "") {
		$doi_id = $case_id;
	}
	$adjuster = passed_var("adjuster", "post");
	$carrier_id = passed_var("carrier", "post");
	$defense_id = passed_var("defense", "post");
	$referral_id = passed_var("referral", "post");
	$law_enforcement_id = "-1";
	if (isset($_POST["law_enforcement"])) {
		$law_enforcement_id = passed_var("law_enforcement", "post");
	}
	$witness_id = "-1";
	if (isset($_POST["witness"])) {
		$witness_id = passed_var("witness", "post");
	}
	$employer_id = passed_var("employer", "post");
	$primary_id = passed_var("primary", "post");
	$lien_holder_id = passed_var("lien_holder", "post");
	$uef_id = passed_var("uef", "post");
	$defendant_id = passed_var("defendant", "post");
	//parties may have been selected from master list
	$partie_ids = passed_var("partie_ids", "post");
	
	$arrLetterParties = array();
	$parties_applicant = "N";
	//parties included in letter
	$parties_employer = "N";
	$parties_uef = "N";
	$parties_carrier = "N";
	$parties_defense = "N";
	$parties_primary = "N";
	$parties_lien_holder = "N";
	
	$arrPartieIDs = array();
	foreach($_POST as $fieldname=>$value) {
		//looking for parties
		$strpos = strpos($fieldname, "event_partie_");
		if ($strpos!==false) {
			$value = passed_var($fieldname, "post");
			$arrPartieIDs[] = $value;
		}
	}
	$partie_ids = implode(";", $arrPartieIDs);
	
	if ($partie_ids=="") {
		$parties_applicant = passed_var("parties_applicant", "post");
		$parties_employer = passed_var("parties_employer", "post");
		$parties_uef = passed_var("parties_uef", "post");
		$parties_carrier = passed_var("parties_carrier", "post");
		$parties_defense = passed_var("parties_defense", "post");
		$parties_primary = passed_var("parties_primary", "post");
		$parties_lien_holder = passed_var("parties_lien_holder", "post");
	}
	
	//get the template from the id
	$blnNormalReturn = true;
	if (is_numeric($template)) {
		$sql = "SELECT document_id id, document_uuid uuid, document_filename, source 
		FROM cse_document 
		WHERE document_id = " . $template . "
		AND customer_id = " . $customer_id;
	} else {
		//we are not doing a standard return, this is a med index report, 
		//track activity but do not add to docs
		
		$blnNormalReturn = false;
		//if ($template!="medindex") {
		$sql = "SELECT document_id id, document_uuid uuid, document_filename, source 
		FROM cse_document 
		WHERE document_filename = 'kase_" . $template . ".docx'
		AND `type` = 'template'
		AND deleted ='N'
		AND customer_id = " . $customer_id;
	}
	
	try {
		if ($_SERVER['REMOTE_ADDR'] == "47.153.49.248") {
			//die($sql);
		}
		$stmt = DB::run($sql);
		$template = $stmt->fetchObject();
		if ($_SERVER['REMOTE_ADDR'] == "47.153.49.248") {
			//die(print_r($template));
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
	$blnFax = false;	
	
	$template_name = $template->document_filename;
	if (strpos(strtolower($template_name), "fax") === 0) {
		$blnFax = true;
	}
	
	//get kase info, parties, etc..
	$kase = getKaseInfo($case_id);
	
	$blnWCAB = checkWCAB($kase->case_type);
	
	if (!$blnWCAB) {
		//get plaintiff for applicant
		if (trim($kase->first_name)=="") {
			$plaintiffs = getKaseParties($case_id, "", true, "plaintiff");
			if (count($plaintiffs) > 0) {
				$kase_plaintiff = $plaintiffs[0];
				
				if (trim($kase_plaintiff->full_name)=="") {
					$kase_plaintiff->full_name = $kase_plaintiff->company_name;
				}
				if (trim($kase_plaintiff->first_name)=="" && trim($kase_plaintiff->full_name)!="") {
					$arrName = explode(" ", $kase_plaintiff->full_name);
					$kase_plaintiff->first_name = $arrName[0];
					unset($arrName[0]);
					$kase_plaintiff->last_name = implode(" ", $arrName);
				}
				$kase_plaintiff_salutation = "Sir/Madam";
				
				if (trim($kase_plaintiff->full_name)!="") {
					$kase_plaintiff_salutation = $kase_plaintiff->full_name;
					if ($kase_plaintiff->salutation!="") {
						$kase_plaintiff_salutation = 	$kase_plaintiff->salutation . " " . $kase_plaintiff_salutation;
					}
				}
				//die(print_r($kase_plaintiff));
			}
		}
	}
	$venue = getKaseVenueInfo($case_id);
	$customer = getCustomerInfo();
	
	if ($customer->eams_no!="") {
		$customer_eams = getEamsRepByNumber($customer->eams_no);
	}
	
	//this should be done in the prep screen IF there is more than one defense
	$claim = "";
	$claim_number = "";
	$applicant_email = "";
	$applicant_cell = "";
	if ($partie_ids!="") {
		foreach($_POST as $fieldname=>$value) {
			//looking for parties
			$strpos = strpos($fieldname, "event_partie_");
			if ($strpos!==false) {
				$value = passed_var($fieldname, "post");
				$value = trim($value);
				
				//could be a person, could be a corporation
				$perspos = strpos($value, "P");
				$corppos = strpos($value, "C");
				
				if ($perspos===false && $corppos===false) {
					continue;
				}
				
				$partie = (object) '';
				
				if ($perspos!==false) {
					$person_id = str_replace("P", "", $value);
					$partie = getPersonInfo($person_id);
					$partie->type = "applicant";
					$applicant_email = $partie->email;
					$applicant_cell = $partie->cell_phone;
				}
				if ($corppos!==false) {
					$corporation_id = str_replace("C", "", $value);
					$partie = getCorporationInfo($corporation_id);
				}

				$arrLetterParties[] = $partie;
			}
		}
	}
	$employer = getCorporationInfo($employer_id);
	$uef = getCorporationInfo($uef_id);
	$defendant = getCorporationInfo($defendant_id);
	
	$carrier = getCorporationInfo($carrier_id);
	$claim = getAdhocsInfo($kase->id, $carrier_id, "claim_number");
	
	if (is_object($claim)) {
		$claim_number = $claim->adhoc_value;
	}
	
	$letter_name = getAdhocsInfo($kase->id, $carrier_id, "letter_name");
	if (count($letter_name) > 0) {
		$carrier->company_name = $letter_name[0]->adhoc_value;
	}
	$defense = getCorporationInfo($defense_id);
	$letter_name = getAdhocsInfo("", $defense_id, "letter_name");
	if (count($letter_name) > 0) {
		 $defense->company_name = $letter_name[0]->adhoc_value;
	}
	$referral = getCorporationInfo($referral_id);
	$law_enforcement = getCorporationInfo($law_enforcement_id);
	if ($law_enforcement_id > 0) {
		//die(print_r($law_enforcement));
	}
	$witness = getCorporationInfo($witness_id);
	$prior_attorney = getKasePartiesInfo($case_id, "prior_attorney");
	if (count($prior_attorney) > 0) {
		$prior_attorney = $prior_attorney[0];
	} else {
		$prior_attorney = array();
	}
	$primary = getCorporationInfo($primary_id);
	
	$lien_holder = getCorporationInfo($lien_holder_id);

			
	//put together the parties	
	if (count($arrLetterParties)==0) {
		if ($parties_carrier=="Y") {
			$arrLetterParties[] = $carrier;
		}
		if ($parties_employer=="Y") {
			$arrLetterParties[] = $employer;
		}
		if ($parties_uef=="Y") {
			$arrLetterParties[] = $uef;
		}
		if ($parties_defense=="Y") {
			$arrLetterParties[] = $defense;
		}
		if ($parties_primary=="Y") {
			$arrLetterParties[] = $primary;
		}
		if ($parties_lien_holder=="Y") {
			$arrLetterParties[] = $lien_holder;
		}
	}
	$arrParties = array();
	$arrPartiesBlock = array();
	$arrPartiesReturn = array();
	$arrPartiesNames = array();
	$arrPartiesFaxes = array();
	$arrPartiesType = array();
	$parties = "";	
	
	foreach($arrLetterParties as $letter_partie) {
		
		if (is_object($letter_partie)) {
			$arrPartieInfo = array();
			$arrPartieName = array();
			//defense or carrier
			if ($letter_partie->type == "carrier" || $letter_partie->type == "defense" || $letter_partie->type == "prior_attorney") {
				$letter_name = getAdhocsInfo("", $letter_partie->corporation_id, "letter_name");
				
				if (count($letter_name) > 0 && trim($letter_name[0]->adhoc_value)!="") {
					$letter_partie->company_name = $letter_name[0]->adhoc_value;
				}
				if ($letter_partie->type == "carrier") {
					$partie_claim = getAdhocsInfo($kase->id, $letter_partie->corporation_id, "claim_number");
					if (count($partie_claim) > 0 && !$blnClaimsFilled) {
						$arrClaims[] = $partie_claim[0]->adhoc_value;
					}
				}
			}
			if ($letter_partie->type == "venue") {
				//Workers' Compensation Appeals Board 
				$arrPartieInfo[] = "Workers' Compensation Appeals Board";
			}
			//put the block together
			if (trim($letter_partie->company_name)!="") {
				$arrPartieInfo[] = $letter_partie->company_name;
				$arrPartieName[] = $letter_partie->company_name;
			}
			if (trim($letter_partie->full_name)!="") {
				$arrPartieInfo[] = $letter_partie->full_name;
				$arrPartieName[] = $letter_partie->full_name;
			}
			if (trim(str_replace(",", "", $letter_partie->full_address)) == "") {
				$letter_partie->full_address = "";
			}
			if ($letter_partie->full_address!="") {
				$arrPartieInfo[] = $letter_partie->full_address;
			}
			
			$arrParties[] = implode(", ", $arrPartieInfo);	
			
			$block = "";
			if ($letter_partie->type == "venue") {
				//Workers' Compensation Appeals Board 
				$block = "Workers' Compensation Appeals Board" . "\\n";
			}
			$block .= $letter_partie->company_name . "\\n";
			if (trim($letter_partie->full_name)!="" && trim($letter_partie->full_name)!=trim($letter_partie->company_name)) {
				if ($letter_partie->salutation!="") {
					//break it up in case it contains the name
					$arrSalutation =  explode(" ", $letter_partie->salutation);
					foreach($arrSalutation as $sindex=>$thesalut) {
						if (strpos($letter_partie->full_name, $thesalut) !== false) {
							unset($arrSalutation[$sindex]);
						}
					}
					$letter_partie->salutation = trim(implode(" ", $arrSalutation));
					if ($letter_partie->salutation!="") {
						//now add it to the block
						$block .= $letter_partie->salutation . " ";
					}
				}
				$block .= $letter_partie->full_name . "\\n";
			}
			$block .= $letter_partie->street;
			if ($letter_partie->suite!="" && $letter_partie->suite!=$letter_partie->street) {
				$block .= "\\n" . $letter_partie->suite;
			}
			if ($letter_partie->city!="") {
				$block .= "\\n" . $letter_partie->city . ", " . $letter_partie->state . " " . $letter_partie->zip;
			}
			if ($blnFax){
				$block .= "\\nFax: " . $letter_partie->fax;
			} else {
				//$block .= "\\n \\n";
			}
			$block .= "\\n \\n";
			
			//$block .= "\\n";
			$arrPartiesBlock[] = $block;
			$arrPartiesType[] = $letter_partie->type;
			$arrPartiesNames[] = implode(", ", $arrPartieName);	//$letter_partie->company_name;
			$arrPartiesFaxes[] = $letter_partie->fax;
			
			$arrBlock = explode("\\n", $block);
			$new_block = array(
				implode(",", $arrBlock)
			);
			
			$arrPartiesReturn[] = $new_block;
		}
	}
	
	if ($parties_applicant=="Y") {
		$arrParties[] = $kase->first_name . " " . $kase->last_name . ", " . $kase->applicant_full_address;
		$block = $kase->first_name . " " . $kase->last_name . "\\n" . $kase->applicant_street;
		if ($kase->applicant_suite!="" && $kase->applicant_suite!=$kase->applicant_street) {
			$block .= "\\n" . $kase->applicant_suite;
		}
		$block .= "\\n" . $kase->applicant_city . ", " . $kase->applicant_state . " " . $kase->applicant_zip;
		if ($blnFax){
			$block .= "\\nFax: " . $kase->applicant_fax;
		} else {
			$block .= "\\n \\n";
			//$block .= "\\n";
		}
		$arrPartiesBlock[] = $block;
		$arrPartiesNames[] = $kase->first_name . " " . $kase->last_name;
		$arrPartiesFaxes[] = $kase->applicant_fax;
	}
	$parties = implode("\\n", $arrParties);

	//we need a specific sort order for partie blocks
	//per thomas 6/9/17
	$partie_index = 300;
	$block_index = 0;
	$arrSortedBlock = array();
	
	$arrCounters = array();
	foreach($arrPartiesType as $pindex=>$partie_type) {
		switch ($partie_type) {
			case "venue":
				$newindex = 20;
				break;
			case "defense":
				if (!isset($arrCounters["defense"])) {
					$arrCounters["defense"] = 20;
				}
				
				$newindex = $block_index + $arrCounters["defense"] + 1;
				$arrCounters["defense"]++;
				$block_index++;
				break;
			case "carrier":
				if (!isset($arrCounters["carrier"])) {
					$arrCounters["carrier"] = 30;
				}
				$newindex = $block_index + $arrCounters["carrier"] + 2;
				$arrCounters["carrier"]++;
				$block_index++;
				break;
			default:
				$newindex = $partie_index;
				$partie_index++;
		}
		$arrSortedBlock[$newindex] = $arrPartiesBlock[$pindex];
	}
	ksort($arrSortedBlock);
	
	$arrPartiesBlock = $arrSortedBlock;
	
	$parties_block = implode("", $arrPartiesBlock);
	$parties_names = implode("\\n", $arrPartiesNames);
	$parties_faxes = implode("\\n", $arrPartiesFaxes);
	$arrInjuries = array();
	$injury_uuid = "";
	if ($doi_id!="") {
		$injury = getInjuryInfo($doi_id);
		if ($_SERVER['REMOTE_ADDR'] == "47.153.49.248") {
			//die($doi_id . " - injury");
			//die(print_r($injury));
		}
		$injury_uuid = $injury->uuid;
		$adj_number = $injury->adj_number;
		$injury_full_address = $injury->full_address;
		$injury_occupation = $injury->occupation;
		$injury_occupation = str_replace("&", "&amp;", $injury_occupation);
		$injury_number = $injury->injury_number;
		
		$injury_start_date = $injury->start_date;
		$injury_end_date = $injury->end_date;
	}
	
	$injuries = getInjuriesInfo($case_id);
	
	$arrInjuries = array();
	foreach($injuries as $list_injury) {
		if ($list_injury->end_date!="0000-00-00") {
			$list_injury->start_date = date("m/d/Y", strtotime($list_injury->start_date)) . " - " . date("m/d/Y", strtotime($list_injury->end_date)) . " CT";
		} else {
			$list_injury->start_date = date("m/d/Y", strtotime($list_injury->start_date));
		}
		$arrInjuries[] = $list_injury->start_date;
	}
	//die(print_r($arrInjuries));
	$body_parts = getBodypartsInfo($case_id, $doi_id);
	$arrBodyParts = array();
	foreach($body_parts as $index=>$body_part) {
		$arrBodyParts[] = $body_part->code . " - " . $body_part->description;
	}
	$body_parts = implode(" / ", $arrBodyParts);
	
	if (count($arrClaims) == 0 && !$blnClaimsFilled) {
		if (is_array($claim)) {
			if (count($claim) > 0) {
				if (is_object($claim[0])) {
					//make sure that the injuries match
					if ($claim[0]->injury_uuid=="" || $injury_uuid=="") {
						$claim_number = $claim[0]->adhoc_value;
					} else {
						if ($claim[0]->injury_uuid==$injury_uuid) {
							$claim_number = $claim[0]->adhoc_value;
						}
					}
				} else {
					//die(print_r($claim));
					//die("no object\r\n");
				}
			}
		}
	}
	
	if (count($arrClaims) > 0) {
		$arrClaims = array_unique($arrClaims);
		$claim_number = implode("; ", $arrClaims);
	}
	
	$sql_letterhead = "SELECT `setting_value` `value`
	FROM  `cse_setting` 
	WHERE `cse_setting`.customer_id = " . $_SESSION['user_customer_id'] . "
	AND `cse_setting`.setting = 'letterhead'
	AND `cse_setting`.deleted = 'N'
	AND `cse_setting`.`setting_value` != ''
	ORDER BY setting_id DESC
	LIMIT 0, 1";
	try {
		$stmt = DB::run($sql_letterhead);
		$letterhead = $stmt->fetchObject();
		
		if ($template->source!="no_letterhead" && $template->source!="clientname_letterhead") {
			if(!is_object($letterhead)) {
				die(json_encode(array("error"=>"no letterhead")));
			}
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
	
	$sql_lettersignature = "SELECT `setting_value` `value`
	FROM  `cse_setting` 
	WHERE `cse_setting`.customer_id = " . $_SESSION['user_customer_id'] . "
	AND `cse_setting`.setting = 'lettersignature'
	AND `cse_setting`.deleted = 'N'";
	
	$signature_img = "";
	try {
		$stmt = DB::run($sql_lettersignature);
		$lettersignature = $stmt->fetchObject();
		
		if (is_object($lettersignature)) {
			$signature_img = $lettersignature->value;
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}

	if (strpos($template->document_filename, "templates/") === false) {
		$prefix = "/templates";
	}
	
	$destination = $template->document_filename;
	$destination = str_replace("templates/", "", $destination);
	$destination = str_replace(".docx", "", $destination);
	$destination .= "_" . $case_id;

	$destination_folder = '../uploads/' . $customer_id . '/' . $case_id . '/letters/';
	if (!is_dir($destination_folder)) {
		mkdir($destination_folder, 0755, true);
	}
	if ($kinv_data!="") {
		//invoices go somewhere else
		$destination_folder = '../uploads/' . $customer_id . '/invoices/';
		$destination_folder_path = UPLOADS_PATH. $customer_id . '\\invoices\\';
		if (!is_dir($destination_folder)) {
			mkdir($destination_folder, 0755, true);
		}
	}
	$destination = $destination_folder . $destination;
	
	$final_destination = $destination;
	$intCounter = 0;
	if ($any_partie_id != "") {
		$perspos = strpos($any_partie_id, "P");
		$corppos = strpos($any_partie_id, "C");
		
		if ($perspos===false && $corppos===false) {
			//continue;
		} else {
			$newCounter = str_replace("P", "", $any_partie_id);
			$newCounter = str_replace("C", "", $newCounter);
			if (is_numeric($newCounter)) {
				$intCounter = $newCounter;
			}
		}
	}
	$blnFirst = true;
	while (file_exists($final_destination . "_" . $intCounter . ".docx")) {
		$intCounter++;
		//echo $final_destination . "_" . $intCounter . ".docx\r\n";
		//$final_destination = $destination . "_" . $intCounter;
		//$blnFirst = false;
	}
	//if ($blnFirst) {
		$final_destination = $destination . "_" . $intCounter;
	//}
	
	//die($final_destination);
	$destination = $final_destination;
	if ($kinv_data!="") {
		$destination_path = $destination_folder_path . $destination;
		$destination_path = str_replace("../uploads/" . $customer_id . "/invoices/", "", $destination_path);
	}
	$arrReplace = array();
	
	//is this a matrix order as well?
	$sql = "SELECT order_id, order_info
	FROM cse_case_matrixorder
	WHERE case_id = :case_id";
	try {
		$db = getConnection();
		
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$matrix_order = $stmt->fetchObject();
		
		if (is_object($matrix_order)) {
			$matrix_order_info = json_decode($matrix_order->order_info);
			$sum_balance_due = str_replace(",", "", $matrix_order_info->sum_balance_due);
			$penalties = $sum_balance_due * .1;
			$daily_interests = $sum_balance_due * .07 / 360;
			
			$max_service_date = "";
			$days_service_date = "";
			if (isset($matrix_order_info->max_service_date)) {
				$max_service_date = $matrix_order_info->max_service_date;
				$days_service_date = str_replace(",", "", $matrix_order_info->days_service_date);
			}
			$arrReplace['MAX_SERVICE_DATE'] = $max_service_date;
			$arrReplace['DAYS_SERVICE_DATE'] = number_format($days_service_date, 0);
			
			if ($customer_id==1105) {
				$arrReplace['INVOICE_COUNT'] = convertNumberToWord($matrix_order_info->invoice_count) . " (" . $matrix_order_info->invoice_count . ")";
			} else {
				$arrReplace['INVOICE_COUNT'] = $matrix_order_info->invoice_count;
			}
			$arrReplace['INVOICE_BALANCE'] = $matrix_order_info->sum_balance_due;
			$arrReplace['FIRST_INVOICE'] = $matrix_order_info->min_invoice_date;
			$arrReplace['LAST_INVOICE'] = $matrix_order_info->max_invoice_date;
			$arrReplace['INVOICE_SIXTY'] = $matrix_order_info->sixty_invoice_date;

			$arrReplace['INVOICE_INTEREST'] = number_format($daily_interests, 2);
			$arrReplace['INVOICE_PENALTIES'] = number_format($penalties, 2);
			$arrReplace['INVOICE_DI_DAYS_CALC'] =  number_format(number_format($daily_interests, 2) * $days_service_date, 2);
				
			//typed in
			$post_first_date = "";
			if (isset($_POST["post_first_date"])) {
				$post_first_date = passed_var("post_first_date", "post");
			}
			$post_second_date = "";
			if (isset($_POST["post_second_date"])) {
				$post_second_date = passed_var("post_second_date", "post");
			}
			$total_pos = "";
			if (isset($_POST["total_pos"])) {
				$total_pos = passed_var("total_pos", "post");
			}
			
			$arrReplace['POS_FIRST_DATE'] = $post_first_date;
			$arrReplace['POS_SECOND_DATE'] = $post_second_date;
			$arrReplace['POSCOUNT'] = convertNumberToWord($total_pos) . " (" . $total_pos . ")";
			//die(print_r($arrReplace));
			/*
			info.penalties = (Number(info.sum_balance_due.replace(",", "")) * .1).toFixed(2);
			info.daily_interest = (Number(info.sum_balance_due.replace(",", "")) * .07 / 360).toFixed(2);
			*/
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
	
	$arrReplace['CASENAME'] = trim(str_replace("&", "&amp;", $kase->name));
	$arrReplace['CASENUMBER'] = strtoupper($adj_number);
	$arrReplace['CASEJUDGE'] = $judge;
	//the letter recipient could be any partie
	$any_subject = "";
	$blnProcessParty = true;
	if ($any_partie_id != "") {
		
		$perspos = strpos($any_partie_id, "P");
		$corppos = strpos($any_partie_id, "C");
		
		if ($perspos===false && $corppos===false) {
			//continue;
			$blnProcessParty - false;
		}
	}
	if ($blnProcessParty) {
		
		$any_partie = (object) '';
		
		if ($perspos!==false) {
			
	
			$any_partie_id = str_replace("P", "", $any_partie_id);
			if ($any_partie_id=="" && $kase->applicant_id!="") {
			//	$any_partie_id = $kase->applicant_id;
			}
			$any_partie = getPersonInfo($any_partie_id);
			$any_subject = str_replace("_", " ", $any_partie->type) . " - " . $any_partie->full_name;
		}
		if ($corppos!==false) {
			$any_partie_id = str_replace("C", "", $any_partie_id);
			$any_partie = getCorporationInfo($any_partie_id);
			
			$any_subject = $any_partie->type . " - " . $any_partie->company_name;
		}
		//die(print_r($any_partie));
		//any specific destination
		$any_partie_salutation = "Sir/Madam";
		if (isset($any_partie->full_name) && trim($any_partie->full_name)!="") {
            $any_partie_salutation = $any_partie->full_name;
            if ($any_partie->salutation!="") {
                $any_partie_salutation = 	$any_partie->salutation . " " . $any_partie_salutation;
            }
        }
		$arrReplace['ANYSALUT1'] = $any_partie_salutation;
		
		$arrReplace['ANYNAME1'] = $any_partie->full_name;
		$arrReplace['ANYFIRM1'] = $any_partie->company_name;
		
		setCityStreet($any_partie);
		$arrReplace['ANYADD11'] = $any_partie->street;
		$arrReplace['ANYADD12'] = $any_partie->suite;
		$arrReplace['ANYADD21'] = $any_partie->suite;
		$arrReplace['ANYFAX1'] = $any_partie->fax;
		$arrReplace['ANYPHONE1'] = $any_partie->phone;
		$arrReplace['ANYCITYSTATEZIP1'] = $any_partie->city . ", " . $any_partie->state . " " . $any_partie->zip;		
	}
		
	$attorney_full_name = $kase->attorney_full_name;
	$attorney_email = $kase->attorney_email;
	if ($kase->attorney_full_name=="" && $kase->attorney != "") {
		if (!is_numeric($kase->attorney)) {
			$the_attorney = getUserByNickname($kase->attorney);
			$attorney_full_name = $the_attorney->user_name;
			$attorney_email = $the_attorney->user_email;
			
			if ($kase->attorney_name == "" && $the_attorney->nickname !="") {
				$kase->attorney_name = $the_attorney->nickname;
			}
		}
	}
	$arrReplace['ATTORNEY'] = $attorney_full_name;
	$arrReplace['ATTORNEYEMAIL'] = $attorney_email;
	
	if (isset($customer_eams)) {
		$arrReplace['EAMSNAME'] = $customer_eams->firm_name;
		$arrReplace['EAMSSTREET1'] = $customer_eams->street_1;
		$arrReplace['EAMSSTREET2'] = $customer_eams->street_2;
		$arrReplace['EAMSCITY'] = $customer_eams->city;
		$arrReplace['EAMSSTATE'] = $customer_eams->state;
		$arrReplace['EAMSZIP'] = $customer_eams->zip_code;
		$arrReplace['EAMSPHONE'] = $customer_eams->phone;
	}
	$customer_full_name = $customer->cus_name_first;
	if ($customer->cus_name_middle!="") {
		$customer_full_name .= " " . $customer->cus_name_middle;
	}
	
	$sql_personal_injury = "SELECT pi.*
	FROM  `cse_personal_injury` `pi`
	WHERE pi.customer_id = " . $_SESSION['user_customer_id'] . "
	AND pi.case_id = '" . $case_id . "'
	AND pi.deleted = 'N'
	ORDER BY pi.personal_injury_id ASC
	LIMIT 0, 1";
	//die($sql_personal_injury);
	try {
		$stmt = DB::run($sql_personal_injury);
		$personal_injury = $stmt->fetchObject();
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}

	if(is_object($personal_injury)) {
		//accident info
		//if ($_SERVER['REMOTE_ADDR']=='47.153.56.2') {
			//print_r($personal_injury);
			//die();
			$accident_data = json_decode($personal_injury->personal_injury_info);
			//die(print_r($accident_info));
			$arrInjuryAddress = array();
			foreach ($accident_data as $accident_info) {
				if ($accident_info->name == "personal_injury_locationInput") {
					$arrInjuryAddress[] = $accident_info->value;
				}
				if ($accident_info->name == "personal_injury2_locationInput") {
					$arrInjuryAddress[] = $accident_info->value;
				}
			}
			if (count($arrInjuryAddress) > 0) {
				$injury_full_address = implode("; ", $arrInjuryAddress);
			}
		//}
		
		if ($personal_injury->personal_injury_details!="") {
			//license plate numbers
			$details = json_decode($personal_injury->personal_injury_details);
			
			foreach($details as $detail) {
				if ($detail->form=="vehicle_form") {
					$arrVehic = $detail->data;				
					foreach ($arrVehic as $vehic) {
						if ($vehic->name == "license_plateInput") {
							$arrReplace['CLIENTLICPLATE'] = $vehic->value;
						}
					}
				}
				if ($detail->form=="defendant_vehicle_form") {
					$arrVehic = $detail->data;				
					foreach ($arrVehic as $vehic) {
						if ($vehic->name == "defendant_license_plateInput") {
							$arrReplace['DFNTLICPLATE'] = $vehic->value;
						}
					}
				}
				
				if ($detail->form=="personal_injury_info_form") {
					$arrAccidentInfo = $detail->data;	
					//die(print_r($arrAccidentInfo));			
					foreach ($arrAccidentInfo as $acci_info) {
						if ($acci_info->name == "report_numberInput") {
							$arrReplace['POLICEREPORTNO1'] = $acci_info->value;
						}
					}
				}
			}
		}
	}
	//bodyparts
	foreach($arrBodyParts as $index=>$body_part) {
		$arrReplace['BODYPARTS' . $index] = $body_part;
	}
	
	//clean up
	for($int = 0; $int < 10; $int++) {
		if (!isset($arrReplace['BODYPARTS' . $int])) {
			$arrReplace['BODYPARTS' . $int] = "";
		}
	}
	$customer_full_name .= " " . $customer->cus_name_last;
	$arrReplace['FIRMATTY'] = $customer_full_name;
	$arrReplace['FIRMNAME'] = str_replace("&", "&amp;", $_SESSION['user_customer_name']);
	$arrReplace['FIRMNUMBER'] = $customer->eams_no;
	$arrReplace['UAN'] = $customer->cus_uan;
	$arrReplace['TAXID'] = $customer->cus_fedtax_id;
	
	$arrReplace['FIRMADD1'] = $customer->cus_street;
	$arrReplace['FIRMADD2'] = "";
	$arrReplace['FIRMATTYFNAME'] = $customer->cus_name_first;
	$arrReplace['FIRMATTYLNAME'] = $customer->cus_name_last;
	$arrReplace['FIRMATTYMIDDLEINITIAL'] = $customer->cus_name_middle;
	$arrReplace['FIRMCITY'] = $customer->cus_city;
	$arrReplace['FIRMSTATE'] = $customer->cus_state;
	$arrReplace['FIRMZIP'] = $customer->cus_zip;
	$arrReplace['FIRMTEL'] = $customer->cus_phone;
	$arrReplace['FIRMEMAIL'] = $customer->cus_email;
	$arrReplace['BARNUMBER'] = $customer->cus_barnumber;
	$arrReplace['BARNO'] = $customer->cus_barnumber;
	$arrReplace['FIRMFAX'] = $customer->cus_fax; 
	$arrReplace['ADDTELFIRMNAME'] = $customer->cus_street . ", " . $customer->cus_city . ", " . $customer->cus_state . " " . $customer->cus_zip . ", " . $customer->cus_phone;
	$arrReplace['CUSCOUNTY'] = $customer->cus_county;
	
	$worker_full_name = $kase->worker_full_name;
	$worker_email = $kase->worker_email;
	$worker_job = $kase->worker_job;
	
	
	//if ($kase->worker_full_name=="" && $kase->worker_name != "") {
		if (!is_numeric($kase->worker_name)) {
			$the_worker = getUserByNickname($kase->worker_name);
			$worker_full_name = $the_worker->user_name;
			$worker_email = $the_worker->user_email;
			$worker_job = $the_worker->job;
		}
		if ($kase->worker_name=="" && $kase->worker!="") {
			if (!is_numeric($kase->worker)) {
				$the_worker = getUserByNickname($kase->worker);
			} else {
				$the_worker = getUserInfo($kase->worker);
			}
			$worker_full_name = $the_worker->user_name;
			$worker_email = $the_worker->user_email;
			$worker_job = $the_worker->job;
			
			$kase->worker_name = $kase->worker;
		}
	//}
	
	$arrReplace['ASSIGNEDUSER'] = $worker_full_name;
	
	
	//changing from kase worker to current user per thomas 3/22/2017
	/*
	$arrReplace['WORKERINITIALS'] = strtolower($kase->worker_name);
	$arrReplace['WORKEREMAIL'] = strtolower($worker_email);
	$arrReplace['WORKERUPPERINITIALS'] = $kase->worker_name;
	$arrReplace['WORKERJOB'] = $worker_job;
	*/
	$current_worker = getUserInfo($_SESSION["user_plain_id"]);
	/*
	$arrCurrentWorkerUsername = explode(" ", $current_worker->user_name);
	$initials = "";
	foreach($arrCurrentWorkerUsername as $current_name) {
		$first_letter = strtoupper(substr($current_name, 0, 1));
		$initials .= $first_letter;
	}
	*/
	$arrReplace['ASSIGNEDWORKER'] = capWords(strtolower($current_worker->user_name));
	$arrReplace['WORKERINITIALS'] = strtolower($current_worker->nickname);
	$arrReplace['WORKEREMAIL'] = strtolower($current_worker->user_email);
	$arrReplace['WORKERUPPERINITIALS'] = strtoupper($current_worker->nickname);
	$arrReplace['WORKERJOB'] = $current_worker->job;
	
	
	$attorney_full_name = str_replace(", Esq.", "", $attorney_full_name);
	$attorney_full_name = str_replace(", ESQ.", "", $attorney_full_name);
	
	$arrReplace['ASSIGNEDATTORNEY'] = $attorney_full_name . ", Esq.";
	$arrReplace['ASSIGNEDATTORNEYNAMEONLY'] = $attorney_full_name;
	
	$arrReplace['ASSIGNEDATTORNEYWords'] = capWords($attorney_full_name) . ", Esq.";
	$arrReplace['ASSIGNEDATTORNEYNAMEONLYWords'] = capWords($attorney_full_name);
	
	$deposition_location = str_replace("\r\n", chr(13), $deposition_location);
	$deposition_location = str_replace("\n", chr(13), $deposition_location);
	$arrDepoLoc = explode(chr(13), $deposition_location);
	$deposition_location = implode("\\n", $arrDepoLoc);
	
	$arrReplace['ATTORNEYINITIALS'] = $kase->attorney_name;
	$arrReplace['DEPOSITIONDATE'] = date("m/d/Y", strtotime($last_date));
	$arrReplace['DEPOSITIONTIME'] = date("h:iA", strtotime($last_date));
	$arrReplace['DEPOSITIONLOC'] = str_replace("\r\n", "\\n", $deposition_location);
	$arrReplace['DEPOSITIONADDRESS'] = str_replace("\r\n", "\\n", $deposition_address);
	$arrReplace['DEPOSITIONTOFFICE'] = $deposition_office;
	$arrReplace['DEPOSITIONTNAME'] = $deposition_party;
	$arrReplace['DEPOSITIONCOUNTY'] = $deposition_county;
	$arrReplace['DEPOSITIONFEE'] = $deposition_fee;
	$arrReplace['COURTORDERDATE'] = $deposition_court_order_date;
	
	$arrReplace['DATE'] = date('F j, Y');
	//if spanish
	if (strpos(strtolower($template_name), "spanish")!==false) {
		$month = getSpanishMonth(date('F'));
		$arrReplace['DATE'] = $month . " " . date('j, Y');
	}
	$arrReplace['LASTDATE'] = $last_date;
	$arrReplace['DUEDATE'] = $last_date;
	
	$arrReplace['APPTDATE'] = date("m/d/Y", strtotime($last_date));
	$arrReplace['APPTTIME'] = date("h:iA", strtotime($last_date));
	
	$arrReplace['PAGES'] = $pages;
	$arrReplace['RUSH'] = $rush;
	$arrReplace['LETTERMONTH'] = date("F");
	$arrReplace['LETTERDAY'] = date("j");
	$arrReplace['LETTERYEAR'] = date("Y");
	$ssn = "";
	if (strlen($kase->ssn)==9) {
		$ssn = substr($kase->ssn, 0, 3) . "-" . substr($kase->ssn, 3, 2) . "-" . substr($kase->ssn, 5, 4);
	}
	
	$arrReplace['CLIENTSSNO'] = $ssn;
	$dob = $kase->dob;
	if (isValidDate($dob, 'Y-m-d')) {
		$dob = date("m/d/Y", strtotime($dob));
	} else {
		$dob = "";
	}
	$arrReplace['CLIENTDOB'] = $dob;
	
	if ($applicant_email=="" && $kase->applicant_id > 0) {
		$person = getPersonInfo($kase->applicant_id);
		$applicant_email = $person->email;
		$applicant_cell = $person->cell_phone;
	}
	$arrReplace['CLIENTEMAIL'] = $applicant_email;
	$arrReplace['CLIENTCELL'] = $applicant_cell;
	if ($kase->gender=="M") {
		$hisher = "his";
		$hishers = "his";
		$heshe = "he";
	}
	if ($kase->gender=="F") {
		$hisher = "her";
		$hishers = "hers";
		$heshe = "she";
	}
	$arrReplace['HISHER'] = $hisher;
	$arrReplace['HESHE'] = $heshe;
	$doi = "";
	if(count($arrDOIDates) == 0) {
		if ($injury_start_date!="0000-00-00" && $injury_start_date!="") {
			$doi = date("m/d/Y", strtotime($injury_start_date));
			if ($injury_end_date!="0000-00-00" && $injury_end_date!="") {
				$doi = "CT " . $doi . " - " . date("m/d/Y", strtotime($injury_end_date));
			}
		}
	} else {
		$arrDOICTDates = array();
		
		foreach($arrDOIDates as $doi_date) {
			$the_doi = date("m/d/Y", strtotime($doi_date["start_date"]));
			if ($doi_date["end_date"]!="0000-00-00" && $doi_date["end_date"]!="") {
				$the_doi = "CT " . $the_doi . " - " . date("m/d/Y", strtotime($doi_date["end_date"])) ;
			}
			$arrDOICTDates[] = $the_doi;
		}
		$doi = implode("; ", $arrDOICTDates);
	}
	$arrReplace['ACCIDATE'] = $doi;
	//this needs to change to accident details when accident screen is ready.
	$arrReplace['ACCIDENTLOCATION'] = $injury_full_address;
	$arrReplace['INJURYFULLADDRESS'] = $injury_full_address;
	
	//die(print_r($arrReplace));
	$arrReplace['ALLINJURYDATES'] = implode("\r\n", $arrInjuries);
	$arrReplace['ALLINJURYDATESINLINE'] = implode(", ", $arrInjuries);
	$arrReplace['INJURIES'] = $body_parts;
	
	//clean up in case
	if ($kase->last_name == "" && $kase->full_name != "") {
		$arrFullName = explode(" ", trim($kase->full_name));
		if (count($arrFullName) > 1) {
			$kase->last_name = $arrFullName[count($arrFullName) - 1];
		}
	}
	if ($kase->full_name=="") {
		$full_name = $kase->first_name;
		if ($kase->middle_name!="") {
			$full_name .= " " . $kase->middle_name; 
		}
		$full_name .= " " . $kase->last_name;
	} else {
		$full_name = $kase->full_name;
	}
	$full_name = capWords(strtolower($full_name));
	$client_salutation = $full_name;
	if ($kase->applicant_salutation!="") {
		$applicant_salutation = $kase->applicant_salutation;
		$applicant_salutation = str_replace($kase->first_name, "", $applicant_salutation);
		$applicant_salutation = str_replace($kase->last_name, "", $applicant_salutation);
		$applicant_salutation = strtolower(trim($applicant_salutation));
		$client_salutation = capWords($applicant_salutation) . " " . $client_salutation;
	}
	
	if (!isset($kase_plaintiff)) {
		$arrReplace['CLIENTSALUT'] = $client_salutation;
		$arrReplace['CLIENTNAME'] = $full_name;
		$arrReplace['CLIENTFIRSTNAME'] = $kase->first_name;
		$arrReplace['CLIENTFNAME'] = $kase->first_name;
		$arrReplace['CLIENTLASTNAME'] = $kase->last_name;
		$arrReplace['CLIENTLNAME'] = $kase->last_name;
		$arrReplace['CLIENTMIDDLENAME'] = $kase->middle_name;
		$arrReplace['CLIENTMIDINITIAL'] = $kase->middle_name;
		$arrReplace['CLIENTADD1'] = $kase->applicant_street;
		$arrReplace['CLIENTADD2'] = $kase->applicant_suite;
		$arrReplace['CLIENTCITYSTATEZIP'] = $kase->applicant_city . ", " . $kase->applicant_state . " " . $kase->applicant_zip;
		$arrReplace['CLIENTCITYSTATEZIP'] = $kase->applicant_city . ", " . $kase->applicant_state . " " . $kase->applicant_zip;
		$arrReplace['CLIENTCITYSTATEZIP'] = $kase->applicant_city . ", " . $kase->applicant_state . " " . $kase->applicant_zip;
		$arrReplace['CLIENTCITY'] = $kase->applicant_city;
		$arrReplace['CLIENTSTATE'] = $kase->applicant_state;
		$arrReplace['CLIENTZIP'] = $kase->applicant_zip;
	} else {
		$arrReplace['CLIENTSALUT'] = $kase_plaintiff_salutation;
		$arrReplace['CLIENTNAME'] = $kase_plaintiff->full_name;
		$arrReplace['CLIENTFIRSTNAME'] = $kase_plaintiff->first_name;
		$arrReplace['CLIENTFNAME'] = $kase_plaintiff->first_name;
		$arrReplace['CLIENTLASTNAME'] = $kase_plaintiff->last_name;
		$arrReplace['CLIENTLNAME'] = $kase_plaintiff->last_name;
		$arrReplace['CLIENTMIDDLENAME'] = $kase_plaintiff->middle_name;
		$arrReplace['CLIENTMIDINITIAL'] = $kase_plaintiff->middle_name;
		$arrReplace['CLIENTADD1'] = $kase_plaintiff->applicant_street;
		$arrReplace['CLIENTADD2'] = $kase_plaintiff->applicant_suite;
		$arrReplace['CLIENTCITYSTATEZIP'] = $kase_plaintiff->applicant_city . ", " . $kase_plaintiff->applicant_state . " " . $kase_plaintiff->applicant_zip;
		$arrReplace['CLIENTCITYSTATEZIP'] = $kase_plaintiff->applicant_city . ", " . $kase_plaintiff->applicant_state . " " . $kase_plaintiff->applicant_zip;
		$arrReplace['CLIENTCITYSTATEZIP'] = $kase_plaintiff->applicant_city . ", " . $kase_plaintiff->applicant_state . " " . $kase_plaintiff->applicant_zip;
		$arrReplace['CLIENTCITY'] = $kase_plaintiff->applicant_city;
		$arrReplace['CLIENTSTATE'] = $kase_plaintiff->applicant_state;
		$arrReplace['CLIENTZIP'] = $kase_plaintiff->applicant_zip;
	}
	
	
	$arrReplace['CODEFSALUT'] = "";
	$arrReplace['CODEFNAME'] = "";
	$arrReplace['CODEFFIRSTNAME'] = "";
	$arrReplace['CODEFFNAME'] = "";
	$arrReplace['CODEFLASTNAME'] = "";
	$arrReplace['CODEFLNAME'] = "";
	$arrReplace['CODEFMIDDLENAME'] = "";
	$arrReplace['CODEFMIDINITIAL'] = "";
	$arrReplace['CODEFADD1'] = "";
	$arrReplace['CODEFADD2'] = "";
	$arrReplace['CODEFCITYSTATEZIP'] = "";
	$arrReplace['CODEFCITYSTATEZIP'] = "";
	$arrReplace['CODEFCITYSTATEZIP'] = "";
	$arrReplace['CODEFCITY'] = "";
	$arrReplace['CODEFSTATE'] = "";
	$arrReplace['CODEFZIP'] = "";
	
	if ($kase->applicant_suite=="") {
		$applicant_full_address = $kase->applicant_street . ", " . $kase->applicant_city . ", " . $kase->applicant_state . " " . $kase->applicant_zip;
		$applicant_from_address = $kase->applicant_street . "\\n" . $kase->applicant_city . ", " . $kase->applicant_state . " " . $kase->applicant_zip;
	} else {
		$applicant_full_address = $kase->applicant_street . ", " . $kase->applicant_suite . ", " . $kase->applicant_city . ", " . $kase->applicant_state . " " . $kase->applicant_zip;
		$applicant_from_address = $kase->applicant_street . "\\n" . $kase->applicant_suite . "\\n" . $kase->applicant_city . ", " . $kase->applicant_state . " " . $kase->applicant_zip;
	}
	$arrReplace['FULLCLIENTCITYSTATEZIP'] = $applicant_full_address;
	$arrReplace['SENDTO'] = $kase->first_name . " " . $kase->last_name . "\\n" . $applicant_from_address;
	
	$arrReplace['APPLICANTNAME'] = capWords(strtolower($full_name));
	$arrReplace['APPLICANTAKA'] = capWords(strtolower($kase->aka));
	$arrReplace['CLIENTAKA'] = capWords(strtolower($kase->aka));
	$arrReplace['CLIENTSEX'] = $kase->gender;
	$arrReplace['CLIENTTEL'] = $kase->applicant_phone;
	$arrReplace['CLIENTCELLS'] = $kase->applicant_cell;
	$arrReplace['CLIENTOCCUPA'] = $injury_occupation;
	$arrReplace['CLIENTOCCUP'] = $injury_occupation;
	
	$applicant_age = $kase->applicant_age;
	if ($applicant_age == 0) {
		$applicant_age = "";
	}
	$arrReplace['CLIENTAGE'] = $kase->applicant_age;
	$arrReplace['CLIENTPAGER'] = "";
	
	if (isset($arrADJs)) {
		$arrReplace['ALLCASENUMBER'] = implode("; ", $arrADJs);
		$arrReplace['CASENUMBER'] = implode("; ", $arrADJs);
	} else {
		$arrReplace['ALLCASENUMBER'] = strtoupper($adj_number);
	}
	//die(print_r($arrReplace));
	
	//all the claim numbers
	if (!$blnClaimsFilled) {
		/*
		$sql = "SELECT inj.adj_number, inj.start_date, inj.end_date, 
			cin.alternate_policy_number claim_number 
			FROM cse_injury inj
            INNER JOIN cse_injury_injury_number ccin
            ON inj.injury_uuid = ccin.injury_uuid
            INNER JOIN cse_injury_number cin
            ON ccin.injury_number_uuid = cin.injury_number_uuid 
			WHERE inj.injury_id IN (" . implode(", ", $arrDOIs) . ")
			ORDER BY inj.start_date";
		*/
		$sql_claims = "SELECT ci.injury_uuid, alternate_policy_number claim_number
		FROM cse_case ccase
		INNER JOIN cse_case_injury cci
		ON ccase.case_uuid = cci.case_uuid AND cci.deleted = 'N'
		INNER JOIN cse_injury ci
		ON cci.injury_uuid = ci.injury_uuid AND ci.deleted = 'N'
		INNER JOIN cse_injury_injury_number ccin
		ON ci.injury_uuid = ccin.injury_uuid AND ccin.deleted = 'N'
		INNER JOIN cse_injury_number cin
		ON ccin.injury_number_uuid = cin.injury_number_uuid AND cin.deleted = 'N'
		WHERE 1
		AND ccase.case_id = " . $case_id . "
		AND ccase.customer_id = '" . $_SESSION["user_customer_id"] . "'";
		if ($doi_id!="") {
			$sql_claims .= "
			AND ci.injury_id = '" . $doi_id . "'";
		}
		$sql_claims .= "
		UNION
		SELECT DISTINCT ci.injury_uuid, adhoc_value claim_number
		FROM cse_corporation_adhoc cadhoc
		INNER JOIN cse_corporation corp
		ON cadhoc.corporation_uuid = corp.corporation_uuid AND corp.deleted = 'N'
		INNER JOIN cse_case_corporation ccorp
		ON corp.corporation_uuid = ccorp.corporation_uuid AND ccorp.deleted = 'N'";
		if ($injury_uuid!="") {
			$sql_claims .= "
			 AND (ccorp.injury_uuid = '" . $injury_uuid . "' OR ccorp.injury_uuid = '')";
		}
		
		$sql_claims .= "
		INNER JOIN cse_case ccase
		ON ccorp.case_uuid = ccase.case_uuid AND ccase.deleted = 'N'
		INNER JOIN cse_case_injury cci
		ON ccase.case_uuid = cci.case_uuid AND cci.deleted = 'N'
		INNER JOIN cse_injury ci
		ON cci.injury_uuid = ci.injury_uuid
		WHERE cadhoc.adhoc = 'claim_number'
		AND cadhoc.deleted = 'N'
		AND ccase.case_id = '" . $case_id . "'
		AND ccase.customer_id = '" . $_SESSION["user_customer_id"] . "'";
		if ($doi_id!="") {
			$sql_claims .= "
			AND ci.injury_id = '" . $doi_id . "'";
		}
		//die($sql_claims);
		try {
			$claim_numbers = DB::select($sql_claims);
			$arrClaimNumbers = array();
			foreach($claim_numbers as $the_claim) {
				if ($the_claim->claim_number!="") {
					if (!in_array($the_claim->claim_number, $arrClaimNumbers)) {
						$arrClaimNumbers[] = $the_claim->claim_number;
					}
				}
			}
			array_unique($arrClaimNumbers);
			$all_claims = implode("; ", $arrClaimNumbers);
			$all_claims = trim($all_claims);
			if (substr($all_claims, 0, 1)==";") {
				$all_claims = substr($all_claims, 1);
				$all_claims = trim($all_claims);
			}
			$arrReplace['ALLCLAIMNO'] = $all_claims;
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
		}
	} else {
		$arrReplace['ALLCLAIMNO'] = $claim_number;
	}

	$arrReplace['SENDCLAIMNUMBER'] = $claim_number;
	if ($kase->case_number=="" && $kase->file_number!="") {
		$kase->case_number = $kase->file_number;
	}
	$arrReplace['FILENO'] = $kase->case_number . "-" . $injury_number;
	
	
	//lien holder
	$lien_holder_salutation = "Sir/Madam";
	if ($lien_holder) {
		if (trim($lien_holder->full_name)!="") {
			$lien_holder_salutation = $lien_holder->full_name;
			if ($lien_holder->salutation!="") {
				$lien_holder_salutation = 	$lien_holder->salutation . " " . $lien_holder_salutation;
			}
		}
	}
	setCityStreet($lien_holder);
	$arrReplace['LIENSALUT1'] = $lien_holder_salutation;
	$arrReplace['LIENNAME1'] = $lien_holder->full_name;
	$arrReplace['LIENFIRM1'] = $lien_holder->company_name;
	$arrReplace['LIENADD11'] = $lien_holder->street;
	$arrReplace['LIENADD12'] = $lien_holder->suite;
	$arrReplace['LIENADD21'] = $lien_holder->suite;
	$arrReplace['LIENCITYSTATEZIP1'] = $lien_holder->city . ", " . $lien_holder->state . " " . $lien_holder->zip;
	$arrReplace['LIENCITY'] = $lien_holder->city;
	$arrReplace['LIENSTATE'] =  $lien_holder->state;
	$arrReplace['LIENZIP'] = $lien_holder->zip;

	//defense attorney
	$defense_salutation = "Sir/Madam";
	if ($defense) {
		if (trim($defense->full_name)!="") {
			$defense_salutation = $defense->full_name;
			if ($_SESSION["user_customer_id"]==1057) {
				$defense_salutation = $defense->last_name;
				$defense_salutation = str_replace("Esq.", "", $defense_salutation);
				$defense_salutation = str_replace("Esq", "", $defense_salutation);
				$defense_salutation = str_replace("ESQ.", "", $defense_salutation);
				$defense_salutation = str_replace("ESQ", "", $defense_salutation);
				$defense_salutation = str_replace(",", "", $defense_salutation);
				$defense_salutation = trim($defense_salutation);
			}
			if ($defense->salutation!="") {
				$defense_salutation = 	$defense->salutation . " " . $defense_salutation;
			}
		}
		if ($_SESSION["user_customer_id"]!=1057) {
			$defense_salutation .= ", ESQ";
		}
	}
	$blnDefense = true;
	$blnCarrier = false;
	
	$carrier_salutation = "Sir/Madam";
	if ($carrier) {
		if (trim($carrier->full_name)!="") {
			$carrier_salutation = $carrier->full_name;
			if ($carrier->salutation!="") {
				$carrier_salutation = 	$carrier->salutation . " " . $carrier_salutation;
			}
		}
	}
	
	if ($kinv_data!="") {
		$invoiced_firm = passed_var("invoiced_firm", "post");
		//let's check
		$blnDefense = ($invoiced_firm=="D");
		$blnCarrier = !$blnDefense;
	} 
	
	if ($blnDefense) {		
		setCityStreet($defense);
		$arrReplace['OPPCSALUT1'] = $defense_salutation;
		$arrReplace['OPPCNAME1'] = $defense->full_name . ", ESQ";
		$arrReplace['OPPCFIRM1'] = $defense->company_name;
		$arrReplace['OPPCADD11'] = $defense->street;
		$arrReplace['OPPCADD12'] = $defense->suite;
		$arrReplace['OPPCADD21'] = $defense->suite;
		$arrReplace['OPPCPHONE1'] = $defense->phone;
		$arrReplace['OPPCTEL1'] = $defense->phone;
		$arrReplace['OPPCFAX1'] = $defense->fax;
		$arrReplace['OPPCCITYSTATEZIP1'] = $defense->city . ", " . $defense->state . " " . $defense->zip;
		
		if ($kinv_data!="") {
			//to assign to kinvoice
			$invoiced = $defense;
		}
	} else {
		setCityStreet($carrier);
		//carrier time, must be for an invoice
		$arrReplace['OPPCSALUT1'] = $carrier_salutation;
		$arrReplace['OPPCNAME1'] = $carrier->full_name;
		$arrReplace['OPPCFIRM1'] = $carrier->company_name;
		$arrReplace['OPPCSELECT'] = $carrier->company_name;
		$arrReplace['OPPCADD11'] = $carrier->street;
		$arrReplace['OPPCADD12'] = $carrier->suite;
		$arrReplace['OPPCADD21'] = $carrier->suite;
		$arrReplace['OPPCCITYSTATEZIP1'] = $carrier->city . ", " . $carrier->state . " " . $carrier->zip;
		$arrReplace['OPPCCITY'] = $carrier->city;
		$arrReplace['OPPCSTATE'] =$carrier->state;
		$arrReplace['OPPCZIP'] = $carrier->zip;
		$arrReplace['OPPCPHONE1'] = $carrier->phone;
		$arrReplace['OPPCTEL1'] = $carrier->phone;
		$arrReplace['OPPCFAX1'] = $carrier->fax;
		
		//to assign to kinvoice
		$invoiced = $carrier;
	}
	$invoiced_corporation_uuid = $invoiced->uuid;
	
	//prior attorney
	$prior_attorney_salutation = "Sir/Madam";
	if ($prior_attorney) {
		if (trim($prior_attorney->full_name)!="") {
			$prior_attorney_salutation = $prior_attorney->full_name;
			if ($prior_attorney->salutation!="") {
				$prior_attorney_salutation = $prior_attorney->salutation . " " . $prior_attorney_salutation;
			}
			$prior_attorney_salutation .= ", ESQ";
		}
		
		$letter_name = getAdhocsInfo("", $prior_attorney->corporation_id, "letter_name");
		if (count($letter_name) > 0) {
			 $prior_attorney->company_name = $letter_name[0]->adhoc_value;
		}
	}
	setCityStreet($prior_attorney);
	$arrReplace['PRIASALUT1'] = $prior_attorney_salutation;
	$arrReplace['PRIANAME1'] = $prior_attorney->full_name . ", ESQ";
	$arrReplace['PRIAFIRM1'] = $prior_attorney->company_name;
	$arrReplace['PRIAADD11'] = $prior_attorney->street;
	$arrReplace['PRIAADD12'] = $prior_attorney->suite;
	$arrReplace['PRIAADD21'] = $prior_attorney->suite;
	$arrReplace['PRIACITYSTATEZIP1'] = $prior_attorney->city . ", " . $prior_attorney->state . " " . $prior_attorney->zip;
	$arrReplace['ADDTELPRIANAME1'] = $prior_attorney->city . ", " . $prior_attorney->state . " " . $prior_attorney->zip . " " . $prior_attorney->phone;
	
	//die();
	
	//don't have that yet, need to get carrier information and then get address for letter
	$arrReplace['INSSALUT1'] = $carrier_salutation;
	$arrReplace['INSNAME1'] = $carrier->full_name;
	$arrReplace['INSFIRM1'] = $carrier->company_name;
	$arrReplace['INSSELECT'] = $carrier->company_name;
	
	//connection.php, make sure that there is street info if no street by full_address
	setCityStreet($carrier);
	
	$arrReplace['INSADD11'] = $carrier->street;
	$arrReplace['INSADD12'] = $carrier->suite;
	$arrReplace['INSADD21'] = $carrier->suite;
	$arrReplace['INSCITYSTATEZIP1'] = $carrier->city . ", " . $carrier->state . " " . $carrier->zip;
	$arrReplace['INSCITY'] = $carrier->city;
	$arrReplace['INSSTATE'] =$carrier->state;
	$arrReplace['INSZIP'] = $carrier->zip;
	$arrReplace['INSPHONE1'] = $carrier->phone;
	$arrReplace['INSTEL1'] = $carrier->phone;
	$arrReplace['INSFAX1'] = $carrier->fax;
	
	$referral_salutation = "Sir/Madam";
	if ($referral) {
		if (trim($referral->full_name)!="") {
			$referral_salutation = $referral->full_name;
			if ($referral->salutation!="") {
				$referral_salutation = 	$referral->salutation . " " . $referral_salutation;
			}
		}
	}
	setCityStreet($referral);
	$arrReplace['REFSALUT1'] = $referral_salutation;
	$arrReplace['REFNAME1'] = $referral->full_name;
	$arrReplace['REFFIRM1'] = $referral->company_name;
	$arrReplace['REFADD11'] = $referral->street;
	$arrReplace['REFADD12'] = $referral->suite;
	$arrReplace['REFADD21'] = $referral->suite;
	$arrReplace['REFCITYSTATEZIP1'] = $referral->city . ", " . $referral->state . " " . $referral->zip;
	
	$law_enforcement_salutation = "Sir/Madam";
	if ($law_enforcement) {
		if (trim($law_enforcement->full_name)!="") {
			$law_enforcement_salutation = $law_enforcement->full_name;
			if ($law_enforcement->salutation!="") {
				$law_enforcement_salutation = 	$law_enforcement->salutation . " " . $law_enforcement_salutation;
			}
		}
	}
	setCityStreet($law_enforcement);
	$arrReplace['POLSALUT1'] = $law_enforcement_salutation;
	$arrReplace['POLNAME1'] = $law_enforcement->full_name;
	$arrReplace['POLFIRM1'] = $law_enforcement->company_name;
	$arrReplace['POLADD11'] = $law_enforcement->street;
	$arrReplace['POLADD12'] = $law_enforcement->suite;
	$arrReplace['POLADD21'] = $law_enforcement->suite;
	$arrReplace['POLCITYSTATEZIP1'] = $law_enforcement->city . ", " . $law_enforcement->state . " " . $law_enforcement->zip;
	
	$witness_salutation = "Sir/Madam";
	if ($witness) {
		if (trim($witness->full_name)!="") {
			$witness_salutation = $witness->full_name;
			if ($witness->salutation!="") {
				$witness_salutation = 	$witness->salutation . " " . $witness_salutation;
			}
		}
	}
	setCityStreet($witness);
	$arrReplace['WITSALUT1'] = $witness_salutation;
	$arrReplace['WITNAME1'] = $witness->full_name;
	$arrReplace['WITFIRM1'] = $witness->company_name;
	$arrReplace['WITADD11'] = $witness->street;
	$arrReplace['WITADD12'] = $witness->suite;
	$arrReplace['WITADD21'] = $witness->suite;
	$arrReplace['WITCITYSTATEZIP1'] = $witness->city . ", " . $witness->state . " " . $witness->zip;
	
	$defendant_salutation = "Sir/Madam";
	if ($defendant) {
		if (trim($defendant->full_name)!="") {
			$defendant_salutation = $defendant->full_name;
			if ($defendant->salutation!="") {
				$defendant_salutation = 	$defendant->salutation . " " . $defendant_salutation;
			}
		}
	}
	
	//special case
	if ($defendant->full_name=="" && $defendant->company_name!="") {
		$defendant->full_name = $defendant->company_name;
	}
	setCityStreet($defendant);
	$arrReplace['DEFENDSALUT1'] = $defendant_salutation;
	$arrReplace['DEFENDNAME1'] = $defendant->full_name;
	$arrReplace['DEFENDFIRM1'] = $defendant->company_name;
	$arrReplace['DEFENDADD11'] = $defendant->street;
	$arrReplace['DEFENDADD12'] = $defendant->suite;
	$arrReplace['DEFENDADD21'] = $defendant->suite;
	$arrReplace['DEFENDCITYSTATEZIP1'] = $defendant->city . ", " . $defendant->state . " " . $defendant->zip;
	
	$arrReplace['DEFENDPHONE1'] = $defendant->phone;
	$arrReplace['DEFENDTEL1'] = $defendant->phone;
	$arrReplace['DEFENDFAX1'] = $defendant->fax;
	
	$uef_salutation = "Sir/Madam";
	if ($uef) {
		if (trim($uef->full_name)!="") {
			$uef_salutation = $uef->full_name;
			if ($uef->salutation!="") {
				$uef_salutation = 	$uef->salutation . " " . $uef_salutation;
			}
		}
	}
	setCityStreet($uef);
	$arrReplace['UEFSALUT1'] = $uef_salutation;
	$arrReplace['UEFNAME1'] = $uef->full_name;
	$arrReplace['UEFFIRM1'] = $uef->company_name;
	$arrReplace['UEFADD11'] = $uef->street;
	$arrReplace['UEFADD12'] = $uef->suite;
	$arrReplace['UEFADD21'] = $uef->suite;
	$arrReplace['UEFCITYSTATEZIP1'] = $uef->city . ", " . $uef->state . " " . $uef->zip;
	
	$arrReplace['UEFPHONE1'] = $uef->phone;
	$arrReplace['UEFTEL1'] = $uef->phone;
	$arrReplace['UEFFAX1'] = $uef->fax;
	
	$employer_salutation = "Sir/Madam";
	if ($employer) {
		if (trim($employer->full_name)!="") {
			$employer_salutation = $employer->full_name;
			if ($employer->salutation!="") {
				$employer_salutation = 	$employer->salutation . " " . $employer_salutation;
			}
		}
	}
	$arrReplace['EMPLSALUT1'] = $employer_salutation;
	$arrReplace['EMPLNAME1'] = $kase->employer_full_name;
	$arrReplace['DEFCLIENTNAME1'] = str_replace("&", "&amp;", capWords(strtolower($kase->employer)));
	
	if ($customer_id==1105) {
		$arrReplace['EMPLFIRM1'] = str_replace("&", "&amp;", strtoupper($kase->employer));
	} else {
		$arrReplace['EMPLFIRM1'] = str_replace("&", "&amp;", capWords(strtolower($kase->employer)));
	}
	//setCityStreet($employer);
	$arrReplace['EMPLADD11'] = $kase->employer_street;
	$arrReplace['EMPLADD12'] = $employer->suite;
	$arrReplace['EMPLADD21'] = $employer->suite;
	$arrReplace['EMPLCITYSTATEZIP1'] = $kase->employer_city . ", " . $kase->employer_state . " " . $kase->employer_zip;
	$arrReplace['EMPLCITY'] = $kase->employer_city;
	$arrReplace['EMPLSTATE'] = $kase->employer_state;
	$arrReplace['EMPLZIP'] = $kase->employer_zip;
	$arrReplace['EMPLPHONE1'] = $employer->phone;
	$arrReplace['EMPLTEL1'] = $employer->phone;
	$arrReplace['EMPLFAX1'] = $employer->fax;
	
	if ($blnWCAB) {
		$arrReplace['DFNTSALUT1'] = $employer_salutation;
		$arrReplace['DFNTNAME1'] = $kase->employer_full_name;
		$arrReplace['DFNTFIRM1'] = str_replace("&", "&amp;", capWords(strtolower($kase->employer)));
		$arrReplace['DFNTADD11'] = $kase->employer_street;
		$arrReplace['DFNTADD12'] = $employer->suite;
		$arrReplace['DFNTADD21'] = $employer->suite;
		$arrReplace['DFNTCITYSTATEZIP1'] = $kase->employer_city . ", " . $kase->employer_state . " " . $kase->employer_zip;
		$arrReplace['DFNTCITY'] = $kase->employer_city;
		$arrReplace['DFNTSTATE'] = $kase->employer_state;
		$arrReplace['DFNTZIP'] = $kase->employer_zip;
		$arrReplace['DFNTPHONE1'] = $employer->phone;
		$arrReplace['DFNTTEL1'] = $employer->phone;
		$arrReplace['DFNTFAX1'] = $employer->fax;
	} else {
		setCityStreet($defendant);
		$arrReplace['DFNTSALUT1'] = $defendant_salutation;
		$arrReplace['DFNTNAME1'] = $defendant->full_name;
		$arrReplace['DFNTFIRM1'] = $defendant->company_name;
		$arrReplace['DFNTADD11'] = $defendant->street;
		$arrReplace['DFNTADD12'] = $defendant->suite;
		$arrReplace['DFNTADD21'] = $defendant->suite;
		$arrReplace['DFNTCITYSTATEZIP1'] = $defendant->city . ", " . $defendant->state . " " . $defendant->zip;
		
		$arrReplace['DFNTPHONE1'] = $defendant->phone;
		$arrReplace['DFNTTEL1'] = $defendant->phone;
		$arrReplace['DFNTFAX1'] = $defendant->fax;
	}
	
	$primary_salutation = "Sir/Madam";
	if ($primary) {
		$primary_full_name = $primary->full_name;
		if ($primary_full_name == "") {
			if (strpos($primary->company_name, "MD") > 0) {
				//break up the name
				$primary_full_name = str_replace("(", "", $primary->company_name);
				$primary_full_name = str_replace(")", "", $primary_full_name);
				
				$arrPrimaryFullName = explode("MD,", $primary_full_name);
				if (count($arrPrimaryFullName)==2) {
					//make it the full name
					$primary->full_name = trim($arrPrimaryFullName[1]) . " " . trim($arrPrimaryFullName[0]) . ", MD";
				}
			}
		}
		if (trim($primary->full_name)!="") {
			$primary_salutation = $primary->full_name;
			if ($primary->salutation!="") {
				$primary_salutation = 	$primary->salutation . " " . $primary_salutation;
			}
		}
	}
	setCityStreet($primary);
	$arrReplace['PROVSALUT1'] = $primary_salutation;
	$arrReplace['PROVNAME1'] = $primary->full_name;
	$arrReplace['PROVTEL1'] = $primary->phone;
	$arrReplace['PROVPHONE1'] = $primary->phone;
	$arrReplace['PROVFAX1'] = $primary->fax;
	$arrReplace['PROVFIRM1'] = $primary->company_name;
	$arrReplace['PROVADD11'] = $primary->street;
	$arrReplace['PROVADD12'] = $primary->suite;
	$arrReplace['PROVADD21'] = $primary->suite;
	$arrReplace['PROVCITYSTATEZIP1'] = $primary->city . ", " . $primary->state . " " . $primary->zip;
	
	$arrReplace['PROVNAME2'] = "";
	$arrReplace['PROVTEL2'] = "";
	$arrReplace['PROVPHONE2'] = "";
	$arrReplace['PROVFAX2'] = "";
	$arrReplace['PROVFIRM2'] = "";
	$arrReplace['PROVADD12'] = "";
	$arrReplace['PROVADD12'] = "";
	$arrReplace['PROVADD22'] = "";
	$arrReplace['PROVCITYSTATEZIP2'] = "";
	
	$arrReplace['PROVNAME2'] = "";
	$arrReplace['PROVTEL2'] = "";
	$arrReplace['PROVPHONE2'] = "";
	$arrReplace['PROVFAX2'] = "";
	$arrReplace['PROVFIRM2'] = "";
	$arrReplace['PROVADD12'] = "";
	$arrReplace['PROVADD12'] = "";
	$arrReplace['PROVADD22'] = "";
	$arrReplace['PROVCITYSTATEZIP2'] = "";
	
	$arrReplace['PROVNAME3'] = "";
	$arrReplace['PROVTEL3'] = "";
	$arrReplace['PROVPHONE3'] = "";
	$arrReplace['PROVFAX3'] = "";
	$arrReplace['PROVFIRM3'] = "";
	$arrReplace['PROVADD13'] = "";
	$arrReplace['PROVADD13'] = "";
	$arrReplace['PROVADD23'] = "";
	$arrReplace['PROVCITYSTATEZIP3'] = "";
	
	$arrReplace['PROVTEL'] = $primary->phone;
	if (trim($primary->full_name) !="") {
		$full_provider =  $primary->full_name . "\\n";
	}
	$full_provider .=  $primary->company_name . "\\n" . $primary->street;
	if ($primary->suite!="") {
		$full_provider .=  $primary->suite . "\\n";
	} else {
		$full_provider .=  ", ";
	}
	$full_provider .=  $primary->city . ", " . $primary->state . " " . $primary->zip. "\\n" . $primary->phone;
	$arrReplace['PROVIDER'] = $full_provider;
	
	$primary_address = $primary->street;
	if ($primary->suite!="") {
		$primary_address .= "," .  $primary->suite;
	}
	$primary_address .= "," .  $primary->city . ", " .  $primary->state . " " .  $primary->zip;
	$arrReplace['PROVFULLADD'] = $primary_address;
	
	setCityStreet($venue);
	$arrReplace['COURTNAME'] = $venue->company_name;
	$arrReplace['VENUECHOICE'] = $venue->company_name;
	$arrReplace['JUDGE'] = $venue->full_name;
	$arrReplace['COURTADD11'] = $venue->street;
	$arrReplace['COURTADD12'] = $venue->suite;
	$arrReplace['COURTADD21'] = $venue->suite;
	$arrReplace['COURTADD1'] = $venue->street;
	$arrReplace['COURTADD2'] = $venue->suite;
	$arrReplace['COURTCITY'] = $venue->city;
	$arrReplace['COURTSTATE'] = $venue->state;
	$arrReplace['COURTZIP'] = $venue->zip;
	$arrReplace['COURTCITYSTATEZIP1'] = $venue->city . ", " . $venue->state . " " . $venue->zip;
	$arrReplace['COURTCITYSTZIP'] = $venue->city . ", " . $venue->state . " " . $venue->zip;
	
	$arrReplace['LETTER'] = $letter;
	$arrReplace['POSDESCRIPTION'] = $letter;
	$arrReplace['SIGNATURE'] = $_SESSION['user_name'];
	$arrReplace['PARTIES'] = $parties;
	$arrReplace['CCPARTIES'] = $parties;	
	
	$arrReplace['PARTIESBLOCK'] = $parties_block;
	$arrReplace['PARTIESNAMES'] = $parties_names;
	$arrReplace['PARTIESFAX'] = $parties_faxes;
	
	//depo additional info
	$depo_dateandtime = passed_var("depo_dateandtime", "post");
	$depo_arrival_time = passed_var("depo_arrival_time", "post");
	$depo_location = passed_var("depo_location", "post");
	$depo_address = passed_var("depo_address", "post");
	$depo_bill_dated = passed_var("depo_bill_dated", "post");
	$depo_preparation = passed_var("depo_preparation", "post");
	$depo_amount_billed = passed_var("depo_amount_billed", "post");
	$depo_atty_fee = passed_var("depo_atty_fee", "post");
	
	$depo_attorney = passed_var("depo_attorney", "post");
	if (!is_numeric($depo_attorney)) {
		$the_depo_atty = getUserByNickname($depo_attorney);
	} else {
		$the_depo_atty = getUserInfo($depo_attorney);
	}
	$depo_attorney_full_name = "";
	if (is_object($the_depo_atty)) {
		$depo_attorney_full_name = $the_depo_atty->user_name;
	}
	/*
	$arrReplace['DEPOSITIONDATE'] = date("m/d/Y", strtotime($depo_dateandtime));
	$arrReplace['DEPOSITIONTIME'] = date("G:iA", strtotime($depo_dateandtime));
	$arrReplace['DEPOSITIONDATETIME'] = date("m/d/Y G:iA", strtotime($depo_dateandtime));
	$arrReplace['DEPOSITIONARRIVALDATE'] = date("m/d/Y", strtotime($depo_arrival_time));
	$arrReplace['DEPOSITIONARRIVALTIME'] = date("G:iA", strtotime($depo_arrival_time));
	$arrReplace['DEPOSITIONARRIVALDATETIME'] = date("m/d/Y G:iA", strtotime($depo_arrival_time));
	$arrReplace['DEPOSITIONNAME'] = $depo_location;
	$arrReplace['DEPOSITIONLOC'] = $depo_address;
	$arrReplace['DEPOSITIONBILLEDDATE'] = $depo_bill_dated;
	$arrReplace['DEPOSITIONPREP'] = $depo_preparation;
	$arrReplace['DEPOSITIONAMTBILLED'] = $depo_amount_billed;
	$arrReplace['DEPOSITIONATTORNEYFEE'] = $depo_atty_fee;
	$arrReplace['DEPOSITIONATTORNEYINITIALS'] = $depo_attorney;
	$arrReplace['DEPOSITIONATTORNEYNAME'] = $depo_attorney_full_name;
	*/
	/*
	if ($_SERVER['REMOTE_ADDR']=='47.153.49.248') {
		die(print_r($arrReplace));
	}
	*/
	$case_uuid = $kase->uuid;
	$case_id = $kase->id;
	$kinvoice_document_id = "";
	$transfer_funds = "";
	$kinvoice_type = "";
	$kinvoice_number = "";
	$invoice_total = 0;
	
	if ($kinv_data!="") {
		$hourly_rate = $arrKInvoice["hourly_rate"];
		$parent_kinvoice_id = $arrKInvoice["kinvoice_id"];
		$kinvoice_number = $arrKInvoice["kinvoice_number"];
		$employee_id = passed_var("assigneeInput", "post");
		$transfer_funds = passed_var("transfer_funds", "post");
		$kinvoice_type = passed_var("kinvoice_type", "post");
		
		//i need the original invoice document id, if any, so i can void it
		$kinvoice_document_id = $arrKInvoice["kinvoice_document_id"];
		
		$employee_name = $_SESSION["user_name"];
		if ($employee_id!="") {
			$employee = getUserInfo($employee_id);
			$employee_uuid = $employee->uuid;
			$employee_name = $employee->user_name;
		} else {
			$employee_uuid = $user_id;
		}
		
		$arrReplace['HOURLY_RATE'] = "$" . $hourly_rate;
		$arrReplace['INVBY'] = $employee_name;
		$arrReplace['INVDATE'] = date("m/d/Y", strtotime($last_date));
		
		//the invoice can be for defense or carrier
		$invoiced_firm = passed_var("invoiced_firm", "post");
		
		$arrInvoiceItems = array();
		$arrInvoiceMinutes = array();
		$arrInvoiceAmounts = array();
		$arrInvoiceActualAmounts = array();
		$arrInvoiceItemDesc = array();
		$arrInvoiceUnits = array();
		$arrCompleted = array();
		$invoice_minutes = 0;
		$invoice_total = 0;
		if (count($arrKInvoice) > 0) {
			//die(print_r($arrKInvoice));
			foreach($arrKInvoice as $fieldname=>$inv_item) {
				if ($fieldname=="hourly_rate" || $fieldname=="kinvoice_id" || $fieldname=="employee_id") {
					continue;
				}
				
				//amount 
				if (strpos($fieldname, "amount_")!==false) {
					$arrInvoiceAmounts[] = "$" . number_format(floatval($inv_item), 2);
					$arrInvoiceActualAmounts[] = $inv_item;
					$invoice_total += floatval($inv_item);
				}
				
				$arrFieldID = explode("_", $fieldname);
				$fieldnumber = $arrFieldID[count($arrFieldID) - 1];
				
				//echo $fieldnumber . "\r\n";
				
				if (in_array($fieldnumber, $arrCompleted)) {
					continue;
				}
				//die("pos:" . strpos($fieldname, "hours_"));
				//minutes 
				if (strpos($fieldname, "hours_")!==false && $inv_item!="") {
					$arrCompleted[] = $fieldnumber;
					//get the id of the template
					$arrHourID = explode("_", $fieldname);
					//die(print_r($arrHourID));
					$kinvoiceitem_id = $arrHourID[1];
					$kinvoiceitem = getKInvoiceItem($kinvoiceitem_id, true);
					
					//print_r($kinvoiceitem);
					$rate = passed_var("kinv_rate_" . $kinvoiceitem_id, "post");
					$unit = $kinvoiceitem->unit;
					//clean up
					$arrName = explode("    (", $kinvoiceitem->item_name);
					$kinvoiceitem->item_name = $arrName[0];
					//$blnCost = ($unit!="");
					$blnCost = ($kinvoiceitem->exact=="Y");
					if (!$blnCost) {
					//die(print_r($kinvoiceitem));
						//echo $fieldname . " ==> " . $kinvoiceitem_id . " :" . $kinvoiceitem->item_name . "(" . count($arrName.length) . ")" . " per " . $unit . "\r\n";
						
						if ($rate=="") {
							$arrInvoiceItems[] = $kinvoiceitem->item_name . "    ($" . $hourly_rate . " per Hour)";
							$arrInvoiceItemDesc[] = $kinvoiceitem->item_description;
							if ($inv_item=="") {
								$inv_item = " ";
							}
							$arrInvoiceMinutes[] = $inv_item . " Hrs";
							if ($inv_item!=" ") {
								$invoice_minutes += floatval($inv_item);
							}
						} else {
							//die(print_r($kinvoiceitem));
							
							$arrInvoiceItems[] = $kinvoiceitem->item_name . "    ($" . $rate . " per " . $unit . ")";
							$arrInvoiceItemDesc[] = $kinvoiceitem->item_description;
							
							if ($inv_item=="") {
								$inv_item = " ";
							} else {
								$inv_item *= $rate;
							}
							$arrInvoiceMinutes[] = $inv_item . " " . $unit . "(s)";
						}
					} else {
						//exact cost
						$arrInvoiceItems[] = $kinvoiceitem->item_name . " (Flat Fee)";
						$arrInvoiceItemDesc[] = $kinvoiceitem->item_description;
						$arrInvoiceMinutes[] = "1";
					}
				}
				
				//cost 
				if (strpos($fieldname, "qty_")!==false) {
					//echo $fieldname . " <==> " . $kinvoiceitem_id . " :" . $kinvoiceitem->item_name . "(" . count($arrName.length) . ")" . " per " . $unit . "\r\n";
					$arrCompleted[] = $fieldnumber;
					//get the id of the template
					$arrQtyID = explode("_", $fieldname);
					//die(print_r($arrHourID));
					$kinvoiceitem_id = $arrQtyID[1];
					$kinvoiceitem = getKInvoiceItem($kinvoiceitem_id, true);
					$arrName = explode("    (", $kinvoiceitem->item_name);
					$kinvoiceitem->item_name = $arrName[0];
					$rate = passed_var("kinv_rate_" . $kinvoiceitem_id, "post");
					$unit = passed_var("kinv_rateunit_" . $kinvoiceitem_id, "post");
					if ($rate!="") {
						//die(print_r($kinvoiceitem));
						//echo $fieldname . " <==> " . $kinvoiceitem_id . " ::" . $kinvoiceitem->item_name . "\r\n";
						$arrInvoiceItems[] = $kinvoiceitem->item_name . "    ($" . $rate . " per " . $unit . ")";
						$arrInvoiceItemDesc[] = $kinvoiceitem->item_description;
						
						if ($inv_item=="") {
							$inv_item = " ";
						}
						$arrInvoiceMinutes[] = $inv_item . " " . $unit . "(s)";
					} else {
						$arrInvoiceItems[] = $kinvoiceitem->item_name . " (Flat Fee)";
						$arrInvoiceItemDesc[] = $kinvoiceitem->item_description;
						$arrInvoiceMinutes[] = "1";
					}
					//keep track of units
					$arrInvoiceUnits[] = $unit;
				}
			}
			//echo $invoice_minutes;
			//print_r($arrCompleted);
			//print_r($arrInvoiceItems);
			//print_r($arrInvoiceAmounts);
			//die();
			//die(print_r($arrInvoiceMinutes));
			
			$formatted_invoice_total =  number_format($invoice_total, 2);
			
			$arrReplace['INVTOTAL'] = "$" . $formatted_invoice_total;
			
			$arrReplace['INVOICEITEMS'] = implode("\\n", $arrInvoiceItems) . "\\n \\n \\nTotal";
			$arrReplace['INVQTY'] = implode("\\n", $arrInvoiceMinutes);
			$arrReplace['INVAMNT'] = implode("\\n", $arrInvoiceAmounts) . "\\n \\n \\n $" . $formatted_invoice_total;
			
			//die(print_r($arrReplace));
			//parent info
			$parent_kinvoice = getKInvoiceInfo($parent_kinvoice_id);
			$parent_kinvoice_uuid = $parent_kinvoice->uuid;
			$right_now = date("Y-m-d H:i:s");
			if ($kinvoice_document_id=="") {
				//store the invoice, with parent with tracking
				try {
					//we need to generate kinvoice_number
					$invoice_counter = getKaseKinvoiceNextCounter($case_id); 
					/*
					$sql = "SELECT COUNT(DISTINCT cck.kinvoice_uuid) invoice_count
					FROM cse_case_kinvoice cck
					INNER JOIN cse_kinvoice ki
					ON cck.kinvoice_uuid = ki.kinvoice_uuid AND ki.deleted = 'N'
					INNER JOIN cse_case ccase
					ON cck.case_uuid = ccase.case_uuid
					WHERE cck.deleted = 'N'
					AND ccase.case_id = :case_id
					AND ccase.customer_id = :customer_id";
					
					$db = getConnection();
			
					$stmt = $db->prepare($sql);
					$stmt->bindParam("case_id", $case_id);
					$stmt->bindParam("customer_id", $customer_id);
					$stmt->execute();
					$counter = $stmt->fetchObject();
		
					$invoice_counter = $counter->invoice_count + 1;
					*/
					$kinvoice_number = $case_id . "-" . $invoice_counter;
					$kinvoice_uuid = uniqid("LI", false);
					
					$template_val = "N";
					$template_name = $parent_kinvoice->template_name;
					$sql = "INSERT INTO cse_kinvoice (`kinvoice_uuid`, `parent_kinvoice_uuid`, `kinvoice_date`, `kinvoice_type`, `kinvoice_number`, `invoice_counter`, 
					`hourly_rate`, `total`, `customer_id`, `template`, `template_name`)
					VALUES (:kinvoice_uuid, :parent_kinvoice_uuid, :kinvoice_date, :kinvoice_type, :kinvoice_number, :invoice_counter, 
					:hourly_rate, :total, :customer_id, :template, :template_name)";
					
					$kinvoice_date = date("Y-m-d H:i:s", strtotime($last_date));
					
					$db = getConnection();
					$stmt = $db->prepare($sql);
					$stmt->bindParam("kinvoice_uuid", $kinvoice_uuid);
					$stmt->bindParam("parent_kinvoice_uuid", $parent_kinvoice_uuid);
					$stmt->bindParam("kinvoice_number", $kinvoice_number);
					$stmt->bindParam("kinvoice_date", $kinvoice_date);
					$stmt->bindParam("kinvoice_type", $kinvoice_type);
					$stmt->bindParam("invoice_counter", $invoice_counter);
					$stmt->bindParam("hourly_rate", $hourly_rate);
					$stmt->bindParam("total", $invoice_total);
					$stmt->bindParam("customer_id", $customer_id);
					$stmt->bindParam("template",$template_val);
					$stmt->bindParam("template_name", $template_name);
					$stmt->execute();
					
					$kinvoice_id = $db->lastInsertId();
					
					trackKInvoice("insert", $kinvoice_id);
					
					if ($kinvoice_type=="P") {
						$kinvoice_number .= "\\n";
						$kinvoice_number .= "DRAFT";
					}
					$arrReplace['INVNUMB'] = $kinvoice_number;
					
					//get the account id
					$account = getBankAccount("", $case_id, "trust", true);
					$account_id = $account->id;
					
					//attach the invoice to the account
					if ($account_id!="") {
						//attach invoice to invoiced
						$sql = "INSERT INTO `cse_account_kinvoice`
						(`account_kinvoice_uuid`, `account_uuid`, `kinvoice_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
						VALUES
						(:account_kinvoice_uuid, :account_uuid, :kinvoice_uuid, :attribute, :right_now, :user_uuid, :customer_id)";
						
						$account_kinvoice_uuid = uniqid("CK", false);
						$account_uuid = $account->uuid;
						$attribute = $account->account_type;
						
						$db = getConnection();
						$stmt = $db->prepare($sql);
						$stmt->bindParam("account_kinvoice_uuid", $account_kinvoice_uuid);
						$stmt->bindParam("account_uuid", $account_uuid);
						$stmt->bindParam("kinvoice_uuid", $kinvoice_uuid);
						$stmt->bindParam("attribute", $attribute);
						$stmt->bindParam("right_now", $right_now);
						$stmt->bindParam("user_uuid", $user_id);	//we can use the user_uuid field to show who the invoice was assigned to
						$stmt->bindParam("customer_id", $customer_id);
						
						$stmt->execute();
					}
					//do we transfer funds
					if ($transfer_funds=="Y" && $account_id != "") {
						//update the account balance
						$sql = "UPDATE cse_account
						SET account_balance = (account_balance - " . $invoice_total . ")
						WHERE account_id = :account_id
						AND customer_id = :customer_id";
						
						$db = getConnection();
						$stmt = $db->prepare($sql);
						$stmt->bindParam("account_id", $account_id);
						$stmt->bindParam("customer_id", $customer_id);
						$stmt->execute();
						
						trackAccount("transfer", $account_id);
					}
					$case_kinvoice_uuid = uniqid("KI", false);
					
					//attach to case
					$sql = "INSERT INTO `cse_case_kinvoice`
		(`case_kinvoice_uuid`, `case_uuid`, `kinvoice_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES
		(:case_kinvoice_uuid, :case_uuid, :kinvoice_uuid, 'main', :right_now, :user_uuid, :customer_id)";
					
					$db = getConnection();
					$stmt = $db->prepare($sql);
					
					$stmt->bindParam("case_kinvoice_uuid", $case_kinvoice_uuid);
					$stmt->bindParam("case_uuid", $case_uuid);
					$stmt->bindParam("kinvoice_uuid", $kinvoice_uuid);
					$stmt->bindParam("right_now", $right_now);
					$stmt->bindParam("user_uuid", $employee_uuid);	//we can use the user_uuid field to show who the invoice was assigned to
					$stmt->bindParam("customer_id", $customer_id);
					
					$stmt->execute();
					
					$corporation_kinvoice_uuid = uniqid("CK", false);
					$attribute = "carrier";
					if ($blnDefense) {
						$attribute = "defense";
					}
					//attach invoice to invoiced
					$sql = "INSERT INTO `cse_corporation_kinvoice`
			(`corporation_kinvoice_uuid`, `corporation_uuid`, `kinvoice_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES
			(:corporation_kinvoice_uuid, :corporation_uuid, :kinvoice_uuid, :attribute, :right_now, :user_uuid, :customer_id)";
						
					$db = getConnection();
					$stmt = $db->prepare($sql);
					
					$stmt->bindParam("corporation_kinvoice_uuid", $corporation_kinvoice_uuid);
					$stmt->bindParam("corporation_uuid", $invoiced_corporation_uuid);
					$stmt->bindParam("kinvoice_uuid", $kinvoice_uuid);
					$stmt->bindParam("attribute", $attribute);
					$stmt->bindParam("right_now", $right_now);
					$stmt->bindParam("user_uuid", $user_id);	//we can use the user_uuid field to show who the invoice was assigned to
					$stmt->bindParam("customer_id", $customer_id);
					
					$stmt->execute();
					
					//now add each item
					foreach($arrInvoiceItems as $kindex=>$item_name) {
						$description = $arrInvoiceItemDesc[$kindex];
						$minutes = $arrInvoiceMinutes[$kindex];
						$arrMinutes = explode(" ", $minutes);
						$minutes = $arrMinutes[0];
						$unit = $arrInvoiceUnits[$kindex];
						
						if (trim($minutes)=="") {
							$minutes = -1;
						}
						$amount = $arrInvoiceActualAmounts[$kindex];
						if ($amount=="") {
							$amount = 0;
						}
						$kinvoiceitem_uuid = uniqid("II", false);
						$sql = "INSERT INTO `cse_kinvoiceitem` (kinvoiceitem_uuid, kinvoice_uuid, item_name, item_description, minutes, amount, unit, customer_id)
						VALUES(:kinvoiceitem_uuid, :kinvoice_uuid, :item_name, :item_description, :minutes, :amount, :unit, :customer_id)";
						
						$db = getConnection();
						$stmt = $db->prepare($sql);
						$stmt->bindParam("kinvoiceitem_uuid", $kinvoiceitem_uuid);
						$stmt->bindParam("kinvoice_uuid", $kinvoice_uuid);
						$stmt->bindParam("item_name", $item_name);
						$stmt->bindParam("item_description", $description);
						$stmt->bindParam("minutes", $minutes);
						$stmt->bindParam("amount", $amount);
						$stmt->bindParam("unit", $unit);
						$stmt->bindParam("customer_id", $customer_id);
						$stmt->execute();
					}
				} catch(PDOException $e) {
					$error = array("error doc"=> array("sql"=>$sql, "text"=>$e->getMessage()));
					die(json_encode($error));
				}
			} else {
				$kinvoice_id = $parent_kinvoice_id;
				$kinvoice_uuid = $parent_kinvoice_uuid;
				$kinvoice_date = date("Y-m-d H:i:s", strtotime($last_date));
				
				//this is an update, need to void kinvoice_document_id
				$sql = "UPDATE cse_kinvoice 
				SET `kinvoice_date` = :kinvoice_date, 
				`hourly_rate` = :hourly_rate, 
				`total` = :total
				WHERE kinvoice_id = :kinvoice_id
				AND customer_id = :customer_id";
				
				$arrReplace['INVNUMB'] = $parent_kinvoice->kinvoice_number;
				
				$db = getConnection();
				$stmt = $db->prepare($sql);
				$stmt->bindParam("kinvoice_id", $kinvoice_id);
				$stmt->bindParam("kinvoice_date", $kinvoice_date);
				$stmt->bindParam("hourly_rate", $hourly_rate);
				$stmt->bindParam("total", $invoice_total);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
				
				//clear invoiced
				if ($parent_kinvoice->corporation_uuid != $invoiced_corporation_uuid) {
					$sql = "UPDATE `cse_corporation_kinvoice`
					SET deleted = 'Y',
					`last_updated_date` = :right_now,
					`last_update_user` = :user_uuid
					WHERE kinvoice_uuid = :kinvoice_uuid
					AND corporation_uuid = :corporation_uuid
					AND customer_id = :customer_id";
					$db = getConnection();
					$stmt = $db->prepare($sql);
					$stmt->bindParam("kinvoice_uuid", $kinvoice_uuid);
					$stmt->bindParam("right_now", $right_now);
					$stmt->bindParam("user_uuid", $user_id);
					$stmt->bindParam("corporation_uuid", $parent_kinvoice->corporation_uuid);
					$stmt->bindParam("customer_id", $customer_id);
					$stmt->execute();
					
					//attach invoice to invoiced
					$sql = "INSERT INTO `cse_corporation_kinvoice`
					(`corporation_kinvoice_uuid`, `corporation_uuid`, `kinvoice_uuid`, `attribute`, 
					`last_updated_date`, `last_update_user`, `customer_id`)
					VALUES
					(:corporation_kinvoice_uuid, :corporation_uuid, :kinvoice_uuid, :attribute, 
					:right_now, :user_uuid, :customer_id)";
						
					$corporation_kinvoice_uuid = uniqid("CK", false);
					$attribute = "carrier";
					if ($blnDefense) {
						$attribute = "defense";
					}
					
					$db = getConnection();
					$stmt = $db->prepare($sql);
					$stmt->bindParam("corporation_kinvoice_uuid", $corporation_kinvoice_uuid);
					$stmt->bindParam("corporation_uuid", $invoiced_corporation_uuid);
					$stmt->bindParam("kinvoice_uuid", $kinvoice_uuid);
					$stmt->bindParam("attribute", $attribute);
					$stmt->bindParam("right_now", $right_now);
					$stmt->bindParam("user_uuid", $user_id);	//we can use the user_uuid field to show who the invoice was assigned to
					$stmt->bindParam("customer_id", $customer_id);
					
					$stmt->execute();
				}
				
				//user
				if ($parent_kinvoice->assigned_to != $employee_uuid) {
					//reassign the invoice
					$sql = "UPDATE `cse_case_kinvoice`
					SET `last_updated_date` = :right_now,
					`last_update_user` = :user_uuid
					WHERE kinvoice_uuid = :kinvoice_uuid
					AND customer_id = :customer_id";
					$db = getConnection();
					$stmt = $db->prepare($sql);
					$stmt->bindParam("kinvoice_uuid", $kinvoice_uuid);
					$stmt->bindParam("right_now", $right_now);
					$stmt->bindParam("user_uuid", $employee_uuid);
					$stmt->bindParam("customer_id", $customer_id);
					$stmt->execute();
				}
				
				//clear items
				$sql = "UPDATE `cse_kinvoiceitem`
				SET deleted = 'Y'
				WHERE kinvoice_uuid = :kinvoice_uuid
				AND customer_id = :customer_id";
				$db = getConnection();
				$stmt = $db->prepare($sql);
				$stmt->bindParam("kinvoice_uuid", $kinvoice_uuid);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
				
				//now add each item
				//die(print_r($arrInvoiceItems));
				//die(print_r($arrInvoiceActualAmounts));
				foreach($arrInvoiceItems as $kindex=>$item_name) {
					$description = $arrInvoiceItemDesc[$kindex];
					$minutes = $arrInvoiceMinutes[$kindex];
					
					$arrMinutes = explode(" ", $minutes);
					$minutes = trim($arrMinutes[0]);
					if ($minutes=="") {
						$minutes = -1;
					}
					$amount = $arrInvoiceActualAmounts[$kindex];
					if ($amount=="") {
						$amount = 0;
					}
					$kinvoiceitem_uuid = uniqid("II", false);
					$sql = "INSERT INTO `cse_kinvoiceitem` (kinvoiceitem_uuid, kinvoice_uuid, item_name, item_description, minutes, amount, customer_id)
					VALUES(:kinvoiceitem_uuid, :kinvoice_uuid, :item_name, :item_description, :minutes, :amount, :customer_id)";
					
					//die("stop");
					$db = getConnection();
					$stmt = $db->prepare($sql);
					$stmt->bindParam("kinvoiceitem_uuid", $kinvoiceitem_uuid);
					$stmt->bindParam("kinvoice_uuid", $kinvoice_uuid);
					$stmt->bindParam("item_name", $item_name);
					$stmt->bindParam("item_description", $description);
					$stmt->bindParam("minutes", $minutes);
					$stmt->bindParam("amount", $amount);
					$stmt->bindParam("customer_id", $customer_id);
					$stmt->execute();
				}
				
				//clear documents
				$sql = "UPDATE `cse_document_kinvoice`
				SET deleted = 'Y'
				WHERE kinvoice_uuid = :kinvoice_uuid
				AND customer_id = :customer_id";
				$db = getConnection();
				$stmt = $db->prepare($sql);
				$stmt->bindParam("kinvoice_uuid", $kinvoice_uuid);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
			}
		}
	}
	$arrReplace['letterhead'] = "";
	
	foreach($arrReplace as $replace_index=>$replace) {
		if (strpos($replace, "&amp;")===false && strpos($replace, "&")!==false) {
			$replace = str_replace("&", "&amp;", $replace);
			//die($replace_index . " = " . $replace);
			$arrReplace[$replace_index] = $replace;
		}
	}
	if ($template->document_filename=="kase_settlement.docx") {
		if ($_SERVER['REMOTE_ADDR'] == "47.153.49.248") {
			//die($doi_id . " - injury_id");
		}
		$settlement_sheet = getSettlementSheetInfo($doi_id);
		//die($settlement_sheet->data);
		$sheet_data = $settlement_sheet->data;
		$arrData= json_decode($sheet_data);
		
		//die($arrData);
		
		$arrReplace['SETTCOSTADV'] = "$" . $arrData->costs;
		$arrReplace['SETTDATE'] = date("m/d/Y g:iA");
		$arrReplace['SETTTOTALDUE'] = "$" . number_format($arrData->due, 2);
	}
	

	$variables = $arrReplace;
	
	$docx = new CreateDocxFromTemplate('../uploads/' . $customer_id . $prefix . '/' . $template->document_filename);
	
	$arrExamAttach = array();
	
	if ($template->document_filename=="kase_settlement.docx") {
		$billings = getMedicalBillingsSummaryInfo($case_id);
		//die(print_r($billings));
		$total_medical_balance = 0;
		$data = array();
		foreach($billings as $billing) {
			if ($billing->balance > 0) {
				$data[] = array(
					'SETTPROVIDER'		=>	$billing->company_name,
					'PROVAMNT'			=>	"$" . $billing->billed,
					'PROVSUB'			=>	"",
					'PROVTOTAL'			=>	""
					
				);
				if ($billing->adjusted != 0) {
					$data[] = array(
						'SETTPROVIDER'		=>	"Adjustments",
						'PROVAMNT'			=>	"$" . $billing->adjusted,
						'PROVSUB'			=>	"",
						'PROVTOTAL'			=>	""
						
					);
				}
				$data[] = array(
					'SETTPROVIDER'		=>	"This Provider Sub-Total",
					'PROVAMNT'			=>	"",
					'PROVSUB'			=>	"$" . $billing->balance,
					'PROVTOTAL'			=>	""
					
				);
				
				$data[] = array(
					'SETTPROVIDER'		=>	"",
					'PROVAMNT'			=>	"",
					'PROVSUB'			=>	"",
					'PROVTOTAL'			=>	""
					
				);
			}
			$total_medical_balance += $billing->balance;
		}
		if (count($data) == 0) {
			$data[] = array(
					'SETTPROVIDER'		=>	"",
					'PROVAMNT'			=>	"",
					'PROVSUB'			=>	"",
					'PROVTOTAL'			=>	""
					
				);
		} else {
			$data[] = array(
				'SETTPROVIDER'		=>	"Less Medical Bills",
				'PROVAMNT'			=>	"",
				'PROVSUB'			=>	"",
				'PROVTOTAL'			=>	"$" . number_format($total_medical_balance, 2)
			);
		}
		
		$docx->replaceTableVariable($data);

		//carriers
		$data = array();
		$carriers = getKasePartiesByTypeInfo($case_id, "carrier");
		foreach($carriers as $carrier) {
			$carrier_id = $carrier->corporation_id;
			$carrier_name = $carrier->company_name;
			
			$financial = getCarrierFinancialInfo($case_id, $carrier_id);
			
			$financial_info = $financial[0]->financial_info;
			if ($financial_info!="") {
			//die($financial_info);
				$arrFinancial = json_decode($financial_info);
				//die(print_r($arrFinancial));
				$total_subro = 0;
				foreach($arrFinancial as $findex=>$item_info) {
					//die(print_r($item_info));
					$arrLength = count($item_info);
					for($int = 0 ; $int < $arrLength; $int++) {
						$itum = $item_info[$int];
						if ($itum->name=="balanceInput") {
							if ($itum->value > 0) {
								$total_subro += $itum->value;
								
								$data[] = array(
									'SETTCARRIER'		=>	$carrier_name,
									'CARRSUBRO'			=>	"$" . $itum->value,
									'LESSSUBRO'			=>	""
								);
								
								$data[] = array(
									'SETTCARRIER'		=>	"Less Insurance Subrogation",
									'CARRSUBRO'			=>	"",
									'LESSSUBRO'			=>	"$" . number_format($total_subro, 2)
								);
							}
						}
					}
				}
			}
		}
		if (count($data) == 0) {
			$data[] = array(
						'SETTCARRIER'		=>	"",
						'CARRSUBRO'			=>	"",
						'LESSSUBRO'			=>	""
					);
		}
		//die(print_r($data));
		$docx->replaceTableVariable($data);
		
		$data = array();
		//offers
		//echo $arrData->grossdesc1; 
		if ($arrData->grossdesc1!="") {
			$data[] = array(
				'SETTDESCRIPTION'		=>	$arrData->grossdesc1,
				'SETTGROSS'				=>	"$" . $arrData->gross,
				'SETTPERCENT'			=>	$arrData->pct,
				'SETTTOTAL'				=>	"$" . $arrData->legalfees
			);
			//$arrReplace['SETTDESCRIPTION0'] = $arrData->grossdesc1;
		}
		//$arrReplace['SETTDESCRIPTION'] = "Attorney Fees";
		if ($_SERVER['REMOTE_ADDR'] == "47.153.49.248") {
			//die(print_r($arrData) . " - name");
		}
		if ($arrData->grossdesc2!="") {
			$data[] = array(
				'SETTDESCRIPTION'		=>	$arrData->grossdesc2,
				'SETTGROSS'				=>	"$" . $arrData->gross2,
				'SETTPERCENT'			=>	$arrData->pct2,
				'SETTTOTAL'				=>	"$" . $arrData->legalfees2
			);
		}
		if ($arrData->grossdesc3!="") {
			$data[] = array(
				'SETTDESCRIPTION'		=>	$arrData->grossdesc3,
				'SETTGROSS'				=>	"$" . $arrData->gross3,
				'SETTPERCENT'			=>	$arrData->pct3,
				'SETTTOTAL'				=>	"$" . $arrData->legalfees3
			);
		}
		
		if (count($data) == 0) {
			$data[] = array(
				'SETTDESCRIPTION'		=>	"ATTORNEY FEES",
				'SETTGROSS'				=>	"$ ",
				'SETTPERCENT'			=>	"% ",
				'SETTTOTAL'				=>	"$" . $arrData->legalfees
			);
		}
		//die(print_r($data));
		//$arrReplace['SETTGROSS'] = $arrData->grossdesc1;
		$docx->replaceTableVariable($data);
	}
	if ($template->document_filename=="kase_medindex.docx") {
		$exams = getExams($case_id, true, false);
		$exam_ids = passed_var("exam_ids", "post");
		
		$arrSelectedExams = explode("|", $exam_ids);
		//die(print_r($arrSelectedExams));
		$zip_dir = "../uploads/" . $customer_id . "/zips";
		if (!file_exists($zip_dir)) {
			mkdir($zip_dir, 0777);
		}
		$zip_dir .= "/" . $case_id;
		if (!file_exists($zip_dir)) {
			mkdir($zip_dir, 0777);
		}
		//$zip_path = $zip_dir . "/med_index_" . date("YmdHi") . ".zip";
		$zip_source_path = UPLOADS_PATH. $customer_id . '\\' . $case_id . '\\';
		$zip_source_path = '../uploads/' . $customer_id . '/' . $case_id . '/';
		$zip_folder_path = UPLOADS_PATH. $customer_id . '\\zips\\' . $case_id . '\\';
		$zip_path = $zip_folder_path . "med_index_" . date("Ymd") . ".zip";
		
		//print_r($arrSelectedExams);
		$data = array();
		$intCounter = 0;
		foreach($exams as $int=>$exam) {
			if ($exam_ids!="") {
				if (!in_array($exam->id, $arrSelectedExams)) {
					continue;
				}
			}
			$intCounter++;
			//echo $exam->id . "\r\n";
			$date = "";
			$fs_date = $exam->fs_date;
			if ($fs_date!="0000-00-00") {
				$date = "Filed: " . date("m/d/Y", strtotime($fs_date));
			}
			$document_id = $exam->document_id;
			
			if ($document_id!="") {
				$exam_document_filename = $exam->document_filename;
				$exam_document_filename = $zip_source_path . urldecode($exam_document_filename);
				
				$arrExamAttach[] = $exam_document_filename;
			}
			$document_date = $exam->document_date;
			if ($date=="") {
				if ($document_date == "") {
					$exam_date = $exam->exam_dateandtime;
					//per rosie q 9/14/2018
					if ($customer_id=="1075") {
						$date = date("m/d/Y", strtotime($exam_date));
					} else {
						$date = "Examined: " . date("m/d/Y", strtotime($exam_date));
					}
				} else {
					$date = "Received: " . date("m/d/Y", strtotime($document_date));
				}
			}
			if ($customer_id=="1075") {
				$docname = $exam->company_name;
				$rectype = "";
				if ($exam->comments!="") {
					$rectype = "Doc Description:" . $exam->comments;
					$rectype = str_replace("<BR>", "
", $rectype);
				}
				$data[] = array(
					'INDEXNO'		=>	$intCounter,
					'DOCNAME'		=>	$docname,
					'RECTYPE'		=>	$rectype,
					'INDEXDATE'		=>	$date
				);
			} else {
				$rectype = $exam->comments;
				$rectype = str_replace("<BR>", "
", $rectype);
				$data[] = array(
					'INDEXNO'		=>	$intCounter,
					'RECTYPE'		=>	$rectype,
					'INDEXDATE'		=>	$date
				);
			}			
		}
		//die(print_r($data));
		
		$docx->replaceTableVariable($data);
	}
	
	if ($template->source!="no_letterhead" && $template->source!="clientname_letterhead") {
		//if ($customer_id!=1042) {
			$docx ->importHeadersAndFooters('../uploads/' . $customer_id . "/" . $letterhead->value);
		/*
		} else {			
			//patel has 2-part header
			include_once("patel_header.php");
			$docx ->importHeadersAndFooters('../uploads/' . $customer_id . "/" . $letterhead->value, 'footer');
		}
		*/
	}
	
	$options = array('parseLineBreaks' => true);
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$pre_time = round(($finish_time - $header_start_time), 4);
	if ($_SERVER['REMOTE_ADDR'] == "47.153.49.248") {
		//die(print_r($options));
	}
	$docx->replaceVariableByText($variables, $options);
	
	//if ($_SERVER['REMOTE_ADDR']=='47.153.56.2') {
	if ($template->source=="clientname_letterhead") {
		$headerText = new WordFragment($docx, 'defaultHeader');
		$textOptions = array(
		'bold' => true
		);
		$headerText->addText("CLIENT'S NAME:" . $arrReplace["CLIENTNAME"], $textOptions);
		$valuesTable = array(
			array(
				array('value' =>$headerText, 'vAlign' => 'left')
			),
		);
		$widthTableCols = array(
			7500,
			700,
			500
		);
		$paramsTable = array(
			'border' => 'nil',
			'columnWidths' => $widthTableCols,
		);
		$headerTable = new WordFragment($docx, 'defaultHeader');
		$headerTable->addTable($valuesTable, $paramsTable);
		
		//add some text to the body of the document
		$docx->addHeader(array('default' => $headerTable));
	}
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$header_time = round(($finish_time - $header_start_time), 4);
	
	if ($signature_img!="") {
		//die($signature_img);
		$signature_img = UPLOADS_PATH. $customer_id . DC . $signature_img;
		if (file_exists($signature_img)) {
			//die(print_r($_SERVER));
			$docx->replacePlaceholderImage('SIGNATURE_IMAGE', $signature_img);
		}
	}
	/*
	$arrReplace['INVOICEITEMS'] = implode("\\n", $arrInvoiceItems) . "\\n \\n \\nTotal";
	$arrReplace['INVQTY'] = implode("\\n", $arrInvoiceMinutes);
	$arrReplace['INVAMNT'] = implode("\\n", $arrInvoiceAmounts) . "\\n \\n \\n $" . $invoice_total;
	*/
	/*
	if ($kinv_data!="") {
		$arrRows = array();
		$arrRows[] = array(
					'ACTIVITY',
					'DATE',
					'QTY',
					'AMOUNT'
				);
		foreach($arrInvoiceItems as $kindex=>$item) {
			$minutes = $arrInvoiceMinutes[$kindex];
			$amount = $arrInvoiceAmounts[$kindex];
			$arrRows[] = array(
					$item,
					date("m/d/y", strtotime($kinvoice_date)),
					$minutes,
					$amount
				);
		}

		$valuesTable = $arrRows;
		
		$paramsTable = array(
			'tableStyle' => 'LightListAccent1PHPDOCX',
			'tableAlign' => 'center',
			'columnWidths' => array(5000, 1500, 1500, 1500),
			'border' => 'bottom',
			'tableAlign' => 'left',
			'borderWidth' => 10,
			'borderColor' => '000000',
			'textProperties' => array('bold' => true, 'font' => 'Times New Roman', 'fontSize' => 12),
		);
		$docx->addTable($valuesTable, $paramsTable);
		
		$valuesTable = array(
			array(
				'Invoiced By',
				$employee_name
			),
			array(
				'Hourly Rate',
				"$" . $hourly_rate . " per hour"
			),
		);
		$paramsTable = array(
			'tableStyle' => 'LightListAccent1PHPDOCX',
			'tableAlign' => 'center',
			'columnWidths' => array(1500, 2500),
			'border' => 'bottom',
			'tableAlign' => 'left',
			'borderWidth' => 10,
			'borderColor' => '000000',
			'textProperties' => array('bold' => false, 'font' => 'Times New Roman', 'fontSize' => 12),
		);
		$docx->addTable($valuesTable, $paramsTable);
	}
	*/
	
	$docx->createDocx($destination); 
	$document_filename = $destination;
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$create_time = round(($finish_time - $header_start_time), 4);
	
	if ($blnNormalReturn) {	
		$document_uuid = uniqid("KS");
	
		$sql = "INSERT INTO cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, description, description_html, source, type, verified, customer_id) 
				VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :description, :description_html, :source, :type, :verified, :customer_id)";
		try {
			$db = getConnection();
			
			
			$document_date = date("Y-m-d H:i:s");
			$document_extension = "docx";
			$subject = passed_var("subject", "post");
			if ($any_subject!="") {
				if ($subject == "") {
					$subject = $any_subject;
				} else {
					$subject .= "<br>" . $any_subject;
				}
			}
			$description_html = $depo_additional;
			$type = "letter";
			$arrData = $_POST;
			$arrData["doi"] = $doi;
			$description = json_encode($arrData);
			//might be an invoice
			if ($kinv_data!="") {
				$description_html = $kinv_data;
				$type = "invoice";
			}
			
			$verified = "Y";
			$source = "";
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("document_uuid", $document_uuid);
			$stmt->bindParam("parent_document_uuid", $template->uuid);
			$stmt->bindParam("document_name", $subject);
			$stmt->bindParam("document_date", $document_date);
			$stmt->bindParam("document_filename", $document_filename);
			$stmt->bindParam("document_extension", $document_extension);
			$stmt->bindParam("description", $description);
			$stmt->bindParam("description_html", $description_html);
			$stmt->bindParam("source", $source);
			$stmt->bindParam("type", $type);
			$stmt->bindParam("verified", $verified);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			$new_id = $db->lastInsertId();
			
			trackDocument("insert", $new_id);
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
	
		//attach to case
		$cd_uuid = uniqid("JK");
		$attribute = "letter";
		$sql = "INSERT INTO `cse_case_document`
		( `case_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $cd_uuid . "','" . $case_uuid . "','" . $document_uuid . "', '" . $attribute . "', '" . date("Y-m-d H:i:s") . "','" . $user_id . "', '" . $customer_id . "')";
		//die($sql);
		try {
			$stmt = DB::run($sql);
			
			//if invoice
			if ($kinv_data!="") {
				//if this is an update, mark original invoice document as deleted
				if ($kinvoice_document_id!="") {
					$sql = "UPDATE cse_document
					SET deleted = 'Y'
					WHERE document_id = :document_id
					AND customer_id = :customer_id";
					//echo $sql . "\r\n";
					
					$db = getConnection();
					$stmt = $db->prepare($sql);
					$stmt->bindParam("document_id", $kinvoice_document_id);
					$stmt->bindParam("customer_id", $customer_id);
					$stmt->execute();
					
					trackDocument("delete", $kinvoice_document_id, "");
				}
				$document_kinvoice_uuid = uniqid("KI", false);
					
				//attach to document
				$sql = "INSERT INTO `cse_document_kinvoice`
		(`document_kinvoice_uuid`, `document_uuid`, `kinvoice_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES
		(:document_kinvoice_uuid, :document_uuid, :kinvoice_uuid, 'main', :right_now, :user_uuid, :customer_id)";
				
				$db = getConnection();
				$stmt = $db->prepare($sql);
				$stmt->bindParam("kinvoice_uuid", $kinvoice_uuid);
				$stmt->bindParam("document_uuid", $document_uuid);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->bindParam("document_kinvoice_uuid", $document_kinvoice_uuid);
				$stmt->bindParam("right_now", $right_now);
				$stmt->bindParam("user_uuid", $user_id);
				$stmt->execute();
			}
		} catch(PDOException $e) {	
			//echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
	}
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	
	//activity
	$billing_time = 0;
	$activity_id = "";
	
	if (isset($_POST["billing_time"])) {
		$billing_time = passed_var("billing_time", "post");
	}
	if ($subject!="") {
		$subject = "<br />Subject: " . $subject;
	}
	
	//for invoicing/med index
	$cmd = "";
	$attachments = "";
	
	if ($kinv_data!="") {
		$subject = "";
		$activity_id = recordActivity("create", "Invoice [<a href='" . $destination . ".docx' target='_blank' class='white_text'>" . $kinvoice_number . "</a>] generated by " . $_SESSION['user_name'] . $subject, $kase->uuid, 0, "Invoices", 0);
		
		//convert to pdf when the invoices folder changes. c:\bat\invoices_monitor.ps1 needs to run upon startup			
		$cmd = "PowerShell.exe -ExecutionPolicy Bypass -File c:\\bat\\topdf.ps1 '" . $destination_path . ".docx'";
		$ps_file = UPLOADS_PATH.'invoices\\pdf_' . $_SESSION["user_plain_id"] . '.ps1';
		if (file_exists($ps_file)) {
			unlink($ps_file);
		}
		$fp = fopen($ps_file, 'w');
		fwrite($fp, $cmd);
		fclose($fp);
	} else {
		if ($blnNormalReturn) {
			//tweak the activity for med index
			$blnNormalReturn = ($template->document_filename!="kase_medindex.docx");
		}
		
		if ($blnNormalReturn) {
			$activity_id = recordActivity("create", "Letter [<a href='" . $destination . ".docx' class='white_text' target='_blank'>" . $template->document_filename . "</a>] generated by " . $_SESSION['user_name'] . "<br />Subject: " . $subject, $kase->uuid, 0, "Letters", $billing_time);
		} else {
			$activity_id = recordActivity("create", "Med Index Report [<a href='" . $destination . ".pdf' class='white_text' target='_blank'>Review Report</a>] generated by " . $_SESSION['user_name'], $kase->uuid, 0, "Med Index", "");
			
			$destination_folder_path = UPLOADS_PATH. $customer_id . '\\' . $case_id . '\\letters\\';
			$destination_path = $destination_folder_path . $destination;
			$destination_path = str_replace("../uploads/" . $customer_id . "/" . $case_id . "/letters/", "", $destination_path);
			
			//convert to pdf when the invoices folder changes. c:\bat\invoices_monitor.ps1 needs to run upon startup			
			$cmd = "PowerShell.exe -ExecutionPolicy Bypass -File c:\\bat\\topdf.ps1 '" . $destination_path . ".docx'";
			$ps_file = UPLOADS_PATH.'invoices\\pdf_' . $_SESSION["user_plain_id"] . '.ps1';
			if (file_exists($ps_file)) {
				unlink($ps_file);
			}
			$fp = fopen($ps_file, 'w');
			fwrite($fp, $cmd);
			fclose($fp);
			
			if (count($arrExamAttach) > 0) {
				if (file_exists($zip_path)) {
					unlink($zip_path);
				}
				
				touch($zip_path);  //<--- this line creates the file
				
				//$attachments = str_replace("../", "https://www.ikase.org/", $zip_path);
				$attachments = str_replace("../", "https://www.ikase.org/", $zip_dir) . "/med_index_" . date("Ymd") . ".zip";
				if (!create_zip($arrExamAttach,$zip_path,true)) {
					$attachments .= "\r\nError: not zipped";
				}
			} else {
				$zip_path = "No Attachments";
			}
		}
	}
	

	//Google Drive Implementation
	$accessToken = $_COOKIE['g_access_token'];
	//$saveFileName = str_replace("-", "_", $saveFileName);
	
	if(isset($accessToken) && $accessToken != 'Authorize') {
		$fileTmpNm = file_get_contents($document_filename.'.docx');
		$saveFileName = str_replace("../uploads/" . $customer_id . "/" . $case_id . "/letters/", "", $document_filename);
		$saveFileName = $saveFileName.'.docx';

		$fileIkase = checkFileExist($accessToken, "name='iKase'");
		$ikaseFolderId = $fileIkase['files'][0]['id'];
		
		if(isset($ikaseFolderId) && !empty($ikaseFolderId)){
			$ikaseParentId = $ikaseFolderId;
		}else{
			$qParam = "{\"name\": \"iKase\", \"mimeType\": \"application/vnd.google-apps.folder\"}\r\n";
			$createIkase = createDriveFolder($accessToken, $qParam);
			$ikaseParentId = $createIkase['id'];
		}
		
		$sql = "SELECT file_number, case_name FROM `cse_case`
					WHERE case_id = " . $case_id;
		
		$db = getConnection();
		$sqlKaseDet = DB::run($sql);
		$kaseDet = $sqlKaseDet->fetchObject();
		$caseFolderNm = $kaseDet->file_number.'_'.$kaseDet->case_name.'_'.$case_id;
		$caseFolderNm = str_replace(" ", "_", $caseFolderNm);
		
		$fileIkaseCaseId = checkFileExist($accessToken, "name='".$caseFolderNm."' and '".$ikaseParentId."' in parents");
		$ikaseCaseFolderId = $fileIkaseCaseId['files'][0]['id'];
		
		if(isset($ikaseCaseFolderId) && !empty($ikaseCaseFolderId)){
			$ikaseCaseParentId = $ikaseCaseFolderId;
		}else{
			$qParam = "{'name':'".$caseFolderNm."','mimeType':'application/vnd.google-apps.folder','parents':['".$ikaseParentId."']}\r\n";
			$createIkaseCase = createDriveFolder($accessToken, $qParam);
			$ikaseCaseParentId = $createIkaseCase['id'];
		}

		$fileLetterCreateId = checkFileExist($accessToken, "name='letter_create' and '".$ikaseCaseParentId."' in parents");
		$LetterCreateFolderId = $fileLetterCreateId['files'][0]['id'];
		
		if(isset($LetterCreateFolderId) && !empty($LetterCreateFolderId)){
			$ikaseLetterCaseParentId = $LetterCreateFolderId;
		}else{
			$qParam = "{'name':'letter_create','mimeType':'application/vnd.google-apps.folder','parents':['".$ikaseCaseParentId."']}\r\n";
			$createIkaseLetterCase = createDriveFolder($accessToken, $qParam);
			$ikaseLetterCaseParentId = $createIkaseLetterCase['id'];
		}
		
		uploadFileGDrive($fileTmpNm, $saveFileName, $ikaseLetterCaseParentId, $accessToken);
	}

	echo json_encode(array("success"=>$document_filename, "activity_id"=>$activity_id, "invoice_number"=>$kinvoice_number, "invoice_total"=>$invoice_total, "zip_path"=>$zip_path));
	//"cmd"=>$cmd, "total_time"=>$total_time, "pre_time"=>$pre_time, "header_time"=>$header_time, "create_time"=>$create_time,  "attachments"=>$attachments, "arrFinal"=>$arrExamAttach
}

function createLetterByPartieType() {
	//Letters to all Defense Attorneys, Carriers, Applicants on open cases

	session_write_close();
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$header_start_time = $time;
	
	$template = passed_var("table_id", "post");
	$partie_type =  passed_var("partie_type", "post");
	$customer_id =  $_SESSION['user_customer_id'];
	
	$arrAcceptableTypes = array("defense", "carrier", "applicant", "plaintiff", "defendant");
	if (!in_array($partie_type, $arrAcceptableTypes)) {
		die("not acceptable");
	}
	$user_id =  $_SESSION['user_id'];
	
	//get the template from the id
	$sql = "SELECT document_id id, document_uuid uuid, document_filename, source 
	FROM cse_document 
	WHERE document_id = :template
	AND customer_id = :customer_id";
	
	try {
		$db = getConnection();
		
		$stmt = $db->prepare($sql);
		$stmt->bindParam("template", $template);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$template = $stmt->fetchObject();
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
	$blnFax = false;	
	$template_name = $template->document_filename;
	if (strpos(strtolower($template_name), "fax") === 0) {
		$blnFax = true;
	}
	
	$customer = getCustomerInfo();
	if ($customer->eams_no!="") {
		$customer_eams = getEamsRepByNumber($customer->eams_no);
	}
	
	//get all the parties
	if ($partie_type=="applicant") {
		$sql = "SELECT pers.person_id partie_id, pers.full_name, cases.case_id, cases.employer, adj_numbers 
		FROM ";
		//$sql .= "`cse_person`";
		
		if (($_SESSION['user_customer_id']==1033)) {
			$sql_encrypt = SQL_PERSONX;
			$sql_encrypt = str_replace("SET utf8)", "SET utf8) COLLATE utf8_general_ci", $sql_encrypt);
			$sql .= "(" . $sql_encrypt . ")";
		} else {
			$sql .= "`cse_person`";
		}
		$sql .= " pers
		INNER JOIN cse_case_person ccp
		ON pers.person_uuid = ccp.person_uuid AND ccp.deleted = 'N'
		INNER JOIN (
			SELECT kase.case_id, kase.case_uuid, IFNULL(employer.company_name, '') employer  
			FROM cse_case kase
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (kase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			WHERE kase.`case_status` NOT LIKE '%Close%'
			AND kase.`case_status`!= 'Dropped'
			AND kase.customer_id = :customer_id
		) cases
		ON ccp.case_uuid = cases.case_uuid
		INNER JOIN (
			SELECT ccase.case_uuid, GROUP_CONCAT(inj.adj_number) adj_numbers
			FROM cse_injury inj
			INNER JOIN cse_case_injury cci
			ON inj.injury_uuid = cci.injury_uuid
			INNER JOIN cse_case ccase
			ON cci.case_uuid = ccase.case_uuid
			WHERE 1
			AND ccase.customer_id = :customer_id
			AND cci.deleted = 'N'
			AND inj.deleted = 'N'
			GROUP BY ccase.case_uuid
		) adjs
		ON ccp.case_uuid = adjs.case_uuid
		WHERE pers.customer_id = :customer_id
		AND pers.deleted = 'N'
		ORDER BY pers.first_name, pers.last_name";
	} else {
		$sql = "SELECT corp.corporation_id partie_id, app.full_name, cases.case_id, cases.employer, adj_numbers 
		FROM cse_corporation corp
		INNER JOIN cse_case_corporation ccc
		ON corp.corporation_uuid = ccc.corporation_uuid AND ccc.deleted = 'N'
		INNER JOIN (
			SELECT kase.case_id, kase.case_uuid, IFNULL(employer.company_name, '') employer  
			FROM cse_case kase
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (kase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			WHERE kase.`case_status` NOT LIKE '%Close%'
			AND kase.`case_status`!= 'Dropped'
			AND kase.customer_id = :customer_id
		) cases
		ON ccc.case_uuid = cases.case_uuid
		INNER JOIN (
			SELECT ccase.case_uuid, GROUP_CONCAT(inj.adj_number) adj_numbers
			FROM cse_injury inj
			INNER JOIN cse_case_injury cci
			ON inj.injury_uuid = cci.injury_uuid
			INNER JOIN cse_case ccase
			ON cci.case_uuid = ccase.case_uuid
			WHERE 1
			AND ccase.customer_id = :customer_id
			AND cci.deleted = 'N'
			AND inj.deleted = 'N'
			GROUP BY ccase.case_uuid
		) adjs
		ON ccc.case_uuid = adjs.case_uuid";
		
		$sql .= " 
			LEFT OUTER JOIN cse_case_person ccapp 
			ON (cases.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N')
			LEFT OUTER JOIN ";
		if (($_SESSION['user_customer_id']==1033)) { 
			$sql .= "(" . SQL_PERSONX . ")";
		} else {
			$sql .= "cse_person";
		}
		$sql .= " app ON ccapp.person_uuid = app.person_uuid

		WHERE corp.`type` = :partie_type
		AND corp.customer_id = :customer_id
		AND corp.deleted = 'N'
		ORDER BY corp.company_name, corp.full_name";
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("partie_type", $partie_type);
		$stmt->execute();
		$parties = $stmt->fetchAll(PDO::FETCH_OBJ);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
        	echo json_encode($error);
	}
	
	//get letterhead
	$sql_letterhead = "SELECT `setting_value` `value`
	FROM  `cse_setting` 
	WHERE `cse_setting`.customer_id = :customer_id
	AND `cse_setting`.setting = 'letterhead'
	AND `cse_setting`.deleted = 'N'
	AND `cse_setting`.`setting_value` != ''
	ORDER BY setting_id DESC";
	try {
		$db = getConnection();
		
		$stmt = $db->prepare($sql_letterhead);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$letterhead = $stmt->fetchObject();
		
		if ($template->source!="no_letterhead" && $template->source!="clientname_letterhead") {
			if(!is_object($letterhead)) {
				die(json_encode(array("error"=>"no letterhead")));
			}
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
	
	//get signature
	$sql_lettersignature = "SELECT `setting_value` `value`
	FROM  `cse_setting` 
	WHERE `cse_setting`.customer_id = :customer_id
	AND `cse_setting`.setting = 'lettersignature'
	AND `cse_setting`.deleted = 'N'";
	
	$signature_img = "";
	try {
		$db = getConnection();
		
		$stmt = $db->prepare($sql_lettersignature);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$lettersignature = $stmt->fetchObject();
		
		if (is_object($lettersignature)) {
			$signature_img = $lettersignature->value;
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
	}
	
	$blnSignatureExists = false;
	if ($signature_img!="") {
			$signature_img = UPLOADS_PATH. $customer_id . DC . $signature_img;
			if (file_exists($signature_img)) {
				$blnSignatureExists = true;
			}
	}
	$arrLetterStack = array();
	$arrCorporationIDs = array();
	$first_letter = "";
	//die(print_r($parties));
	foreach($parties as $partie) {
		$corporation_id = $partie->partie_id;
		$case_id = $partie->case_id;
		$arrCorporationIDs[] = $corporation_id;
		$arrReplace = array();
		if ($partie_type=="applicant") {
			if ($customer_id==1033) {
				$letter_partie = getPersonXInfo($corporation_id);
			} else {
				$letter_partie = getPersonInfo($corporation_id);
			}
		} else {
			$letter_partie = getCorporationInfo($corporation_id);
		}
		
		//get kase info for app
		//$kase = getKaseInfo($case_id);
		
		//die(print_r($kase));
		/*
		//get adj_numbers
		$sql = "SELECT ccase.case_id, GROUP_CONCAT(inj.adj_number) adj_numbers
		FROM cse_injury inj
		INNER JOIN cse_case_injury cci
		ON inj.injury_uuid = cci.injury_uuid
		INNER JOIN cse_case ccase
		ON cci.case_uuid = ccase.case_uuid
		WHERE ccase.case_id = :case_id
		AND ccase.customer_id = :customer_id
		AND cci.deleted = 'N'
		AND inj.deleted = 'N'
		GROUP BY ccase.case_id";
		
		try {
			$db = getConnection();
			
			$stmt = $db->prepare($sql_lettersignature);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->bindParam("case_id", $case_id);
			$stmt->execute();
			$adj_numbers = $stmt->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
		}
		$arrCaseNumbers = array();
		foreach($adj_numbers as $adj_number) {
			$arrCaseNumbers[] =  $adj_number;
		}
		*/
		$letter_partie_salutation = "Sir/Madam";
		if (count($letter_partie) > 0) {	
			if (trim($letter_partie->full_name)!="") {
				$letter_partie_salutation = $letter_partie->full_name;
				if ($letter_partie->salutation!="") {
					$letter_partie_salutation = 	$letter_partie->salutation . " " . $letter_partie_salutation;
				}
			}
		}
		
		$arrParties = array();
		$arrPartiesBlock = array();
		$arrPartiesReturn = array();
		$arrPartiesNames = array();
		$arrPartiesFaxes = array();
		$arrPartiesType = array();
		$parties = "";	
		
		//foreach($arrLetterParties as $letter_partie) {
			$arrPartieInfo = array();
			$arrPartieName = array();
			//defense or carrier
			if ($letter_partie->type == "carrier" || $letter_partie->type == "defense" || $letter_partie->type == "prior_attorney") {
				$letter_name = getAdhocsInfo("", $letter_partie->corporation_id, "letter_name");
				if (count($letter_name) > 0) {
					$letter_partie->company_name = $letter_name[0]->adhoc_value;
				}
				if ($letter_partie->type == "carrier") {
					$partie_claim = getAdhocsInfo($kase->id, $letter_partie->corporation_id, "claim_number");
					if (count($partie_claim) > 0 && !$blnClaimsFilled) {
						$arrClaims[] = $partie_claim[0]->adhoc_value;
					}
				}
			}
			
			//put the block together
			if (trim($letter_partie->company_name)!="") {
				$arrPartieInfo[] = $letter_partie->company_name;
				$arrPartieName[] = $letter_partie->company_name;
			}
			if (trim($letter_partie->full_name)!="") {
				$arrPartieInfo[] = $letter_partie->full_name;
				$arrPartieName[] = $letter_partie->full_name;
			}
			if (trim(str_replace(",", "", $letter_partie->full_address)) == "") {
				$letter_partie->full_address = "";
			}
			if ($letter_partie->full_address!="") {
				$arrPartieInfo[] = $letter_partie->full_address;
			}
			
			$arrParties[] = implode(", ", $arrPartieInfo);	
			
			$block = "";
			$block .= $letter_partie->company_name . "\\n";
			if (trim($letter_partie->full_name)!="" && trim($letter_partie->full_name)!=trim($letter_partie->company_name)) {
				if ($letter_partie->salutation!="") {
					//break it up in case it contains the name
					$arrSalutation =  explode(" ", $letter_partie->salutation);
					foreach($arrSalutation as $sindex=>$thesalut) {
						if ($thesalut=="") {
							unset($arrSalutation[$sindex]);
							continue;
						}
						if (strpos($letter_partie->full_name, $thesalut) !== false) {
							unset($arrSalutation[$sindex]);
						}
					}
					$letter_partie->salutation = trim(implode(" ", $arrSalutation));
					if ($letter_partie->salutation!="") {
						//now add it to the block
						$block .= $letter_partie->salutation . " ";
					}
				}
				$block .= $letter_partie->full_name . "\\n";
			}
			$block .= $letter_partie->street;
			if ($letter_partie->suite!="" && $letter_partie->suite!=$letter_partie->street) {
				$block .= "\\n" . $letter_partie->suite;
			}
			if ($letter_partie->city!="") {
				$block .= "\\n" . $letter_partie->city . ", " . $letter_partie->state . " " . $letter_partie->zip;
			}
			if ($blnFax){
				$block .= "\\nFax: " . $letter_partie->fax;
			} else {
				//$block .= "\\n \\n";
			}
			$block .= "\\n \\n";
			//$block .= "\\n";
			$arrPartiesBlock[] = $block;
			$arrPartiesType[] = $letter_partie->type;
			$arrPartiesNames[] = implode(", ", $arrPartieName);	//$letter_partie->company_name;
			$arrPartiesFaxes[] = $letter_partie->fax;
			
			$arrBlock = explode("\\n", $block);
			$new_block = array(
				implode(",", $arrBlock)
			);
			
			$arrPartiesReturn[] = $new_block;
		//}
		
		$parties = implode("\\n", $arrParties);
		if (strpos($template->document_filename, "templates/") === false) {
			$prefix = "/templates";
		}
		
		$destination = $template->document_filename;
		$destination = str_replace("templates/", "", $destination);
		$destination = str_replace(".docx", "", $destination);
		$destination .= "_" . $corporation_id;
		$destination_folder = '../uploads/' . $customer_id . '/letters/';
		if (!is_dir($destination_folder)) {
			mkdir($destination_folder, 0755, true);
		}
		$destination = $destination_folder . $destination;
		
		$final_destination = $destination;
		
		$arrReplace['CLIENTNAME'] = $partie->full_name;
		if ($customer_id==1105) {
			$arrReplace['EMPLFIRM1'] = strtoupper($partie->employer);
		} else {
			$arrReplace['EMPLFIRM1'] = $partie->employer;
		}
		
		$arrReplace['ALLCASENUMBER'] = $partie->adj_numbers;
		
		setCityStreet($letter_partie);
		$arrReplace['ANYSALUT1'] = $letter_partie_salutation;
		$arrReplace['ANYNAME1'] = $letter_partie->full_name;
		$arrReplace['ANYFIRM1'] = $letter_partie->company_name;
		$arrReplace['ANYADD11'] = $letter_partie->street;
		$arrReplace['ANYADD12'] = $letter_partie->suite;
		$arrReplace['ANYADD21'] = $letter_partie->suite;
		$arrReplace['ANYFAX1'] = $letter_partie->fax;
		$arrReplace['ANYPHONE1'] = $letter_partie->phone;
		$arrReplace['ANYCITYSTATEZIP1'] = $letter_partie->city . ", " . $letter_partie->state . " " . $letter_partie->zip;
		
		if (isset($customer_eams)) {
			$arrReplace['EAMSNAME'] = $customer_eams->firm_name;
			$arrReplace['EAMSSTREET1'] = $customer_eams->street_1;
			$arrReplace['EAMSSTREET2'] = $customer_eams->street_2;
			$arrReplace['EAMSCITY'] = $customer_eams->city;
			$arrReplace['EAMSSTATE'] = $customer_eams->state;
			$arrReplace['EAMSZIP'] = $customer_eams->zip_code;
			$arrReplace['EAMSPHONE'] = $customer_eams->phone;
		}
		$customer_full_name = $customer->cus_name_first;
		if ($customer->cus_name_middle!="") {
			$customer_full_name .= " " . $customer->cus_name_middle;
		}
		$customer_full_name .= " " . $customer->cus_name_last;
		$arrReplace['FIRMATTY'] = $customer_full_name;
		$arrReplace['FIRMNAME'] = str_replace("&", "&amp;", $_SESSION['user_customer_name']);
		$arrReplace['FIRMNUMBER'] = $customer->eams_no;
		$arrReplace['UAN'] = $customer->cus_uan;
		$arrReplace['TAXID'] = $customer->cus_fedtax_id;
		
		$arrReplace['FIRMADD1'] = $customer->cus_street;
		$arrReplace['FIRMADD2'] = "";
		$arrReplace['FIRMATTYFNAME'] = $customer->cus_name_first;
		$arrReplace['FIRMATTYLNAME'] = $customer->cus_name_last;
		$arrReplace['FIRMATTYMIDDLEINITIAL'] = $customer->cus_name_middle;
		$arrReplace['FIRMCITY'] = $customer->cus_city;
		$arrReplace['FIRMSTATE'] = $customer->cus_state;
		$arrReplace['FIRMZIP'] = $customer->cus_zip;
		$arrReplace['FIRMTEL'] = $customer->cus_phone;
		$arrReplace['FIRMEMAIL'] = $customer->cus_email;
		$arrReplace['BARNUMBER'] = $customer->cus_barnumber;
		$arrReplace['BARNO'] = $customer->cus_barnumber;
		$arrReplace['FIRMFAX'] = $customer->cus_fax; 
		$arrReplace['ADDTELFIRMNAME'] = $customer->cus_street . ", " . $customer->cus_city . ", " . $customer->cus_state . " " . $customer->cus_zip . ", " . $customer->cus_phone;
		$arrReplace['CUSCOUNTY'] = $customer->cus_county;
		
		$arrReplace['DATE'] = date('F j, Y');
		//if spanish
		if (strpos(strtolower($template_name), "spanish")!==false) {
			$month = getSpanishMonth(date('F'));
			
			$arrReplace['DATE'] = $month . " " . date('j, Y');
		}
		
		$arrReplace['LETTERMONTH'] = date("F");
		$arrReplace['LETTERDAY'] = date("j");
		$arrReplace['LETTERYEAR'] = date("Y");
		
		$arrReplace['LETTER'] = $letter;
		$arrReplace['SIGNATURE'] = $_SESSION['user_name'];
		
		$arrReplace['letterhead'] = "";
		
		foreach($arrReplace as $replace_index=>$replace) {
			if (strpos($replace, "&amp;")===false && strpos($replace, "&")!==false) {
				$replace = str_replace("&", "&amp;", $replace);
				//die($replace_index . " = " . $replace);
				$arrReplace[$replace_index] = $replace;
			}
		}
		//die(print_r($arrReplace));
		$variables = $arrReplace;
		//die('../uploads/' . $customer_id . $prefix . '/' . $template->document_filename);
		$docx = new CreateDocxFromTemplate('../uploads/' . $customer_id . $prefix . '/' . $template->document_filename);
		
		if ($template->source!="no_letterhead" && $template->source!="clientname_letterhead") {
			//if ($customer_id!=1042) {
				$docx ->importHeadersAndFooters('../uploads/' . $customer_id . "/" . $letterhead->value);
			/*
			//turned off per new letterhead 5/31/2018
			} else {			
				//patel has 2-part header
				include_once("patel_header.php");
				$docx ->importHeadersAndFooters('../uploads/' . $customer_id . "/" . $letterhead->value, 'footer');
			}
			*/
		}
		
		$options = array('parseLineBreaks' =>true);
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$pre_time = round(($finish_time - $header_start_time), 4);
		
		$docx->replaceVariableByText($variables, $options);
		
		if ($template->source=="clientname_letterhead") {
			$headerText = new WordFragment($docx, 'defaultHeader');
			$textOptions = array(
			'bold' => true
			);
			$headerText->addText("CLIENT'S NAME:" . $arrReplace["CLIENTNAME"], $textOptions);
			$valuesTable = array(
				array(
					array('value' =>$headerText, 'vAlign' => 'left')
				),
			);
			$widthTableCols = array(
				7500,
				700,
				500
			);
			$paramsTable = array(
				'border' => 'nil',
				'columnWidths' => $widthTableCols,
			);
			$headerTable = new WordFragment($docx, 'defaultHeader');
			$headerTable->addTable($valuesTable, $paramsTable);
			
			//add some text to the body of the document
			$docx->addHeader(array('default' => $headerTable));
		}
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$header_time = round(($finish_time - $header_start_time), 4);
		
		if ($blnSignatureExists) {
			$docx->replacePlaceholderImage('SIGNATURE_IMAGE', $signature_img);
		}
		$docx->createDocx($destination); 
		
		$arrLetterStack[] = $destination . ".docx";
		
		if ($first_document == "") {
			$first_document = $destination . ".docx";
		}
	}
	$destination = $template->document_filename;
	$destination = str_replace("templates/", "", $destination);
	$destination = str_replace(".docx", "", $destination);
	$destination .= "_" . $partie_type;
	
	$destination = $destination_folder . $destination . ".docx";
	$merge = new MultiMerge();
	$options = array(
		"mergeType" => 0,
		"enforceSectionPageBreak" => true
	);
	if (count($arrLetterStack)==0) {
		echo json_encode(array("success"=>false, "file"=>""));
		die();
	}
	$merge->mergeDocx($first_document, $arrLetterStack, $destination, $options);
	//echo '<a href="' . $destination . '">' . $destination . '</a>';
	echo json_encode(array("success"=>true, "file"=>$destination));
}
function addLetter() {
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$case_uuid = "";
	$send_document_id = "";
	$attachments = "";
	$blnAttachments = true;
	//default attribute
	$table_attribute = "main";
	foreach($_POST as $fieldname=>$value) {
		if ($fieldname!="letterInput") {
			$value = passed_var($fieldname, "post");
		} else {
			//special case
			//remove script
			$value = @processHTML($_POST["letterInput"]);
		}
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="table_attribute") {
			$table_attribute = $value;
			continue;
		}
		if ($fieldname=="billing_time") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		if ($fieldname=="case_uuid") {
			$case_uuid = $value;
			continue;
		}
		if ($fieldname=="case_id") {
			$case_id = $value;
			continue;
		}
		if ($fieldname=="partie_id") {
			$partie_id = $value;
			continue;
		}
		if ($fieldname=="case_id" || $fieldname=="table_id" || $fieldname=="focusme") {
			continue;
		}
		if (strpos($fieldname, "_uuid") > -1) {
			continue;
		}
		if ($fieldname=="send_document_id") {
			$send_document_id = $value;
			if ($send_document_id!="") {
				$send_document = getDocumentInfo($send_document_id);
				$attachments = $send_document->document_filename;
				
				$arrFields[] = "`attachments`";
				$arrSet[] = "'" . $attachments . "'";
				$blnAttachments = false;
			}
			continue;
		}
		if ($fieldname=="attachments") {
			if (!$blnAttachments) {
				continue;
			}
			$attachments = $value;
			//continue;
		}
		$arrFields[] = "`" . $fieldname . "`";
		if ($fieldname=="dateandtime" || $fieldname=="start_date" || $fieldname=="end_date") {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			}
		}
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	
	$table_uuid = uniqid("KS", false);
	$sql = "INSERT INTO `cse_" . $table_name ."` (`customer_id`, `entered_by`, `" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $_SESSION['user_customer_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $table_uuid . "', " . implode(",", $arrSet) . ")";
	//die($sql);
	try { 
		
		DB::run($sql);
	$new_id = DB::lastInsertId();
		
		//let's get send document details if any
		if ($send_document_id != "") {
			$last_updated_date = date("Y-m-d H:i:s");
			$message_document_uuid = uniqid("TD", false);
			$sql = "INSERT INTO cse_message_document (`message_document_uuid`, `message_uuid`, `document_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $message_document_uuid  ."', '" . $table_uuid . "', '" . $send_document->document_uuid . "', 'attach', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			$stmt = DB::run($sql);
		}
		
		//attach attachments
		if ($attachments!="") {
			$arrAttachments = explode("|", $attachments);
			foreach ($arrAttachments as $attachment) {
				$document_name = $attachment;
				$document_name = explode("/", $document_name);
				$document_name = $document_name[count($document_name) - 1];
				$document_date = date("Y-m-d H:i:s");
				$document_extension = explode(".", $document_name);
				$document_extension = $document_extension[count($document_extension) - 1];
				$customer_id = $_SESSION["user_customer_id"];
				$description = "note attachment";
				$description_html = "note attachment";
				$type = "note_attachment";
				$verified = "Y";
				
				//attachment is a document
				$document_uuid = uniqid("KS");
				$sql = "INSERT INTO cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, description, description_html, type, verified, customer_id) 
			VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :description, :description_html, :type, :verified, :customer_id)";
				
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("document_uuid", $document_uuid);
				$stmt->bindParam("parent_document_uuid", $document_uuid);
				$stmt->bindParam("document_name", $document_name);
				$stmt->bindParam("document_date", $document_date);
				$stmt->bindParam("document_filename", $document_name);
				$stmt->bindParam("document_extension", $document_extension);
				$stmt->bindParam("description", $description);
				$stmt->bindParam("description_html", $description_html);
				$stmt->bindParam("type", $type);
				$stmt->bindParam("verified", $verified);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
				$new_id = $db->lastInsertId();

				//die(print_r($newEmployee));
				trackDocument("insert", $new_id);
				
				$message_document_uuid = uniqid("TD", false);
				$last_updated_date = date("Y-m-d H:i:s");
				$sql = "INSERT INTO cse_letter_document (`letter_document_uuid`, `letter_uuid`, `document_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $message_document_uuid  ."', '" . $table_uuid . "', '" . $document_uuid . "', 'attach', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				
				$stmt = DB::run($sql);
			}
		}
		
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid)); 
		
		if ($case_uuid=="" && $case_id!="") {
			$kase = getKaseInfo($case_id);
			$case_uuid = $kase->uuid;
		}
		$case_table_uuid = uniqid("KA", false);
		//attribute
		if ($table_attribute=="") {
			//default
			$table_attribute = "main";
		}
		
		$last_updated_date = date("Y-m-d H:i:s");
		//now we have to attach the note to the case 
		$sql = "INSERT INTO cse_case_" . $table_name . " (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . $table_attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		
		//die($sql);
		try {
			$stmt = DB::run($sql);
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		
		//attachments
		//attach attachments
		if ($attachments!="") {
			$arrAttachments = explode("|", $attachments);
			foreach ($arrAttachments as $attachment) {
				$document_name = $attachment;
				
				$document_name = explode("/", $document_name);
				$document_name = $document_name[count($document_name) - 1];
				$document_date = date("Y-m-d H:i:s");
				$document_extension = explode(".", $document_name);
				$document_extension = $document_extension[count($document_extension) - 1];
				$customer_id = $_SESSION["user_customer_id"];
				$description = "note attachment";
				$description_html = "note attachment";
				$type = "note attachment";
				$verified = "Y";
				
				//attachment is a document
				$document_uuid = uniqid("KS");
				$sql = "INSERT INTO cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, description, description_html, type, verified, customer_id) 
			VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :description, :description_html, :type, :verified, :customer_id)";
				
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("document_uuid", $document_uuid);
				$stmt->bindParam("parent_document_uuid", $document_uuid);
				$stmt->bindParam("document_name", $document_name);
				$stmt->bindParam("document_date", $document_date);
				$stmt->bindParam("document_filename", $document_name);
				$stmt->bindParam("document_extension", $document_extension);
				$stmt->bindParam("description", $description);
				$stmt->bindParam("description_html", $description_html);
				$stmt->bindParam("type", $type);
				$stmt->bindParam("verified", $verified);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
				$new_id = $db->lastInsertId();

				//die(print_r($newEmployee));
				trackDocument("insert", $new_id);
				
				$message_document_uuid = uniqid("TD", false);
				$last_updated_date = date("Y-m-d H:i:s");
				$sql = "INSERT INTO cse_letter_document (`letter_document_uuid`, `letter_uuid`, `document_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $message_document_uuid  ."', '" . $table_uuid . "', '" . $document_uuid . "', 'attach', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				
				$stmt = DB::run($sql);
			}
		}
		
		trackLetter("insert", $new_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}

function updateLetter() {
	$arrSet = array();
	$where_clause = "";
	$table_name = "";
	$table_id = "";
	$arrAttachments = array();
	foreach($_POST as $fieldname=>$value) {
		if ($fieldname!="letterInput") {
			$value = passed_var($fieldname, "post");
		} else {
			//special case
			//remove script
			$value = @processHTML($_POST["letterInput"]);
		}
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="table_attribute") {
			$table_attribute = $value;
			continue;
		}
		if ($fieldname=="case_id" || $fieldname=="focusme") {
			continue;
		}
		if ($fieldname=="billing_time") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		if ($fieldname=="partie_id") {
			continue;
		}
		//no uuids  || $fieldname=="case_uuid"
		if (strpos($fieldname, "_uuid") > -1) {
			continue;
		}
		if ($fieldname=="send_document_id") {
			$send_document_id = $value;
			if ($send_document_id!="") {
				$arrDocs = explode("|", $send_document_id);
				foreach($arrDocs as $send_document_id) {
					$send_document = getDocumentInfo($send_document_id);
					$arrAttachments[] = $send_document->document_filename;
				}
			}
			continue;
		}
		if ($fieldname=="attachments") {
			if ($value!="") {
				$attachments = $value;
				$arrAttachments[] = $value;
			}
			continue;
		}
		if ($fieldname=="dateandtime" || $fieldname=="start_date" || $fieldname=="end_date" || $fieldname=="event_dateandtime") {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			}
		}
		if ($fieldname=="table_id" || $fieldname=="id") {
			$table_id = $value;
			$where_clause = " = " . $value;
		} else {
			$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
		}
	}
	if (count($arrAttachments) > 0) {
		$arrSet[] = "`attachments` = '" . implode("|", $arrAttachments) . "'";
		//we're going to need a table_uuid for attaching
		$note = getLetterInfo($table_id);
		$table_uuid = $note->uuid;
	}
	$where_clause = "`" . $table_name . "_id`" . $where_clause;
	$sql = "
	UPDATE `cse_" . $table_name . "`
	SET " . implode(", ", $arrSet) . "
	WHERE " . $where_clause;
	//die( $sql . "\r\n");
	try {
		$stmt = DB::run($sql);
		
		echo json_encode(array("success"=>$table_id)); 
		
		foreach ($arrAttachments as $attachment) {
			$document_name = $attachment;
			//first check if this document is _already_ attached
			$sql = "SELECT COUNT(doc.document_id) thecount
			FROM `cse_document` doc
			INNER JOIN `cse_letter_document` cnd
			ON doc.document_uuid = cnd.document_uuid
			WHERE doc.document_name = '" . $document_name . "'";
			$stmt = DB::run($sql);
			$document_count = $stmt->fetchObject();
			
			if ($document_count->thecount==0) {
				$document_name = explode("/", $document_name);
				$document_name = $document_name[count($document_name) - 1];
				$document_date = date("Y-m-d H:i:s");
				$document_extension = explode(".", $document_name);
				$document_extension = $document_extension[count($document_extension) - 1];
				$customer_id = $_SESSION["user_customer_id"];
				$description = "note attachment";
				$description_html = "note attachment";
				$type = "note_attachment";
				$verified = "Y";
				
				//attachment is a document
				$document_uuid = uniqid("KS");
				$sql = "INSERT INTO cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, description, description_html, type, verified, customer_id) 
			VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :description, :description_html, :type, :verified, :customer_id)";
				
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("document_uuid", $document_uuid);
				$stmt->bindParam("parent_document_uuid", $document_uuid);
				$stmt->bindParam("document_name", $document_name);
				$stmt->bindParam("document_date", $document_date);
				$stmt->bindParam("document_filename", $document_name);
				$stmt->bindParam("document_extension", $document_extension);
				$stmt->bindParam("description", $description);
				$stmt->bindParam("description_html", $description_html);
				$stmt->bindParam("type", $type);
				$stmt->bindParam("verified", $verified);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
				$new_id = $db->lastInsertId();

				//die(print_r($newEmployee));
				trackDocument("insert", $new_id);
				
				$message_document_uuid = uniqid("TD", false);
				$last_updated_date = date("Y-m-d H:i:s");
				$sql = "INSERT INTO cse_letter_document (`letter_document_uuid`, `letter_uuid`, `document_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $message_document_uuid  ."', '" . $table_uuid . "', '" . $document_uuid . "', 'attach', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				
				$stmt = DB::run($sql);
			}
		}
		//track now
		//trackLetter("update", $table_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function trackLetter($operation, $letter_id) {
	$sql = "INSERT INTO cse_letter_track (`user_uuid`, `user_logon`, `operation`, `letter_id`, `letter_uuid`, `type`, `note`, `title`, `subject`, `entered_by`, `attachments`, `status`, `dateandtime`, `callback_date`, `verified`, `deleted`, `customer_id`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `letter_id`, `letter_uuid`, `type`, `note`, `title`, `subject`, `entered_by`, `attachments`, `status`, `dateandtime`, `callback_date`, `verified`, `deleted`, `customer_id`
	FROM cse_letter
	WHERE 1
	AND letter_id = " . $letter_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		$stmt = DB::run($sql);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
