<?php
//class to manage users
class systemuser
{
	var $name; 
	var $id;
	var $uuid;
	var $user_name;
	var $user_type;
	var $user_logon;
	var $user_groups;
	var $work_location;
	var $assignments;
	var $shift;
	var $hired_date;
	var $clock_in_time;
	var $clock_out_time;
	var $last_week_dates;
	var $next_week_dates;
	//data access
	var $datalink;
	var $search_location_id;
	
	var $locations;
	//nocal
	var $nocal_ziplimit = "93199";
	
	function __construct() {
		return true;
	}
	function insert($user_name,$user_logon,$user_pd,$user_groups) {
		//inserts
		$query="insert into `user` (user_name,user_logon,user_pd,`user_groups`, `data`) 
		 VALUES ('$user_name','$user_logon','$user_pd','" . $user_groups . "', '')";
		$result = MYSQL_QUERY($query,$this->datalink) or die("Unable to insert user<br>$query<br>" . mysql_error());
		//now retrieve the new id
		$query="select `user_id` from `user` order by `user_id` desc LIMIT 0,1";
		$result = MYSQL_QUERY($query, $this->datalink) or die("Unable to retrieve new user id<br>$query");
		$row_order = mysql_fetch_row($result);
		
		$this->id = $row_order[0];
		//get the newly created id
		$this->uuid = getAutoincrement('user', 'user', $result, $this->datalink);
	}
	function clearAssignments($user_id = "", $unixdate = "", $location_id = "") {
		if ($user_id == "") {
			$user_id = $this->id;
		}
		if ($unixdate == "") {
			$unixdate = date("Y-m-d");
		} else {
			$unixdate = date("Y-m-d", strtotime($unixdate));
		}
		//clear any previous daily assignments for this user
		$query="delete from `driver_assignments` 
		where `user_id`= '$user_id' and `dateandtime` = '" . $unixdate . "'";
		if ($location_id!="") {
			$query .= " AND location_id = '" . $location_id . "'";
		}
		//die($query ."<br>");
		$result = MYSQL_QUERY($query, $this->datalink) or die("unable to clear user assignments<br>$query<br>" . mysql_error());
		return $result;
	}
	function clearOrderAssignments($user_id = "", $unixdate = "", $order_id = "") {
		if ($user_id == "") {
			$user_id = $this->id;
		}
		if ($unixdate == "") {
			$unixdate = date("Y-m-d");
		} else {
			$unixdate = date("Y-m-d", strtotime($unixdate));
		}
		//clear any previous daily assignments for this user
		$query="delete from `driver_orders` 
		where `user_id`= '$user_id' and `dateandtime` = '" . $unixdate . "'";
		if ($location_id!="") {
			$query .= " AND `order_id` = '" . $order_id . "'";
		}
		//die($query ."<br>");
		$result = MYSQL_QUERY($query, $this->datalink) or die("unable to clear user assignments<br>$query<br>" . mysql_error());
		return $result;
	}
	function clearCallerAssignments($user_id = "", $location_id = "") {
		if ($user_id == "") {
			$user_id = $this->id;
		}
		//clear any previous daily assignments for this user
		$query="delete from `caller_assignments` 
		where `user_id`= '$user_id'";
		if ($location_id!="") {
			$query .= " AND location_id = '" . $location_id . "'";
		}
		//echo $query ."<br>";
		$result = MYSQL_QUERY($query, $this->datalink) or die("unable to clear user caller assignments<br>$query<br>" . mysql_error());
		return $result;
	}
	function id($id) {
		//give a normal id, set the uuid
		//prep an array
		$this->id=$id;
		if ($this->uuid=="") {
			//get the firm name, role person, phone, fax
			$querycomp = "select user_uuid
			from `user`
			where user_id = '$this->id'";			
			//$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or 
					die ("Unable to get the user uuid<br>$querycomp<br>" . mysql_error());
			//$numberupdate = $resultcomp->rowCount();
			if ($numberupdate > 0) {
				//fill up the array
				$this->uuid=mysql_result($resultcomp,0,"user_uuid");
			}
		}
		$this->fetch();
	}
	function listall($group, $sortby = "", $in_statement = "") {
		$querydriver = "select distinct user_id, user_uuid, user_name, user_logon, work_location
		from `user` 
		where user_groups like '%" . $group . "%'";
		if ($in_statement != "" && $in_statement != "''") {
			$querydriver .= " AND `user_logon` IN (" . $in_statement . ")";
		}
		if ($this->work_location!="") {
			$querydriver .= " AND `work_location` = '" . $this->work_location . "'";
		}
		
		if ($sortby!="") {
			$querydriver .= " order by " . $sortby;
		}
		//echo $querydriver . "<br>";
		$resultdriver = MYSQL_QUERY($querydriver, $this->datalink) or die($querydriver . "<br>" . mysql_error());
		return $resultdriver;
	}
	function search_notification($notification) {
		if ($notification!="") {
			$querydriver = "SELECT DISTINCT user.`user_id`, user.`user_uuid`, user.`user_name`, user.`user_logon`, user.`user_groups`, user.`user_pd`, user.`shift`, user.`work_location`, user.`ssn`, user.`employee_number`, user.`dob`, user.`hired_date`, user.`call_center`, user.`clock_in_time`, user.`clock_out_time`, user.`lunch_out_time`, user.`data`, user.`cell_phone`, user.`session_id`, user.`dateandtime`
			from `user`
			INNER JOIN user_comm ucomm
			ON `user`.user_uuid = ucomm.user_uuid
			AND ucomm.attribute = :notification
			INNER JOIN comm
			ON ucomm.comm_uuid = comm.comm_uuid
			WHERE comm.comm = 'Y'";
		}
		//$resultdriver = mysql_query($querydriver, $this->datalink) or die("Unable to get notification users<br>" . mysql_error());
		//return $resultdriver;
		
		try {
			$sql = $querydriver;
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("notification", $notification);
			$stmt->execute();
			$users = $stmt->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		
		return $users;
	}
	function search($group = "", $sortby = "", $in_statement = "", $verified = "") {
		$querydriver = "SELECT DISTINCT user.`user_id`, user.`user_uuid`, user.`user_name`, user.`user_logon`, user.`user_groups`, user.`user_pd`, user.`shift`, user.`work_location`, user.`ssn`, user.`employee_number`, user.`dob`, user.`hired_date`, user.`call_center`, user.`clock_in_time`, user.`clock_out_time`, user.`lunch_out_time`, user.`data`, user.`cell_phone`, user.`session_id`, user.`dateandtime`
		from `user` 
		where 1 
		AND user_groups like '%" . $group . "%'
		AND  `user_logon` != ''
		AND user.user_id NOT IN (SELECT DISTINCT user_id FROM employee_notes WHERE `status` = 'FIRE')";
		if ($in_statement != "") {
			$querydriver .= " AND `user_logon` IN (" . $in_statement . ")";
		}
		if ($this->work_location!="") {
			$querydriver .= " AND `work_location` = '" . $this->work_location . "'";
		}
		if ($verified!="") {
			$querydriver .= " AND `verified` = '" . $verified . "'";
		}
		if ($sortby!="") {
			$querydriver .= " order by " . $sortby;
		}
		//echo $querydriver . "<br>";
		$resultdriver = MYSQL_QUERY($querydriver, $this->datalink) or die($querydriver . "<br>" . mysql_error());
		return $resultdriver;
		
	}
	function listdob() {
		$querydriver = "select distinct user_id, user_uuid, user_name, user_logon, work_location
		from `user` 
		where DATE_FORMAT(dob, '%m-%d') = '" . date("m-d") . "'";
		$querydriver .= " order by user_logon";
		//echo $querydriver . "<br>";
		$resultdriver = MYSQL_QUERY($querydriver, $this->datalink) or die($querydriver . "<br>" . mysql_error());
		return $resultdriver;
	}
	function listhired($active = true, $search_date = "") {
		//default date
		if ($search_date=="") {
			$search_date = date("m-d");
		}
		$querydriver = "select distinct `user`.user_id, `user`.user_uuid, 
		`user`.user_name, `user`.user_logon, `user`.hired_date, `user`.work_location
		from `user` ";
		/*
		if ($active) {
			$querydriver .= " INNER JOIN employee_notes enotes 
			ON (user.user_id = enotes.user_id AND enotes.status != 'FIRE')";
		}
		*/
		$querydriver .= " WHERE DATE_FORMAT(`user`.hired_date, '%m-%d') = '" . $search_date . "'";
		if ($active) {
			$querydriver .= " AND `user`.user_id NOT IN (SELECT user_id FROM employee_notes WHERE `status` = 'FIRE')";
		}
		$querydriver .= " ORDER BY `user`.user_logon";
		echo $querydriver . "<br>";
		$resultdriver = MYSQL_QUERY($querydriver, $this->datalink) or die($querydriver . "<br>" . mysql_error());
		return $resultdriver;
	}
	function make_selectoptions($group, $sortby = "", $attribute = "") {
		if ($sortby != "") {
			$sortby = "user_name ASC, " . $sortby;
		} else {
			$sortby = "user_name ASC";
		}
		$resultset = $this->listall($group, $sortby);
		$numberdrop = $resultset->rowCount();
		//die("numberdrop: " . $numberdrop);
		$xdrop=0;
		$blnFoundit = false;
		if ($numberdrop>0) {	
			while ($xdrop < $numberdrop)
			{
				$user_id = mysql_result($resultset,$xdrop,"user_id"); 
				$user_name = mysql_result($resultset,$xdrop,"user_name");  
	
				if ($user_id==$this->id) {
					$selected = " selected";
					$blnFoundit = true;
				} else {
					$selected = "";
				}
//				echo $category . "<br>";
				$optionstring .= "<option value='".$user_id."'" . $selected . ">".$user_name."</option>/r/n"; 
				
				 $xdrop++;
			} // end while 
			if ($blnFoundit==false) {
				//select the empty first choice
				$selected = " selected";
			}
			$optionstring = "<option value=''" . $selected . ">Select a User</option>/r/n" . $optionstring; 
		} // end if 
		return $optionstring;
	}
	function fetch($id = "") {
		if ($id != "") {
			$this->id = $id;
		}
		if ($this->id == "") {
			$querydriver = "SELECT * 
			FROM `user` 
			WHERE user_uuid = :user_id";
			$user_id = $this->uuid;
		} else {
			$querydriver = "SELECT * 
			FROM `user` 
			WHERE user_id = :user_id";
			$user_id = $this->id;
		}
		//die($querydriver);
		//$resultdriver = MYSQL_QUERY($querydriver, $this->datalink);
		try {
			$sql = $querydriver;
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("user_id", $user_id);
			$stmt->execute();
			$user = $stmt->fetchObject();
			//die(print_r($user));
			
			if (is_object($user)) {
				$this->id = $user->user_id;
				$this->uuid = $user->user_uuid; 
				$this->user_name = $user->user_name; 
				$this->user_type = $user->user_type; 
				$this->work_location = $user->work_location; 
				$this->data = $user->data; 
				$this->user_logon = $user->user_logon; 
				$this->user_groups = $user->user_groups;
			}
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
	}
	function fetchuser() {
		if ($this->user_logon == "") {
			return false;
		}
		$querydriver = "select user_name, user_id, user_pd, user_uuid, shift, user_groups, work_location,
		dob, ssn, hired_date, call_center, clock_in_time, clock_out_time, lunch_out_time
		from `user` 
		where user_logon = :user_logon";
		//echo $querydriver . "<br>";
		try {
			$sql = $querydriver;
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("user_logon", $this->user_logon);
			$stmt->execute();
			$user = $stmt->fetchObject();
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		if (is_object($user)) {
			$x = 0;
			$this->id = $user->user_id; 
			$this->uuid = $user->user_uuid; 
			//echo "uuid:" . $this->uuid  . "<BR>";
			$this->user_name = $user->user_name; 
			$this->user_pd = $user->user_pd; 
			$this->work_location = $user->work_location; 
			$this->user_groups = $user->user_groups;
			$this->hired_date = $user->hired_date;
			$this->clock_in_time = $user->clock_in_time;
			$this->clock_out_time = $user->clock_out_time;
			$this->shift = $user->shift;
			$this->call_center = $user->call_center;
		} else {
			$this->id = ""; 
			$this->uuid = ""; 
			$this->user_name = ""; 
			$this->user_groups = "";
			$this->shift = "";
			$this->hired_date = "";
			$this->clock_in_time = "";
			$this->clock_out_time = "";
			$this->call_center = "";
		}
	}
	function update_logon($new_logon) {
		$queryupdate = "UPDATE `userlogin_track` 
		SET username = '" . $new_logon . "',
		username_track = '" . $new_logon . "',
		userlogin_id = '" . $this->id . "',
		username = '" . $new_logon . "'
		WHERE username_track = '" . $this->user_logon . "'";
		$resultupdate = MYSQL_QUERY($queryupdate, $this->datalink) or die("Unable to update user logon<b>$queryupdate<br>" . mysql_error());
		
		$queryupdate = "UPDATE `userlogin` SET username = '" . $new_logon . "'
		WHERE username = '" . $this->user_logon . "'";
		$resultupdate = MYSQL_QUERY($queryupdate, $this->datalink) or die("Unable to update user logon<b>$queryupdate<br>" . mysql_error());
		
		$queryupdate = "UPDATE `user` SET user_logon = '" . $new_logon . "'
		WHERE user_uuid = '" . $this->uuid . "'";
		$resultupdate = MYSQL_QUERY($queryupdate, $this->datalink) or die("Unable to update user logon<b>$queryupdate<br>" . mysql_error());
	}
	function getAssignments($id="", $status = "") {
		if ($id!="") {
			$this->id = $id;
		}
		$queryupdate = "select * from `user` where user_id = '$this->id'
				order by user_id desc";
		$resultupdate = MYSQL_QUERY($queryupdate, $this->datalink) or die("Unable to get user<b>$queryupdate<br>" . mysql_error());
		$numberupdate = $resultupdate->rowCount();

		if ($numberupdate>0) {
			//get the info, and then display it
			$x=0;
		
			$this->user_name = mysql_result($resultupdate,$x,'user_name'); 
			$this->work_location = mysql_result($resultupdate,$x,'work_location'); 
			$this->user_logon = mysql_result($resultupdate,$x,'user_logon'); 
			$this->user_groups = mysql_result($resultupdate,$x,'user_groups');
			
			//get the locations
			$queryass = "select loc.location_id, dass.user_id, dass.dateandtime, order_number, loc.location_number, 
			dass.status, stc.status_number
			from location loc
			INNER JOIN driver_assignments dass
			on loc.location_id = dass.location_id
			INNER JOIN status_current stc
			on (loc.order_id = stc.order_id
			and loc.location_id = stc.location_id)
			where dass.user_id = '$this->id'";
			if ($status != "") {
				$queryass .= " and dass.status = '" .$status . "'";
			}
			$queryass .= " order by dass.dateandtime desc, order_number, location_number";
			//echo $queryass . "<br><br>";
			$resultass = MYSQL_QUERY($queryass, $this->datalink) or die("Unable to get user<b>$queryass<br>" . mysql_error());
			$numberass = $resultass->rowCount();

			if ($numberass>0) {
				$y = 0;
				$assign_table = "";
				while ($y<$numberass) {
					$location_id = mysql_result($resultass,$y,'location_id'); 
					$user_id = mysql_result($resultass,$y,'user_id'); 
					$dateandtime = mysql_result($resultass,$y,'dateandtime'); 
					$order_number = mysql_result($resultass,$y,'order_number'); 
					$location_number = mysql_result($resultass,$y,'location_number'); 
					$status = mysql_result($resultass,$y,'status'); 
					$status_number = mysql_result($resultass,$y,'status_number'); 
//					echo $status_number . "<BR>";
					$status_color = $this->getstatuscolor($status_number);
					$invoice_number = "<a href='../orders/notes_edit.php?locid=" . $location_id . "' target='_blank'><font color='". $status_color . "'>" . $order_number . "-" . $location_number . "</font></a>";
					if ($status=="IN") {
						//make it green
						$invoice_number = '<font color="#00CC00"><b>' . $invoice_number . '</b></font>';
					}
					$invoice_number .= "&nbsp;(" . $status_number . ")";
					if ($currentday != $dateandtime) {
						if ($day_cell!="" && $locations_cell!="") {
							$assign_table .= "<tr>" . $day_cell . "<td>" . $locations_cell . "</td></tr>";
							$day_cell = "";
							$locations_cell = "";
						}
						$currentday = $dateandtime;
						$arrDay = explode("-",$dateandtime);
						$that_day = $arrDay[1] . "/" . $arrDay[2] . "/" . $arrDay[0];
						//$day_cell = "<td width='10%' valign='top'><b>" . $that_day . "<br><a href='drivers_clearassign.php?user_id=" . $user_id . "&assignment_date=" . $dateandtime . "'>Clear</a></b></td>";
						$day_cell = "<td width='10%' valign='top'><b>" . $that_day . "</b></td>";
						//get map link
						//$map_link = "<td><a href='drivers_clearassign.php?user_id=" . $user_id . "&assignment_date=" . $dateandtime . "'>Clear</a>";
						$map_link = "<td nowrap valign='top' width='2%'><a href='daily_listing.php?user_id=" . $user_id . "&assignment_date=" . $dateandtime ."'>Log</a>&nbsp;|&nbsp;<a href='addresses.php?user_id=" . $user_id . "&assignment_date=" . $dateandtime . "'>Get Map</a><br><a href='trips.php?user_id=" . $user_id . "&assignment_date=" . $dateandtime . "'>Export</a>";
						$day_cell .= $map_link;
					}
					if ($locations_cell=="") {
						$locations_cell = $invoice_number;
					} else {
						$locations_cell .= " | " . $invoice_number;
					}
					$y++;
				}
				if ($day_cell!="" && $locations_cell!="") {
					
					$assign_table .= "<tr>" . $day_cell . "<td valign='top' bgcolor='#000000'><span align='justify'><font color='#FFFFFF'>" . $locations_cell . "</font></span></td></tr>";
					$day_cell = "";
					$locations_cell = "";
				}
			}
			if ($assign_table!="") {
				$assign_table ="<table width='100%' border='1' cellspacing='0'>" . $assign_table . "</table>";
			}
		}
		return $assign_table;
	}
	function getCallerAssignmentsVertical($id="", $status = "", $thedate = "", $sortby = "", $singleUser = false) {
		if ($id!="") {
			$this->id = $id;
		}
		$queryupdate = "select * from `user` where user_id = '$this->id'
				order by user_id desc";
		$resultupdate = MYSQL_QUERY($queryupdate, $this->datalink) or die("Unable to get user<b>$queryupdate<br>" . mysql_error());
		$numberupdate = $resultupdate->rowCount();

		if ($numberupdate>0) {
			//get the info, and then display it
			$x=0;
		
			$this->user_name = mysql_result($resultupdate,$x,'user_name'); 
			$this->user_logon = mysql_result($resultupdate,$x,'user_logon'); 
			$this->user_groups = mysql_result($resultupdate,$x,'user_groups');
			
			//get the locations
			$queryass = "select DISTINCT loc.location_id, loc.order_id, dass.user_id, dass.dateandtime, loc.order_number, loc.location_number, 
			dass.status, stc.status_number, orders.rush, fac.zip as facilityzip
			from location loc
			INNER JOIN caller_assignments dass
			on loc.location_id = dass.location_id
			INNER JOIN status_current stc
			on (loc.order_id = stc.order_id
			and loc.location_id = stc.location_id)
			INNER JOIN orders ON loc.order_id = orders.order_id
			INNER JOIN companies fac
			on loc.facility_id = fac.company_id
			where dass.user_id = '$this->id'
			AND loc.cancelled != 'Y'
			AND dass.`status` != 'CANCEL'";
			if ($status != "") {
				$queryass .= " and dass.status = '" .$status . "'";
			}
			if ($thedate != "") {
				$queryass .= " and dass.dateandtime = '" .$thedate . "'";
			}
			//all case
			$queryass .= " and stc.status_number < 5.5";
			
			//$queryass .= " order by orders.rush ASC, status_number, loc.order_id, loc.location_id";
			if ($sortby=="") {
				$queryass .= " order by dass.dateandtime";
			} else {
				$queryass .= " order by " . $sortby;
			}
			
			//echo $queryass . "<Br>";
			$resultass = MYSQL_QUERY($queryass, $this->datalink) or die("Unable to get user<b>$queryass<br>" . mysql_error());
			$numberass = $resultass->rowCount();
			$this->assignments = $numberass;

			if ($numberass>0) {
				//echo $queryass . "<br><br>";
				$y = 0;
				$assign_table = "";
				
				while ($y<$numberass) {
					$location_id = mysql_result($resultass,$y,'location_id'); 
					$user_id = mysql_result($resultass,$y,'user_id'); 
					$dateandtime = mysql_result($resultass,$y,'dateandtime'); 
					$order_id = mysql_result($resultass,$y,'order_id'); 
					$order_number = mysql_result($resultass,$y,'order_number'); 
					$location_number = mysql_result($resultass,$y,'location_number'); 
					$status = mysql_result($resultass,$y,'status'); 
					$status_number = mysql_result($resultass,$y,'status_number'); 
					$rush = mysql_result($resultass,$y,'rush'); 
					$facilityzip=mysql_result($resultass,$y,"facilityzip");
					if ($facilityzip > $this->nocal_ziplimit) {
						$blnNoCal = true;
						//$location_number .= "N/C"; 
					} else {
						$blnNoCal = false;
					}
					//echo "stat:" . $status_number . "<BR>";
					$display_number = $status_number;
					 
					if ($current_status_number != $display_number) {
						$current_status_number = $display_number;
						$intCounter = 1;
					} else {
						$intCounter++;
					}
//					echo $status_number . "<BR>";
					$status_color = $this->getstatuscolor($status_number);
					//echo $order_number . "-" . $location_number . " [" . $display_number . "-" . $status_color . "<BR>";
					if ($status_color=="#FFFFFF" && date("m/d", strtotime($dateandtime))!= date("m/d")) {
						$status_color="#CCCCCC";
					}
					$invoice_number = "<a href='../orders/notes_edit.php?locid=" . $location_id . "' target='_blank'><font color='". $status_color . "'>" . $order_number . "-" . $location_number . "</font></a>
					<input type='hidden' value='" . $location_id . "' name='locations[]'>";
					$td_bgcolor = "";
					if ($blnNoCal && !$singleUser) {
						$td_bgcolor = " bgcolor = '#000066' ";
					}
					
					$invoice_number = "<td align='left'" . $td_bgcolor . " nowrap>" . $invoice_number . "</td>";
					$invoice_date_cell = "<td align='left' class='status_view'>" . date("m/d", strtotime($dateandtime)) . "</td>";
					if ($singleUser) {
						if ($blnNoCal) {
							$nocal_indicator = "<td align='left' nowrap>NoCal</td>";
						} else {
							$nocal_indicator = "<td align='left' nowrap>&nbsp;</td>";
						}
						$time_stamp = "&nbsp;";
						//let's look up the last note on this location
						$querynote = "SELECT `time_stamp` FROM `notes` 
						WHERE location_id = '" . $location_id . "'
						ORDER BY notes_id DESC
						LIMIT 0,1";
						$resultnote = mysql_query($querynote, $this->datalink) or die("Unable to get last note time stamp");
						$numbnote = $resultnote->rowCount();
						if ($numbnote > 0) {
							$time_stamp = mysql_result($resultnote, 0, "time_stamp");
						} 
						$last_note = "<td align='left' nowrap>" . date("m/d/y g:iA", strtotime($time_stamp)) . "</td>";
						$invoice_date_cell .= $nocal_indicator . $last_note;
					}
					$invoice_number .= $invoice_date_cell;
//					echo $currentday ."!=" . $dateandtime . "=" . strlen($day_cell) . "<BR>";
					if ($y==0) {
						$arrDay = explode("-",$dateandtime);
						$that_day = $arrDay[1] . "/" . $arrDay[2] . "/" . $arrDay[0];
						//$day_cell = "<td valign='top' colspan='3'>&nbsp;</td>";
						//$assign_table .= "<tr>" . $day_cell . "</tr>";
					}
					
					if ($locations_cell=="") {	
						//<font color='white'>" . $intCounter . "</font>
						if ($singleUser) {
							$locations_cell = "<Tr><td align='left'>&nbsp;</td><td align='left' class='white_header'>Location</td><td align='left' colspan='1' class='white_header'>Assigned</td><td align='left' colspan='1' class='white_header'>NoCal</td><td align='left' colspan='1' class='white_header'>Last Note</td></tr>";
						} else {
							$locations_cell = "<Tr><td align='left'>&nbsp;</td><td align='left' class='white_header'>Location</td><td align='left' colspan='3' class='white_header'>Assigned</td></tr>";
						}
						$theclass = "class = 'row_black'";
						$rush_warning = "<td>&nbsp;</td>";
						if ($rush=="Y") {
							$theclass = "class = 'row_red'";
							$rush_warning = "<td><span class='rush_warning'>RUSH&nbsp;</span></td>";
						}
						$locations_cell .= "<tr " . $theclass . "><td align='left' nowrap><input type='hidden' value='" . $order_id . "' name='order" . $location_id . "'><input type='checkbox' name='super" . $location_id . "' value='Y' class='location_checkbox'></td>" . $invoice_number . $rush_warning . "<td align='right'>" . "<a href='clear_assign.php?id=" . $this->id . "&assignment_date=" . $dateandtime . "&location_id=" . $location_id . "&work_location=" . $this->work_location . "' title='Click to clear assignment'><span class='del_link'>del</span></a></td></tr>";
						/*
						<font color='white'>" . $intCounter . ")</font>&nbsp;" . $invoice_number;
						*/
					} else {
						//$bgcolor = "bgcolor = '#00000'";
						$theclass = "class = 'row_black'";
						if (($intCounter%2)==0) {
							//$bgcolor = "bgcolor = '#272727'";
							$theclass = "class = 'row_grey'";
						}
						if ($rush=="Y") {
							$theclass = "class = 'row_red'";
						}
						$rush_warning = "<td>&nbsp;</td>";
						if ($rush=="Y") {
							$theclass = "class = 'row_red'";
							$rush_warning = "<td><span class='rush_warning'>RUSH&nbsp;</span></td>";
						}
						//$bgcolor . 
						$locations_cell .= "<tr " . $theclass . "><td align='left' nowrap><input type='checkbox' name='super" . $location_id . "' value='Y' class='location_checkbox'></td>" . $invoice_number . $rush_warning . "<td align='right'>" . "<a href='clear_assign.php?id=" . $this->id . "&assignment_date=" . $dateandtime . "&location_id=" . $location_id . "&work_location=" . $this->work_location . "' title='Click to clear assignment'><span class='del_link'>del</span></a></td></tr>";
					}
					//$locations_cell = $y . "-" . $locations_cell;
					$y++;
				}
				//$day_cell!="" && 
				if ($locations_cell!="") {
					
					$assign_table .= $map_link . "<tr><td valign='top' colspan='3'><table border='0' width='100%' cellpadding='1' cellspacing='0'>" . $locations_cell . "</table></td></tr>";
					$day_cell = "";
					$locations_cell = "";
				}
			}
		}
		if ($assign_table!="") {
			$assign_table ="<table border='0' cellspacing='0'>" . $assign_table . "</table>";
		}
		return $assign_table;
	}
	function getCallerAssignmentsLate($id="", $status = "", $thedate = "", $sortby = "", $singleUser = false) {
		if ($id!="") {
			$this->id = $id;
		}
		$queryupdate = "select * from `user` where user_id = '$this->id'
				order by user_id desc";
		$resultupdate = MYSQL_QUERY($queryupdate, $this->datalink) or die("Unable to get user<b>$queryupdate<br>" . mysql_error());
		$numberupdate = $resultupdate->rowCount();

		if ($numberupdate>0) {
			//get the info, and then display it
			$x=0;
		
			$this->user_name = mysql_result($resultupdate,$x,'user_name'); 
			$this->user_logon = mysql_result($resultupdate,$x,'user_logon'); 
			$this->user_groups = mysql_result($resultupdate,$x,'user_groups');
			
			//get the locations
			$queryass = "select DISTINCT loc.location_id, DATEDIFF('" . date("Y-m-d") . "',dass.dateandtime) datedif, loc.order_id, dass.user_id, dass.dateandtime, loc.order_number, loc.location_number, 
			dass.status, stc.status_number, orders.rush, fac.zip as facilityzip
			from location loc
			INNER JOIN caller_assignments dass
			on loc.location_id = dass.location_id
			INNER JOIN status_current stc
			on (loc.order_id = stc.order_id
			and loc.location_id = stc.location_id)
			INNER JOIN orders ON loc.order_id = orders.order_id
			INNER JOIN companies att ON orders.attorney_id = att.company_id
			INNER JOIN companies firm ON att.firm_uuid = firm.company_uuid
			INNER JOIN notes_lastentry nlast ON loc.location_id = nlast.location_id
			INNER JOIN companies fac ON loc.facility_id = fac.company_id
			LEFT OUTER JOIN notes ON ( loc.location_id = notes.location_id
			AND `notes`.`notes` LIKE 'Letter Sent:FYI%'
			AND `notes`.`status` = 'LETTER SENT' )
			
			WHERE dass.user_id = '$this->id'
			AND `notes`.`notes_id` IS NULL
			AND firm.thirty_day_reminder = 'Y'
			AND DATEDIFF('" . date("Y-m-d") . "',dass.dateandtime) > 28
			AND loc.cancelled != 'Y'
			AND dass.`status` != 'CANCEL'";
			if ($status != "") {
				$queryass .= " and dass.status = '" .$status . "'";
			}
			if ($thedate != "") {
				$queryass .= " and dass.dateandtime = '" .$thedate . "'";
			}
			//all case
			$queryass .= " and stc.status_number < 5";
			
			//$queryass .= " order by orders.rush ASC, status_number, loc.order_id, loc.location_id";
			if ($sortby=="") {
				$queryass .= " order by dass.dateandtime";
			} else {
				$queryass .= " order by " . $sortby;
			}
			
			//echo $queryass . "<Br>";
			$resultass = MYSQL_QUERY($queryass, $this->datalink) or die("Unable to get user<b>$queryass<br>" . mysql_error());
			$numberass = $resultass->rowCount();
			$this->assignments = $numberass;

			if ($numberass>0) {
				//echo $queryass . "<br><br>";
				$y = 0;
				$assign_table = "";
				
				while ($y<$numberass) {
					$location_id = mysql_result($resultass,$y,'location_id'); 
					$user_id = mysql_result($resultass,$y,'user_id'); 
					$dateandtime = mysql_result($resultass,$y,'dateandtime'); 
					$order_id = mysql_result($resultass,$y,'order_id'); 
					$order_number = mysql_result($resultass,$y,'order_number'); 
					$location_number = mysql_result($resultass,$y,'location_number'); 
					$datedif = mysql_result($resultass,$y,'datedif'); 
					$status = mysql_result($resultass,$y,'status'); 
					$status_number = mysql_result($resultass,$y,'status_number'); 
					$rush = mysql_result($resultass,$y,'rush'); 
					$facilityzip=mysql_result($resultass,$y,"facilityzip");
					if ($facilityzip > $this->nocal_ziplimit) {
						$blnNoCal = true;
						//$location_number .= "N/C"; 
					} else {
						$blnNoCal = false;
					}
					//echo "stat:" . $status_number . "<BR>";
					$display_number = $status_number;
					 
					if ($current_status_number != $display_number) {
						$current_status_number = $display_number;
						$intCounter = 1;
					} else {
						$intCounter++;
					}
//					echo $status_number . "<BR>";
					$status_color = $this->getstatuscolor($status_number);
					//echo $order_number . "-" . $location_number . " [" . $display_number . "-" . $status_color . "<BR>";
					if ($status_color=="#FFFFFF" && date("m/d", strtotime($dateandtime))!= date("m/d")) {
						$status_color="#CCCCCC";
					}
					$invoice_number = "<a href='../orders/notes_edit.php?locid=" . $location_id . "' target='_blank'><font color='". $status_color . "'>" . $order_number . "-" . $location_number . "</font></a>
					<input type='hidden' value='" . $location_id . "' name='locations[]'>";
					$td_bgcolor = "";
					if ($blnNoCal && !$singleUser) {
						$td_bgcolor = " bgcolor = '#000066' ";
					}
					
					$invoice_number = "<td align='left'" . $td_bgcolor . " nowrap>" . $invoice_number . "</td>";
					$invoice_date_cell = "<td align='left' class='status_view'>" . date("m/d", strtotime($dateandtime)) . "</td><td align='left'>" . $datedif . "</td>";
					if ($singleUser) {
						if ($blnNoCal) {
							$nocal_indicator = "<td align='left' nowrap>NoCal</td>";
						} else {
							$nocal_indicator = "<td align='left' nowrap>&nbsp;</td>";
						}
						$time_stamp = "&nbsp;";
						//let's look up the last note on this location
						$querynote = "SELECT `time_stamp` FROM `notes` 
						WHERE location_id = '" . $location_id . "'
						ORDER BY notes_id DESC
						LIMIT 0,1";
						$resultnote = mysql_query($querynote, $this->datalink) or die("Unable to get last note time stamp");
						$numbnote = $resultnote->rowCount();
						if ($numbnote > 0) {
							$time_stamp = mysql_result($resultnote, 0, "time_stamp");
						} 
						$last_note = "<td align='left' nowrap>" . date("m/d/y g:iA", strtotime($time_stamp)) . "</td>";
						$invoice_date_cell .= $nocal_indicator . $last_note;
					}
					$invoice_number .= $invoice_date_cell;
//					echo $currentday ."!=" . $dateandtime . "=" . strlen($day_cell) . "<BR>";
					if ($y==0) {
						$arrDay = explode("-",$dateandtime);
						$that_day = $arrDay[1] . "/" . $arrDay[2] . "/" . $arrDay[0];
						//$day_cell = "<td valign='top' colspan='3'>&nbsp;</td>";
						//$assign_table .= "<tr>" . $day_cell . "</tr>";
					}
					
					if ($locations_cell=="") {	
						//<font color='white'>" . $intCounter . "</font>
						if ($singleUser) {
							$locations_cell = "<Tr><td align='left'>&nbsp;</td><td align='left' class='white_header'>Location</td><td align='left' colspan='1' class='white_header'>Assigned</td><td align='left'><strong>Days</strong></td><td align='left' colspan='1' class='white_header'>NoCal</td><td align='left' colspan='1' class='white_header'>Last Note</td></tr>";
						} else {
							$locations_cell = "<Tr><td align='left'>&nbsp;</td><td align='left' class='white_header'>Location</td><td align='left' colspan='3' class='white_header'>Assigned</td></tr>";
						}
						$theclass = "class = 'row_black'";
						$rush_warning = "<td>&nbsp;</td>";
						if ($rush=="Y") {
							$theclass = "class = 'row_red'";
							$rush_warning = "<td><span class='rush_warning'>RUSH&nbsp;</span></td>";
						}
						$locations_cell .= "<tr " . $theclass . "><td align='left' nowrap><input type='hidden' value='" . $order_id . "' name='order" . $location_id . "'><input type='checkbox' name='super" . $location_id . "' value='Y' class='location_checkbox'></td>" . $invoice_number . $rush_warning . "<td align='right'>" . "<a href='clear_assign.php?id=" . $this->id . "&assignment_date=" . $dateandtime . "&location_id=" . $location_id . "&work_location=" . $this->work_location . "' title='Click to clear assignment'><span class='del_link'>del</span></a></td></tr>";
						/*
						<font color='white'>" . $intCounter . ")</font>&nbsp;" . $invoice_number;
						*/
					} else {
						//$bgcolor = "bgcolor = '#00000'";
						$theclass = "class = 'row_black'";
						if (($intCounter%2)==0) {
							//$bgcolor = "bgcolor = '#272727'";
							$theclass = "class = 'row_grey'";
						}
						if ($rush=="Y") {
							$theclass = "class = 'row_red'";
						}
						$rush_warning = "<td>&nbsp;</td>";
						if ($rush=="Y") {
							$theclass = "class = 'row_red'";
							$rush_warning = "<td><span class='rush_warning'>RUSH&nbsp;</span></td>";
						}
						//$bgcolor . 
						$locations_cell .= "<tr " . $theclass . "><td align='left' nowrap><input type='checkbox' name='super" . $location_id . "' value='Y' class='location_checkbox'></td>" . $invoice_number . $rush_warning . "<td align='right'>" . "<a href='clear_assign.php?id=" . $this->id . "&assignment_date=" . $dateandtime . "&location_id=" . $location_id . "&work_location=" . $this->work_location . "' title='Click to clear assignment'><span class='del_link'>del</span></a></td></tr>";
					}
					//$locations_cell = $y . "-" . $locations_cell;
					$y++;
				}
				//$day_cell!="" && 
				if ($locations_cell!="") {
					
					$assign_table .= $map_link . "<tr><td valign='top' colspan='3'><table border='0' width='100%' cellpadding='1' cellspacing='0'>" . $locations_cell . "</table></td></tr>";
					$day_cell = "";
					$locations_cell = "";
				}
			}
		}
		if ($assign_table!="") {
			$assign_table ="<table border='0' cellspacing='0' cellpadding='2'>" . $assign_table . "</table>";
		}
		return $assign_table;
	}
	function getAssignmentsVertical($id="", $status = "", $thedate = "") {
		if ($id!="") {
			$this->id = $id;
		}
		$queryupdate = "select * from `user` where user_id = '$this->id'
				order by user_id desc";
		$resultupdate = MYSQL_QUERY($queryupdate, $this->datalink) or die("Unable to get user<b>$queryupdate<br>" . mysql_error());
		$numberupdate = $resultupdate->rowCount();

		if ($numberupdate>0) {
			//get the info, and then display it
			$x=0;
		
			$this->user_name = mysql_result($resultupdate,$x,'user_name'); 
			$this->user_logon = mysql_result($resultupdate,$x,'user_logon'); 
			$this->user_groups = mysql_result($resultupdate,$x,'user_groups');
			
			//get the orders
			//let's get the lien assignments
			$queryass = "select distinct ord.order_id, dass.user_id, dass.dateandtime, dass.status
			from orders ord
			INNER JOIN driver_orders dass
			on ord.order_id = dass.order_id
			INNER JOIN location loc
			on ord.order_id = loc.order_id
			INNER JOIN status_current stc
			on (loc.order_id = stc.order_id
			and loc.location_id = stc.location_id)
			where dass.user_id = '$this->id'";
			if ($status != "") {
				$queryass .= " and dass.status = '" .$status . "'";
			}
			if ($thedate != "") {
				$queryass .= " and dass.dateandtime = '" .$thedate . "'";
			}
			//special case for nocal
			if ($this->work_location=="visalia") {
				$queryass .= " and stc.status_number < 5.25";
			}
			$queryass .= " order by dass.dateandtime desc, ord.order_id";
			
			$resultords = MYSQL_QUERY($queryass, $this->datalink) or die("Unable to get user<b>$queryass<br>" . mysql_error());
			$numberords = $resultords->rowCount();
			
			//get the locations
			$queryass = "select DISTINCT loc.location_id, loc.order_id, dass.user_id, dass.dateandtime, loc.order_number, loc.location_number, 
			dass.status, stc.status_number, orders.rush
			from location loc
			INNER JOIN driver_assignments dass
			on loc.location_id = dass.location_id
			INNER JOIN status_current stc
			on (loc.order_id = stc.order_id
			and loc.location_id = stc.location_id)
			INNER JOIN orders ON loc.order_id = orders.order_id
			where dass.user_id = '$this->id'
			AND loc.cancelled != 'Y'";
			if ($status != "") {
				$queryass .= " and dass.status = '" .$status . "'";
			}
			if ($thedate != "") {
				$queryass .= " and dass.dateandtime = '" .$thedate . "'";
			}
			//special case
			if ($this->work_location=="visalia") {
				$queryass .= " and stc.status_number < 5.5";
			}
			$queryass .= " order by status_number, orders.rush, dass.dateandtime desc, loc.order_id, loc.location_id";
			//echo $queryass . "<Br>";
			$resultass = MYSQL_QUERY($queryass, $this->datalink) or die("Unable to get user<b>$queryass<br>" . mysql_error());
			$numberass = $resultass->rowCount();
			$this->assignments = $numberass;

			if ($numberass>0) {
				//echo $queryass . "<br><br>";
				$y = 0;
				$assign_table = "";
				while ($y<$numberass) {
					$location_id = mysql_result($resultass,$y,'location_id'); 
					$user_id = mysql_result($resultass,$y,'user_id'); 
					$dateandtime = mysql_result($resultass,$y,'dateandtime'); 
					$order_id = mysql_result($resultass,$y,'order_id'); 
					$order_number = mysql_result($resultass,$y,'order_number'); 
					$location_number = mysql_result($resultass,$y,'location_number'); 
					$status = mysql_result($resultass,$y,'status'); 
					$status_number = mysql_result($resultass,$y,'status_number');
					$rush = mysql_result($resultass,$y,'rush');
					//echo "stat:" . $status_number . "<BR>";
					$display_number = $status_number;
					if ($status_number=="3.00") {
						$display_number="2.00";
					}
					if ($status_number=="7.00") {
						$display_number="6.00";
					} 
					if ($current_status_number != $display_number) {
						$current_status_number = $display_number;
						$intCounter = 1;
					} else {
						$intCounter++;
					}
//					echo $status_number . "<BR>";
					$status_color = $this->getstatuscolor($status_number);
					//echo $order_number . "-" . $location_number . " [" . $display_number . "-" . $status_color . "<BR>";
					if ($status_color=="#FFFFFF" && date("m/d", strtotime($dateandtime))!= date("m/d")) {
						$status_color="#CCCCCC";
					}
					$invoice_number = "<a href='../orders/notes_edit.php?locid=" . $location_id . "' target='_blank'><font color='". $status_color . "'>" . $order_number . "-" . $location_number . "</font></a>
					<input type='hidden' value='" . $location_id . "' name='locations[]'>";
					if ($status=="IN") {
						//make it green
						$invoice_number = '<font color="#00CC00"><b>' . $invoice_number . '</b></font>';
					}
					if ($rush=="Y") {
						$invoice_number .= "&nbsp;<font color=red>R</font>"; 
					}
					$invoice_number = "<td align='left' nowrap>" . $invoice_number . "</td>";
					if ($intCounter=="1") {
						$invoice_date_cell = "<td align='left' style='color:white'>" . date("m/d", strtotime($dateandtime)) . "</td>";
					} else {
						$invoice_date_cell = "<td>&nbsp;</td>";
					}
					$invoice_number .= $invoice_date_cell;
//					echo $currentday ."!=" . $dateandtime . "=" . strlen($day_cell) . "<BR>";
					if ($y==0) {
						$arrDay = explode("-",$dateandtime);
						$that_day = $arrDay[1] . "/" . $arrDay[2] . "/" . $arrDay[0];
						$day_cell = "<td valign='top' colspan='3'><b><font color='white'>" . $that_day . "</font></b></td>";
						$assign_table .= "<tr>" . $day_cell . "</tr>";
					}
					if ($currentday != $dateandtime) {
//						echo $invoice_number . "<Br>";
						if ($day_cell!="" && $locations_cell!="") {
							$assign_table .= "<tr>" . $day_cell . "</tr>" . "<tr><td valign='top' bgcolor='#000000' colspan='3'><span align='justify'><font color='#FFFFFF'><table border='0' width='100%;>" . $locations_cell . "</table></font></span></td></tr>";
							$day_cell = "";
							$locations_cell = "";
						}
						$currentday = $dateandtime;
						$arrDay = explode("-",$dateandtime);
						$that_day = $arrDay[1] . "/" . $arrDay[2] . "/" . $arrDay[0];
						$day_cell = "<td valign='top' colspan='3'><b><font color='white'>" . $that_day . "</font></b></td>";
						//get map link
						//$map_link = "<td><a href='drivers_clearassign.php?user_id=" . $user_id . "&assignment_date=" . $dateandtime . "'>Clear</a>";
						$map_link = "<tr><td nowrap valign='top' align='left'><a href='daily_listing.php?user_id=" . $user_id . "&assignment_date=" . $dateandtime ."'>Log</a></td><td nowrap valign='top' align='left'><a href='addresses.php?user_id=" . $user_id . "&assignment_date=" . $dateandtime . "'>Get Map</a></td><td align='left'><a href='trips.php?user_id=" . $user_id . "&assignment_date=" . $dateandtime . "'>Export</a></td></tr>";
						//$day_cell .= $map_link;
					}
					if ($locations_cell=="") {
						$locations_cell = "<Tr><td colspan='4'><font color=white>";
						if ($display_number=="2.00") {
							$locations_cell .= "<strong>SERVES</strong>";
						}
						if ($display_number=="5A") {
							$locations_cell .= "<strong>READY</strong>";
						}
						if ($display_number=="5.50") {
							$locations_cell .= "<strong>READY TO PRINT</strong>";
						}
						if ($display_number=="6.00") {
							$locations_cell .= "<strong>DELIVERIES</strong>";
						}
						$locations_cell .= "</font></td></tr>";
						$locations_cell = "<Tr><td align='left'>&nbsp;</td><td align='left'><font color=white><strong>Location</strong></font></td><td align='left' colspan='2'><font color=white><strong>Assigned</strong></font></td></tr>";
						$locations_cell .= "<tr><td align='left' nowrap><input type='hidden' value='" . $order_id . "' name='order" . $location_id . "'><input type='checkbox' name='super" . $location_id . "' value='Y'><font color='white'>" . $intCounter . "</font></td>" . $invoice_number . "<td align='right'><a href='clear_assign.php?id=" . $this->id . "&assignment_date=" . $dateandtime . "&location_id=" . $location_id . "&work_location=" . $this->work_location . "' title='Click to clear assignment'><span style='color:red;font-style:italic'>clear</span></a></td></tr>";
						/*
						<font color='white'>" . $intCounter . ")</font>&nbsp;" . $invoice_number;
						*/
					} else {
						if ($intCounter==1) {
							$locations_cell .= "<tr><td colspan='4'><hr></td></tr>";
							$locations_cell .= "<tr><td colspan='4'><font color=white>";
							if ($display_number=="5A") {
								$locations_cell .= "<strong>READY</strong>";
							}
							if ($display_number=="6.00") {
								$locations_cell .= "<strong>DELIVERIES</strong>";
							}
							if ($display_number=="5.50") {
								$locations_cell .= "<strong>READY TO PRINT</strong>";
							}
							$locations_cell .= "</font></td></tr>";
						}
						$bgcolor = "bgcolor = '#00000'";
						if (($intCounter%2)==0) {
							$bgcolor = "bgcolor = '#272727'";
						}
						$locations_cell .= "<tr " . $bgcolor . "><td align='left' nowrap><input type='checkbox' name='super" . $location_id . "' value='Y'><font color='white'>" . $intCounter . "</font></td>" . $invoice_number . "<td align='right'><a href='clear_assign.php?id=" . $this->id . "&assignment_date=" . $dateandtime . "&location_id=" . $location_id . "&work_location=" . $this->work_location . "' title='Click to clear assignment'><span style='color:red;font-style:italic'>clear</span></a></td></tr>";
					}
					//$locations_cell = $y . "-" . $locations_cell;
					$y++;
				}
				if ($day_cell!="" && $locations_cell!="") {
					
					$assign_table .= $map_link . "<tr><td valign='top' bgcolor='#000000' colspan='3'><span align='justify'><font color='#FFFFFF'><table border='0' width='100%' cellpadding='2' cellspacing='0'>" . $locations_cell . "</table></font></span></td></tr>";
					$day_cell = "";
					$locations_cell = "";
				}
			}
			if ($numberords>0) {
				$z = 0;
				$intCounter=0;
				$currentday = "";
				while ($z<$numberords) {
					$order_id = mysql_result($resultords,$z,'order_id'); 
					$user_id = mysql_result($resultords,$z,'user_id'); 
					$dateandtime = mysql_result($resultords,$z,'dateandtime'); 
					$intCounter++;
					
					$status_color = "green";
					$billing_notes_link = "<td align='left'><a href='../orders/billing_notes_edit.php?id=" . $order_id . "' target='_blank'><font color='". $status_color . "'>" . $order_id . "</font></a></td>";
					$billing_notes_link .= "<td align='left'><span style='color:white;'>" . date("m/d", strtotime($dateandtime)) . "</span></td>
					<td align='right'><a href='clear_order_assign.php?id=" . $user_id . "&assignment_date=" . $dateandtime . "&order_id=" . $order_id . "&work_location=" . $this->work_location . "' title='Click to clear assignment'><span style='color:red;font-style:italic'>clear</span></a></td>";
					if ($currentday != $dateandtime) {
						if ($day_cell!="" && $locations_cell!="") {
							//$assign_table .= "<tr>" . $day_cell . "<td>" . $locations_cell . "</td></tr>";
							$assign_table .= "<tr>" . $day_cell . "</tr>" . "<tr><td valign='top' bgcolor='#000000' colspan='3'><span align='justify'><font color='#FFFFFF'><table border='0' width='100%;>" . $locations_cell . "</table></font></span></td></tr>";
							$day_cell = "";
							$locations_cell = "";
						}
						$currentday = $dateandtime;
						$arrDay = explode("-",$dateandtime);
						$that_day = $arrDay[1] . "/" . $arrDay[2] . "/" . $arrDay[0];
						//$day_cell = "<td width='10%' valign='top'><b>" . $that_day . "<br><a href='drivers_clearordsign.php?user_id=" . $user_id . "&assignment_date=" . $dateandtime . "'>Clear</a></b></td>";
						$day_cell = "<td valign='top' colspan='3'><b><font color='white'><b>" . $that_day . "</b></td>";
						//get map link
						//$map_link = "<td><a href='drivers_clearordsign.php?user_id=" . $user_id . "&assignment_date=" . $dateandtime . "'>Clear</a>";
						$map_link = "<tr><td nowrap valign='top' width='2%'><a href='daily_listing.php?user_id=" . $user_id . "&assignment_date=" . $dateandtime ."'>Log</a>&nbsp;|&nbsp;<a href='addresses.php?user_id=" . $user_id . "&assignment_date=" . $dateandtime . "'>Get Map</a><br><a href='trips.php?user_id=" . $user_id . "&assignment_date=" . $dateandtime . "'>Export</a></td></tr>";
						//$day_cell .= $map_link;
					}
					if ($locations_cell=="") {
						$locations_cell = "<tr><td colspan='4'><font color=white>";
						$locations_cell .= "<font color=white>";
						$locations_cell .= "<strong>LIENS</strong>";
						$locations_cell .= "</font>";
						$locations_cell .= "</td></tr>";
//						 . $intCounter . ")&nbsp;" . $billing_notes_link;
						$locations_cell .= "<tr><td align='left'><font color=white>" . $intCounter . "</td>" . $billing_notes_link;
					} else {
						if ($intCounter==1) {
							$locations_cell .= "<tr><td colspan='4'><font color=white>";
							$locations_cell .= "<hr>";
							$locations_cell .= "</td></tr>";
							$locations_cell .= "<font color=white>";
							$locations_cell .= "<tr><td colspan='4'><font color=white>";
							$locations_cell .= "<strong>LIENS</strong>";
							$locations_cell .= "</font>" . 
							$locations_cell .= "</td></tr>";
//							$locations_cell .= $intCounter . ")&nbsp;" . $billing_notes_link;
						}
						$locations_cell .= "<tr><td align='left'><font color=white>" . $intCounter . "</td>" . $billing_notes_link;
					}
					$z++;
				}
				//echo $day_cell . "<BR>locs:". $locations_cell . "<BR>";
				if ($day_cell!="" && $locations_cell!="") {
					
					$assign_table .= $map_link. "<tr><td valign='top' bgcolor='#000000' colspan='3'><span align='justify'><font color='#FFFFFF'><table border='0' width='100%' cellpadding='1' cellspacing='0'>" . $locations_cell . "</table></font></span></td></tr>";
					$day_cell = "";
					$locations_cell = "";
				}
			}
		}
		if ($assign_table!="") {
			$assign_table ="<table width='175px' border='0' cellspacing='0'>" . $assign_table . "</table>";
		}
		return $assign_table;
	}
	function getAssignmentsByDate($id="", $status = "", $thedate = "", $start_date = "", $end_date = "", $assigned_to = "") {
		if ($id!="") {
			$this->id = $id;
		}
		if ($this->last_week_dates!="") {
			//get the links for previous week
			$arrLastWeek = explode("|", $this->last_week_dates);
			$prev_monday = $arrLastWeek[0];
			$prev_friday = $arrLastWeek[1];
		}
		if ($this->next_week_dates!="") {
			//get the links for previous week
			$arrNextWeek = explode("|", $this->next_week_dates);
			$next_monday = $arrNextWeek[0];
			$next_friday = $arrNextWeek[1];
		}
		$queryupdate = "select * from `user` where user_id = '$this->id'
				order by user_id desc";
		$resultupdate = MYSQL_QUERY($queryupdate, $this->datalink) or die("Unable to get user<b>$queryupdate<br>" . mysql_error());
		$numberupdate = $resultupdate->rowCount();

		if ($numberupdate>0) {
			//get the info, and then display it
			$x=0;
		
			$this->user_name = mysql_result($resultupdate,$x,'user_name'); 
			$this->user_logon = mysql_result($resultupdate,$x,'user_logon'); 
			$this->user_groups = mysql_result($resultupdate,$x,'user_groups');
			
			//get the locations
			$queryass = "select DISTINCT loc.location_id, loc.order_id, 
			dass.user_id, dass.dateandtime, loc.order_number, loc.location_number, 
			dass.status, dass.assignment_type, stc.status_number, orders.rush, nlast.lastentry, notes.printstatus
			from location loc
			LEFT OUTER JOIN `notes_lastentry` nlast ON loc.location_id = nlast.location_id
			LEFT OUTER JOIN (
				SELECT DISTINCT `location_id`,`status` printstatus 
				FROM `notes`
				WHERE `notes`.`status` LIKE 'NOCAL_PRINT%'
				) notes 
			ON loc.location_id = notes.location_id
			INNER JOIN driver_assignments dass
			on loc.location_id = dass.location_id
			INNER JOIN status_current stc
			on (loc.order_id = stc.order_id
			and loc.location_id = stc.location_id)
			INNER JOIN orders ON loc.order_id = orders.order_id";
			if ($assigned_to!="") {
				$queryass .= " INNER JOIN location_driver ldriv
				ON (loc.location_id = ldriv.location_id
				AND ldriv.user_id = '" . $assigned_to . "')";
			}
			$queryass .= " WHERE dass.user_id = '$this->id'
			AND loc.cancelled != 'Y'";
			if ($status != "") {
				$queryass .= " and dass.status = '" .$status . "'";
			}
			if ($thedate != "") {
				$queryass .= " and dass.dateandtime = '" .$thedate . "'";
			}
			if ($start_date != "") {
				$queryass .= " and dass.dateandtime >= '" .$start_date . "'";
			}
			if ($end_date != "") {
				$queryass .= " and dass.dateandtime <= '" .$end_date . "'";
			}
			//special case
			if ($this->work_location=="visalia") {
				$queryass .= " and stc.status_number < 5.25
				and stc.status_number != 3";
				//or if it's been completed as 3
				//$this->work_location=="visalia" && $assignment_type=="serve" && $status_number > 3
				//$queryass .= " AND (work_location!='visalia' AND assignment_type != 'serve' AND status_number !=3)";
				//$queryass .= " and loc.location NOT IN (select location_id FROM `status_completed` WHERE status_number = 3)";
			}
			$queryass .= " order by dass.dateandtime asc, status_number, orders.rush, loc.order_id, loc.location_id";
			//echo $queryass . "<Br>";
			//flush();
			$resultass = MYSQL_QUERY($queryass, $this->datalink) or die("Unable to get user<b>$queryass<br>" . mysql_error());
			$numberass = $resultass->rowCount();
			$this->assignments = $numberass;

			if ($numberass>0) {
				//echo $queryass . "<br><br>";
				$y = 0;
				$assign_table = "";
				$arrDates = array();
				$arrLocations = array();
				while ($y<$numberass) {
					$location_id = mysql_result($resultass,$y,'location_id'); 
					$print_status = mysql_result($resultass,$y,'printstatus'); 
					$suffix = "";
					if ($print_status!="") {
						$ipos=strpos($print_status, "SERVE");
						if ($ipos!==false) {
							$suffix .= "3";
						}
						$ipos=strpos($print_status, "COPY");
						if ($ipos!==false) {
							$suffix .= "5";
						}
						$print_status = "<span style='color:white'>P". $suffix  . "</span>";
					}
					$lastentry = mysql_result($resultass,$y,'lastentry'); 
					if ($lastentry!="") {
						$lastentry = "<font color=white>(" . date("m/j", strtotime($lastentry)) . ")</font>";
					}
					$assignment_type = mysql_result($resultass,$y,'assignment_type'); 
					$arrLocations[] = $location_id;
					$user_id = mysql_result($resultass,$y,'user_id'); 
					$dateandtime = mysql_result($resultass,$y,'dateandtime'); 
					//set up the cells if any for later
					$blnReset = false;
					if (!in_array($dateandtime, $arrDates)) {
						$invoice_cells[$dateandtime] = array();
						//keep track of dates to build final table
						$arrDates[] = $dateandtime;
						$blnReset = true;
					}
					$order_id = mysql_result($resultass,$y,'order_id'); 
					$order_number = mysql_result($resultass,$y,'order_number'); 
					$location_number = mysql_result($resultass,$y,'location_number'); 
					$status = mysql_result($resultass,$y,'status'); 
					$status_number = mysql_result($resultass,$y,'status_number'); 
					$blnShowRow = true;
					if ($this->work_location=="visalia" && ($assignment_type=="serve" || $assignment_type=="delivery") && $status_number > 3) {
						$blnShowRow = false;
					}
					if ($this->work_location=="visalia" && $assignment_type=="copy" && $status_number > 5) {
						$blnShowRow = false;
					}
					//only show locations with the proper credentials
					if ($blnShowRow) {
						$rush = mysql_result($resultass,$y,'rush'); 
						//echo "stat:" . $status_number . "<BR>";
						$display_number = $status_number;
						if ($status_number=="3.00") {
							$display_number="2.00";
						}
						if ($status_number=="7.00") {
							$display_number="6.00";
						}
						//do we show a line?
						$blnShowHR = false;
						if ($current_actual_status_number != $status_number) {
							$current_actual_status_number = $status_number;
							$blnShowHR = true;
						} 
						if ($current_status_number != $display_number) {
							$current_status_number = $display_number;
							$intCounter = 1;
							$blnShowHR = true;
						} else {
							$intCounter++;
						}
	//					echo $status_number . "<BR>";
						$status_color = $this->getstatuscolor($status_number);
						//echo $order_number . "-" . $location_number . " [" . $display_number . "-" . $status_color . "<BR>";
						if ($status_color=="#FFFFFF" && date("m/d", strtotime($dateandtime))!= date("m/d")) {
							$status_color="#CCCCCC";
						}
						$invoice_number = "<a href='../orders/notes_edit.php?locid=" . $location_id . "' target='_blank'><font color='". $status_color . "'>" . $order_number . "-" . $location_number . $print_status . "</font></a>&nbsp;" . $lastentry . "
						<input type='hidden' value='" . $location_id . "' name='locations[]'>
						<input type='hidden' value='" .$status_number ."' name='status" . $location_id . "'>";
						if ($status=="IN") {
							//make it green
							$invoice_number = '<font color="#00CC00"><b>' . $invoice_number . '</b></font>';
						}
						if ($rush=="Y") {
							$invoice_number .= "&nbsp;<font color=red><strong>R</strong></font>";
						}
						$next_cell = "<tr><td align='left' nowrap><input type='hidden' value='" . $order_id . "' name='order" . $location_id . "'><input type='checkbox' name='super" . $location_id . "' value='Y' onchange='trackChecks(this)'><font color='white'>" . $intCounter . "</font></td><td align='left' nowrap";
						if ($this->search_location_id==$location_id) { 
							$next_cell .= " bgcolor='dimgray'";
						}
                        $next_cell .= ">" . $invoice_number . "</td><td align='left'>[[ASSIGN_TO_" . $location_id . "]]</td><td align='right'><a href='clear_assign.php?id=" . $this->id . "&assignment_date=" . $dateandtime . "&location_id=" . $location_id . "&work_location=" . $this->work_location . "' title='Click to clear assignment'><span style='color:red;font-style:italic'>clear</span></a></td></tr>";
						if ($blnShowHR && !$blnReset) {
							//add a line to separate status
							$next_cell = "<tr><td align='left' nowrap colspan='3'><hr></td></tr>" . $next_cell;
						}
						$invoice_cells[$dateandtime][] = $next_cell;
					}
					$y++;
				}
				//cycle through the dates to output final table
				for ($intD=0;$intD<count($arrDates);$intD++) {
					$thedate = $arrDates[$intD];
					if ($assigned_to=="") {
						$assign_table .= "<td align=center valign=top width='20%'><table cellpadding='2' cellspacing='0' border='0' width'175px'>";
						$assign_table .= "<tr><td align='center' style='font-weight:bold;color:white' colspan='3'>" . date("m/d/Y", strtotime($thedate)) . "</td></tr>";
						$assign_table .= implode("\r\n", $invoice_cells[$thedate]);
						$assign_table .= "</table></td>";
					} else {
						//specific person, only show columns with data
						if (count($invoice_cells[$thedate])>0) {
							$assign_table .= "<td align=center valign=top width='20%'><table cellpadding='2' cellspacing='0' border='0' width'175px'>";
							$assign_table .= "<tr><td align='center' style='font-weight:bold;color:white' colspan='3'>" . date("m/d/Y", strtotime($thedate)) . "</td></tr>";
							$assign_table .= implode("\r\n", $invoice_cells[$thedate]);
							$assign_table .= "</table></td>";
						}
					}
				}
			}
		}
		if ($assign_table!="") {
			//pad the table for missing days
			for ($intD=count($arrDates);$intD<5;$intD++) {
				$assign_table .= "<td>&nbsp;</td>";
			}
		}
		//<a href=\"multiassign_nocal.php?id=$this->id&work_location=" . $this->work_location . "\">multi&nbsp;assign</a>&nbsp;|&nbsp;
		if ($assign_table!="") {
			$the_links = $this->user_name . "&nbsp;(" . $this->assignments . ")&nbsp;<a href='daily_listing.php?user_id=$user_id&assignment_date=" . date("Y-m-d") . "' target='_blank'>logs</a><BR><a href=\"assign_listing_nocal.php?id=$this->id&work_location=" . $this->work_location . "\">assign</a>&nbsp;|&nbsp;<a href='javascript:displayLayer(" . chr(34) . "Notes" . chr(34) . ");'>notes</a></td>";
			//hide headers when filtering for assigned user
			$header_row = array();
			if ($assigned_to=="") {//add additional columns if not 5
				$header_row[]= "<td width='20%' align='center' style='font-weight:bold;color:white'>Monday</td>";
				$header_row[]= "<td width='20%' align='center' style='font-weight:bold;color:white'>Tuesday</td>";
				$header_row[]= "<td width='20%' align='center' style='font-weight:bold;color:white'>Wednesday</td>";
				$header_row[]= "<td width='20%' align='center' style='font-weight:bold;color:white'>Thursday</td>";
				$header_row[]= "<td width='20%' align='center' style='font-weight:bold;color:white'>Friday</td>";
			}
			$notes_row = "<tr id='lyrNotes' style='display:none'><td colspan='5' style='background-color:white'>
				<form action='drivers_notes_insert.php' enctype='multipart/form-data' id='formNotes'>
				<input type='hidden' value='' name='apply_locations' id='apply_locations' style='width:250px'>
				<input type='hidden' value='" . $start_date . "' name='monday'>
				<input type='hidden' value='" . $end_date . "' name='friday'>
					<table width='60%' cellpadding='2' cellspacing='2' border='0' align='center'>
						<tr>
							<td valign='top' align='left' width='10%'>Note:</td>
							<td valign='top' align='left' width='90%'>
								<textarea name='notesField' cols='40' rows='3'></textarea>
							</td>
						</tr>
						<tr>
							<td valign='top' align='center' colspan='2'>
								<input type='submit' name='submitNotes' value='Submit to Callback Notes'><br />
								<em>The note will be applied to all checked locations</em>
							</td>
						</tr>
					</table>
				</form>
			</td></tr>";
			
			$assign_table = "<table width='770px' cellpadding='2' cellspacing='0' border='1' style='background-color:#000000' align='center'><tr><td colspan='1' align='left' style='background-color:white'><a href='drivers_listing_nocal.php?work_location=visalia&monday=" . $prev_monday . "&friday=" . $prev_friday . "&direction=previous'><<</a></td><td colspan='3' style='background-color:white' align='center'>" . $the_links . "</td><td colspan='1' align='right' style='background-color:white'><a href='drivers_listing_nocal.php?work_location=visalia&monday=" . $next_monday . "&friday=" . $next_friday . "&direction=next'>>></a></td></tr>" . $notes_row . "</table><form name='locationform' method='post' action='../reports/superpreprint_drivers.php'><table width='770px' cellpadding='2' cellspacing='0' border='1' style='background-color:#000000' align='center'><tr>" . implode("", $header_row) . "</tr>" . $assign_table . "</table></form>";
		}
		//set the locations for the user
		$this->locations = $arrLocations;
		return $assign_table;
	}
	function locationAssigmentDate($order_id, $location_number) {
		//get the locations
			$queryass = "select loc.location_id, dass.dateandtime, dass.assignment_type, stc.status_number
			from location loc
			INNER JOIN driver_assignments dass
			on loc.location_id = dass.location_id
			INNER JOIN status_current stc
			on (loc.order_id = stc.order_id
			and loc.location_id = stc.location_id)
			where 1
			AND loc.cancelled != 'Y'";
			$queryass .= " AND loc.order_id = '" .$order_id . "'";
			$queryass .= " AND loc.location_number = '" .$location_number. "'";
			
			//special case
			if ($this->work_location=="visalia") {
				$queryass .= " and stc.status_number < 5.25
				and stc.status_number != 3
				and dass.assignment_type !='delivery'";
			}
			//echo $queryass . "<BR>";
			$resultass = mysql_query($queryass, $this->datalink) or die("Unable to get location info<b>$queryass<br>" . mysql_error());
			$numberass = $resultass->rowCount();
			$dateandtime = "";
			if ($numberass >0) {
				for ($intA=0;$intA<$numberass;$intA++) {
					$location_id = $row->location_id;
					$dateandtime = $row->dateandtime;
					$assignment_type = $row->assignment_type;
					$status_number = $row->status_number;
					$blnShowRow = true;
					if ($this->work_location=="visalia" && $assignment_type=="serve" && $status_number > 3) {
						$blnShowRow = false;
					}
					if ($this->work_location=="visalia" && $assignment_type=="copy" && $status_number > 5.25) {
						$blnShowRow = false;
					}
					//if ($location_id == "55990") {
						//echo $this->work_location. ", " . $assignment_type. ", " . $status_number . "<BR>";
					//}
					if ($blnShowRow) {
						$the_dateandtime = $dateandtime;
						$this->search_location_id = $location_id;
						break;
					}
				}
			}
			return $the_dateandtime;
	}
	function getstatuscolor($status_number) {
		$status_color="";
		switch ($status_number) {
			case "1.00":
				$status_color='#FFFFFF';
				break;
			case "2.00":
						//die("stat: ". $status_number);
				$status_color='#FFFF10';
				break;
			case "3.00":
					//die("stat: ". $status_number);
				$status_color='#ffce9c';
				break;
			case "5.00":
				$status_color='#ff0000';
				break;
			case "5A":
				$status_color='#FF00FF';
				break;
			case "5.50":
				$status_color='#00FFFF';
				break;
			case "6.00":
				$status_color='#083194';
				break;
			case "7.00":
				$status_color='#a1a1a1';
				break;
			case "8.00":
				$status_color='#f7bdde';
				break;
			case "9.00":
				$status_color='#299c39';
				break;
		}
		//die("doodah: " . $status_color);
		return $status_color;
	}
	function getattribute($table, $attribute, $blnShowall = false) {
		//get a user sub value (ie: address), by table and by attribute
		$querycomp = "select `". $table . "_uuid`
		from `user_". $table . "` 
		where 1 ";
		if ($this->uuid!="") { 
			$querycomp .= " 
			AND `user_uuid` = :user_uuid";
		}
		if ($attribute != "") {
			$querycomp .= " 
			AND `attribute` = :attribute";
		}
		//echo "$querycomp<br>";
		//$resultcomp = MYSQL_QUERY($querycomp,$this->datalink) or die ("Unable to get the uuid<br>$querycomp<br>" . mysql_error());
		//$numberupdate = $resultcomp->rowCount();
		$user_uuid = $this->uuid;
		try {
			$sql = $querycomp;
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("user_uuid", $user_uuid);
			if ($attribute!="") {
				$stmt->bindParam("attribute", $attribute);
			}
			$stmt->execute();
			$attributes = $stmt->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		$numberupdate = count($attributes);
		if ($numberupdate > 0) {
			if ($numberupdate==1 && !$blnShowall) {
				//get the uuid
				$uuid = $attributes[0]->{$table . "_uuid"};
				return $uuid;
			} else {
				return $attributes;
			}	
		}
	}
	function users_info() {
		//payrate
		$querypayrate = "select `user`.* , `payrate`.base_payrate, `overtime`.hours_day, `overtime`.hours_week, `overtime`.start_date overtime_start_date, `overtime`.end_date overtime_end_date
		from `user` 
		left outer join `payrate`
		on (`user`.`user_id` = `payrate`.`user_id` AND `payrate`.type = 'standard')
		left outer join `overtime`
		ON `user`.`user_id` = `overtime`.`user_id`
		where user_logon = '" . $this->user_logon . "'
		order by user_id desc";

		try {
			$sql = $querypayrate;
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			//$stmt->bindParam("user", $user_logon);
			$stmt->execute();
			$users_info = $stmt->fetchObject();
			
			return $users_info;
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
	}
}
