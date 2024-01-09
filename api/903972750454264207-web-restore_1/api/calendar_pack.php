<?php

$app->get('/calendar', authorize('user'),	'getCalendars');
$app->get('/calendar/:calendar_id', authorize('user'),'getTheCalendar');
$app->get('/calendar_order/:sort_order', authorize('user'),'getCalendarBySortOrder');
$app->get('/calendar/:category/:case_id', authorize('user'), 'getCalendarByCategory');
$app->get('/personalcalendars', authorize('user'), 'getPersonalCalendars');
$app->get('/ics', authorize('user'), 'getICSCalendar');

//posts
$app->post('/calendar/delete', authorize('user'), 'deleteForm');
$app->post('/calendar/add', authorize('user'), 'addCalendar');
$app->post('/calendar/update', authorize('user'), 'updateCalendar');
$app->post('/calendar/assign', authorize('user'), 'assignCalendar');
$app->post('/calendar/unassign', authorize('user'), 'unassignCalendar');

$app->get('/blocked/:blocked_id', authorize('user'),'getBlock');
$app->get('/blockeddate/:start_date', authorize('user'),'getBlocks');
$app->get('/blockeddates/:start_date/:end_date', authorize('user'),'getBlockDates');
$app->get('/blockedactive', authorize('user'),'getActiveBlocks');

$app->post('/blocked/add', authorize('user'), 'addBlock');
$app->post('/blocked/delete', authorize('user'), 'deleteBlock');

function getBlockDates($start_date, $end_date) {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT bl.start_date, bl.end_date, bl.recurring_count, bl.recurring_span,
	IFNULL(usr.user_id, -1) user_id, 
	IFNULL(usr.user_name, '') user_name 
	FROM cse_blocked bl
	LEFT OUTER JOIN cse_user_blocked cub
	ON bl.blocked_uuid = cub.blocked_uuid
	LEFT OUTER JOIN ikase.cse_user usr
	ON cub.user_uuid = usr.user_uuid AND usr.customer_id = :customer_id
	WHERE (
			CAST(bl.start_date AS DATE) BETWEEN :start_date AND :end_date
		)
	AND bl.customer_id = :customer_id
	AND usr.user_id IS NULL
	AND bl.deleted = 'N'";
//	die($sql);

	$arrBlockedDates = array();
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("start_date", $start_date);
		$stmt->bindParam("end_date", $end_date);
		$stmt->execute();
		$blocks = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		for($int = count($blocks) - 1; $int >= 0; $int--) {
			//is this a recurring block
			$block = $blocks[$int];
			
			//we must find the blocked dates 
			//a range
			if ($block->recurring_span=="") {
				$days_add = " +1 days";
				$arrBlockedDates[] = date("Y-m-d", strtotime($block->start_date));
				$next_date = date("Y-m-d", strtotime($block->start_date . $days_add));
				while (strtotime($next_date) <= strtotime($block->end_date) && strtotime($next_date) <= strtotime($end_date)) {
					$arrBlockedDates[] = $next_date;
					$next_date = date("Y-m-d", strtotime($next_date . $days_add));
				}
			}
			//a recurring
			if ($block->recurring_span!="") {
				//if yes, what days
				switch($block->recurring_span) {
					case "week":
						$days_add = " +7 days";
						break;
					case "2_weeks":
						$days_add = " +14 days";
						break;
					case "month":
						$days_add = " +1 month";					
						break;
				}
				$intCounter = 0;
				$arrBlockedDates[] = date("Y-m-d", strtotime($block->start_date));
				$next_date = date("Y-m-d", strtotime($block->start_date . $days_add));
				while($intCounter < $block->recurring_count && strtotime($next_date) <= strtotime($end_date)) {				
					$arrBlockedDates[] = $next_date;
					$next_date = date("Y-m-d", strtotime($next_date . $days_add));
					//increment
					$intCounter++;
				}
			}
		}
		
		echo json_encode($arrBlockedDates);
	} catch(PDOException $e) {
		//die($sql);
		
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}	
}
function getBlock($blocked_id) {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT bl.start_date, bl.end_date, bl.recurring_count, bl.recurring_span,
	IFNULL(usr.user_id, -1) user_id, 
	IFNULL(usr.user_name, '') user_name 
	FROM cse_blocked bl
	LEFT OUTER JOIN cse_user_blocked cub
	ON bl.blocked_uuid = cub.blocked_uuid
	LEFT OUTER JOIN ikase.cse_user usr
	ON cub.user_uuid = usr.user_uuid AND usr.customer_id = :customer_id
	WHERE bl.blocked_id = :blocked_id
	AND bl.customer_id = :customer_id
	AND bl.deleted = 'N'";
//	die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("blocked_id", $blocked_id);
		$stmt->execute();
		$block = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode($block);
	} catch(PDOException $e) {
		//die($sql);
		
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}	
}
function getBlocks($start_date) {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT bl.start_date, bl.end_date, bl.recurring_count, bl.recurring_span,
	IFNULL(usr.user_id, -1) user_id, 
	IFNULL(usr.user_name, '') user_name 
	FROM cse_blocked bl
	LEFT OUTER JOIN cse_user_blocked cub
	ON bl.blocked_uuid = cub.blocked_uuid
	LEFT OUTER JOIN ikase.cse_user usr
	ON cub.user_uuid = usr.user_uuid AND usr.customer_id = :customer_id
	WHERE (
			CAST(bl.start_date AS DATE) = :start_date
			OR
				(
					CAST(bl.start_date AS DATE) <= :start_date AND CAST(bl.end_date AS DATE) >= :start_date
				)
		)
	AND bl.customer_id = :customer_id
	AND bl.deleted = 'N'";
//	die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("start_date", $start_date);
		$stmt->execute();
		$blocks = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		//die(print_r($blocks));
		for($int = count($blocks) - 1; $int >= 0; $int--) {
			//is this a recurring block
			$block = $blocks[$int];
			//die(print_r($block));
			if ($block->recurring_span=="") {
				if (strtotime($start_date) >= strtotime($block->start_date) && strtotime($start_date) <= strtotime($block->end_date)) {
					continue;
				}
			}
			if ($block->recurring_span!="") {
				//if yes, what days
				switch($block->recurring_span) {
					case "week":
						$days_add = " +7 days";
						break;
					case "2_weeks":
						$days_add = " +14 days";
						break;
					case "month":
						$days_add = " +1 month";					
						break;
				}
				//cycle through days
				//is the start date the same as search date
				if (date("Y-m-d", strtotime($block->start_date)) == $start_date) {
					//it's a match, no need to do more
					continue;
				}
				$block_start_date = $block->start_date;
				$next_date = date("Y-m-d", strtotime($block_start_date . $days_add));
				$blnMatch = false;
				$intCounter = 0;
				//add increment to start date, then check if it matches search date
				while($intCounter < $block->recurring_count) {
					//echo $next_date . " ==> ";
					if (strtotime($next_date) == strtotime($start_date)) {
						$blnMatch = true;
						break;
					}
					if (strtotime($next_date) > strtotime($start_date)) {
						//we've gone beyond the search date, no need to look further
						break;
					}
					//increment the next day
					$next_date = date("Y-m-d", strtotime($next_date . $days_add));
					//echo $next_date . "\r\n";
					$intCounter++;
				}
				/*
				die(strtotime($block_start_date)." <= ".strtotime($start_date));
				die(strtotime($block_start_date)." <= ".strtotime($start_date));
				while($intCounter < $block->recurring_count && strtotime($block_start_date) >= strtotime($start_date) && strtotime($block_start_date) <= strtotime($block->end_date)) {
					$next_date = date("Y-m-d", strtotime($block_start_date . $days_add));
					die("match:" . $next_date);
					if (date("Y-m-d", strtotime($next_date)) == $start_date) {
						//it's a match, no need to do more
						$blnMatch = true;
						break;
					}
					$block_start_date = $next_date;
					$intCounter++;
				}
				*/
				
				if (!$blnMatch) {
					//die("not matched");
					//no match, remove this block from the list
					unset($blocks[$int]);
				}
				//die("matched");
			}
		}
		echo json_encode($blocks);
	} catch(PDOException $e) {
		//die($sql);
		
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}	
}
function getActiveBlocks() {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	$end_date = date("Y-m-d");
	
	$sql = "SELECT bl.blocked_id, bl.start_date, bl.end_date, bl.recurring_count, bl.recurring_span,
	IFNULL(usr.user_id, -1) user_id, 
	IFNULL(usr.user_name, '') user_name 
	FROM cse_blocked bl
	LEFT OUTER JOIN cse_user_blocked cub
	ON bl.blocked_uuid = cub.blocked_uuid
	LEFT OUTER JOIN ikase.cse_user usr
	ON cub.user_uuid = usr.user_uuid AND usr.customer_id = :customer_id
	WHERE CAST(bl.end_date AS DATE) >= :end_date
	AND bl.customer_id = :customer_id
	AND bl.deleted = 'N'";
//	die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("end_date", $end_date);
		$stmt->execute();
		$blocks = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode($blocks);
	} catch(PDOException $e) {
		//die($sql);
		
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}	
}
function deleteBlock() {
	session_write_close();
	
	$customer_id = $_SESSION["user_customer_id"];
	$user_uuid = $_SESSION["user_id"];
	
	//is this a user specific request, or company wide
	$id = passed_var("id", "post");
	$sql = "UPDATE cse_blocked 
			SET deleted = 'Y'
			WHERE blocked_id=:id
			AND customer_id = " . $_SESSION['user_customer_id'];
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		echo json_encode(array("success"=>"blocked marked as deleted"));
		
		trackBlock("delete", $id);
		
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}	
}
function unblockDays() {
	session_write_close();
	
	$customer_id = $_SESSION["user_customer_id"];
	$user_uuid = $_SESSION["user_id"];
	
	//is this a user specific request, or company wide
	$user_ids = passed_var("user_ids", "post");
	$start_date = passed_var("user_ids", "post");
	$end_date = passed_var("end_date", "post");
	try {
		//get any block dates
		$sql = "SELECT GROUP_CONCAT(bl.blocked_uuid) uuids
		FROM cse_blocked bl";
		if ($user_ids!="") {
			$sql .= "
				INNER JOIN cse_user_blocked cub
				ON bl.blocked_uuid = cub.blocked_uuid
				INNER JOIN ikase.cse_user usr
				ON cub.user_uuid = usr.user_uuid";
		}
		$sql .= "
		WHERE start_date = :start_date
		AND end_date = :end_date
		AND bl.customer_id = :customer_id
		AND bl.deleted = 'N'";
		if ($user_ids!="") {
			$sql .= "
				AND usr.user_id IN (" . str_replace("|", ",", $user_ids) . ")";
		}
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("start_date", $start_date);
		$stmt->bindParam("end_date", $end_date);
		$stmt->execute();
		//$blocks = $stmt->fetchAll(PDO::FETCH_OBJ);
		$block = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		//delete any blocks in that date range, unless user specific
		if (is_object($block)) {
			if ($block->uuids!="") {
				$sql = "UPDATE `cse_user_blocked`
				SET attribute = 'cancelled',
				`deleted` = 'Y'
				WHERE blocked_uuid IN ('" . implode("','", $block->uuids) . ")
				AND customer_id = :customer_id";
				
				$db = getConnection();
				$stmt = $db->prepare($sql);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
				$stmt = null; $db = null;
			}
		}
		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		//die($sql);
		
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}		
}
function addBlock() {
	session_write_close();
	
	$customer_id = $_SESSION["user_customer_id"];
	$user_uuid = $_SESSION["user_id"];
	
	//is this a user specific request, or company wide
	$user_ids = passed_var("assignee", "post");
	$start_date = passed_var("start_date", "post");
	$start_date = date("Y-m-d H:i:s", strtotime($start_date));
	
	$end_date = passed_var("end_date", "post");
	$end_date = date("Y-m-d H:i:s", strtotime($end_date));
	
	$recurring_count = passed_var("recurring_count", "post");
	if ($recurring_count=="9999") {
		$end_date = "2100-12-31 00:00:00";
	}
	$recurring_span = passed_var("recurring_span", "post");
	
	//if we have a span, must calculate the end date
	if ($recurring_count!="9999" && $recurring_span!="") {
		
		switch($recurring_span) {
			case "week":
				$multiplyer = ($recurring_count * 7);
				$days_add = " +" . $multiplyer . " days";
				break;
			case "2_weeks":
				$multiplyer = ($recurring_count * 14);
				$days_add = " +" . $multiplyer . " days";
				break;
			case "month":
				$multiplyer = ($recurring_count * 1);
				$days_add = " +" . $multiplyer . " month";					
				break;
		}
		$end_date = date("Y-m-d", strtotime($start_date . $days_add));
	}
	
	$blocked_uuid = uniqid("BL", false);
	//insert only once
	$sql = "INSERT INTO cse_blocked (blocked_uuid, start_date, end_date, recurring_count, recurring_span, customer_id)
	SELECT :blocked_uuid, :start_date, :end_date, :recurring_count, :recurring_span, :customer_id
	FROM dual
						WHERE NOT EXISTS (
							SELECT * 
							FROM `cse_blocked` 
							WHERE start_date = :start_date
							AND end_date = :end_date
							AND customer_id = :customer_id
							AND deleted = 'N'
						)";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("blocked_uuid", $blocked_uuid);
		$stmt->bindParam("start_date", $start_date);
		$stmt->bindParam("end_date", $end_date);
		$stmt->bindParam("recurring_count", $recurring_count);
		$stmt->bindParam("recurring_span", $recurring_span);
		$stmt->execute();
		
		$count = $stmt->rowCount();
		$blocked_id = 0;
		if ($count==1) {
			$blocked_id = $db->lastInsertId();
		}
		$stmt = null; $db = null;
		
		if ($blocked_id > 0) {
			$arrUserIDs = explode(",", $user_ids);
			foreach($arrUserIDs as $user_block_id) {
				//attach to blocked
				$user_blocked_uuid = uniqid("UB", false);
				$sql = "INSERT INTO `cse_user_blocked`
				(`user_blocked_uuid`, `user_uuid`, `blocked_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
	";
				$sql .= "
				SELECT :user_blocked_uuid, `user_uuid`, :blocked_uuid, 'main', :today, :user_uuid, :customer_id
				FROM ikase.cse_user
				WHERE user_id = :user_id";
				
				$today = date("Y-m-d H:i:s");
				$db = getConnection();
				$stmt = $db->prepare($sql);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->bindParam("user_blocked_uuid", $user_blocked_uuid);
				$stmt->bindParam("blocked_uuid", $blocked_uuid);
				$stmt->bindParam("today", $today);
				$stmt->bindParam("user_uuid", $user_uuid);
				$stmt->bindParam("user_id", $user_block_id);
				$stmt->execute();
				$stmt = null; $db = null;
			}
		}
		//track it
		$track = trackBlock("insert", $blocked_id);
		
		echo json_encode(array("success"=>true, "tracked"=>$track));
	} catch(PDOException $e) {
		//die($sql);
		
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function getICSCalendar() {
	session_write_close();
	
	$customer_id = $_SESSION["user_customer_id"];
	$today = date("Y-m-d") . " 00:00:00";
	$user_id = $_SESSION["user_id"];
	$arrIDs = array();
	//die($user_id);
	$sql = "SELECT cs.*, cs.setting_uuid uuid, 'user' setting_level
			FROM  `cse_setting` cs
			INNER JOIN `cse_setting_user` csu
			ON cs.setting_uuid = csu.setting_uuid
			WHERE 1
			AND `cs`.`deleted` = 'N'
			AND `cs`.`category` = 'calendar_access'
			AND `cs`.customer_id = :customer_id
			AND csu.user_uuid = :user_id
			LIMIT 0, 1";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("user_id", $user_id);
		$stmt->execute();
		$setting = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		//echo json_encode($setting);
		if (!is_object($setting)) {
			die(json_encode(array("succes"=>true, "events"=>"none", "event_ids"=>$arrIDs)));
		}
		//let's process the import
		//echo $setting->setting_value;
		try {
			$import = file_get_contents($setting->setting_value);
		} catch (Exception $e) {
			die(json_encode(array("succes"=>true, "events"=>"none", "event_ids"=>array())));
		}
		
		
		
		$events = explode("BEGIN:VEVENT", $import);
		//skip first one, header
		//die(print_r($events));
		for($int = 1; $int < count($events); $int++) {
			$event_dateandtime = "";
			$event_date = "";
			$event_enddate = "";
			$event_duration = 0;
			$event_uuid = "";
			$event_description = "";
			$full_address = "";
			$event_status = "";
			$event_title = "";
			$last_modified = "0000-00-00 00:00:00";
			$event = explode("\r\n", $events[$int]);
			$event_type = "imported";
			$event_class = "PRIVATE";	//assume a personal event
			
			$arrIDs = array();
			if (isset($kase)) {
				unset($kase);
			}
			//die(print_r($event));
			foreach($event as $item) {
				
				if ($item == "") {
					continue;
				}
				$item = str_replace("DTSTART:", "DTSTART]", $item);
				$item = str_replace("DTSTART;VALUE=DATE:", "DTSTART]", $item);
				$item = str_replace("DTEND:", "DTEND]", $item);
				$item = str_replace("DTEND;VALUE=DATE:", "DTEND]", $item);
				$item = str_replace("UID:", "UID]", $item);
				$item = str_replace("CLASS:", "CLASS]", $item);
				$item = str_replace("DESCRIPTION:", "DESCRIPTION]", $item);
				$item = str_replace("LOCATION:", "LOCATION]", $item);
				$item = str_replace("SUMMARY:", "SUMMARY]", $item);
				$item = str_replace("LAST-MODIFIED:", "LAST-MODIFIED]", $item);
				$item = str_replace("STATUS:", "STATUS]", $item);
				
				//no assumptions...
				$arrItem = explode("]", $item);
				switch($arrItem[0]) {
					case "DTSTART":
						//convert from Z format
						$event_dateandtime = date("Y-m-d H:i:s", strtotime($arrItem[1]));
						$event_date = date("Y-m-d", strtotime($arrItem[1]));
						//echo $arrItem[1] . " -> time:" . $event_dateandtime . "\r\n";
						
						//only today and forward, do not update old stuff
						$past = dateDiff("d", $today, $event_dateandtime);
						
						if ($past < 0) {
							echo $event_dateandtime . " => past:" . $past . "\r\n";
							die();
							continue 3;
						}
						break;
					case "DTEND":
						//convert from Z format
						$event_enddate = date("Y-m-d H:i:s", strtotime($arrItem[1]));
						//echo $arrItem[1] . " -> time:" . $event_enddate . "\r\n";
						break;
					case "UID":
						$event_uuid = $arrItem[1];
						break;
					case "CLASS":
						$event_class = $arrItem[1];
						//echo $event_class . "\r\n";
						if ($event_class!="PUBLIC") {
							echo $event_dateandtime . " => class:" . $event_class . "\r\n";
							//only marked public goes through
							continue 3;
						}
						break;
					case "DESCRIPTION":
						$event_description = $arrItem[1];
						$event_description = str_replace("\\,", ",", $event_description);
						break;
					case "LOCATION":
						$full_address = $arrItem[1];
						$full_address = str_replace("\\,", ",", $full_address);
						break;
					case "SUMMARY":
						$event_title = $arrItem[1];
						$event_title = str_replace("\\,", ",", $event_title);
						break;
					case "LAST-MODIFIED":
						//convert from Z format
						$last_modified = date("Y-m-d H:i:s", strtotime($arrItem[1]));
						break;
				}
			}
			$event_duration = dateDiff("n", $event_dateandtime, $event_enddate);
			//die("loc:" . $full_address);
			//die(print_r($event));
			//can/should we link
			//echo $event_title;
			$arrTitle = explode(":", $event_title);
			//die(print_r($arrTitle));
			if (count($arrTitle) > 1) {
				$blnTyped = false;
				switch($arrTitle[0]) {
					case "NP":
						$event_type = "new_prospect";
						$blnTyped = true;
						$event_title = str_replace("NP:", "", $event_title);
						break;
					case "P":
						$event_type = "personal";
						$blnTyped = false;
						$event_title = str_replace("P:", "", $event_title);
						break;
				}
				//maybe a case number
				if (!$blnTyped) {
					$sql = "SELECT case_uuid uuid
					FROM cse_case
					WHERE case_number = :case_number
					AND customer_id = :customer_id";
					$db = getConnection();
					$stmt = $db->prepare($sql);
					$stmt->bindParam("customer_id", $customer_id);
					$stmt->bindParam("case_number", $arrTitle[0]);
					$stmt->execute();
					$kase = $stmt->fetchObject();
					$stmt->closeCursor(); $stmt = null; $db = null;
				}
			}
			
			if ($event_type != "personal") {
				//firm calendar
				$sort_order = 0;
			} else {
				//employee calendar
				$sort_order = 5;
			}
			$customer_calendar = getCalendarBySortOrder($sort_order);
			//die(print_r($customer_calendar));
			//import into ikase
			//is it already in ikase?
			$sql = "SELECT etra.event_id, etra.time_stamp 
			FROM cse_event_track etra
			INNER JOIN cse_event eve
			ON etra.event_uuid = eve.event_uuid
			WHERE etra.event_uuid = :event_uuid
			AND etra.customer_id = :customer_id
			AND etra.operation = 'imported'";
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->bindParam("event_uuid", $event_uuid);
			$stmt->execute();
			$ikase_event = $stmt->fetchObject();
			$stmt->closeCursor(); $stmt = null; $db = null;
			//die(print_r($ikase_event));
			if (is_object($ikase_event)) {
				$event_title = str_replace($arrTitle[0] . ":", "", $event_title);
				
				$arrIDs[] = $ikase_event->event_id;
				//update if the date is different
				
				if ($ikase_event->time_stamp==$last_modified) {
					continue;
				}
				//echo $last_modified;
				//die(print_r($ikase_event));
				$sql = "UPDATE cse_event
				SET event_dateandtime = :event_dateandtime,
				event_date = :event_date,
				event_title = :event_title,
				event_duration = :event_duration,
				event_description = :event_description,
				full_address = :full_address
				WHERE event_uuid = :event_uuid
				AND customer_id = :customer_id";

				$db = getConnection();
				$stmt = $db->prepare($sql);
				$stmt->bindParam("event_dateandtime", $event_dateandtime);
				$stmt->bindParam("event_date", $event_date);
				$stmt->bindParam("event_title", $event_title);
				$stmt->bindParam("event_uuid", $event_uuid);
				$stmt->bindParam("event_duration", $event_duration);
				$stmt->bindParam("event_description", $event_description);
				$stmt->bindParam("full_address", $full_address);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
				$stmt = null; $db = null;
				
				//track the change
				trackEvent("update", $ikase_event->event_id, $last_modified);
			} else {
				//die(print_r($event));
				$sql = "INSERT cse_event (event_uuid, event_type, event_date, event_dateandtime, event_title, event_duration, event_description, full_address, customer_id)
				VALUES (:event_uuid, :event_type, :event_date, :event_dateandtime, :event_title, :event_duration, :event_description, :full_address, :customer_id)";

				$db = getConnection();
				$stmt = $db->prepare($sql);
				$stmt->bindParam("event_uuid", $event_uuid);
				$stmt->bindParam("event_type", $event_type);
				$stmt->bindParam("event_date", $event_date);
				$stmt->bindParam("event_dateandtime", $event_dateandtime);
				$stmt->bindParam("event_title", $event_title);
				$stmt->bindParam("event_duration", $event_duration);
				$stmt->bindParam("event_description", $event_description);
				$stmt->bindParam("full_address", $full_address);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
				
				$event_id = $db->lastInsertId();
				$arrIDs[] = $event_id;
				
				$stmt = null; $db = null;
				
				//track the change
				trackEvent("imported", $event_id, $last_modified);
				
				$calendar_id = $customer_calendar->id;
				//attach to calendar
				//$customer_calendar = getCalendarInfo($calendar_id);
				//die(print_r($customer_calendar));
				if (is_object($customer_calendar)) {
					$calendar_event_uuid = uniqid("KE", false);
					$sql = "INSERT INTO cse_calendar_event (`calendar_event_uuid`, `calendar_uuid`, `user_uuid`, `event_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $calendar_event_uuid  ."', '" . $customer_calendar->uuid . "', '" . $user_id . "', '" . $event_uuid . "', '" . $customer_calendar->calendar . "', '" . $last_modified . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
					$db = getConnection();
					$stmt = $db->prepare($sql);
					$stmt->execute();
					$db = null; $stmt = null;					
					//echo $sql . "\r\n";
				}
				
				if (isset($kase)) {
					if (is_object($kase)) {
						//only attach to the case once
						$case_table_uuid = uniqid("KA", false);
						$attribute_1 = "import";
			
						//attach the event to the kase
						$sql = "INSERT INTO cse_case_event (`case_event_uuid`, `case_uuid`, `event_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
						SELECT '" . $case_table_uuid  ."', '" . $kase->uuid . "', '" . $event_uuid . "', '" . $attribute_1 . "', '" . $last_modified . "', '" . $_SESSION['user_id'] . "', '" . $customer_id . "'
						FROM dual
						WHERE NOT EXISTS (
							SELECT * 
							FROM `cse_case_event` 
							WHERE event_uuid = '" . $event_uuid . "'
							AND case_uuid = '" . $kase->uuid . "'
						)";
						//echo $sql . "\r\n";
						$db = getConnection();
						$stmt = $db->prepare($sql);
						$stmt->execute();
						$db = null; $stmt = null;
					}
				}
				print_r($event);
				die("one at a time");
			}
		}
		die(json_encode(array("succes"=>true, "events"=>"many", "event_ids"=>$arrIDs)));
		
	} catch(PDOException $e) {
		//die($sql);
		
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	
}
function getPersonalCalendars() {
	session_write_close();
	$sql = "SELECT CONCAT(user.user_first_name, ' ', user.user_last_name) name, `user`.user_id id, IF(ISNULL(cuc.user_uuid), 0, 1) assigned, IFNULL(cuc.attribute, '') permissions
			FROM ikase.`cse_user` user 
			LEFT OUTER JOIN cse_user_calendar cuc
			ON (user.user_uuid = cuc.calendar_uuid 
			AND cuc.user_uuid IN (SELECT user_uuid FROM ikase.cse_user WHERE user_id = " . $_SESSION['user_plain_id'] . " AND customer_id = " . $_SESSION['user_customer_id'] . "))
			INNER JOIN `cse_customer` cus
			ON user.customer_id = cus.customer_id
			LEFT OUTER JOIN `cse_user_job` cjob
			ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
			LEFT OUTER JOIN `cse_job` job
			ON cjob.job_uuid = job.job_uuid
			WHERE user.deleted = 'N'
			AND user.user_id != " . $_SESSION['user_plain_id'] . "
			AND user.customer_id = " . $_SESSION['user_customer_id'] . "
			AND user.personal_calendar = 'Y'
			ORDER by user.user_id";
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$users = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		//die(print_r($kases));
        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($users);
        } else {
            echo $_GET['callback'] . '(' . json_encode($users) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCalendars() {
	session_write_close();
    $sql = "SELECT cal.*, cal.calendar_id id, cal.calendar_uuid uuid
			FROM  `cse_calendar` cal
			WHERE 1
			AND cal.deleted = 'N'
			AND `cal`.customer_id = " . $_SESSION['user_customer_id'] . "
			ORDER BY cal.`sort_order`";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$customers_calendar = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;

        // Include support for JSONP requests
         echo json_encode($customers_calendar);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTheCalendar($calendar_id) {
	session_write_close();
    $sql = "SELECT cal.*, cal.calendar_id id, cal.calendar_uuid uuid
			FROM  `cse_calendar` cal
			WHERE `cal`.customer_id = " . $_SESSION['user_customer_id'] . "
			AND `cal`.deleted = 'N'
			AND `cal`.calendar_id = '" . $calendar_id . "'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $calendar_id);
		$stmt->execute();
		$the_customer_calendar = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        // Include support for JSONP requests
         echo json_encode($the_customer_calendar);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCalendarInfo($calendar_id) {
	session_write_close();
    $sql = "SELECT cal.*, cal.calendar_id id, cal.calendar_uuid uuid
			FROM  `cse_calendar` cal
			WHERE `cal`.customer_id = " . $_SESSION['user_customer_id'] . "
			AND `cal`.deleted = 'N'
			AND `cal`.calendar_id = '" . $calendar_id . "'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $calendar_id);
		$stmt->execute();
		$the_customer_calendar = $stmt->fetchObject();
		$db = null;

        // Include support for JSONP requests
         return $the_customer_calendar;        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCalendarBySortOrder($sort_order) {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
    $sql = "SELECT cal.*, cal.calendar_id id, cal.calendar_uuid uuid
			FROM  `cse_calendar` cal
			WHERE `cal`.customer_id = :customer_id
			AND `cal`.deleted = 'N'
			AND `cal`.sort_order = :sort_order";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("sort_order", $sort_order);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$the_customer_calendar = $stmt->fetchObject();
		
		$db = null;

         return $the_customer_calendar;        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addCalendar() {
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$table_id = "";
	$partie_id = "";
	$case_uuid = "";
	$send_document_id = "";
	//default attribute
	$table_attribute = "main";
	$db = getConnection();
	
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="sort_order") {
			$sort_order = $value;
			if ($sort_order=="") {
				//get the last sort order
				$sql = "SELECT IFNULL(MAX(sort_order), -1) sort_order
				FROM cse_calendar 
				WHERE customer_id = " . $_SESSION['user_customer_id'];
				try { 	
					$stmt = $db->prepare($sql);  
					$stmt->execute();
					$max_calendar = $stmt->fetchObject();
					$value = $max_calendar->sort_order + 1;
				} catch(PDOException $e) {	
					echo '{"error":{"text":'. $e->getMessage() .'}}'; 
				}
			}
		}
		if ($fieldname=="table_id") {
			continue;
		}
		if (strpos($fieldname, "_uuid") > -1) {
			continue;
		}
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	
	$table_uuid = uniqid("KS", false);
	$sql = "INSERT INTO `cse_" . $table_name ."` (`customer_id`, `" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $_SESSION['user_customer_id'] . "', '" . $table_uuid . "', " . implode(",", $arrSet) . ")";
	//die($sql);
	try { 	
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid)); 
		
		//trackNote("insert", $new_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	$db = null;
}
function assignCalendar() {
	$request = Slim::getInstance()->request();
	$calendar_id = passed_var("calendar_id", "post");
	$user_id = passed_var("user_id", "post");
	$permissions = passed_var("permissions", "post");
	if ($permissions=="") {
		$permissions = "read";
	}
	$user_calendar = getUserInfo($calendar_id);
	$calendar_uuid = $user_calendar->user_uuid;
	
	$user_user = getUserInfo($user_id);
	$user_uuid = $user_user->user_uuid;
	
	joinTables("user", "calendar", $user_uuid,$calendar_uuid, $permissions, false);
	
	echo json_encode(array("success"=>true));
}
function unassignCalendar() {
	$request = Slim::getInstance()->request();
	$calendar_id = passed_var("calendar_id", "post");
	$user_id = passed_var("user_id", "post");
		
	$user_calendar = getUserInfo($calendar_id);
	$calendar_uuid = $user_calendar->user_uuid;
	
	$user_user = getUserInfo($user_id);
	$user_uuid = $user_user->user_uuid;
	
	unjoinTables("user", "calendar", $user_uuid,$calendar_uuid, "", false);
	
	echo json_encode(array("success"=>true));
}
function updateCalendar() {
	$request = Slim::getInstance()->request();
	$arrSet = array();
	$where_clause = "";
	$table_name = "";
	$table_id = "";
	$table_attribute = "";
	$blnColors = false;
	$source_message_id = "";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if (strpos($fieldname, "_uuid") > -1) {
			continue;
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
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$db = null;
		
		echo json_encode(array("success"=>$table_id)); 
		
		//track now
		//trackNote("update", $table_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function trackBlock($operation, $blocked_id) {
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "INSERT INTO `cse_blocked_track`
	(`user_uuid`, `user_logon`, `operation`, `time_stamp`, `blocked_id`, `blocked_uuid`, `start_date`, `end_date`, `recurring_count`, `recurring_span`, `customer_id`, `deleted`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', '". date("Y-m-d H:i:s") . "', `blocked_id`, `blocked_uuid`, `start_date`, `end_date`, `recurring_count`, `recurring_span`, `customer_id`, `deleted`
	FROM cse_blocked
	WHERE 1
	AND blocked_id = $blocked_id
	AND customer_id = $customer_id
	LIMIT 0, 1";
	//echo $sql;
	

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		/*
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("blocked_id", $blocked_id);
		$stmt->bindParam("operation", $operation);
		*/
		$stmt->execute();
		
		$new_id = $db->lastInsertId();
		
		$stmt = null; $db = null;
		
		return $new_id;
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		return false;
	}
}
?>