<?php
if ($_SERVER['REMOTE_ADDR']=='173.55.229.70') {
	error_reporting(E_ALL);
	ini_set('display_errors', '1');	
}
ini_set('max_execution_time', 600); //300 seconds = 5 minutes

include_once("functions.php");

if (isset($_POST["image_path"])) {
	$image_path = $_POST["image_path"];
	$batchscan_id = $_POST["batchscan_id"];
	$page = $_POST["page"];
	$pages = $_POST["pages"];
	$customer_id = $_POST["customer_id"];
	$uploaded = $_POST["uploaded"];
	//timestamp for file names
	$timestamp = $_POST["timestamp"];
} else {
	$image_path = $_GET["image_path"];
	$batchscan_id = $_GET["batchscan_id"];
	$page = $_GET["page"];
	$pages = $_GET["pages"];
	$customer_id = $_GET["customer_id"];
	$uploaded = $_GET["uploaded"];
	//timestamp for file names
	$timestamp = $_GET["timestamp"];
}
if (!is_numeric($batchscan_id) || !is_numeric($page)) {
	die();
}

$msg = "start multithread " . $image_path . " -- " . $batchscan_id;
include("../api/cls_logging.php");
include_once("header.php");

$arrAttemptList[] = $page;
//get attempted
$sql = "SELECT `attempted` FROM cse_batchscan
WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;

$db = getConnection();

try {
	$stmt = $db->prepare($sql);  
	$stmt->execute();
	$attempted = $stmt->fetchObject();
	
	//break up into array
	if ($attempted->attempted!="") {
		$arrAttempt = explode("|", $attempted->attempted);
	} else {
		$arrAttempt = array();
	}
	if (count($arrAttempt) > 0) {
		//add to array
		$arrAttemptList = array_merge($arrAttemptList, $arrAttempt);
		$arrAttemptList = array_filter($arrAttemptList, "noEmpty");
		sort($arrAttemptList);
		$arrAttemptList = array_unique($arrAttemptList);
	}
	
	//update attempted
	$sql = "UPDATE cse_batchscan
	SET attempted = '" . implode("|", $arrAttemptList) . "'
	WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
	
	$stmt = $db->prepare($sql);
	$stmt->execute();


	//$filename = "/home/cstmwb/public_html/autho/web/uploads/" . $customer_id . "/" . $timestamp . "/" . $image_path;
	$filename = $_SERVER['DOCUMENT_ROOT'] . '\\uploads\\' . $customer_id . '\\imports\\' . $timestamp . '\\' . $image_path;
	
	/**************************************************************************************************/
		//MAIN
		/**************************************************************************************************/
		
	//If you create a new font include file replace char_inc_6.php with your own
	$conf['font_file']					= 'char_inc_highway80.php';
	
	
	//The default output format. You can chose from xml,html,plain,template.
	$conf['default_output_format']		= 'html';
	
	//You shold probably not need to change thees
	$conf['word_lines_min_dispersion']	= 0;
	$conf['letters_min_dispersion']		= 0;
	
	
	$intFound = 0;
	
	//*******************************************************
	//This is the main function. Format of the output array is $retmas[$line_number][$letter_number][$type]
	//where $type is 0 for digit and 1 for relative closeness
	
	$crit = "IKASESCAN";
	
	$arrSeparators = array();
	
	$lines = number_lines($filename,$conf['font_file']);	
	$time_start = getmicrotime();
	
	$msg = "parse started " . $filename . " --- " . $batchscan_id;
	$log->lwrite($msg);
	//echo "parse "  . $filename . "\r\n";
	//$retmas = array();
	$retmas = parse_image($filename,$conf['font_file'], $customer_id, $batchscan_id);
	//die(print_r($retmas));
	$msg = "parse completed " . $filename . " --- " . $batchscan_id;
	$log->lwrite($msg);
	
	foreach($retmas as $line_number=>$line) {
		//we're looking for a close match, not exact		
		$arrMatches = array();
		$not_found = 0;
	
		if (count($line) < 5) {
			continue;
		}
		//echo "line_number:" . $line_number. " - " . count($line) . "\r\n";
		//echo "looking for:" . $crit . "\r\n";
		for ($int=0;$int<count($line);$int++) {
			$val = $line[$int][0];
			
			//is the val in the criterion
			if (strpos($crit, $val) > 0) {
				$arrMatches[] = $val;
			}
			if ($val!=substr($crit, $int, 1)) {
				$not_found++;
			}
		}
		//echo "intFound = " . strlen($crit) . " - " . $not_found . "\r\n";
		$intFound = strlen($crit) - $not_found;
		//die(print_r($arrMatches));
		if ($intFound >= 7) {
			$arrSeparators[] = $page;
			//get out of the list
			//echo $filename . " determined to be a separator (" . $intFound . ")\r\n";
			//die(print_r($arrSeparators));
			break;
		}
		if (count($arrMatches) < 5) {
			continue;
		}
		
		$no_match = 0;
		//first instance of first match in criterion
		$start = strpos($crit, $arrMatches[0]);
		//try something else
		for ($int=0;$int<count($arrMatches);$int++) {
			$val = $arrMatches[$int];
			if ($val!=substr($crit, ($int + $start), 1)) {
				$no_match++;
			}
		}
		//echo strlen($crit)." -=- " . $no_match . "\r\n";
		$matchFound = strlen($crit) - $no_match;
		if ($matchFound >= 6) {
			//echo $filename . " is a separator (" . $intFound . ")\r\n";
			$arrSeparators[] = $page;
			//get out of the list
			break;
		}
	}
	
	$db = null;
	$db = getConnection();
	
	if (count($arrSeparators)>0) {
		//get separators
		$sql = "SELECT separators FROM cse_batchscan
		WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
		
		$msg = $sql;
		$log->lwrite($msg);
	
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$batchscan = $stmt->fetchObject();
		
		$msg = "sep: " . strlen($batchscan->separators) . " -> " . $batchscan->separators . " -- " . $batchscan_id;
		$log->lwrite($msg);
		//break up into array
		if ($batchscan->separators!="") {
			$arrSep = explode("|", $batchscan->separators);
		} else {
			$arrSep = array();
		}
		//add to array
		$arrSeparators = array_merge($arrSeparators, $arrSep);
		
		$msg = "separators merged:" . implode("|", $arrSeparators) . " -> " . $batchscan_id;
		$log->lwrite($msg);
	
		$arrSeparators = array_filter($arrSeparators, "noEmpty");
		
		$msg = "separators filtered:" . implode("|", $arrSeparators) . " -> " . $batchscan_id;
		$log->lwrite($msg);
	
		sort($arrSeparators);
		
		$msg = "separators sorted:" . implode("|", $arrSeparators) . " -> " . $batchscan_id;
		$log->lwrite($msg);
	
		$arrSeparators = array_unique($arrSeparators);
		
		$msg = "separators unique:" . implode("|", $arrSeparators) . " -> " . $batchscan_id;
		$log->lwrite($msg);
		//update separators
		$sql = "UPDATE cse_batchscan
		SET separators = '" . implode("|", $arrSeparators) . "'
		WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
		
		$msg = $sql;
		$log->lwrite($msg);
				
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
	}
	
	$msg = "separators stored:" . $batchscan_id;
	$log->lwrite($msg);
	
	//get completion
	$sql = "SELECT completion FROM cse_batchscan
	WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
		
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$completion = $stmt->fetchObject();

	//break up into array
	if ($completion->completion!="") {
		$arrComplete = explode("|", $completion->completion);
	} else {
		$arrComplete = array();
	}
	$arrCompletion[] = $page;
	if (count($arrComplete) > 0) {
		//add to array
		$arrCompletion = array_merge($arrCompletion, $arrComplete);
		$arrCompletion = array_filter($arrCompletion, "noEmpty");
		sort($arrCompletion);
		$arrCompletion = array_unique($arrCompletion);
	}
	
	
	//update completion
	$sql = "UPDATE cse_batchscan
	SET completion = '" . implode("|", $arrCompletion) . "'
	WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
		
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$msg = "completion established:" . $batchscan_id;
	$log->lwrite($msg);
	//update completion
	$sql = "UPDATE cse_batchscan
	SET `match` = '1'
	WHERE `consideration` = `completion` 
	AND customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
		
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	//update completion
	$sql = "UPDATE cse_batchscan
	SET `match` = '0'
	WHERE `consideration` != `completion` 
	AND customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
		
		$stmt = $db->prepare($sql);
		$stmt->execute();
	
	
	$msg = "match update completed " . $batchscan_id;
	$log->lwrite($msg);
	//get match
	$sql = "SELECT `match` FROM cse_batchscan
	WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
	
	
	$blnLast = false;	
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$match = $stmt->fetchObject();
	$blnLast = ($match->match == 1);
	
	if ($blnLast) {
		//update stacked status
		$sql = "UPDATE cse_batchscan
		SET stacked = 'P'
		WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
		
		$stmt = $db->prepare($sql);
		$stmt->execute();		

		$msg = "\r\n" . $sql . "
			";
		$log->lwrite($msg);
			
		//complete by assembling	
		//get separators
		$sql = "SELECT separators FROM cse_batchscan
		WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
		
		
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$batchscan = $stmt->fetchObject();
		
		//break up into array
		if ($batchscan->separators!="") {
			$arrSeparators = explode("|", $batchscan->separators);
		} else {
			$arrSeparators = array();
		}
		//die(print_r($arrSeparators));
		//get the stacks
		$arrStack = array();
		$document_count = -1;
		for ($jnt=0;$jnt<$pages;$jnt++) {
			//if the first page is not a separator, start from zero
			if ($jnt==0) {
				if (!in_array($jnt, $arrSeparators)) {
					$document_count = 0;
				} else {
					//the first page is a separator
					continue;
				}
			} else {
				if (in_array($jnt, $arrSeparators)) {
					$document_count++;
					continue;
				}
			}
			
			$arrStack[$document_count][] = $jnt;
		}
		$arrStackList = array();
		
		//loop to stitch stacks
		for($int=0;$int<count($arrStack);$int++) {
			if (!isset($arrStack[$int])) {
				continue;
			}
			//last page of the stack
			$max_page = (count($arrStack[$int]) -1);
			$new_pdf_stacks = $timestamp . "|" . $int . "|" . $arrStack[$int][0] . "|" . $arrStack[$int][$max_page];
			//echo $new_pdf_path . "<br />";
			
			$arrStackList[] = $arrStack[$int][0] . "~" . $arrStack[$int][$max_page];
			
			$document_list = implode("|", $arrStack[$int]);
			//stitch
			$url = "https://www.ikase.website/phpOCR/stitch.php";
			$params = array('document_list'=>$document_list,'batchscan_id'=>$batchscan_id,'customer_id'=>$customer_id,'uploaded'=>$uploaded, 'stacks'=>$new_pdf_stacks);
			//die($url . "?" . implode("&", $params));
			$msg = "\r\n" . $url . "?document_list=" . $document_list . "&batchscan_id=" . $batchscan_id . "&customer_id=" . $customer_id . "&uploaded=" . $uploaded . "&stacks=" . $new_pdf_stacks . "
			";
			$log->lwrite($msg);
			curl_post_async($url, $params);
		}
		
		//update stacks
		$sql = "UPDATE cse_batchscan
		SET stacks = '" . implode("|", $arrStackList) . "'
		WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
			
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$source = "<a href='../../web/uploads/" . $customer_id . "/" . $uploaded . ".pdf' target='_blank'>Source</a>";
		
		//update stacked status
		$sql = "UPDATE cse_batchscan
		SET stacked = 'Y'
		WHERE 
		customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
			
		$stmt = $db->prepare($sql);
		$stmt->execute();		
		
		$msg = "stacked ready sent to stitch " . $batchscan_id;
		$log->lwrite($msg);
		
		echo json_encode(array("success"=>"Y", "source"=>$source, "stacks"=>count($arrStack), "timestamp"=>$timestamp));
	}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	die(json_encode($error));
}
$db = null;

$msg = "multithread completed " . $image_path . " -- " . $batchscan_id;
$log->lwrite($msg);
$log->lclose();
?>