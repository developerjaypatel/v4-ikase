<?php
$app->get('/batchscan', authorize('user'),	'getBatchscans');
$app->get('/batchscan/:id', authorize('user'),	'getBatchscan');

$app->post('/batchscan/delete', authorize('user'), 'deleteBatchscan');
$app->post('/batchscan/remoteadd', 'addRemoteBatchscan');
$app->post('/batchscan/add', authorize('user'), 'addBatchscan');
$app->post('/batchscan/update', authorize('user'), 'updateBatchscan');

$app->get('/batchscans/open', 'openQueue');
$app->get('/batchscans/queue', 'getQueue');
$app->get('/batchscans/queuescount', authorize('user'), 'getQueuesCount');
$app->get('/batchscans/preprocess', 'currentOpenQueue');
$app->get('/batchscans/stitchprocess', 'currentStitchQueue');

$app->get('/batchscans/processed', 'getProcessed');
$app->get('/batchscans/processeddocuments', 'getProcessedDocuments');

$app->post('/batchscans/new', 'newQueue');
$app->post('/batchscans/track', 'trackScan');
$app->post('/batchscans/lasttrack', 'lastTrack');
$app->post('/batchscans/updatequeue', 'updateQueue');
$app->post('/batchscans/restart', authorize('user'), 'restartQueue');

//new batchscan procedure below, jquery based on cropped png thumbnails
//upload pdf
//break up into pngs
$app->post('/batchscan/explode', authorize('user'), 'explodeBatchscan');
$app->post('/batchscan/crop', authorize('user'), 'createCrops');
$app->post('/batchscan/checkprep', authorize('user'), 'checkPrepBatchscan');

$app->post('/batchscan/addseparator', authorize('user'), 'addSepBatchscan');
$app->post('/batchscan/addseparators', authorize('user'), 'addSepsBatchscan');
$app->post('/batchscan/stitchstack', authorize('user'), 'stitchBatchscanStacks');
$app->post('/batchscan/checkstitch', authorize('user'), 'checkStitchBatchscan');
$app->post('/batchscan/cleanafter', authorize('user'), 'cleanAfterBatchscan');

//thumbnail for each png
//crop each png
//put pngs in batchscans/customer_id/date/timestamp/
//add customer_id/date/timestamp to customer_queue
//send to browser list of images for barcode processing
//iterate through crops to identify ikase barcode
//create stacks after each separator identification
//add stacks to pending batchscans for notification and case attach


function ipRestrict() {
	session_write_close();
	if (!isset($_SERVER["REMOTE_HOST"])) {
		die("no host");
	}
	if (strpos($_SERVER["REMOTE_HOST"], "173.58.194.")===false && $_SERVER["REMOTE_HOST"]!="47.153.51.181") {
		die("not you"); //die(print_r($_SERVER));
	} else {
		$sql = "INSERT INTO `ikase`.`cse_batchscan_calls` (`request`, `uri`)
				VALUES ('" .  addslashes(json_encode($_REQUEST)) . "', '" . $_SERVER['REQUEST_URI'] . "')";
	
		try {
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$db = null; $stmt = null;
			
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
			return false;
		}
	}
}
function newQueue() {
	ipRestrict();
	$customer_id = passed_var("customer_id", "post");
	$stored_file = passed_var("stored_file", "post");
	$user_id = passed_var("user_id", "post");
	$user_name = passed_var("user_name", "post");
	
	$sql = "INSERT INTO `ikase`.`cse_batchscan_queue` (`queue_uuid`, `stored_file`, `queue_date`, `user_id`, `user_name`, `customer_id`)
				VALUES (:queue_uuid, :stored_file, '" .  date("Y-m-d H:i:s") . "', :user_id, :user_name, :customer_id)";
	
	try {
		$queue_uuid = uniqid("BS");
		//$stored_file = addslashes($stored_file);
		//$user_name = addslashes($user_name);
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("queue_uuid", $queue_uuid);
		$stmt->bindParam("stored_file", $stored_file);
		$stmt->bindParam("user_name", $user_name);
		$stmt->bindParam("user_id", $user_id);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		
		$queue_id = $db->lastInsertId();
		$db = null; $stmt = null;

        echo json_encode(array("success"=>true, "queue_id"=>$queue_id));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
		return false;
	}
}
function lastTrack() {
	ipRestrict();
	$customer_id = passed_var("customer_id", "post");
	$uploaded = passed_var("uploaded", "post");
	
	$sql = "SELECT cbq.stored_file, cbq.timestamp, cbq.pages, cbq.documents, cbqt.*
	FROM `ikase`.`cse_batchscan_queue_track` cbqt
	INNER JOIN `ikase`.`cse_batchscan_queue` cbq
	ON cbqt.queue_uuid = cbq.queue_uuid
	WHERE 1
	AND cbq.customer_id = '" . $customer_id . "'
	AND stored_file LIKE '%" . $uploaded . "'
	ORDER BY track_id DESC
	LIMIT 0, 1";
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		//$stmt->bindParam("customer_id", $customer_id);
		//$stmt->bindParam("uploaded", $uploaded);
		$queue_track = $stmt->fetchObject();
		$db = null;

        echo json_encode($queue_track);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function restartQueue() {
	session_write_close();
	
	$sql = "UPDATE ikase.cse_batchscan_queue
	SET queue_status = 'QUEUE'
	WHERE customer_id = :customer_id
	AND queue_status = 'OPEN'";
	
	try {
		$customer_id = $_SESSION["user_customer_id"];
		$db = getConnection();
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$db = null;

		echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function openQueue() {
	ipRestrict();
	
	$sql = "SELECT MIN(queue_id) queue_id, COUNT(queue_id) `queue_count`
	FROM `ikase`.`cse_batchscan_queue`
	WHERE `queue_status` = 'OPEN'";
	
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$queue = $stmt->fetchObject();
		$db = null;

        echo json_encode($queue);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function currentOpenQueue() {
	ipRestrict();
	
	$sql = "SELECT *
	FROM `ikase`.`cse_batchscan_queue`
	WHERE `queue_status` = 'OPEN'
	#AND `documents` = 0";
	
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$queue = $stmt->fetchObject();
		$db = null;

        echo json_encode($queue);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function currentStitchQueue() {
	ipRestrict();
	
	$sql = "SELECT *
	FROM `ikase`.`cse_batchscan_queue`
	WHERE `queue_status` = 'OPEN'
	AND `documents` > 0";
	
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$queue = $stmt->fetchObject();
		$db = null;

        echo json_encode($queue);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function getQueuesCount() {
	$sql = "SELECT COUNT(queue_id) queue_count
	FROM `ikase`.`cse_batchscan_queue`
	WHERE `queue_status` = 'QUEUE'
	ORDER BY queue_id ASC
	LIMIT 0, 1";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$queue = $stmt->fetchObject();
		$db = null;
		
		//die(print_r($queue));
		echo json_encode($queue);
		
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getQueue() {
	ipRestrict();
	$sql = "SELECT *
	FROM `ikase`.`cse_batchscan_queue`
	WHERE `queue_status` = 'QUEUE'
	ORDER BY queue_id ASC
	LIMIT 0, 1";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$queue = $stmt->fetchObject();
		$db = null;
		
		//die(print_r($queue));
		if (is_object($queue)) {
        	echo json_encode($queue);
		} else {
			echo "";
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function updateQueue() {
	ipRestrict();
	$queue_id = passed_var("queue_id", "post");
	$queue_status = passed_var("queue_status", "post");
	$pages = passed_var("pages", "post");
	$timestamp = passed_var("timestamp", "post");
	$separators = passed_var("separators", "post");
	$documents = passed_var("documents", "post");
	
	$sql = "UPDATE `ikase`.`cse_batchscan_queue`
	SET `queue_status` = :queue_status";
	if ($pages!="") {
		$sql .= ",
		`pages` = :pages,
		`timestamp` = :timestamp";
	}
	if ($separators!="") {
		$sql .= ",
		`separators` = :separators";
	}
	if ($documents!="") {
		$sql .= ",
		`documents` = :documents";
	}
	$sql .= "
	WHERE `queue_id` = :queue_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("queue_id", $queue_id);
		$stmt->bindParam("queue_status", $queue_status);
		if ($pages!="") {
			$stmt->bindParam("pages", $pages);
			$stmt->bindParam("timestamp", $timestamp);
		}
		if ($separators!="") {
			$stmt->bindParam("separators", $separators);
		}
		if ($documents!="") {
			$stmt->bindParam("documents", $documents);
		}
		$stmt->execute();
		$db = null; $stmt = null;

        echo json_encode(array("success"=>true, "queue_id"=>$queue_id, "queue_status"=>$queue_status));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
		return false;
	}
}
function trackScan() {
	ipRestrict();
	$queue_id = passed_var("queue_id", "post");
	$description = passed_var("description", "post");
	
	$sql = "INSERT INTO `ikase`.`cse_batchscan_queue_track` (`queue_uuid`, `description`, `microtime`, `ip_address`)
	SELECT `queue_uuid`,  :description, '" .  microtime(true) . "', '" . $_SERVER["REMOTE_HOST"] . "'
	FROM `ikase`.`cse_batchscan_queue`
	WHERE queue_id = :queue_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("queue_id", $queue_id);
		$stmt->bindParam("description", $description);
		$stmt->execute();
		$db = null; $stmt = null;

        return true;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
		return false;
	}
}
function getBatchscans() {
	session_write_close();
    $sql = "SELECT bt.`batchscan_id`, bt.`dateandtime`, bt.`filename`, bt.`pages`, bt.`stacks`, bt.time_stamp, bt.completion,
			bt.`batchscan_id` `id`, cu.nickname user_name
			FROM `cse_batchscan_track` bt
			INNER JOIN `cse_batchscan` cb
			ON bt.batchscan_id = cb.batchscan_id AND bt.operation = 'insert'
			INNER JOIN ikase.cse_user cu
			ON bt.user_uuid = cu.user_uuid
			WHERE bt.customer_id = " . $_SESSION['user_customer_id'] . "
			AND bt.deleted ='N' 
			AND cb.deleted = 'N'
			AND bt.batchscan_track_id > 368
			ORDER BY bt.dateandtime DESC
			LIMIT 100";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$batchscan = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;

        echo json_encode($batchscan);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getBatchscan($id) {
	session_write_close();
    $sql = "SELECT `batchscan_id`, `dateandtime`, `filename`, `pages`, `separators`, `customer_id`, `processed`, `separated`, `stacked`, `deleted`
			FROM `cse_batchscan` 
			WHERE batchscan_id=:id
			AND cse_batchscan.customer_id = " . $_SESSION['user_customer_id'] . "
			AND deleted = 'N'";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$batchscan = $stmt->fetchObject();
		$db = null;

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($batchscan);
        } else {
            echo $_GET['callback'] . '(' . json_encode($batchscan) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getBatchscanInfo($id) {
	session_write_close();
    $sql = "SELECT `batchscan_id`, `dateandtime`, `filename`, `pages`, `separators`, `customer_id`, `processed`, `separated`, `stacked`, `deleted`, `time_stamp`
			FROM `cse_batchscan` 
			WHERE batchscan_id=:id
			AND cse_batchscan.customer_id = " . $_SESSION['user_customer_id'] . "
			AND deleted = 'N'";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$batchscan = $stmt->fetchObject();
		$db = null;

        // Include support for JSONP requests
        return $batchscan;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function deleteBatchscan() {
	session_write_close();
	$id = $_POST["batchscan_id"];
	$sql = "UPDATE cse_batchscan 
			SET deleted = 'Y'
			WHERE batchscan_id=:id
			AND cse_batchscan.customer_id = " . $_SESSION['user_customer_id'];
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$db = null;
		trackBatchscan("delete", $id);
		echo json_encode(array("success"=>"employee marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addRemoteBatchscan() {
	if ($_SERVER['REMOTE_ADDR']!="173.58.194.150" && $_SERVER['REMOTE_ADDR']!="173.58.194.146" && $_SERVER['REMOTE_ADDR']!="173.58.194.148" && $_SERVER['REMOTE_ADDR']!="71.106.134.58") {
		echo $_SERVER['REMOTE_ADDR'] . "\r\n";
		die("no go...");
	}
	
	$customer_id = passed_var("customer_id", "post");
	$user_id = passed_var("user_id", "post");
	$user_name = passed_var("user_name", "post");
	$filename = passed_var("filename", "post");
	$pages = passed_var("pages", "post");
	$documents = passed_var("documents", "post");
	$time_stamp = passed_var("timestamp", "post");
	$first_page = passed_var("first_page", "post");
	if (!is_numeric($customer_id)) {
		die("no id");
	}
	
	$_SESSION['user_customer_id'] = $customer_id;
	if (is_numeric($user_id)) {
		//look up the uuid
		$user = getUserInfo($user_id);
		if (!is_object($user)) {
			die("no user");
		}
		$user_id = $user->user_uuid;
	}
	$_SESSION['user_id'] = $user_id;
	$_SESSION['user_name'] = $user_name;
	
	session_write_close();
	//make sure directories exist
	$import_dir = "C:\\inetpub\\wwwroot\\iKase.org\\uploads\\" . $customer_id;
	if (!is_dir($import_dir)) {
		mkdir($import_dir, 0755, true);
	}
	$import_dir = "C:\\inetpub\\wwwroot\\iKase.org\\uploads\\" . $customer_id . "\\imports\\";
	if (!is_dir($import_dir)) {
		mkdir($import_dir, 0755, true);
	}
	//die($import_dir);
	$sql_customer = "SELECT cus_name, data_source, permissions
	FROM  `ikase`.`cse_customer` 
	WHERE customer_id = :customer_id AND deleted = 'N'";
	try {				
		$db = getConnection();
		$stmt = $db->prepare($sql_customer);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$customer = $stmt->fetchObject();
		
		//echo print_r($customer);
		if (!is_object($customer)) {
			die("no go");
		}
		$cus_name = $customer->cus_name;
		$data_source = $customer->data_source;
		if ($data_source=="") {
			$return = "ikase";
		}
		if ($data_source!="") {
			$return = "ikase_" . $data_source;
		}
		$db = null;

	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	
	//first check if already in
	$sql = "SELECT batchscan_id
	FROM `" . $return . "`.`cse_batchscan`
	WHERE time_stamp = :time_stamp
	AND customer_id = :customer_id";
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->bindParam("time_stamp", $time_stamp);
	$stmt->execute();
	$batchscan = $stmt->fetchObject();
	$stmt->closeCursor(); $db = null; $stmt = null;
	
	if (is_object($batchscan)) {
		//update the batchscan with latest info
		$sql = "UPDATE `" . $return . "`.`cse_batchscan`
		SET `pages` = :pages, 
		`stacks` = :documents, 
		`completion` = :first_page
		WHERE batchscan_id = :batchscan_id
		AND customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("batchscan_id", $batchscan_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("pages", $pages);
		$stmt->bindParam("documents", $documents);
		$stmt->bindParam("first_page", $first_page);
		$stmt->execute();
		$db = null; $stmt = null;
		
		echo json_encode(array("state"=>"exists", "id"=>$batchscan->batchscan_id, "ip"=>$_SERVER['REMOTE_ADDR'], "user_id"=>$_SESSION["user_id"])); 
	} else {
		//if not
		$sql = "INSERT INTO `" . $return . "`.`cse_batchscan` (`filename`, `customer_id`, `time_stamp`, `pages`, `stacks`, `completion`) 
			VALUES (:filename, :customer_id, :time_stamp, :pages, :documents, :first_page)";
		//echo $sql;
		try {
			
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("filename", $filename);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->bindParam("time_stamp", $time_stamp);
			$stmt->bindParam("pages", $pages);
			$stmt->bindParam("documents", $documents);
			$stmt->bindParam("first_page", $first_page);
			$stmt->execute();
			$new_id = $db->lastInsertId();
			$db = null; $stmt = null;
	
			echo json_encode(array("state"=>"new", "id"=>$new_id, "ip"=>$_SERVER['REMOTE_ADDR'], "user_id"=>$_SESSION["user_id"])); 
			
			$return = "`" . $return . "`.";
			trackBatchscan("insert", $new_id, $return);
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
	}
}
function addBatchscan() {
	session_write_close();
	$request = Slim::getInstance()->request();

	$sql = "INSERT INTO cse_batchscan (`filename`, `customer_id`) 
			VALUES (:filename, :customer_id)";
	try {
		
		$db = getConnection();
		//die($sql); 
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("filename", $_POST["filename"]);
		$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
		$stmt->execute();
		$new_id = $db->lastInsertId();
		$db = null;
		
		trackBatchscan("insert", $new_id);
		echo json_encode(array("id"=>$new_id, "ip"=>$_SERVER['HTTP_REFERER'])); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function stitchBatchscanStacks() {
	$blnBatchscan3 = true;	//as of 1/29/2018	//($_SESSION["user_plain_id"]==2);
	
	$batchscan_id = passed_var("id", "post");
	$customer_id =  $_SESSION["user_customer_id"];
	$batchscan = getBatchscanInfo($batchscan_id);
	$pages = $batchscan->pages;
	$sep = $batchscan->separators;
	$date = date("Ymd", strtotime($batchscan->dateandtime));
	$uploaded = $batchscan->filename;
	$uploaded = str_replace("C:\\inetpub\\wwwroot\\iKase.org\\scans\\" . $customer_id . "\\" . $date . "\\", "", $uploaded);
	$uploaded = str_replace(".pdf", "", $uploaded);
	
	
	$time_stamp = date("U", strtotime($batchscan->dateandtime));
	
	//update the batchscan record with new time_stamp
	$sql = "UPDATE `cse_batchscan` 
			SET time_stamp = :time_stamp
			WHERE batchscan_id = :batchscan_id
			AND customer_id = :customer_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("time_stamp", $time_stamp);
		$stmt->bindParam("batchscan_id", $batchscan_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		$db = null;
		trackBatchscan("prep", $batchscan_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	
	//break up into multiple pdfs
	$pdftk = '"C:\\Program Files (x86)\\PDFtk\\bin\\pdftk.exe"';
	$uploadDir = $_SERVER['DOCUMENT_ROOT'] . "\\scans\\" . $customer_id . "\\" . $date;
	
	$source_file = '"' . $uploadDir . '\\' . $uploaded . '.pdf"';
	$destination = '"' . $uploadDir . '\\' . $uploaded . '_%02d.pdf"';
	$command = $pdftk . " " . $source_file . " burst output " . " " . $destination;
	exec($command);
	
	$arrSeparators = array();
	if ($sep!="") {
		$arrSeparators = explode("|", $sep);
		sort($arrSeparators);
		$arrSeparators = array_unique($arrSeparators);
		$separators = implode("|", $arrSeparators);
	} else {
		$separators = "";
	}
	//update after sorting
	//update the batchscan record with sorted separators
	$sql = "UPDATE `cse_batchscan` 
			SET `separators` = :separators
			WHERE batchscan_id = :batchscan_id
			AND customer_id = :customer_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("separators", $separators);
		$stmt->bindParam("batchscan_id", $batchscan_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		$db = null;
		trackBatchscan("sep_sort", $batchscan_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	
	$arrStack = array();
	$document_count = 0;
	
	//
	//now start stitching
	$min_page = 0;
	$blnStacked = false;
	
	for ($jnt=0; $jnt < $pages;$jnt++) {
		if (in_array($jnt, $arrSeparators)) {
			$document_count++;
			//new stack
			$arrStacks[] = $arrStack;
			$blnStacked = true;
		} else {
			$arrStack[$document_count][] = $jnt;
		}
	}
	$pdftk = '"C:\\Program Files (x86)\\PDFtk\\bin\\pdftk.exe"';

	$arrStackList = array();
	$int = $pages - 1;
	
	$arrCommands = array();
	$arrStitchList = array();
	
	foreach($arrStack as $stack) {
		//die(print_r($stack));
		//last page of the stack
		$start = $stack[0];
		$max_page = count($stack) - 1;
		$len = $stack[$max_page] + 1;
		
		$arrList = array();
		
		//echo $stack[0] . "\r\n";
		//foreach($arrListInfo as $stack_item) {
		for($stack_item=$start; $stack_item < $len; $stack_item++) {
			//$arrList[] = "/home/cstmwb/public_html/autho/web/uploads/" . $customer_id . "/" . $uploaded . ".pdf[" . $stack_item . "]";
			$write_item = $stack_item + 1;
			if($write_item < 10) {
				$write_item = "0" . $write_item;
			}
			
			//echo $write_item . "\r\n";
			$arrList[] = $uploadDir . "\\" . $uploaded . "_" . $write_item . ".pdf";
		}
		//print_r($arrList);
		//die();
		$document_list = '"' . implode('" "', $arrList) . '"';
		
		//$new_pdf_path = "/home/cstmwb/public_html/autho/web/uploads/" .$customer_id . "/" .  $uploaded . "_" . ($arrStackInfo[2]+1) . "_" . ($arrStackInfo[3]+1) . ".pdf";
		$import_dir = $_SERVER['DOCUMENT_ROOT'] . "\\scans\\" . $customer_id . "\\" . $date . "\\imports\\";
		if (!file_exists($import_dir)) {
			mkdir($import_dir, 0755, true);
		}
		if ($blnBatchscan3) {
			$customer_dir = $_SERVER['DOCUMENT_ROOT'] . "\\uploads\\" . $customer_id . "\\imports";
			if (!is_dir($customer_dir)) {
				mkdir($customer_dir . $time_stamp, 0755, true);
			}
			$customer_dir = $_SERVER['DOCUMENT_ROOT'] . "\\uploads\\" . $customer_id . "\\imports\\" . $time_stamp;
			if (!is_dir($customer_dir)) {
				mkdir($customer_dir, 0755, true);
			}
			//place it in the normal upload folder structure
			$import_dir = $customer_dir . "\\";
		}
		$new_pdf_path = $import_dir . $uploaded . "_" . ($stack[0] + 1) . "_" . ($stack[$max_page] + 1) . ".pdf";
		if (file_exists($new_pdf_path)) {
			unlink($new_pdf_path);
		}
		$new_pdf_path = '"' . $new_pdf_path . '"';
		$command = $pdftk . " " . $document_list . " cat output " . $new_pdf_path;
		
		exec($command);
		$arrCommands[] = $command;
		
		$stitch = ($stack[0] + 1) . "_" . ($stack[$max_page] + 1);
		$arrStitchList[] =  $stitch;
		
		$new_pdf_path = $uploaded . "_" . ($stack[0] + 1) . "_" . ($stack[$max_page] + 1) . ".pdf";
		
		$full_pdf_path = $import_dir . $new_pdf_path;
		
		//add the new pdf to documents
		$sql = "INSERT INTO ";
		$sql .= "cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, description, description_html, type, thumbnail_folder, verified, customer_id) 
				VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :description, :description_html, :type, :thumbnail_folder, :verified, :customer_id)";
				
		$document_uuid = uniqid("KS");
		$parent_document_uuid = $document_uuid;
		$document_name =  $new_pdf_path;
		$document_date = $batchscan->dateandtime;
		
		$document_extension = "pdf";
		$description = $stitch;
		$description_html = $stitch;
		
		if ($blnBatchscan3) {
			$type = "batchscan3";
			$document_filename = $new_pdf_path;
			$thumbnail_folder = $time_stamp;
		} else {
			$document_filename = $full_pdf_path;
			$thumbnail_folder = date("Ymd", strtotime($batchscan->dateandtime));
		}
		$verified = "Y";
		
		$sql_insert = $sql;
		
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("document_uuid", $document_uuid);
		$stmt->bindParam("parent_document_uuid", $batchscan_id);
		$stmt->bindParam("document_name", $document_name);
		$stmt->bindParam("document_date", $document_date);
		$stmt->bindParam("document_filename", $document_filename);
		$stmt->bindParam("document_extension", $document_extension);
		$stmt->bindParam("description", $description);
		$stmt->bindParam("description_html", $description_html);
		$stmt->bindParam("type", $type);
		$stmt->bindParam("thumbnail_folder", $thumbnail_folder);
		$stmt->bindParam("verified", $verified);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$new_id = $db->lastInsertId();
		$stmt = null; $db = null;
		
		trackDocument("insert", $new_id);
		
		$notification_uuid = uniqid("KN", false);
		$sql = "INSERT INTO `cse_notification` 
		(`document_uuid`, `notification_uuid`, `user_uuid`, `notification`, `notification_date`, `customer_id`)
		VALUES ('" . $document_uuid . "', '" . $notification_uuid . "', '" . $_SESSION['user_id'] . "','review', '" . date("Y-m-d H:i:s") . "', '" . $customer_id . "')";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$stmt = null; $db = null;
	}
	
	//clean up after yourself
	//die("upl:" .  $uploaded . ".pdf");
	$scans = scandir($uploadDir);
	//die(print_r($arrCommands));
	foreach($scans as $scan_index=>$scan) {
		if ($scan=="." || $scan==".." || $scan=="imports" || $scan==$uploaded . ".pdf") {
			continue;
		}
		$blnContinue = false;
		foreach($arrStitchList as $stitch) {
			$arrStitch = explode("_", $stitch);
			$stitch_start = $arrStitch[0];
			//die($scan . "-" . $stitch_start . ".jpg");
			$strpos = strpos($scan, "-" . $stitch_start . ".jpg");
			//echo $scan . " - " . $strpos . "\r\n";
			
			if (strpos($scan, "-" . $stitch_start . ".jpg") > -1) {
				//die($scan . " -> " . $stitch);
				$blnContinue = true;
				break;
			}
		}
		//die("stop");
		if ($blnContinue) {
			continue;
		}
		//unlink($uploadDir . "\\" . $scan);
	}
	
	//update the batchscan as processed
	$processed = date("Y-m-d H:i:s");
	$sql = "UPDATE `cse_batchscan` 
			SET processed = :processed
			WHERE batchscan_id = :batchscan_id
			AND customer_id = :customer_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("processed", $processed);
		$stmt->bindParam("batchscan_id", $batchscan_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		$db = null;
		trackBatchscan("prep", $batchscan_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	
	//die(print_r($arrCommands));
	die(json_encode(array("success"=>true, "batchscan_id"=>$batchscan_id, "stitches"=>$arrStitchList)));
}
function addSepsBatchscan() {
	session_write_close();
	$batchscan_id = passed_var("id", "post");
	$separators = passed_var("separators", "post");
	$customer_id = $_SESSION["user_customer_id"];
	
	//first get the batchscan
	$batchscan = getBatchscanInfo($batchscan_id);
	//update the batchscan record with pages
	$sql = "UPDATE `cse_batchscan` 
			SET `separators` = :separators
			WHERE batchscan_id = :batchscan_id
			AND customer_id = :customer_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("separators", $separators);
		$stmt->bindParam("batchscan_id", $batchscan_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		$db = null;
		trackBatchscan("sep_add", $batchscan_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	echo json_encode(array("success"=>true, "batchscan_id"=>$batchscan_id));
}
function addSepBatchscan() {
	session_write_close();
	$batchscan_id = passed_var("id", "post");
	$page_number = passed_var("page", "post");
	$customer_id = $_SESSION["user_customer_id"];
	
	//first get the batchscan
	$batchscan = getBatchscanInfo($batchscan_id);
	//update separator
	$sep = $batchscan->separators;
	$arrSep = explode("|", $sep);
	if (!in_array($page_number, $arrSep)) {
		$arrSep[] = $page_number;
	}
	for($int = count($arrSep) - 1; $int >=0; $int--) {
		if ($arrSep[$int]=="") {
			unset($arrSep[$int]);
		}
	}
	$separators = implode("|", $arrSep);
	//die("sep:" . $separators);
	
	//save again
	//update the batchscan record with pages
	$sql = "UPDATE `cse_batchscan` 
			SET `separators` = :separators
			WHERE batchscan_id = :batchscan_id
			AND customer_id = :customer_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("separators", $separators);
		$stmt->bindParam("batchscan_id", $batchscan_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		$db = null;
		trackBatchscan("sep _add", $batchscan_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	echo json_encode(array("success"=>true, "page_number"=>$page_number, "batchscan_id"=>$batchscan_id));
}
function checkStitchBatchscan() {
	session_write_close();
	$batchscan_id = passed_var("id", "post");
	$customer_id = $_SESSION["user_customer_id"];
	$batchscan = getBatchscanInfo($batchscan_id);
	$separators = $batchscan->separators;
	$arrSeparators = explode("|", $separators);
	
	$sql = "SELECT COUNT(document_id) docs
	FROM cse_document doc
	WHERE doc.parent_document_uuid  = :batchscan_id
	AND doc.customer_id = :customer_id";
	$doc_count = 0;
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("batchscan_id", $batchscan_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$doc = $stmt->fetchObject();
		$stmt->closeCursor(); $db = null; $stmt = null;
		
		if (is_object($doc)) {
			$doc_count = $doc->docs;
		}
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	echo json_encode(array("success"=>true, "documents"=>count($arrSeparators), "found"=>$doc_count));
}
function cleanAfterBatchscan() {
	session_write_close();
	die();
	$batchscan_id = passed_var("id", "post");
	$customer_id = $_SESSION["user_customer_id"];
	$batchscan = getBatchscanInfo($batchscan_id);
	$date = date("Ymd", strtotime($batchscan->dateandtime));
	$filename = $batchscan->filename;
	$arrFile = explode("\\", $filename);
	$filename = $arrFile[count($arrFile) - 1];
	$filename = str_replace(".pdf", "", $filename);
	$parentname = $filename;
	
	$customer_dir = $_SERVER['DOCUMENT_ROOT'] . "\\scans\\" . $customer_id . "\\" . $date;

	$sql = "SELECT doc.* 
	FROM cse_document doc
	WHERE doc.parent_document_uuid = :batchscan_id
	AND doc.customer_id = :customer_id
	AND doc.deleted = 'N'";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("batchscan_id", $batchscan_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$docs = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $db = null; $stmt = null;
		
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	
	if (!is_array($docs)) {
		//already deleted from database
		echo json_encode(array("success"=>true, "batchscan_id"=>$batchscan_id, "message"=>"no docs"));
		die();
	}
	
	$arrKeepers = array();
	$arrToDelete = array();
	
	foreach($docs as $doc) {
		$name = $doc->document_name;
		$name = str_replace(".pdf", "", $name);
		$arrName = explode("_", $name);
		$startNumber = $arrName[count($arrName) - 2];
		$name = $parentname . "-" . $startNumber . ".jpg";
		$arrKeepers[] = $name;
	}
	$images = scandir($customer_dir);
	//print_r($arrKeepers);
	foreach($images as $image_index=>$image) {
		if ($image==".." || $image==".") {
			continue;
		}
		if (strpos($image, ".jpg") > -1) {
			//echo $image . " - " . $parentname . "\r\n";
			if (strpos($image, $parentname . "-") > -1) {
				if (in_array($image, $arrKeepers)) {
					continue;
				} else {
					$arrToDelete[] = $image;
				}
			}
		}
	}
	
	//die(print_r($arrToDelete));
	
	
	foreach($images as $image_index=>$image) {
		
		if (strpos($image, ".pdf") > -1) {
			//not main one
			if ($image != $parentname . ".pdf") {
				//die($image);			
				if (strpos($image, $parentname . "_") > -1) {
					//echo $parentname . "\r\n";
					$arrToDelete[] =$image;
	
				}
			}
		}
	}
	//die(print_r($arrToDelete));
	foreach($arrToDelete as $deletename) {
		unlink($customer_dir . "\\" . $deletename);
	}
	echo json_encode(array("success"=>true, "batchscan_id"=>$batchscan_id));
}
function checkPrepBatchscan() {
	session_write_close();
	$batchscan_id = passed_var("id", "post");
	$customer_id = $_SESSION["user_customer_id"];
	$batchscan = getBatchscanInfo($batchscan_id);
	$date = date("Ymd", strtotime($batchscan->dateandtime));
	$filename = $batchscan->filename;
	$arrFile = explode("\\", $filename);
	$filename = $arrFile[count($arrFile) - 1];
	$filename = str_replace(".pdf", "", $filename);
	
	$customer_dir = $_SERVER['DOCUMENT_ROOT'] . "\\scans\\" . $customer_id . "\\" . $date;
	$images = scandir($customer_dir);
	$pages = $batchscan->pages;
	$counter = 0;
	//die(print_r($images));
	foreach($images as $image_index=>$image) {
		if (strpos($image, "_crop.jpg") > -1) {
			//only ucrops
			//die($image);			
			if (strpos($image, $filename) > -1) {
				//echo $filename . "\r\n";
				//die($image);
				//filename is in the pdf file name
				$counter++;	
			}
		}
	}
	echo json_encode(array("success"=>true, "pages"=>$pages, "found"=>$counter));
}
function explodeBatchscan() {
	session_write_close();
	$batchscan_id = passed_var("id", "post");
	$customer_id = $_SESSION["user_customer_id"];
	$batchscan = getBatchscanInfo($batchscan_id);
	$date = date("Ymd", strtotime($batchscan->dateandtime));
	$customer_dir = $_SERVER['DOCUMENT_ROOT'] . "\\scans\\" . $customer_id . "\\" . $date;
	
	//clear it
	/*
	$images = scandir($customer_dir);
	foreach($images as $image_index=>$image) {
		if ($image=="." || $image==".." || $image=="imports") {
			continue;
		}
		if (strpos($image, ".pdf") > -1) {
			continue;
		}
		$filepath = $customer_dir . "\\" . $image;
		unlink($filepath);
	}
	*/
	$uploaded = $batchscan->filename;
	
	$file_path = $uploaded;
	//remove the extension
	$thumbnail_path = str_replace(".pdf", ".jpg", $file_path);
	
	$image_magick = new imagick();
	$image_magick->readImage($file_path);
	$pages = $image_magick->getNumberImages();
	$image_magick->setResolution(300,300);
	$image_magick->writeImages($thumbnail_path, false);
	
	//update the batchscan record with pages
	$sql = "UPDATE `cse_batchscan` 
			SET `pages` = :pages,
			separators = '',
			processed = '0000-00-00 00:00:00'
			WHERE batchscan_id = :batchscan_id
			AND customer_id = :customer_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("pages", $pages);
		$stmt->bindParam("batchscan_id", $batchscan_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		$db = null;
		trackBatchscan("prep", $batchscan_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	
	for($int = 0; $int < $pages; $int++) {
		$targetFile = str_replace(".jpg", "-" . $int .".jpg", $thumbnail_path);
		
		//side up
		$crop_path = str_replace("-" . $int .".jpg", "-" . $int ."_crop.jpg", $targetFile);
		$image_magick = new imagick(); 
		$image_magick->setResourceLimit (6, 1);
		$image_magick->readImage($targetFile);
		//$image_magick = $image_magick->flattenImages();
		$image_magick = $image_magick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
		$image_magick->setResolution(150,150);
		$image_magick->thumbnailImage(1300, 1700, true);
		
		$image_magick->setImageFormat('png');
		$image_magick->cropImage(1000, 380, 155, 450);
		
		$image_magick->writeImage($crop_path);
		$image_magick->destroy();
				
		//now flip it in case it was stacked upside down
		$crop_path = str_replace("-" . $int .".jpg", "-" . $int ."_ucrop.jpg", $targetFile);
		$image_magick = new imagick(); 
		$image_magick->setResourceLimit (6, 1);
		$image_magick->readImage($targetFile);
		//$image_magick = $image_magick->flattenImages();
		$image_magick = $image_magick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
		$image_magick->setResolution(150,150);
		$image_magick->thumbnailImage(1300, 1700, true);
		
		$image_magick->setImageFormat('png');
		//$image_magick->rotateimage("#00000000", 180);
		$image_magick->cropImage(1000, 380, 140, 854);
		
		$image_magick->writeImage($crop_path);
		$image_magick->destroy();
	}
	
	$uploaded = str_replace("C:\\inetpub\\wwwroot\\iKase.org\\scans\\" . $customer_id . "\\" . $date . "\\", "", $uploaded);
	echo json_encode(array("success"=>true, "uploaded"=>$uploaded, "pages"=>$pages, "id"=>$batchscan_id, "date"=>$date));
}
function createCrops() {
	session_write_close();
	$batchscan_id = passed_var("id", "post");
	$customer_id = $_SESSION["user_customer_id"];
	$batchscan = getBatchscanInfo($batchscan_id);
	$date = date("Ymd", strtotime($batchscan->dateandtime));
	$pages = $batchscan->pages;
	$customer_dir = $_SERVER['DOCUMENT_ROOT'] . "\\scans\\" . $customer_id . "\\" . $date;
	
	$uploaded = $batchscan->filename;
	
	$file_path = $uploaded;
	//remove the extension
	$thumbnail_path = str_replace(".pdf", ".jpg", $file_path);
	
	$int = passed_var("page_number", "post");
	//create crops
	//for($int = 0; $int < $pages; $int++) {
		$targetFile = str_replace(".jpg", "-" . $int .".jpg", $thumbnail_path);
		
		//side up
		$crop_path = str_replace("-" . $int .".jpg", "-" . $int ."_crop.jpg", $targetFile);
		$image_magick = new imagick(); 
		$image_magick->setResourceLimit (6, 1);
		$image_magick->readImage($targetFile);
		//$image_magick = $image_magick->flattenImages();
		$image_magick = $image_magick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
		$image_magick->setResolution(150,150);
		$image_magick->thumbnailImage(1300, 1700, true);
		
		$image_magick->setImageFormat('png');
		$image_magick->cropImage(1000, 380, 155, 450);
		
		$image_magick->writeImage($crop_path);
		$image_magick->destroy();
				
		//now flip it in case it was stacked upside down
		$crop_path = str_replace("-" . $int .".jpg", "-" . $int ."_ucrop.jpg", $targetFile);
		$image_magick = new imagick(); 
		$image_magick->setResourceLimit (6, 1);
		$image_magick->readImage($targetFile);
		//$image_magick = $image_magick->flattenImages();
		$image_magick = $image_magick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
		$image_magick->setResolution(150,150);
		$image_magick->thumbnailImage(1300, 1700, true);
		
		$image_magick->setImageFormat('png');
		//$image_magick->rotateimage("#00000000", 180);
		$image_magick->cropImage(1000, 380, 140, 854);
		
		$image_magick->writeImage($crop_path);
		$image_magick->destroy();
	//}
	echo json_encode(array("success"=>true, "page_number"=>$int, "uploaded"=>$uploaded, "pages"=>$pages, "id"=>$batchscan_id, "date"=>$date));
}
function updateBatchscan() {
	session_write_close();
	$request = Slim::getInstance()->request();

	$sql = "UPDATE `cse_batchscan` 
			SET `filename`=:filename,
			`pages`=:pages,
			`separators`=:separators,
			`customer_id`=:customer_id,
			`processed`=:processed,
			`separated`=:separated,
			`stacked`=:stacked
			WHERE batchscan_id = :batchscan_id
			AND cse_batchscan.customer_id = " . $_SESSION['user_customer_id'];
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("batchscan_id", $_POST["batchscan_id"]);
		$stmt->bindParam("filename", $_POST["filename"]);
		$stmt->bindParam("pages", $_POST["pages"]);
		$stmt->bindParam("separators", $_POST["separators"]);
		$stmt->bindParam("customer_id", $_POST["customer_id"]);
		$stmt->bindParam("processed", $_POST["processed"]);
		$stmt->bindParam("separated", $_POST["separated"]);
		$stmt->execute();
		
		$db = null;
		//die(print_r($newEmployee));
		trackBatchscan("update", $_POST["batchscan_id"]);
		echo json_encode(array("success"=>$_POST["batchscan_id"])); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}

function separateBatchscan() {
	$request = Slim::getInstance()->request();
	
	$sql = "UPDATE `cse_batchscan` cd
		SET cd.`separated`  = '" . addslashes($_POST["separated"]) . "'
		WHERE cd.batchscan_id = '" . $_POST["batchscan_id"] . "'
		AND cse_batchscan.customer_id = " . $_SESSION['user_customer_id'];
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$db = null;
		trackBatchscan("separate", $_POST["batchscan_id"]);
		echo json_encode(array("success"=> $_POST["case_id"] . "-" . $_POST["filename"])); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function processBatchscan() {
	$request = Slim::getInstance()->request();
	
	$sql = "UPDATE `cse_batchscan` cd
		SET cd.`processed`  = '" . addslashes($_POST["processed"]) . "'
		WHERE cd.batchscan_id = '" . $_POST["batchscan_id"] . "'
		AND cse_batchscan.customer_id = " . $_SESSION['user_customer_id'];
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$db = null;
		trackBatchscan("process", $_POST["batchscan_id"]);
		echo json_encode(array("success"=> $_POST["case_id"] . "-" . $_POST["filename"])); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function stackBatchscan() {
	$request = Slim::getInstance()->request();
	
	$sql = "UPDATE `cse_batchscan` cd
		SET cd.`stacked`  = '" . addslashes($_POST["stacked"]) . "'
		WHERE cd.batchscan_id = '" . $_POST["batchscan_id"] . "'
		AND cse_batchscan.customer_id = " . $_SESSION['user_customer_id'];
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$db = null;
		trackBatchscan("process", $_POST["batchscan_id"]);
		echo json_encode(array("success"=> $_POST["case_id"] . "-" . $_POST["filename"])); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function trackBatchscan($operation, $batchscan_id, $db_name = "") {
	$sql = "INSERT INTO " . $db_name . "cse_batchscan_track (`user_uuid`, `user_logon`, `operation`, `batchscan_id`, `dateandtime`, `filename`, `time_stamp`, `pages`, `consideration`, `attempted`, `completion`, `match`, `separators`, `stacks`, `stitched`, `customer_id`, `readimage`, `processed`, `separated`, `stacked`, `deleted`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `batchscan_id`, `dateandtime`, `filename`, `time_stamp`, `pages`, `consideration`, `attempted`, `completion`, `match`, `separators`, `stacks`, `stitched`, `customer_id`, `readimage`, `processed`, `separated`, `stacked`, `deleted`
	FROM " . $db_name . "cse_batchscan
	WHERE 1
	AND batchscan_id = " . $batchscan_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
	
		$stmt->execute();
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function getProcessed() {
	session_write_close();
		
	$url = "https://ikase.xyz/ikase/limapi/scans_processed.php";
	$contents = file_get_contents($url);
	if (!$contents) {
		die(json_encode(array("error"=>"no processed batches")));
	}
	$jdata = json_decode($contents);
	//die(print_r($jdata));
	$customer_id = $jdata->customer_id;
	$sql = "SELECT data_source 
	FROM ikase.cse_customer
	WHERE customer_id = :customer_id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$customer = $stmt->fetchObject();
		$stmt->closeCursor(); $db = null; $stmt = null;

		$data_source = $customer->data_source;
		if ($data_source=="") {
			$data_source = "ikase";
		} else {
			$data_source = "ikase_" . $data_source;
		}
        //return json_encode($queue);
		
		//insert the batchscan
		$sql = "INSERT INTO `" . $data_source . "`.`cse_batchscan` (`dateandtime`, `filename`, `time_stamp`, `pages`, `completion`, `stacks`, `customer_id`) VALUES ";
		$sql .= "('" . $jdata->dateandtime . "', '" . $jdata->filename . "', '" . $jdata->time_stamp . "', '" . $jdata->pages . "', '" . $jdata->completion . "', '" . $jdata->stacks . "', '" . $jdata->customer_id . "')";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$batchscan_id = $db->lastInsertId();
		$db = null; $stmt = null;
		
		trackBatchscan("insert", $batchscan_id, "`" . $data_source . "`.");
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
		die();
	}
	
	getProcessedDocuments($jdata->queue_id, $batchscan_id, $jdata->time_stamp, $jdata->user_id, $jdata->filename, $jdata->completion, $jdata->customer_id, $data_source);
}
function getProcessedDocuments($queue_id, $batchscan_id, $time_stamp, $user_id, $filename, $first_page, $customer_id, $data_source) {
	$url = "https://ikase.xyz/ikase/limapi/scans_processed_documents.php?customer_id=" . $customer_id . "&time_stamp=" . $time_stamp;
	//die($url);
	$contents = file_get_contents($url);
	//echo $url;
	$jdata = json_decode($contents);
	//die(print_r($jdata));
	
	//echo "dat:" . $data_source . "\r\n<br />";
	//die();
	
	//get the user now
	$sql = "SELECT user.user_id, user.user_uuid
			FROM ikase.`cse_user` user
			WHERE user.user_id=:id
			AND user.customer_id = :customer_id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $user_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$user = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	$user_uuid = $user->user_uuid;
	
	//clear any bad transfers
	
	foreach($jdata as $data) {
		$arrDeleteID[] = "'" . $data->document_uuid . "'";
	}
	 
	 try {
		$query_clear = "DELETE FROM `" . $data_source . "`.`cse_document`
		WHERE document_uuid IN ";
		$sql = $query_clear . "(" . implode(",", $arrDeleteID) . ")";
		//die($sql);
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$db = null; $stmt = null;
		
		//don't forget notifications
		$query_clear = "DELETE FROM `" . $data_source . "`.`cse_notification`
		WHERE document_uuid IN ";
		$sql = $query_clear . "(" . implode(",", $arrDeleteID) . ")";
		//die($sql);
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$db = null; $stmt = null;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
		die();
	}
	
	//transfer the actual data
	$query_doc = "INSERT INTO `" . $data_source . "`.`cse_document` (`document_uuid`, `parent_document_uuid`, `document_name`, `document_date`, `document_filename`, `document_extension`, `thumbnail_folder`, `description`, `description_html`, `source`, `received_date`, `type`, `verified`, `customer_id`) VALUES ";
	
	$query_notif = "INSERT INTO `" . $data_source . "`.`cse_notification` (`document_uuid`, `notification_uuid`, `user_uuid`, `notification`, `notification_date`, `customer_id`) VALUES ";
	$arrDocInserts = array();
	$arrNotifInserts = array();
	
	//die(print_r($jdata));
	foreach($jdata as $data) {
		$arrValues = array();
		$values = "'" . addslashes($data->document_uuid) . "'"; $arrValues[] = $values;
		$values = "'" . $batchscan_id . "'"; $arrValues[] = $values;
		$values = "'" . addslashes($data->document_name) . "'"; $arrValues[] = $values;
		$values = "'" . $data->document_date . "'"; $arrValues[] = $values;
		$values = "'" . addslashes($data->document_filename) . "'";		$arrValues[] = $values;
		$values = "'" . addslashes($data->document_extension) . "'";		$arrValues[] = $values;
		$values = "'" . addslashes($data->thumbnail_folder) . "'";		$arrValues[] = $values;
		$values = "'" . addslashes($data->description) . "'";		$arrValues[] = $values;
		$values = "'" . addslashes($data->description_html) . "'";		$arrValues[] = $values;
		$values = "'" . addslashes($data->source) . "'";		$arrValues[] = $values;
		$values = "'" . date("Y-m-d H:i:s") . "'";		$arrValues[] = $values;
		$values = "'" . addslashes($data->type) . "'";		$arrValues[] = $values;
		$values = "'" . addslashes($data->verified) . "'";		$arrValues[] = $values;
		$values = "'" . addslashes($customer_id) . "'";		$arrValues[] = $values;
		
		$arrDocInserts[] = " (" . implode(", ", $arrValues) . ")";
		
		//notifications
		$arrValues = array();
		$values = "'" . addslashes($data->document_uuid) . "'"; $arrValues[] = $values;
		$values = "'" . addslashes($data->notification_uuid) . "'"; $arrValues[] = $values;
		$values = "'" . addslashes($user_uuid) . "'"; $arrValues[] = $values;
		$values = "'review'"; $arrValues[] = $values;
		$values = "'" . addslashes($data->notification_date) . "'"; $arrValues[] = $values;
		$values = "'" . addslashes($customer_id) . "'";		$arrValues[] = $values;
		
		$arrNotifInserts[] = " (" . implode(", ", $arrValues) . ")";
	}
	
	try {
		$sql = $query_doc . implode(",\r\n", $arrDocInserts);
		//die($sql);
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$db = null; $stmt = null;
		
		//don't forget notifications
		$sql = $query_notif . implode(",\r\n", $arrNotifInserts);
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$db = null; $stmt = null;
		
		//must update the batchscan_queue
		/*
		$url = "https://ikase.xyz/ikase/limapi/scans_transferred.php";
		$params = array("customer_id"=>$customer_id, "queue_id"=>$queue_id);
		curl_post_async($params);
		*/
		
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
		die();
	}
	
	//transfer the actual files
	//connect via ftp
	$ftp_server = "ikase.xyz";
	$ftp_username = "nick";
	$ftp_pwd = "access9090";
	$uploadDir =  $_SERVER['DOCUMENT_ROOT'] . '\\uploads\\' . $customer_id . '\\imports\\';
	
	//thumbnail connection
	$conn_id = ftp_connect($ftp_server); 
	
	// login with username and password 
	$login_result = ftp_login($conn_id, $ftp_username, $ftp_pwd); 
	if (!$login_result) {
		exit('FTP Login Failed\r\n');
	} else {
		ftp_pasv($conn_id,true);
		//echo "FTP login succeeded<br />\r\n";
	}
	
	ftp_chdir($conn_id, "ikase");
	ftp_chdir($conn_id, "ikaseuploads");
	ftp_chdir($conn_id, $customer_id);
	ftp_chdir($conn_id, $time_stamp);
	//add the thumbnail
	$targetFile = $first_page;
	$uploadDir .= $time_stamp . "\\";
	if (!is_dir($uploadDir)) {
		mkdir($uploadDir, 0755, true);
	}
	//die($uploadDir);
	if (ftp_get($conn_id, $uploadDir . $targetFile, $targetFile, FTP_BINARY)) {
		//echo "successfully uploaded $targetFile\n";
	} else {
		echo "ERROR ftpd " . $targetFile . " to " .  $uploadDir . $targetFile . "<br />\r\n";
		die();
	}
	//die("thumb done");
	ftp_close($conn_id);
	
	//connect to ftp
	$conn_id = ftp_connect($ftp_server); 
	$uploadDir =  $_SERVER['DOCUMENT_ROOT'] . '\\uploads\\' . $customer_id . '\\imports\\';
	// login with username and password 
	$login_result = ftp_login($conn_id, $ftp_username, $ftp_pwd); 
	if (!$login_result) {
		exit('FTP Login Failed\r\n');
	} else {
		ftp_pasv($conn_id,true);
		//echo "FTP login succeeded<br />\r\n";
	}
	
	ftp_chdir($conn_id, "ikase");
	ftp_chdir($conn_id, "ikaseuploads");
	ftp_chdir($conn_id, $customer_id);
	ftp_chdir($conn_id, "exports");
	
	$nList = ftp_nlist($conn_id, ".");
	//die(print_r($nList));
	
	if (!is_dir($uploadDir)) {
		mkdir($uploadDir, 0755, true);
	}
	foreach($nList as $localFile) {
		//die($localFile);
		$targetFile = str_replace("./", "", $localFile);
		$remote_file = "C:\\inetpub\\wwwroot\\ikase\\ikaseuploads\\" . $customer_id . "\\exports\\" . $targetFile;
		echo $localFile." --> ".$uploadDir . $targetFile . "<br />\r\n";

		if (ftp_get($conn_id, $uploadDir . $targetFile, $localFile, FTP_BINARY)) {
			//echo "ftpd " . $targetFile . " to " .  $uploadDir . $targetFile . "<br />\r\n";
		} else {
			echo "ERROR ftpd " . $targetFile . " to " .  $uploadDir . $targetFile . "<br />\r\n";
			die();
		}
		//die("for now");
	}
	ftp_close($conn_id);
	
	$url = "https://ikase.xyz/ikase/limapi/scans_transferred.php?id=" . $queue_id;
	//echo $url . "<br />\r\n";
	$contents = file_get_contents($url);
	echo $contents;
	die();
	//die("FTP DONE");
	//echo json_encode(array("success"=>true));
}
?>		
