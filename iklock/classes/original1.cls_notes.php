 <?php
//class to manage locations
class notes
{
	var $id;
	var $arrInfo;
	var $location_id;
	var $notes ;
	var $note_type;
	var $time_stamp  ;
	var $user_name ;
	var $callback_date ;
	var $contact  ;
	var $status  ;
	var $todays;
	var $pending;
	
	//data access
	var $datalink;
	var $sortby;
	function notes () {
		return true;
	}
	function insert($location_id,$notes,$time_stamp,$USERNAME,$callback_date,$callback_time, $contact,$status, $assign_to="") {
		$notes=addslashes($notes);
		$notes = str_replace("\\" . chr(34), chr(34), $notes);
		$status =addslashes($status);
		$contact =addslashes($contact);
		//insert into new location
		if ($assign_to == "") {
			$assign_to = $USERNAME;
		}
		$time_stamp_track = date("Y-m-d G:i:s");
		if ($time_stamp=="") {
			$time_stamp = $time_stamp_track;
		}
		$query="insert into notes (location_id,notes,time_stamp,user_name,callback_date, callback_time, contact, status) 
		VALUES ('$location_id','$notes','$time_stamp','$assign_to','$callback_date', '$callback_time', '$contact','$status')";
		//echo $query . "<bR>";
		$result = MYSQL_QUERY($query, $this->datalink) or die ("Unable to add notes<br>$query<br>" . mysql_error());
		$time_stamp_track = date("Y-m-d G:i:s");
		
		//now retrieve the new notes_id
		$query="SELECT notes_id from `notes` ORDER BY notes_id desc LIMIT 0,1";
		$result = MYSQL_QUERY($query, $this->datalink);
		$row_order = mysql_fetch_row($result);
		$notes_id = $row_order[0];
		$this->id = $notes_id;
		
		$query="insert into notes_track (user_name_track, operation_track, time_stamp_track,location_id,notes_id, notes,time_stamp,user_name,callback_date, contact, status) 
		VALUES ('$USERNAME','insert','$time_stamp_track','$location_id','$notes_id','$notes','$time_stamp','$USERNAME','$callback_date', '$contact','$status')";
		
		$result = MYSQL_QUERY($query, $this->datalink) or die ("Unable to add notes track<br>$query<br>" . mysql_error());
		//let keep track of status
		$max_callback_id = $notes_id;
		$actual_callback_date = "";
		if (strlen($callback_date)!="10") {
			$callback_date = "";
			$max_callback_id = 0;
		} else {
			//let's get a real date going
			$arrDate = explode("/", $callback_date);
			$actual_callback_date = $arrDate[2] . "-" . $arrDate[0] . "-" . $arrDate[1];
		}
		//first check whether or not there is already an entry in notes_status
		$query ="SELECT notes_status_id from notes_status WHERE location_id = '$location_id'";
		$resultstat = MYSQL_QUERY($query, $this->datalink) or die ("Unable to get notes status<br>$query<br>" . mysql_error());
		$numberstat = mysql_numrows($resultstat);

		if ($numberstat == 0 ) {
			$query="insert into notes_status (`location_id`, `max_notes_id`, `max_callback_id`, `user_name`, `callback_date`, `actual_callback_date`) 
			VALUES ('$location_id', '$notes_id', '$max_callback_id', 
			'$assign_to', '$callback_date', '$actual_callback_date')";
			//echo $query . "<bR>";
			$result = MYSQL_QUERY($query, $this->datalink) or die ("Unable to add notes status<br>$query<br>" . mysql_error());
		} else {
			$query="update notes_status set `max_notes_id` = '$notes_id', 
			`max_callback_id` = '$max_callback_id', 
			`user_name` =  '$assign_to', 
			`callback_date` = '$callback_date', 
			`actual_callback_date` = '$actual_callback_date'
			WHERE location_id = '$location_id'";
			//echo $query . "<bR>";
			$result = MYSQL_QUERY($query, $this->datalink) or die ("Unable to update notes status<br>$query<br>" . mysql_error());
		}
		$querystatus = "delete from `notes_lastentry` WHERE location_id = '$location_id'";
		$resultstatus = MYSQL_QUERY($querystatus, $this->datalink) or die($querystatus . "<br>" . mysql_error());
		
		$querystatus = "insert into `notes_lastentry` (`location_id`,`lastentry`, `lastnote`)
SELECT notes.location_id, max( time_stamp ) lastentry, '' lastnote
		FROM notes_track notes
		WHERE notes.location_id = '$location_id'
		GROUP BY notes.location_id";
		$resultstatus = MYSQL_QUERY($querystatus, $this->datalink) or die($querystatus . "<br>" . mysql_error());
		//now add the last user to enter notes on that location
		$querystatus = "update notes_lastentry nlast, notes_track ntrack
		set nlast.user_name = ntrack.user_name,
		nlast.lastnote = ntrack.notes
		WHERE ntrack.location_id = nlast.location_id AND cast( ntrack.time_stamp AS DATETIME ) = nlast.lastentry
		and ntrack.location_id = '$location_id'";
		$resultstatus = MYSQL_QUERY($querystatus, $this->datalink) or die($querystatus . "<br>" . mysql_error());

	}
	function insert_ready($notes_id) {
		$querystatus = "INSERT INTO `notes_rtc` ( `notes_id` , `location_id` , `notes` , 
			`time_stamp` , `actual_timestamp` , `user_name` , `callback_date` , `callback_time` , `contact` , `status` )
		SELECT notes .`notes_id`, notes.location_id, `notes`.`notes`, `notes`.`time_stamp` , CAST(notes.`time_stamp` AS DATE) , notes.`user_name` , notes.`callback_date` , notes.`callback_time` , notes.`contact` , notes.`status`
		FROM `notes`
		INNER JOIN location ON notes.location_id = location.location_id
		WHERE notes.notes_id = '" . $notes_id . "'";
		//echo $querystatus;
		$result= MYSQL_QUERY($querystatus, $this->datalink) or die($querystatus . "<br>" . mysql_error());
		//update the rtc table
		$querystatus = "UPDATE `notes_rtc` set RTC = 'Y' WHERE notes like '%RTC:Y%' AND notes_id ='" . $notes_id . "'";
		$result= MYSQL_QUERY($querystatus, $this->datalink) or die($querystatus . "<br>" . mysql_error());
		
		//$querystatus = "UPDATE `notes_rtc` set RTC = 'N' WHERE notes like '%RTC:N%' AND notes_id ='" . $notes_id . "'";
		//$result= MYSQL_QUERY($querystatus, $this->datalink) or die($querystatus . "<br>" . mysql_error());
		
		$querystatus = "UPDATE `notes_rtc` set RTP = 'Y' WHERE notes like '%RTP:Y%' AND notes_id ='" . $notes_id  . "'";
		$result= MYSQL_QUERY($querystatus, $this->datalink) or die($querystatus . "<br>" . mysql_error());
		
		//$querystatus = "UPDATE `notes_rtc` set RTP = 'N' WHERE notes like '%RTP:N%' AND notes_id ='" . $notes_id  . "'";
		//$result= MYSQL_QUERY($querystatus, $this->datalink) or die($querystatus . "<br>" . mysql_error());
		
		$querystatus = "UPDATE `notes_rtc` set WCIRB = 'Y' WHERE notes like '%WCIRB:Y%' AND notes_id ='" . $notes_id  . "'";
		$result= MYSQL_QUERY($querystatus, $this->datalink) or die($querystatus . "<br>" . mysql_error());
		
		//$querystatus = "UPDATE `notes_rtc` set WCIRB = 'N' WHERE notes like '%WCIRB:N%' AND notes_id ='" . $notes_id  . "'";
		//$result= MYSQL_QUERY($querystatus, $this->datalink) or die($querystatus . "<br>" . mysql_error());
		
		$querystatus = "UPDATE `notes_rtc` set BILL_OUT = 'Y' WHERE notes like '%BILL_OUT:Y%' AND notes_id ='" . $notes_id  . "'";
		$result= MYSQL_QUERY($querystatus, $this->datalink) or die($querystatus . "<br>" . mysql_error());
		
		//$querystatus = "UPDATE `notes_rtc` set BILL_OUT = 'N' WHERE notes like '%BILL_OUT:N%' AND notes_id ='" . $notes_id  . "'";
		//$result= MYSQL_QUERY($querystatus, $this->datalink) or die($querystatus . "<br>" . mysql_error());
			
		$querystatus = "UPDATE `notes_rtc`  SET SCAN = 'Y' WHERE notes LIKE '%SCAN:Y%' AND notes_id ='" . $notes_id  . "'";
		$result= MYSQL_QUERY($querystatus, $this->datalink) or die($querystatus . "<br>" . mysql_error());
		
		$querystatus = "UPDATE `notes_rtc` SET SCAN = 'N' WHERE notes LIKE '%SCAN:N%' AND notes_id ='" . $notes_id  . "'";
		$result= MYSQL_QUERY($querystatus, $this->datalink) or die($querystatus . "<br>" . mysql_error());
			
		$querystatus = "UPDATE `notes_rtc`  SET SCAN = 'Y' WHERE notes LIKE '%SCAN:via e-mail%' AND notes_id ='" . $notes_id  . "'";
		$result= MYSQL_QUERY($querystatus, $this->datalink) or die($querystatus . "<br>" . mysql_error());
		$querystatus = "UPDATE `notes_rtc`  SET SCAN = 'Y' WHERE notes LIKE '%SCAN:via mail%' AND notes_id ='" . $notes_id  . "'";
		$result= MYSQL_QUERY($querystatus, $this->datalink) or die($querystatus . "<br>" . mysql_error());
		
		$querystatus = "UPDATE `notes_rtc`  SET SCAN = 'Y' WHERE notes LIKE '%SCAN:via fax%' AND notes_id ='" . $notes_id  . "'";
		$result= MYSQL_QUERY($querystatus, $this->datalink) or die($querystatus . "<br>" . mysql_error());
		
		//$querystatus = "UPDATE `notes_rtc`  SET SCAN = 'N' WHERE SCAN = '' AND notes_id ='" . $notes_id  . "'";
		//$result= MYSQL_QUERY($querystatus, $this->datalink) or die($querystatus . "<br>" . mysql_error());
	}
	function insert_pickup_cnr($notes_id) {
		$querystatus = "INSERT INTO `notes_rtc` ( `notes_id` , `location_id` , `notes` , 
			`time_stamp` , `actual_timestamp` , `user_name` , `callback_date` , `callback_time` , `contact` , `status` )
		SELECT notes .`notes_id`, notes.location_id, `notes`.`notes`, `notes`.`time_stamp` , CAST(notes.`time_stamp` AS DATE) , notes.`user_name` , notes.`callback_date` , notes.`callback_time` , notes.`contact` , notes.`status`
		FROM `notes`
		INNER JOIN location ON notes.location_id = location.location_id
		WHERE notes.notes_id = '" . $notes_id . "'";
		//echo $querystatus;
		$result= MYSQL_QUERY($querystatus, $this->datalink) or die($querystatus . "<br>" . mysql_error());
		//update the rtc table
		$querystatus = "UPDATE `notes_rtc` set PICKUP_CNR = 'Y' WHERE notes like '%PICKUP_CNR:Y%' AND notes_id ='" . $notes_id . "'";
		$result= MYSQL_QUERY($querystatus, $this->datalink) or die($querystatus . "<br>" . mysql_error());
	}
	function clear_ready($location_id) {
		$querystatus = "DELETE FROM `notes_rtc` 
		WHERE notes_rtc.location_id = '" . $location_id . "'";
		//echo $querystatus . "<BR>";
		$result= MYSQL_QUERY($querystatus, $this->datalink) or die($querystatus . "<br>" . mysql_error());
	}
	function insert_order_note($order_id,$notes,$time_stamp,$USERNAME,$callback_date,$callback_time, $contact,$status, $assign_to="") {
		//insert into new location
		if ($assign_to == "") {
			$assign_to = $USERNAME;
		}
		$notes=addslashes($notes);
		//insert into new location
		$query="insert into order_notes (order_id,notes,time_stamp,user_name,callback_date, contact, status) 
		VALUES ('$order_id','$notes','$time_stamp','$assign_to','$callback_date', '$contact','$status')";
//		echo $query . "<BR>";
		$result = MYSQL_QUERY($query, $this->datalink) or die ("Unable to add order notes");
		
		//now retrieve the new invoice_id
		$query="SELECT notes_id from `order_notes` ORDER BY notes_id desc LIMIT 0,1";
		$result = MYSQL_QUERY($query, $this->datalink);
		$row_order = mysql_fetch_row($result);
		$notes_id = $row_order[0];
		$this->id = $notes_id;
		$query="insert into order_notes_track (user_name_track, operation_track, time_stamp_track,order_id,notes_id, notes,time_stamp,user_name,callback_date, contact, status) 
		VALUES ('$USERNAME','insert','$time_stamp_track','$order_id','notes_id','$notes','$time_stamp','$USERNAME','$callback_date', '$contact','$status')";
		//$result = MYSQL_QUERY($query, $this->datalink) or die ("Unable to add notes track<br>" . mysql_error());
	}
	function insert_secondset_note($order_id,$notes,$time_stamp,$USERNAME,$callback_date,$callback_time, $contact,$status, $assign_to="") {
		//insert into new location
		if ($assign_to == "") {
			$assign_to = $USERNAME;
		}
		$notes=addslashes($notes);
		//insert into new location
		$query="insert into secondset_notes (order_id,notes,time_stamp,user_name,callback_date, contact, status) 
		VALUES ('$order_id','$notes','$time_stamp','$assign_to','$callback_date', '$contact','$status')";
//		echo $query . "<BR>";
		$result = MYSQL_QUERY($query, $this->datalink) or die ("Unable to add order notes");
		
		//now retrieve the new invoice_id
		$query="SELECT notes_id from `secondset_notes` ORDER BY notes_id desc LIMIT 0,1";
		$result = MYSQL_QUERY($query, $this->datalink);
		$row_order = mysql_fetch_row($result);
		$notes_id = $row_order[0];
		$this->id = $notes_id;
		$query="insert into secondset_notes_track (user_name_track, operation_track, time_stamp_track,order_id,notes_id, notes,time_stamp,user_name,callback_date, contact, status) 
		VALUES ('$USERNAME','insert','$time_stamp_track','$order_id','notes_id','$notes','$time_stamp','$USERNAME','$callback_date', '$contact','$status')";
		//$result = MYSQL_QUERY($query, $this->datalink) or die ("Unable to add notes track<br>" . mysql_error());
	}
	function insert_billing_note($order_id,$notes,$time_stamp,
		$USERNAME,$callback_date, $contact, $status, $assign_to="") {
		//insert into new location
		if ($assign_to == "") {
			$assign_to = $USERNAME;
		}
		$notes=addslashes($notes);
		//insert into new order
		$query="insert into billing_notes (order_id,notes,time_stamp,user_name,callback_date, contact, status) 
VALUES ('$order_id','$notes','$time_stamp','$assign_to','$callback_date', '$contact','$status')";
//		echo $query . "<BR>";
		$result = MYSQL_QUERY($query, $this->datalink) or die ("Unable to add BILLING notes");
		//now retrieve the new invoice_id
		$query="SELECT notes_id from `billing_notes` ORDER BY notes_id desc LIMIT 0,1";
		$result = MYSQL_QUERY($query, $this->datalink);
		$row_order = mysql_fetch_row($result);
		$notes_id = $row_order[0];
		return $notes_id;
	}
	function insert_hour_note($login_id,$notes,$time_stamp,$USERNAME, $note_type) {
		//insert into new location
		$notes=addslashes($notes);
		//insert into new location
		$query="insert into hour_notes (login_id,notes,note_type,time_stamp,user_name) 
		VALUES ('$login_id','$notes','$note_type','$time_stamp','$USERNAME')";
		$result = MYSQL_QUERY($query, $this->datalink) or die ("Unable to add hour notes<br>" . mysql_error());
		
		//now retrieve the new invoice_id
		$query="SELECT notes_id from `hour_notes` ORDER BY notes_id desc LIMIT 0,1";
		$result = MYSQL_QUERY($query, $this->datalink);
		$row_order = mysql_fetch_row($result);
		$notes_id = $row_order[0];
		
		$query="insert into hour_notes_track (user_name_track, operation_track, time_stamp_track,login_id,notes_id, notes,note_type,time_stamp,user_name) 
		VALUES ('$USERNAME','insert','$time_stamp_track','$login_id', '$notes_id','$notes','$note_type','$time_stamp','$USERNAME')";
		//$result = MYSQL_QUERY($query, $this->datalink) or die ("Unable to add notes track<br>" . mysql_error());
	}
	function insert_user_note($notes, $time_stamp, $from_user_id, $to_user_id, $from_user_name, $to_user_name, $note_type = "standard", $subject = "") {
		$notes=addslashes($notes);
		$subject=addslashes($subject);
		//insert into new location
		$query="insert into user_notes (`subject`, `notes`, `time_stamp`, `from_user_id`, `to_user_id`, `from_user_name`, `to_user_name`, `note_type`) 
		VALUES ('$subject', '$notes', '$time_stamp', '$from_user_id', '$to_user_id', '$from_user_name', '$to_user_name', '$note_type')";
		//echo $query . "<BR>";
		/*
		$result = MYSQL_QUERY($query, $this->datalink) or die ("Unable to add USER notes<BR>" . $query . "<BR>" . mysql_error());
		
		//now retrieve the new invoice_id
		$query="SELECT notes_id from `user_notes` ORDER BY notes_id desc LIMIT 0,1";
		$result = MYSQL_QUERY($query, $this->datalink);
		$row_order = mysql_fetch_row($result);
		$notes_id = $row_order[0];
		*/
		try {
			$sql = $query;
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			
			$notes_id = $db->lastInsertId();
			$stmt->closeCursor(); $stmt = null; $db = null;
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		
		$this->id = $notes_id;
		return true;
	}
	function insert_company_note($company_id,$notes,$time_stamp,$USERNAME,$callback_date,$callback_time, $contact,$status) {
		$notes=addslashes($notes);
		//insert into new location
		$query="insert into company_notes (company_id,notes,time_stamp,user_name,callback_date, contact, status) 
		VALUES ('$company_id','$notes','$time_stamp','$USERNAME','$callback_date', '$contact','$status')";
		$result = MYSQL_QUERY($query, $this->datalink) or die ("Unable to add company notes");
		
		//now retrieve the new invoice_id
		$query="SELECT notes_id from `company_notes` ORDER BY notes_id desc LIMIT 0,1";
		$result = MYSQL_QUERY($query, $this->datalink);
		$row_order = mysql_fetch_row($result);
		$notes_id = $row_order[0];
		
		$query="insert into company_notes_track (user_name_track, operation_track, time_stamp_track,company_id,notes_id, notes,time_stamp,user_name,callback_date, contact, status) 
		VALUES ('$USERNAME','insert','$time_stamp_track','$company_id','notes_id','$notes','$time_stamp','$USERNAME','$callback_date', '$contact','$status')";
		//$result = MYSQL_QUERY($query, $this->datalink) or die ("Unable to add notes track<br>" . mysql_error());
	}
	function insert_newcompany_note($newcompany_id,$notes,$time_stamp,$USERNAME,$callback_date,$callback_time, $contact,$status) {
		$notes=addslashes($notes);
		//insert into new location
		$query="insert into newcompany_notes (newcompany_id,notes,time_stamp,user_name,callback_date, contact, status) 
		VALUES ('$newcompany_id','$notes','$time_stamp','$USERNAME','$callback_date', '$contact','$status')";
		$result = MYSQL_QUERY($query, $this->datalink) or die ("Unable to add newcompany notes");
		
		//now retrieve the new invoice_id
		$query="SELECT notes_id from `newcompany_notes` ORDER BY notes_id desc LIMIT 0,1";
		$result = MYSQL_QUERY($query, $this->datalink);
		$row_order = mysql_fetch_row($result);
		$notes_id = $row_order[0];
		
		$query="insert into newcompany_notes_track (user_name_track, operation_track, time_stamp_track,newcompany_id,notes_id, notes,time_stamp,user_name,callback_date, contact, status) 
		VALUES ('$USERNAME','insert','$time_stamp','$newcompany_id','$notes_id','$notes','$time_stamp','$USERNAME','$callback_date', '$contact','$status')";
		$result = MYSQL_QUERY($query, $this->datalink) or die ("Unable to add notes track<br>$query" . mysql_error());
	}
	function insert_prospects_note($company_id,$notes,$time_stamp,$USERNAME,$callback_date,$callback_time, $contact,$status) {
		$notes=addslashes($notes);
		//insert into new location
		$query="insert into prospects_notes (company_id,notes,time_stamp,user_name,callback_date, contact, status) 
		VALUES ('$company_id','$notes','$time_stamp','$USERNAME','$callback_date', '$contact','$status')";
		$result = MYSQL_QUERY($query, $this->datalink) or die ("Unable to add company notes");
		
		//now retrieve the new invoice_id
		$query="SELECT notes_id from `prospects_notes` ORDER BY notes_id desc LIMIT 0,1";
		$result = MYSQL_QUERY($query, $this->datalink);
		$row_order = mysql_fetch_row($result);
		$notes_id = $row_order[0];
		
		$query="insert into prospects_notes_track (user_name_track, operation_track, time_stamp_track,company_id,notes_id, notes,time_stamp,user_name,callback_date, contact, status) 
		VALUES ('$USERNAME','insert','$time_stamp_track','$company_id','notes_id','$notes','$time_stamp','$USERNAME','$callback_date', '$contact','$status')";
		//$result = MYSQL_QUERY($query, $this->datalink) or die ("Unable to add notes track<br>" . mysql_error());
	}
	function rtc_report($blnUsers = false, $user_name = "", $start_date = "", $end_date = "") {
		if ($start_date!="") {
			$whereClause = " AND (`actual_timestamp` > '" . $start_date . "' AND `actual_timestamp` < '" . $end_date . "')";
		}
		if ($blnUsers == false) {
			$statusquery = "SELECT user_name, `actual_timestamp` thedate, count( notes_id ) note_count
			FROM `notes_rtc`
			WHERE ( RTC = 'Y' OR RTP = 'Y' OR SCAN = 'Y' )";	
			$statusquery .= $whereClause . " GROUP BY `actual_timestamp`, user_name";
		} else {
			$statusquery = "SELECT user_name, count( notes_id ) note_count
			FROM `notes_rtc`
			WHERE (RTC = 'Y'
			OR RTP = 'Y'
			OR SCAN = 'Y')";
			if ($user_name !="") {
				$statusquery .= " WHERE user_name = '" . $user_name . "'";
			}
			$statusquery .= $whereClause . " GROUP BY user_name
			ORDER BY user_name";
		}
		
		//echo $statusquery . "<br>";
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve info<br>$statusquery<br>" . mysql_error());
		//just give them back the notes
		return $resultstat;
	}
	function week_report($blnUsers = false, $user_name = "", $start_date = "", $end_date = "") {
		if ($start_date!="") {
			$whereClause = " AND (`actual_timestamp` > '" . $start_date . "' AND `actual_timestamp` < '" . $end_date . "')";
		}
		if ($blnUsers == false) {
			$statusquery = "SELECT DISTINCT user_name, `actual_timestamp` thedate, count( notes_id ) note_count
			FROM `notes_rtc`
			WHERE ( RTC = 'Y' OR RTP = 'Y' OR SCAN = 'Y' OR BILL_OUT = 'Y' OR WCIRB = 'Y' )";	
		} else {
			$statusquery = "SELECT DISTINCT user_name, `actual_timestamp` thedate, count( notes_id ) note_count
			FROM `notes_rtc`
			WHERE (RTC = 'Y'
			OR RTP = 'Y'
			OR SCAN = 'Y' OR BILL_OUT = 'Y' OR WCIRB = 'Y')";
			if ($user_name !="") {
				$statusquery .= " AND user_name = '" . $user_name . "'";
			}
		}
		$statusquery .= $whereClause . " GROUP BY `actual_timestamp`, user_name
		ORDER BY ";
		if ($user_name =="") {
			$statusquery .= " user_name asc, ";
		}
		$statusquery .= " `actual_timestamp` asc";
		//echo $statusquery . "<br>";
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve info<br>$statusquery<br>" . mysql_error());
		//just give them back the notes
		return $resultstat;
	}
	function week_report_users() {
		$statusquery = "SELECT DISTINCT user.user_id, user.user_logon as user_name
			FROM `user`
			left outer JOIN `notes_rtc` ON `user`.user_logon = `notes_rtc`.user_name
			WHERE  `user`.call_center = 'Y' OR user.user_groups LIKE '%scheduling%'
			ORDER BY `user`.user_logon ASC";
			/*
			(
			notes_rtc.RTC = 'Y'
			OR notes_rtc.RTP = 'Y'
			OR notes_rtc.SCAN = 'Y'
			)
			AND
			*/
		//echo $statusquery . "<br>";
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve users<br>$statusquery<br>" . mysql_error());
		//just give them back the notes
		return $resultstat;
	}
	function check_report_users($user_name) {
		//default
		$result = "false";
		$statusquery = "SELECT DISTINCT user.user_logon as user_name
			FROM `user`
			left outer JOIN `notes_rtc` ON `user`.user_logon = `notes_rtc`.user_name
			WHERE  `user`.call_center = 'Y'
			and `user`.user_logon = '" . $user_name . "'";
			
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve users<br>$statusquery<br>" . mysql_error());
		$numberstat = mysql_numrows($resultstat);
		//based on the number
		if ($numberstat>0) {
			$result = "true";
		}
		return $result;
	}
	function callbacks($start_date, $end_date, $USERNAME, $user_id="") {
		$statusquery = "SELECT 	notes_status.`location_id`, location.order_number, 
		location.location_number, location.assigned_date, notes_status.`max_notes_id`, 
		notes_status.`max_callback_id`, notes_status.`user_name` , 
		notes_status.`callback_date`, ord.order_id, 
		ord.assigned_date ord_assigned_date, 
		ord.requiredby_date, notes.notes_id, notes.time_stamp,
		notes.user_name, notes.callback_date, IF (
notes.callback_time = '00:00:00', '', notes.callback_time
) callback_time, notes.notes as notesnotes, stc.status_number statnumb
		FROM `notes_status`
		inner join `notes` notes
		on notes_status.max_notes_id = notes.notes_id
		inner join location
		on notes_status.location_id = location.location_id
		inner join `orders` ord 
		on location.order_id = ord.order_id
		inner join status_current stc
		on location.location_id = stc.location_id
		INNER JOIN user ON notes_status.user_name = user.user_logon
		WHERE `max_notes_id` = `max_callback_id`";
		if ($start_date!="") { 
			$statusquery .= " AND `actual_callback_date` > '" . $start_date . "'";
		}
		$statusquery .= " AND `actual_callback_date` < '" . $end_date . "'";
		if ($USERNAME!="" && $user_id=="") {
			$statusquery .= " AND user.`user_logon` = '" . $USERNAME . "'";
		}
		if ($user_id!="") {
			$statusquery .= " AND user.`user_id` = '" . $user_id . "'";
		}
		$statusquery .= " ORDER BY notes_status.actual_callback_date";
		//ifnull(location.cancelled,'N') <> 'Y' and 
		//echo $statusquery . "<Br>";
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve info<br>" . mysql_error());
		//just give them back the notes
		return $resultstat;
	}
	function callback_employee_summary($start_date, $end_date, $USERNAME) {
		$statusquery = "SELECT MONTH( location.actual_assigned_date ) assigned_month, YEAR( location.actual_assigned_date ) assigned_year, count( notes_status.location_id ) location_count
		FROM `notes_status_employee` notes_status
		INNER JOIN `notes` notes ON notes_status.max_notes_id = notes.notes_id
		INNER JOIN location ON notes_status.location_id = location.location_id
		INNER JOIN status_current stc ON location.location_id = stc.location_id
		INNER JOIN `orders` ord ON location.order_id = ord.order_id
		WHERE `max_notes_id` = `max_callback_id`";
		$statusquery .= " AND stc.status_number < 6";
		if ($USERNAME!="") {
			$statusquery .= " AND notes_status.`user_name` = '" . $USERNAME . "'";
		}
		if ($start_date!="") { 
			$statusquery .= " AND notes_status.`actual_callback_date` >= '" . $start_date . "'";
		}
		$statusquery .= " AND notes_status.`actual_callback_date` < '" . $end_date . "'";
		$statusquery .= " GROUP BY MONTH( location.actual_assigned_date ) , YEAR( location.actual_assigned_date )
		ORDER BY YEAR( location.actual_assigned_date ) , MONTH( location.actual_assigned_date )";
		//echo $statusquery . "<BR>";
		$result = mysql_query($statusquery, $this->datalink) or die("unable to get callback summary<br>" . mysql_error());
		return $result;
	}
	function callback_employee_week_summary($start_date, $end_date, $start_location, $end_location, $USERNAME) {
		$statusquery = "SELECT count( notes_status.location_id ) location_count
		FROM `notes_status_employee` notes_status
		INNER JOIN `notes` notes ON notes_status.max_notes_id = notes.notes_id
		INNER JOIN location ON notes_status.location_id = location.location_id
		INNER JOIN status_current stc ON location.location_id = stc.location_id
		INNER JOIN `orders` ord ON location.order_id = ord.order_id
		WHERE `max_notes_id` = `max_callback_id`";
		$statusquery .= " AND stc.status_number < 6";
		if ($USERNAME!="") {
			$statusquery .= " AND notes_status.`user_name` = '" . $USERNAME . "'";
		}
		if ($start_date!="") { 
			$statusquery .= " AND notes_status.`actual_callback_date` >= '" . $start_date . "'";
		}
		$statusquery .= " AND notes_status.`actual_callback_date` < '" . $end_date . "'";
		if ($start_location!="") { 
			$statusquery .= " AND location.actual_assigned_date >= '" . $start_location . "'";
		}
		if ($end_location!="") { 
			$statusquery .= " AND location.actual_assigned_date < '" . $end_location . "'";
		}
		$statusquery .= " ORDER BY YEAR( location.actual_assigned_date ) DESC, MONTH( location.actual_assigned_date ) DESC, WEEK( location.actual_assigned_date ) DESC";
		//echo $statusquery . "<BR>";
		$result = mysql_query($statusquery, $this->datalink) or die("unable to get callback summary<br>" . mysql_error());
		return $result;
	}
	function callbacks_employee($start_date, $end_date, $USERNAME, $user_id="", $sortby = "", $records_type = "") {
		$statusquery = "SELECT distinct notes_status.`location_id`, location.order_number, 
		location.location_number, location.assigned_date, notes_status.`max_notes_id`, 
		notes_status.`max_callback_id`, notes_status.`user_name` , 
		notes_status.`callback_date`, ord.order_id, 
		ord.assigned_date ord_assigned_date, ord.rush,
		ord.requiredby_date, notes.notes_id, notes.time_stamp,
		notes.user_name, notes.callback_date, IF (
notes.callback_time = '00:00:00', '', notes.callback_time
) callback_time, notes.notes as notesnotes, stc.status_id, stc.status_number statnumb, facility.verified_by, facility.zip facilityzip
		FROM `notes_status_employee` notes_status
		INNER JOIN `notes` notes
		on (notes_status.max_notes_id = notes.notes_id)
		INNER JOIN location
		on notes_status.location_id = location.location_id
		INNER JOIN `orders` ord 
		on location.order_id = ord.order_id
		INNER JOIN status_current stc
		on location.location_id = stc.location_id
		INNER JOIN user ON notes_status.user_name = user.user_logon
		INNER JOIN `companies` facility on
		location.facility_id = facility.company_id";
		
		if ($records_type!="") {
			$statusquery .= " INNER JOIN `records`
			ON location.records_id = records.records_id";
		}
		$statusquery .= " WHERE `max_notes_id` = `max_callback_id`
		AND location.cancelled != 'Y'";
		if ($start_date!="") { 
			$statusquery .= " AND `actual_callback_date` > '" . $start_date . "'";
		}
		$statusquery .= " AND `actual_callback_date` < '" . $end_date . "'";
		if ($USERNAME!="" && $user_id=="") {
			$statusquery .= " AND notes_status.`user_name` = '" . $USERNAME . "'";
		}
		if ($user_id!="") {
			$statusquery .= " AND user.`user_id` = '" . $user_id . "'";
		}
		if ($records_type!="") {
			$statusquery .= " AND records.`records_type` = '" . $records_type . "'";
		}
		if ($this->pending=="Y") {
			$statusquery .= " AND notes.notes LIKE '%Pending%'";		
			$statusquery .= " AND stc.status_number = 5";
		} else {
			//status billed is not allowed
			$statusquery .= " AND stc.status_number < 6";
		}
		if ($sortby=="") {
			$statusquery .= " ORDER BY IFNULL(ord.rush, 'N') DESC, IF (facility.zip > '93199', 0, 1) ASC, ord.order_id, location.location_id, notes_status.actual_callback_date";
		} else {
			$statusquery .= " ORDER BY " . $sortby;
		}
		//$statusquery .= " ORDER BY notes_status.actual_callback_date";
		//ifnull(location.cancelled,'N') <> 'Y' and 
		//echo $statusquery . "<Br>";
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve info<br>" . mysql_error());
		//just give them back the notes
		return $resultstat;
	}
	function callbacks_bydate($start_date, $end_date, $USERNAME, $user_id="", $sortby = "", $records_type = "", $week, $year, $start_assigned_date, $end_assigned_date) {
		$statusquery = "SELECT distinct notes_status.`location_id`, location.order_number, 
		location.location_number, location.actual_assigned_date assigned_date, notes_status.`max_notes_id`, 
		notes_status.`max_callback_id`, notes_status.`user_name` , 
		notes_status.`callback_date`, ord.order_id, 
		ord.assigned_date ord_assigned_date, ord.rush,
		ord.requiredby_date, notes.notes_id, notes.time_stamp,
		notes.user_name, notes.callback_date, IF (
notes.callback_time = '00:00:00', '', notes.callback_time
) callback_time, notes.notes as notesnotes, stc.status_id, stc.status_number statnumb, facility.verified_by, facility.zip facilityzip
		FROM `notes_status_employee` notes_status
		inner join `notes` notes
		on (notes_status.max_notes_id = notes.notes_id
		and notes_status.user_name = notes.user_name)
		inner join location
		on notes_status.location_id = location.location_id
		inner join `orders` ord 
		on location.order_id = ord.order_id
		inner join status_current stc
		on location.location_id = stc.location_id
		INNER JOIN user ON notes_status.user_name = user.user_logon
		left outer join `companies` facility on
		location.facility_id = facility.company_id";
		if ($records_type!="") {
			$statusquery .= " INNER JOIN `records`
			ON location.records_id = records.records_id";
		}
		$statusquery .= " WHERE `max_notes_id` = `max_callback_id`";
		
		if ($start_assigned_date!="") {
			$statusquery .= " AND location.actual_assigned_date >= '" . $start_assigned_date . "'";
		}
		
		if ($end_assigned_date!="") {
			$statusquery .= " AND location.actual_assigned_date < '" . $end_assigned_date . "'";
		}
		$statusquery .= " AND `location`.`actual_assigned_date` < '" . $end_date . "'";
		if ($USERNAME!="" && $user_id=="") {
			$statusquery .= " AND user.`user_logon` = '" . $USERNAME . "'";
		}
		if ($user_id!="") {
			$statusquery .= " AND user.`user_id` = '" . $user_id . "'";
		}
		if ($records_type!="") {
			$statusquery .= " AND records.`records_type` = '" . $records_type . "'";
		}
		//status billed is not allowed
		$statusquery .= " AND stc.status_number < 6";
		if ($sortby=="") {
			$statusquery .= " ORDER BY ord.order_id, location.location_id, notes_status.actual_callback_date";
		} else {
			$statusquery .= " ORDER BY " . $sortby;
		}
		//$statusquery .= " ORDER BY notes_status.actual_callback_date";
		//ifnull(location.cancelled,'N') <> 'Y' and 
		//echo $statusquery . "<Br>";
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve info<br>" . mysql_error());
		//just give them back the notes
		return $resultstat;
	}
	function nocallbacks($sortby = "") {
		$lastmonth = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-30, date("Y")));
		$statusquery = "SELECT location.*, scurr.status_number, 
		orders.requiredby_date, 
		attorney.company_id as attorney_id,
		attorney.company as firm,
		attorney.company_person as attorney,
		attorney.contact as attorneycontact,
		attorney.bar_number as bar_number,
		attorney.street as attorneystreet,
		attorney.city as attorneycity, 
		attorney.state as attorneystate, attorney.zip as attorneyzip,
		attorney.phone as attorneyphone, attorney.fax as attorneyfax
		FROM location
		inner join orders
		on location.order_id = orders.order_id
		inner join `companies` attorney on
		orders.attorney_id = attorney.company_id
		inner join status_current scurr
		on location.location_id = scurr.location_id
		LEFT OUTER JOIN notes ON location.location_id = notes.location_id
		WHERE notes.location_id IS NULL
		and ifnull(location.cancelled,'N') <> 'Y'
		and cast(CONCAT( SUBSTRING_INDEX( location.assigned_date, '/', -
		1) , '/', SUBSTRING_INDEX( location.assigned_date, '/', 
		1), '/', SUBSTRING_INDEX(SUBSTRING_INDEX(location.assigned_date, '/',2), '/', -1)) as DATE) <  '" . $lastmonth . "'";
		$statusquery .= " AND scurr.status_number < 6 ";
		if ($sortby=="") {
			$statusquery .= " ORDER BY orders.order_id, `location`.`location_number`";
		} else {
			$statusquery .= " ORDER BY " . $sortby;
		}
		//ifnull(location.cancelled,'N') <> 'Y' and 
		//echo $statusquery . "<Br>";
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve info<br>" . mysql_error());
		//just give them back the notes
		return $resultstat;
	}
	function callbacks_users($start_date, $end_date) {
		$statusquery = "SELECT 	distinct notes_status.`user_name`
		FROM `notes_status`
		inner join `notes` notes
		on notes_status.max_notes_id = notes.notes_id
		inner join location
		on notes_status.location_id = location.location_id
		inner join `orders` ord 
		on location.order_id = ord.order_id
		WHERE `max_notes_id` = `max_callback_id`";
		if ($start_date!="") { 
			$statusquery .= " AND `actual_callback_date` > '" . $start_date . "'";
		}
		$statusquery .= " AND `actual_callback_date` < '" . $end_date . "'";
		//$statusquery .= " ORDER BY notes_status.actual_callback_date, callback_time";
		//ifnull(location.cancelled,'N') <> 'Y' and 
		//echo $statusquery . "<Br>";
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve info<br>" . mysql_error());
		//just give them back the notes
		return $resultstat;
	}
	function callbacks_count($start_date, $end_date, $USERNAME) {
		$statusquery = "SELECT count(notes_status.location_id) location_count
		FROM `notes_status`
		inner join `notes` notes
		on notes_status.max_notes_id = notes.notes_id
		inner join location
		on notes_status.location_id = location.location_id
		inner join `orders` ord 
		on location.order_id = ord.order_id
		WHERE `max_notes_id` = `max_callback_id`";
		if ($start_date!="") { 
			$statusquery .= " AND `actual_callback_date` > '" . $start_date . "'";
		}
		$statusquery .= " AND `actual_callback_date` < '" . $end_date . "'";
		if ($USERNAME!="") {
			$statusquery .= " AND notes_status.`user_name` = '" . $USERNAME . "'";
//			
		}
		//ifnull(location.cancelled,'N') <> 'Y' and
		//echo $statusquery . "<Br><Br>";
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve info<br>" . mysql_error());
		$numberstat = mysql_Numrows($resultstat);
		if ($numberstat >0) {
			return mysql_result($resultstat, 0, "location_count");
		} else {
			return 0;
		}
	}
	function callbacks_list($start_date, $end_date, $USERNAME = "", $location_id = "") {
		$statusquery = "SELECT DISTINCT notes_status.location_id
		FROM `notes_status_employee` notes_status
		inner join `notes` notes
		on notes_status.max_notes_id = notes.notes_id
		inner join location
		on notes_status.location_id = location.location_id
		inner join `orders` ord 
		on location.order_id = ord.order_id
		WHERE `max_notes_id` = `max_callback_id`";
		if ($start_date!="") { 
			$statusquery .= " AND `actual_callback_date` > '" . $start_date . "'";
		}
		$statusquery .= " AND `actual_callback_date` < '" . $end_date . "'";
		if ($USERNAME!="") {
			$statusquery .= " AND notes_status.`user_name` = '" . $USERNAME . "'";
		}
		if ($location_id!="") {
			$statusquery .= " AND notes_status.`location_id` = '" . $location_id . "'";
			
		}
		//ifnull(location.cancelled,'N') <> 'Y' and
//		echo $statusquery . "<Br><Br>";
//		die("nick");
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve callback list<br>" . mysql_error());
		$numberstat = mysql_Numrows($resultstat);
		return $numberstat;
	}
	function callbacks_employee_count($start_date, $end_date, $USERNAME) {
		$statusquery = "SELECT count(notes_status.location_id) location_count
		FROM `notes_status_employee` notes_status
		inner join `notes` notes
		on notes_status.max_notes_id = notes.notes_id
		inner join location
		on notes_status.location_id = location.location_id
		inner join `orders` ord 
		on location.order_id = ord.order_id
		INNER JOIN `status_current` snumber 
		ON notes_status.location_id = snumber.location_id
		WHERE snumber.`status_number` < 6
		AND location.cancelled != 'Y'
		AND `max_notes_id` = `max_callback_id`";
		if ($start_date!="") { 
			$statusquery .= " AND `actual_callback_date` > '" . $start_date . "'";
		}
		$statusquery .= " AND `actual_callback_date` < '" . $end_date . "'";
		if ($USERNAME!="") {
			$statusquery .= " AND notes_status.`user_name` = '" . $USERNAME . "'";
//			
		}
		//ifnull(location.cancelled,'N') <> 'Y' and
		//echo $statusquery . "<Br><Br>";
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve info<br>" . mysql_error());
		$numberstat = mysql_Numrows($resultstat);
		if ($numberstat >0) {
			return mysql_result($resultstat, 0, "location_count");
		} else {
			return 0;
		}
	}
	function callbacks_employee_completed($date = "") {
		if ($date=="") {
			$date = date("Y-m-d");
		}
		$query = "DELETE FROM  `user_calls` WHERE user_logon = 'karen'";
		$result = mysql_query($query, $this->datalink) or die("unable to get clean count<Br>" . $query);
		
		$query = "SELECT user_logon, callbacks_count, callbacks_completed
		FROM `user_calls`
		WHERE callback_date = '" . $date . "'
		ORDER BY user_logon";
		$result = mysql_query($query, $this->datalink) or die("unable to get count<Br>" . $query);
		
		return $result;
	}
	function activity($user_name="", $activity_date = "") {
		if ($activity_date=="") {
			//today
			$activity_date=date("Y-m-d");
		}
		$statusquery = "SELECT user_name, callback_date
		FROM `notes_track`
		WHERE `time_stamp_track`
		LIKE '" . $activity_date . "%' ";
		if ($user_name != "") {
			$statusquery .= " and user_name = '" . $user_name . "'";
		}
		$statusquery .= " ORDER BY user_name asc";
		//echo $statusquery ."<br>";
 		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve info<br>" . mysql_error());
		$numberstat = mysql_Numrows($resultstat);
		$this->todays = $numberstat;
		return $resultstat;
	}
	function order_callbacks($start_date, $end_date, $USERNAME) {
		$statusquery = "SELECT 	notes_status.`order_id`, ord.order_number,
		ord.assigned_date, notes_status.`max_order_notes_id`, 
		notes_status.`max_callback_id`, notes_status.`user_name` notes_user_name, 
		notes_status.`callback_date`, ord.order_id, 
		ord.assigned_date ord_assigned_date, 
		ord.requiredby_date, notes.notes_id, notes.time_stamp,
		notes.user_name, notes.callback_date, notes.callback_time, notes.notes as notesnotes
		FROM `order_notes_status` notes_status
		inner join `order_notes` notes
		on notes_status.max_order_notes_id = notes.notes_id
		inner join `orders` ord 
		on notes_status.order_id = ord.order_id
		WHERE `max_order_notes_id` = `max_callback_id`";
		if ($start_date!="") { 
			$statusquery .= " AND `actual_callback_date` > '" . $start_date . "'";
		}
		$statusquery .= " AND `actual_callback_date` < '" . $end_date . "'";
		if ($USERNAME!="") {
			$statusquery .= " AND notes_status.`user_name` = '" . $USERNAME . "'";
		}
		$statusquery .= " ORDER BY ord.order_id";
		//echo $statusquery . "<Br>";
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve info<br>" . mysql_error());
		//just give them back the notes
		return $resultstat;
	}
	function order_callbacks_count($start_date, $end_date, $USERNAME) {
		$statusquery = "SELECT count(order_notes_status.order_id) order_count
		FROM `order_notes_status`
		inner join `order_notes` notes
		on order_notes_status.max_order_notes_id = notes.notes_id
		inner join `orders` ord 
		on order_notes_status.order_id = ord.order_id
		WHERE `max_order_notes_id` = `max_callback_id`";
		if ($start_date!="") { 
			$statusquery .= " AND `actual_callback_date` > '" . $start_date . "'";
		}
		$statusquery .= " AND `actual_callback_date` < '" . $end_date . "'";
		if ($USERNAME!="") {
			$statusquery .= " AND order_notes_status.`user_name` = '" . $USERNAME . "'";
		}
		//echo $statusquery . "<Br><Br>";
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve order notes info<br>" . mysql_error());
		$numberstat = mysql_Numrows($resultstat);
		if ($numberstat >0) {
			return mysql_result($resultstat, 0, "order_count");
		} else {
			return 0;
		}
	}
	function secondset_callbacks_count($start_date, $end_date, $USERNAME) {
		$statusquery = "SELECT count(secondset_notes_status.order_id) secondset_count
		FROM `secondset_notes_status`
		inner join `secondset_notes` notes
		on secondset_notes_status.max_secondset_notes_id = notes.notes_id
		inner join `orders` ord 
		on secondset_notes_status.order_id = ord.order_id
		WHERE `max_secondset_notes_id` = `max_callback_id`";
		if ($start_date!="") { 
			$statusquery .= " AND `actual_callback_date` > '" . $start_date . "'";
		}
		$statusquery .= " AND `actual_callback_date` < '" . $end_date . "'";
		if ($USERNAME!="") {
			$statusquery .= " AND secondset_notes_status.`user_name` = '" . $USERNAME . "'";
		}
		//echo $statusquery . "<Br><Br>";
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve secondset notes info<br>" . mysql_error());
		$numberstat = mysql_Numrows($resultstat);
		if ($numberstat >0) {
			return mysql_result($resultstat, 0, "secondset_count");
		} else {
			return 0;
		}
	}
	function billing_callbacks($start_date, $end_date, $USERNAME) {
		$statusquery = "SELECT 	notes_status.`order_id`, ord.order_number,
		ord.assigned_date, notes_status.`max_billing_notes_id`, 
		notes_status.`max_callback_id`, notes_status.`user_name` , 
		notes_status.`callback_date`, ord.order_id, 
		ord.assigned_date ord_assigned_date, 
		ord.requiredby_date, notes.notes_id, notes.time_stamp,
		notes.user_name, notes.callback_date, notes.notes as notesnotes
		FROM `billing_notes_status` notes_status
		inner join `billing_notes` notes
		on notes_status.max_billing_notes_id = notes.notes_id
		inner join `orders` ord 
		on notes_status.order_id = ord.order_id
		WHERE `actual_callback_date` <> '0000-00-00' ";
		//`max_billing_notes_id` = `max_callback_id`
		if ($start_date!="") { 
			$statusquery .= " AND `actual_callback_date` > '" . $start_date . "'";
		}
		$statusquery .= " AND `actual_callback_date` < '" . $end_date . "'";
		if ($USERNAME!="") {
			$statusquery .= " AND notes_status.`user_name` = '" . $USERNAME . "'";
		}
		$statusquery .= " ORDER BY ord.order_id";
		//echo $statusquery . "<Br>";
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve info<br>$statusquery<br>" . mysql_error());
		//just give them back the notes
		return $resultstat;
	}
	function secondset_callbacks($start_date, $end_date, $USERNAME) {
		$statusquery = "SELECT 	notes_status.`order_id`, ord.order_number,
		ord.assigned_date, notes_status.`max_secondset_notes_id`, 
		notes_status.`max_callback_id`, notes_status.`user_name` note_status_user_name, 
		notes_status.`callback_date`, ord.order_id, 
		ord.assigned_date ord_assigned_date, 
		ord.requiredby_date, notes.notes_id, notes.time_stamp,
		notes.user_name, notes.callback_date, notes.notes as notesnotes
		FROM `secondset_notes_status` notes_status
		inner join `secondset_notes` notes
		on notes_status.max_secondset_notes_id = notes.notes_id
		inner join `orders` ord 
		on notes_status.order_id = ord.order_id
		WHERE `actual_callback_date` <> '0000-00-00' ";
		//`max_secondset_notes_id` = `max_callback_id`
		if ($start_date!="") { 
			$statusquery .= " AND `actual_callback_date` > '" . $start_date . "'";
		}
		$statusquery .= " AND `actual_callback_date` < '" . $end_date . "'";
		if ($USERNAME!="") {
			$statusquery .= " AND notes_status.`user_name` = '" . $USERNAME . "'";
		}
		$statusquery .= " ORDER BY ord.order_id";
		//echo $statusquery . "<Br>";
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve info<br>$statusquery<br>" . mysql_error());
		//just give them back the notes
		return $resultstat;
	}
	function order_callbacks_user($start_date, $end_date, $USERNAME) {
		$statusquery = "SELECT 	notes_status.`order_id`, ord.order_number,
		ord.assigned_date, notes_status.`max_order_notes_id`, 
		notes_status.`max_callback_id`, notes_status.`user_name` , 
		notes_status.`callback_date`, ord.order_id, 
		ord.assigned_date ord_assigned_date, 
		ord.requiredby_date, notes.notes_id, notes.time_stamp,
		notes.user_name, notes.callback_date, notes.notes as notesnotes
		FROM `order_notes_user_status` notes_status
		inner join `order_notes` notes
		on notes_status.max_order_notes_id = notes.notes_id
		inner join `orders` ord 
		on notes_status.order_id = ord.order_id
		WHERE `actual_callback_date` <> '0000-00-00' ";
		//`max_order_notes_id` = `max_callback_id`
		if ($start_date!="") { 
			$statusquery .= " AND `actual_callback_date` > '" . $start_date . "'";
		}
		$statusquery .= " AND `actual_callback_date` < '" . $end_date . "'";
		if ($USERNAME!="") {
			$statusquery .= " AND notes_status.`user_name` = '" . $USERNAME . "'";
		}
		$statusquery .= " ORDER BY ord.order_id";
		//echo $statusquery . "<Br>";
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve info<br>$statusquery<br>" . mysql_error());
		//just give them back the notes
		return $resultstat;
	}
	function secondset_callbacks_user($start_date, $end_date, $USERNAME) {
		$statusquery = "SELECT 	notes_status.`order_id`, ord.order_number,
		ord.assigned_date, notes_status.`max_secondset_notes_id`, 
		notes_status.`max_callback_id`, notes_status.`user_name` , 
		notes_status.`callback_date`, ord.order_id, 
		ord.assigned_date ord_assigned_date, 
		ord.requiredby_date, notes.notes_id, notes.time_stamp,
		notes.user_name, notes.callback_date, notes.notes as notesnotes
		FROM `secondset_notes_user_status` notes_status
		inner join `secondset_notes` notes
		on notes_status.max_secondset_notes_id = notes.notes_id
		inner join `orders` ord 
		on notes_status.order_id = ord.order_id
		WHERE `actual_callback_date` <> '0000-00-00' ";
		//`max_secondset_notes_id` = `max_callback_id`
		if ($start_date!="") { 
			$statusquery .= " AND `actual_callback_date` > '" . $start_date . "'";
		}
		$statusquery .= " AND `actual_callback_date` < '" . $end_date . "'";
		if ($USERNAME!="") {
			$statusquery .= " AND notes_status.`user_name` = '" . $USERNAME . "'";
		}
		$statusquery .= " ORDER BY ord.order_id";
		//echo $statusquery . "<Br>";
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve info<br>$statusquery<br>" . mysql_error());
		//just give them back the notes
		return $resultstat;
	}
	function billing_callbacks_user($start_date, $end_date, $USERNAME) {
		$statusquery = "SELECT 	notes_status.`order_id`, ord.order_number,
		ord.assigned_date, notes_status.`max_billing_notes_id`, 
		notes_status.`max_callback_id`, notes_status.`user_name` , 
		notes_status.`callback_date`, ord.order_id, 
		ord.assigned_date ord_assigned_date, 
		ord.requiredby_date, notes.notes_id, notes.time_stamp,
		notes.user_name, notes.callback_date, notes.notes as notesnotes
		FROM `billing_notes_user_status` notes_status
		inner join `billing_notes` notes
		on notes_status.max_billing_notes_id = notes.notes_id
		inner join `orders` ord 
		on notes_status.order_id = ord.order_id
		WHERE `actual_callback_date` <> '0000-00-00' ";
		//`max_billing_notes_id` = `max_callback_id`
		if ($start_date!="") { 
			$statusquery .= " AND `actual_callback_date` > '" . $start_date . "'";
		}
		$statusquery .= " AND `actual_callback_date` < '" . $end_date . "'";
		if ($USERNAME!="") {
			$statusquery .= " AND notes_status.`user_name` = '" . $USERNAME . "'";
		}
		$statusquery .= " ORDER BY ord.order_id";
		//echo $statusquery . "<Br>";
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve info<br>$statusquery<br>" . mysql_error());
		//just give them back the notes
		return $resultstat;
	}
	function billing_report_users($start_date, $end_date, $USERNAME) {
		$statusquery = "SELECT 	DISTINCT notes_status.user_name
		FROM `billing_notes_status` notes_status
		inner join `billing_notes_track` notes
		on notes_status.max_billing_notes_id = notes.notes_id
		inner join `orders` ord 
		on notes_status.order_id = ord.order_id
		INNER JOIN user
		ON notes_status.`user_name` = user.user_logon
		WHERE 1 AND user.user_groups LIKE '%billing%'";
		//`max_billing_notes_id` = `max_callback_id`
		if ($start_date!="") { 
			$statusquery .= " AND `time_stamp_track` > '" . $start_date . "'";
		}
		$statusquery .= " AND `time_stamp_track` < '" . $end_date . "'";
		if ($USERNAME!="") {
			$statusquery .= " AND notes_status.`user_name` = '" . $USERNAME . "'";
		} else {
			$statusquery .= " AND notes_status.`user_name` <> ''";
		}
		$statusquery .= " ORDER BY notes_status.`user_name`";
		//echo $statusquery . "<Br>";
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve info<br>$statusquery<br>" . mysql_error());
		//just give them back the users
		return $resultstat;
	}
	function billing_callbacks_count($start_date, $end_date, $USERNAME) {
		$statusquery = "SELECT count(billing_notes_status.order_id) order_count
		FROM `billing_notes_status`
		inner join `billing_notes` notes
		on billing_notes_status.max_billing_notes_id = notes.notes_id
		inner join `orders` ord 
		on billing_notes_status.order_id = ord.order_id
		WHERE 1" ; 	//`actual_callback_date` <> '0000-00-00'";
//		`max_billing_notes_id` = `max_callback_id`
		if ($start_date!="") { 
			$statusquery .= " AND `actual_callback_date` > '" . $start_date . "'";
		}
		$statusquery .= " AND `actual_callback_date` < '" . $end_date . "'";
		if ($USERNAME!="") {
			$statusquery .= " AND billing_notes_status.`user_name` = '" . $USERNAME . "'";
		}
		//echo $statusquery . "<Br><Br>";
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve order notes info<br>$statusquery<br>" . mysql_error());
		$numberstat = mysql_Numrows($resultstat);
		if ($numberstat >0) {
			return mysql_result($resultstat, 0, "order_count");
		} else {
			return 0;
		}
	}
	function billing_callbacks_user_count($start_date, $end_date, $USERNAME) {
		$statusquery = "SELECT count(bns.max_callback_id) callback_count
		FROM `billing_notes_user_status` bns
		inner join `billing_notes` notes
		on bns.max_billing_notes_id = notes.notes_id
		inner join `orders` ord 
		on bns.order_id = ord.order_id
		WHERE `actual_callback_date` <> '0000-00-00'";
//		`max_billing_notes_id` = `max_callback_id`
		if ($start_date!="") { 
			$statusquery .= " AND `actual_callback_date` > '" . $start_date . "'";
		}
		$statusquery .= " AND `actual_callback_date` < '" . $end_date . "'";
		if ($USERNAME!="") {
			$statusquery .= " AND bns.`user_name` = '" . $USERNAME . "'";
		}
		//echo $statusquery . "<Br><Br>";
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve order notes info<br>$statusquery<br>" . mysql_error());
		$numberstat = mysql_Numrows($resultstat);
		if ($numberstat >0) {
			return mysql_result($resultstat, 0, "callback_count");
		} else {
			return 0;
		}
	}
	function secondset_callbacks_user_count($start_date, $end_date, $USERNAME) {
		$statusquery = "SELECT count(bns.max_callback_id) callback_count
		FROM `secondset_notes_user_status` bns
		inner join `secondset_notes` notes
		on bns.max_secondset_notes_id = notes.notes_id
		inner join `orders` ord 
		on bns.order_id = ord.order_id
		WHERE `actual_callback_date` <> '0000-00-00'";
//		`max_secondset_notes_id` = `max_callback_id`
		if ($start_date!="") { 
			$statusquery .= " AND `actual_callback_date` > '" . $start_date . "'";
		}
		$statusquery .= " AND `actual_callback_date` < '" . $end_date . "'";
		if ($USERNAME!="") {
			$statusquery .= " AND bns.`user_name` = '" . $USERNAME . "'";
		}
		//echo $statusquery . "<Br><Br>";
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve order notes info<br>$statusquery<br>" . mysql_error());
		$numberstat = mysql_Numrows($resultstat);
		if ($numberstat >0) {
			return mysql_result($resultstat, 0, "callback_count");
		} else {
			return 0;
		}
	}
	function order_callbacks_user_count($start_date, $end_date, $USERNAME) {
		$statusquery = "SELECT count(bns.max_callback_id) callback_count
		FROM `order_notes_user_status` bns
		inner join `order_notes` notes
		on bns.max_order_notes_id = notes.notes_id
		inner join `orders` ord 
		on bns.order_id = ord.order_id
		WHERE `actual_callback_date` <> '0000-00-00'";
//		`max_order_notes_id` = `max_callback_id`
		if ($start_date!="") { 
			$statusquery .= " AND `actual_callback_date` > '" . $start_date . "'";
		}
		$statusquery .= " AND `actual_callback_date` < '" . $end_date . "'";
		if ($USERNAME!="") {
			$statusquery .= " AND bns.`user_name` = '" . $USERNAME . "'";
		}
		//echo $statusquery . "<Br><Br>";
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve order notes info<br>$statusquery<br>" . mysql_error());
		$numberstat = mysql_Numrows($resultstat);
		if ($numberstat >0) {
			return mysql_result($resultstat, 0, "callback_count");
		} else {
			return 0;
		}
	}
	function billing_week_report($blnUsers, $USERNAME, $start_date, $end_date) {
		$statusquery = "SELECT notes_status.user_name, cast(`time_stamp_track` as date) thedate, 
		count(notes_status.order_id) note_count
		FROM `billing_notes_status` notes_status
		inner join `billing_notes_track` notes
		on notes_status.max_billing_notes_id = notes.notes_id
		inner join `orders` ord 
		on notes_status.order_id = ord.order_id
		WHERE 1 ";
		//`max_billing_notes_id` = `max_callback_id`
		if ($start_date!="") { 
			$statusquery .= " AND `time_stamp_track` > '" . $start_date . "'";
		}
		$statusquery .= " AND `time_stamp_track` < '" . $end_date . "'";
		if ($USERNAME!="") {
			$statusquery .= " AND notes_status.`user_name` = '" . $USERNAME . "'";
		} else {
			$statusquery .= " AND notes_status.`user_name` <> ''";
		}
		$statusquery .= " GROUP BY cast(`time_stamp_track` as date), user_name
		ORDER BY";
		if ($USERNAME=="") {
			$statusquery .= " user_name asc, ";
		}
		$statusquery .= " `time_stamp_track` asc";
		//echo $statusquery . "<Br><Br>";
		
		$resultstat = MYSQL_QUERY($statusquery, $this->datalink) or die("unable to retrieve order notes info<br>$statusquery<br>" . mysql_error());
		return $resultstat;
	}
	function search_user_notes($username, $prefix, $read_status="", $visible="", $notesearch = "", $folder_id = "NULL", $user_id = "", $search_date = "", $from_user_filter = "", $to_user_filter = "") {
		if ($this->sortby!="") {
			$sorted = " ORDER BY " . $this->sortby;
		} else {
			$sorted = " ORDER BY user_notes.notes_id desc ";	
		}
		if ($search_date!="") {
			$sorted = " ORDER BY user_notes.notes_id asc ";	
		}
		if ($folder_id=="NULL") {
			$bareQuery = "SELECT ufold.folder_id, user_notes . *
			FROM user_notes
			LEFT OUTER JOIN user_notes_folder ufold ON (user_notes.notes_id = ufold.notes_id AND ufold.user_id = '" . $user_id . "')
			WHERE 1
			AND ufold.folder_id IS NULL";
		} else {
			$bareQuery = "SELECT DISTINCT user_notes.* 
			from user_notes ";
			if ($folder_id!="") {
				//give it a folder name
				$bareQuery .= " INNER JOIN user_notes_folder ufold
				ON (user_notes.notes_id = ufold.notes_id
				AND ufold.folder_id = '" . $folder_id . "'
				AND ufold.user_id = '" . $user_id . "')
				WHERE 1";
			}
		}
		if ($prefix!="" && $from_user_filter == "" && $to_user_filter == "") {
			$bareQuery .= " AND " . $prefix . "_user_name = '$username'";
		} else {
			$bareQuery .= " AND (to_user_name = '$username' OR from_user_name = '$username')";
		}
		if ($from_user_filter != "") {
			$bareQuery .= " AND from_user_name = '" . $from_user_filter . "'";
		}
		if ($to_user_filter != "") {
			$bareQuery .= " AND to_user_name = '" . $to_user_filter . "'";
		}
		if ($read_status!="") {
			if ($read_status=="Y") {
				$bareQuery .= " AND read_status = 'Y'";
			} else {
				$bareQuery .= " AND read_status != 'Y'";
			}
		}
		if ($visible!="") {
			$bareQuery .= " AND visible = '" . $visible . "'";
		}
		if ($notesearch!="") {
			$bareQuery .= " AND (";
			$bareQuery .= " notes LIKE '%" . $notesearch . "%'";
			$bareQuery .= " OR from_user_name = '" . $notesearch . "'";
			$bareQuery .= " OR to_user_name = '" . $notesearch . "'";
			$bareQuery .= ")";
		}
		if ($search_date=="") {
			$lastmonth = mktime(0, 0, 0, date("m")-3, date("d"),   date("Y"));
		} else {
			$lastmonth = strtotime($search_date);
		}
		$bareQuery .= " AND user_notes.time_stamp > '" . date("Y-m-d", $lastmonth) . "'";
		$queryall = $bareQuery.$sorted;
		if ($search_date!="") {
			$queryall .= " LIMIT 0,500";
		} else {
			$queryall .= " LIMIT 0,500";
		}
		//die( $queryall . "<br>");
		//$resultall = MYSQL_QUERY($queryall, $this->datalink) or die("unable to retrieve user notes info<br>" . mysql_error());
		//return $resultall;
		try {
			$sql = $queryall;
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$notes = $stmt->fetchAll(PDO::FETCH_OBJ);
			$stmt->closeCursor(); $stmt = null; $db = null;
			
			return $notes;
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
	}
	function detail_user_notes($timestamp, $from) {
		$sorted = " ORDER BY user_notes.notes_id desc ";	
		$queryall = "SELECT *
		FROM `user_notes`
		WHERE `from_user_id` = '" . $from . "'
		AND `time_stamp` = '" . $timestamp . "'";
		die( $queryall . "<br>++");
		$resultall = MYSQL_QUERY($queryall, $this->datalink) or die("unable to retrieve user notes info<br>" . mysql_error());
		return $resultall;
	}
	function mark_read($username) {
		$queryall = "update user_notes set read_status = 'Y' 
		WHERE to_user_name = '$username' and read_status = 'N'";
		$resultall = MYSQL_QUERY($queryall, $this->datalink) or die("unable to mark user notes as read<br>" . mysql_error());
	}
	function clear_company_note($company_id, $status) {
		if ($status!="") {
			$queryall = "delete from company_notes 
			WHERE status = '" . $status . "'
			and company_id = '" . $company_id . "'";
			$resultall = MYSQL_QUERY($queryall, $this->datalink) or die("unable to clear special company_notes<br>" . mysql_error());
		}
	}
	function clear_newcompany_note($company_id, $status) {
		if ($status!="") {
			$queryall = "delete from newcompany_notes 
			WHERE status = '" . $status . "'
			and company_id = '" . $company_id . "'";
			$resultall = MYSQL_QUERY($queryall, $this->datalink) or die("unable to clear newcompany_notes<br>" . mysql_error());
		}
	}

	function fetch_company_notes($company_id, $status = "") {
		$querynotes = "SELECT notes_id, notes,time_stamp,user_name,callback_date, contact, status 
		from company_notes WHERE company_id = '" . $company_id . "'";
		if ($status!="" && $status!="DISPLAY") {
			$querynotes .= " and status = '" . $status . "'";
		} else {
			if ($status=="DISPLAY") {
				$querynotes .= " and status = '" . $status . "'";
			} else {
				$querynotes .= " AND status != 'DISPLAY'";
			}
		}
		$querynotes .= " ORDER BY notes_id desc";
		//echo $querynotes . "<br>";
		$resultnotes = MYSQL_QUERY($querynotes, $this->datalink) or die("unable to get company_notes<br>" . mysql_error());
		return $resultnotes;
	}
	function fetch_user_notes($notes_id) {
		$querynotes = "SELECT `notes_id`, `from_user_id`, `to_user_id`, `from_user_name`, `to_user_name`, `subject`, `notes`, `time_stamp`, `read_status`, `reply_status`, `visible`, `note_type`
		from user_notes WHERE notes_id = '" . $notes_id . "'";
		//echo $querynotes . "<br>";
		$resultnotes = MYSQL_QUERY($querynotes, $this->datalink) or die("unable to get company_notes<br>" . mysql_error());
		return $resultnotes;
	}
	function fetch_prospects_notes($company_id, $status = "") {
		$querynotes = "SELECT notes_id, notes,time_stamp,user_name,callback_date, contact, status 
		from prospects_notes WHERE company_id = '" . $company_id . "'";
		if ($status!="") {
			$querynotes .= " and status = '" . $status . "'";
		}
		$querynotes .= " ORDER BY notes_id desc";
		//echo $querynotes . "<br>";
		$resultnotes = MYSQL_QUERY($querynotes, $this->datalink) or die("unable to get prospects_notes<br>" . mysql_error());
		return $resultnotes;
	}
	function fetch_employee_notes($employee_id, $status = "") {
		$querynotes = "SELECT notes_id, notes,time_stamp,user_name,callback_date, contact, status 
		FROM employee_notes 
		WHERE user_id = :employee_id
		AND deleted = 'N'
		AND customer_id = :customer_id";
		if ($status!="") {
			$querynotes .= " 
			and `status` = :status";
		}
		$querynotes .= " 
		ORDER BY notes_id desc";
		//echo "notes: " . $querynotes . "<br>";
		//$resultnotes = MYSQL_QUERY($querynotes, $this->datalink) or die("unable to get employee_notes<br>" . mysql_error());
		//return $resultnotes;
		$customer_id = $_SESSION["user_customer_id"];
		try {
			$sql = $querynotes;
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("employee_id", $employee_id);
			$stmt->bindParam("customer_id", $customer_id);
			if ($status!="") {
				$stmt->bindParam("status", $status);
			}
			$stmt->execute();
			$employee_notes = $stmt->fetchAll(PDO::FETCH_OBJ);
			$stmt->closeCursor(); $stmt = null; $db = null;
			
			return $employee_notes;
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
	}
	function fetch_newcompany_notes($newcompany_id, $status = "") {
		$querynotes = "SELECT notes_id, notes,time_stamp,user_name,callback_date, contact, status 
		from newcompany_notes WHERE newcompany_id = '" . $newcompany_id . "'";
		if ($status!="") {
			$querynotes .= " and status = '" . $status . "'";
		}
		$querynotes .= " ORDER BY notes_id desc";
		//echo $querynotes . "<br>";
		$resultnotes = MYSQL_QUERY($querynotes, $this->datalink) or die("unable to get newcompany_notes<br>" . mysql_error());
		return $resultnotes;
	}
}
?>