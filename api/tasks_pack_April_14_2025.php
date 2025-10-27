<?php
use Slim\Routing\RouteCollectorProxy;

$app->group('', function (RouteCollectorProxy $app) {
	$app->group('/tasks', function (RouteCollectorProxy $app) {
		$app->get('', 'getTasks');
		$app->get('/recent', 'getRecentTasks');
		$app->post('/transfer', 'transferTasks');
		$app->get('/{task_id}', 'getTask');
	});

	$app->get('/tasksbydates/{start}/{end}', 'getTasksByDates');
	$app->get('/kasetasksbydates/{start}/{case_id}', 'getKaseTasksByDates');
	$app->get('/tasksbydatescompleted/{start}/{end}', 'getCompletedTasksByDates');

	$app->get('/taskinbox', 'getTaskInbox');
	$app->get('/taskclosedinbox', 'getTaskClosedInbox');
	$app->get('/tasksuser/{user_id}', 'getUserTasks');
	$app->get('/tasksuser/{user_id}/{day}', 'getUserTasksByDate');
	$app->get('/tasksuser/{user_id}/{day}/{end_day}', 'getUserTasksByDateRange');

	$app->get('/taskcaseinbox/{case_id}', 'getTaskCaseInbox');
	$app->get('/taskcaseinboxclosed/{case_id}', 'getTaskCaseInboxClosed');
	$app->get('/taskcaseinboxdeleted/{case_id}', 'getTaskCaseInboxDeleted');
	$app->get('/taskcasedayinbox/{case_id}/{day}', 'getTaskCaseDayInbox');
	$app->get('/tasksingledaycompletedall/{day}', 'getCompletedTasksAllUsers');
	$app->get('/taskcompleted', 'getCompletedTasks');

	$app->get('/taskdayinbox/{day}', 'getTaskDayInbox');
	$app->get('/taskdaycompleted/{day}', 'getTaskDayCompleted');

	$app->get('/tasksingledayinboxall/{day}', 'getTaskSingleDayInboxAllUser');
	$app->get('/tasksinledayinbox/{day}', 'getTaskSingleDayInbox');
	$app->get('/taskweekinbox/{week}/{year}', 'getTaskWeekInbox');
	$app->get('/taskinboxnew', 'newTaskInbox');
	$app->get('/taskinboxcheck', 'checkTaskInbox');
	$app->get('/taskoutbox', 'getTaskOutbox');
	$app->get('/taskoutboxcount', 'getTaskOutboxCount');
	$app->get('/taskcaseoutbox/{case_id}', 'getTaskCaseOutbox');
	$app->get('/taskdayoutbox/{day}', 'getTaskDayOutbox');
	$app->get('/taskoutboxbydate/{start}/{end}', 'getTaskOutboxByDate');
	$app->get('/taskhistory/{task_id}', 'getTaskHistory');
	$app->get('/tasksummary', 'getTasksSummary');

	//overdues
	$app->get('/overduetasks', 'getOverDueTasks');
	$app->get('/overduefirmtasks', 'getOverDueFirmTasks');
	$app->get('/overdueusertasks/{nickname}', 'getOverDueUserTasks');
	$app->get('/overduefirmtaskscount', 'getOverDueFirmTasksCount');
	$app->get('/overduetaskscount', 'getOverDueTasksCount');
	$app->get('/overduekasetasks/{case_id}', 'getOverDueKaseTasks');
	$app->get('/overduekasetaskscount/{case_id}', 'getOverDueKaseTasksCount');

	//posts
	$app->group('/task', function (RouteCollectorProxy $app) {
		$app->post('/read', 'readTask');
		$app->post('/restore', 'restoreTask');
		$app->post('/delete', 'deleteTask');
		$app->post('/add', 'addTask');
		$app->post('/update', 'updateTask');
		$app->post('/close', 'closeTask');
		$app->post('/update/date', 'updateTaskDate');
	});

	$app->get('/task_types', 'getTaskTypes');
	$app->post('/task_type/add', 'saveTaskTypes');
	$app->post('/task_type/update', 'updateTaskTypes');
})->add(\Api\Middleware\Authorize::class);

function getTasks() {
	session_write_close();
    $sql = "SELECT tsk.*, tsk.task_id id, tsk.task_uuid uuid, 
		thread.thread_id, thr.thread_uuid, 
		tsk.task_id id, tsk.task_uuid uuid, 
		ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, ccase.cpointer
		FROM `cse_task` tsk
		INNER JOIN `cse_thread_task` thr
		ON tsk.task_uuid = thr.task_uuid
		INNER JOIN cse_thread thread
		ON thr.thread_uuid = thread.thread_uuid
		LEFT OUTER JOIN cse_case_task cct
		ON tsk.task_uuid = cct.task_uuid
		LEFT OUTER JOIN cse_case ccase
		ON cct.case_uuid = ccase.case_uuid
		LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
		LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
		LEFT OUTER JOIN `cse_case_corporation` ccorp
		ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` employer
		ON ccorp.corporation_uuid = employer.corporation_uuid
		
		LEFT OUTER JOIN `cse_case_corporation` pcorp
		ON (ccase.case_uuid = pcorp.case_uuid AND pcorp.attribute = 'plaintiff' AND pcorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` plaintiff
		ON pcorp.corporation_uuid = plaintiff.corporation_uuid
		WHERE 1
		AND tsk.customer_id = " . $_SESSION['user_customer_id'] . "
		AND tsk.deleted = 'N'
		#AND tsk.task_type != 'closed' 
		AND thread.deleted = 'N'
		ORDER BY tsk.task_dateandtime DESC";
	if ($_SESSION['user_customer_id']==1121) { 
		if ($_SERVER['REMOTE_ADDR']=='172.119.228.204') {
			//die( $sql);
		}
	}
	try {
		$tasks = DB::select($sql);
		//die(print_r($tasks));
        echo json_encode($tasks);     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCompletedTasksByDates($start, $end) {
	getCompletedTasks("", "n", $start, $end);
}
function getKaseTasksByDates($start, $end, $case_id) {
	getTasksByDates($start, $end, false, $case_id);
}
function getTasksByDates($start, $end, $blnCompleted = false, $case_id = -1) {
	session_write_close();
    $sql = "SELECT DISTINCT tsk.*, tsk.task_id id, tsk.task_uuid uuid, 
		tsk.task_id id, tsk.task_uuid uuid, ctu.read_status, '' `read_date`, 
		ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, ccase.cpointer,
		IF (tsk.`from` = '', ccm.last_update_user, tsk.`from`) `originator`, IFNULL(ccm.last_updated_date, '0000-00-00 00:00:00') `date_assigned`,
		IFNULL(plaintiff.company_name, '') plaintiff_name
		FROM `cse_task` tsk";
	if ($case_id == -1) {
		$sql .= " 
		INNER JOIN cse_task_user utask
		ON tsk.task_uuid = utask.task_uuid AND tsk.assignee = '" . $_SESSION['user_nickname'] . "' AND utask.deleted = 'N'";
	}
	$sql .= "
		LEFT OUTER JOIN `cse_task_user` ctu
		ON tsk.task_uuid = ctu.task_uuid AND ctu.deleted = 'N'
		LEFT OUTER JOIN `cse_case_task` ccm
		ON tsk.task_uuid = ccm.task_uuid
		LEFT OUTER JOIN `cse_case` ccase
		ON ccm.case_uuid = ccase.case_uuid
		LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
		LEFT OUTER JOIN ";
	if (($_SESSION['user_customer_id']==1033)) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
		LEFT OUTER JOIN `cse_case_corporation` ccorp
		ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` employer
		ON ccorp.corporation_uuid = employer.corporation_uuid
		
		LEFT OUTER JOIN `cse_case_corporation` pcorp
		ON (ccase.case_uuid = pcorp.case_uuid AND pcorp.attribute = 'plaintiff' AND pcorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` plaintiff
		ON pcorp.corporation_uuid = plaintiff.corporation_uuid
		
		WHERE 1
	AND tsk.customer_id = " . $_SESSION['user_customer_id'] . "
	AND tsk.deleted = 'N'";
	if ($blnCompleted) {
		$sql .= "
		AND tsk.task_type = 'closed'";
	}
	
	if ($case_id > -1) {
		$sql .= "
		AND ccase.case_id = :case_id";
	}
	$sql .= " 
		AND `ctu`.`type` = 'to'
		AND `ctu`.deleted = 'N'";
		if ($start != $end) {
			$sql .=	" AND CAST(tsk.task_dateandtime AS DATE) BETWEEN '" . $start . "' AND '" . $end . "'";
		} else {
			$sql .=	" AND CAST(tsk.task_dateandtime AS DATE) = '" . $start . "'";
		}
		$sql .=	" ORDER BY tsk.task_dateandtime DESC";	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($case_id > -1) {
			$stmt->bindParam("case_id", $case_id);
		}
		$stmt->execute();
		$tasks = $stmt->fetchAll(PDO::FETCH_OBJ);

        echo json_encode($tasks);     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTaskClosedInbox() {
	//redundant function, we don't need it or use it
	getTaskInbox("open");
}
function getUserTasksByDateRange($user_id, $date, $end_date) {
	if (!is_numeric($user_id)){
		$error = array("error"=> array("text"=>"no id"));
		echo json_encode($error);
		die();
	}
	$user = getUserInfo($user_id);
	$user_uuid = $user->uuid;

	getTaskInbox("user", "", $user_uuid, $date, $end_date);
}
function getUserTasksByDate($user_id, $date) {
	if (!is_numeric($user_id)){
		$error = array("error"=> array("text"=>"no id"));
		echo json_encode($error);
		die();
	}
	$user = getUserInfo($user_id);
	$user_uuid = $user->uuid;
	getTaskInbox("user", "", $user_uuid, $date, $date);
}
function getUserTasks($user_id) {
	if (!is_numeric($user_id)){
		$error = array("error"=> array("text"=>"no id"));
		echo json_encode($error);
		die();
	}
	$user = getUserInfo($user_id);
	$user_uuid = $user->uuid;
	getTaskInbox("user", "", $user_uuid);
}
function getTaskInbox($exclude_task_type = "closed", $new = "", $user_uuid = "", $start_date = "", $end_date = "") {
	session_write_close();
	
	$blnUseDates = true;
	if ($exclude_task_type=="user") {
		$exclude_task_type = "closed";
		$blnUseDates = false;
	}
	if ($user_uuid=="") {
		$user_uuid = $_SESSION['user_id'];
	}
    $sql = "SELECT * FROM (SELECT DISTINCT tsk.*, 
		tsk.task_id id, tsk.task_uuid uuid, ctu.read_status, '' `read_date`, 
		ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, ccase.cpointer,
		IFNULL(usr.nickname, tsk.task_from) `originator`, IFNULL(ccm.last_updated_date, '0000-00-00 00:00:00') `date_assigned`,
		IFNULL(plaintiff.company_name, '') plaintiff_name
		FROM `cse_task` tsk
		INNER JOIN `cse_task_user` ctu
		ON tsk.task_uuid = ctu.task_uuid AND ctu.deleted = 'N' AND tsk.deleted = 'N' 
		LEFT OUTER JOIN `cse_case_task` ccm
		ON tsk.task_uuid = ccm.task_uuid
		
		LEFT OUTER JOIN ikase.cse_user usr
		ON ccm.last_update_user = usr.user_uuid 
		
		LEFT OUTER JOIN `cse_case` ccase
		ON ccm.case_uuid = ccase.case_uuid
		LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
		LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
		LEFT OUTER JOIN `cse_case_corporation` ccorp
		ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` employer
		ON ccorp.corporation_uuid = employer.corporation_uuid
		
		LEFT OUTER JOIN `cse_case_corporation` pcorp
		ON (ccase.case_uuid = pcorp.case_uuid AND pcorp.attribute = 'plaintiff' AND pcorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` plaintiff
		ON pcorp.corporation_uuid = plaintiff.corporation_uuid
		INNER JOIN `cse_case_injury` cinj ON ccase.case_uuid = cinj.case_uuid 
		AND cinj.deleted = 'N' 
		AND cinj.`attribute` = 'main' 
		INNER JOIN `cse_injury` inj ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
		
		 
		
		WHERE 1
		AND tsk.customer_id = " . $_SESSION['user_customer_id'] . "
		AND ctu.user_uuid = '" . $user_uuid . "'
		AND tsk.task_type != '" . $exclude_task_type . "'";
		if ($_SESSION["user_customer_id"] == "1121") {
			$sql .= " AND CAST(task_dateandtime AS DATE) > '2017-04-13' OR task_dateandtime = '0000-00-00 00:00:00'";
		}
		$sql .= " AND tsk.task_type != 'closed'
		AND tsk.deleted = 'N'
		AND (`ctu`.`type` = 'to' OR `ctu`.`type` = 'cc')
		AND `ctu`.deleted = 'N'
		AND tsk.deleted = 'N'";
		
	if ($new=="Y") {
		$sql .= " AND ctu.read_status = 'N'";
	}
	if ($_SESSION["user_customer_id"] != "1121") {
	if ($blnUseDates) {
		//$six_months_ago = mktime(0, 0, 0, date("m") - 2,   date("d"),   date("Y"));
		//Change to today forward per thomas 7/27/2017 
		//older tasks will now show up under overdue tasks
		$six_months_ago = mktime(0, 0, 0, date("m"),   date("d") - 2,   date("Y"));
		
		$sql .=	" AND (tsk.task_date < '" . date("Y-m-d", $six_months_ago) . "'
	 OR tsk.`task_dateandtime` < '" . date("Y-m-d", $six_months_ago) . "')";
	}
	
		if ($start_date!="") {
			if ($start_date==$end_date) {
				$sql .=	" AND CAST(tsk.task_dateandtime AS DATE) = '" . $start_date . "'";
			} else {
				
				$sql .=	" AND CAST(tsk.task_dateandtime AS DATE) BETWEEN '" . $start_date . "'
				AND  '" . $end_date . "'";
			}
			//die($sql);
		}
	}
	//Change to ASC per thomas 7/27/2017 
	$sql .=	"
	) task_list
	ORDER BY task_dateandtime ASC";
	
	//if (//$_SESSION['user_customer_id']==1121) { 
		if ($_SERVER['REMOTE_ADDR']=='172.112.165.93') {
			//die( $sql);#AND CAST(task_dateandtime AS DATE) > '2017-04-13' OR task_dateandtime = '0000-00-00 00:00:00'
		}
	//}
	if ($_SERVER['REMOTE_ADDR']=='47.154.255.60') {
		//die($sql);
	}
	try {
		$tasks = DB::select($sql);

        echo json_encode($tasks);     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTaskCaseDayInbox($case_id, $day) {
	session_write_close();
	
	$end_day = mktime(0, 0, 0, date("m", strtotime($day)),   date("d", strtotime($day)) + 1,   date("Y", strtotime($day)));
	$end_day = date("Y-m-d", $end_day);
	//echo $day . " - " . $end_day;
    $sql = "SELECT DISTINCT tsk.*, tsk.task_id id, tsk.task_uuid uuid, 
		ctu.read_status, '' `read_date`, 
		ccase.case_id, ccase.`case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, ccase.cpointer,
		IF (tsk.`from` = '', ccm.last_update_user, tsk.`from`) `originator`, IFNULL(ccm.last_updated_date, '0000-00-00 00:00:00') `date_assigned`
		FROM `cse_task` tsk
		LEFT OUTER JOIN `cse_task_user` ctu
		ON tsk.task_uuid = ctu.task_uuid AND ctu.`type` = 'to' AND ctu.deleted = 'N'
		INNER JOIN `cse_case_task` ccm
		ON tsk.task_uuid = ccm.task_uuid
		INNER JOIN `cse_case` ccase
		ON ccm.case_uuid = ccase.case_uuid
		
		WHERE 1
		AND tsk.customer_id = " . $_SESSION['user_customer_id'] . "
		AND tsk.deleted = 'N'
		AND ccase.case_id = :case_id
		AND tsk.task_dateandtime BETWEEN :day AND :end_day
		ORDER BY tsk.task_dateandtime DESC";
	//echo $sql . "\r\n";	die();
	if ($_SERVER['REMOTE_ADDR']=='172.119.228.204') {
		//die( $sql);
	}
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("day", $day);
		$stmt->bindParam("end_day", $end_day);
		$stmt->execute();
		$tasks = $stmt->fetchAll(PDO::FETCH_OBJ);

        echo json_encode($tasks);     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTaskCaseInboxDeleted($case_id) {
	$_SESSION["deleted_tasks"] = true;
	getTaskCaseInbox($case_id);
}
function getTaskCaseInboxClosed($case_id) {
	$_SESSION["closed_tasks"] = true;
	getTaskCaseInbox($case_id);
}
function getTaskCaseInbox($case_id, $blnReturn = false) {
	$blnClosedTasks = false;
	if (isset($_SESSION["closed_tasks"])) {
		$blnClosedTasks = $_SESSION["closed_tasks"];
		unset($_SESSION["closed_tasks"]);
	}
	
	$blnDeletedTasks = false;
	if (isset($_SESSION["deleted_tasks"])) {
		$blnDeletedTasks = $_SESSION["deleted_tasks"];
		unset($_SESSION["deleted_tasks"]);
	}
	session_write_close();
	
	$kase = getKaseInfo($case_id);
	$case_uuids = $kase->uuid;
	$related_kases = getRelatedKases($case_id);
	
	if (count($related_kases) > 0) {
		$arrRelatedList = array();
		foreach($related_kases as $related_kase) {
			$arrRelatedList[] = $related_kase->case_uuid;
		}
		$case_uuids = "'" . implode("','", $arrRelatedList) . "'";
	}
	/*
    $sql = "SELECT DISTINCT tsk.*, tsk.task_id id, tsk.task_uuid uuid, 
		ctu.read_status, '' `read_date`, 
		ccase.case_id, ccase.`case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, ccase.cpointer,
		IF (tsk.`from` = '', ccm.last_update_user, tsk.`from`) `originator`, IFNULL(ccm.last_updated_date, '0000-00-00 00:00:00') `date_assigned`
		FROM `cse_task` tsk
		LEFT OUTER JOIN `cse_task_user` ctu
		ON tsk.task_uuid = ctu.task_uuid AND ctu.`type` = 'to'
		INNER JOIN `cse_case_task` ccm
		ON tsk.task_uuid = ccm.task_uuid
		INNER JOIN `cse_case` ccase
		ON ccm.case_uuid = ccase.case_uuid
		
		WHERE 1
		AND tsk.customer_id = " . $_SESSION['user_customer_id'] . "
		AND tsk.deleted = 'N'
		AND ccase.case_id = " . $case_id . "
		ORDER BY tsk.task_dateandtime DESC";
	
	*/
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "
	SELECT DISTINCT injury_info.injury_dates, `main_case_id`, `main_case_number`, tsk.*, tsk.task_id id, tsk.task_uuid uuid, 
	ctu.read_status, '' `read_date`, 
	ccase.case_id, ccase.`case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, ccase.cpointer,
	IFNULL(usr.nickname, tsk.task_from) `originator`, IFNULL(ccm.last_updated_date, '0000-00-00 00:00:00') `date_assigned`,
	IFNULL(inj.injury_id, '') doi_id, IFNULL(inj.start_date, '') doi_start, IFNULL(inj.end_date, '') doi_end
	";
	if ($blnDeletedTasks || $blnClosedTasks) {
		$sql .= "
		, IFNULL(ctt.user_logon, '') track_user, IFNULL(ctt.time_stamp, '') track_date,
		IFNULL(ctt.task_type, '') track_type";
	}
	$sql .= "
	FROM `cse_task` tsk
				
	LEFT OUTER JOIN `cse_injury_task` `citask`
	ON `tsk`.`task_uuid` = `citask`.`task_uuid` AND citask.deleted = 'N'
	LEFT OUTER JOIN `cse_injury` inj
	ON citask.injury_uuid = inj.injury_uuid	
	
	LEFT OUTER JOIN `cse_task_user` ctu
	ON tsk.task_uuid = ctu.task_uuid AND ctu.`type` = 'to' AND ctu.deleted = 'N'
	
	INNER JOIN `cse_case_task` ccm
	ON tsk.task_uuid = ccm.task_uuid AND `ccm`.case_uuid IN (" . $case_uuids . ")
	
	LEFT OUTER JOIN ikase.cse_user usr
	ON ccm.last_update_user = usr.user_uuid 
	
	INNER JOIN `cse_case` ccase
	ON ccm.case_uuid = ccase.case_uuid
	INNER JOIN ( 
		SELECT DISTINCT ccase.case_id `main_case_id`, ccase.case_number `main_case_number`, IFNULL(injury_dates, '') injury_dates
		FROM cse_case ccase

		INNER JOIN (
			SELECT DISTINCT ccase.case_id, ccase.case_uuid
			FROM  cse_case_injury cci
			INNER JOIN cse_case ccase
			ON cci.case_uuid = ccase.case_uuid
				
			INNER JOIN (
				SELECT injury_uuid 
				FROM cse_case_injury cinj 
				INNER JOIN cse_case ccase
				ON cinj.case_uuid = ccase.case_uuid
				where case_id = :case_id
			) injury_list
			ON cci.injury_uuid = injury_list.injury_uuid
		) related_cases
		ON ccase.case_uuid = related_cases.case_uuid

		LEFT OUTER JOIN (
			SELECT case_list.case_id main_case_id, case_list.case_uuid, GROUP_CONCAT(CONCAT(injury_id, '|', start_date, '|', end_date, '|', case_number)) injury_dates
			FROM (
				SELECT case_id, case_uuid, case_number
				FROM (
					SELECT DISTINCT ccase.case_id, ccase.case_uuid, ccase.case_number
					FROM  cse_case_injury cci
					INNER JOIN cse_case ccase
					ON cci.case_uuid = ccase.case_uuid
					AND ccase.customer_id = :customer_id
					AND ccase.deleted = 'N'	
					INNER JOIN (
						SELECT inj.*
						FROM cse_case_injury cinj 
						INNER JOIN cse_case ccase
						ON cinj.case_uuid = ccase.case_uuid
						INNER JOIN cse_injury inj
						ON cinj.injury_uuid = inj.injury_uuid
						where case_id = :case_id
					) injury_list
					ON cci.injury_uuid = injury_list.injury_uuid
				) all_cases
				WHERE case_id != :case_id
			) case_list
			INNER JOIN cse_case_injury cci
			ON cci.case_uuid = case_list.case_uuid AND cci.attribute != 'related'
			INNER JOIN cse_injury inj
			ON cci.injury_uuid = inj.injury_uuid
			GROUP BY case_uuid
		) case_injuries
		ON ccase.case_id = case_injuries.main_case_id 
	) injury_info
	ON ccase.case_id = injury_info.main_case_id
	";
	
	if ($blnDeletedTasks || $blnClosedTasks) {
		$operation = "delete";
		if ($blnClosedTasks) {
			$operation = "update";
		}
		$sql .= "
		LEFT OUTER JOIN (
			SELECT DISTINCT trk.task_uuid, trk.user_logon, trk.time_stamp, trk.task_type
			FROM cse_task_track trk
			INNER JOIN cse_case_task cct
			ON trk.task_uuid = cct.task_uuid
			INNER JOIN cse_case ccase
			ON cct.case_uuid = ccase.case_uuid
			INNER JOIN (
				SELECT task_uuid, MAX(task_track_id) max_id
				FROM cse_task_track 
				WHERE operation = '" . $operation . "'
				GROUP BY task_uuid
			) max_track
			ON trk.task_track_id = max_track.max_id
			WHERE ccase.case_id = :case_id
		) ctt
		ON tsk.task_uuid = ctt.task_uuid
		";
	}
	$sql .= "
	WHERE 1
	AND tsk.customer_id = :customer_id";
	
	if ($blnDeletedTasks) {
		$sql .= "
		AND tsk.deleted = 'Y'";
	} else {
		$sql .= "
		AND tsk.deleted = 'N'";
		if ($blnClosedTasks) {
			$sql .= "
			AND tsk.task_type = 'closed'";
		} else {
			$sql .= "
			AND tsk.task_type != 'closed'";
		}
	}
	$sql .= "
	ORDER BY task_dateandtime DESC";
	/*
	if ($blnDeletedTasks) {
		echo $sql . "\r\n";	
		die();
	}
	*/
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$tasks = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		if (!$blnReturn) {
        	echo json_encode($tasks);     
		} else {
			return $tasks;
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTaskSingleDayInboxAllUser($day) {
	getTaskSingleDayInbox($day, "y");
}
function getTaskDayCompleted($day) {
	getTaskDayInbox($day, "n", "y");
}
function getTaskDayInbox($day, $all_users = "n", $completed = "n") {
	session_write_close();
    /*
	$sql = "SELECT DISTINCT tsk.*, tsk.task_id id, tsk.task_uuid uuid, 
		tsk.task_id id, tsk.task_uuid uuid, ctu.read_status, '' `read_date`, 
		ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, ccase.cpointer,
		IF (tsk.`from` = '', ccm.last_update_user, tsk.`from`) `originator`, IFNULL(ccm.last_updated_date, '0000-00-00 00:00:00') `date_assigned`
		FROM `cse_task` tsk
		LEFT OUTER JOIN `cse_task_user` ctu
		ON tsk.task_uuid = ctu.task_uuid
		LEFT OUTER JOIN `cse_case_task` ccm
		ON tsk.task_uuid = ccm.task_uuid
		LEFT OUTER JOIN `cse_case` ccase
		ON ccm.case_uuid = ccase.case_uuid
		LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
		LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$end_day = mktime(0, 0, 0, date("m", strtotime($day)),   date("d", strtotime($day)) + 1,   date("Y", strtotime($day)));
	
	
	$end_day = date("Y-m-d", strtotime($end_day));
	
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
		LEFT OUTER JOIN `cse_case_corporation` ccorp
		ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` employer
		ON ccorp.corporation_uuid = employer.corporation_uuid
		
		LEFT OUTER JOIN `cse_case_corporation` pcorp
		ON (ccase.case_uuid = pcorp.case_uuid AND pcorp.attribute = 'plaintiff' AND pcorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` plaintiff
		ON pcorp.corporation_uuid = plaintiff.corporation_uuid
		
		WHERE 1
		
		AND tsk.task_uuid IN (
			SELECT DISTINCT task.task_uuid
			FROM cse_task task
			INNER JOIN cse_task_user utask
			ON task.task_uuid = utask.task_uuid
			WHERE task.assignee = '" . $_SESSION['user_nickname'] . "'
			AND CAST(task.task_dateandtime AS DATE) BETWEEN :day AND :end_day
			
			AND task.customer_id = " . $_SESSION['user_customer_id'] . "
			AND task.deleted = 'N'
		)
		AND `ctu`.`type` = 'to'
		AND `ctu`.deleted = 'N'
		ORDER BY tsk.task_dateandtime ASC, tsk.task_id ASC";
	//AND ctu.user_uuid = '" . $_SESSION['user_id'] . "'
	//$_SESSION['user_nickname']
	*/
	//$end_day = mktime(0, 0, 0, date("m", strtotime($day)),   date("d", strtotime($day)) + 1,   date("Y", strtotime($day)));
	//$end_day = date("Y-m-d", $end_day);
	$two_days = mktime(0, 0, 0, date("m"),   date("d") + 2,   date("Y"));
	
	$arrDay = firstAvailableDay( date("Y-m-d", $two_days));
	$end_day = $arrDay["linux_date"];
	
	$sql = "SELECT DISTINCT tsk.*, tsk.task_id id, tsk.task_uuid uuid, 
		tsk.task_id id, tsk.task_uuid uuid, ctu.read_status, '' `read_date`, 
		ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, ccase.cpointer,
		IF (tsk.`from` = '', ccm.last_update_user, tsk.`from`) `originator`, IFNULL(ccm.last_updated_date, '0000-00-00 00:00:00') `date_assigned`,
		IFNULL(plaintiff.company_name, '') plaintiff_name
		FROM `cse_task` tsk
		INNER JOIN `cse_task_user` ctu
		ON tsk.task_uuid = ctu.task_uuid AND ctu.deleted = 'N'
		LEFT OUTER JOIN `cse_case_task` ccm
		ON tsk.task_uuid = ccm.task_uuid
		LEFT OUTER JOIN `cse_case` ccase
		ON ccm.case_uuid = ccase.case_uuid
		LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
		LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
		LEFT OUTER JOIN `cse_case_corporation` ccorp
		ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` employer
		ON ccorp.corporation_uuid = employer.corporation_uuid
		
		LEFT OUTER JOIN `cse_case_corporation` pcorp
		ON (ccase.case_uuid = pcorp.case_uuid AND pcorp.attribute = 'plaintiff' AND pcorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` plaintiff
		ON pcorp.corporation_uuid = plaintiff.corporation_uuid
		
		WHERE 1";
		$sql .= " AND tsk.customer_id = " . $_SESSION['user_customer_id'];
		if ($all_users=="n") {
			$sql .= " AND ctu.user_uuid = '" . $_SESSION['user_id'] . "'";
		}
		$sql .= " AND tsk.deleted = 'N'
		AND `ctu`.`type` = 'to'
		AND `ctu`.deleted = 'N'";
	if ($completed=="n") {
		$sql .= " 
		AND tsk.task_type != 'closed'";
	} else {
		$sql .= " 
		AND tsk.task_type = 'closed'";
	}
	$sql .= " 
	AND CAST(tsk.task_dateandtime AS DATE) BETWEEN :day AND :end_day
		ORDER BY tsk.task_dateandtime ASC";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("day", $day);
		$stmt->bindParam("end_day", $end_day);

		$stmt->execute();
		$tasks = $stmt->fetchAll(PDO::FETCH_OBJ);

        echo json_encode($tasks);     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTaskSingleDayInbox($day, $all_users = "n") {
	session_write_close();
    
	$sql = "SELECT DISTINCT tsk.*, tsk.task_id id, tsk.task_uuid uuid, 
		tsk.task_id id, tsk.task_uuid uuid, ctu.read_status, '' `read_date`, 
		ccase.case_id, CONCAT(app.first_name,' ', app.last_name, IF(employer.`company_name` IS NULL, '', CONCAT(' vs ', employer.`company_name`))) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, ccase.cpointer,
		IFNULL(usr.nickname, tsk.task_from) `originator`, IFNULL(ccm.last_updated_date, '0000-00-00 00:00:00') `date_assigned`,
		IFNULL(plaintiff.company_name, '') plaintiff_name
		
		FROM `cse_task` tsk
		
		INNER JOIN `cse_task_user` ctu
		ON tsk.task_uuid = ctu.task_uuid AND ctu.deleted = 'N'
		
		LEFT OUTER JOIN `cse_case_task` ccm
		ON tsk.task_uuid = ccm.task_uuid
		
		LEFT OUTER JOIN ikase.cse_user usr
		ON ccm.last_update_user = usr.user_uuid 
		
		LEFT OUTER JOIN `cse_case` ccase
		ON ccm.case_uuid = ccase.case_uuid
		
		LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
		LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
		LEFT OUTER JOIN `cse_case_corporation` ccorp
		ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` employer
		ON ccorp.corporation_uuid = employer.corporation_uuid
		
		LEFT OUTER JOIN `cse_case_corporation` pcorp
		ON (ccase.case_uuid = pcorp.case_uuid AND pcorp.attribute = 'plaintiff' AND pcorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` plaintiff
		ON pcorp.corporation_uuid = plaintiff.corporation_uuid
		
		WHERE 1
		AND tsk.customer_id = " . $_SESSION['user_customer_id'];
		if ($all_users=="n") {
			$sql .= " AND ctu.user_uuid = '" . $_SESSION['user_id'] . "'";
		}
		$sql .= " 
		AND tsk.task_type != 'closed'";
		$sql .= " 
		AND tsk.deleted = 'N'
		AND `ctu`.`type` = 'to'
		AND CAST(tsk.task_dateandtime AS DATE) = :day
		AND `ctu`.deleted = 'N'";
		if ($all_users=="n") {
			$sql .= " 
			group by task_id ORDER BY tsk.task_dateandtime ASC, tsk.task_id ASC";
		} else {
			$sql .= " 
			ORDER BY tsk.assignee ASC, tsk.task_dateandtime ASC, tsk.task_id ASC";
		}
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("day", $day);
		$stmt->execute();
		$tasks = $stmt->fetchAll(PDO::FETCH_OBJ);

        echo json_encode($tasks);     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTaskWeekInbox($week, $year) {
	session_write_close();
	//figure out the week days
	/*
	$week_number = date("W");
	$year = date("Y");
	if ($week=="next") {
		$week_number++;
	}
	if ($week_number > 52) {
		$week_number = 1;
		$year = date("Y") + 1;
	}
	*/
    $sql = "SELECT DISTINCT tsk.*, tsk.task_id id, tsk.task_uuid uuid, 
		tsk.task_id id, tsk.task_uuid uuid, ctu.read_status, '' `read_date`, 
		ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, ccase.cpointer,
		IF (tsk.`from` = '', ccm.last_update_user, tsk.`from`) `originator`, IFNULL(ccm.last_updated_date, '0000-00-00 00:00:00') `date_assigned`,
		IFNULL(plaintiff.company_name, '') plaintiff_name
		FROM `cse_task` tsk
		LEFT OUTER JOIN `cse_task_user` ctu
		ON tsk.task_uuid = ctu.task_uuid AND ctu.deleted = 'N'
		LEFT OUTER JOIN `cse_case_task` ccm
		ON tsk.task_uuid = ccm.task_uuid
		LEFT OUTER JOIN `cse_case` ccase
		ON ccm.case_uuid = ccase.case_uuid
		LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
		LEFT OUTER JOIN ";
	if (($_SESSION['user_customer_id']==1033)) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
		LEFT OUTER JOIN `cse_case_corporation` ccorp
		ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` employer
		ON ccorp.corporation_uuid = employer.corporation_uuid
		
		LEFT OUTER JOIN `cse_case_corporation` pcorp
		ON (ccase.case_uuid = pcorp.case_uuid AND pcorp.attribute = 'plaintiff' AND pcorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` plaintiff
		ON pcorp.corporation_uuid = plaintiff.corporation_uuid
		
		WHERE 1
		
		AND tsk.task_uuid IN (SELECT DISTINCT task.task_uuid
		FROM cse_task task
		INNER JOIN cse_task_user utask
		ON task.task_uuid = utask.task_uuid
		WHERE assignee = '" . $_SESSION['user_nickname'] . "')
		
		AND WEEKOFYEAR(tsk.task_dateandtime) = :week
		AND YEAR(tsk.task_dateandtime) = :year
		AND tsk.customer_id = " . $_SESSION['user_customer_id'] . "
		AND tsk.deleted = 'N'
		#AND tsk.task_type != 'closed'
		AND `ctu`.`type` = 'to'
		AND `ctu`.deleted = 'N'
		ORDER BY tsk.task_dateandtime DESC";
	//AND ctu.user_uuid = '" . $_SESSION['user_id'] . "'
	
	//die($sql);	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("week", $week);
		$stmt->bindParam("year", $year);
		$stmt->execute();
		$tasks = $stmt->fetchAll(PDO::FETCH_OBJ);

        echo json_encode($tasks);     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function newTaskInbox() {
	//the first argument excludes closed cases
	getTaskInbox("closed", "Y");
	return;
	//new is for TODAY only
	session_write_close();
    $sql = "SELECT DISTINCT tsk.*, tsk.task_id id, tsk.task_uuid uuid, 
		tsk.task_id id, tsk.task_uuid uuid, ctu.read_status, '' `read_date`, cc.case_id,
		IF (tsk.`from` = '', ccm.last_update_user, tsk.`from`) `originator`, IFNULL(ccm.last_updated_date, '0000-00-00 00:00:00') `date_assigned`,
		IFNULL(plaintiff.company_name, '') plaintiff_name
		FROM `cse_task` tsk
		INNER JOIN `cse_task_user` ctu
		ON tsk.task_uuid = ctu.task_uuid AND ctu.deleted = 'N'
		LEFT OUTER JOIN `cse_case_task` ccm
		ON tsk.task_uuid = ccm.task_uuid
		LEFT OUTER JOIN `cse_case` cc
		ON ccm.case_uuid = cc.case_uuid
		WHERE 1
		AND CAST(tsk.task_dateandtime AS DATE) = '" . date("Y-m-d") . "'
		AND tsk.customer_id = " . $_SESSION['user_customer_id'] . "
		AND ctu.user_uuid = '" . $_SESSION['user_id'] . "'
		AND tsk.deleted = 'N'
		#AND tsk.task_type != 'closed'
		AND `ctu`.`type` = 'to'
		AND `ctu`.read_status = 'N'
		AND `ctu`.deleted = 'N'
		ORDER BY tsk.task_id DESC";
	//die($sql);	
	try {
		$tasks = DB::select($sql);

        echo json_encode($tasks);     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCompletedTasksAllUsers($day) {
	getCompletedTasks($day, "y");
}
function getCompletedTasks($day = "", $all_users = "n", $start = "", $end = "") {
	session_write_close();
    $sql = "SELECT DISTINCT tsk.*, tsk.task_id id, tsk.task_uuid uuid, 
		tsk.task_id id, tsk.task_uuid uuid, ctu.read_status, '' `read_date`, cc.case_id,
		IF (tsk.`from` = '', ccm.last_update_user, tsk.`from`) `originator`, IFNULL(ccm.last_updated_date, '0000-00-00 00:00:00') `date_assigned`,
		IFNULL(plaintiff.company_name, '') plaintiff_name,
		CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, cc.case_number, cc.cpointer
		FROM `cse_task` tsk
		INNER JOIN `cse_task_user` ctu
		ON tsk.task_uuid = ctu.task_uuid AND ctu.deleted = 'N'
		LEFT OUTER JOIN `cse_case_task` ccm
		ON tsk.task_uuid = ccm.task_uuid
		LEFT OUTER JOIN `cse_case` cc
		ON ccm.case_uuid = cc.case_uuid
		LEFT OUTER JOIN cse_case_person ccapp ON cc.case_uuid = ccapp.case_uuid
		LEFT OUTER JOIN ";
	if (($_SESSION['user_customer_id']==1033)) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
		LEFT OUTER JOIN `cse_case_corporation` ccorp
		ON (cc.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` employer
		ON ccorp.corporation_uuid = employer.corporation_uuid
		
		LEFT OUTER JOIN `cse_case_corporation` pcorp
		ON (cc.case_uuid = pcorp.case_uuid AND pcorp.attribute = 'plaintiff' AND pcorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` plaintiff
		ON pcorp.corporation_uuid = plaintiff.corporation_uuid
		WHERE 1
		AND tsk.customer_id = " . $_SESSION['user_customer_id'];
		if ($all_users=="n") {
			$sql .= " AND ctu.user_uuid = '" . $_SESSION['user_id'] . "'";
		}
		$sql .= " AND tsk.deleted = 'N'
		AND tsk.task_type = 'closed'
		AND `ctu`.`type` = 'to'
		AND `ctu`.deleted = 'N'";
	if ($day != "") {
		$sql .= " AND CAST(tsk.task_dateandtime AS DATE) = :day";
	}
	if ($start != "") {
		if ($start != $end) {
			$sql .=	" AND CAST(tsk.task_dateandtime AS DATE) BETWEEN '" . $start . "' AND '" . $end . "'";
		} else {
			$sql .=	" AND CAST(tsk.task_dateandtime AS DATE) = '" . $start . "'";
		}
	}
	if ($all_users=="n") {
		$sql .= " ORDER BY tsk.task_dateandtime DESC, tsk.task_id DESC";
	} else {
		$sql .= " ORDER BY tsk.assignee ASC, tsk.task_dateandtime DESC, tsk.task_id DESC";
	}
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($day != "") {
			$stmt->bindParam("day", $day);
		}
		$stmt->execute();
		$tasks = $stmt->fetchAll(PDO::FETCH_OBJ);

        echo json_encode($tasks);     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function checkTaskInbox() {
	session_write_close();
    $sql = "SELECT COUNT( tsk.task_id) task_count
		FROM `cse_task` tsk
		INNER JOIN `cse_task_user` ctu
		ON tsk.task_uuid = ctu.task_uuid AND ctu.deleted = 'N'
		WHERE 1
		AND tsk.customer_id = " . $_SESSION['user_customer_id'] . "
		AND ctu.user_uuid = '" . $_SESSION['user_id'] . "'
		AND tsk.deleted = 'N'
		AND `ctu`.`type` = 'to'
		AND `ctu`.read_status = 'N'
		AND `ctu`.deleted = 'N'
		ORDER BY tsk.task_id DESC";
	//die($sql);	
	try {
		$stmt = DB::run($sql);
		$stmt->execute();
		$task_count = $stmt->fetchObject();
		
		header('Content-Type: text/event-stream');
		header('Cache-Control: no-cache'); // recommended to prevent caching of event data.
		
		echo "id: 0" . PHP_EOL;
		echo "data: " . $task_count->task_count . PHP_EOL;
		echo PHP_EOL;
		ob_flush();
		flush();
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTaskOutboxByDate($start, $end) {
	$_SESSION["task_start_date"] = $start;
	$_SESSION["task_end_date"] = $end;
	getTaskOutbox();
}
function getTaskOutboxCount() {
	getTaskOutbox(true);
}
function getTaskOutbox($blnCount = false) {
	$task_start_date = "";
	$task_end_date = "";
	if (isset($_SESSION["task_start_date"])) {
		$task_start_date = $_SESSION["task_start_date"];
		unset($_SESSION["task_start_date"]);
	}
	if (isset($_SESSION["task_end_date"])) {
		$task_end_date = $_SESSION["task_end_date"];
		unset($_SESSION["task_end_date"]);
	}
	session_write_close();
    $sql = "SELECT DISTINCT tsk.*, tsk.task_id id, tsk.task_uuid uuid, 
		tsk.task_id id, tsk.task_uuid uuid, ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ') `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, ccase.cpointer,
		IF (tsk.`from` = '', ccm.last_update_user, tsk.`from`) `originator`, IFNULL(ccm.last_updated_date, '0000-00-00 00:00:00') `date_assigned`
		
		FROM `cse_task` tsk
		INNER JOIN `cse_task_user` ctu
		ON tsk.task_uuid = ctu.task_uuid AND ctu.deleted = 'N'
		LEFT OUTER JOIN `cse_case_task` ccm
		ON tsk.task_uuid = ccm.task_uuid
		LEFT OUTER JOIN `cse_case` ccase
		ON ccm.case_uuid = ccase.case_uuid
		LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
		LEFT OUTER JOIN ";
	if (($_SESSION['user_customer_id']==1033)) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
		
		WHERE 1
		AND tsk.customer_id = " . $_SESSION['user_customer_id'] . "
		AND tsk.deleted = 'N'";
		/*
		CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`,
		
		
		IFNULL(plaintiff.company_name, '') plaintiff_name
		
		LEFT OUTER JOIN `cse_case_corporation` ccorp
		ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` employer
		ON ccorp.corporation_uuid = employer.corporation_uuid
		
		LEFT OUTER JOIN `cse_case_corporation` pcorp
		ON (ccase.case_uuid = pcorp.case_uuid AND pcorp.attribute = 'plaintiff' AND pcorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` plaintiff
		ON pcorp.corporation_uuid = plaintiff.corporation_uuid
		
		
		*/
		//request from Johana Lopez 3/30/17
	if ($_SESSION["user_customer_id"]==1075) {
		$sql .= " AND tsk.task_type != 'closed'";
	}
	
	if ($task_start_date!="") {
		$sql .= " AND tsk.task_dateandtime >= '" . $task_start_date . "'";
	}
	if ($task_end_date!="") {
		$sql .= " AND tsk.task_dateandtime <= '" . $task_end_date . "'";
	}
	$sql .= " AND `ctu`.`type` = 'from'
		AND `ctu`.deleted = 'N'
		AND `ctu`.user_uuid = '" . $_SESSION["user_id"] . "'
		ORDER BY tsk.task_dateandtime DESC";
	//die($sql);
	if ($_SERVER['REMOTE_ADDR'] == '107.77.231.200') { 
		//die($sql);
	}	
	try {
		$tasks = DB::select($sql);
		if ($_SERVER['REMOTE_ADDR'] == '107.77.231.200') { 
				die(print_r($tasks));
			}
		
		if ($blnCount) {
			echo json_encode(array("success"=>true, "count"=>count($tasks)));
		} else {
        	echo json_encode($tasks); 
			if ($_SERVER['REMOTE_ADDR'] == '107.77.231.200') { 
				die($tasks);
			}	    
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTaskCaseOutbox($case_id) {
	session_write_close();
    $sql = "SELECT DISTINCT tsk.*, tsk.task_id id, tsk.task_uuid uuid, 
		tsk.task_id id, tsk.task_uuid uuid, ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, ccase.cpointer,
		IF (tsk.`from` = '', ccm.last_update_user, tsk.`from`) `originator`, IFNULL(ccm.last_updated_date, '0000-00-00 00:00:00') `date_assigned`,
		IFNULL(plaintiff.company_name, '') plaintiff_name
		FROM `cse_task` tsk
		INNER JOIN `cse_task_user` ctu
		ON tsk.task_uuid = ctu.task_uuid AND ctu.deleted = 'N'
		LEFT OUTER JOIN `cse_case_task` ccm
		ON tsk.task_uuid = ccm.task_uuid
		LEFT OUTER JOIN `cse_case` ccase
		ON ccm.case_uuid = ccase.case_uuid
		LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
		LEFT OUTER JOIN ";
	if (($_SESSION['user_customer_id']==1033)) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
		LEFT OUTER JOIN `cse_case_corporation` ccorp
		ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` employer
		ON ccorp.corporation_uuid = employer.corporation_uuid
		
		LEFT OUTER JOIN `cse_case_corporation` pcorp
		ON (ccase.case_uuid = pcorp.case_uuid AND pcorp.attribute = 'plaintiff' AND pcorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` plaintiff
		ON pcorp.corporation_uuid = plaintiff.corporation_uuid
		WHERE 1
		AND tsk.customer_id = " . $_SESSION['user_customer_id'] . "
		AND ccase.case_id = '" . $case_id . "'
		AND tsk.deleted = 'N'
		
		AND `ctu`.`type` = 'from'
		AND `ctu`.deleted = 'N'
		ORDER BY tsk.task_dateandtime DESC";
	//die($sql);	
	try {
		$tasks = DB::select($sql);

        echo json_encode($tasks);     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTaskDayOutbox($day) {
	session_write_close();
    $sql = "SELECT DISTINCT tsk.*, tsk.task_id id, tsk.task_uuid uuid, 
		tsk.task_id id, tsk.task_uuid uuid, ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, ccase.cpointer,
		IF (tsk.`from` = '', ccm.last_update_user, tsk.`from`) `originator`, IFNULL(ccm.last_updated_date, '0000-00-00 00:00:00') `date_assigned`,
		IFNULL(plaintiff.company_name, '') plaintiff_name
		FROM `cse_task` tsk
		INNER JOIN `cse_task_user` ctu
		ON tsk.task_uuid = ctu.task_uuid AND ctu.deleted = 'N'
		LEFT OUTER JOIN `cse_case_task` ccm
		ON tsk.task_uuid = ccm.task_uuid
		LEFT OUTER JOIN `cse_case` ccase
		ON ccm.case_uuid = ccase.case_uuid
		LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
		LEFT OUTER JOIN ";
	if (($_SESSION['user_customer_id']==1033)) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
		LEFT OUTER JOIN `cse_case_corporation` ccorp
		ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` employer
		ON ccorp.corporation_uuid = employer.corporation_uuid
		
		LEFT OUTER JOIN `cse_case_corporation` pcorp
		ON (ccase.case_uuid = pcorp.case_uuid AND pcorp.attribute = 'plaintiff' AND pcorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` plaintiff
		ON pcorp.corporation_uuid = plaintiff.corporation_uuid
		WHERE 1
		AND CAST(tsk.task_dateandtime AS DATE) = :day
		AND tsk.customer_id = " . $_SESSION['user_customer_id'] . "
		AND ctu.user_uuid = '" . $_SESSION['user_id'] . "'
		AND tsk.deleted = 'N'
		AND `ctu`.`type` = 'from'
		AND `ctu`.deleted = 'N'
		ORDER BY tsk.task_id DESC";
	//die($sql);	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("day", $day);
		$stmt->execute();
		$tasks = $stmt->fetchAll(PDO::FETCH_OBJ);

        echo json_encode($tasks);     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getOverDueKaseTasks($case_id) {
	getOverDueTasks(false, $case_id, false);
}
function getOverDueKaseTasksCount($case_id) {
	getOverDueTasks(true, $case_id, false);
}
function getOverDueTasksCount() {
	getOverDueTasks(true, "", false);
}
function getOverDueFirmTasksCount() {
	getOverDueTasks(true, "", true);
}
function getOverDueFirmTasks() {
	getOverDueTasks(false, "", true);
}
function getOverDueUserTasks($nickname) {
	getOverDueTasks(false, "", false, $nickname);
}
function getOverDueTasks($blnCount = false, $case_id = "", $blnFirm = false, $assignee = "") {
	session_write_close();
	
	try {
		/*
		,' - ', 
            REPLACE(IF (DATE_FORMAT(inj.start_date, '%m/%d/%Y') IS NULL, '', DATE_FORMAT(inj.start_date, '%m/%d/%Y')), '00/00/0000', '')
		*/
		if ($blnCount) {
			$sql = "SELECT DISTINCT task.*, task.task_id id ";
		} else {
			$sql = "SELECT DISTINCT ccase.case_id, ccase.case_number, ccase.file_number, ccase.case_status,
			ccase.case_name case_stored_name, ccase.cpointer, CONCAT(app.first_name,' ',app.last_name,' vs ', IFNULL(employer.`company_name`, '')) `case_name`,  
			#IF (task.`from` = '', ccm.last_update_user, task.`from`) `originator`,
			IFNULL(usr.nickname, task.task_from) `originator`,
			task.*, task.task_id id ";
		}
		$sql .= "
		FROM cse_task task
		INNER JOIN cse_case_task cct
		ON task.task_uuid = cct.task_uuid
		
		LEFT OUTER JOIN ikase.cse_user usr
		ON cct.last_update_user = usr.user_uuid 
	
		INNER JOIN cse_case ccase
		ON cct.case_uuid = ccase.case_uuid
		LEFT OUTER JOIN `cse_case_task` ccm
		ON task.task_uuid = ccm.task_uuid
		
		LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N'
		LEFT OUTER JOIN ";
			
		if (($_SESSION['user_customer_id']==1033)) { 
			$sql .= "(" . SQL_PERSONX . ")";
		} else {
			$sql .= "cse_person";
		}
		$sql .= " app ON ccapp.person_uuid = app.person_uuid
		LEFT OUTER JOIN `cse_case_corporation` ccorp
		ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` employer
		ON ccorp.corporation_uuid = employer.corporation_uuid
		
		LEFT OUTER JOIN `cse_case_corporation` pcorp
		ON (ccase.case_uuid = pcorp.case_uuid AND pcorp.attribute = 'plaintiff' AND pcorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` plaintiff
		ON pcorp.corporation_uuid = plaintiff.corporation_uuid
		INNER JOIN `cse_case_injury` cinj
		ON ccase.case_uuid = cinj.case_uuid AND cinj.deleted = 'N' AND cinj.`attribute` = 'main'
		INNER JOIN `cse_injury` inj
		ON cinj.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
		WHERE task.customer_id = :customer_id
		
		AND task.deleted = 'N'
		AND (CAST(task_dateandtime AS DATE) < '" . date("Y-m-d") . "' AND CAST(task_dateandtime AS DATE) > '2017-04-13' OR task_dateandtime = '0000-00-00 00:00:00')";
		
		if ($_SESSION["user_customer_id"]=="1075") {
			$sql .= "
			AND ccase.case_status NOT LIKE 'CL-%'";
		}
		$sql .= "
		AND task.task_type != 'closed'";
		/*		
		if ($_SERVER['REMOTE_ADDR']=='47.153.49.248') {
			$sql .= "
			AND task.task_type != 'closed'";
		} else {
			if ($_SESSION["user_customer_id"]=="1134") {
				$sql .= "
				AND task.task_type != 'closed'";
			} else {
				$sql .= "
				AND (task.task_type = 'open' OR task.task_type = 'Phone_call')";
			}
		}
		AND (ccase.case_status NOT LIKE '%close%'  AND ccase.case_status != 'DROPPED'
		*/
		if ($case_id!="") {
			$sql .= "
			AND ccase.case_id = :case_id";
		} else {
			if (!$blnFirm) {
				if ($assignee=="") {
					$assignee = $_SESSION["user_nickname"];

				}
				$sql .= " AND task.assignee = '" . $assignee . "'";
			}
		}
		$sql .= "
		ORDER BY IF(task_priority='', 'ZLOW', task_priority) ASC, task_dateandtime ASC";
		
		if ($_SERVER['REMOTE_ADDR']=='47.153.53.238') {
			//die($sql);
		}
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$customer_id = $_SESSION["user_customer_id"];
		$stmt->bindParam("customer_id", $customer_id);
		if ($case_id!="") {
			$stmt->bindParam("case_id", $case_id);
		}
		$stmt->execute();
		$tasks = $stmt->fetchAll(PDO::FETCH_OBJ);

		if ($blnCount) {
			echo json_encode(array("success"=>true, "count"=>count($tasks)));
		} else {
        	echo json_encode($tasks);     
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTasksSummary() {
	session_write_close();
	
	 $sql = "SELECT user_id, nickname, user_name, COUNT(DISTINCT task_id) task_count, SUM(overdue) overdues,
	MIN(task_dateandtime) oldest_task, MAX(task_dateandtime) newest_task
	FROM (
		SELECT  usr.user_id, usr.nickname, usr.user_name, tsk.task_id, tsk.task_dateandtime,
    	IF (tsk.task_dateandtime > '" . date("Y-m-d") . " 00:00:00', 0, 1) overdue
		FROM `cse_task` tsk
		INNER JOIN `cse_task_user` ctu
		ON tsk.task_uuid = ctu.task_uuid AND ctu.deleted = 'N'
		INNER JOIN  `ikase`.`cse_user` usr
        ON ctu.user_uuid = usr.user_uuid
		LEFT OUTER JOIN `cse_case_task` ccm
		ON tsk.task_uuid = ccm.task_uuid
		LEFT OUTER JOIN `cse_case` ccase
		ON ccm.case_uuid = ccase.case_uuid
		LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
		LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
		LEFT OUTER JOIN `cse_case_corporation` ccorp
		ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` employer
		ON ccorp.corporation_uuid = employer.corporation_uuid
		
		LEFT OUTER JOIN `cse_case_corporation` pcorp
		ON (ccase.case_uuid = pcorp.case_uuid AND pcorp.attribute = 'plaintiff' AND pcorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` plaintiff
		ON pcorp.corporation_uuid = plaintiff.corporation_uuid
		
		WHERE 1
		AND tsk.customer_id = :customer_id
		AND usr.customer_id = :customer_id
		AND tsk.task_type != 'closed'
		AND tsk.completed_date = '0000-00-00 00:00:00'
        AND tsk.task_dateandtime > '2015-01-01 00:00:00'
		AND tsk.deleted = 'N'
		AND (`ctu`.`type` = 'to' OR `ctu`.`type` = 'cc')
		AND `ctu`.deleted = 'N'
		AND usr.level != 'masteradmin'
	) tasks
	GROUP BY user_id";
	// line number:1399 added by solulab dev for import a1 employee tasks issue : AND usr.customer_id = :customer_id
	// echo $_SESSION["user_customer_id"]."<br>";
	// die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
		$stmt->execute();
		$summary = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		echo json_encode($summary);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getRecentTasks() {
	session_write_close();
	$error = array("error"=> array("text"=>"no recent tasks"));
    echo json_encode($error);
			
	/*
    $sql = "SELECT * FROM `cse_task` 
			WHERE customer_id =  " . $_SESSION['user_customer_id'] . "
			AND deleted = 'N'
			ORDER BY task_id DESC";
	try {
		$stmt = $db->query($sql);
		$recent_tasks = $stmt->fetchAll(PDO::FETCH_OBJ);
		echo json_encode($recent_tasks);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	*/
}
function getTaskHistory($task_id) {
	session_write_close();
    $sql = "SELECT trk.*
		FROM `cse_task_track` trk
		INNER JOIN `cse_task` tsk
		ON trk.task_uuid = tsk.task_uuid
		WHERE `tsk`.`task_id` = :task_id
		AND `trk`.`customer_id` = " . $_SESSION['user_customer_id'] . "
		ORDER BY task_track_id ASC";
	//die($sql);	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("task_id", $task_id);
		$stmt->execute();
		$tasks = $stmt->fetchAll(PDO::FETCH_OBJ);

        echo json_encode($tasks);     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTask($task_id) {
	session_write_close();
    $sql = "SELECT tsk.*, tsk.task_id id, tsk.task_uuid uuid, 
		ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, ccase.case_number, ccase.file_number, ccase.case_name case_stored_name, ccase.cpointer,
		IF (tsk.`from` = '', ccm.last_update_user, tsk.`from`) `originator`, 
		IFNULL(inj.injury_id, '') doi_id, IFNULL(inj.start_date, '') doi_start, IFNULL(inj.end_date, '') doi_end
		
		FROM `cse_task` tsk
				
		LEFT OUTER JOIN `cse_injury_task` `citask`
		ON `tsk`.`task_uuid` = `citask`.`task_uuid` AND citask.deleted = 'N'
		LEFT OUTER JOIN `cse_injury` inj
		ON citask.injury_uuid = inj.injury_uuid
	
		LEFT OUTER JOIN cse_case_task ccm
		ON tsk.task_uuid = ccm.task_uuid
		LEFT OUTER JOIN cse_case ccase
		ON ccm.case_uuid = ccase.case_uuid
		LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
		LEFT OUTER JOIN ";
	if (($_SESSION['user_customer_id']==1033)) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
		LEFT OUTER JOIN `cse_case_corporation` ccorp
		ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` employer
		ON ccorp.corporation_uuid = employer.corporation_uuid
		
		LEFT OUTER JOIN `cse_case_corporation` pcorp
		ON (ccase.case_uuid = pcorp.case_uuid AND pcorp.attribute = 'plaintiff' AND pcorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` plaintiff
		ON pcorp.corporation_uuid = plaintiff.corporation_uuid
		WHERE tsk.task_id = :task_id
		AND tsk.customer_id = " . $_SESSION['user_customer_id'] . "
		AND tsk.deleted = 'N'
		ORDER BY tsk.task_id DESC";
	//die($sql);	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("task_id", $task_id);
		$stmt->execute();
		$task = $stmt->fetchObject();

        echo json_encode($task);     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTaskInfo($task_id) {
	session_write_close();
    $sql = "SELECT tsk.*, tsk.task_id id, tsk.task_uuid uuid, cse.case_id
		FROM `cse_task` tsk
		LEFT OUTER JOIN cse_case_task ccm
		ON tsk.task_uuid = ccm.task_uuid
		LEFT OUTER JOIN cse_case cse
		ON ccm.case_uuid = cse.case_uuid
		WHERE tsk.task_id = :task_id
		AND tsk.customer_id = " . $_SESSION['user_customer_id'] . "
		ORDER BY tsk.task_id DESC";
	//die($sql);	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("task_id", $task_id);
		$stmt->execute();
		$task = $stmt->fetchObject();

        return $task;     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addTask() {
	session_write_close();
	
	$db = getConnection();
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$table_id = "";
	$case_id = "";
	$notes_id = "";
	$notes_uuid = "";
	$user_uuid = "";
	$send_document_id = "";
	$attachments = "";
	$blnAttachments = true;
	$arrTo = array();
	$arrToID = array();
	$arrCc = array();
	$arrCcID = array();
	$task_name = "";
	
	$injury_id = "";
	$injury_uuid = "";
	
	foreach($_POST as $fieldname=>$value) {
		if ($fieldname!="task_descriptionInput") {
			$value = passed_var($fieldname, "post");
		} else {
			$value = @processHTML($_POST["task_descriptionInput"]);
		}
		if ($value=="undefined") {
			$value = "";
		}
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			if ($table_name=="tasks") {
				$table_name = "task";
			}
			continue;
		}
		if ($fieldname=="case_id") {
			$case_id = $value;
			continue;
		}
		if ($fieldname=="notes_id") {
			$notes_id = $value;
			
			$note = getNoteInfo($notes_id);
			/*
			if ($_SERVER['REMOTE_ADDR']=='47.153.59.9') {
				die(print_r($note));
			}
			*/
			
			$notes_uuid = $note->uuid;
			continue;
		}
		
		if ($fieldname=="injury_id") {
			$injury_id = $value;
			//get the uuid
			$injury = getInjuryInfo($injury_id);
			$injury_uuid = $injury->uuid;
			continue;
		}
		 //commented out by angel
		if ($fieldname=="number_of_days" || $fieldname=="calctask") {
			continue;
		}
		if ($fieldname=="billing_time" || $fieldname=="task_id"  || strpos($fieldname, "kinvoice")!==false) {
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		if ($fieldname=="case_file") {
			$case_id = $value;
			continue;
		}
		if (strpos($fieldname, "reminder_")!==false || strpos($fieldname, "recurrent_")!==false) {
			continue;
		}
		if ($fieldname=="user_id") {
			if ($value!="") {
				//look up the user_uuid
				$user = getUserInfo($value);
				if (is_object($user)) {
					$user_uuid = $user->uuid;
				}
			}
			continue;
		}
		//FOR NOW
		$arrExclude = array("case_uuid", "table_id", "table_uuid", "thread_uuid", "injury_uuid", "task_kind", "source_message_id", "reaction", "message_cc", "message_bcc", "priority", "calendar_id", "injury_id", "number_of_days", "task_duration", "ignore_me", "partie_id", "table_attribute", "type", "status", "notes_id");
		
		//if ($fieldname=="case_uuid" || $fieldname=="table_id" || $fieldname=="table_uuid" || $fieldname=="thread_uuid" || $fieldname=="injury_uuid" || $fieldname=="task_kind" || $fieldname=="source_message_id" || $fieldname=="reaction") {
		if (in_array($fieldname, $arrExclude)) {
			continue;
		}
		
		if ($fieldname=="attach_document_id") {
			$fieldname = "send_document_id";
		}
		if ($fieldname=="from") {
			$fieldname = "task_from";
		}
		if ($fieldname=="attach_document_id") {
			$fieldname = "send_document_id";
		}
		if ($fieldname=="start_date" || $fieldname=="end_date" || $fieldname=="callback_date") {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			} else {
				$value = "0000-00-00 00:00:00";
			}
		}
		if ($fieldname=="task_dateandtime") {
			if ($value!="") {
				if (date("H:i:s", strtotime($value))!="00:00:00") {
					$value = date("Y-m-d H:i:s", strtotime($value));
				} else {
					$value = date("Y-m-d", strtotime($value)) . " 08:00:00";
				}
			} else {
				$value = date("Y-m-d H:i:s");
			}
		}
		if ($fieldname=="assignee") {
			explodeRecipient($value, $arrTo, $arrToID, $db);
			$to = implode(";", $arrTo);
			$value = $to;
		}
		if ($fieldname=="cc") {
			explodeRecipient($value, $arrCc, $arrCcID, $db);
			$cc = implode(";", $arrCc);
			$value = $cc;
		}
		/*
		if ($fieldname=="task_type") {
			$color = "blue";
			switch($value) {
				case "appointment":
					$color = "orange";
					break;
				case "hearing":
					$color = "red";
					break;
				case "phone_call":
					$color = "green";
					break;
			}
			$arrSet[] = "`color` = '" . $color . "'";
		}
		*/
		if ($fieldname=="send_document_id") {
			$send_document_id = $value;
			if ($send_document_id!="") {
				$send_document = getDocumentInfo($send_document_id);
				$attachments = $send_document->document_filename;
				
				$arrFields[] = "`attachments`";
				$arrSet[] = "'" . $attachments . "'";
				$blnAttachments = false;
			}
			continue;
		}
		if ($fieldname=="task_name") {
			$task_name = $value;
			continue;
		}
		if ($fieldname=="attachments") {
			if (!$blnAttachments) {
				continue;
			}
			$attachments = $value;
			//continue;
		}
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	
	if (isset($_POST["calctask"])) {
		$calctask = passed_var("calctask", "post");
		$next_day = mktime(0, 0, 0, date("m")  , date("d") + $calctask, date("Y"));
		$arrResult = firstAvailableDay(date("Y-m-d", $next_day));
		//die(print_r($arrResult));
		$task_dateandtime = $arrResult["calculated_date"];
		$arrFields[] = "`task_dateandtime`";
		$arrSet[] = "'" . date("Y-m-d", strtotime($task_dateandtime)) . " 09:00:00'";
	}
	
	if ($case_id!="") {
		$kase = getKaseInfo($case_id);
		
		$arrFields[] = "`task_first_name`";
		$arrSet[] = "'" . addslashes($kase->first_name) . "'";
		
		$arrFields[] = "`task_last_name`";
		$arrSet[] = "'" . addslashes($kase->last_name) . "'";
	}
	$arrFields[] = "`task_name`";
	$arrSet[] = "'" . addslashes($task_name) . "'";
	
	$arrFields[] = "`from`";
	$arrSet[] = "'" . addslashes($_SESSION['user_name']) . "'";
	
	$arrFields[] = "`customer_id`";
	$arrSet[] = $_SESSION['user_customer_id'];

	$table_uuid = uniqid("KS", false);
	$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', " . implode(",", $arrSet) . ")";
	//echo $sql;die;
	$last_updated_date = date("Y-m-d H:i:s");
	
	/*
	if ($_SESSION['user_customer_id']==1042) {
		
		
		$MySqlHostname = "ikase.org";
		$MySqlUsername = "root";
		$MySqlPassword = "admin527#";
		$db = "ikase_patel";
	}
	if ($_SESSION['user_customer_id']==1121) {
		
		
		$MySqlHostname = "ikase.org";
		$MySqlUsername = "root";
		$MySqlPassword = "admin527#";
		$db = "ikase_goldberg2";
	}
	if ($_SESSION['user_customer_id']==1073) {
		
		
		$MySqlHostname = "ikase.org";
		$MySqlUsername = "root";
		$MySqlPassword = "admin527#";
		$db = "ikase_goldflam";
	}
	if ($_SESSION['user_customer_id']==1109) {
		
		
		$MySqlHostname = "ikase.org";
		$MySqlUsername = "root";
		$MySqlPassword = "admin527#";
		$db = "ikase_barsoum";
	}
	if ($_SESSION['user_customer_id']==1069) {
		
		
		$MySqlHostname = "ikase.org";
		$MySqlUsername = "root";
		$MySqlPassword = "admin527#";
		$db = "ikase_dantin";
	}
	if ($_SESSION['user_customer_id']==1033) {
		
		
		$MySqlHostname = "ikase.org";
		$MySqlUsername = "root";
		$MySqlPassword = "admin527#";
		$db = "ikase";
	}
	if ($_SESSION['user_customer_id']==1137) {
		
		
		$MySqlHostname = "ikase.org";
		$MySqlUsername = "root";
		$MySqlPassword = "admin527#";
		$db = "ikase_harmon";
	}
	if ($_SESSION['user_customer_id']==1111) {
		
		
		$MySqlHostname = "ikase.org";
		$MySqlUsername = "root";
		$MySqlPassword = "admin527#";
		$db = "ikase_fair";
	}
	if ($_SESSION['user_customer_id']==1075) {
		
		
		$MySqlHostname = "ikase.org";
		$MySqlUsername = "root";
		$MySqlPassword = "admin527#";
		$db = "ikase_dordulian3";
	}
	if ($_SESSION['user_customer_id']==1062) {
		
		
		$MySqlHostname = "ikase.org";
		$MySqlUsername = "root";
		$MySqlPassword = "admin527#";
		$db = "ikase_hernandez3";
	}
	if ($_SESSION['user_customer_id']==1055) {
		
		
		$MySqlHostname = "ikase.org";
		$MySqlUsername = "root";
		$MySqlPassword = "admin527#";
		$db = "ikase_reino";
	}
	if ($_SESSION['user_customer_id']==1070) {
		
		
		$MySqlHostname = "ikase.org";
		$MySqlUsername = "root";
		$MySqlPassword = "admin527#";
		$db = "ikase_leyva";
	}
	if ($_SESSION['user_customer_id']==1131) {
		
		
		$MySqlHostname = "ikase.org";
		$MySqlUsername = "root";
		$MySqlPassword = "admin527#";
		$db = "ikase_leeds";
	}
	if ($_SESSION['user_customer_id']==1054) {
		
		
		$MySqlHostname = "ikase.org";
		$MySqlUsername = "root";
		$MySqlPassword = "admin527#";
		$db = "ikase_sunil";
	}
	if ($_SESSION['user_customer_id']==1095) {
		
		
		$MySqlHostname = "ikase.org";
		$MySqlUsername = "root";
		$MySqlPassword = "admin527#";
		$db = "ikase_jasminder";
	}
	if ($_SESSION['user_customer_id']==1058) {
		
		
		$MySqlHostname = "ikase.org";
		$MySqlUsername = "root";
		$MySqlPassword = "admin527#";
		$db = "ikase_pimentel";
	}
	if ($_SESSION['user_customer_id']==1064) {
		
		
		$MySqlHostname = "ikase.org";
		$MySqlUsername = "root";
		$MySqlPassword = "admin527#";
		$db = "ikase_pag";
	}
	if ($_SESSION['user_customer_id']==1088) {
		
		
		$MySqlHostname = "ikase.org";
		$MySqlUsername = "root";
		$MySqlPassword = "admin527#";
		$db = "ikase_mahoney";
	}
	*/
	
	/* Solulab code start from here viren */
		$MySqlHostname = "ikase.website";
		$MySqlUsername = "root";
		$MySqlPassword = "admin527#";
		if($_SESSION['user_data_source'] && $_SESSION['user_data_source'] != ''){
		$db = "ikase_".$_SESSION['user_data_source'];
		}else{
			$db = "ikase";
		}
	/* solulab code end viren*/

        //FIXME: currently this code is the only one using a connection different than the rest of the API (through getConnection). Either get the DB::run() calls back to mysqli(), or remove the following line and all the db connections above
//		$conn = new mysqli($MySqlHostname, $MySqlUsername, $MySqlPassword, $db);
		if (DB::run($sql)) {
			$new_id = DB::lastInsertId();
			if ($case_id!="") {
				$case_table_uuid = uniqid("KA", false);
				$attribute_1 = "main";
				//now we have to attach the task to the case 
				$sql = "INSERT INTO cse_case_" . $table_name . " (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_table_uuid  ."', '" . $kase->uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				DB::run($sql);
			}
			
			
			if ($injury_uuid!="") {
				$last_updated_date = date("Y-m-d H:i:s");
				$injury_table_uuid = uniqid("KA", false);
				//attribute
				$table_attribute = "main";
				
				//now we have to attach the note to the case 
				$sql = "INSERT INTO cse_injury_" . $table_name . " (`injury_" . $table_name . "_uuid`, `injury_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $injury_table_uuid  ."', '" . $injury_uuid . "', '" . $table_uuid . "', '" . $table_attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				DB::run($sql);
			}
			
			//assigner
			//attach the from
			$task_user_uuid = uniqid("TD", false);
			$sql = "INSERT INTO cse_task_user (`task_user_uuid`, `task_uuid`, `user_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $task_user_uuid  ."', '" . $table_uuid . "', '" . $_SESSION['user_id'] . "', 'from', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			if ($_SESSION['user_customer_id']==1121 && $_SERVER['REMOTE_ADDR'] == '47.153.49.248') { 
				//die($sql);
			}
			DB::run($sql);

			//assignee
			if (count($arrToID) > 0) {
				attachRecipients('task', $table_uuid, $last_updated_date, $arrToID, 'to', $db = getConnection());
			}
			if (count($arrCcID) > 0) {
				attachRecipients('task', $table_uuid, $last_updated_date, $arrCcID, 'cc', $db = getConnection());
			}
			
			//attach attachments
			/*if ($attachments!="") {
				$arrAttachments = explode("|", $attachments);
				foreach ($arrAttachments as $attachment) {
					$document_name = $attachment;
					$document_name = explode("/", $document_name);
					$document_name = $document_name[count($document_name) - 1];
					$document_date = date("Y-m-d H:i:s");
					$document_extension = explode(".", $document_name);
					$document_extension = $document_extension[count($document_extension) - 1];
					$customer_id = $_SESSION["user_customer_id"];
					$description = "task attachment";
					$description_html = "task attachment";
					$type = "task_attachment";
					$verified = "Y";
					
					//attachment is a document
					$document_uuid = uniqid("KS");
					$sql = "INSERT INTO cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, description, description_html, type, verified, customer_id) 
				VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :description, :description_html, :type, :verified, :customer_id)";
					
					$stmt = $db->prepare($sql);  
					$stmt->bindParam("document_uuid", $document_uuid);
					$stmt->bindParam("parent_document_uuid", $document_uuid);
					$stmt->bindParam("document_name", $document_name);
					$stmt->bindParam("document_date", $document_date);
					$stmt->bindParam("document_filename", $document_name);
					$stmt->bindParam("document_extension", $document_extension);
					$stmt->bindParam("description", $description);
					$stmt->bindParam("description_html", $description_html);
					$stmt->bindParam("type", $type);
					$stmt->bindParam("verified", $verified);
					$stmt->bindParam("customer_id", $customer_id);
					$stmt->execute();
					$new_document_id = $db->lastInsertId();
	
					//die(print_r($newEmployee));
					trackDocument("insert", $new_document_id);
					
					$message_document_uuid = uniqid("TD", false);
					$last_updated_date = date("Y-m-d H:i:s");
					$sql = "INSERT INTO cse_task_document (`task_document_uuid`, `task_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $message_document_uuid  ."', '" . $table_uuid . "', '" . $document_uuid . "', 'attach', '', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
					
					$stmt = DB::run($sql);
				}
			}
			*/
			
			if ($notes_uuid!="") {
				$notes_task_uuid = uniqid("TD", false);
				$last_updated_date = date("Y-m-d H:i:s");
				$sql = "INSERT INTO cse_notes_task (`notes_task_uuid`, `notes_uuid`, `task_uuid`, `attribute`,`last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $notes_task_uuid . "', '" . $notes_uuid  ."', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				DB::run($sql);
			}
			
			// Changes from SOLULAB START
			trackTask("insert", $new_id); //changes made by angel $activity_id = 
			
			echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid)); //changes made by angel , "activity_id"=>$activity_id
			// Changes from SOLULAB END
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
}

function closeTask() {
	$id = passed_var("id", "post");
	$status = passed_var("status", "post");
	$sql = "UPDATE cse_task tsk
			SET tsk.`task_type` = '" . $status . "'
			WHERE `task_id`=:id";
	
	try {
		
		$task = getTaskInfo($id);
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		
		//track now
		trackTask("update", $id);
		
		echo json_encode(array("success"=>"tasks marked as closed"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function updateTask() {
	session_write_close();
	
	$db = getConnection();
	$arrSet = array();
	$where_clause = "";
	$table_name = "";
	$table_id = "";
	$task_dateandtime = "";
	$arrAttachments = array();
	$arrTo = array();
	$arrToID = array();
	$arrCc = array();
	$arrCcID = array();
	foreach($_POST as $fieldname=>$value) {
		if ($fieldname!="task_descriptionInput") {
			$value = passed_var($fieldname, "post");
		} else {
			$value = @processHTML($_POST["task_descriptionInput"]);
		}
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		//skip fields in update
		if ($fieldname=="case_id" || $fieldname=="case_uuid" || $fieldname=="table_uuid" || $fieldname=="task_kind") {
			continue;
		}
		if ($fieldname=="number_of_days") {
			continue;
		}
		if ($fieldname=="send_document_id") {
			$send_document_id = $value;
			if ($send_document_id!="") {
				$arrDocs = explode("|", $send_document_id);
				foreach($arrDocs as $send_document_id) {
					$send_document = getDocumentInfo($send_document_id);
					$arrAttachments[] = $send_document->document_filename;
				}
			}
			continue;
		}
		if ($fieldname=="attachments") {
			if ($value!="") {
				$attachments = $value;
				$arrAttachments[] = $value;
			}
			continue;
		}
		if ($fieldname=="billing_time") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		if ($fieldname=="assignee") {
			explodeRecipient($value, $arrTo, $arrToID, $db);
			$to = implode(";", $arrTo);
			$value = $to;
		}
		if ($fieldname=="cc") {
			explodeRecipient($value, $arrCc, $arrCcID, $db);
			$cc = implode(";", $arrCc);
			$value = $cc;
		}
		if ($fieldname=="task_type") {
			$color = "blue";
			switch($value) {
				case "appointment":
					$color = "orange";
					break;
				case "hearing":
					$color = "red";
					break;
				case "phone_call":
					$color = "green";
					break;
			}
			$arrSet[] = "`color` = '" . $color . "'";
		}
		if ($fieldname=="start_date" || $fieldname=="end_date") {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			} else {
				$value = "0000-00-00";
			}
		}
		
		if ($fieldname=="task_dateandtime" || $fieldname=="callback_date") {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00 00:00:00";
			}
			if ($fieldname=="task_dateandtime") {
				$task_dateandtime = $value;
			}
		}
		
		if ($fieldname=="injury_id") {
			continue;
		}
		if ($fieldname=="case_file") {
			$case_id = $value;
			continue;
		}
		if ($fieldname=="table_id" || $fieldname=="id") {
			$table_id = $value;
			$where_clause = " = " . $value;
		} else {
			$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
		}
	}

	$my_task = getTaskInfo($table_id);
	$table_uuid = $my_task->uuid;
	
	if (count($arrAttachments) > 0) {
		$arrSet[] = "`attachments` = '" . implode("|", $arrAttachments) . "'";
	}
	$where_clause = "`" . $table_name . "_id`" . $where_clause;
	$sql = "
	UPDATE `cse_" . $table_name . "`
	SET " . implode(", ", $arrSet) . "
	WHERE " . $where_clause;
	$sql .= " AND `cse_" . $table_name . "`.customer_id = " . $_SESSION['user_customer_id'];
	
	if ($_SERVER['REMOTE_ADDR']=='47.153.59.9') {
		//die($sql);
	}
	
	try {		
		$stmt = DB::run($sql);
		
		$last_updated_date = date("Y-m-d H:i:s");
		
		if (count($arrToID) > 0) {
			attachRecipients('task', $table_uuid, $last_updated_date, $arrToID, 'to', $db);
		}
		if (count($arrCcID) > 0) {
			attachRecipients('task', $table_uuid, $last_updated_date, $arrCcID, 'cc', $db);
		}
		
		foreach ($arrAttachments as $attachment) {
			$document_name = $attachment;
			//first check if this document is _already_ attached
			$sql = "SELECT COUNT(doc.document_id) thecount
			FROM `cse_document` doc
			INNER JOIN `cse_task_document` cnd
			ON doc.document_uuid = cnd.document_uuid
			WHERE doc.document_name = '" . $document_name . "'";
			$stmt = DB::run($sql);
			$document_count = $stmt->fetchObject();
			
			if ($document_count->thecount==0) {
				$document_name = explode("/", $document_name);
				$document_name = $document_name[count($document_name) - 1];
				$document_date = date("Y-m-d H:i:s");
				$document_extension = explode(".", $document_name);
				$document_extension = $document_extension[count($document_extension) - 1];
				$customer_id = $_SESSION["user_customer_id"];
				$description = "task attachment";
				$description_html = "task attachment";
				$type = "task_attachment";
				$verified = "Y";
				
				//attachment is a document
				$document_uuid = uniqid("KS");
				$sql = "INSERT INTO cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, description, description_html, type, verified, customer_id) 
			VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :description, :description_html, :type, :verified, :customer_id)";
				
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("document_uuid", $document_uuid);
				$stmt->bindParam("parent_document_uuid", $document_uuid);
				$stmt->bindParam("document_name", $document_name);
				$stmt->bindParam("document_date", $document_date);
				$stmt->bindParam("document_filename", $document_name);
				$stmt->bindParam("document_extension", $document_extension);
				$stmt->bindParam("description", $description);
				$stmt->bindParam("description_html", $description_html);
				$stmt->bindParam("type", $type);
				$stmt->bindParam("verified", $verified);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
				$new_id = $db->lastInsertId();

				//die(print_r($newEmployee));
				trackDocument("insert", $new_id);
				
				$message_document_uuid = uniqid("TD", false);
				$last_updated_date = date("Y-m-d H:i:s");
				$sql = "INSERT INTO cse_task_document (`task_document_uuid`, `task_uuid`, `document_uuid`, `attribute_1`,  `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $message_document_uuid  ."', '" . $table_uuid . "', '" . $document_uuid . "', 'attach', '', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				
				$stmt = DB::run($sql);
			}
		}
		//track now
		trackTask("update", $table_id);
		
		//might be a note task
		$sql = "SELECT notes_uuid
		FROM cse_notes_task cnt
		WHERE task_uuid = :task_uuid";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("task_uuid", $table_uuid);
		$stmt->execute();
		$note = $stmt->fetchObject();
		
		if (is_object($note)) {
			//update the callback date in the note
			$sql = "UPDATE cse_notes
			SET callback_date = :callback_date
			WHERE notes_uuid = :notes_uuid";
			$notes_uuid = $note->notes_uuid;
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("callback_date", $task_dateandtime);
			$stmt->bindParam("notes_uuid", $notes_uuid);
			$stmt->execute();
		}
		echo json_encode(array("success"=>$table_id)); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function readTask() {
	session_write_close();
	$id = passed_var("id", "post");
	$sql = "UPDATE cse_task mes, cse_task_user ctu
			SET ctu.`read_status` = 'Y',
			ctu.read_date = '" . date("Y-m-d H:i:s") . "'
			WHERE mes.`task_uuid`= ctu.task_uuid
			AND ctu.type = 'to'
			AND mes.task_id = :id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		
		//track now
		trackTask("read", $id);
		
		echo json_encode(array("success"=>"task marked as read"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function restoreTask() {
	session_write_close();
	$id = passed_var("id", "post");
	$sql = "UPDATE cse_task tsk
			SET tsk.`deleted` = 'N'
			WHERE `task_id`=:id";
	
	try {
		
		$task = getTaskInfo($id);
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		
		//track now
		trackTask("restore", $id);
		
		echo json_encode(array("success"=>"task restored from deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function deleteTask() {
	session_write_close();
	$id = passed_var("id", "post");
	$sql = "UPDATE cse_task tsk
			SET tsk.`deleted` = 'Y'
			WHERE `task_id`=:id";
	
	try {
		
		$task = getTaskInfo($id);
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		
		//track now
		trackTask("delete", $id);
		
		echo json_encode(array("success"=>"task marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function updateTaskDate() {
	session_write_close();
	$id = passed_var("id", "post");
	$dateandtime = passed_var("dateandtime", "post");
	$dateandtime = date("Y-m-d H:i:s", strtotime($dateandtime));
	$sql = "UPDATE cse_task tsk
			SET tsk.`task_dateandtime` = '" . $dateandtime . "'
			WHERE `task_id`=:id";
	//die($sql);
	try {
		
		$task = getTaskInfo($id);
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		//track now
		trackTask("update", $id);
		echo json_encode(array("success"=>"task date updated", "id"=>$id));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function transferTasks($ids = "", $from_id = "", $user_id = "") {
	session_write_close();
	$blnReturn = true;
	if ($ids == "") {
		$blnReturn = false;
		$ids = passed_var("ids", "post");
		$from_id = passed_var("from", "post");
		$user_id = passed_var("assignee", "post");
	}
	$customer_id =  $_SESSION["user_customer_id"];
	
	$from_user = getUserInfo($from_id);
	$from_user_uuid = $from_user->uuid;
	
	$to_user = getUserInfo($user_id);
	$to_user_uuid = $to_user->uuid;
	
	//die(print_r($user));
	try {
		//go through each task one by one
		//open the assignees
		//break up
		//rewrite the assignee field
		//update
		//track update
		
		//open the ccs
		//break up
		//rewrite the assignee field
		//update
		//track update
		
		//print_r($user);
		$sql = "SELECT tsk.*
		FROM cse_task tsk
		WHERE `task_id` IN (" . $ids . ")
		AND customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$tasks = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		foreach($tasks as $task) {	
			//print_r($task);
			$task_id = $task->task_id;
			$assignee = $task->assignee;
			$arrAssignee = explode(";", $assignee);
			foreach($arrAssignee as $andex=>$ass) {
				if ($ass==$from_user->nickname) {
					$arrAssignee[$andex] = $to_user->nickname;
				}
			}
			$assignee = implode(";", $arrAssignee);
			
			$cc = $task->cc;
			$arrCc = explode(";", $cc);
			foreach($arrCc as $andex=>$ass) {
				if ($ass==$from_user->nickname) {
					$arrCc[$andex] = $to_user->nickname;
				}
			}
			$cc = implode(";", $arrCc);
			
			$sql = "UPDATE cse_task tsk
				SET tsk.`assignee` = :assignee, 
				tsk.cc = :cc
				WHERE `task_id` = :task_id
				AND customer_id = :customer_id";
			//echo $sql . "\r\n";
			//print_r($arrAssignee);
			//die(print_r($arrCc));
			//echo $assignee . "\r\n";
			//echo $cc . "\r\n";
			//die();
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->bindParam("task_id", $task_id);
			$stmt->bindParam("assignee", $assignee);
			$stmt->bindParam("cc", $cc);
			$stmt->execute();
			
			$sql = "UPDATE cse_task_user tuser, cse_task task
			SET tuser.user_uuid = :to_user_uuid
			WHERE tuser.task_uuid = task.task_uuid
			AND `task_id` = :task_id
			AND `type` = 'to'
			AND tuser.user_uuid = :from_user_uuid
			AND  tuser.customer_id = :customer_id";
			
			//echo $sql . "\r\n";
			//die();
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->bindParam("task_id", $task_id);
			$stmt->bindParam("to_user_uuid", $to_user_uuid);
			$stmt->bindParam("from_user_uuid", $from_user_uuid);
			$stmt->execute();
			
			$sql = "UPDATE cse_task_user tuser, cse_task task
			SET tuser.user_uuid = :to_user_uuid
			WHERE tuser.task_uuid = task.task_uuid
			AND `task_id` = :task_id
			AND `type` = 'cc'
			AND tuser.user_uuid = :from_user_uuid
			AND  tuser.customer_id = :customer_id";
			//echo $sql . "<br />";

			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->bindParam("task_id", $task_id);
			$stmt->bindParam("to_user_uuid", $to_user_uuid);
			$stmt->bindParam("from_user_uuid", $from_user_uuid);
			$stmt->execute();
			
			trackTask("transfer", $task_id);
		}
		if (!$blnReturn) {
			echo json_encode(array("success"=>"transfer completed", "ids"=>$ids));
		} else {
			return true;
		}
	} catch(PDOException $e) {
		if (!$blnReturn) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
		} else {
			return false;
		}
	}
}
function getTaskTypes() {
	session_write_close();
	
	$sql = "SELECT cct.*, cct.task_type_id id 
	FROM `cse_task_type` cct 
	WHERE 1
	ORDER BY task_type ASC";
	try {
		$types = DB::select($sql);
		echo json_encode($types);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function saveTaskTypes() {
	session_write_close();
	$user_uuid = $_SESSION["user_id"];
	$right_now = date("Y-m-d H:i:s");
	$task_type = passed_var("task_type", "post");

	$table_name = "task_type";
	
	$sql = "INSERT INTO `cse_" . $table_name . "` (" . $table_name . ", last_change_user, last_change_date)
	SELECT :task_type, :user_uuid, :right_now
						FROM dual
						WHERE NOT EXISTS (
							SELECT * 
							FROM `cse_" . $table_name . "` 
							WHERE " . $table_name . " = :task_type
						)";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("task_type", $task_type);
		$stmt->bindParam("user_uuid", $user_uuid);
		$stmt->bindParam("right_now", $right_now);
		$stmt->execute();
		
		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
}
function updateTaskTypes() {
	session_write_close();
	$table_uuid = uniqid("SF", false);
	$user_uuid = $_SESSION["user_id"];
	$right_now = date("Y-m-d H:i:s");
	$id = passed_var("task_type_id", "post");
	$deleted = passed_var("deleted", "post");
	$task_type = passed_var("task_type", "post");

	$table_name = "task_type";
	
	$sql = "UPDATE `cse_" . $table_name . "` 
	SET `" . $table_name . "` = :task_type,
	deleted = :deleted, 
	last_change_user = :user_uuid, 
	last_change_date = :right_now
	WHERE `" . $table_name . "_id` = :id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("task_type", $task_type);
		$stmt->bindParam("deleted", $deleted);
		$stmt->bindParam("user_uuid", $user_uuid);
		$stmt->bindParam("right_now", $right_now);
		$stmt->execute();
		
		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("error3"=> array("text"=>$e->getMessage()));
		die(json_encode($error));
	}	
}
function trackTask($operation, $task_id) {
	$sql = "INSERT INTO cse_task_track (`user_uuid`, `user_logon`, `operation`, `task_id`, `task_uuid`, `task_name`, `task_date`, `task_description`, `task_first_name`, `task_last_name`, `task_dateandtime`, `task_end_time`, `full_address`, 
	`assignee`, `cc`, `task_title`, `task_email`, `task_hour`, `task_type`, `type_of_task`, `task_from`, 
	`task_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed`, `color`, `customer_id`, `attachments`, `deleted`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `task_id`, `task_uuid`, `task_name`, `task_date`, `task_description`, `task_first_name`, `task_last_name`, `task_dateandtime`, `task_end_time`, `full_address`, 
	`assignee`, `cc`, `task_title`, `task_email`, `task_hour`, `task_type`, `type_of_task`, `task_from`, 
	`task_priority`, `end_date`, `completed_date`, `callback_date`, `callback_completed`, `color`, `customer_id`, `attachments`, `deleted`
	FROM cse_task
	WHERE 1
	AND task_id = " . $task_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	
	try {
		DB::run($sql);
	$new_id = DB::lastInsertId();
	
		$task = getTaskInfo($task_id);
		//new the case_uuid
		$kase = getKaseInfoByTask($task_id);
		$case_uuid = "";
		if (is_object($kase)) {
			$case_uuid = $kase->uuid;
		} else {
			return false;
		}
		$activity_category = "Task";
		switch($operation){
			case "insert":
				$operation .= "ed";
				break;
			case "transfer":
				$operation .= "red";
				break;
			case "update":
			case "delete":
				$operation .= "d";
				break;
		}
		$activity_uuid = uniqid("KS", false);
		$activity = "Task [<a title='Click to edit task' class='white_text edit_task' id='edit_task_" . $task_id . "_" . $kase->id . "' data-toggle='modal' data-target='#myModal4' style='cursor:pointer'>" . $task->task_id . " scheduled for " . date("m/d/y h:iA", strtotime($task->task_dateandtime)) . "</a>] was " . $operation . "  by " . $_SESSION['user_name'];
		if ($task->assignee!="") {
			$activity .= "<br />Assigned To:" . $task->assignee;
		}
		if ($task->cc!="") {
			$activity .= "<br />Copied To:" . $task->cc;
		}
		
		if ($task->full_address!="") {
			$activity .= "<br />Location:" . $task->full_address;
		}
		
		if ($task->task_description!="") {
			$activity .= "<br />" . $task->task_description;
		}
		$billing_time = 0;
		if (isset($_POST["billing_time"])) {
			$billing_time = passed_var("billing_time", "post");
		}
		$activity_id = recordActivity($operation, $activity, $case_uuid, $new_id, $activity_category, $billing_time);
		
		return $activity_id;
		
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
