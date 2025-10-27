<?php 
include('docusent/cls_docucents.php');

//solulab code start - 05-06-2019
/* upload document to docucents */
if(isset($_POST['call_intension']) && $_POST['call_intension']=="letter_upload"){
	$file = "D:/uploads/1100/513/eams_forms/app_cover_final.pdf";
    $caseid=$_POST['caseid'];
    $document_id=$_POST['document_id'];
    $cusid=$_POST['cusid'];
	$api_key ="4c56ee9a5d1d4093:e42bca0f386157aae42bca0f386157aa";
	if(!file_exists($file)){
		$vendor = file_exists($file);
		echo json_encode($vendor);
		die;
	}
	if($api_key){
	$obj = new docucents("cmd",$api_key);
	$billingcode = $document_id.uniqid();
	$poswording = "Document uploaded to docucents";
	$obj->Submittals_AddForDelivery($_POST['caseid'].uniqid(),$billingcode , "LETTERID_-" . $document_id.uniqid(), "None", "comment:" . uniqid(), $poswording);
	$obj->PartyData_AddForDelivery("ikase.org", "testSolulab2", "testSolulab2", "", 'address1', "", "testSolula2b", "testSolulab2", "test", "test");
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