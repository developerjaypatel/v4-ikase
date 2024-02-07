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
	$file_paths = [];
	
	if(count($document_ids) > 0) {
		foreach($document_ids as $document) {
			if(file_exists("../uploads/".$customer_id."/".$case_id."/".$document)) {
				$file_paths[] = "../uploads/".$customer_id."/".$case_id."/".$document;//findDocumentFolder($customer_id, $case_id, $file, $type, $thumbnail_folder, $document_id);
			}
		}
	}
	//var_dump($file_paths);die;
	if(count($file_paths) < 1) {
		header('Location: ' . $_SERVER['HTTP_REFERER']);
	}

	switch ($operation) {
	  case "zip":
		$download_name = 'documents_'.date("Y-m-d_H:i").'.zip';
		$file_name = tempnam("tmp", "zip");
		$zip = new ZipArchive;
		if ($zip->open($file_name, ZipArchive::CREATE) === TRUE) {
			foreach($file_paths as $file_path) {
				$zip->addFromString(basename($file_path),  file_get_contents($file_path));  
			}
			$zip->close();
			header('Content-Type: application/zip');
			header('Content-Length: ' . filesize($file_name));
			header('Content-Disposition: attachment; filename='.$download_name);
			readfile($file_name);
			unlink($file_name); 
		}
		break;
	  case "merge":
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
				//echo $file_path."<br/>";
			}
			
			$pdf->addPDF($file_path, 'all'); 
		}
		//var_dump($a);die;
		$pdf->merge('download', 'merge_documents_'.date("Y-m-d_H:i").'.pdf');
		break;
	  default:
		break;
	}
	
} else {
	http_response_code(404);
	die();
}
