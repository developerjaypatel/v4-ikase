<?php
//Set no caching
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

header("strict-transport-security: max-age=600");
header('X-Frame-Options: SAMEORIGIN');
header("X-XSS-Protection: 1; mode=block");

error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', '1');

require_once('../shared/legacy_session.php');
include("../api/connection.php");

function getCustomerInfo() {
	$sql = "SELECT cus.*
		FROM `ikase`.`cse_customer` cus 
		WHERE cus.customer_id = " . $_SESSION['user_customer_id'];
	//die($sql);
	try {
		$stmt = DB::run($sql);
		$customer = $stmt->fetchObject();
		//die($sql);

        return $customer;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

if($_SERVER["HTTPS"]=="off") {
	
	header("location:https://v4.ikase.org" . $_SERVER['REQUEST_URI']);
}

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	//die(print_r($_SESSION));
	//header("location:../index.php");
	die("<script language='javascript'>parent.location.href='../index.php'</script>");
}

session_write_close();

$cus_id = $_SESSION['user_customer_id'];
$case_id = passed_var("case_id", "GET");
$injury_id = passed_var("injury_id", "GET");
$jetfile_id = passed_var("jetfile_id", "GET");

if ($case_id == "" || $case_id < 0 || !is_numeric($case_id)) {
	die("<script language='javascript'>parent.location.href='app_1_2.php?case_id=" . $case_id . "&injury_id=" . $injury_id . "'</script>");
}
if ($injury_id == "" || $injury_id < 0 || !is_numeric($injury_id)) {
	die("<script language='javascript'>parent.location.href='app_1_2.php?case_id=" . $case_id . "&injury_id=" . $injury_id . "'</script>");
}
//for page 2, we need to have saved page 1
if ($jetfile_id == "" || $jetfile_id < 0 || !is_numeric($jetfile_id)) {
	die("<script language='javascript'>parent.location.href='app_1_2.php?case_id=" . $case_id . "&injury_id=" . $injury_id . "'</script>");
}

include("jetfile_kase.php");

//die(print_r($kase));

$url = "https://www.cajetfile.com/ikase/receive.php";
	
//let's look up the customer to see if they have a jetfile id
$customer = getCustomerInfo();

$fields = array("cus_id"=>$customer->jetfile_id, 'case_id'=>$kase->id, 'data'=>$kase->jetfile_info);

//die(print_r($fields));
$result = post_curl($url, $fields);
//return the json directly back to the view
die($result);
?>
