<?php
$app->get('/groups', authorize('user'), 'getGroups');
$app->get('/group/:group_id', authorize('user'), 'getGroup');

//crud
$app->post('/group/add', authorize('user'), 'addGroup');
$app->post('/group/update', authorize('user'), 'updateGroup');
$app->post('/group/delete', authorize('user'), 'deleteGroup');

function getGroup($group_id) {
	getGroups($group_id);
}
function getGroups($group_id = "") {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	$sql = "SELECT 
    `grp`.`group_id`, `grp`.`group_uuid`, `grp`.`group_name`, `grp`.`description`, `grp`.`deleted`, `grp`.`customer_id`, `grp`.`group_id` id, `grp`.`group_uuid` uuid
	FROM
    `md_reminder`.`tbl_group` grp
	WHERE 1
	AND `grp`.`deleted` = 'N'
	AND `grp`.customer_id = :customer_id";
	if ($group_id != "") {
		$sql .= " AND grp.group_id = :group_id";
	}
	try {
		//die($sql);
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		if ($group_id != "") {
			$stmt->bindParam("group_id", $group_id);
		}
		$stmt->execute();
		if ($group_id != "") {
			$group = $stmt->fetchObject();
			$db = null;
	
			echo json_encode($group);
		} else {
			$groups = $stmt->fetchAll(PDO::FETCH_OBJ);
			$db = null;
	
			echo json_encode($groups);
		}
		
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addGroup() {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	$group_id = passed_var("group_id", "post");
	$group_name = passed_var("group_name", "post");
	$description = passed_var("description", "post");
	$group_uuid = uniqid("GR");
	$sql = "INSERT INTO md_reminder.tbl_group
	(`group_uuid`, `group_name`, `description`, `customer_id`)
	VALUES (:group_uuid, :group_name, :description, :customer_id)";
	try {
		//die($sql);
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("group_uuid", $group_uuid);
		$stmt->bindParam("group_name", $group_name);
		$stmt->bindParam("description", $description);
		
		$stmt->execute();
		$new_id = $db->lastInsertId();
		$db = null; $stmt = null;

		echo json_encode(array("success"=>true, "group_id"=>$new_id));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function updateGroup() {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	$group_id = passed_var("group_id", "post");
	$group_name = passed_var("group_name", "post");
	$description = passed_var("description", "post");
	$group_uuid = uniqid("GR");
	$sql = "UPDATE md_reminder.tbl_group
	SET `group_name` = :group_name, 
	`description` = :description
	WHERE group_id = :group_id
	AND `customer_id` = :customer_id";
	try {
		//die($sql);
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("group_id", $group_id);
		$stmt->bindParam("group_name", $group_name);
		$stmt->bindParam("description", $description);
		
		$stmt->execute();
		$db = null; $stmt = null;

		echo json_encode(array("success"=>true, "group_id"=>$group_id));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function deleteGroup() {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	$group_id = passed_var("id", "post");
	try {
		$db = getConnection();
		
		$sql = "UPDATE tbl_group
				 SET `deleted` = 'Y'
				 WHERE `group_id` = :group_id
				 AND customer_id = :customer_id";
		
		$stmt = $db->prepare($sql);
		$stmt->bindParam("group_id", $group_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		$db = null; $stmt = null;
		
		echo json_encode(array("success"=>"group marked as deleted", "group_id"=>$group_id));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
?>