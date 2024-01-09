<?php
include("../api/manage_session.php");
if (!isset($_SESSION['user_data_path'])) {
	$_SESSION['user_data_path'] = '';
}
session_write_close();

include ("../api/connection.php");
include ("../browser_detect.php");

$blnIPad = isPad();

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
			//rename the calendar with their initials
			$customer_calendar->calendar = str_replace("Employee", strtoupper($_SESSION['user_nickname']), $customer_calendar->calendar);
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
	$menu_item = '<li class="kases-list-menu' . $class . '" style="text-align:left; width:150px">';
				if ($customer_calendar->sort_order == 0) {
					//$menu_item .= '<div style="float:right"></div>';
				}
				$menu_item .= '
				<a href="#ikalendar/' . $customer_calendar->calendar_id . '/' . $customer_calendar->sort_order . '" style="text-align:left; width:198px' . $link_color . '" target="">' . $customer_calendar->calendar . '</a>';
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
	
	/*
	if ($customer_calendar->sort_order == 0) {	
		$menu_item = '<li class="kases-list-menu' . $class . '" style="text-align:left; width:220px">
					<div style="padding-left:20px"><div style="display:inline-block"><a href="#ikalendar' . $disabled . '/' . $customer_calendar->calendar_id . '/' . $customer_calendar->sort_order . '" style="text-align:left; width:198px' . $link_color . '" target="_blank">' . $customer_calendar->calendar . '</a></div><div style="font-size:0.8em; display:inline-block">&nbsp;(new&nbsp;window)</div></div></li>';
		$arrCalendars[] = $menu_item;
	}
	*/
	/*
	//if ($_SERVER['REMOTE_ADDR']=='173.55.229.70') {
		$menu_item = '<li class="divider"></li><li class="kases-list-menu" style="text-align:left; width:100px">
				<a href="#ikalendar' . $disabled . '/' . $customer_calendar->calendar_id . '/-1" style="text-align:left; width:198px' . $link_color . '">' . $customer_calendar->calendar . ' Day View</a></li>
				<li class="kases-list-menu" style="text-align:left; width:100px">
				<a href="#ikalendar' . $disabled . '/' . $customer_calendar->calendar_id . '/-2" style="text-align:left; width:198px' . $link_color . '">' . $customer_calendar->calendar . ' Week View</a></li>
				';
	$arrCalendars[] = $menu_item;
	//}
	*/
}

$menu_item = '<li class="employee_calendar_holder divider" style="display:"><hr /></li>';
$arrCalendars[] = $menu_item;
$link_color = "color:black";

//employee calendar	
$menu_item = '<li class="employee_calendar_holder kases-list-menu' . $class . '" style="text-align:left; width:220px; display:">
			<div style="padding-left:20px"><div style="display:inline-block"><a href="#employee_kalendar" style="text-align:left; width:198px' . $link_color . '">Employee Calendar</a></div></div></li>';
$arrCalendars[] = $menu_item;

if (strtolower($_SESSION['user_data_path'])=="a1") {
	$menu_item = '<li class="partner_calendar_holder divider" style="display:none"><hr /></li>';
	$arrCalendars[] = $menu_item;
	$link_color = "color:black";
	
	//partner
	$menu_item = '<li class="partner_calendar_holder kases-list-menu' . $class . '" style="text-align:left; width:220px; display:none">
				<div style="padding-left:20px"><div style="display:inline-block"><a href="#partner_calendar" style="text-align:left; width:198px' . $link_color . '">Partner Calendar</a></div></div></li>';
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
	
	$menu_item = '<li class="kases-list-menu" style="text-align:left; width:100px">
				<a href="#userkalendar/' . $personal_calendar->id . '" style="text-align:left; width:198px' . $link_color . '">' . ucwords($personal_calendar->name) . '</a>
			  </li>';
	$arrCalendars[] = $menu_item;
}

$db = null;
?>
<nav class="navbar navbar-inverse" style="width:100%; border:0px solid yellow; margin-left:0px; ">
<div class="navbar-header" style="display:; margin-top:-5px; border:0px solid yellow">
    <a class="navbar-brand" href="#home" title="<%=customer_id %> - <%=dbname %> - <%=customer_name %> - <%=login_username %>"><img src="img/favicon.png" width="30" height="30" alt="iKase">&nbsp;<div style="font-size:1.5em; margin-top:-28px; margin-left:38px">iKase</div></a>
</div>
       	  <div style="float:right; border:0px solid yellow; position:absolute; top:2px; right:10px; display:inline-block">
                <div style="float:left">
                <a href="#search_docs" style="font-size:1.5em">
                     <i style="font-size:1.1em;color:red; cursor:pointer; margin-top:9px" class="glyphicon glyphicon-search"></i>
                </a>
                </div>&nbsp;&nbsp;&nbsp;&nbsp;
                <div style="float:right">
              <ul class="nav navbar-nav">
                  <li role="presentation" class="dropdown" style="margin-top:-10px">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" style="font-size:1em">
                      <i style="font-size:1.5em;color:#EDEDED; cursor:pointer;" class="glyphicon glyphicon-align-justify" title="Click to logout"></i>
                    </a>
                    <ul class="dropdown-menu" style="background:black">
                      <li role="presentation" class="dropdown" style="padding:10px">
                        <a href="#home" style="font-size:1.5em">
                          Search&nbsp;&nbsp;<i style="font-size:1em;color:white; cursor:pointer; padding-right:50px; margin-top:5px" class="glyphicon glyphicon-search"></i>
                        </a>
                      </li>
                      <li role="presentation" class="dropdown" style="padding:10px">
                        <a id="reloadLink" style="font-size:1.5em; cursor:pointer">Reload</a>
                      </li>
                      <li role="presentation" class="dropdown" style="padding:10px">
                        <a id="logoutLink" style="font-size:1.5em">Logout&nbsp;&nbsp;<i style="color:red; cursor:pointer; position:absolute; top:12px" class="glyphicon glyphicon-log-out" title="Click to logout"></i></a>
                      </li>
                    </ul>
                  </li>
              </ul>
             </div>
          </div>
          
</nav>
<!--/.nav-collapse -->