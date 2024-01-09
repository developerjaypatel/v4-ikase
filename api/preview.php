<?php
require_once('../rootdata.php');
if (isset($_GET["type"])) {
	if ($_GET["type"]=="merus") {
		$path = 'http://kustomweb.xyz/merus/archive.php?path=' . urlencode($_GET['file']);
		//die($path);
		$homepage = file_get_contents($path);
		echo $homepage;
		die();
	}
}
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', '1');

$http_origin = $_SERVER['HTTP_ORIGIN'];

if ($http_origin == "https://www.matrixdocuments.com" || $http_origin == "https://www.cajetfile.com" || $http_origin == "https://www.ikase.xyz") {  
    header("Access-Control-Allow-Origin: $http_origin");
}

include("connection.php");
require_once('../shared/legacy_session.php');

if (!isset($_SESSION["user_customer_id"]) && !isset($_GET["demo"])) {
	die("no go");
}

$document_id = passed_var("id", "get");
$file = passed_var("file", "get");
$file = str_replace(".PDF.pdf", ".pdf", $file);

$arrFile = explode(".", $file);
$extension = strtolower($arrFile[count($arrFile) - 1]);
$case_id = passed_var("case_id", "get");
$type = passed_var("type", "get");
$thumbnail_folder = passed_var("thumbnail_folder", "get");
if (isset($_GET["demo"])) {
	$customer_id = passed_var("cusid", "get");
} else {
	$customer_id = $_SESSION["user_customer_id"];
}

$db = getConnection();
//i need this to allow to proceed
$batchscan_id = "";

include("customer_lookup.php");

$path = findDocumentFolder($customer_id, $case_id, $file, $type, $thumbnail_folder, $document_id);

if (isset($_GET["download"])) {
	if ($extension=="pdf" || $extension=="docx" || $extension=="wav") {
		//die("<span style='background:green; color:white; padding:2px'>" . $path . " was found in our system </span>");
		
		$filename = explode("/", $path);
		$filename = $filename[count($filename) - 1];
		
		//die("filename:" . $filename);
		header('Content-Transfer-Encoding: binary');  // For Gecko browsers mainly
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($path)) . ' GMT');
		header('Accept-Ranges: bytes');  // Allow support for download resume
		header('Content-Length: ' . filesize($path));  // File size
		header('Content-Encoding: none');
		
		if ($extension=="docx") {
			header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');  // Change the mime type if the file is not docx
		} 
		if ($extension=="pdf") {
			header("Content-type:application/pdf");
		}
		if ($extension=="wav") {
			$path = '../uploads/'.$customer_id.'/'.$case_id.'/'.$file;
			$filename = explode("/", $path);
			$filename = $filename[count($filename) - 1];
			header('Content-Length: ' . filesize($path));
			header("Content-type:audio/wav");
		}
		
		header('Content-Disposition: attachment; filename=' . $filename);  // Make the browser display the Save As dialog
		readfile($path);  // This is necessary in order to get it to actually download the file, otherwise it will be 0Kb
	}
}

$iframe = "";
//$path = '../uploads/'.$customer_id.'/'.$case_id.'/'.$file;
$path = findDocumentFolder($customer_id, $case_id, $file, $type, $thumbnail_folder, $document_id);
if (!$path || !$extension) {
	die("<span style='background:red; color:white; padding:2px'>" . $file . " was not found in our system</span>");


} else {

	
	if (strpos($path, ".pdf") !== false || strpos($path, ".png") !== false || strpos($path, ".wma") !== false || strpos($path, ".wav") !== false || strpos($path, ".mp3") !== false || strpos($path, ".jpg") !== false) {
		if (strpos($path, ".pdf") !== false) {
			$iframe = '<iframe id="letter_frame" src="'.$server_name.'/' . $path . '" width="100%" height="800px"></iframe>';
			echo $iframe;
		} else {
			header("location:" . $path);
		}
	} else {
		$path = str_replace("../", "", $path);
		
		if (strpos($path, ".pdf") !== false || strpos($path, ".doc") !== false) {
			$iframe = '<iframe id="letter_frame" src="https://docs.google.com/gview?url='.$server_name.'/' . $path . '&embedded=true" width="100%" height="800px"></iframe>';
		} else {
			$iframe = '<iframe id="letter_frame" src="'.$server_name.'/' . $path . '" width="100%" height="800px"></iframe>';
		}
		echo $iframe;
	}
}
/*
$path = "../uploads/" . $customer_id . "/" . $case_id . "/" .  $file;
if ($type=="jetfile" || $type=="DOR" || $type=="DORE" || $type=="LIEN") {
	$arrFile = explode("/", $file);
	$filename = $arrFile[count($arrFile) - 1];
	$path = "../uploads/" . $customer_id . "/" . $case_id . "/jetfiler/" .  $filename;
}
if ($type=="eams_form") {
	$path = "../uploads/" . $customer_id . "/" . $case_id . "/eams_forms/" .  $file;
}
if (is_numeric($thumbnail_folder) && $extension!="docx" && $thumbnail_folder!="") {
	$path = "../uploads/" . $customer_id . "/imports/" . $file;
}
if ($type == "abacus") {
	$path = "https://www.ikase.xyz/ikase/abacus/" . $customer_data_source + "/" . $thumbnail_folder . "/" . $file;
}

if (file_exists($path)) {
	header("location:" . $path);
	die();
} else {
	//maybe it's a jetfile
	$path = "../uploads/" . $customer_id . "/" . $case_id . "/jetfiler/" .  $file;
	if (file_exists($path)) {
		header("location:" . $path);
		die();
	} else {
		//might be a jetfiler form?
		$path = "../uploads/" . $customer_id . "/" . $case_id . "/eams_forms/" .  $file;
		if (file_exists($path)) {
			header("location:" . $path);
			die();
		}
	}
}
die($path . " was not found in our system");
*/
?>
<script type="application/javascript">
document.getElementById("letter_frame").style.width = (window.innerWidth - 40) + "px";
document.getElementById("letter_frame").style.height = window.innerHeight + "px";
window.history.replaceState("", "Preview", "<?=$server_name?>");
</script>
