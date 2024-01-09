<?php
use Api\Bootstrap;
use Api\Middleware\Authorize;

//region CORS check
if (in_array($_SERVER['HTTP_ORIGIN'], ['https://www.matrixdocuments.com', 'https://www.cajetfile.com', 'https://www.ikase.xyz'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
}
//endregion

Bootstrap::session();

include('connection.php');

$app = Bootstrap::slim('/api');
Bootstrap::addCommonCalls(true, false, 'ikase.cse_');

$app->get('/killsleep', 'killSleep');

killSleep();

function killSleep() {
	$sql = "select concat('KILL ',id,';') killswitch from information_schema.processlist 
	WHERE user='root' 
	AND `COMMAND` = 'Sleep'
	AND `TIME` > 2";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$sleeps = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		foreach($sleeps as $sleep) {
			$kill_command = $sleep->killswitch;
			//echo $kill_command . "<br />";
			$stmt = $db->prepare($kill_command);
			$stmt->execute();
		}
		$stmt = null; $db = null;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	//echo json_encode($error);
	}
	
	$sql = "select concat('KILL ',id,';') killswitch from information_schema.processlist where user='root' 
AND `COMMAND` = 'Query' AND `TIME` > 33";
	//if ($_SESSION["user_customer_id"] == "1121") {
		//$sql .= "AND `DB` LIKE '%ikase_goldberg3%' AND `TIME` > 45";
	//} else {
		//$sql .= "AND `TIME` > 29";
	//}
//AND `DB` LIKE '%ikase%'	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$sleeps = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		foreach($sleeps as $sleep) {
			$kill_command = $sleep->killswitch;
			//echo $kill_command . "<br />";
			$stmt = $db->prepare($kill_command);
			$stmt->execute();
		}
		$stmt = null; $db = null;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	//echo json_encode($error);
	}
}

//region API imports and Employee endpoints
$app->group('/employees', function (Slim\Routing\RouteCollectorProxy $app) {
	$app->get('', 'getEmployees');
	$app->get('/{id}',	'getEmployee');
	$app->post('/update', 'updateEmployee');
	$app->post('/documents/add', 'addEmployeeDocument');

	$app->get('/{id}/reports', 'getReports')->add(Authorize::class);
	$app->get('/search/{query}', 'getEmployeesByName')->add(Authorize::class);

});
$app->get('/employees_type', 'typeEmployees')->add(Authorize::class);

//if ($_SERVER['REMOTE_ADDR']=='47.153.51.181') {
//for kases
$uri = substr($_SERVER['REQUEST_URI'], 4);

//die($uri);
$blnSpecificPack = false;
$pack = "";
switch($uri){
	case "/openkases":
	case "/kases":
	case "/closedkases":
	case "/kases/matrix":
	case "/kases/recent":
		$blnSpecificPack = true;
		$pack = "kases_pack.php";
		break;
	//batchscans
	case "/batchscans/new":
	case "/batchscans/track":
	case "/batchscans/lasttrack":
	case "/batchscans/updatequeue":
	case "/batchscans/open":
	case "/batchscans/queue":
	case "/batchscans/preprocess":
	case "/batchscans/stitchprocess":
	case "/batchscans/remoteadd":
		$blnSpecificPack = true;
		$pack = "batchscan_pack.php";
		break;
	//documents
	case "/documents/remoteadd":
		$blnSpecificPack = true;
		$pack = "documents_pack.php";
		break;
	//messages
	case "/thread/inbox":
	case "/inboxnew":
	case "/inbox":
		$blnSpecificPack = true;
		$pack = "messages_pack.php";
		break;
	//tasks
	case "/taskinboxnew":
		$blnSpecificPack = true;
		$pack = "tasks_pack.php";
		break;
}

//get requests
if (!$blnSpecificPack) {
	if ($_SERVER['REQUEST_METHOD']=='GET') {
		//echo "pack2:" . strpos($uri, "/kase") . "<br />";
		if (strpos($uri, "/kases/search/")===0) {
			$blnSpecificPack = true;
			$pack = "kases_pack.php";
		}
		if (strpos($uri, "/kases/search/")===0) {
			$blnSpecificPack = true;
			$pack = "kases_pack.php";
		}
		if (strpos($uri, "/kases/matrix/")===0) {
			$blnSpecificPack = true;
			$pack = "kases_pack.php";
		}
		//documents
		if (strpos($uri, "/stacks/")===0) {
			$blnSpecificPack = true;
			$pack = "documents_pack.php";
		}
		//tasks
		if (strpos($uri, "/taskdayinbox/")===0) {
			$blnSpecificPack = true;
			$pack = "tasks_pack.php";
		}
		if (strpos($uri, "/taskcaseinbox")===0) {
			$blnSpecificPack = true;
			$pack = "tasks_pack.php";
		}
		//notes
		if (strpos($uri, "/notes/kases/")===0) {
			$blnSpecificPack = true;
			$pack = "notes_pack.php";
		}
		if (strpos($uri, "notes/quick/")===0) {
			$blnSpecificPack = true;
			$pack = "notes_pack.php";
		}
		if (strpos($uri, "/notes/dash/")===0) {
			$blnSpecificPack = true;
			$pack = "notes_pack.php";
		}
		//activity
		if (strpos($uri, "/activity/kases/")===0) {
			$blnSpecificPack = true;
			$pack = "activity_pack.php";
		}
		//users
		if (strpos($uri, "/customerinfo/")===0) {
			$blnSpecificPack = true;
			$pack = "users_pack.php";
		}
	}
}

//die("pack:" . $pack);
/*
} else {
	$blnSpecificPack = false;
}
*/

if ($blnSpecificPack && $pack!="") {
	include($pack);
	if ($pack!="kases_pack.php") {
		//we going to need kase info
		include("kases_pack.php");
	}
	if ($pack!="users_pack.php") {
		//we going to need user info
		include("users_pack.php");
	}
} else {
	include("accidents_pack.php");
	include("financial_pack.php");
	include("activity_pack.php");
	include("adhoc_pack.php");

	include("attorney_search_pack.php");
	include("batchscan_pack.php");
	include("bodypart_pack.php");
	include("calendar_pack.php");
	include("chats_pack.php");
	include("checks_pack.php");
	include("contact_pack.php");
	include("corporations_pack.php");
	include("documents_pack.php");

	include("eams_carriers_pack.php");
	include("eams_reps_pack.php");
	include("email_inbox.php");
	include("email_pack.php");
	include("events_pack.php");
	include("fee_pack.php");
	include("forms_pack.php");
	include("encrypt_functions.php");
	include("homemedical_pack.php");
	include("injurys_pack.php");
	include("injury_numbers_pack.php");
	include("jetfile.php");
	include("kases_pack.php");
	include("letters_pack.php");
	include("lien_pack.php");
	include("messages_pack.php");
	include("empty_buffer.php");
	include("exam_pack.php");
	include("negotiation_pack.php");
	include("notes_pack.php");
	include("parties_pack.php");
	include("pdf_pack.php");
	include("persons_pack.php");
	include("personal_injury_pack.php");
	include("new_legal_pack.php");
	include("coa_pack.php");
	//encrypted person, must finish
	include("personx_pack.php");
	include("billing_pack.php");
	include("reminders_pack.php");
	include("rx_pack.php");
	include("scanfiles_pack.php");
	include("scrape_pack.php");
	include("setting_pack.php");
	include("settlement_pack.php");
	include("signature_pack.php");
	include("sms_pack.php");
	include("tasks_pack.php");
	include("users_pack.php");
	include("vservices_pack.php");
	include("workflow_pack.php");
}
//endregion

//FIXME: /relogin should... reuse... the login code?
$app->post('/relogin', function () {
	$_SESSION["need_password"] = false;
	if(!empty($_POST['user_id'])) {
		$blnLoggedIn = false;
		$user_id = passed_var('user_id','post');
		$sess_id = passed_var('sess_id','post');
		$customer_id = passed_var('customer_id','post');

		// load credentials from the database.
		$sql = "SELECT TIMEDIFF(NOW(), user.dateandtime) timediff, `user`.*, cus.cus_name customer_name, cus.inhouse_id, cus.jetfile_id, cus.eams_no,
		cus.pwd `token`,
		CONCAT(cus.cus_street, '<br> ', cus.cus_city, ', ', cus.cus_state, ' ', cus.cus_zip) customer_address,
		cus.data_source, cus.data_path, cus.cus_phone, cus.cus_email, cus.cus_type
		FROM `ikase`.`cse_user` `user`
		INNER JOIN `ikase`.`cse_customer` `cus`
		ON `user`.customer_id = `cus`.customer_id
		WHERE `user`.user_id = :user_id
		AND user.sess_id = :sess_id
		AND user.customer_id = :customer_id
		AND user.ip_address = '" . $_SERVER['REMOTE_ADDR'] . "'";

		//die($sql);

		//die($password . " -> " . encrypt( $password, $crypt_key));
		try {
			$db = getConnection();
			//$stmt = $db->query($sql);
			$stmt = $db->prepare($sql);
			$stmt->bindParam("user_id", $user_id);
			$stmt->bindParam("sess_id", $sess_id);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			$users = $stmt->fetchAll(PDO::FETCH_OBJ);
			//die(print_r($users));

			if (count($users)>0) {
				//check the timediff
				$timediff = $users[0]->timediff;
				$arrTimeDiff = explode(":", $timediff);
				$hours_diff = $arrTimeDiff[0];
				if ($hours_diff >= 10) {
					$error = array("success"=>false, "error"=> array("timediff"=>$hours_diff));
					echo json_encode($error);
					die();
				}
				//die(print_r($users));
				if ($users[0]->user_id > 0) {
					$blnLoggedIn = true;
					//store the user role
					switch($users[0]->user_type) {
						case 0:
							$role = "stranger";
							break;
						case 1:
							$role = "admin";
							break;
						case 2:
							$role = "user";
							break;
						case 3:
							$role = "masteradmin";
							break;
					}
					$user_name = passed_var('email','post');
					//give a session id, update the system
					$ip_address = $_SERVER['REMOTE_ADDR'];

					session_regenerate_id();
					$session_id = session_id();

					$sql = "UPDATE `ikase`.`cse_user`
					SET sess_id = '" . $session_id . "',
					ip_address = '" . $ip_address . "'
					WHERE user_uuid = '" . $users[0]->user_uuid . "'";
					try {
						//die(print_r($_SESSION));
						$stmt = DB::run($sql);

						$_SESSION['user'] = $session_id;
						$_SESSION['CREATED'] = time();
						$_SESSION['user_role'] = $role;
						$_SESSION['user_plain_id'] = $users[0]->user_id;
						$_SESSION['user_id'] = $users[0]->user_uuid;
						$_SESSION['user_name'] = $users[0]->user_name;
						$_SESSION['user_logon'] = $users[0]->user_logon;
						$_SESSION['user_job'] = $users[0]->job;
						$_SESSION['user_nickname'] = $users[0]->nickname;
						$_SESSION['user_customer_id'] = $users[0]->customer_id;
						$_SESSION['user_data_source'] = $users[0]->data_source;
						$_SESSION['user_data_path'] = $users[0]->data_path;
						$_SESSION['user_customer_name'] = $users[0]->customer_name;
						$_SESSION['user_customer_address'] = $users[0]->customer_address;
						$_SESSION['user_customer_phone'] = $users[0]->cus_phone;
						$_SESSION['user_customer_email'] = $users[0]->cus_email;
						$_SESSION['user_customer_type'] = $users[0]->cus_type;
						$_SESSION['user_inhouse_id'] = $users[0]->inhouse_id;
						$_SESSION['user_jetfile_id'] = $users[0]->jetfile_id;
						$_SESSION['customer_eams_no'] = $users[0]->eams_no;
						$_SESSION['user_email'] = $users[0]->user_email;
						$_SESSION['subscription_string'] = "&token=" . $users[0]->token . "." . $users[0]->user_uuid;
						$_SESSION['personal_calendar'] = $users[0]->personal_calendar;

						//track logins
						$sql ="INSERT INTO `ikase`.`cse_userlogin` (`user_name`,`user_uuid`,`status`,`ip_address`,`dateandtime`, `login_date`, `customer_id`)
						VALUES('" . addslashes($_SESSION['user_name']) . "','" . $_SESSION['user_id'] . "','INN','" . $ip_address . "','" . date("Y-m-d H:i") . "','" . date("Y-m-d") . "', " . $_SESSION['user_customer_id'] . ")";

						$stmt = DB::run($sql);

						$session_id = session_id();
						$filename = ROOT_PATH.'sessions'.DC.'data_' . $session_id  . '.txt';
						$_SESSION["time_stamp"] = date("m/d/y H:i:s");
						$_SESSION["login_type"] = "user";
						$fp = fopen($filename, 'w');
						fwrite($fp, json_encode($_SESSION));
						fclose($fp);

						echo json_encode(array("success"=>true, "sess_id"=>$session_id, "role"=>$role, "user_name"=>$users[0]->user_name, "user_customer_id"=>$users[0]->customer_id, "user_nickname"=>$users[0]->nickname, "session_id"=>$session_id));
					} catch (PDOException $e) {
						$error = array("error"=> array("text"=>$e->getMessage()));
						echo json_encode($error);
					}
				}
			}
		} catch(PDOException $e) {
			$error = array("success"=>false, "error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
		}

		if (!$blnLoggedIn) {
			$error = array("success"=>false, "reason"=>"not found", "sql"=>$sql);
			echo json_encode($error);
		}

	} else {
		$error = array("success"=>false, "error"=> array("text"=>"Username and Password are required."));
		echo json_encode($error);
	}
});

//UPDATE THEME 2021-12-21 10.29AM
$app->post('/themeupdate', function() {
	$userid = $_POST['user_id'];
	$themename = $_POST['themename'];
	$sql ="UPDATE ikase.cse_user SET theme = '".$themename."' WHERE user_id = '".$userid."'";
	$stmt = DB::run($sql);

	$success = array("success"=>true, "response" => "Theme updated successfully");
	echo json_encode($success);
});
//END UPDATE THEME 2021-12-21 10.29AM


//region Other random endpoints
$app->get('/quote', function() {
	$filename = "https://www.matrixdocuments.com/dis/pws/quicks/orders/quote.php?remote=";
	$quote = file_get_contents($filename);
	echo str_replace("-", "<br /><br /><span style='font-size:0.8em; font-style:italic'>", $quote) . "</span>";
});

//FIXME: wtf? are these three even used?
$app->get('/check/{name}', fn ($name) => ["valid" => verifySession(), "cus_id" => $_SESSION["user_customer_id"]]);
$app->get('/session/verify', function () {
	/*
	if ($_SESSION["user_role"] == "masteradmin") {
		$isValid = true;
	} else {
		$isValid = verifySession();
	}
	*/
	$isValid = true;
	echo json_encode(["valid"=>$isValid]);
});
function verifySession() {
	try {
		$db = getConnection();
		$stmt = $db->prepare("SELECT COUNT(*) FROM ikase.cse_user WHERE customer_id = :customer_id AND sess_id = :sess_id");
		$stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
		$stmt->bindParam("sess_id", $_SESSION["user"]);
		$stmt->execute();
		return (bool)$stmt->fetchColumn(0);

	} catch(PDOException $e) {
		//FIXME: silent error
		$error = ["error" => ["text" => $e->getMessage()]];
		//echo json_encode($error);
		return false;
	}
}

$app->get('/currentsession/{sess_id}', function ($sess_id) {
	try {
		$db = getConnection();
		$stmt = $db->query("SELECT user_id, customer_id FROM `ikase`.`cse_user`	WHERE sess_id = '" . $sess_id . "'");
		return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
	}
	catch (PDOException $e) {
		return ["error" => ["text" => $e->getMessage()]];
	}
});

$app->get('/checkzip/{query}', function ($query) {
	if (strlen($query) < 3) { //TODO: can be done by using {query:[0-9]{3,}} (will generate 404 instead of empty response)
		die();
	}

	//FIXME: typo - latitude
	$sql = "SELECT lat AS lattitude, lon AS longitude, city, county, state_prefix,
              state_name, area_code, time_zone
              FROM `ikase`.`zip_code`
              WHERE zip_code LIKE '" . $query . "%'";
	try {
		$stmt = DB::run($sql);
		$cities = $stmt->fetchObject();

		die(json_encode($cities));
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
})->add(Authorize::class);

$app->get('/levels', function () {
	return [
		'success' => true,
		'level' => (int)(in_array($_SESSION['user_role'], ['admin', 'masteradmin'])),
	];
})->add(Authorize::class);
//endregion

$app->run();

//FIXME: DRY!!!
function login_encrypt() {
	// code added for anti CSRF require for CWE-332 - CASA for Gmail app verification and apprval : 12-7-2023
	$token = array("","312#3","6$213","23!45","43%23","3*233","8@!54","1921&","87%4","977@3","65@15");
	if(array_search($_POST['csrftoken'], $token) == false)
	{
		$error = array("error"=>"405");
		echo json_encode($error);
		exit;
	}
	// end CSRF code
	$crypt_key = CRYPT_KEY;
    if(!empty($_POST['email']) && !empty($_POST['password'])) {
        $blnLoggedIn = false;
		$user_logon = passed_var('email','post');
		$password = passed_var('password','post');

		$_SESSION["need_password"] = ($password=="n1ck23" || $password=="password1");

		// load credentials from the database.
		$sql = "SELECT `user`.*, cus.cus_name customer_name, cus.pwd `token`,
		CONCAT(cus.cus_street, '<br> ', cus.cus_city, ', ', cus.cus_state, ' ', cus.cus_zip) customer_address,
		cus.inhouse_id, cus.jetfile_id, cus.eams_no, cus.cus_type,
		cus.data_source, cus.data_path, cus.cus_phone, cus.cus_email, cus.permissions,
		IFNULL(`restricted_clients`, '') `restricted_clients`
		FROM `ikase`.`cse_user` `user`
		INNER JOIN `ikase`.`cse_customer` `cus`
		ON `user`.customer_id = `cus`.customer_id
		LEFT OUTER JOIN (
			SELECT user_uuid, CONCAT('\'', GROUP_CONCAT(`corporation_uuid` SEPARATOR '\',\''), '\'') `restricted_clients`
            FROM `ikase`.`cse_user_corporation`
            WHERE `attribute_1` = 'restricted'
			GROUP BY `user_uuid`
		) `ucorp`
		ON `user`.`user_uuid` = `ucorp`.`user_uuid`
		WHERE `user`.user_logon = '" . $user_logon . "'";
		/*AND `user`.`activated` = 'Y'
		AND `user`.`pwd` = '" . encrypt( $password, $crypt_key) . "'";*/

		try {
			$db = getConnection();
			$stmt = $db->query($sql);
			$users = $stmt->fetchAll(PDO::FETCH_OBJ);
			$ip_address = $_SERVER['REMOTE_ADDR'];
			/*
			//WHITE LIST FILTER, WORKS, MUST BE OKAYED 3/6/2015
			if (count($users) > 0) {
				$customer_id = $users[0]->customer_id;
				//white list filter
				if ($customer_id == 1033) {
					$sql_customer = "SELECT cus_ip
					FROM  `ikase`.`cse_customer`
					WHERE customer_id = :customer_id";
					//die($sql_customer);
					$stmt = $db->prepare($sql_customer);
					$stmt->bindParam("customer_id", $customer_id);
					$stmt->execute();
					$customer = $stmt->fetchObject();
					//default, go in
					$blnCusIp = true;
					if ($customer->cus_ip!="") {
						//we have a white list UNLESS WE HAVE ANYTIME ACCESS
						$position_ip = strpos($customer->cus_ip, $ip_address);
						if (strpos($customer->cus_ip, $ip_address) === false) {
							$blnCusIp = false;
						}
					}
					if (!$blnCusIp) {
						$error = array("error"=> array("text"=>"no entry:" . $ip_address . "\r\nPlease contact your administrator."));
						echo json_encode($error);
						die();
					}
				}
			}
			*/

			if (count($users)>0) {
				if ($users[0]->user_id > 0) {
					$blnLoggedIn = true;
					//store the user role
					switch($users[0]->user_type) {
						case 0:
							$role = "stranger";
							break;
						case 1:
							$role = "admin";
							break;
						case 2:
							$role = "user";
							break;
						case 3:
							$role = "masteradmin";
							break;
					}
					$user_name = passed_var('email','post');
					//give a session id, update the system
					//$session_id = uniqid('iK')  . ".." . $ip_address;
					$session_id = session_id();

					$sql = "UPDATE `ikase`.`cse_user`
					SET sess_id = '" . $session_id . "',
					ip_address = '" . $ip_address . "', dateandtime = '" . date("Y-m-d H:i:s") . "'
					WHERE user_uuid = '" . $users[0]->user_uuid . "'";
					try {
						$stmt = DB::run($sql);

						$_SESSION['user'] = $session_id;
						$_SESSION['CREATED'] = time();
						$_SESSION['user_role'] = $role;
						$_SESSION['user_plain_id'] = $users[0]->user_id;
						$_SESSION['user_id'] = $users[0]->user_uuid;
						$_SESSION['user_name'] = $users[0]->user_name;
						$_SESSION['user_logon'] = $users[0]->user_logon;
						$_SESSION['user_job'] = $users[0]->job;
						$_SESSION['user_nickname'] = $users[0]->nickname;
						$_SESSION['user_customer_id'] = $users[0]->customer_id;
						$_SESSION['user_data_source'] = $users[0]->data_source;
						$_SESSION['user_data_path'] = $users[0]->data_path;
						$_SESSION['user_customer_name'] = $users[0]->customer_name;
						$_SESSION['user_customer_address'] = $users[0]->customer_address;
						$_SESSION['user_customer_email'] = $users[0]->cus_email;
						$_SESSION['user_customer_type'] = $users[0]->cus_type;
						$_SESSION['user_inhouse_id'] = $users[0]->inhouse_id;
						$_SESSION['user_jetfile_id'] = $users[0]->jetfile_id;
						$_SESSION['customer_eams_no'] = $users[0]->eams_no;

						$permissions = $users[0]->permissions;
						$blnBilling = (strpos($permissions, "b")!==false);
						$_SESSION['permissions_billing'] = $blnBilling;
						$_SESSION['user_customer_phone'] = $users[0]->cus_phone;
						$_SESSION['user_email'] = $users[0]->user_email;
						$_SESSION['subscription_string'] = "&token=" . $users[0]->token . "." . $users[0]->user_uuid;
						$_SESSION['personal_calendar'] = $users[0]->personal_calendar;
						$_SESSION['restricted_clients'] = $users[0]->restricted_clients;

						$_SESSION['timeout'] = time();

						//die(print_r($_SESSION));

						//track logins
						$sql ="INSERT INTO `ikase`.`cse_userlogin` (`user_name`,`user_uuid`,`status`,`ip_address`,`dateandtime`, `login_date`, `customer_id`)
						VALUES('" . addslashes($_SESSION['user_name']) . "','" . $_SESSION['user_id'] . "','IN','" . $ip_address . "','" . date("Y-m-d H:i") . "','" . date("Y-m-d") . "', " . $_SESSION['user_customer_id'] . ")";

						$stmt = DB::run($sql);
/*
if ($user_logon=="nancy@ryon"){

}
*/
						$session_id = session_id();
						$filename = ROOT_PATH.'sessions'.DC.'data_' . $session_id  . '.txt';
						$_SESSION["time_stamp"] = date("m/d/y H:i:s");
						$_SESSION["login_type"] = "regular_user";
						$fp = fopen($filename, 'w');
						fwrite($fp, json_encode($_SESSION));
						fclose($fp);

						echo json_encode(array("sess_id"=>$session_id, "role"=>$role, "user_id"=>$users[0]->user_id, "user_name"=>$users[0]->user_name, "user_customer_id"=>$users[0]->customer_id, "user_nickname"=>$users[0]->nickname, "session_id"=>$session_id, "filename"=>$filename));
					} catch (PDOException $e) {
						$error = array("error"=> array("text"=>$e->getMessage()));
						echo json_encode($error);
					}
				}
			}
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
		}

		if (!$blnLoggedIn) {
			//try logging in as an owner
			// load credentials from the database.
			$sql = "SELECT *
			FROM `ikase`.`cse_owner`
			WHERE admin_client = '" . $user_logon . "' AND `pwd` = '" . encrypt( $password, $crypt_key ) . "'";
			//die($sql);
			try {
				$db = getConnection();
				$stmt = $db->query($sql);
				$owners = $stmt->fetchAll(PDO::FETCH_OBJ);
				//die(print_r($owners));
				if(count($owners)) {
					if ($owners[0]->owner_id > 0) {
						$blnLoggedIn = true;

						$user_name = $owners[0]->name;
						$role = $owners[0]->role;
						//give a session id, update the system
						$ip_address = $_SERVER['REMOTE_ADDR'];
						$session_id = uniqid('iK')  . ".." . $ip_address; $session_id = session_id();

						$sql = "UPDATE `ikase`.`cse_owner`
						SET `session_id` = '" . $session_id . "',
						ip_address = '" . $ip_address . "',
						dateandtime = '" . date("Y-m-d H:i:s") . "'
						WHERE owner_id = '" . $owners[0]->owner_id . "'";
						//die($sql);
						try {
							$stmt = DB::run($sql);

							$_SESSION['user'] = $session_id;
							$_SESSION['CREATED'] = time();
							$_SESSION['user_role'] = $role;
							$_SESSION['user_job'] = "Administrator";
							$_SESSION['user_id'] = $owners[0]->owner_id;
							$_SESSION['user_plain_id'] = $owners[0]->owner_id;
							$_SESSION['user_name'] = $owners[0]->name;
							$_SESSION['user_nickname'] = $owners[0]->nickname;
							$_SESSION['user_customer_id'] = -1;

							$php_session_id = session_id();
							$_SESSION["time_stamp"] = date("m/d/y H:i:s");
							$_SESSION["login_type"] = "owner";
							$filename = ROOT_PATH.'sessions'.DC.'data_' . $session_id  . '.txt';
							$fp = fopen($filename, 'w');
							fwrite($fp, json_encode($_SESSION));
							fclose($fp);

							echo json_encode(array("session_id"=>$php_session_id, "sess_id"=>$session_id, "role"=>$role, "user_name"=>$owners[0]->name, "user_customer_id"=>-1));
						} catch (PDOException $e) {
							$error = array("error"=> array("text"=>$e->getMessage()));
							echo json_encode($error);
						}
					}
				}

			} catch(PDOException $e) {
				echo '{"error":{"text":'. $e->getMessage() .'}}';
			}
		}
		if (!$blnLoggedIn) {
			$error = array("error"=> array("text"=>"Login failed."));
	        echo json_encode($error);
		}

    } else {
		$error = array("error"=> array("text"=>"Username and Password are required."));
        echo json_encode($error);
    }
}
function login_master() {
	$_SESSION["need_password"] = false;
	$crypt_key = CRYPT_KEY;
    if(!empty($_POST['user_id'])) {

		session_regenerate_id(true);

        $blnLoggedIn = false;
		$user_id = passed_var('user_id','post');
		// load credentials from the database.
		$sql = "SELECT `user`.*, cus.cus_name customer_name, cus.inhouse_id, cus.jetfile_id, cus.eams_no,
		cus.pwd `token`,
		CONCAT(cus.cus_street, '<br> ', cus.cus_city, ', ', cus.cus_state, ' ', cus.cus_zip) customer_address,
		cus.data_source, cus.data_path, cus.cus_phone, cus.cus_email, cus.cus_type
		FROM `ikase`.`cse_user` `user`
		INNER JOIN `ikase`.`cse_customer` `cus`
		ON `user`.customer_id = `cus`.customer_id
		WHERE `user`.user_id = '" . $user_id . "'
		AND `user`.`activated` = 'Y'";


		//die($password . " -> " . encrypt( $password, $crypt_key));
		try {
			$db = getConnection();
			$stmt = $db->query($sql);
			$users = $stmt->fetchAll(PDO::FETCH_OBJ);

			if (count($users)>0) {
				//die(print_r($users));
				if ($users[0]->user_id > 0) {
					$blnLoggedIn = true;
					//store the user role
					switch($users[0]->user_type) {
						case 0:
							$role = "stranger";
							break;
						case 1:
							$role = "admin";
							break;
						case 2:
							$role = "user";
							break;
						case 3:
							$role = "masteradmin";
							break;
					}
					$user_name = passed_var('email','post');
					//give a session id, update the system
					$ip_address = $_SERVER['REMOTE_ADDR'];
					//$session_id = uniqid('iK')  . ".." . $ip_address;
					$session_id = session_id();


					$sql = "UPDATE `ikase`.`cse_user`
					SET sess_id = '" . $session_id . "',
					ip_address = '" . $ip_address . "', dateandtime = '" . date("Y-m-d H:i:s") . "'
					WHERE user_uuid = '" . $users[0]->user_uuid . "'";

					try {
						$stmt = DB::run($sql);

						$sess_count = $stmt->rowCount();
						/*
						if ($user_id==1853) {
							die($sql . "<br />" . $sess_count);
						}
						*/

						$_SESSION['user'] = $session_id;
						$_SESSION['CREATED'] = time();
						$_SESSION['user_role'] = $role;
						$_SESSION['user_plain_id'] = $users[0]->user_id;
						$_SESSION['user_id'] = $users[0]->user_uuid;
						$_SESSION['user_name'] = $users[0]->user_name;
						$_SESSION['user_logon'] = $users[0]->user_logon;
						$_SESSION['user_job'] = $users[0]->job;
						$_SESSION['user_nickname'] = $users[0]->nickname;
						$_SESSION['user_customer_id'] = $users[0]->customer_id;
						$_SESSION['user_data_source'] = $users[0]->data_source;
						$_SESSION['user_data_path'] = $users[0]->data_path;
						$_SESSION['user_customer_name'] = $users[0]->customer_name;
						$_SESSION['user_customer_address'] = $users[0]->customer_address;
						$_SESSION['user_customer_phone'] = $users[0]->cus_phone;
						$_SESSION['user_customer_email'] = $users[0]->cus_email;
						$_SESSION['user_customer_type'] = $users[0]->cus_type;
						$_SESSION['user_inhouse_id'] = $users[0]->inhouse_id;
						$_SESSION['user_jetfile_id'] = $users[0]->jetfile_id;
						$_SESSION['customer_eams_no'] = $users[0]->eams_no;
						$_SESSION['user_email'] = $users[0]->user_email;
						$_SESSION['subscription_string'] = "&token=" . $users[0]->token . "." . $users[0]->user_uuid;
						$_SESSION['personal_calendar'] = $users[0]->personal_calendar;
						//die(print_r($_SESSION));
						//track logins
						$sql ="INSERT INTO `ikase`.`cse_userlogin` (`user_name`,`user_uuid`,`status`,`ip_address`,`dateandtime`, `login_date`, `customer_id`)
						VALUES('" . addslashes($_SESSION['user_name']) . "','" . $_SESSION['user_id'] . "','IN','" . $ip_address . "','" . date("Y-m-d H:i") . "','" . date("Y-m-d") . "', " . $_SESSION['user_customer_id'] . ")";

						$stmt = DB::run($sql);

						$session_id = session_id();
						$filename = ROOT_PATH.'sessions'.DC.'data_' . $session_id  . '.txt';
						$_SESSION["time_stamp"] = date("m/d/y H:i:s");
						$_SESSION["login_type"] = "master";
						$fp = fopen($filename, 'w');
						fwrite($fp, json_encode($_SESSION));
						fclose($fp);

						echo json_encode(array("sess_id"=>$session_id, "role"=>$role, "user_name"=>$users[0]->user_name, "user_customer_id"=>$users[0]->customer_id, "user_nickname"=>$users[0]->nickname, "session_id"=>$session_id ));
						//, "sql"=>$track_sql, "count"=>$sess_count
					} catch (PDOException $e) {
						$error = array("error"=> array("text"=>$e->getMessage()));
						echo json_encode($error);
					}
				}
			}
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
		}

		if (!$blnLoggedIn) {
			//try logging in as an owner
			// load credentials from the database.
			$sql = "SELECT *
			FROM `cse_owner`
			WHERE admin_client = '" . $user_logon . "' AND `pwd` = '" . encrypt( $password, $crypt_key ) . "'";
			//die($sql);
			try {
				$db = getConnection();
				$stmt = $db->query($sql);
				$owners = $stmt->fetchAll(PDO::FETCH_OBJ);
				if(count($owners)) {
					if ($owners[0]->owner_id > 0) {
						$blnLoggedIn = true;

						$user_name = $owners[0]->name;
						$role = $owners[0]->role;
						//give a session id, update the system
						$ip_address = $_SERVER['REMOTE_ADDR'];
						$session_id = uniqid('iK')  . ".." . $ip_address; $session_id = session_id();

						$sql = "UPDATE `cse_owner`
						SET `session_id` = '" . $session_id . "',
						ip_address = '" . $ip_address . "',
						dateandtime = '" . date("Y-m-d H:i:s") . "'
						WHERE owner_id = '" . $owners[0]->owner_id . "'";
						//die($sql);
						try {
							$stmt = DB::run($sql);

							$_SESSION['user'] = $session_id;
							$_SESSION['CREATED'] = time();
							$_SESSION['user_role'] = $role;
							$_SESSION['user_id'] = $owners[0]->owner_id;
							$_SESSION['owner_id'] = $owners[0]->owner_id;
							$_SESSION['user_plain_id'] = $owners[0]->owner_id;
							$_SESSION['user_name'] = $owners[0]->name;
							$_SESSION['user_nickname'] = $owners[0]->nickname;
							$_SESSION['user_customer_id'] = -1;

							echo json_encode(array("sess_id"=>$session_id, "role"=>$role, "user_name"=>$owners[0]->name, "user_customer_id"=>-1));
						} catch (PDOException $e) {
							$error = array("error"=> array("text"=>$e->getMessage()));
							echo json_encode($error);
						}
					}
				}

			} catch(PDOException $e) {
				echo '{"error":{"text":'. $e->getMessage() .'}}';
			}
		}
		//die(print_r($_SESSION));
		if (!$blnLoggedIn) {
			$error = array("error"=> array("text"=>"Login failed."));
	        echo json_encode($error);
		}

    } else {
		$error = array("error"=> array("text"=>"Username and Password are required."));
        echo json_encode($error);
    }
	exit();
}

function getEmployees() {

    if (isset($_GET['name'])) {
        return getEmployeesByName($_GET['name']);
    } else if (isset($_GET['modifiedSince'])) {
        return getModifiedEmployees($_GET['modifiedSince']);
    }

    $sql = "select e.id, e.firstName, e.lastName, CONCAT(e.firstName, ' ',e.lastName) fullName, e.title, e.officePhone, e.cellPhone, e.email, count(r.id) reportCount " .
    		"from employee e left join employee r on r.managerId = e.id
			WHERE e.deleted ='N' " .
    		"group by e.id order by e.lastName, e.firstName";
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$employees = $stmt->fetchAll(PDO::FETCH_OBJ);
		//echo json_encode(array("user_name"=>$_SESSION['user_name']));
        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($employees);
        } else {
            echo $_GET['callback'] . '(' . json_encode($employees) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function typeEmployees() {
    $sql = "select e.id, CONCAT(e.firstName, ' ',e.lastName) name " .
    		"from employee e
			WHERE e.deleted ='N'";
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$employees = $stmt->fetchAll(PDO::FETCH_OBJ);
		//echo json_encode(array("user_name"=>$_SESSION['user_name']));
        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($employees);
        } else {
            echo $_GET['callback'] . '(' . json_encode($employees) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getEmployee($id) {
    $sql = "select e.id, e.firstName, e.lastName, CONCAT(e.firstName, ' ',e.lastName) fullName, e.title, e.officePhone, e.cellPhone, e.email, e.managerId, e.twitterId, m.firstName managerFirstName, m.lastName managerLastName, count(r.id) reportCount " .
			"from employee e " .
			"left join employee r on r.managerId = e.id " .
    		"left join employee m on e.managerId = m.id " .
    		"where e.id=:id
			AND e.deleted = 'N'";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$employee = $stmt->fetchObject();

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($employee);
        } else {
            echo $_GET['callback'] . '(' . json_encode($employee) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function addEmployeeDocument() {
	$sql = "INSERT INTO `document` (`name`) VALUES (:name)";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("name", $_POST["name"]);
		$stmt->execute();
		$new_id = $db->lastInsertId();

		$blnClearFirst = ($_POST["clearFirst"]=="y");

		if ($blnClearFirst) {
			$sql = "DELETE FROM `employee_document`
			WHERE  employee_id = :employee_id
			AND attribute = :attribute";
			try {
				$db = getConnection();
				$stmt = $db->prepare($sql);
				$stmt->bindParam("employee_id", $_POST["tableId"]);
				$stmt->bindParam("attribute", $_POST["attribute"]);
				$stmt->execute();
			} catch(PDOException $e) {
				die( '{"error":{"text":'. $e->getMessage() .'}}');
			}
		}
		$sql = "INSERT INTO `employee_document` (`employee_id`, `document_id`, `attribute`, `uploaded_by`) VALUES (:employee_id, :document_id, :attribute, :uploaded_by)";
		try {
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("employee_id", $_POST["tableId"]);
			$stmt->bindParam("document_id", $new_id);
			$stmt->bindParam("attribute", $_POST["attribute"]);
			$stmt->bindParam("uploaded_by", $_POST["uploaded_by"]);
			$stmt->execute();

			echo json_encode(array("id"=>$new_id));
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}';
		}
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}

function updateEmployee() {
	$sql = "UPDATE employee
	SET firstName = :firstnameInput,
	lastName =  :lastnameInput,
	title =  :titleInput,
	officePhone =  :phoneInput,
	cellPhone = :cellphoneInput,
	email = :emailInput,
	twitterId = :twitterInput
	WHERE id = :idInput";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("idInput", $_POST["id"]);
		$stmt->bindParam("firstnameInput", $_POST["firstName"]);
		$stmt->bindParam("lastnameInput", $_POST["lastName"]);
		$stmt->bindParam("titleInput", $_POST["title"]);
		$stmt->bindParam("phoneInput", $_POST["officePhone"]);
		$stmt->bindParam("cellphoneInput", $_POST["cellPhone"]);
		$stmt->bindParam("emailInput", $_POST["email"]);
		$stmt->bindParam("twitterInput", $_POST["twitterId"]);
		$stmt->execute();
		//die(print_r($newEmployee));

		echo json_encode(array("success"=>$_POST["id"]));
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}

function getReports($id) {

    $sql = "select e.id, e.firstName, e.lastName, CONCAT(e.firstName, ' ',e.lastName) fullName, e.title, count(r.id) reportCount " .
    		"from employee e left join employee r on r.managerId = e.id " .
			"where e.managerId=:id
			AND e.deleted = 'N'" .
    		"group by e.id order by e.lastName, e.firstName";

    try {
        $db = getConnection();
    	$stmt = $db->prepare($sql);
    	$stmt->bindParam("id", $id);
    	$stmt->execute();
    	$employees = $stmt->fetchAll(PDO::FETCH_OBJ);

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($employees);
        } else {
            echo $_GET['callback'] . '(' . json_encode($employees) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getEmployeesByName($name) {
    $sql = "select e.id, e.firstName, e.lastName, e.title, count(r.id) reportCount " .
    		"from employee e left join employee r on r.managerId = e.id " .
            "WHERE UPPER(CONCAT(e.firstName, ' ', e.lastName)) LIKE :name " .
    		"group by e.id order by e.lastName, e.firstName";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$name = "%".$name."%";
		$stmt->bindParam("name", $name);
		$stmt->execute();
		$employees = $stmt->fetchAll(PDO::FETCH_OBJ);

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($employees);
        } else {
            echo $_GET['callback'] . '(' . json_encode($employees) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getModifiedEmployees($modifiedSince) {
    if ($modifiedSince == 'null') {
        $modifiedSince = "1000-01-01";
    }
    $sql = "select * from employee WHERE lastModified > :modifiedSince";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("modifiedSince", $modifiedSince);
		$stmt->execute();
		$employees = $stmt->fetchAll(PDO::FETCH_OBJ);

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($employees);
        } else {
            echo $_GET['callback'] . '(' . json_encode($employees) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function deleteForm() {
	$table_name = passed_var("table_name","post");
	$table_id = passed_var("table_id","post");

	$sql = "UPDATE `cse_" . $table_name . "`
			SET `deleted` = 'Y'
			WHERE `" . $table_name . "_id`= " . $table_id;
	try {
		$stmt = DB::run($sql);
		echo json_encode(array("success"=>"" . $table_name . " marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addForm() {
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$table_id = "";
	$partie_id = "";
	$case_uuid = "";
	//default attribute
	$table_attribute = "main";
	foreach($_POST as $fieldname=>$value) {
		if ($fieldname!="noteInput") {
			$value = passed_var($fieldname, "post");
		} else {
			//special case
			//remove script
			$value = @processHTML($_POST["noteInput"]);
		}
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="table_attribute") {
			$table_attribute = $value;
			continue;
		}
		if ($fieldname=="case_uuid") {
			$case_uuid = $value;
			continue;
		}
		if ($fieldname=="case_id") {
			$case_id = $value;
			continue;
		}
		if ($fieldname=="partie_id") {
			$partie_id = $value;
			continue;
		}
		if ($fieldname=="case_id" || $fieldname=="table_id") {
			continue;
		}
		if (strpos($fieldname, "_uuid") > -1) {
			continue;
		}
		$arrFields[] = "`" . $fieldname . "`";
		if ($fieldname=="dateandtime" || $fieldname=="start_date" || $fieldname=="end_date") {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			}
		}
		$arrSet[] = "'" . addslashes($value) . "'";
	}

	$table_uuid = uniqid("KS", false);
	$sql = "INSERT INTO `cse_" . $table_name ."` (`customer_id`, `entered_by`, `" . $table_name . "_uuid`, " . implode(",", $arrFields) . ")
			VALUES('" . $_SESSION['user_customer_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $table_uuid . "', " . implode(",", $arrSet) . ")";
	//die($sql);
	try {

		DB::run($sql);
	$new_id = DB::lastInsertId();

		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid));

		if ($case_uuid=="" && $case_id!="") {
			$kase = getKaseInfo($case_id);
			$case_uuid = $kase->uuid;
		}
		$case_table_uuid = uniqid("KA", false);
		//attribute
		if ($table_name == "notes" && $table_attribute=="") {
			//default
			$table_attribute = "main";
		}

		$last_updated_date = date("Y-m-d H:i:s");
		//now we have to attach the applicant to the case
		$sql = "INSERT INTO cse_case_" . $table_name . " (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . $table_attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";

		//die($sql);
		try {
			$stmt = DB::run($sql);
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}';
		}

		//could this be a partie/applicant note
		if ($table_attribute!="quick" && $table_attribute!="main") {
			$link_table = "corporation";
			if ($table_attribute=="applicant") {
				$link_table = "person";
			}
			if ($partie_id > -1) {
				//get the uuid
				$sql = "SELECT `" . $link_table . "_uuid` `uuid` FROM `cse_" . $link_table . "`
				WHERE `" . $link_table . "_id` = " . $partie_id;
				$stmt = DB::run($sql);
				$record = $stmt->fetchObject();

				$link_uuid = $record->uuid;
				$link_table_uuid = uniqid("KA", false);
				$last_updated_date = date("Y-m-d H:i:s");
				//now we have to attach the applicant to the case
				$sql = "INSERT INTO cse_" . $link_table . "_" . $table_name . " (`" . $link_table . "_" . $table_name . "_uuid`, `" . $link_table . "_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $link_table_uuid  ."', '" . $link_uuid . "', '" . $table_uuid . "', '" . $table_attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				try {
					$stmt = DB::run($sql);
				} catch(PDOException $e) {
					echo '{"error":{"text":'. $e->getMessage() .'}}';
				}
			}
		}
		//track now
		switch($table_name) {
			case "notes":
				trackNote("insert", $new_id);
				break;
		}
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}
function updateForm() {
	$arrSet = array();
	$where_clause = "";
	$table_name = "";
	$table_id = "";
	$table_attribute = "";
	foreach($_POST as $fieldname=>$value) {
		if ($fieldname!="noteInput") {
			$value = passed_var($fieldname, "post");
		} else {
			//special case
			//remove script
			$value = @processHTML($_POST["noteInput"]);
		}
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="table_attribute") {
			$table_attribute = $value;
			continue;
		}
		if ($fieldname=="case_id") {
			continue;
		}
		if ($fieldname=="partie_id") {
			continue;
		}
		//no uuids  || $fieldname=="case_uuid"
		if (strpos($fieldname, "_uuid") > -1) {
			continue;
		}
		if ($fieldname=="dateandtime" || $fieldname=="start_date" || $fieldname=="end_date" || $fieldname=="event_dateandtime") {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			}
		}
		if ($fieldname=="table_id" || $fieldname=="id") {
			$table_id = $value;
			$where_clause = " = " . $value;
		} else {
			$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
		}
	}
	$where_clause = "`" . $table_name . "_id`" . $where_clause;
	$sql = "
	UPDATE `cse_" . $table_name . "`
	SET " . implode(", ", $arrSet) . "
	WHERE " . $where_clause;
	//die( $sql . "\r\n");
	try {
		$stmt = DB::run($sql);

		echo json_encode(array("success"=>$table_id));

		//track now
		switch($table_name) {
			case "notes":
				trackNote("update", $table_id);
				break;
		}
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}
