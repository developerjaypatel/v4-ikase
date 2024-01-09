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
$uploadDir = ROOT_PATH.'scans'.DC.$customer_id.DC.date("Ymd").DC;

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
		if ($_SERVER['REMOTE_ADDR']=='47.154.251.216') {
			//die($tempFile);
		}
		//if a specific directory is requested, the file will be overwritten
		if (file_exists($uploadDir . $targetFile)) {
			//die("<span style='color:white'>" . $targetFile . " has already been uploaded to " . $uploadDir . $targetFile . ".<br />If you are concerned that the file was not processed properly, please contact support</span>");
			//break up the file name with ., add an increment in parentheses
			$arrFile = explode(".", $_FILES['Filedata']['name'][$int]);
			
			$suffix = "_" . date("ymjGis");
			$arrFile[count($arrFile)-1] = $suffix . "." .  $arrFile[count($arrFile)-1];
			
			$targetFile = implode("", $arrFile);
	
			$document_counter++;
		}
		$thumbFile = str_replace(".PDF", ".jpg", $targetFile);
		$thumbFile = str_replace(".pdf", ".jpg", $targetFile);
		$uploadedFile = strtolower($targetFile);

		$targetFile = $uploadDir. $targetFile;
		if ($_SERVER['REMOTE_ADDR']=='47.154.251.216') {
			//die($targetFile);
		}
		//$targetFile = strtolower($targetFile);
		$arrFile = explode(".", $targetFile);
		// Validate the filetype
		$fileParts = pathinfo($_FILES['Filedata']['name'][$int]);
		if (in_array(strtolower($fileParts['extension']), $fileTypes)) {
			// Save the file
			if(move_uploaded_file($tempFile, $targetFile)){
				//echo $targetFile.' TargetFile';
			}else{
				echo $targetFile.' ERROR '. $_FILES['Filedata']['error'][$int];
			}
			
			// Google Drive Upload Implementation - 2021-10-12 09:18:00
			$accessToken = $_COOKIE['g_access_token'];
			$saveFileName = $uploadedFile;
			
			if(isset($accessToken) && $accessToken != 'Authorize') {
				
				$fileIkase = checkFileExist($accessToken, "name='iKase'");
				$ikaseFolderId = $fileIkase['files'][0]['id'];
				
				if(isset($ikaseFolderId) && !empty($ikaseFolderId)){
					$ikaseParentId = $ikaseFolderId;
				}else{
					$qParam = "{\"name\": \"iKase\", \"mimeType\": \"application/vnd.google-apps.folder\"}\r\n";
					$createIkase = createDriveFolder($accessToken, $qParam);
					$ikaseParentId = $createIkase['id'];
				}
				
				$batchscanCaseId = checkFileExist($accessToken, "name='batchscan' and '".$ikaseParentId."' in parents");
				$batchscanFolderId = $batchscanCaseId['files'][0]['id'];
				
				if(isset($batchscanFolderId) && !empty($batchscanFolderId)){
					$ikaseCaseParentId = $batchscanFolderId;
				}else{
					$qParam = "{'name':'batchscan','mimeType':'application/vnd.google-apps.folder','parents':['".$ikaseParentId."']}\r\n";
					$createIkaseCase = createDriveFolder($accessToken, $qParam);
					$ikaseCaseParentId = $createIkaseCase['id'];
				}
				$fileTmpNm = file_get_contents($targetFile);
				uploadFileGDrive($fileTmpNm, $saveFileName, $ikaseCaseParentId, $accessToken);
			}
			
			
			$arrFileDetails = explode("\\", $targetFile);
			$targetFile = $arrFileDetails[count($arrFileDetails) - 1];
			
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$finish_time = $time;
			$total_time = round(($finish_time - $header_start_time), 4);
			
			//add to the queue
			$stored_file = $uploadDir . $targetFile;

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
        //$batchscan_id = $db->lastInsertId();
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
		echo "<br />It will now be processed, and you will be notified when it is ready. <br /><br />
";
		echo "</div>";
	//}
	
	echo "<br /><div style='color:" . $color . ";font-family:arial'>Return to <a href='ikase_form.php' style='color:" . $color . ";font-family:arial'>Batchscan Import Form</a>.</div>";
} else {
	die("No files were uploaded");
}

//Google Drive Implementation 2021-10-12 12:30 PM
function createDriveFolder($accessToken, $qParam){
	$ch = curl_init();
	$options = [
		CURLOPT_URL =>  "https://www.googleapis.com/drive/v3/files",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_HTTPHEADER => [
			'Authorization:Bearer ' . $accessToken,
			'Accept: application/json',
			'Content-Type: application/json',
		],
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_POSTFIELDS => $qParam,
		CURLOPT_SSL_VERIFYHOST => 0,
	];
	
	curl_setopt_array($ch, $options);
	$result = curl_exec($ch);
	curl_close ($ch);
	
	$resultJ = json_decode($result, true);
	return $resultJ;
}


function checkFileExist($accessToken, $qParam){
	$ch = curl_init();
	$qParam = urlencode($qParam);
	$options = [
		CURLOPT_URL =>  "https://www.googleapis.com/drive/v3/files?q=".$qParam,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HTTPHEADER => [
			'Authorization:Bearer ' . $accessToken,
			'Accept: application/json',
			'Content-Type: application/json',
		],
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => 0,
	];
	curl_setopt_array($ch, $options);
	$result = curl_exec($ch);
	curl_close ($ch);
	
	$resultJ = json_decode($result, true);
	return $resultJ;
}

function uploadFileGDrive($uploaded_file, $saveFileName, $ikaseCaseParentId, $accessToken){
	$fileTmpNm = $uploaded_file;
	$boundary = "xxxxxxxxxx";
	$data = "--" . $boundary . "\r\n";
	$data .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
	$data .= "{'name':'" .$saveFileName. "','parents':['".$ikaseCaseParentId."']}\r\n";
	$data .= "--" . $boundary . "\r\n";
	$data .= "Content-Transfer-Encoding: base64\r\n\r\n";
	$data .= base64_encode($fileTmpNm);
	$data .= "\r\n--" . $boundary . "--";
			
	$ch = curl_init();
	$options = [
		CURLOPT_URL =>  'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart',
		CURLOPT_POST => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POSTFIELDS => $data,
		CURLOPT_HTTPHEADER => [
			'Authorization:Bearer ' . $accessToken,
			'Accept: application/json',
			'Content-Type:multipart/related; boundary=' . $boundary
		],
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => 0,
	];
	
	curl_setopt_array($ch, $options);
	$result = curl_exec($ch);
	curl_close ($ch);
}
// #END# Google Drive Implementation 2021-10-12 12:30 PM
?>
<script type="application/x-javascript">
parent.currentBatchscanID("<?php echo $batchscan_id; ?>");
</script>
