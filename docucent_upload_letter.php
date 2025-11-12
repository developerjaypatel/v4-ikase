<?php 
include('docusent/cls_docucents.php');
include ("api/connection.php");
//solulab code start - 05-06-2019
/* upload document to docucents */
if(isset($_POST['call_intension']) && $_POST['call_intension']=="letter_upload"){
	//$file = "D:/uploads/1302/9487/eams_forms/app_cover_final.pdf";
	
	//$file = 'D:/uploads/1033/templates/1.10 Day Letter.docx';
	$file = $_POST['letterpath'];

	//$target = str_replace('.docx', '.pdf', $file);
	$target = preg_replace('/\.(docx?|DOCX?)$/', '.pdf', $file);
	$apiSecret = 'ltqshaZEZWl3GDuUgRJZ7Eh2OTfqXDAl'; // get free secret from convertapi.com
	$url = 'https://v2.convertapi.com/convert/doc/to/pdf?Secret=' . $apiSecret;

	$cfile = new CURLFile($file);
	$post = ['File' => $cfile];

	$ch = curl_init();
	curl_setopt_array($ch, [
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => $post,
		CURLOPT_SSL_VERIFYHOST => 0,
		CURLOPT_SSL_VERIFYPEER => 0
	]);

	$response = curl_exec($ch);
	curl_close($ch);

	file_put_contents($target, $response);
	$result = json_decode($response, true);
	
	
	$pdfUrl = $result['Files'][0]['Url'];
	$result['Files'][0]['Url'];

	$pdfData = file_get_contents($pdfUrl);
	print_r($pdfData);die;
	if ($pdfData === false || strlen($pdfData) < 100) {
		die("❌ Failed to download or empty PDF content from: $pdfUrl");
	}

	// ✅ Save binary data to PDF file
	if (file_put_contents($target, $pdfData) === false) {
		die("❌ Failed to save PDF to $target");
	}

	//echo "✅ PDF created successfully: $target";						
	die($response);
	//print_r($_REQUEST);die;
    $caseid=$_POST['caseid'];
    $document_id=$_POST['document_id'];
    $cusid=$_POST['cusid'];
	
	$api_key = getCustomerDocucentsAPIKey($cusid);
	
	
	if(!file_exists($file)){
		$vendor = file_exists($file);
		echo json_encode($vendor);
		die;
	}
	//print_r($api_key);die;
	if($api_key)
	{
		$obj = new docucents("cmd",$api_key,$cusid);
		$billingcode = $document_id.uniqid();
		$poswording = "Document uploaded to docucents";
		$obj->Submittals_AddForDelivery($_POST['caseid'].uniqid(),$billingcode , "LETTERID_-" . $document_id.uniqid(), "None", "comment:" . uniqid(), $poswording);
		$obj->PartyData_AddForDelivery($_SERVER['SERVER_NAME'], "testSolulab2", "testSolulab2", "", 'address1', "", "testSolula2b", "testSolulab2", "test", "test");
		$origpdf = file_get_contents($file);
		$filecontent = base64_encode($origpdf);
		$filedate = date("Ymd", time());

		xmlrpc_set_type($filecontent, "base64");

		xmlrpc_set_type($filedate, "datetime");
		$attachment = array(
			"file_name" => "APPFULLPDF_".$_POST['document_id']."_".$_POST['customer_id']."_" . rand(1, 5) . ".pdf",
			"type" => "APPFULLPDF_-" . uniqid(),
			"title" => "APPFULLPDF_-" . uniqid(),
			"unit" => "ADJ",
			"date" => $filedate,
			"author" => "author-".$_POST['customer_id']."-" . uniqid(),
			"base64" => $filecontent
		);
		$temp = $obj->AddAttachment($attachment, false);

		$post_result = $obj->SetSubmittalStatus();
		
		/* $request1 = xmlrpc_encode_request('system.listMethods', []);
		$response1 = $this->do_call($this->buildhost(), $request1);
		echo "<pre>" . htmlspecialchars($response1) . "</pre>";die; */
		print_r($temp);
		die;
		try{	
			include ("api/connection.php");
			$sql = "INSERT INTO cse_docucents (`document_id`, `case_id`, `billing_code`, `pos_wording`, `customer_id`, `vendor_submittal_id`, `docucents_upload_date`, `document_submitted_by`) VALUES ('".$_POST['document_id']."','".$caseid . "', '" . $billingcode . "','Letter uploaded to docucents','" . $cusid . "','" .  $obj->vendor_submittal_id . "','" . date("Y-m-d")." ".date("h:i:s"). "','Admin')";
			$db = getConnection();
			//$stmt = $db->query($sql);
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			
			$vendor = "Document Uploaded Successfully!!";
		} catch(PDOException $e) {	
			$vendor="Please try again"; 
		}	
		echo json_encode($vendor);
	}else{
		$vendor ="Docucents API key Invalid";
		echo json_encode($vendor);
	}
}




// AP KEY SET IN DB
if(isset($_POST['docucent_key_submit'])){
	$apiKey = $_POST['apikey'];
	$username=$_POST['username'];
	
$check="SELECT * FROM docucents_api_key WHERE cus_id = '$username'";
$rs = mysql_query($check,$r_link) or die("unable to GET data");
$data = mysql_num_rows($rs);
echo $data;
if($data>=1) {
	try{
		$query = "UPDATE docucents_api_key SET `key` = '" . $apiKey . "' WHERE cus_id = " . $username;
		$result = mysql_query($query, $r_link) ;
		echo mysql_error();
		if($result==1){
			$_SESSION['docusentapisuccess']="1";
		}elseif($result==0){
			$_SESSION['docusentapisuccess']="0";
		}
	} catch(PDOException $e) {	
		$_SESSION['docusentapisuccess']="0";
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
else{
	try{
		$query = "INSERT INTO docucents_api_key (`cus_id`, `key`, `date`) 
					VALUES ('".$username . "',
					 '" . $apiKey . "',
					 '" . date("d-m-Y H:i:s") . "')";
		$result = mysql_query($query, $r_link) or die("unable to insert data");
		if($result==1){
			$_SESSION['docusentapisuccess']="1";
		}elseif($result==0){
			$_SESSION['docusentapisuccess']="0";
		}
	} catch(PDOException $e) {	
		$_SESSION['docusentapisuccess']="0";
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
$url=$_POST['url'];
header("Location:$url");
}
//solulab code end - 05-06-2019
?>