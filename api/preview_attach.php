<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', '1');

$http_origin = $_SERVER['HTTP_ORIGIN'];

if ($http_origin == "https://www.matrixdocuments.com" || $http_origin == "https://www.cajetfile.com" || $http_origin == "https://www.ikase.xyz") {  
    header("Access-Control-Allow-Origin: $http_origin");
}

include("connection.php");
require_once('../shared/legacy_session.php');

if (!isset($_SESSION["user_customer_id"])) {
	die("no go");
}
$file = passed_var("file", "get");
$file = urldecode($file);
$case_id = passed_var("case_id", "get");
$arrFile = explode(".", $file);
$extension = $arrFile[count($arrFile) - 1];
$customer_id = $_SESSION["user_customer_id"];

$db = getConnection();
//i need this to allow to proceed
$batchscan_id = "";

include("customer_lookup.php");

$path = "../uploads/" . $customer_id . "/" . $file;


if (file_exists($path)) {
	header("location:" . $path);
	die();
} else {
	//maybe it's a webmail_previews
	$path = "../uploads/" . $customer_id . "/webmail_previews/" . $_SESSION["user_plain_id"] . "/" . $file;
	if (file_exists($path)) {
		header("location:" . $path);
		die();
	} else {
		//might be on the main customer folder
		$path = "../uploads/" . $customer_id . "/" .  $file;
		if (file_exists($path)) {
			return $path;
			die();
		}
		if ($case_id!="") {
			$path = "../uploads/" . $customer_id . "/" . $case_id . "/" .  $file;
			//die($path);
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
					} else {
						//might just be in the customer folder
						$path = "../uploads/" . $customer_id . "/" .  $file;
						if (file_exists($path)) {
							header("location:" . $path);
							die();
						} else {
							//might just be in the customer folder
							$user_id = $_SESSION["user_plain_id"];
							$path = "../uploads/" . $customer_id . "/webmail_previews/" . $user_id . "/" . $file;
							if (file_exists($path)) {
								header("location:" . $path);
								die();
							} else {
								//last ditch
								//is the file in xyz
								$url = "https://www.ikase.xyz/ikase/gmail/ui/attachments/" . $customer_id . "/" . $user_id . "/" . $file;
								//die($url);
								if (url_exists($url)) {
									//die($_SERVER['PHP_SELF']);
									//yes? get it
									$url = "https://www.ikase.xyz/ikase/gmail/ui/get_document.php";
									$fields = array("filename"=>$file, 'case_id'=>-1, 'customer_id'=>$customer_id, 'user_id'=>$user_id);;
									//die(print_r($fields));
									$result = post_curl($url, $fields);
									$json = json_decode($result);
									//die($result);
									if ($json->success) {
										$path = "preview_attach.php?" . $_SERVER['QUERY_STRING'];
										//die($path);
										echo("
										Retrieving attachment from mail server...
										<script type='application/javascript'>
											setTimeout(function() {
												document.location.href = '" . $path . "';
											}, 3000);
										</script>");
										die();
									}
								}
							}
						}
					}
				}
			}
		}
	}
}
die($path . " was not found in our system");
