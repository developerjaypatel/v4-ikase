<?php
$app->get('/contacts', authorize('user'), 'getContacts');
$app->get('/contacts/search/:search_term', authorize('user'), 'searchContacts');
$app->get('/contact/:debtor_id', authorize('user'), 'getContact');
$app->get('/duplicates', authorize('user'), 'getDuplicateContacts');

//these functions are on debtor.php
$app->post('/contact/delete', authorize('user'), 'deleteDebtor');
$app->post('/contact/switchlanguage', authorize('user'), 'switchDebtorLanguage');
function getDuplicateContacts() {
	//die(print_r($_SERVER));
	$_SESSION["duplicates"] = true;
	getContacts("");
}
function searchContacts($search_term) {
	getContacts($search_term);
}
function getContacts($search_term = "") {
	$blnDuplicates = false;
	if (isset($_SESSION["duplicates"])) {
		unset($_SESSION["duplicates"]);
		$blnDuplicates = true;
	}
	session_write_close();
	
	$full_name_search = str_replace("_", "", $search_term);
	$full_name_search = str_replace(" ", "", $full_name_search);
	$customer_id = $_SESSION["user_customer_id"];
	
	
	try {
		$dup_ids = "";
		if ($blnDuplicates) {
			$sql = "SELECT GROUP_CONCAT(debt.debtor_id) ids
			FROM md_reminder.tbl_debtor debt
			WHERE debt.customer_id = :customer_id
			AND debt.deleted = 'N'
			GROUP BY first_name, last_name
			HAVING COUNT(debtor_id) > 1";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			$debtor_ids = $stmt->fetchAll(PDO::FETCH_OBJ);
			$stmt->closeCursor(); $stmt = null; $db = null;
			
			
			foreach($debtor_ids as $debtor_id) {
				if ($dup_ids=="") {
					$dup_ids = $debtor_id->ids;
				} else {
					$dup_ids .= "," . $debtor_id->ids;
				}
			}
		}
		$sql = "SELECT `debt`.*,
		`debt`.`debtor_id` `id`, `debt`.`debtor_uuid` `uuid`,
		IFNULL(rems.attempt_date, '') attempt_date,
		IFNULL(rems.sent_count, '') sent_count,
		IFNULL(groups.group_ids, '') group_ids,
		IFNULL(groups.group_names, '') group_names
		FROM `md_reminder`.`tbl_debtor` `debt`
		LEFT OUTER JOIN (		
			SELECT debt.debtor_uuid, MAX(trs.`timestamp`) attempt_date, 
			COUNT(trs.remindersent_id) sent_count, GROUP_CONCAT(DISTINCT rem.reminder_type) attempt_method        
			FROM md_reminder.tbl_reminder rem
			INNER JOIN md_reminder.tbl_debtor debt
			ON rem.reminder_debtor_uuid = debt.debtor_uuid 
			INNER JOIN md_reminder.tbl_remindersent trs
			ON rem.reminder_uuid = trs.reminder_uuid
			WHERE 1
			AND rem.sent = 'Y'
			AND `rem`.`deleted` = 'N'
			AND rem.customer_id = :customer_id
			GROUP BY debt.debtor_uuid
		) rems
		ON debt.debtor_uuid = rems.debtor_uuid
		LEFT OUTER JOIN (
			SELECT tdg.debtor_uuid, 
			GROUP_CONCAT(DISTINCT group_id) group_ids, 
			GROUP_CONCAT(DISTINCT group_name) group_names
			FROM tbl_debtor_group tdg
			INNER JOIN tbl_group tg
			ON tdg.group_uuid = tg.group_uuid
			AND `tg`.`deleted` = 'N'
			AND `tdg`.`deleted` = 'N'
			AND `tg`.customer_id = :customer_id
			GROUP BY tdg.debtor_uuid
		) groups
		ON debt.debtor_uuid = groups.debtor_uuid
		WHERE 1
		AND `debt`.`deleted` = 'N'
		AND `debt`.`customer_id` = :customer_id";
		
		if ($blnDuplicates) {
			$sql .= " 
			AND debt.debtor_id IN (" . $dup_ids . ")";
		}
		if ($search_term!="") {
			$sql .= " AND (
				`debt`.first_name LIKE '" . addslashes($search_term) . "%'
				OR `debt`.last_name LIKE '" . addslashes($search_term) . "%'
				OR `debt`.email LIKE '" . addslashes($search_term) . "%'
				OR REPLACE(CONCAT(TRIM(`debt`.`first_name`), TRIM(`debt`.`last_name`)), ' ', '') LIKE '" . addslashes($full_name_search) . "%'";
				if (is_numeric($search_term)) {
					$sql .= " OR `debt`.phone LIKE '%" . addslashes($search_term) . "%'
					OR `debt`.cellphone LIKE '%" . addslashes($search_term) . "%'
					";
				}
			$sql .= ")";
		}
		
		$sql .= "
		ORDER BY debt.last_name, debt.first_name";
		//REPLACE(REPLACE(`tbl_debtor`.`content`, '{', '`'), '}', '~') `content`,
		//echo $sql; die();
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$debtors = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;

		echo json_encode($debtors);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getContact($debtor_id) {
	session_write_close();
	$sql = "SELECT `tbl_debtor`.*,
	`debtor_id` `id`, `debtor_uuid` `uuid`
	FROM `md_reminder`.`tbl_debtor` 
	WHERE 1
	AND `tbl_debtor`.deleted = 'N'
	AND debtor_id = :debtor_id
	AND customer_id = :customer_id";
	
	//REPLACE(REPLACE(`tbl_debtor`.`content`, '{', '`'), '}', '~') `content`,
	//echo $sql;
	$customer_id = $_SESSION["user_customer_id"];
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("debtor_id", $debtor_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$debtor = $stmt->fetchObject();
		$db = null;
		
		echo json_encode($debtor);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getContactInfo($debtor_id) {
	session_write_close();
	$sql = "SELECT `tbl_debtor`.*,
	`debtor_id` `id`, `debtor_uuid` `uuid`
	FROM `md_reminder`.`tbl_debtor` 
	WHERE 1
	AND debtor_id = :debtor_id
	AND customer_id = :customer_id";
	
	//REPLACE(REPLACE(`tbl_debtor`.`content`, '{', '`'), '}', '~') `content`,
	//echo $sql;
	$customer_id = $_SESSION["user_customer_id"];
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("debtor_id", $debtor_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$debtor = $stmt->fetchObject();
		$db = null;
		
		return $debtor;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getDuplicates() {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	$sql = "SELECT debt.*
	FROM tbl_debtor debt
	WHERE debt.debtor_id IN (
		SELECT first_name, last_name, COUNT(debtor_id) debtor_count
		FROM tbl_debtor
		WHERE debt.customer_id = :customer_id
		AND debt.deleted = 'N'
		GROUP BY first_name, last_name
		HAVING COUNT(debtor_id) > 1
	)
	";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		//$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$debtors = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode($debtors);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
?>