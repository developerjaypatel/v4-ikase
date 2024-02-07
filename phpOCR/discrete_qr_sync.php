<?php
session_start();
session_write_close();

$msg = "sync started";
include("../api/cls_logging.php");

ini_set('max_execution_time', 600); //300 seconds = 5 minutes

include_once("functions.php");
include_once('../qrcode/lib/QrReader.php');

//passed variables
$uploaded = $_POST["uploaded"];
if ($uploaded=="") {
	$uploaded = $_GET["uploaded"];
}
if ($uploaded=="") {
	die("no file");
}

$batchscan_id = $_POST["batchscan_id"];
if ($batchscan_id=="") {
	$batchscan_id = $_GET["batchscan_id"];
}
if ($batchscan_id=="" || !is_numeric($batchscan_id)){
	die();
}
$pages = $_POST["pages"];
if ($pages=="") {
	$pages = $_GET["pages"];
}
$customer_id = $_POST["customer_id"];
if ($customer_id=="") {
	$customer_id = $_GET["customer_id"];
}
$user_id = $_POST["user_id"];
if ($user_id=="") {
	$user_id = $_GET["user_id"];
}
if ($pages=="") {
	die("no pages");
}
$timestamp = $_POST["timestamp"];
if ($timestamp=="") {
	$timestamp = $_GET["timestamp"];
}
$db = getConnection();

include("../api/customer_lookup.php");
if ($data_source != "") {
	$data_source = "`ikase_" . $data_source . "`";
} else {
	$data_source = "`ikase`";
}

$msg = $data_source . " << \r\n";
$log->lwrite($msg);
	
$pipe = array();
$pipe_name = array();
for($page=0; $page < $pages; $page++) {
	//$image_path = $_SERVER["DOCUMENT_ROOT"] . "/uploads/" . $customer_id . "/" . $timestamp . "/" . $uploaded . "_" . $page . ".png";
	//get image from the imports folder
	$image_path = UPLOADS_PATH . $customer_id . "\\imports\\" . $timestamp . DC . $uploaded . "_" . $page . ".png";
	$image_name = $uploaded . "_" . $page . ".png";
	
	$filesize = filesize($image_path);
	
	//continue;
	if ($filesize > 3000 && $filesize < 5000) {
		$msg = $image_name . " -> " . $filesize . "\r\n";
		$log->lwrite($msg);
	
		//get consideration
		$sql = "SELECT consideration FROM cse_batchscan
		WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
		
		try {
			$stmt = $db->query($sql);
			$stmt->execute();
			$consideration = $stmt->fetchObject();
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			die(json_encode($error));
		}
	
		//break up into array
		if ($consideration->consideration!="") {
			$arrSep = explode("|", $consideration->consideration);
		} else {
			$arrConsider = array();
		}
		$arrConsideration[] = $page;
		if (count($arrConsider) > 0) {
			//add to array
			$arrConsideration = array_merge($arrConsideration, $arrConsider);
			$arrConsideration = array_filter($arrConsideration, "strlen");
			sort($arrConsideration);
			$arrConsideration = array_unique($arrConsideration);
		}
		
		//update consideration
		$sql = "UPDATE cse_batchscan
		SET consideration = '" . implode("|", $arrConsideration) . "'
		WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
		//die($sql);
		try {	
			$stmt = DB::run($sql);
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			die(json_encode($error));
		}
		$time_start = getmicrotime();
	}
}
//die("stop");

for($page=0; $page < $pages; $page++) {
	//$image_path = $_SERVER["DOCUMENT_ROOT"] . "/uploads/" . $customer_id . "/" . $timestamp . "/" . $uploaded . "_" . $page . ".png";
	//get image from the imports folder
	$image_path = UPLOADS_PATH . $customer_id . "\\imports\\" . $timestamp . DC . $uploaded . "_" . $page . ".png";
	$image_name = $uploaded . "_" . $page . ".png";
	
	$filesize = filesize($image_path);
	//echo $image_name . " -> " . $filesize . "\r\n";
	//die();
	if ($filesize > 3000 && $filesize < 5000) {
		$filename = $image_path;
		//echo "considering file:" . $filename . "<br />";
		$qrcode = new QrReader($filename);
		$arrSeparators = array();
		
		$msg = "qr says:" . $qrcode->text() . "\r\n";
		$log->lwrite($msg);
		//echo "qr says:" . $qrcode->text() . "<br /><br />";
		if ($qrcode->text()=="ikase separator") {
			//echo $filename . " is a separator (" . $intFound . ")\r\n";
			$arrSeparators[] = $page;
		}
		
		if (count($arrSeparators)>0) {
			$msg = $page . " ++\r\n";
			$log->lwrite($msg);
			
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
			$arrSep = array();
			if ($batchscan->separators!="") {
				$arrSep = explode("|", $batchscan->separators);
			}
			//add to array
			$arrSeparators = array_merge($arrSeparators, $arrSep);
			//die(print_r($arrSeparators));
			$msg = "separators merged:" . implode("|", $arrSeparators) . " -> " . $batchscan_id;
			$log->lwrite($msg);
		
			$arrSeparators = array_filter($arrSeparators, "strlen");
			//die(print_r($arrSeparators));
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
			//echo $sql . "<br />";
			$msg = $sql;
			$log->lwrite($msg);
					
			$stmt = DB::run($sql);
			
		}
		
		$msg = "separators stored:" . $batchscan_id;
		$log->lwrite($msg);
	}
}
//die(print_r($arrSeparators));
/*
foreach($arrSeparators as $page) {
	//$image_path = $_SERVER["DOCUMENT_ROOT"] . "/uploads/" . $customer_id . "/" . $timestamp . "/" . $uploaded . "_" . $page . ".png";
	//get image from the imports folder
	$image_path = UPLOADS_PATH . $customer_id . "\\imports\\" . $timestamp . DC . $uploaded . "_" . $page . ".png";
	$image_name = $uploaded . "_" . $page . ".png";
	
	//$filesize = filesize($image_path);
	//echo $image_name . " -> " . $filesize . "\r\n";
	//die();
	if ($image_name!="") {
		$filename = $image_path;
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
		
		//die($sql);
		
		$blnLast = false;	
		$stmt = DB::run($sql);
		$match = $stmt->fetchObject();
		$blnLast = ($match->match == 1);
		
		//$blnLast = ($page == $pages - 1);
		if ($blnLast) {
			*/
			$db = getConnection();
			
			$arrStitchList = array();
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
			
			foreach($arrStack as $stack) {
				//last page of the stack
				$max_page = (count($stack) -1);
				$new_pdf_stacks = $timestamp . "|" . $int . "|" . $stack[0] . "|" . $stack[$max_page];
				//echo $new_pdf_path . "<br />";
				
				$arrStackList[] = $stack[0] . "~" . $stack[$max_page];
				
				$document_list = implode("|", $stack);
				
				//now we stitch
				$msg = "stitch started: " . $document_list . "
				stacks:" . $stacks . "
				batchscan_id:" . $batchscan_id;
					
				//break it all up
				$arrStackInfo = explode("|", $stacks);
				//0 = timestamp, 1 stack_number, 2 = first_page, 3 = last_page
				$thumbnail_folder = $timestamp;
				
				$arrListInfo = explode("|", $document_list);
				$arrList = array();
				foreach($arrListInfo as $stack_item) {
					//$arrList[] = "/home/cstmwb/public_html/autho/web/uploads/" . $customer_id . "/" . $uploaded . ".pdf[" . $stack_item . "]";
					$arrList[] = UPLOADS_PATH . $customer_id . DC . $uploaded . ".pdf[" . $stack_item . "]";
				}
				$document_list = '"' . implode('" "', $arrList) . '"';
				
				//$new_pdf_path = "/home/cstmwb/public_html/autho/web/uploads/" .$customer_id . "/" .  $uploaded . "_" . ($arrStackInfo[2]+1) . "_" . ($arrStackInfo[3]+1) . ".pdf";
				$new_pdf_path = UPLOADS_PATH . $customer_id . "\\imports\\" . $uploaded . "_" . $stack[0] . "_" . $stack[$max_page] . ".pdf";
				
				$msg = "new_pdf_path: " . $new_pdf_path;
				
				//die($msg);
				$log->lwrite($msg);
				//echo 'convert -density 150 ' . $document_list . ' "' . $new_pdf_path . '"<br />';
				exec('convert -density 150 ' . $document_list . ' "' . $new_pdf_path . '"');
				
				$arrStitchList[] =  $stack[0] . "~" . $stack[$max_page];
				//get stitched
				$sql = "SELECT stitched 
				FROM " . $data_source . ".cse_batchscan
				WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
				
				try {
					$db = getConnection();
					
					$stmt = $db->query($sql);
					$stmt->execute();
					$stitched = $stmt->fetchObject();
					
					$msg = "stitched found " . $batchscan_id;
					$log->lwrite($msg);
						
					//break up into array
					if ($stitched->stitched!="") {
						$arrStitch = explode("|", $stitched->stitched);
					} else {
						$arrStitch = array();
					}
					if (count($arrStitch) > 0) {
						//add to array
						$arrStitchList = array_merge($arrStitchList, $arrStitch);
						$arrStitchList = array_filter($arrStitchList, "strlen");
						sort($arrStitchList);
						$arrStitchList = array_unique($arrStitchList);
					}
					
					//update stitched
					$sql = "UPDATE " . $data_source . ".cse_batchscan
					SET stitched = '" . implode("|", $arrStitchList) . "'
					WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
					
					$msg = $sql;
					//echo $msg . "<br />";
					$log->lwrite($msg);
					
					$stmt = DB::run($sql);
				
					//add the stack as a document, unattached so far
					$table_uuid = uniqid("KS", false);
					$notification_uuid = uniqid("KN", false);
					$document_name = $uploaded . "_" . $stack[0] . "_" . $stack[$max_page] . ".pdf";
					$msg = "docname :" . $document_name  . " -- " . $batchscan_id;
					$log->lwrite($msg);
					//echo $document_name . "<br />";
					$document_date = date("Y-m-d H:i:s");
					$document_extension = ".pdf";
					/*
					if ($arrStackInfo[2] != $arrStackInfo[3]) {
						$description = ($arrStackInfo[2]+1) . "-" . ($arrStackInfo[3]+1);
					} else {
						$description = ($arrStackInfo[2]+1);
					}
					*/
					$description =  $stack[0] . "-" . $stack[$max_page];
					$description_html = $description;	//for now
					
					$type = "batchscan";
					$verified = "Y";
					
					$sql = "INSERT INTO " . $data_source . ".cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, thumbnail_folder, description, description_html, type, verified, customer_id) 
							VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :thumbnail_folder, :description, :description_html, :type, :verified, :customer_id)";
							
					//echo "inserting " . $document_name . "<br />";
					
					$msg = "inserting " . $document_name  . " -- " . $batchscan_id;
					$log->lwrite($msg);
				
					$stmt = $db->prepare($sql);  
					$stmt->bindParam("document_uuid", $table_uuid);
					//reason for batch uuid, use batch id for now
					$stmt->bindParam("parent_document_uuid", $batchscan_id);
					$stmt->bindParam("document_name", $document_name);
					$stmt->bindParam("document_date", $document_date);
					$stmt->bindParam("document_filename", $document_name);
					$stmt->bindParam("document_extension", $document_extension);
					$stmt->bindParam("thumbnail_folder", $thumbnail_folder);
					$stmt->bindParam("description", $description);
					$stmt->bindParam("description_html", $description_html);
					$stmt->bindParam("type", $type);
					$stmt->bindParam("customer_id", $customer_id);
					$stmt->bindParam("verified", $verified);
					$stmt->execute();
//					$new_id = $db->lastInsertId();
					
					$sql = "INSERT INTO " . $data_source . ".cse_notification (`document_uuid`, `notification_uuid`, `user_uuid`, `notification`, `notification_date`, `customer_id`)
					VALUES ('" . $table_uuid . "', '" . $notification_uuid . "', '" . $user_id . "','review', '" . date("Y-m-d H:i:s") . "', '" . $customer_id . "')";
					$msg = "notifying " . $document_name  . " -- " . $batchscan_id;
					
					//echo $msg . "<br />";
					
					$log->lwrite($msg);
					$log->lwrite($sql);
				
				
					DB::run($sql);
					//die(print_r($newEmployee));
					//echo json_encode(array("id"=>$new_id)); 
				} catch(PDOException $e) {	
					die( '{"error":{"text":'. $e->getMessage() .'}}'); 
				}
				
				
				$msg = "convert -density 150 " . $document_list . " " . $new_pdf_path;
				$log->lwrite($msg);
				
				$msg = "stitch completed " . $document_list . " " . $batchscan_id;
				$log->lwrite($msg);
			}
		//}
	//}
//}
//die("<br />done");
//update separators
$db = getConnection();

$sql = "UPDATE cse_batchscan
SET separated = 'Y'
WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;

try {	
	$stmt = DB::run($sql);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	die(json_encode($error));
}

$msg = "\r\n" . $sql . "
";
// set path and name of log file (optional)
$log->lwrite($msg);
		
//update completion
$sql = "UPDATE cse_batchscan
SET `match` = '-1'
WHERE 1
AND customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
try {	
	$stmt = DB::run($sql);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	die(json_encode($error));
}

$msg = "\r\n" . $sql . "
";
// set path and name of log file (optional)
$log->lwrite($msg);

echo json_encode(array("stacks"=>"done"));


$msg = "
async completed " . $batchscan_id . "
";
// set path and name of log file (optional)
$log->lwrite($msg);
// close log file
$log->lclose();
?>
