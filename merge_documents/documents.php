<?php
ini_set('max_execution_time', 0);
ini_set('memory_limit', -1);

require_once 'vendor/autoload.php';
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

require_once('../shared/legacy_session.php');
session_write_close();
error_reporting(0);

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:index.html");
	die();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include("../api/connection.php");
	$db = getConnection();	
	$operation = $_POST['operation'];
	$case_id = filter_var($_POST['case_id'], FILTER_SANITIZE_STRING);
	$ids = filter_var($_POST['ids'], FILTER_SANITIZE_STRING);
	$document_ids = explode(",",$_POST['ids']);
	$customer_id = $_SESSION["user_customer_id"];
	$db_prefix = $_SESSION["user_data_source"];
	$file_paths = [];
	
	if(count($document_ids) > 0) {
		$sql = "SELECT * FROM ikase_".$db_prefix.".cse_document where document_id IN (".implode(',', $document_ids).");";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$sql = "SELECT * FROM ikase_".$db_prefix.".cse_case where case_id ='".$case_id."';";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$kase = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		
		$activity_uuid = uniqid("KS", false);
		$case_uuid = $kase[0]->case_uuid;
		$category = 'Documents';
		$last_updated_date = date("Y-m-d H:i:s");
		$case_activity_uuid = uniqid("KA", false);
		$document_uuid = uniqid("IK", false);
		$case_document_uuid = uniqid("JK", false);
			
		if(count($documents) > 0) {
			foreach($documents as $document) {
				$document_id = $document->document_id;
				$thumbnail_folder = $document->thumbnail_folder;
				$type = $document->type;
				$file = $document->document_filename;
				if(file_exists("../uploads/".$customer_id."/".$case_id."/".$file)) {
					$file_paths[] = "../uploads/".$customer_id."/".$case_id."/".$file;
				}
			}
		}
	}
	//var_dump($file_paths);die;
	if(count($file_paths) < 1) {
		header('Location: ' . $_SERVER['HTTP_REFERER']);
	}

	switch ($operation) {
	  case "zip":
		$download_name = 'documents_'.date("Y-m-d_H_i_s").'.zip';
		$file_name = "../uploads/".$customer_id."/".$case_id."/".$download_name;
		$zip = new ZipArchive;
		if ($zip->open($file_name, ZipArchive::CREATE) === TRUE) {
			foreach($file_paths as $file_path) {
				$zip->addFromString(basename($file_path),  file_get_contents($file_path));  
			}
			$zip->close();
			
			$activity = "Zip Document(s) [<a href='".$file_name."' target='_blank' style='background:#7ceeeebd;color:black'>".$download_name."</a>] was generated and downloaded by ".$_SESSION["user_name"];
			$sql_cse_act = "INSERT INTO ikase_".$db_prefix.".cse_activity 
			(`activity_uuid`, `activity`,  `activity_category`, `activity_user_id`, `customer_id`)
			VALUES ('" . $activity_uuid . "', '" . addslashes($activity) . "',  '" . addslashes($category) . "', '" . $_SESSION['user_plain_id'] . "', " . $_SESSION['user_customer_id'] . ")";
			$stmt_cse_act = $db->prepare($sql_cse_act);  
			$stmt_cse_act->execute();
			$activity_id = $db->lastInsertId();

			if ($case_uuid != "") {
				$attribute = "Documents";
				$track_id = '-1';
				$sql_cse_case_act = "INSERT INTO ikase_".$db_prefix.".cse_case_activity 
				(`case_activity_uuid`, `case_uuid`, `activity_uuid`, `attribute`, `case_track_id`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_activity_uuid . "', '" . $case_uuid . "', '" . $activity_uuid . "', '" . $attribute . "', " . $track_id . ", '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				$stmt_cse_case_act = $db->prepare($sql_cse_case_act);  
				$stmt_cse_case_act->execute();
				
				$sql_cse_document = "INSERT INTO ikase_".$db_prefix.".cse_document 
				(`document_uuid`, `document_name`, `document_date`, `document_filename`, `document_extension`, `type`, `deleted`, `customer_id`, `description`)
				VALUES ('" . $document_uuid . "', '" . $download_name . "', '" . $last_updated_date . "', '" . $download_name . "', 'Document', 'document', 'Y', '" . $_SESSION['user_customer_id'] . "', '')";
				//echo $sql_cse_document;die;
				$sql_cse_document = $db->prepare($sql_cse_document);  
				$sql_cse_document->execute();
				
				$sql_cse_case_document = "INSERT INTO ikase_".$db_prefix.".cse_case_document 
				(`case_document_uuid`, `case_uuid`, `document_uuid`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
				VALUES ('" . $case_document_uuid . "', '" . $case_uuid  . "', '" . $document_uuid . "', '" . $last_updated_date . "', '" . $_SESSION["user_id"]."', 'Y', '" . $_SESSION['user_customer_id'] . "')";
				//echo $sql_cse_document;die;
				$sql_cse_case_document = $db->prepare($sql_cse_case_document);  
				$sql_cse_case_document->execute();
			}

			header('Content-Type: application/zip');
			header('Content-Length: ' . filesize($file_name));
			header('Content-Disposition: attachment; filename='.$download_name);
			readfile($file_name); 
		}
		break;
	  case "merge":
		$download_name = 'merge_documents_'.date("Y-m-d_H_i_s").'.pdf';
		$file_name = "../uploads/".$customer_id."/".$case_id."/".$download_name;
		$pdf = new \Clegginabox\PDFMerger\PDFMerger;
		$junk_file_path = "junk";
		
		$files = glob($junk_file_path.'/*'); // get all file names
		//var_dump($files);die;
		foreach($files as $file){ // iterate files
			if(is_file($file) && $file !== $junk_file_path."/index.php") {
				unlink($file); // delete file
			}
		}

		Settings::setOutputEscapingEnabled(true);
		Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);
		Settings::setPdfRendererPath($junk_file_path);

		foreach($file_paths as $file_path) {
			$basename = basename($file_path);

			$ext = pathinfo($basename , PATHINFO_EXTENSION);
			if(strtolower($ext) == "docx") {
				$new_file_name = md5(rand(100000, 999999).time().rand(1000,9999));
				$doc_name  = $new_file_name . '.pdf';
				
				$php_word = IOFactory::load($file_path, 'Word2007');
				$php_word ->save($junk_file_path.'/'.$doc_name, 'PDF');
				$file_path = $junk_file_path .'/'.$doc_name;
			}
			
			$pdf->addPDF($file_path, 'all'); 
		}
		$activity = "Merge Document(s) [<a href='".$file_name."' target='_blank' style='background:#7ceeeebd;color:black'>".$download_name."</a>] was generated and downloaded by ".$_SESSION["user_name"];
		$sql_cse_act = "INSERT INTO ikase_".$db_prefix.".cse_activity 
		(`activity_uuid`, `activity`,  `activity_category`, `activity_user_id`, `customer_id`)
		VALUES ('" . $activity_uuid . "', '" . addslashes($activity) . "',  '" . addslashes($category) . "', '" . $_SESSION['user_plain_id'] . "', " . $_SESSION['user_customer_id'] . ")";
		$stmt_cse_act = $db->prepare($sql_cse_act);  
		$stmt_cse_act->execute();
		$activity_id = $db->lastInsertId();

		if ($case_uuid != "") {
			$attribute = "Documents";
			$track_id = '-1';
			$sql_cse_case_act = "INSERT INTO ikase_".$db_prefix.".cse_case_activity 
			(`case_activity_uuid`, `case_uuid`, `activity_uuid`, `attribute`, `case_track_id`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_activity_uuid . "', '" . $case_uuid . "', '" . $activity_uuid . "', '" . $attribute . "', " . $track_id . ", '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			$stmt_cse_case_act = $db->prepare($sql_cse_case_act);  
			$stmt_cse_case_act->execute();
			
			$sql_cse_document = "INSERT INTO ikase_".$db_prefix.".cse_document 
			(`document_uuid`, `document_name`, `document_date`, `document_filename`, `document_extension`, `type`, `deleted`, `customer_id`, `description`)
			VALUES ('" . $document_uuid . "', '" . $download_name . "', '" . $last_updated_date . "', '" . $download_name . "', 'Document', 'document', 'Y', '" . $_SESSION['user_customer_id'] . "', '')";
			//echo $sql_cse_document;die;
			$sql_cse_document = $db->prepare($sql_cse_document);  
			$sql_cse_document->execute();
			
			$sql_cse_case_document = "INSERT INTO ikase_".$db_prefix.".cse_case_document 
			(`case_document_uuid`, `case_uuid`, `document_uuid`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
			VALUES ('" . $case_document_uuid . "', '" . $case_uuid  . "', '" . $document_uuid . "', '" . $last_updated_date . "', '" . $_SESSION["user_id"]."', 'Y', '" . $_SESSION['user_customer_id'] . "')";
			//echo $sql_cse_document;die;
			$sql_cse_case_document = $db->prepare($sql_cse_case_document);  
			$sql_cse_case_document->execute();
		}

		$pdf->merge('file', $file_name);
		header('Content-Type: application/pdf');
		header('Content-Length: ' . filesize($file_name));
		header('Content-Disposition: attachment; filename='.$download_name);
		readfile($file_name); 
		break;
	  default:
		break;
	}
	
} else {
	http_response_code(404);
	die();
}
