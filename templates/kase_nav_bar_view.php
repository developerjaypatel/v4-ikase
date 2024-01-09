<?php
require_once('../shared/legacy_session.php');
if (!isset($_SESSION['user_data_path'])) {
	$_SESSION['user_data_path'] = '';
}
session_write_close();

include ("../api/connection.php");
include ("../browser_detect.php");

$sql = "SELECT Count(DISTINCT ccase.case_id) as cnt
	FROM cse_case  ccase
	INNER JOIN cse_personal_injury cpi
	ON ccase.case_id = cpi.case_id
	
	LEFT OUTER JOIN cse_case_person cpers
	ON ccase.case_uuid = cpers.case_uuid AND cpers.attribute = 'main' AND cpers.deleted = 'N'
	LEFT OUTER JOIN cse_person pers
	ON cpers.person_uuid = pers.person_uuid
	
	LEFT OUTER JOIN cse_case_corporation cplaint
	ON ccase.case_uuid = cplaint.case_uuid AND cplaint.attribute = 'plaintiff' AND cplaint.deleted = 'N'
	LEFT OUTER JOIN cse_corporation plaintiff
	ON cplaint.corporation_uuid = plaintiff.corporation_uuid
	
	INNER JOIN cse_case_corporation cdef
	ON ccase.case_uuid = cdef.case_uuid AND cdef.attribute = 'defendant' AND cdef.deleted = 'N'
	INNER JOIN cse_corporation defendant
	ON cdef.corporation_uuid = defendant.corporation_uuid
	
	WHERE 1
	AND ccase.customer_id = :customer_id
	AND ccase.case_status NOT LIKE '%close%' AND ccase.case_status NOT LIKE 'CL-%' AND ccase.case_status NOT LIKE 'CLOSED%' AND ccase.case_status NOT LIKE 'Sub%' AND ccase.case_status != 'DROPPED' AND ccase.case_status != 'REJECTED'
	AND (
		INSTR(ccase.case_type, 'Personal Injury') > 0
		OR
		ccase.case_type = 'NewPI'
	)
	AND cpi.statute_limitation >= '" . date("Y-m-01") . "'
	GROUP BY YEAR(cpi.statute_limitation), MONTH(cpi.statute_limitation)
	ORDER BY YEAR(cpi.statute_limitation), MONTH(cpi.statute_limitation)";
	//#AND ccase.case_id = 9414
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
	$stmt->execute();
	$statutescount = $stmt->fetchAll(PDO::FETCH_OBJ);

    foreach($statutescount as $index=>$statutecount){
	if($index == 0) {
		$first_count = $statutecount->cnt;
	}
    if($index == 1) {
		$second_count = $statutecount->cnt;
	}
    }

$blnIPad = isPad();

$blnNewLayout = true;	//($_SESSION['user_plain_id']==2 || $_SESSION['user_plain_id']==1288 || $_SESSION['user_plain_id']==29 || $_SESSION['user_plain_id']==670);
$blnShowRate = true;	//($_SESSION['user_customer_id']==1033);

$arrCalendars = array();

$sql = "SELECT *
		FROM cse_calendar 
		WHERE 1
		AND customer_id = '" . $_SESSION['user_customer_id'] . "'
		ORDER by sort_order";
		
$db = getConnection();

try {
	$stmt = $db->query($sql);
	$customer_calendars = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
	$error = array("error nav"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

$blnCustomCalendars = false;
foreach($customer_calendars as $customer_calendar) {
	//may have to skip the personal kalendar
	if (isset($_SESSION['personal_calendar'])) {
		if ($_SESSION['personal_calendar']!="Y") {
			if ($customer_calendar->sort_order==5) {
				continue;
			}
		} else {
			if ($customer_calendar->sort_order==5) {
				//rename the calendar with their initials
				$customer_calendar->calendar = str_replace("Employee", "Personal", $customer_calendar->calendar) . "&nbsp;(" . strtoupper($_SESSION['user_nickname']) . ")";
			}
		}
	} else {
		if ($customer_calendar->sort_order==5) {
			continue;
		}
	}

	//the mandatory calendars will be first, then all others.
	if (!$blnCustomCalendars) {
		if ($customer_calendar->mandatory=="N") {
			$blnCustomCalendars = true;
			$menu_item = '<li class="divider"><hr /></li>';
			$arrCalendars[] = $menu_item;
		}
	}
	$disabled = "";
	$link_color = "color:black";
	if ($customer_calendar->active=="N") {
		$disabled = "disabled";
		$link_color = ";color:#EDEDED";
		continue;
	}
	$class = "";
	$menu_item = '<li class="kases-list-menu' . $class . '" style="text-align:left; width:100%">';
				//build the drop down
				$menu_item .= '
				<a href="#ikalendar/' . $customer_calendar->calendar_id . '/' . $customer_calendar->sort_order . '" style="text-align:left; width:100%' . $link_color . '" target="">' . $customer_calendar->calendar . '</a>';
				//firm calendar gets special love
				if ($customer_calendar->sort_order == 0) {	
					$menu_item .= '<div style="margin-top:-10px; margin-left:20px"><table width="100%">';
					$link_color = "color:black; font-size:0.8em";
					$menu_item .= '<tr>';
					$menu_item .= '<td><a href="#listkalendar/' . $customer_calendar->calendar_id . '/0/' . date("Y-m") . '-01/' . date("Y-m-t") . '" style="' . $link_color . '" title="Click to open calendar List view">List</a>&nbsp;|&nbsp;</td>';
					$menu_item .= '<td><a href="#listkalendar/' . $customer_calendar->calendar_id . '/-1/' . date("Y-m-d") . '/' . date("Y-m-d") . '" style="' . $link_color . '" title="Click to open calendar in Day List view">Day</a>&nbsp;|&nbsp;</td>';
					$menu_item .= '<td><a href="#ikalendar/' . $customer_calendar->calendar_id . '/-2" style="' . $link_color . '" title="Click to open calendar in Week view">Week</a>&nbsp;|&nbsp;</td>';
					$menu_item .= '<td><a href="#ikalendar/' . $customer_calendar->calendar_id . '/' . $customer_calendar->sort_order . '" style="' . $link_color . '" target="_blank" title="Click to open calendar in new window">New&nbsp;Window</a>&nbsp;|&nbsp;</td>
					<td><a id="refresh_firm_calendar" style="' . $link_color . '; cursor:pointer" title="Click to refresh Firm events"><i class="glyphicon glyphicon-refresh"></i></a></td></tr></table></div>';
				}
	$menu_item .= '</li>';
	$arrCalendars[] = $menu_item;
	
	$blnCalendarByCaseType = ($_SESSION["user_customer_id"]==1075 || $_SESSION["user_customer_id"]==1121);
	
	if ($blnCalendarByCaseType) {
		if ($customer_calendar->sort_order == 0) {	
			$menu_item = '<li class="kases-list-menu" style="text-align:left; width:100%">';
			$menu_item .= '
			<a href="#firmkalendar/wcab" style="text-align:left; width:100%" target="">WCAB Calendar</a>';
			$menu_item .= '</li>';
			$arrCalendars[] = $menu_item;
			
			$menu_item = '<li class="kases-list-menu" style="text-align:left; width:100%">';
			$menu_item .= '
			<a href="#firmkalendar/pi" style="text-align:left; width:100%" target="">PI Calendar</a>';
			$menu_item .= '</li>';
			$arrCalendars[] = $menu_item;
		}
	}
}

$menu_item = '<li class="employee_calendar_holder divider" style="display:"><hr /></li>';
$arrCalendars[] = $menu_item;
$link_color = "color:black";

//employee calendar	
/*
	$menu_item = '<li class="employee_calendar_holder kases-list-menu' . $class . '" style="text-align:left; width:220px; display:">
				<div style="padding-left:20px"><div style="display:inline-block"><a href="#employee_kalendar" style="text-align:left; width:100%;' . $link_color . '">Employee Calendar</a></div></div></li>';
	$arrCalendars[] = $menu_item;
}
*/
if (strtolower($_SESSION['user_data_path'])=="a1") {
	$menu_item = '<li class="partner_calendar_holder divider" style="display:none"><hr /></li>';
	$arrCalendars[] = $menu_item;
	$link_color = "color:black";
	
	//partner
	$menu_item = '<li class="partner_calendar_holder kases-list-menu' . $class . '" style="text-align:left; width:220px; display:none">
				<div style="padding-left:20px"><div style="display:inline-block"><a href="#partner_calendar" style="text-align:left; width:100%' . $link_color . '">Partner Calendar</a></div></div></li>';
	$arrCalendars[] = $menu_item;
}
//let's get the names of the calendars we've been assigned to, and their id
$sql = "SELECT CONCAT(user.user_first_name, ' ', user.user_last_name) name, `user`.user_id id, IF(ISNULL(cuc.user_uuid), 0, 1) assigned, IFNULL(cuc.attribute, '') permissions
	FROM `cse_user` user 
	INNER JOIN cse_user_calendar cuc
	ON (cuc.deleted = 'N' AND user.user_uuid = cuc.calendar_uuid 
	AND cuc.user_uuid IN (SELECT user_uuid FROM cse_user WHERE user_id = " . $_SESSION['user_plain_id'] . " AND customer_id = " . $_SESSION['user_customer_id'] . "))
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
//die($sql);
try {
	$stmt = $db->query($sql);
	$personal_calendars = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
	$error = array("error NAV 2"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
//die(print_r($personal_calendars));
$blnPersonalCalendars = false;
foreach($personal_calendars as $personal_calendar) {			
	//rename the calendar with their initials
	$personal_calendar->name = $personal_calendar->name . " Kalendar";

	//the mandatory calendars will be first, then all others.
	if (!$blnPersonalCalendars) {
		$blnPersonalCalendars = true;
		$menu_item = '<li class="divider"><hr /></li>';
		$arrCalendars[] = $menu_item;
	}
	$disabled = "";
	$link_color = "";
	
	$menu_item = '<li class="kases-list-menu" style="text-align:left; width:100%">
				<a href="#userkalendar/' . $personal_calendar->id . '" style="text-align:left; width:100%' . $link_color . '">' . ucwords($personal_calendar->name) . '</a>
			  </li>';
	$arrCalendars[] = $menu_item;
}

$blnShowCourtCalendar = true;
//special case for Dordulian per Steve 7/11/17
if ($_SESSION["user_customer_id"]==1075) {
  $blnShowCourtCalendar = false;
  //steve and tracy only
  if ($_SESSION["user_plain_id"]=='660' || $_SESSION["user_plain_id"]=='669') {
	  $blnShowCourtCalendar = true;
  }
}

$blnAdmin = (strpos($_SESSION['user_role'], "admin")!==false);
$blnShowEmployeeReports = (($_SESSION['user_role']=="masteradmin" || $_SESSION['user_job']=="attorney") && !$blnIPad);

if (!$blnShowEmployeeReports) {
	//let's notify the check request authorizer
	$sql = "SELECT COUNT(user_id) user_count
	FROM ikase.cse_user
	WHERE INSTR(adhoc, '\"employee_reports\":\"Y\"') > 0
	AND user_id = :user_id
	AND customer_id = :customer_id";
	
	$customer_id = $_SESSION['user_customer_id'];
	$user_id = $_SESSION['user_plain_id'];
	
	$db = getConnection();
	$stmt = $db->prepare($sql);  	
	$stmt->bindParam("user_id", $user_id);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$check_user =  $stmt->fetchObject();
	
	$blnShowEmployeeReports = ($check_user->user_count == 1 && !$blnIPad);
}

if ($blnAdmin) {
	$blnShowCourtCalendar = true;
	$blnShowEmployeeReports = true;
}

//however, might be blocked anyway
if ($blnShowEmployeeReports) {
	//let's notify the check request authorizer
	$sql = "SELECT COUNT(user_id) user_count
	FROM ikase.cse_user
	WHERE INSTR(adhoc, '\"employee_reports_block\":\"Y\"') > 0
	AND user_id = :user_id
	AND customer_id = :customer_id";
	
	$customer_id = $_SESSION['user_customer_id'];
	$user_id = $_SESSION['user_plain_id'];
	
	$db = getConnection();
	$stmt = $db->prepare($sql);  	
	$stmt->bindParam("user_id", $user_id);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$check_user =  $stmt->fetchObject();
	
	$blnShowEmployeeReports = !($check_user->user_count == 1);
}
//$_SESSION['user_role']=="admin" || 
$blnCheckApproval = ($_SESSION['user_role']=="masteradmin" && !$blnIPad);
if (!$blnCheckApproval) {
	//let's notify the check request authorizer
	$sql = "SELECT COUNT(user_id) user_count
	FROM ikase.cse_user
	WHERE INSTR(adhoc, '\"checkrequest\":\"Y\"') > 0
	AND user_id = :user_id
	AND customer_id = :customer_id";
	
	$customer_id = $_SESSION['user_customer_id'];
	$user_id = $_SESSION['user_plain_id'];
	
	$db = getConnection();
	$stmt = $db->prepare($sql);  	
	$stmt->bindParam("user_id", $user_id);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$check_user =  $stmt->fetchObject();
	
	$blnCheckApproval = ($check_user->user_count == 1 && !$blnIPad);
}

$blnAccountsEdit = ($_SESSION['user_role']=="masteradmin" && !$blnIPad);
if (!$blnAccountsEdit) {
	//let's notify the check request authorizer
	$sql = "SELECT COUNT(user_id) user_count
	FROM ikase.cse_user
	WHERE INSTR(adhoc, '\"access_accounts\":\"Y\"') > 0
	AND user_id = :user_id
	AND customer_id = :customer_id";
	
	$customer_id = $_SESSION['user_customer_id'];
	$user_id = $_SESSION['user_plain_id'];
	
	$db = getConnection();
	$stmt = $db->prepare($sql);  	
	$stmt->bindParam("user_id", $user_id);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$check_user =  $stmt->fetchObject();
	
	$blnAccountsEdit = ($check_user->user_count == 1 && !$blnIPad);
}
?>
<div class="navbar-header" style="display:">
    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
      <span class="sr-only">Toggle navigation</span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </button>
    <a id="home_button" class="navbar-brand" style="cursor:pointer" title="<%=customer_id %> - <%=dbname %> - <%=customer_name %> - <%=login_username %>"><img src="img/favicon.png" width="18" height="18" alt="iKase">&nbsp;Home </a>
    <?php if(!$blnMobile) { ?>
    	<a id="left_side_show" style="cursor:pointer; display:none; position:absolute;" onClick="showLeftSide();"><i style="font-size:1.1em;color:#FFFFFF" class="glyphicon glyphicon-chevron-right" title="Click to show Recent kases"></i></a><a id="left_side_hide" style="cursor:pointer; display:none; position:absolute;" onClick="hideLeftSide();"><i style="font-size:1.1em;color:#FFFFFF" class="glyphicon glyphicon-chevron-left" title="Click to hide Recent kases"></i></a>
    <?php } ?>
</div>

<div class="collapse navbar-collapse">
	<div style="float:right; margin-top:7px" class="white_text">
	    <span id="nav_customer_name" style="font-size:1.3em">
    		<%=customer_name %>
        </span>
        <br />
        Welcome <%=login_username %>
    	&nbsp;&nbsp;
        <a href='javascript:location.reload(true);' title="Click to reload iKase and empty browser cache">Reload</a>
        &nbsp;|&nbsp;
        <a href='v8.php?n=' title="Click to open iKase in a new" target="_blank">New Window</a>
    </div>
    <ul class="nav navbar-nav">
        <!--
        <li><a href="#about">About</a></li>
        <li><a href="#contact">Contact</a></li>
        -->
        <li class="kases-list-menu kases_main dropdown">
        <div id="intake_indicator" class="intake_indicator" style="cursor:pointer; position:absolute; z-index:1039; left:0px; top:5px; font-size:0.75em; background:rgb(0, 102, 255); color:white; border:1px solid white; height:16px; width:18px; text-align:center; vertical-align:bottom; display:none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; margin-bottom:10px" title="Click to review Pending Intakes"></div>
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Kases <b class="caret"></b></a>
            <ul class="dropdown-menu" style="width:100%">
                <li class="kases-list-menu" style="text-align:left">
                	<a id="new_kase" style="width:100%; cursor:pointer">New Kase</a>
                </li> 
				<% if (blnIntakeScreen) { %>
                <li class="kases-list-menu" style="text-align:left">
                	<a id="intake_kase" style="width:100%; cursor:pointer">Phone Intake</a>
                </li> 
                <li class="kases-list-menu" style="text-align:left"><a href="#intakes" style="width:100%">List Intakes</a></li> 
                <li class="divider"></li>
                <% } %>
				<li class="kases-list-menu" style="text-align:left"><a href="#recentkases" style="width:100%">Recent Kases</a></li>  
				<li class="kases-list-menu" style="text-align:left"><a id="active_kases" style="width:100%; cursor:pointer">Active Kases</a></li>
                <li class="kases-list-menu" style="text-align:left"><a id="closed_kases" style="width:100%; cursor:pointer">Closed Kases</a></li>
                
                <!--
                <li class="kases-list-menu" style="text-align:left"><a href="#kasesw" style="width:100%">WCAB Kases</a></li>  
                <li class="kases-list-menu" style="text-align:left"><a href="#kasespi" style="width:100%">PI Kases</a></li>  
                -->
                <li class="kases-list-menu" style="text-align:left"><a id="search_kases" style="width:100%; cursor:pointer">Adv Search Kases</a></li> 
                <li class="kases-list-menu" style="text-align:left"><a id="show_reports" style="width:100%; cursor:pointer">Kase Reports</a></li>  
                <li class="divider"></li>
                <li class="kases-list-menu" style="text-align:left"><a href="#workflows" style="width:100%; cursor:pointer">Workflows</a></li> 
                <li class="divider"></li>
                <li class="kases-list-menu" style="text-align:left"><a id="eams_import" style="width:100%; cursor:pointer">EAMS Import</a></li> 
                <!--remote search eams cases -->
                <li class="kases-list-menu" style="text-align:left"><a id="eams_search" href="#eams_search" style="width:100%">EAMS Case Search</a></li>
                <li class="kases-list-menu" style="text-align:left"><a id="eams_lookup" href="api/request_eams_information.php" style="width:100%" target="_blank">EAMS Case Lookup</a></li>
                
                <!--
                <li class="divider"></li>
                <li class="dropdown-header">Nav header</li>
                <li><a href="#">Separated link</a></li>
                <li><a href="#">One more separated link</a></li>
              -->
            </ul>
        </li>
       <?php 
	   $document_label = "Documents";
	   $indicator_left = "95";
	   if ($_SESSION['user_customer_id']==1049) {
		   $document_label = '<span style="font-size:0.9em">Mail/Documents</span>';
		   $indicator_left = "105";
	   }
	   ?>
       <li class="kases-list-menu" style="text-align:left;">
	        <a class="navbar-brand" href="#rolodex">Rolodex</a>
        </li>
        <% if (blnShowInvoiceMenu) { %>
        <li class="kases-new-menu dropdown">
        	<?php if ($blnCheckApproval) { ?>
            <div id="checkrequest_indicator2" class="checkrequest_indicator" style="cursor:pointer; position:absolute; z-index:1039; left:0px; top:5px; font-size:0.75em; background:rgb(0, 102, 255); color:white; border:1px solid white; height:16px; width:18px; text-align:center; vertical-align:bottom; display:none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; margin-bottom:10px" title="Click to review Check Requests"></div>
            <?php } ?>
            <div id="my_late_checkrequest_indicator2" class="my_late_checkrequest_indicator" style="cursor:pointer; position:absolute; z-index:1039; left:0px; top:32px; font-size:0.75em; background:red; color:white; border:1px solid white; height:16px; width:18px; text-align:center; vertical-align:bottom; display:none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; margin-bottom:10px" title="Click to review late Check Requests Approval"></div>
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Accounts<b class="caret"></b></a>
            <ul class="dropdown-menu" style="width:230px">
            	<?php if ($blnCheckApproval) { ?>
	            <li class="kases-list-menu" style="text-align:left; width:100%; padding-bottom:5px">
                	<div style="float:right; color:black; padding: 1px"><span id="checkrequest_trust_indicator" class="checkrequest_trust_indicator" style="background:white; color:black; padding-left:2px; padding-right:2px; font-weight:bold; display:none" title="Pending Requests"></span></div>
                    <a href="#pendingrequests/trust" style="display:unset" title="Click to review Pending Trust Requests">Pending Trust Requests</a>
                </li>
                <li class="kases-list-menu" style="text-align:left; width:100%; padding-bottom:5px">
                	<div style="float:right; color:black; padding: 1px"><span id="checkrequest_operating_indicator" class="checkrequest_operating_indicator" style="background:white; color:black; padding-left:2px; padding-right:2px; font-weight:bold; display:none" title="Pending Requests"></span></div>
                    <a href="#pendingrequests/operating" style="display:unset" title="Click to review Pending Cost Trust Requests">Pending Cost Trust Requests</a>
                </li>
                <li class="kases-list-menu" style="text-align:left; width:100%; padding-bottom:5px">
                	<a href="#checkrequests/approved" style="display:unset" title="Click to review Approved Check Requests">Approved Requests</a>
                </li>
                <li class="kases-list-menu" style="text-align:left; width:100%; padding-bottom:5px">
                	<a href="#checkrequests/denied" style="display:unset" title="Click to review Denied Check Requests">Denied Check Requests</a>
                </li>
                <li class="kases-list-menu" style="text-align:left; width:100%; padding-bottom:5px">
                	<a id="new_checkrequest" style="display:unset; cursor:pointer" title="Click to add a Check Request">Request Check</a>
                </li>
                <?php } ?>
                <li class="kases-list-menu" style="text-align:left; width:100%; padding-bottom:5px">
                	<div style="float:right; color:black; padding: 1px"><span id="my_checkrequest_indicator" class="my_checkrequest_indicator" style="background:white; color:black; padding-left:2px; padding-right:2px; font-weight:bold; display:none" title="My Pending Requests"></span></div>
                	<a href="#checkrequests/mine" style="display:unset" title="Click to review your Check Requests">My Check Requests</a>
                </li>
                <li class="kases-list-menu" style="text-align:left; width:100%; padding-bottom:5px">
                	<div style="float:right; color:black; padding: 1px"><span id="my_late_checkrequest_indicator" class="my_late_checkrequest_indicator" style="background:white; color:black; padding-left:2px; padding-right:2px; font-weight:bold; display:none" title="My Late Requests"></span></div>
                	<a href="#checkrequests/mine" style="display:unset" title="Click to review your Check Requests">My Late Requests</a>
                </li>
                <?php if ($_SESSION["user_customer_id"]=="1121") { ?>
                <li class="divider"></li>
            	<li class="kases-list-menu" style="text-align:left; width:100%">
                    <a href="#checkrequests/all" style="cursor:pointer">All Check Requests</a>
                </li>
                <?php } ?>
                <?php if ($blnCheckApproval) { ?>
                <li class="divider"></li>
            	<li class="kases-list-menu" style="text-align:left; width:100%">
                	<div style="float:right; color:black; padding: 1px"><span id="uncleared_indicator" class="uncleared_indicator" style="background:white; color:black; padding-left:2px; padding-right:2px; font-weight:bold; display:none" title="Not Yet Cleared Checks"></span></div>
                    <a href="#checks/uncleared" id="uncleared_checks" style="cursor:pointer; display:unset" title="Click to list all checks that have not yet been cleared">Uncleared Checks</a>
                </li>
                <li class="kases-list-menu" style="text-align:left; width:100%">
                	<div style="float:right; color:black; padding: 1px"><span id="cleared_indicator" class="cleared_indicator" style="background:white; color:black; padding-left:2px; padding-right:2px; font-weight:bold; display:none; margin-right:-25px" title="Cleared Checks Count"></span></div>
                    <a href="#checks/cleared" id="cleared_checks" style="cursor:pointer; display:unset" title="Click to list all checks that have been cleared">Cleared Checks</a>
                </li>
                <li class="divider"></li>
            	<li class="kases-list-menu" style="text-align:left; width:100%">
                	<div style="float:right; color:black; padding: 1px"><span id="unprinted_indicator" class="unprinted_indicator" style="background:white; color:black; padding-left:2px; padding-right:2px; font-weight:bold; display:none" title="Not Yet Printed Checks"></span></div>
                    <a href="#checks/unprinted" id="unprinted_checks" style="cursor:pointer; display:unset" title="Click to list all checks that have not yet been printed">Unprinted Checks</a>
                </li>
                <li class="kases-list-menu" style="text-align:left; width:100%">
                	<div style="float:right; color:black; padding: 1px"><span id="printed_indicator" class="printed_indicator" style="background:white; color:black; padding-left:2px; padding-right:2px; font-weight:bold; display:none; margin-right:-25px" title="Printed Checks Count"></span></div>
                    <a href="#checks/printed" id="printed_checks" style="cursor:pointer; display:unset" title="Click to list all checks that have been printed">Printed Checks</a>
                 </li>
                <?php } ?>
                <li class="divider"></li>
            	<li class="kases-list-menu" style="text-align:left; width:100%">
                    <a id="newinvoice" style="cursor:pointer">New Invoice</a>
                </li>
                <li class="kases-list-menu" style="text-align:left; width:100%; padding-bottom:5px">
                	<div style="float:right; color:black; padding: 1px"><span id="billables_indicator" class="billables_indicator" style="background:white; color:black; padding-left:2px; padding-right:2px; font-weight:bold; display:none"></span></div>
                    <a href="#billables" style="display:unset; width:100%; cursor:pointer">Billables</a>
                </li>
                <li class="kases-list-menu" style="text-align:left; width:100%; padding-bottom:5px">
                	<div style="float:right; color:black; padding: 1px"><span id="outstanding_invoices_indicator" class="outstanding_invoices_indicator" style="background:white; color:black; padding-left:2px; padding-right:2px; font-weight:bold; display:none"></span></div>
                    <a href="#accounts/receivable" style="display:unset">Outstanding Invoices</a>
                </li>
                 <li class="kases-list-menu" style="text-align:left; width:100%; padding-bottom:5px">
                 	<div style="float:right; color:black; padding: 1px"><span id="paid_invoices_indicator" class="paid_invoices_indicator" style="background:white; color:black; padding-left:2px; padding-right:2px; font-weight:bold; display:none"></span></div>
                    <a href="#accounts/paid" style="display:unset">Paid Invoices</a>
                </li>
                <li class="kases-list-menu" style="text-align:left; width:100%; padding-bottom:5px">
                	<div style="float:right; color:black; padding: 1px"><span id="prebill_invoices_indicator" class="prebill_invoices_indicator" style="background:white; color:black; padding-left:2px; padding-right:2px; font-weight:bold; display:none"></span></div>
                    <a href="#accounts/prebill" style="display:unset">Pre-Bill Invoices</a>
                </li>
                <li class="divider"></li>
                <li class="kases-list-menu" style="text-align:left; width:100%">
                    <a id="invoices_templates" style="width:100%; cursor:pointer">Invoice Templates</a>
                </li>
                <?php if ($blnShowRate) { ?>
                <li class="kases-list-menu" style="text-align:left; width:100%">
                    <a id="show_rate" style="width:100%; cursor:pointer">Activity Duration Schedule</a>
                </li>
                <?php } ?>
                <?php if ($blnAccountsEdit) { ?>
                <li class="divider"></li>
                <li class="kases-list-menu" style="text-align:left; width:100%">
                    <a href="#bankaccount/list/operating">Cost Account<span id="operating_account_indicator"></span></a>
                </li>
                <li class="kases-list-menu" style="text-align:left; width:100%">
                    <a href="#bankaccount/list/trust">Trust Account<span id="trust_account_indicator"></span></a>
              </li>
                <?php } ?>
                
            </ul>
        </li>
        <% } %>
        <li class="kases-new-menu dropdown">
        	<div id="unassigned_indicator" class="unassigned_indicator" style="cursor:pointer; position:absolute; z-index:1039; left:0px; top:5px; font-size:0.75em; background:rgb(0, 102, 255); color:white; border:1px solid white; height:16px; width:18px; text-align:center; vertical-align:bottom; display:none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; margin-bottom:10px" title="Click to review recently uploaded Unassigned documents"></div>
            
            <div id="orphan_unassigned_indicator" style="cursor:pointer; position:absolute; z-index:1039; left:0px; top:32px; font-size:0.75em; background:yellow; color:red; border:1px solid red; height:16px; width:18px; text-align:center; vertical-align:bottom; display:none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; margin-bottom:10px" title="Click to review inattended uploaded Unassigned documents"></div>
            
            <div id="new_import_indicator" class="new_import_indicator" style="cursor:pointer; position:absolute; z-index:1039; left:<?php echo $indicator_left; ?>px; top:5px; font-size:0.75em; background:white; color:black; border:1px solid white; height:16px; width:18px; text-align:center; vertical-align:bottom; display:none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; margin-bottom:10px" title="Click to review recently uploaded Scans"></div>
            
            <div id="orphan_import_indicator" style="cursor:pointer; position:absolute; z-index:1039; left:<?php echo $indicator_left; ?>px; top:32px; font-size:0.75em; background:white; color:red; border:1px solid red; height:16px; width:18px; text-align:center; vertical-align:bottom; display:none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; margin-bottom:10px" title="Click to review inattended uploaded Scans"></div>
            
            <div class="notifications_indicator" id="notifications_indicator" style="cursor:pointer; position:absolute; z-index:1039; left:<?php echo $indicator_left; ?>px; top:32px; font-size:0.75em; background:rgb(0, 102, 255); color:white; border:1px solid white; height:16px; width:18px; text-align:center; vertical-align:bottom; display:none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; margin-bottom:10px" title="Click to review documents assigned to you"></div>
            
        	<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $document_label; ?><b class="caret"></b></a>
            <ul class="dropdown-menu" style="width:190px">
                <li class="kases-list-menu" style="text-align:left; width:100%">
                <a href="#unassigned" style="width:100%">Upload Unassigned</a>
                </li>
                <li class="kases-list-menu" style="text-align:left; width:100%">
                	<div style="float:right; color:black; padding: 1px"><span id="unassigned_indicator2" class="unassigned_indicator" style="background:white; color:black; padding-left:2px; padding-right:2px; font-weight:bold; display:none"></span></div>
                	<a href="#unassigneds" style="width:100%; display:unset">List Unassigned</a>
                </li>
                <li class="divider"></li>
                <?php //if ($_SESSION['user_customer_id']!=1033) { ?>
                <li class="kases-list-menu" style="text-align:left; width:100%">
                <a href="#import" style="width:100%" title="Click here to process a stack of documents via batchscan">Batchscan</a>
                </li>
                <?php /*} else { ?>
                <li class="kases-list-menu" style="text-align:left; width:100%">
                Demo Batchscan only on Azure
                </li>
                <?php }*/ ?>
                <li class="kases-list-menu" style="text-align:left; width:100%">
                	<div style="float:right; color:black; padding: 1px"><span id="new_import_indicator2" class="new_import_indicator" style="background:white; color:black; padding-left:2px; padding-right:2px; font-weight:bold; display:none"></span></div>
	                <a href="#imports" style="width:100%; display:unset" title="Click here to review the extracted documents via batschscan operations">List Extracts</a>
                </li>
                <li class="kases-list-menu" style="text-align:left; width:100%">
                <a href="#batchscans" style="width:100%" title="Click here to review the original full length batchscan documents">List Batchscans</a>
                </li>
                <?php if ($_SESSION['user_customer_id']!=1033) { ?>
                <li class="kases-list-menu" style="text-align:left; width:100%">
                <a href="#batchscans/dateassigned/<?php echo date("Y-m") . "-01"; ?>/<?php echo date("Y-m-t"); ?>" style="width:100%" title="Click here to review the assigned batschscan documents">Assigned Batchscans</a>
                </li>
                <?php } ?>
                <li class="divider"></li>
                <li class="kases-list-menu" style="text-align:left; width:100%">
                	<a id="letter_templates" style="width:100%; cursor:pointer">Letter Templates</a>
                </li>
                <li class="kases-list-menu" style="text-align:left; width:100%">
                <a id="search_documents" style="width:100%; cursor:pointer">Search Documents</a>
                </li>                
                <li class="divider"></li>
                <li class="kases-list-menu" style="text-align:left; width:100%">
                    <a id="eams_submissions" style="width:100%; cursor:pointer">EAMS Submissions</a>
                </li>
                <li class="kases-list-menu" style="text-align:left; width:100%">
                    <a id="eamsfiler_submissions" style="width:100%; cursor:pointer">EAMSFiler Submissions</a>
                </li>
                <li class="kases-list-menu" style="text-align:left; width:100%">
                    <a href="#submissions" style="width:185px">Demographics Submissions</a>
                </li>
            </ul>
        </li>
        <li class="dropdown kases-new-menu">
        	<?php if ($blnShowCourtCalendar) { ?>
        	<div id="new_courtcalendar_indicator" style="cursor:pointer; position:absolute; z-index:1039; left:<?php echo ($indicator_left - 20); ?>px; top:5px; font-size:0.75em; background:aqua; color:black; border:1px solid white; height:16px; width:18px; text-align:center; vertical-align:bottom; display:none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; margin-bottom:10px" title="Click to review Court Calendar events NOT in your Firm Calendar"></div>
            <?php } ?>
       	<a href="#firmkalendar" class="dropdown-toggle" data-toggle="dropdown">Kalendar <b class="caret"></b></a>
        	<ul class="dropdown-menu" role="menu" style="width:260px">
            	<li class="kases-list-menu" style="text-align:left; width:100%">
                    <a id="new_event" style="text-align:left; width:100%; cursor:pointer">New Event</a>
                  </li>
        		<?php if (count($arrCalendars) == 0) { ?>
              <li class="kases-list-menu" style="text-align:left; width:100%">
	            <a href="#firmkalendar" style="text-align:left; width:100%">Appearances</a>
              </li>
              <li class="kases-list-menu" style="text-align:left; width:100%">
	            <!--<a href="#officekalendar" style="text-align:left; width:100%" disabled>-->
                <span style="color:#CCC; padding-left:20px">In&nbsp;Office&nbsp;Appearances</span>
              </li>
              <li class="kases-list-menu" style="text-align:left; width:100%">
	            <!--<a href="#officekalendar" style="text-align:left; width:100%" disabled>-->
                <span style="color:#CCC; padding-left:20px">Employee&nbsp;Attendance</span>
              </li>
              <li class="kases-list-menu" style="text-align:left; width:100%">
	            <a href="#intakekalendar" style="text-align:left; width:100%">Intake</a>
              </li>
              <li class="kases-list-menu" style="text-align:left; width:100%">
	            <a href="#personalkalendar" style="text-align:left; width:100%">Personal (<?php echo strtoupper($_SESSION["user_nickname"]); ?>)</a>
              </li>
              <?php } else { ?>
              <?php echo implode("", $arrCalendars); ?>
              <?php } ?>
              <!--
              <li class="divider"></li>
              <li class="kases-list-menu" style="text-align:left; width:100%">
	            <a id="calendar_link_wc" class="calendar_link" style="text-align:left; width:100%">WC Calendar</a>
              </li>
              <li class="kases-list-menu" style="text-align:left; width:100%">
	            <a id="calendar_link_pi" class="calendar_link" style="text-align:left; width:100%">PI Calendar</a>
              </li>
              <li class="divider"></li>
              -->
              <li class="kases-list-menu" style="text-align:left; width:100%">
	            <a href="#blocked" style="text-align:left; width:100%">Blocked Days</a>
              </li>
              <?php
			  if ($blnShowCourtCalendar) { 
			  ?>
              <li class="kases-subscribe-menu" style="text-align:left; width:100&">
              	<a href="#courtkalendar" style="text-align:left; width:100%">Court Calendar<span id="court_calendar_count" style="display:inline"></span></a>
              </li>
              <li class="divider"></li>
              <?php } ?>
              <li class="kases-subscribe-menu" style="text-align:left; width:100%">
	            <a href="#subscribekalendar" style="text-align:left; width:100%">Sync Link</a>
              </li>
              <?php if ($_SESSION['user_role']=="admin" ) { ?>
              <li class="kases-manage-kalendars-menu" style="text-align:left; width:100%">
	            <a href="#kalendars" style="text-align:left; width:100%">Manage Calendars</a>
              </li>
              <?php } ?>
            </ul>
        </li>
        <?php if ($blnNewLayout) { ?>
        <li class="kases-new-menu">
        	<div id="new_message_indicator2" class="new_message_indicator" style="cursor:pointer; position:absolute; z-index:1039; left:0px; top:5px; font-size:0.75em; background:#06F; color:white; border:1px solid white; height:16px; width:18px; text-align:center; vertical-align:bottom; display:none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; margin-bottom:10px" title="New Messages in your Inbox"></div>
            <div class="pending_indicator" style="cursor:pointer; position:absolute; z-index:1039; left:45px; top:5px; font-size:0.75em; background:white; color:black; border:1px solid white; height:16px; width:18px; text-align:center; vertical-align:bottom; display:none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; margin-bottom:10px" title="Click to review Pending emails"></div>
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" id="mail_navigation_link">Mail <b class="caret"></b></a>
            <ul class="dropdown-menu" style="width:190px">
            	<li class="kases-list-menu" style="text-align:left; width:100%">
                	<a id="thread_inbox" title="Click to list messages in your Inbox" style="cursor:pointer; display:unset">Inbox</a>
				</li>
                <li class="kases-list-menu" style="text-align:left; width:100%; padding-top:10px; padding-bottom:5px">
                	<div style="float:right; color:white; padding: 1px"><span class="new_message_indicator"></span></div>
					<a id="thread_inbox_new" title="Click to list unread messages" style="cursor:pointer; display:unset">Unread Messages</a>
				</li>
                <li class="kases-list-menu" style="text-align:left; width:100%">
					<a id="thread_outbox" title="Click to list messages in your Outbox" style="cursor:pointer">Outbox</a>
				</li>
                <li class="kases-list-menu" style="text-align:left; width:100%; padding-top:10px">
                	<div style="float:right; color:black; background:white; padding: 1px"><span class="drafts_indicator"></span></div>
					<a title="Click to list Draft Messages" id="list_drafts" style="cursor:pointer; display:unset">Drafts</a>
				</li>
                <li class="divider"></li>
                <li class="kases-list-menu" style="text-align:left; width:100%">
					<a title="Click to compose a new message" id="compose_message" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer">Compose</a>
				</li>
                <li class="divider"></li>
                 <li class="kases-list-menu" style="text-align:left; width:100%" id="pending_emails_nav">
                 	<div style="float:right; color:black; padding: 1px"><span id="pending_indicator_full" class="pending_indicator" style="background:white; color:black;"></span></div>
					<a id="thread_pending" title="Click to list pending emails" style="cursor:pointer; display:unset">Pending Emails</a>
				</li>
				<li class="tools-list-menu  webmail_menu" style="text-align:left; width:100%">
					<a id="refresh_webmail" title="Click to refresh messages in your Inbox from your email account" style="cursor:pointer">Refresh Emails</a>
				</li>
                <li class="divider"></li>
                 <li class="kases-list-menu" style="text-align:left; width:100%">
					<a href="#contacts" title="Click to list Email Contacts">Contacts</a>
				</li>
                <li class="kases-list-menu" style="text-align:left; width:100%">
					<a href="#contactsblocked" title="Click to list Contacts marked as SPAM">Blocked SPAM</a>
				</li>
                <li class="divider test_feedback_divider"></li>
                 <li class="kases-list-menu test_feedback" style="text-align:left; width:100%">
				</li>
                <li class="kases-reports-menu"><a id="email_settings" style="cursor:pointer; width:100%">Email Settings</a></li>
            </ul>
        </li>
        <!--
        <li class="kases-new-menu" style="margin-left:15px">	
            <a title="Click to compose a new message" id="compose_message" class="navbar-brand" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer">New Message</a> 
        </li>
        -->
        <li class="kases-new-menu"><a href="#" class="dropdown-toggle" data-toggle="dropdown">Tasks <b class="caret"></b></a>
        	<div class="task_count_indicator" style="cursor:pointer; position:absolute; z-index:1039; left:0px; top:5px; font-size:0.75em; background:rgb(0, 102, 255); color:white; border:1px solid white; height:16px; width:18px; text-align:center; vertical-align:bottom; display:none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; margin-bottom:10px" title="Count of all tasks in your inbox"></div>
            <div class="daily_task_indicator" style="cursor:pointer; position:absolute; z-index:1039; left:52px; top:5px; font-size:0.75em; background:white; color:black; border:1px solid white; height:16px; width:18px; text-align:center; vertical-align:bottom; display:none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; margin-bottom:10px" title="Today's Tasks"></div>
        	<div class="overdue_tasks_indicator" id="overdue_tasks_indicator2" style="cursor:pointer; position:absolute; z-index:1039; left:52px; top:32px; font-size:0.75em; background:red; color:white; border:1px solid white; height:16px; width:18px; text-align:center; vertical-align:bottom; display:none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; margin-bottom:10px" title="Click to review overdue Tasks"></div>
            <ul class="dropdown-menu" style="width:260px;overflow-x: hidden;">
                <li class="tasks-list-menu" style="text-align:left; width:100%">
                    <div style="float:right; color:black; padding: 1px">
                        <span class="task_count_indicator" title="Count of all tasks in your inbox"></span>
                    </div>
                    <a href="#taskinbox" style="display:unset">Tasks Inbox</a>
                </li>
                <li class="tasks-list-menu" style="text-align:left; width:100%">
                	<div style="float:right; color:black; padding: 1px; margin-right:-23px; text-align:right">
                        <span class="outbox_task_indicator" title="Count of all tasks in your outbox"></span>
                    </div>
                	<a href="#taskoutbox" style="display:unset">Tasks Outbox</a>
                </li>
                <li class="divider"></li>
                <li class="tasks-list-menu" style="text-align:left; width:100%">
	                <a title="Click to create a Task" class="compose_task" style="cursor:pointer">New Task</a>
                </li>
                <li class="tasks-list-menu" style="text-align:left; width:100%">
                    <div style="float:right; color:black; padding: 1px">
                        <span class="daily_task_indicator" title="Today's Tasks"></span>
                    </div>
                    <a href="#dailytask/<?php echo date("Y-m-d"); ?>" style="display:unset">User Daily Tasks</a>
                </li>
                <li class="tasks-list-menu" style="text-align:left; width:100%">
                    <div style="float:right; color:black; padding: 1px; margin-right:-10px">
                        <span class="upcoming_task_indicator" title="Upcoming Tasks"></span>
                    </div>
                    <a href="#tasksupcoming/<?php echo date("Y-m-d"); ?>" style="display:unset">Upcoming Tasks</a>
                </li>
                <% if (blnAdmin) { %>
                <li class="divider"></li>
                <li class="tasks-list-menu" style="text-align:left; width:100%">
                    <!--
                    <div style="float:right; color:black; padding: 1px;">
                    </div>
                    -->
                    <a href="#taskfirmoverdue" style="display:unset">Firm Overdue Tasks</a>&nbsp;<div style="display:inline-block; width:99px; text-align:right" id="firm_overdue_task_count"></div>
                </li> 
                <% } %>
                <li class="tasks-list-menu" style="text-align:left; width:100%">
                    <div style="float:right; color:black; padding: 1px; margin-right:-13px">
                        <span id="overdue_task_count"></span>
                    </div>
                    <a href="#taskoverdue" style="display:unset">Overdue Tasks</a>
                </li> 
				<li class="divider"></li>
                <li class="tasks-list-menu" style="text-align:left; width:100%"><a href="#taskcompleted">Completed Tasks</a></li>
                <!--
                <li class="tasks-list-menu" style="text-align:left; width:100%"><a href="#tasksreports">Task Reports</a></li>
                -->                
                <li class="tasks-list-menu" style="text-align:left; width:100%"><a href="#dailytaskall/<?php echo date("Y-m-d"); ?>">Firm Daily Tasks</a></li>
                <li class="tasks-list-menu" style="text-align:left; width:100%"><a href="#taskcompletedall/<?php echo date("Y-m-d"); ?>">Firm Completed Tasks</a></li>
            </ul>
        </li>
        <?php } ?>
        <?php if (!$blnIPad) { ?>
        <li class="kases-new-menu reports_menu"><a href="#" class="dropdown-toggle" data-toggle="dropdown" id="reports_main_link">Reports <b class="caret"></b></a>
            <ul class="dropdown-menu" style="width:210px">
            	<% if (blnAdmin) { %>
                <li class="kases-list-menu" style="text-align:left"><a href="#activities/all/<?php echo date("Y-m-d") . "/" . date("Y-m-d"); ?>" style="width:100%; cursor:pointer">Daily Activity</a></li>
                <% } else { %>
                <li class="kases-list-menu" style="text-align:left"><a href="#activities/<?php echo $_SESSION['user_plain_id']; ?>/<?php echo date("Y-m-d") . "/" . date("Y-m-d"); ?>" style="width:100%; cursor:pointer">Daily Activity</a></li>
                <% } %>
                <li class="kases-list-menu" style="text-align:left">
                	<a href="report.php#kasereport/opens" style="width:100%; cursor:pointer" target="_blank">Open Kases Report</a>
                </li>
                <li class="kases-list-menu">
                	<a id="all_kases_export" style="width:100%; cursor:pointer">Export All Kases</a>
                 </li>
                <li class="kases-list-menu" style="text-align:left"><a href="report.php#kasereport/notasks" style="width:100%; cursor:pointer" target="_blank">Kases w/o Tasks</a></li>
                <li class="kases-list-menu" style="text-align:left"><a href="report.php#kasereport/emails" style="width:100%; cursor:pointer" target="_blank">Kases w/Email Report</a></li>
                <li class="kases-list-menu" style="text-align:left"><a href="#inactive/45" style="width:100%">45 Day Inactives</a></li>
                <li class="divider"></li>
                <li class="kases-list-menu" style="text-align:left">
                	<a href="../reports/statutes.php" target="_blank" style="width:100%; cursor:pointer">Statute Report</a>
                    <?php if ($customer_id == "1121") { ?>
                    <div class="statute_count_current_indicator" style="cursor:pointer; z-index:1039; font-size:0.75em; background:red; color:white; border:1px solid white; height:16px; width:18px; text-align:center; display:; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; margin-top:-20px" title="Count of all statutes this month"><?php echo $first_count ?></div>
                     <div class="statute_count_next_indicator" style="cursor:pointer; z-index:1039; font-size:0.75em; background:#06F; color:white; border:1px solid white; height:16px; width:18px; text-align:center; display:; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; margin-top:-15px; margin-right:35%; float:right" title="Count of all statutes in the coming month"><?php echo $second_count ?></div>
                     <?php } ?>
                </li>
                <li class="kases-list-menu" style="text-align:left"><a id="search_settlements" style="width:100%; cursor:pointer">Settlement Report</a></li>
                <li class="kases-list-menu" style="text-align:left"><a id="list_calls" style="width:100%; cursor:pointer">Phone Messages Report</a></li>
                <li class="divider"></li>
                <li class="kases-list-menu" style="text-align:left"><a href="report.php#kasereport/bymonth" style="width:100%" target="_blank">Kases Summary</a></li> 
                <?php if ($blnShowEmployeeReports) { ?>
                <li class="kase-summary-list-menu" style="text-align:left; width:100%"><a href="#employeekases">Employee Kases</a></li>
                <?php } ?>
                <li class="kases-list-menu" style="text-align:left"><a href="report.php#clientreport/bymonth" style="width:100%" target="_blank">Clients Summary</a></li> 
                <li class="kases-list-menu" style="text-align:left"><a href="report.php#referralreport/bymonth" style="width:100%" target="_blank">Referrals Summary</a></li> 
                <li class="kases-list-menu" style="text-align:left"><a href="report.php#employers" style="width:100%" target="_blank">Employers</a></li> 
                <li class="divider"></li>
        	</ul>
        </li>
        
        <li class="kases-new-menu marketing_menu">
        	<?php if ($blnAdmin) { ?>
        	<div class="dob_indicator" id="dob_indicator2" style="cursor:pointer; position:absolute; z-index:1039; left:2px; top:5px; font-size:0.75em; background:blue; color:white; border:1px solid white; height:16px; width:18px; text-align:center; vertical-align:bottom; display:none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; margin-bottom:10px" title="Count of Birthdays for this month"></div>
            <?php } ?>
        	<a href="#" class="dropdown-toggle" data-toggle="dropdown">Marketing <b class="caret"></b></a>
            <ul class="dropdown-menu" style="width: 210px;overflow-x: hidden;">
            	<!--
                <li class="users-list-menu" style="text-align:left; width:100%">
                    <a title="Click to compose a new message to all Clients" id="compose_clients" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer; width:100%"><div style="float:right"><i class="glyphicon glyphicon-pencil" style="color:#000000">&nbsp;</i></div>Email All Clients</a>
                </li>
                -->
                <li class="users-list-menu" style="text-align:left; width:100%">
                	<a href="reports/export_client_emails.php" title="Click to Export Client Emails to XL" target="_blank">Export Client Emails</a>
                </li>
                <li class="users-list-menu" style="text-align:left; width:100%">
                	<a href="reports/export_client_info.php" title="Click to Export Client full Info to XL" target="_blank">Export Clients Info</a>
                </li>
                 <li class="users-list-menu" style="text-align:left; width:100%">
                	<a href="reports/export_cases_info.php" title="Click to Export Cases with Client full Info to XL" target="_blank">Export Cases w/ Client Info</a>
                </li>
                <li class="divider"></li>
                <li class="users-list-menu" style="text-align:left; width:100%">
                    <a title="Click to create envelopes for Current Clients (No Closed/Dropped/Subout, All Other Status OK)" id="envelope_clients" style="cursor:pointer; width:240px" href="templates/multi.php" target="_blank"><div style="float:right"><i class="glyphicon glyphicon-envelope" style="color:#000000">&nbsp;</i></div>Envelope Current Cases</a>
                </li>
                <li class="users-list-menu" style="text-align:left; width:100%">
                    <a title="Click to create envelopes for Active Clients (Open Cases Only)" id="envelope_clients" style="cursor:pointer; width:240px" href="templates/multi.php?active=" target="_blank"><div style="float:right"><i class="glyphicon glyphicon-envelope" style="color:#000000">&nbsp;</i></div>Envelope Open Cases</a>
                </li>
                <li class="users-list-menu" style="text-align:left; width:100%">
                    <a title="Click to create envelopes for All Clients (All Status OK)" id="envelope_clients" style="cursor:pointer; width:240px" href="templates/multi.php?all=" target="_blank"><div style="float:right"><i class="glyphicon glyphicon-envelope" style="color:#000000">&nbsp;</i></div>Envelope All Cases</a>
                </li>
                <li class="divider"></li>
                <li class="users-list-menu" style="text-align:left; width:100%">
                    <a title="Click to create Avery Labels (5160) for Current Clients (No Closed/Dropped/Subout Cases, All Other Status OK)" id="label_clients" style="cursor:pointer; width:240px" href="templates/multi_labels.php" target="_blank"><div style="float:right"><i class="glyphicon glyphicon-th" style="color:#000000">&nbsp;</i></div>Label Current Cases</a>
                </li>
                <li class="users-list-menu" style="text-align:left; width:100%">
                    <a title="Click to create Avery Labels (5160) for Active Clients (Open Cases Only)" id="label_clients" style="cursor:pointer; width:240px" href="templates/multi_labels.php?active=" target="_blank"><div style="float:right"><i class="glyphicon glyphicon-th" style="color:#000000">&nbsp;</i></div>Label Open Cases</a>
                </li>
                <li class="users-list-menu" style="text-align:left; width:100%">
                    <a title="Click to create Avery Labels (5160) for All Clients (All Status OK)" id="label_clients" style="cursor:pointer; width:240px" href="templates/multi_labels.php?all=" target="_blank"><div style="float:right"><i class="glyphicon glyphicon-th" style="color:#000000">&nbsp;</i></div>Label All Cases</a>
                </li>
                <li class="divider"></li>
                <li class="users-list-menu" style="text-align:left; width:100%">
                	<a href="reports/export_dob_emails.php" title="Click to Export DOB Clients Emails to XL" target="_blank">Export DOB Emails</a>
                </li>
                <li class="users-list-menu" style="text-align:left; width:100%">
                	<div style="float:right; color:black; padding: 1px">
                        <span class="dob_indicator" title="Count of Birthdays for this month"></span>
                    </div>
                	<a id="dob_email_report" title="Click to List DOB Clients" style="cursor:pointer; display:unset">List DOB Clients</a>
                </li>
                <li class="users-list-menu" style="text-align:left; width:100%">
                	<div style="float:right; color:black; padding: 1px; margin-right:-10px">
                        <span id="next_month_dob_indicator" title="Count of Birthdays for next month"></span>
                    </div>
                	<a id="next_dob_email_report" title="Click to Next Month DOB Clients" style="cursor:pointer; display:unset">Next Month DOB Clients</a>
                </li>
                <li class="users-list-menu" style="text-align:left; width:100%">
                	<a href="reports/export_cases_dob.php" title="Click to Export All Kases DOB Info to XL" target="_blank">Export All Cases DOBs</a>
                </li>
                <li class="users-list-menu" style="text-align:left; width:100%">
                    <a title="Click to create envelopes for all birthday clients" id="envelope_dobs" style="cursor:pointer; width:185px" href="templates/multi_dob.php" target="_blank"><div style="float:right"><i class="glyphicon glyphicon-envelope" style="color:#000000">&nbsp;</i></div>Envelope DOBs</a>
                </li>
                <!--
                <li class="users-list-menu" style="text-align:left; width:100%"><a href="#ninety" style="width:100%">90 Day Email (Auto)</a></li>
                <li class="users-list-menu" style="text-align:left; width:100%"><a href="#noactivity" style="width:100%">90 Day No Activity</a></li>
                -->
            </ul>
        </li>
        <?php if ($_SESSION["user_customer_id"]==1121) { ?>
        <li class="task-summary-list-menu" style="text-align:left;">
	        <a href="#tasksummary">Employee Tasks</a>
        </li>
        <?php } ?>
        <li id="navigation_admin" class="dropdown"">
        	<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php if ($_SESSION['user_role']=="admin" || $_SESSION['user_role']=="masteradmin" && !$blnIPad) { ?>Admin/<?php } ?>Tools<b class="caret"></b></a>
            <ul class="dropdown-menu" style="width:100%">
            	<?php if ($_SESSION['user_role']=="admin" || $_SESSION['user_role']=="masteradmin" && !$blnIPad) { ?>
                    <li class="users-list-menu" style="text-align:left; width:100%"><a href="#users">Users</a></li>
                    <?php if ($blnShowEmployeeReports) { ?>
                    	<li class="kase-summary-list-menu" style="text-align:left; width:100%"><a href="#employeekases">Employee Kases</a></li>
                    <?php } ?>
                    <li class="task-summary-list-menu" style="text-align:left; width:100%"><a href="#tasksummary">Employee Tasks</a></li>
                    <% if (blnLeavingKaseWarning) { %>
                    <li class="users-list-menu" style="text-align:left; width:100%"><a href="reports/no_notes.php" target="_blank">No Notes Report</a></li>
                    <% } %>
                    <li class="divider"></li>
                    <li class="forms-list-menu" style="text-align:left; width:100%">
                        <a href="#forms" style="width:100%">EAMS Forms</a>
                    </li>
                    <li class="divider"></li>
                 <?php } ?>
                 
                 <li class="kases-reports-menu"><a href="#contacts" style="width:100%">Contacts</a></li>
                <?php if ($_SESSION["user_customer_id"]==1111) { ?>
                <li class="kases-reports-menu"><a href="#contactparties" style="width:100%">Contact Parties</a></li>
                <?php } ?>
                <?php if ($_SESSION['user_role']=="admin" || $_SESSION['user_role']=="masteradmin" && !$blnIPad) { ?>
                <li class="kases-reports-menu"><a href="#settings" style="width:100%">Firm Settings</a></li>
                <?php } ?>
                <?php if ($_SESSION['user_role']=="admin" || $_SESSION['user_role']=="masteradmin" && !$blnIPad) { ?>
                <li class="kases-reports-menu"><a id="<?php echo $_SESSION['user_customer_id'];?>" class="docucentssetting" style="width:100%">DCC Settings</a></li>
                <?php } ?>
                <li class="kases-reports-menu"><a href="#usersettings" style="width:100%">User Settings</a></li>
                <li class="divider"></li>
                <li class="searchqme-list-menu" style="text-align:left; width:100%"><a href="#qme/-1">Search QME</a></li>
                <!--internal search of eams tables -->
                <li class="kases-list-menu" style="text-align:left"><a id="search_eams_firms" style="width:100%; cursor:pointer" href="#search_eams_firms/_">Search EAMS Firms</a></li> 
                <?php if ($_SESSION["user_customer_id"]!="1121") { ?>
				<li class="divider"></li>
				<li class="kases-list-menu" style="text-align:left; width:100%; display: none">
					<a title="Click to enter Chat" id="list_chat" style="cursor:pointer; text-decoration:none;">Enter Chat</a>
				</li>
                <%
                var new_chat_display = ""; 
                if ((login_nickname == "NG" || login_nickname == "av" || login_nickname == "ts") && customer_id == 1033) {
                	new_chat_display = "none";
                 } 
                 %>
				<li class="kases-list-menu" style="display: none; text-align:left; width:100%">
					<a title="Click to start Chat" id="new_chat" style="cursor:pointer; text-decoration:none">Start Chat</a>
				</li>
                <?php } ?>
                <li class="divider" style="display:none"></li>
                <li class="tools-list-menu  announce_menu" style="text-align:left; width:100%">
					<a id="show_announcements" title="Click to read system announcments" style="cursor:pointer">Announcements</a>
				</li>
                
                <!--
                <li class="tools-list-menu google_signin_menu" id="google_signin_menu" btnType="Authorize" style="text-align:left; width:100%">
					<a id="google_authorize_button" title="Click to SignIn via Google" style="cursor:pointer">Google Sign-In</a>
				</li>-->
                
                <li class="tools-list-menu" style="text-align:left; width:100%">
					<a data-toggle="modal" data-target="#myModal" title="Click to change the theme" style="cursor:pointer">Theme Picker</a>
				</li>
            </ul>
        </li>
        
        <li class="search" style="margin-right: 10px;margin-left: 10px;">
        	<div class="input-group" style="margin-top:10px; margin-left:0px; border:#00FF00 0px solid">
            
            	 <label for="srch-term" id="label_search" style="width:130px; font-size:1em; cursor:text; position:relative; top:0px; left:0px; color:#CCC; margin-left:0px; margin-top:-28px; -moz-user-select: none; -webkit-user-select: none; -ms-user-select:none; user-select:none;-o-user-select:none;" unselectable="on" onselectstart="return false;"><?php if (!$blnIPad) { ?>Kase Search <i class="glyphicon glyphicon-search"></i><?php } else { ?>Kases<?php } ?></label>
            
              <input type="text" class="form-control" placeholder="" name="srch-term" id="srch-term" autocomplete="off" style="margin-left:-135px; margin-top:0px; width:<?php if (!$blnIPad) { ?>155<?php } else { ?>80<?php } ?>px; height:30px"><a id="clear_search" style="z-index:3445; position:absolute; left:<?php if (!$blnIPad) { ?>132<?php } else { ?>62<?php } ?>px; top:7px; border:0px #FF8787 solid: cursor:pointer"><i class="glyphicon glyphicon-remove-circle" style='font-size:1em; color:#cccccc'></i></a>
				<a title="Click to Filter Search" style="z-index:3445; position:absolute; left:<?php if (!$blnIPad) { ?>155<?php } else { ?>80<?php } ?>px; top:7px; border:0px #FF8787 solid; cursor:pointer; color:white" id="search_filter">&#8711;</a>
			</div>
             <div id="search_modifiers" style="display:none; position:absolute; z-index:999; background:#333; width:<?php if (!$blnIPad) { ?>120<?php } else { ?>70<?php } ?>px">
             	<div class='white_text' style="display:">
                	<input class="search_modifier" name="search_modifer" id='search_open_cases' type='radio' checked='checked'>&nbsp;Open Cases
               	</div>
                <div class='white_text' style="display:">
                	<input class="search_modifier" name="search_modifer" id='search_closed_cases' type='radio'>&nbsp;Closed Cases
               	</div>
                <div class='white_text' style="display:">
                	<input class="search_modifier" name="search_modifer" id='search_subout_cases' type='radio'>&nbsp;Sub Out Cases
               	</div>
                <div class='white_text' style="display:">
                	<input class="search_modifier" name="search_modifer" id='search_sol' type='radio'>&nbsp;SOL
               	</div>
                <div class='white_text' style="display:">
                	<input class="search_modifier" name="search_modifer" id='search_doctors' type='radio'>&nbsp;Doctors
               	</div>
                <div class='white_text' style="display:">
                	<input class="search_modifier" name="search_modifer" id='search_all_cases' type='radio'>&nbsp;All Cases
               	</div>
             </div>
        </li>
        
        <?php } ?>
        <?php if ($blnIPad) { ?>
		<li class="tools-list-menu dropdown" style="display:">
        	<a href="#" class="dropdown-toggle" data-toggle="dropdown">Tools<b class="caret"></b></a>
            <ul class="dropdown-menu" style="width:140px;">
                <li class="kases-reports-menu"><a href="#contacts" style="width:100%">Contacts</a></li>
                <?php if ($_SESSION["user_customer_id"]==1111) { ?>
                <li class="kases-reports-menu"><a href="#contactparties" style="width:100%">Contact Parties</a></li>
                <?php } ?>
                <?php if ($_SESSION['user_role']=="admin" || $_SESSION['user_role']=="masteradmin" && !$blnIPad) { ?>
                <li class="kases-reports-menu"><a href="#settings" style="width:100%">Firm Settings</a></li>
                <?php } ?>
                <li class="kases-reports-menu"><a href="#usersettings" style="width:100%">User Settings</a></li>
                <li class="divider"></li>
                <li class="searchqme-list-menu" style="text-align:left; width:100%"><a href="#qme/-1">Search QME</a></li>
                <!--internal search of eams tables -->
                <li class="kases-list-menu" style="text-align:left"><a id="search_eams_firms" style="width:100%; cursor:pointer" href="#search_eams_firms/_">Search EAMS Firms</a></li> 
                <?php if ($_SESSION["user_customer_id"]!="1121") { ?>
				<li class="divider"></li>
				<li class="kases-list-menu" style="text-align:left; width:100%">
					<a title="Click to enter Chat" id="list_chat" style="cursor:pointer; text-decoration:none">Enter Chat</a>
				</li>
                <%
                var new_chat_display = ""; 
                if ((login_nickname == "NG" || login_nickname == "av" || login_nickname == "ts") && customer_id == 1033) {
                	new_chat_display = "none";
                 } 
                 %>
				<li class="kases-list-menu" style="display: <%=new_chat_display %>; text-align:left; width:100%">
					<a title="Click to start Chat" id="new_chat" style="cursor:pointer; text-decoration:none">Start Chat</a>
				</li>
                <?php } ?>
                <li class="divider"></li>
                <li class="tools-list-menu  announce_menu" style="text-align:left; width:100%">
					<a id="show_announcements" title="Click to read system announcments" style="cursor:pointer">Announcements</a>
				</li>
                <li class="tools-list-menu" style="text-align:left; width:100%">
					<a data-toggle="modal" data-target="#myModal" title="Click to change the theme" style="cursor:pointer">Theme Picker</a>
				</li>
				<!--
                <li class="kases-list-menu" style="text-align:left; width:100%">
					<a title="Click to compose a new message" id="compose_message" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer">New Message</a>
				</li>
				<li class="tools-list-menu  webmail_menu" style="text-align:left; width:100%">
					<a id="refresh_webmail" title="Click to refresh messages in your Inbox from your email account" style="cursor:pointer">Webmail</a>
				</li>
				<li class="divider"></li>
				<li class="users-list-menu" style="text-align:left; width:100%"><a href="#taskinbox">Tasks Inbox</a></li>
				<li class="users-list-menu" style="text-align:left; width:100%"><a href="#taskoutbox">Tasks Outbox</a></li>
                <li class="divider"></li>
                <li class="users-list-menu" style="text-align:left; width:100%">
                	<a title="Click to create a Task" class="compose_task" style="cursor:pointer">New Task</a>
                </li>
				<li class="users-list-menu" style="text-align:left; width:100%"><a href="#dailytask/<?php echo date("Y-m-d"); ?>">User Daily Tasks</a></li>
				<li class="users-list-menu" style="text-align:left; width:100%"><a href="#taskcompleted">Completed Tasks</a></li>
                <li class="divider"></li>
                <li class="users-list-menu" style="text-align:left; width:100%"><a href="#dailytaskall/<?php echo date("Y-m-d"); ?>">Firm Daily Tasks</a></li>
                <li class="users-list-menu" style="text-align:left; width:100%"><a href="#taskcompletedall/<?php echo date("Y-m-d"); ?>">Firm Completed Tasks</a></li>
                -->
                
                <li class="divider"></li>
                <li class="kases-new-menu"><a href="#" class="dropdown-toggle" data-toggle="dropdown" style="width:138px">Marketing <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li class="users-list-menu" style="text-align:left; width:100%">
                            <!--<a href="#clients">Clients</a>-->
                            <a title="Click to compose a new message" id="compose_clients" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><div style="float:right"><i class="glyphicon glyphicon-pencil" style="color:#000000">&nbsp;</i></div>Clients</a>
                        </li>
                        <li class="users-list-menu" style="text-align:left; width:100%"><a href="#ninety">90 Day Email (Auto)</a></li>
                        <li class="users-list-menu" style="text-align:left; width:100%"><a href="#noactivity">90 Day No Activity</a></li>
                    </ul>
                </li>
                
            </ul>
        </li>
        <?php } ?>
        <?php if (!$blnIPad) { ?>
        <?php if (!$blnNewLayout) { ?>
        <li style="margin-top:15px; padding-right:5px; border-left:1px solid white">&nbsp;</li>
        <li class="messaging">
        	<div style="margin-top:15px">
            	<div style="display:inline-block">
                    <div id="new_message_indicator" class="new_message_indicator" style="cursor:pointer; position:absolute; z-index:1039; left:11px; top:5px; font-size:0.75em; background:#06F; color:white; border:1px solid white; height:16px; width:18px; text-align:center; vertical-align:bottom; display:none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; margin-bottom:10px" title="New Messages in your Inbox"></div>
                    <a href="#thread/inbox" title="Click to list messages in your Inbox"><i class="glyphicon glyphicon-save" style="color:#06F;">&nbsp;</i></a>
                </div>
                <div style="display:inline-block">
                	<a href="#thread/outbox" title="Click to list messages in your Outbox"><i class="glyphicon glyphicon-open" style="color:#99F">&nbsp;</i></a>
                </div>
                <div style="display:inline-block">
                	<a title="Click to compose a new message" id="compose_message" data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" style="cursor:pointer"><i class="glyphicon glyphicon-pencil" style="color:#00FFFF">&nbsp;</i></a>
                </div>
            </div>
        </li>
        <?php } ?>
        <?php if (!$blnNewLayout) { ?>
        <li style="margin-top:15px; padding-right:5px; border-left:1px solid white">&nbsp;</li>
        <li class="tasks">
        	<div style="margin-top:15px">
                <div style="display:inline-block">
                	<div id="new_task_indicator" style="cursor:pointer; position:absolute; z-index:1039; left:11px; top:5px; font-size:0.75em; background:rgb(0, 102, 255); color:white; border:1px solid white; height:16px; width:18px; text-align:center; vertical-align:bottom; display:none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; margin-bottom:10px" title="Click to list Today's Tasks"></div>
                    <div class="overdue_tasks_indicator" id="overdue_tasks_indicator" style="cursor:pointer; position:absolute; z-index:1039; left:11px; top:32px; font-size:0.75em; background:white; color:black; border:1px solid white; height:16px; width:18px; text-align:center; vertical-align:bottom; display:none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; margin-bottom:10px" title="Click to review overdue Tasks"></div>
                	<span class="dropdown-toggle" data-toggle="dropdown" id="list_task" style="cursor:pointer"><i class="glyphicon glyphicon-tasks" style="color:#090">&nbsp;</i></span>
		            <ul class="dropdown-menu">
                    	<li class="users-list-menu" style="text-align:left; width:100%"><a href="#taskinbox">Tasks Inbox</a></li>
                        <li class="users-list-menu" style="text-align:left; width:100%"><a href="#taskoutbox">Tasks Outbox</a></li>
                		<li class="users-list-menu" style="text-align:left; width:100%">
                        	<!-- data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false"  -->
                        	<a title="Click to create a Task" class="compose_task" style="cursor:pointer">New Task</a>
                        </li>
                        <li class="users-list-menu" style="text-align:left; width:100%"><a href="#dailytask/<?php echo date("Y-m-d"); ?>">User Daily Task </a></li>
                       	 <li class="users-list-menu" style="text-align:left; width:100%"><a href="#taskoverdue">Overdue Tasks<span id="overdue_task_count" style="display:inline"></span></a></li> 
                        <li class="users-list-menu" style="text-align:left; width:100%"><a href="#taskcompleted">Completed Tasks</a></li>
                        <li class="divider"></li>
                        <!--
                        <li class="users-list-menu" style="text-align:left; width:100%"><a href="#tasksreports">Task Reports</a></li>
                        -->
                        
                        <li class="users-list-menu" style="text-align:left; width:100%"><a href="#dailytaskall/<?php echo date("Y-m-d"); ?>">Firm Daily Tasks</a></li>
                        <li class="users-list-menu" style="text-align:left; width:100%"><a href="#taskcompletedall/<?php echo date("Y-m-d"); ?>">Firm Completed Tasks</a></li>
                    </ul>
        		</div>
                <div style="display:inline-block">
                	<!-- data-toggle="modal" data-target="#myModal4" data-backdrop="static" data-keyboard="false" -->
        			<a title="Click to create a Task" class="compose_task" style="cursor:pointer"><i class="glyphicon glyphicon-inbox" style="color:#66FF33">&nbsp;</i></a>
            	</div>
            </div>
        </li>
        <?php } ?>
        <?php if ($_SESSION["user_customer_id"]!="1121") { ?>
        <li style="margin-top:15px; padding-right:5px; border-left:0px solid white; margin-left:15px">&nbsp;</li>
        <li class="tasks">
        	<div style="margin-top:10px">
                <div style="display:none">
                <div id="new_chat_indicator" style="position:absolute; z-index:1039; left:13px; top:12px; background:#F9F; color:black; border:1px solid white; font-size:0.75em; height:16px; width:18px; text-align:center; vertical-align:bottom; display:none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; margin-bottom:10px" title="New Chat Request">0</div>
                <a title="Click to enter Chat" id="list_chat" style="cursor:pointer; font-size:1.5em; color:#F3C; text-decoration:none; display:none"><i class="icon-comment">&nbsp;</i></a>
        		</div>
                <%
                var new_chat_display = "inline-block"; 
                //if ((login_nickname == "NG" || login_nickname == "av" || login_nickname == "ts") && customer_id == 1033) {
                	new_chat_display = "none";
                 //} 
                 %>
                 
                <div style="display: <%=new_chat_display %>; padding-left:0px">
        	<a title="Click to start Chat" id="new_chat" style="cursor:pointer; font-size:1.5em; color:#F9F; text-decoration:none; display:none"><i class="icon-comment-1">&nbsp;</i></a>
            	</div>
                
            </div>
        </li>
		<?php } ?>
        <li style="margin-top:15px; <?php if ($_SESSION["user_customer_id"]=="1121") { ?>margin-left:15px; <?php } ?>padding-right:5px; border-left:0px solid white">&nbsp;</li>
        <?php } ?>
        <li class="phone">
        	<div style="margin-top:15px">
                <div style="display:inline-block; padding-left:0px">
                <div id="new_phone_indicator" style="cursor:pointer; position:absolute; z-index:1039; left:11px; top:5px; font-size:0.75em; background:#3C9; color:white; border:1px solid white; height:16px; width:18px; text-align:center; vertical-align:bottom; display:none; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; margin-bottom:10px" title="New Phone Messages"></div>
        	<a title="Click to send phone message" id="new_phone_message" style="cursor:pointer; color:#3C9; text-decoration:none"><i class="glyphicon glyphicon-earphone">&nbsp;</i></a>
            	</div>
            </div>
        </li>
        <li style="margin-top:15px; padding-right:5px; border-left:1px solid white">&nbsp;</li>
        <li class="currentuser">
        	<div style="margin-top:15px">
                <div style="display:inline-block; padding-left:0px">        
		        	<a title="List users currently logged in to iKase" id="current_users" style="cursor:pointer; color:white; text-decoration:none"><i class="glyphicon glyphicon-user">&nbsp;</i></a>
            	</div>
            </div>
        </li>
        <li style="padding-top:15px; padding-left:3px">
        	<div style="display:inline-block"><?php if (!$blnIPad) { ?>&nbsp;<a id="logoutLink" style="display:;"><i style="font-size:1.0em;color:red; cursor:pointer" class="glyphicon glyphicon-log-out" title="Click to logout"></i></a></div><?php } ?>
        </li>
    </ul>
</div><!--/.nav-collapse -->
<div id="popup_holder"></div>

<!-- THEME PICKER 2021-12-24 10.17AM -->
<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog modal-lg themepickerModal">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Theme Picker</h4>
        </div>
        <div class="modal-body">
			<div class="Palette">
                <div class="Palette_Color Palette_Color--default" data-color="default"></div>
                <div class="Palette_Color Palette_Color--turqoise" data-color="th_turqoise"></div>
				<div class="Palette_Color Palette_Color--blue" data-color="th_blue"></div>
				<div class="Palette_Color Palette_Color--orange" data-color="th_orange"></div>
				<div class="Palette_Color Palette_Color--purple" data-color="th_purple"></div>
				<div class="Palette_Color Palette_Color--pink" data-color="th_pink"></div>
				<div class="Palette_Color Palette_Color--yellow" data-color="th_yellow"></div>
			</div>
        </div>
      </div>
      
    </div>
  </div>