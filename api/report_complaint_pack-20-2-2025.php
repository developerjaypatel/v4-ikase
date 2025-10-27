<?php
use Slim\Routing\RouteCollectorProxy;
$app->group('', function (RouteCollectorProxy $app) {
//posts
$app->post('/complaint/add', 'addComplaint');
$app->post('/complaint/status_update', 'complaintStatusUpdate');
$app->post('/complaint/read_flag', 'readFlagUpdate');
$app->post('/complaint/check_issue_attention', 'checkIssueAttention');
})->add(\Api\Middleware\Authorize::class);

function addComplaint() {
	session_write_close();
	
	/*------- start attachment upload ---*/
	$attachment = '';
	$target_dir = "../complaints_uploads/";
	
	if(isset($_FILES["send_document_id"]) && !empty($_FILES["send_document_id"]["name"]))
	{
		$file_name = rand(10000,10000000) . "_" . basename($_FILES["send_document_id"]["name"]);
		$target_file = $target_dir . $file_name;
		$uploadOk = 1;
		$fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

		// Check if file already exists
		if (file_exists($target_file)) {
		  echo "Sorry, file already exists.";
		  $uploadOk = 0;
		  exit;
		}

		// Check file size
		if ($_FILES["send_document_id"]["size"] > 50000000) {
		  echo "Sorry, your file is too large.";
		  $uploadOk = 0;
		  exit;
		}

		// Allow certain file formats
		if($fileType != "jpg" && $fileType != "png" && $fileType != "jpeg"
		&& $fileType != "gif" && $fileType != "pdf" && $fileType != "mp4") {
		  echo "Sorry, only JPG, JPEG, PNG , GIF, PDF & MP4 files are allowed.";
		  $uploadOk = 0;
		  exit;
		}

		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
		  echo "Sorry, your file was not uploaded.";
		  exit;
		// if everything is ok, try to upload file
		} else {
		  if (move_uploaded_file($_FILES["send_document_id"]["tmp_name"], $target_file)) {
			$attachment = "complaints_uploads/" . $file_name;
		  } else {
			echo "Sorry, there was an error uploading your file.";
			exit;
		  }
		}	
	}
	/*---- end attachment upload ---*/

	$MySqlHostname = "127.0.0.1";
	$MySqlUsername = "root";
	$MySqlPassword = "admin527#";

	$dateandtime = $_POST['event_dateandtimeInput'];
	$dateandtime = date("Y-m-d H:i:s", strtotime($dateandtime));
	
	if(isset($_SESSION['user_customer_id']))
	{
		$user_customer_id = $_SESSION['user_customer_id'];
	}
	else
	{
		$user_customer_id = 0;
	}
	
	if(isset($_SESSION['user_plain_id']))
	{
		$user_plain_id = $_SESSION['user_plain_id'];
	}
	else
	{
		$user_plain_id = 0;
	}
	
	if(isset($_SESSION['user_logon']))
	{
		$user_logon = $_SESSION['user_logon'];
	}
	else
	{
		$user_logon = 0;
	}
	
	$case_idInput = 0;
	$case_name = "";
	if(!empty($_POST['case_idInput']) || $_POST['case_idInput']!="")
	{
		$case_idInput = $_POST['case_idInput'];

		// --- case name ----
		$conn = new mysqli($MySqlHostname, $MySqlUsername, $MySqlPassword);
		$query = "select customer_id,data_source from ikase.cse_customer";

		$result = $conn->query($query);

		if($result->num_rows > 0)
		{
			$customer_db = array();
			while($row = $result->fetch_array())
			{
				if($row["data_source"]=="")
				{
					$customer_db[$row["customer_id"]] = "ikase";
				}
				else
				{
					$customer_db[$row["customer_id"]] = "ikase_" . $row["data_source"];
				}
			}
		}

		$query = "select case_name from ". $customer_db[$user_customer_id] .".cse_case where case_id=" . $case_idInput;
		$result = $conn->query($query);

		if($result->num_rows > 0)
		{
			while($row_case = $result->fetch_array())
			{
				$case_name = $row_case['case_name'];
			}
		}

		// end case name ----
	}
	else
	{
		$case_idInput = 0;
		$case_name = "";
	}
	
	if(isset($_POST['event_titleInput']))
	{
		$event_titleInput = $_POST['event_titleInput'];
	}
	else
	{
		$event_titleInput = 0;
	}
	$event_titleInput = str_replace("'","&apos;",$event_titleInput);
	
	if(isset($_POST['event_dateandtimeInput']))
	{
		$event_dateandtimeInput = $_POST['event_dateandtimeInput'];
	}
	else
	{
		$event_dateandtimeInput = 0;
	}
	
	if(isset($_POST['event_descriptionInput']))
	{
		$event_descriptionInput = $_POST['event_descriptionInput'];
	}
	else
	{
		$event_descriptionInput = 0;
	}
	$event_descriptionInput = str_replace("'","&apos;",$event_descriptionInput);
	
	
	if(isset($_POST['event_fromInput']))
	{
		$event_fromInput = $_POST['event_fromInput'];
	}
	else
	{
		$event_fromInput = 0;
	}
	
	if(isset($_POST['event_priorityInput']))
	{
		$event_priorityInput = $_POST['event_priorityInput'];
	}
	else
	{
		$event_priorityInput = 0;
	}
	
	$db = "ikase";
	
	$sql = "INSERT INTO `cse_complaint` (`customer_id`, `user_id`, `user_logon`, `case_id`, `subject`, `issue_dateandtime`, `attachment`, `details`, `entered_by`, `priority`, `status`, `case_name`) VALUES(";
	$sql .= $user_customer_id . ",";
	$sql .= $user_plain_id . ",'";
	$sql .= $user_logon . "',";
	$sql .= $case_idInput . ",'";
	$sql .= $event_titleInput . "','";
	$sql .= $dateandtime . "','";
	$sql .= $attachment . "','";	// attachment code
	$sql .= $event_descriptionInput . "','";
	$sql .= $event_fromInput . "','";	
	$sql .= $event_priorityInput . "','open','";
	$sql .= $case_name . "')";
	
	//echo $sql;
	//exit;
	$conn = new mysqli($MySqlHostname, $MySqlUsername, $MySqlPassword, $db);
	if (mysqli_query($conn, $sql))
	{		
		$new_id = mysqli_insert_id($conn);
		
		if($new_id)
		{
			echo "Thank you for reporting the issue! Your complaint ID is #". $new_id .". We’ll look into it shortly."; 
		}
		else
		{
			echo "Sorry, try again later...";
		}
		//echo json_encode(array("id"=>$new_id));
		
	} 
	else 
	{
		echo "Error: " . $conn->error;
	}
}

function complaintStatusUpdate() {
	session_write_close();
	
	$MySqlHostname = "127.0.0.1";
	$MySqlUsername = "root";
	$MySqlPassword = "admin527#";
	
	$db = "ikase";
	$conn = new mysqli($MySqlHostname, $MySqlUsername, $MySqlPassword, $db);
	$sql = "update cse_complaint set is_read='N', status='". $_POST['status'] ."' where complaint_id = " . $_POST['complaint_id'];

	if (mysqli_query($conn, $sql))
	{
		echo "Done!";
	}
	else
	{
		echo "Error! Please try again!";
	}
}

function readFlagUpdate() {
	session_write_close();
	
	$MySqlHostname = "127.0.0.1";
	$MySqlUsername = "root";
	$MySqlPassword = "admin527#";
	
	$db = "ikase";
	$conn = new mysqli($MySqlHostname, $MySqlUsername, $MySqlPassword, $db);
	$sql = "update cse_complaint set is_read='Y' where complaint_id = " . $_POST['complaint_id'];

	if (mysqli_query($conn, $sql))
	{
		echo "Done!";
	}
	else
	{
		echo "Error! Please try again!";
	}
}

function checkIssueAttention(){
	session_write_close();
	if(isset($_SESSION['user_plain_id']))
	{
		$MySqlHostname = "127.0.0.1";
		$MySqlUsername = "root";
		$MySqlPassword = "admin527#";
		
		$db = "ikase";
		$conn = new mysqli($MySqlHostname, $MySqlUsername, $MySqlPassword, $db);
		$sql = "select is_read from cse_complaint where is_read='N' and user_id = " . $_SESSION['user_plain_id'];

		$result = mysqli_query($conn, $sql);

		echo mysqli_num_rows($result);
	}
	else
	{
		echo 0;
	}
}
?>