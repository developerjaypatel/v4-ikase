<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');	
	
include("manage_session.php");
date_default_timezone_set('America/Los_Angeles');

define("CRYPT_KEY", "CaptainBradAtTheControls$4U");
require 'Slim/Slim.php';
include("connection.php");

$app = new Slim();
//authorize('user'), 

include("contact.php");
//debtor is really about contacts with reminders
include("debtor.php");
include("events.php");
include("group.php");
include("message.php");
include("upload.php");

/*
include("batch.php");
include("batch_report.php");
include("drip_report.php");
include("drop_report.php");
include("debtor.php");
include("events.php");
include("cascade.php");
include("drip.php");
include("feedback.php");
include("import.php");
include("list.php");
include("emphasis.php");
include("language.php");
include("percentile.php");
include("tone.php");
include("upload.php");
// include("unsubscribe.php");
include("neustar.php");
include("report.php");
include("IVR_querries.php");
//Mock data
include("mock_data_queries.php");
*/

$app->get('/killsleep', 'killSleep');

$app->get('/hash/:id', 'makeHash');
$app->get('/citystate/:zip', 'getCityState');
$app->get('/hash/:id', 'makeHash');

$app->get('/check/:name', function ($name) {
	die($name . " is here");
	echo $_SESSION['timeout'];
	echo "<br>" . time();
	echo "<br>" . (($_SESSION['timeout'] + (8 * 60 * 60)) - time());
    die(print_r($_SESSION));
});

// I add the login route as a post, since we will be posting the login form info
$app->post('/login', 'login_encrypt');
$app->post('/masterlogin', authorize('user'), 'login_master');
$app->post('/logout', 'logout');

$app->run();

function killSleep() {
	$sql = "select concat('KILL ',id,';') killswitch from information_schema.processlist where user='root' 
AND `COMMAND` = 'Sleep'
AND `TIME` > 10";
	
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
		$db = null;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function makeHash($password) {
	$crypt_key = CRYPT_KEY;
    echo encrypt( $password, $crypt_key);
}
function encrypt($v1,$v2=''){
    $token = md5(sha1(md5(base64_decode($v1.$v2)).$v2).$v1);
    return $token;
}
function login_encrypt() {
	$crypt_key = CRYPT_KEY;
	
	// die($crypt_key);
    if(!empty($_POST['email']) && !empty($_POST['password'])) {
        $blnLoggedIn = false;
		$user_logon = passed_var('email','post');
		$password = passed_var('password','post');
		// load credentials from the database. 
		$sql = "SELECT `user`.*, `cus`.`cus_name` customer_name, '' `token`, '' customer_address, '' data_source,
		`cus`.`cus_phone`, `cus`.cus_type
		FROM `tbl_user` `user`
		INNER JOIN `tbl_customer` `cus`
		ON `user`.`customer_id` = `cus`.`customer_id`
		WHERE `user`.`user_logon` = '" . $user_logon . "' AND `user`.`pwd` = '" . encrypt( $password, $crypt_key) . "'";
		//die($sql);
		try {
			$db = getConnection();
			$stmt = $db->query($sql);
			//die($stmt);
			$users = $stmt->fetchAll(PDO::FETCH_OBJ);
			$ip_address = $_SERVER['REMOTE_ADDR'];
			
			$db = null;
			//die(count($users));
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
					
					$sql = "UPDATE `tbl_user` 
					SET sess_id = '" . $session_id . "',
					ip_address = '" . $ip_address . "', dateandtime = '" . date("Y-m-d H:i:s") . "'
					WHERE user_uuid = '" . $users[0]->user_uuid . "'";
					
					//die($sql);
					try {
						$db = getConnection();
						$stmt = $db->prepare($sql);
						$stmt->execute();
						
						$_SESSION['user'] = $session_id;
						$_SESSION['user_role'] = $role;	
						$_SESSION['user_plain_id'] = $users[0]->user_id;
						$_SESSION['user_id'] = $users[0]->user_uuid;
						$_SESSION['user_name'] = $users[0]->user_name;
						$_SESSION['user_nickname'] = $users[0]->nickname;
						$_SESSION['user_customer_id'] = $users[0]->customer_id;
						$_SESSION['user_data_source'] = $users[0]->data_source;
						$_SESSION['user_customer_type'] = $users[0]->cus_type;
						$_SESSION['user_customer_name'] = $users[0]->customer_name;
						$_SESSION['user_customer_address'] = $users[0]->customer_address;
						$_SESSION['user_email'] = $users[0]->user_email;
						$_SESSION['subscription_string'] = "&token=" . $users[0]->token . "." . $users[0]->user_uuid;
						$_SESSION['personal_calendar'] = $users[0]->personal_calendar;
						$_SESSION["provider_contact_number"] = $users[0]->cus_phone;
						
						$_SESSION['timeout'] = time();
						
						//track logins
						$sql ="INSERT INTO `tbl_userlogin` (`user_name`,`user_uuid`,`status`,`ip_address`,`dateandtime`, `login_date`, `customer_id`)
						VALUES('" . $_SESSION['user_name'] . "','" . $_SESSION['user_id'] . "','IN','" . $ip_address . "','" . date("Y-m-d H:i") . "','" . date("Y-m-d") . "', " . $_SESSION['user_customer_id'] . ")";
						
						$stmt = $db->prepare($sql);
						$stmt->execute();
						
						echo json_encode(array("sess_id"=>$session_id, "role"=>$role, "user_name"=>$users[0]->user_name, "user_customer_id"=>$users[0]->customer_id, "user_nickname"=>$users[0]->nickname, "session_id"=>$session_id));
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
			FROM `tbl_owner` 
			WHERE admin_client = '" . $user_logon . "' AND `pwd` = '" . encrypt( $password, $crypt_key ) . "'";
			//die($sql);
			try {
				$db = getConnection();
				$stmt = $db->query($sql);
				$owners = $stmt->fetchAll(PDO::FETCH_OBJ);

				$db = null;
				if(count($owners)) {
					if ($owners[0]->owner_id > 0) {
						$blnLoggedIn = true;
						
						$user_name = $owners[0]->name;
						$role = $owners[0]->role;
						//give a session id, update the system
						$ip_address = $_SERVER['REMOTE_ADDR'];
						$session_id = uniqid('iK')  . ".." . $ip_address;
						
						$sql = "UPDATE `tbl_owner` 
						SET `session_id` = '" . $session_id . "',
						ip_address = '" . $ip_address . "', 
						dateandtime = '" . date("Y-m-d H:i:s") . "'
						WHERE owner_id = '" . $owners[0]->owner_id . "'";
						//die($sql);
						try {
							$db = getConnection();
							$stmt = $db->prepare($sql);
							$stmt->execute();
							
							$_SESSION['user'] = $session_id;
							$_SESSION['user_role'] = $role;
							$_SESSION['user_id'] = $owners[0]->owner_id;
							$_SESSION['user_plain_id'] = $owners[0]->owner_id;
							$_SESSION['user_name'] = $owners[0]->name;
							$_SESSION['user_nickname'] = $owners[0]->nickname;
							$_SESSION['user_customer_id'] = -1;
	
							echo json_encode(array("sess_id"=>$session_id, "role"=>$role, "user_name"=>$owners[0]->name, "user_customer_id"=>-1, "session_id"=>$session_id));
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
	$crypt_key = CRYPT_KEY;
    if(!empty($_POST['user_id'])) {
        $blnLoggedIn = false;
		$user_id = passed_var('user_id','post');
		// load credentials from the database. 
		$sql = "SELECT `user`.*, cus.cus_name customer_name, cus.data_source, cus.cus_phone, `cus`.cus_type
		FROM `tbl_user` `user`
		INNER JOIN `tbl_customer` `cus`
		ON `user`.customer_id = `cus`.customer_id
		WHERE `user`.user_id = '" . $user_id . "'";
		//die($sql);
		try {
			$db = getConnection();
			$stmt = $db->query($sql);
			$users = $stmt->fetchAll(PDO::FETCH_OBJ);
			
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
					//$user_name = passed_var('email','post');
					//give a session id, update the system
					$ip_address = $_SERVER['REMOTE_ADDR'];
					$session_id = uniqid('iK')  . ".." . $ip_address;
					
					$sql = "UPDATE `tbl_user` 
					SET sess_id = '" . $session_id . "',
					ip_address = '" . $ip_address . "', dateandtime = '" . date("Y-m-d H:i:s") . "'
					WHERE user_uuid = '" . $users[0]->user_uuid . "'";
					//die($sql);
					$stmt = $db->prepare($sql);
					$stmt->execute();
					
					$_SESSION['user'] = $session_id;
					$_SESSION['user_role'] = $role;	
					$_SESSION['user_plain_id'] = $users[0]->user_id;
					$_SESSION['user_id'] = $users[0]->user_uuid;
					$_SESSION['user_name'] = $users[0]->user_name;
					$_SESSION['user_nickname'] = $users[0]->nickname;
					$_SESSION['user_customer_id'] = $users[0]->customer_id;
					$_SESSION['user_data_source'] = $users[0]->data_source;
					$_SESSION['user_customer_name'] = $users[0]->customer_name;
					$_SESSION['user_customer_type'] = $users[0]->cus_type;
					$_SESSION['user_email'] = $users[0]->user_email;
					$_SESSION["provider_contact_number"] = $users[0]->cus_phone;
					
					//die(print_r($_SESSION));
					
					echo json_encode(array("sess_id"=>$session_id, "role"=>$role, "user_name"=>$users[0]->user_name, "user_customer_id"=>$users[0]->customer_id, "user_nickname"=>$users[0]->nickname, "session_id"=>$session_id));
				}
			}
			$db = null;
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
		}
		//die("not in");
		if (!$blnLoggedIn) {
			//try logging in as an owner
			// load credentials from the database. 
			$sql = "SELECT * 
			FROM `tbl_owner` 
			WHERE admin_client = '" . $user_logon . "' AND `pwd` = '" . encrypt( $password, $crypt_key ) . "'";
			//die($sql);
			try {
				$db = getConnection();
				$stmt = $db->query($sql);
				$owners = $stmt->fetchAll(PDO::FETCH_OBJ);

				$db = null;
				if(count($owners)) {
					if ($owners[0]->owner_id > 0) {
						$blnLoggedIn = true;
						
						$user_name = $owners[0]->name;
						$role = $owners[0]->role;
						//give a session id, update the system
						$ip_address = $_SERVER['REMOTE_ADDR'];
						$session_id = uniqid('iK')  . ".." . $ip_address;
						
						$sql = "UPDATE `tbl_owner` 
						SET `session_id` = '" . $session_id . "',
						ip_address = '" . $ip_address . "', 
						dateandtime = '" . date("Y-m-d H:i:s") . "'
						WHERE owner_id = '" . $owners[0]->owner_id . "'";
						//die($sql);
						try {
							$db = getConnection();
							$stmt = $db->prepare($sql);
							$stmt->execute();
							
							$_SESSION['user'] = $session_id;
							$_SESSION['user_role'] = $role;
							$_SESSION['user_id'] = $owners[0]->owner_id;
							$_SESSION['user_plain_id'] = $owners[0]->owner_id;
							$_SESSION['user_name'] = $owners[0]->name;
							$_SESSION['user_nickname'] = $owners[0]->nickname;
							$_SESSION['user_customer_id'] = -1;
	
							echo json_encode(array("sess_id"=>$session_id, "role"=>$role, "user_name"=>$owners[0]->name, "user_customer_id"=>-1, "session_id"=>$session_id));
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
function logout() {
	$ip_address = $_SERVER['REMOTE_ADDR'];
	//track logins
	if (isset($_SESSION['user_name'])) {
		if ($_SESSION['user_name']!="") {
			$sql ="INSERT INTO `tbl_userlogin` (`user_name`,`user_uuid`,`status`,`ip_address`,`dateandtime`, `login_date`, `customer_id`)
			VALUES('" . $_SESSION['user_name'] . "','" . $_SESSION['user_id'] . "','OUT','" . $ip_address . "','" . date("Y-m-d H:i") . "','" . date("Y-m-d") . "', " . $_SESSION['user_customer_id'] . ")";
			
			try {
				$db = getConnection();
				$stmt = $db->prepare($sql);
				$stmt->execute();
				$db = null;
			} catch(PDOException $e) {
				$error = array("error"=> array("text"=>$e->getMessage()));
					echo json_encode($error);
			}
		}
	}
	if (isset($_SESSION["user"])) {
		$session_id = $_SESSION["user"];
	} else {
		$session_id = session_id();
	}
	$_SESSION['user'] = false;
	$_SESSION['user_role'] = false;
	$_SESSION['user_name'] = false;
	if (isset($_SESSION['owner_id'])) {
		$_SESSION['owner_id'] = false;
		unset($_SESSION['owner_id']);
	}
	/* generate new session id and delete old session in store */
	session_regenerate_id(true);
	
	/* optional: unset old session variables */
	$_SESSION = array();
	
	$filename = 'C:\\inetpub\\wwwroot\\ikase.org\\remind\\sessions\\data_' . $session_id  . '.txt';
	$fp = fopen($filename, 'w');
	fwrite($fp, "");
	fclose($fp);
	
	echo '{"success":{"text":"You are logged out..."}}';
	die();
}

/**
 * Authorise function, used as Slim Route Middlewear (http://www.slimframework.com/documentation/stable#routing-middleware)
 */
function authorize($role = "stranger") {
	//die(print_r($_SESSION));
	if (isset($_SESSION['timeout'])) {
		if (($_SESSION['timeout'] + (8 * 60 *60)) < time()) {
			logout();
			return false;
		}
	}
	//reset the timeout
	//$_SESSION['timeout'] = time();
	
	return function () use ( $role ) {
        // Get the Slim framework object
        $app = Slim::getInstance();
		
		//die(print_r($_SESSION));
        // First, check to see if the user is logged in at all
        if(!empty($_SESSION['user'])) {
            // Next, validate the role to make sure they can access the route
            // We will assume admin role can access everything
            if($_SESSION['user_role'] == $role || $_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'masteradmin' || $_SESSION['user_role'] == 'owner') {
                //User is logged in and has the correct permissions... Nice!
				//update the user table
				/*
				$sql = "UPDATE `tbl_user` 
				SET dateandtime = '" . date("Y-m-d H:i:s") . "'
				WHERE user_uuid = '" . $_SESSION["user_id"] . "'";
				try {
					$db = getConnection();
					$stmt = $db->prepare($sql);
					$stmt->execute();
				} catch (PDOException $e) {
					$error = array("error"=> array("text"=>$e->getMessage()));
					echo json_encode($error);
				}
				*/
                return true;
            }
            else {
                // If a user is logged in, but doesn't have permissions, return 403
                $app->halt(403, 'You shall not pass!');
            }
        }
        else {
            // If a user is not logged in at all, return a 401
            $app->halt(401, 'Not logged in... sign in for me, will you?\r\n' . json_encode($_SESSION));
        }
    };
}
function getCityState($zip, $blnUSA = true) {
	$url = "http://maps.googleapis.com/maps/api/geocode/json?address=" . $zip . "&sensor=true";

	$address_info = file_get_contents($url);
	$json = json_decode($address_info);
	$city = "";
	$state = "";
	$country = "";
		
	if (count($json->results) > 0) {
		//die(print_r($json));
		//break up the components
		$arrComponents = $json->results[0]->address_components;
		
		foreach($arrComponents as $index=>$component) {
			$type = $component->types[0];
			
			if ($city == "" && ($type == "sublocality_level_1" || $type == "locality") ) {
				$city = trim($component->short_name);
			}
			if ($state == "" && $type=="administrative_area_level_1") {
				$state = trim($component->short_name);
			}
			if ($country == "" && $type=="country") {
				$country = trim($component->short_name);
				
				if ($blnUSA && $country!="US") {
					$city = "";
					$state = "";
					break;
				}
			}
			if ($city != "" && $state != "" && $country != "") {
				//we're done
				break;
			}
		}
	}
	$arrReturn = array("city"=>$city, "state"=>$state, "country"=>$country);
	
	die(json_encode($arrReturn));
}
?>