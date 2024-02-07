<?php
if ($_SERVER['REMOTE_ADDR']=='173.55.229.70') {
	error_reporting(E_ALL);
	ini_set('display_errors', '1');	
}
ini_set('max_execution_time', 6*MIN);

include_once("functions.php");
include_once('../qrcode/lib/QrReader.php');

if (isset($_POST["image_path"])) {
	$image_path = $_POST["image_path"];
	$batchscan_id = $_POST["batchscan_id"];
	$page = $_POST["page"];
	$pages = $_POST["pages"];
	$customer_id = $_POST["customer_id"];
	$user_id = $_POST["user_id"];
	$uploaded = $_POST["uploaded"];
	//timestamp for file names
	$timestamp = $_POST["timestamp"];
} else {
	$image_path = $_GET["image_path"];
	$batchscan_id = $_GET["batchscan_id"];
	$page = $_GET["page"];
	$pages = $_GET["pages"];
	$customer_id = $_GET["customer_id"];
	$user_id = $_GET["user_id"];
	$uploaded = $_GET["uploaded"];
	//timestamp for file names
	$timestamp = $_GET["timestamp"];
}
if (!is_numeric($batchscan_id) || !is_numeric($page)) {
	die();
}

$db = getConnection();

include("../api/customer_lookup.php");

$msg = "start multithread " . $image_path . " -- " . $batchscan_id . " --> " . $user_id;
include("../api/cls_logging.php");
include_once("header.php");

$arrAttemptList[] = $page;
if ($data_source != "") {
	$data_source = "`ikase_" . $data_source . "`";
} else {
	$data_source = "`ikase`";
}
//get attempted
$sql = "SELECT `attempted` 
FROM " . $data_source . ".`cse_batchscan`
WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;


try {
	$stmt = DB::run($sql);
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
	$sql = "UPDATE " . $data_source . ".cse_batchscan
	SET attempted = '" . implode("|", $arrAttemptList) . "'
	WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
	
	$stmt = DB::run($sql);


	//$filename = "/home/cstmwb/public_html/autho/web/uploads/" . $customer_id . "/" . $timestamp . "/" . $image_path;
	$filename = UPLOADS_PATH . $customer_id . '\\imports\\' . $timestamp . '\\' . $image_path;
	
	$qrcode = new QrReader($filename);
	//die($qrcode->text());
	if ($qrcode->text()=="ikase separator") {
		//echo $filename . " is a separator (" . $intFound . ")\r\n";
		$arrSeparators[] = $page;
	}
	$db = getConnection();
	
	if (count($arrSeparators)>0) {
		//get separators
		$sql = "SELECT separators FROM " . $data_source . ".cse_batchscan
		WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
		
		$msg = $sql;
		$log->lwrite($msg);
	
		$stmt = DB::run($sql);
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
		$sql = "UPDATE " . $data_source . ".cse_batchscan
		SET separators = '" . implode("|", $arrSeparators) . "'
		WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
		
		$msg = $sql;
		$log->lwrite($msg);
				
		$stmt = DB::run($sql);
		
	}
	
	$msg = "separators stored:" . $batchscan_id;
	$log->lwrite($msg);
	
	//get completion
	$sql = "SELECT completion 
	FROM " . $data_source . ".cse_batchscan
	WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
		
	$stmt = DB::run($sql);
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
	$sql = "UPDATE " . $data_source . ".cse_batchscan
	SET completion = '" . implode("|", $arrCompletion) . "'
	WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
		
	$stmt = DB::run($sql);
	
	$msg = "completion established:" . $batchscan_id;
	$log->lwrite($msg);
	//update completion
	$sql = "UPDATE " . $data_source . ".cse_batchscan
	SET `match` = '1'
	WHERE `consideration` = `completion` 
	AND customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
		
	$stmt = DB::run($sql);
	
	//update completion
	$sql = "UPDATE " . $data_source . ".cse_batchscan
	SET `match` = '0'
	WHERE `consideration` != `completion` 
	AND customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
		
	$stmt = DB::run($sql);
	
	$msg = "match update completed " . $batchscan_id;
	$log->lwrite($msg);
	//get match
	$sql = "SELECT `match` 
	FROM " . $data_source . ".cse_batchscan
	WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
	
	$blnLast = false;	
	$stmt = DB::run($sql);
	$match = $stmt->fetchObject();
	$blnLast = ($match->match == 1);
	
	if ($blnLast) {
		//update stacked status
		$sql = "UPDATE " . $data_source . ".cse_batchscan
		SET stacked = 'P'
		WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
		
		$stmt = DB::run($sql);		

		$msg = "\r\n" . $sql . "
			";
		$log->lwrite($msg);
			
		//complete by assembling	
		//get separators
		$sql = "SELECT separators 
		FROM " . $data_source . ".cse_batchscan
		WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
		
		$stmt = DB::run($sql);
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
			$params = array('document_list'=>$document_list,'batchscan_id'=>$batchscan_id,'customer_id'=>$customer_id,'uploaded'=>$uploaded, 'stacks'=>$new_pdf_stacks, 'user_id'=>$user_id);
			//die($url . "?" . implode("&", $params));
			$msg = "\r\n" . $url . "?document_list=" . $document_list . "&batchscan_id=" . $batchscan_id . "&customer_id=" . $customer_id . "&uploaded=" . $uploaded . "&stacks=" . $new_pdf_stacks . "&user_id=" . $user_id . "
			";
			$log->lwrite($msg);
			curl_post_async($url, $params);
		}
		
		//update stacks
		$sql = "UPDATE " . $data_source . ".cse_batchscan
		SET stacks = '" . implode("|", $arrStackList) . "'
		WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
			
		$stmt = DB::run($sql);
		
		$source = "<a href='../../web/uploads/" . $customer_id . "/" . $uploaded . ".pdf' target='_blank'>Source</a>";
		
		//update stacked status
		$sql = "UPDATE " . $data_source . ".cse_batchscan
		SET stacked = 'Y'
		WHERE 
		customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
			
		$stmt = DB::run($sql);		
		
		$msg = "stacked ready sent to stitch " . $batchscan_id;
		$log->lwrite($msg);
		
		echo json_encode(array("success"=>"Y", "source"=>$source, "stacks"=>count($arrStack), "timestamp"=>$timestamp));
	}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	die(json_encode($error));
}

$msg = "multithread completed " . $image_path . " -- " . $batchscan_id;
$log->lwrite($msg);
$log->lclose();
?>
