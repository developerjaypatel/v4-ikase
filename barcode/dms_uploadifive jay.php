<?php
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', '1');	

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("../shared/legacy_session.php");

if (!isset($_SESSION["user_customer_id"])) {
	die("noNoNO");
}

include("../api/connection.php");

$color = "white";
$customer_id = $_SESSION["user_customer_id"];
$uploadDir = ROOT_PATH.'scans'.DC.$customer_id.DC.date("Ymd");
//die($uploadDir);
if (!file_exists($uploadDir)) {
	mkdir($uploadDir, 0755, true);
}
//clear out old files
$yesterday  = mktime(0, 0, 0, date("m") , date("d") - 3, date("Y"));

// Set the allowed file extensions
$fileTypes = array('pdf'); // Allowed file extensions

//$verifyToken = md5('ikase_system' . $_POST['timestamp']);
//echo "verify:" . $verifyToken . "\r\n";


$arrStoredFiles = array();
if (!empty($_FILES)) {
	for($int = 0; $int < count($_FILES['Filedata']['tmp_name']); $int++) {
		$tempFile   = $_FILES['Filedata']['tmp_name'][$int];
		$targetFile = $_FILES['Filedata']['name'][$int];
		$document_counter = 1;
	
		//if a specific directory is requested, the file will be overwritten
		if (file_exists($uploadDir ."\\". $targetFile)) {
			//die("<span style='color:white'>" . $targetFile . " has already been uploaded to " . $uploadDir . $targetFile . ".<br />If you are concerned that the file was not processed properly, please contact support</span>");
			//break up the file name with ., add an increment in parentheses
			$arrFile = explode(".", $_FILES['Filedata']['name'][$int]);
			
			$suffix = "_" . date("ymjGis");
			$arrFile[count($arrFile)-1] = $suffix . "." .  $arrFile[count($arrFile)-1];
			
			$targetFile = implode("", $arrFile);
	
			$document_counter++;
		}
		$uploadedFile = $targetFile;
		$thumbFile = str_replace(".PDF", ".jpg", $targetFile);
		$thumbFile = str_replace(".pdf", ".jpg", $targetFile);
		
		// Jay change start 11-19-2021
		// $targetFile = $uploadDir. $targetFile;
		$targetFile = $uploadDir."\\". $targetFile;
		// Jay change end 11-19-2021
		$targetFile = strtolower($targetFile);
		$arrFile = explode(".", $targetFile);
		// Validate the filetype
		$fileParts = pathinfo($_FILES['Filedata']['name'][$int]);
		if (in_array(strtolower($fileParts['extension']), $fileTypes)) {
			// Save the file
			move_uploaded_file($tempFile, $targetFile);
			
			// Jay change start 11-18-2021
			// $arrFileDetails = explode("\\", $targetFile);
			// $targetFile = $arrFileDetails[count($arrFileDetails) - 1];
			// Jay change end 11-18-2021

			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$finish_time = $time;
			$total_time = round(($finish_time - $header_start_time), 4);
			
			//add to the queue
			
			// Jay change start 11-18-2021
			// $stored_file = $uploadDir . $targetFile;
			$stored_file = $targetFile;
			// Jay change end 11-18-2021

			$arrStoredFiles[] = $stored_file;
		} else {
			// The file type wasn't allowed
			echo 'Invalid file type.';
			die();
		}
	}
	$stored_file = $arrStoredFiles[0];
	//foreach($arrStoredFiles as $stored_file) {				
		$sql = "INSERT INTO cse_batchscan (`filename`, `customer_id`) 
			VALUES (:filename, :customer_id)";
        //die($sql);
        DB::runOrApiError($sql, ['filename' => $stored_file, 'customer_id' => $_SESSION['user_customer_id']]);
        $batchscan_id = DB::lastInsertId();

		$operation = "insert";
		$sql = "INSERT INTO cse_batchscan_track (`user_uuid`, `user_logon`, `operation`, `batchscan_id`, `dateandtime`, `filename`, `time_stamp`, `pages`, `consideration`, `attempted`, `completion`, `match`, `separators`, `stacks`, `stitched`, `customer_id`, `readimage`, `processed`, `separated`, `stacked`, `deleted`)
		SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `batchscan_id`, `dateandtime`, `filename`, `time_stamp`, `pages`, `consideration`, `attempted`, `completion`, `match`, `separators`, `stacks`, `stitched`, `customer_id`, `readimage`, `processed`, `separated`, `stacked`, `deleted`
		FROM cse_batchscan
		WHERE 1
		AND batchscan_id = " . $batchscan_id . "
		AND customer_id = " . $_SESSION['user_customer_id'] . "
		LIMIT 0, 1";
		//die($sql);
        DB::runOrApiError($sql);
		echo "<div style='color:" . $color . ";font-family:arial'>";
		echo "The file was uploaded successfully";	//in " . $total_time;	
		echo "<br />It will now be processed, and you will be notified when it is ready.<br /><br />
";
		echo "</div>";
	//}
	
	echo "<br /><div style='color:" . $color . ";font-family:arial'>Return to <a href='ikase_form.php' style='color:" . $color . ";font-family:arial'>Batchscan Import Form</a>.</div>";
} else {
	die("No files were uploaded");
}
?>
<script type="application/x-javascript">
parent.currentBatchscanID("<?php echo $batchscan_id; ?>");
</script>
