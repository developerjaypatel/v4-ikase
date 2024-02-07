<?php
use Api\Bootstrap;

Bootstrap::session();


include('connection.php');

$app = Bootstrap::slim('/iklock/api');
Bootstrap::addCommonCalls(false, true, '', 'user_logon');

//region API imports
include ("../classes/cls_address.php");
include ("../classes/cls_comm.php");
include ("../classes/cls_department.php");
include ("../classes/cls_document_matrix.php");
include ("../classes/cls_events.php");
include ("../classes/cls_eventscalendar.php");
include ("../classes/cls_notes.php");
include ("../classes/cls_person.php");
include ("../classes/cls_user.php");
include("company_pack.php");
include("employee_pack.php");
include("paycheck_pack.php");
include("reimbursment_pack.php");
//endregion

$app->run();

//FIXME: DRY!!! pretty much the same as remind's and remind dev's
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
		FROM `user` `user`
		INNER JOIN `customer` `cus`
		ON `user`.`customer_id` = `cus`.`customer_id`
		WHERE `user`.`user_logon` = '" . $user_logon . "' AND `user`.`pwd` = '" . encrypt( $password, $crypt_key) . "'";
		//die($sql);
		try {
			$db = getConnection();
			$stmt = $db->query($sql);
			//die($stmt);
			$users = $stmt->fetchAll(PDO::FETCH_OBJ);
			$ip_address = $_SERVER['REMOTE_ADDR'];
			
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
					
					$sql = "UPDATE `user` 
					SET sess_id = '" . $session_id . "',
					ip_address = '" . $ip_address . "', dateandtime = '" . date("Y-m-d H:i:s") . "'
					WHERE user_uuid = '" . $users[0]->user_uuid . "'";
					
					//die($sql);
					try {
						$stmt = DB::run($sql);
						
						$_SESSION['user'] = $session_id;
						$_SESSION['user_logon'] = $user_logon;
						$_SESSION['user_role'] = $role;	
						$_SESSION['user_type'] = $users[0]->user_type;
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
						$_SESSION["provider_contact_number"] = $users[0]->cus_phone;
						
						$_SESSION['timeout'] = time();
						
						//track logins
						
						$sql ="INSERT INTO `userlogin` (`username`,`user_uuid`,`status`,`ip_address`,`dateandtime`, `login_date`, `customer_id`)
						VALUES('" . $_SESSION['user_logon'] . "','" . $_SESSION['user_id'] . "','IN','" . $ip_address . "','" . date("Y-m-d H:i") . "','" . date("Y-m-d") . "', " . $_SESSION['user_customer_id'] . ")";
						
						$stmt = DB::run($sql);
						
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
			FROM `owner` 
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
						$session_id = uniqid('iK')  . ".." . $ip_address;
						
						$sql = "UPDATE `owner` 
						SET `session_id` = '" . $session_id . "',
						ip_address = '" . $ip_address . "', 
						dateandtime = '" . date("Y-m-d H:i:s") . "'
						WHERE owner_id = '" . $owners[0]->owner_id . "'";
						//die($sql);
						try {
							$stmt = DB::run($sql);
							
							$_SESSION['user'] = $session_id;
							$_SESSION['user_logon'] = $user_logon;
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
		FROM `user` `user`
		INNER JOIN `customer` `cus`
		ON `user`.customer_id = `cus`.customer_id
		WHERE `user`.user_id = :user_id";
		//die($sql);
		try {
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("user_id", $user_id);
			$stmt->execute();
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
					
					$sql = "UPDATE `user` 
					SET sess_id = '" . $session_id . "',
					ip_address = '" . $ip_address . "', dateandtime = '" . date("Y-m-d H:i:s") . "'
					WHERE user_uuid = '" . $users[0]->user_uuid . "'";
					//die($sql);
					$stmt = DB::run($sql);
					
					$_SESSION['user'] = $session_id;
					$_SESSION['user_logon'] = $users[0]->user_logon;
					$_SESSION['user_role'] = $role;	
					$_SESSION['user_type'] = $users[0]->user_type;
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
					
					echo json_encode(array("success"=>true, "sess_id"=>$session_id, "role"=>$role, "user_name"=>$users[0]->user_name, "user_customer_id"=>$users[0]->customer_id, "user_nickname"=>$users[0]->nickname, "session_id"=>$session_id));
				}
			}
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
		}
		//die("not in");
		if (!$blnLoggedIn) {
			//try logging in as an owner
			// load credentials from the database. 
			$sql = "SELECT * 
			FROM `owner` 
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
						$session_id = uniqid('iK')  . ".." . $ip_address;
						
						$sql = "UPDATE `owner` 
						SET `session_id` = '" . $session_id . "',
						ip_address = '" . $ip_address . "', 
						dateandtime = '" . date("Y-m-d H:i:s") . "'
						WHERE owner_id = '" . $owners[0]->owner_id . "'";
						//die($sql);
						try {
							$stmt = DB::run($sql);
							
							$_SESSION['user'] = $session_id;
							$_SESSION['user_logon'] = $user_logon;
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
