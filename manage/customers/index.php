<?php
require_once('../../shared/legacy_session.php');
session_write_close();

include("sec.php");

if($_SERVER['SERVER_NAME']=="v2.starlinkcms.com")
{
  $application = "StarLinkCMS";
  $application_logo = "logo-starlinkcms.png";
  $application_url = "https://v2.starlinkcms.com/";
}
else
{
  $application = "iKase";
  $application_logo = "ikase_logo_login.png";
  $application_url = "https://v4.ikase.org/";
}

$blnOwnerAdmin = ($_SESSION["user_role"] == "owner" && ($_SESSION["user_id"] == 11 || $_SESSION["user_id"] == 12));
//die(print_r($_SESSION));

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

/*
include ("../../text_editor/ed/functions.php");
include ("../../text_editor/ed/datacon.php");
*/
include("../../api/connection.php");

$cus_id = -1;

$admin_client = passed_var("admin_client");
$administrator = passed_var("administrator");

$query = "SELECT `owner_id`, `admin_client`, `name`, `owner_email`, `url`, `password`, `pwd`, `session_id`, `dateandtime`, `ip_address`
FROM cse_owner 
WHERE 1
AND owner_id > 4
ORDER BY `name`";

try {
	$owners = DB::select($query);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
$arrOptions = array();
$selected = "";
if ($administrator=="") {
	$selected = " selected";
	$administrator = 0;
}
$option = "<option value=''" . $selected . ">Select Marketer</option>";
$arrOptions[] = $option;

foreach($owners as $owner) {
	$the_owner_id = $owner->owner_id;
	$owner_name = $owner->name;
	
	$selected = "";
	if ($administrator==$the_owner_id) {
		$selected = " selected";
	}
	$option = "<option value='" . $the_owner_id . "'" . $selected . ">" . $owner_name . "</option>";
	$arrOptions[] = $option;
}


$sql = "SELECT table_name 
FROM information_schema.tables 
WHERE table_schema='ikase'
AND table_name LIKE '%_track'
AND table_name != 'cse_batchscan_queue_track'
AND table_name != 'cse_bodyparts_track'
AND table_name != 'cse_customer_track'
AND table_name != 'cse_eams_forms_track'
AND table_name != 'cse_email_track'
AND table_name != 'cse_accident_track'
AND table_name != 'cse_lien_track'";
//die($sql_last);

try {
	$tables = DB::select($sql);
} catch(PDOException $e) {
	echo $e->getMessage() . "<br />";
	$error = array("sql"=>$sql, "error"=> array("text"=>$e->getMessage()));
	//print_r($error);
	//die($sql);
	echo json_encode($error);
}
//die(print_r($tables));
//activity on kases
$sql_statement = "SELECT user_uuid, user_logon, customer_id, MAX(time_stamp) last_action
FROM ikase.cse_case_track
WHERE CAST(time_stamp AS DATE) = '" . date("Y-m-d") . "'
GROUP BY user_uuid";

$sql_statement = "
SELECT user_uuid, user_logon, customer_id, MAX(last_action) last_action
FROM (
	SELECT 'kase' tablename, user_uuid, user_logon, customer_id, MAX(time_stamp) last_action
	FROM ikase.cse_case_track
	WHERE CAST(time_stamp AS DATE) = '" . date("Y-m-d") . "'
	GROUP BY user_uuid
	UNION
	SELECT 'activity' tablename, user_uuid, user_logon, customer_id, MAX(time_stamp) last_action
	FROM ikase.cse_activity_track
	WHERE CAST(time_stamp AS DATE) = '" . date("Y-m-d") . "'
	GROUP BY user_uuid
) lasts
GROUP BY user_uuid";

//we need all of the jetfile submissions
$sql = "SELECT `schema_name`
FROM `information_schema`.schemata 
WHERE schema_name LIKE 'ikase%'";

try {
	$schemas = DB::select($sql);
} catch(PDOException $e) {
	echo $e->getMessage() . "<br />";
	$error = array("sql"=>$sql, "error"=> array("text"=>$e->getMessage()));
	//print_r($error);
	//die($sql);
	echo json_encode($error);
}

//die(print_r($schemas));
$arrUnion = array();
	
foreach($schemas as $schema) {
	//skip
	if (	$schema->schema_name=="ikase_glauber" 
			|| $schema->schema_name=="ikase_glauber2"
			|| $schema->schema_name=="ikase_dordulian2"
			|| $schema->schema_name=="ikase_dordulian"
			|| $schema->schema_name=="ikase_goldberg"
		) 
			{
		continue;
	}
	$new_sql = str_replace("ikase.", "`" . $schema->schema_name . "`.", $sql_statement);
	$arrUnion[] = $new_sql;
}

$sql_last = implode("
UNION
", $arrUnion);

//currently active
$query = "SELECT usr.user_id, cus.customer_id cus_id, cus.cus_name, last_action.last_action, ulog.* 
FROM ikase.cse_userlogin ulog
INNER JOIN ikase.cse_customer cus
ON ulog.customer_id = cus.customer_id
INNER JOIN (
	SELECT ulog.user_uuid, MAX(userlogin_id) max_id
	FROM ikase.cse_userlogin ulog
	GROUP BY ulog.user_uuid
) maxes
ON ulog.userlogin_id = maxes.max_id

LEFT OUTER JOIN (
	" . $sql_last . "
) last_action
ON ulog.user_uuid = last_action.user_uuid

INNER JOIN ikase.cse_user usr
ON ulog.user_uuid = usr.user_uuid

WHERE CAST(usr.dateandtime AS DATE) = '" . date("Y-m-d") . "'

AND ulog.`status` = 'IN'
ORDER BY cus.cus_name, ulog.user_name";
try {
	$db = getConnection();
	//die($query);
	$actives = DB::select($query);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

$arrActiveLogins = array();
$arrLastActivity = array();
//die(print_r($actives));
foreach($actives as $login) {
	$arrLastActivity[$login->user_id] = $login->last_action;
	if (!isset($arrActiveLogins[$login->cus_id])) {
		$arrActiveLogins[$login->cus_id] = 0;
	}
	//die($login->last_action);
	if (dateDiff("n", $login->last_action, date("Y-m-d H:i:s")) > 10) {
		//
	} else {
		$arrActiveLogins[$login->cus_id]++;
	}
}
//currently logged in
$query = "SELECT usr.user_id, cus.customer_id cus_id, cus.cus_name, usr.dateandtime last_action, ulog.* 
FROM ikase.cse_userlogin ulog
INNER JOIN ikase.cse_customer cus
ON ulog.customer_id = cus.customer_id
INNER JOIN (
	SELECT ulog.user_uuid, MAX(userlogin_id) max_id
	FROM ikase.cse_userlogin ulog
	GROUP BY ulog.user_uuid
) maxes
ON ulog.userlogin_id = maxes.max_id

INNER JOIN ikase.cse_user usr
ON ulog.user_uuid = usr.user_uuid

WHERE CAST(usr.dateandtime AS DATE) = '" . date("Y-m-d") . "'
AND ulog.`status` = 'IN'
ORDER BY cus.cus_name, ulog.user_name";
try {
	$db = getConnection();
	//die($query);
	$logins = DB::select($query);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

$arrRows = array();

$row = "
<tr>
	<th align='left' valign='top'>
	&nbsp;
	</th>
	<th align='left' valign='top' class='no_show no_show_" . $login->cus_id . "'>
	User
	</th>
	<th align='left' valign='top' class='no_show no_show_" . $login->cus_id . "'>
	Login Time
	</th>
	<th align='left' valign='top' class='no_show no_show_" . $login->cus_id . "' style='background:" . $background . "'>
	Last Action
	</th>
</tr>";

$arrRows[] = $row;

$arrCount = array();

$arrUserLogins = array();
//die(print_r($logins));
foreach($logins as $login) {
	if (!isset($arrLastActivity[$login->user_id])) {
		$arrLastActivity[$login->user_id] = "";
	}
	if ($current_cus != $login->cus_name) {
		$current_cus = $login->cus_name;
		$arrCount[$login->cus_id] = 0;
		$display_cus = "<a style='cursor:pointer; color:blue; text-decoration:underline' onclick='showUserRows(" . $login->cus_id . ")' title='Click to show all logins for " . $login->cus_name . "'>" . $login->cus_name . "</a>&nbsp;<span id='owner_total_" . $login->cus_id . "'></span>";
		$show_class = "";
	} else {
		$display_cus = "";
		$show_class = "no_show";
	}
	if (!isset($arrUserLogins[$login->cus_id])) {
		$arrUserLogins[$login->cus_id] = 0;
	}
	if (!isset($arrActiveCount[$login->cus_id])) {
		$arrActiveCount[$login->cus_id] = 0;
	}
	
	$last_action = $arrLastActivity[$login->user_id];
	$diff = "--";
	$background = "";
	if ($last_action!=="") {
		$last_action = date("g:iA", strtotime($last_action));
		$last_action_date = date("Y-m-d H:i:s", strtotime($last_action));
		$minutes =  dateDiff("n", $last_action_date , date("Y-m-d H:i:s"));
		$diff = " (" . $minutes . " minutes ago)" ;
		$background = "lime";
		if ($minutes > 10) {
			$background = "orange";
		}
	}
	
	$minutes =  dateDiff("n", $login->last_action , date("Y-m-d H:i:s"));
	$login_background = "lime";
	if ($minutes > 10) {
		$login_background = "orange";
	}
	if ($minutes < 10) {
		$arrUserLogins[$login->cus_id]++;
	}
	$row = "
	<tr>
		<td align='left' valign='top' class='" . $show_class . " no_show_" . $login->cus_id . "'>
		" . $display_cus . "
		</td>
		<td align='left' valign='top' class='no_show no_show_" . $login->cus_id . "'>
		" . $login->user_name . "
		</td>
		<td align='left' valign='top' class='no_show no_show_" . $login->cus_id . "' style='background:" . $login_background . "'>
		" . date("g:iA", strtotime($login->dateandtime)) . " 
		</td>
		<td align='left' valign='top' class='no_show no_show_" . $login->cus_id . "' style='background:" . $background . "'>
		" . $last_action. " " . $diff . "
		</td>
	</tr>";
	
	$arrRows[] = $row;
	$arrCount[$login->cus_id]++;
}

$sql = "SELECT cus.cus_name, cus.cus_email, inv.* 
FROM ikase.cse_invoice inv
INNER JOIN ikase.cse_customer cus
ON inv.customer_id = cus.customer_id
WHERE inv.notification_date = '0000-00-00 00:00:00'
AND inv.customer_id != 1033
AND inv.invoice_date > '2018-12-01'
ORDER BY inv.invoice_date ASC";

$invoices_ready = DB::select($sql); $dbPDO = null;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="nofollow,noindex" />
<title>Customers</title>
</head>
<style>
.yui-dt td {
	font-family:arial;
	font-size:9pt; 
	vertical-align:top;	
}
.yui-skin-sam .yui-dt .yui-dt-col-users {
	width: 55px;
}
.yui-skin-sam .yui-dt .yui-dt-col-invoice {
	width: 65px;
}
.yui-dt th {
	text-align: left;
}
.yui-skin-sam .yui-dt .yui-dt-col-cus_id {
	width: 5px;
	text-align: right;
}
</style>
<body class="yui-skin-sam">
<?php //echo $_SESSION["user_customer_id"] . " // " . $_SESSION["user_plain_id"] . " // " . $_SESSION["user"]; ?>
<table width="1280" border="0" align="center" cellpadding="2" cellspacing="0">
  <tr>
    <td colspan="2" bgcolor="#CCCCCC"><img src="../../img/<?= $application_logo; ?>" alt="<?= $application; ?>" height="32" /></td>
  </tr>
  <tr>
    <td colspan="2" bgcolor="#CCCCCC">
    	<div style="float:right">
        	&nbsp;|&nbsp;
            <a href="../../index.html">Logout</a>
            &nbsp;|&nbsp;
            <a href="../owner/index.php?suid=<?php echo $suid; ?>">List of Administrators</a>&nbsp;|&nbsp;
            <a href="../cards/index.php?suid=<?php echo $suid; ?>">Cards</a>&nbsp;|&nbsp;
            <a href="editor.php?admin_client=<?php echo $admin_client; ?>&suid=<?php echo $suid; ?>&cus_id=<?php echo "-1"; ?>">New Customer</a>
        </div>
        <div style="float:right; position:relative">
        	Current Active Users: <span id="total_users"></span>&nbsp;<span id="total_invoiced"></span>
            <a href="invoices_list.php?filter=unnotif" style="margin-right:10px">Invoices Ready to Send&nbsp;<span style="position:absolute; margin-top:-5px; background:white; border:1px solid black; padding:2px"><?php echo count($invoices_ready); ?></span></a>
        </div>
        <strong>List of Customers</strong>    </td>
  </tr>
  <tr>
  	<td colspan="2" align="left">
    	<div align="left">
        	<div>
                <a href="javascript:showList()" id="show_list_link">Show List</a>
                &nbsp;|&nbsp;
                <a href="javascript:showLogins()" id="show_logins_link">Show Logins <span id="show_logins_count"></span></a>
                <a href="javascript:hideList()" id="hide_list_link" style="display:none">Hide List</a>
                <div style="display:inline-block">
                    <input type="text" id="search_box" placeholder="Search for customer" onkeyup="scheduleSearch()" />
                    <div id="search_result"></div>
                </div>
            </div>
            <div id="login_records" style="display:none; width:60%; margin-left:auto; margin-right:auto">
            	<div style="float:right">
                	<a href="javascript:showAllUsers()">Show All</a>
                </div>
            	<div style="text-align:left; font-size:1.4em; font-weight:bold; margin-top:10px">System Users:&nbsp;<span id="total_count"></span></div>
                <table align="center" cellpadding="2" cellspacing="0" width="100%" border="0">
                    <tr>
                        <th align="left" valign="top">Customer</th>
                        <th align="left" valign="top" class="no_show show_column">Employee</th>
                        <th align="left" valign="top" class="no_show show_column">Login Time</th>
                        <th align="left" valign="top" class="no_show show_column">Last Action</th>
                    </tr>
                    <?php echo implode("", $arrRows); ?>
                </table>
                <script type="application/javascript">
				var total_count = 0;
				var total_login_count = 0;
				var total_active_count = 0;
				
				<?php foreach($arrCount as $cindex=>$count) { ?>
				var cus_id = "<?php echo $cindex; ?>";
				var cus_count = "<?php echo $count; ?>";
				var login_count = "<?php echo $arrUserLogins[$cindex] ; ?>";
				var active_count = "<?php echo $arrActiveLogins[$cindex] ; ?>";
				
				total_count += Number(cus_count);
				
				total_login_count += Number(login_count);
				total_active_count += Number(active_count);
				
				var background_color = "lime";
				if (login_count==0) {
					background_color = "pink";
				}
				
				var active_color = "lime";
				if (active_count==0) {
					active_color = "pink";
				}
				document.getElementById("owner_total_" + cus_id).innerHTML = " - [" + cus_count + "]&nbsp;/&nbsp;[<span style='background:" + background_color + "'>" + login_count + "</span>]&nbsp;/&nbsp;[<span style='background:" + active_color + "'>" + active_count + "</span>]";
				<?php } ?>
				var background_color = "lime";
				if (total_login_count==0) {
					background_color = "pink";
				}
				
				var active_color = "lime";
				if (total_active_count==0) {
					active_color = "pink";
				}
				document.getElementById("total_count").innerHTML = "[" + total_count + "] today&nbsp;/&nbsp;[<span style='background:" + background_color + "''>" + total_login_count + "</span>] logged-in&nbsp;/&nbsp;[<span style='background:" + active_color + "'>" + total_active_count + "</span>] active";
				document.getElementById("show_logins_count").innerHTML = "<span title='Total System Users Today'>[" + total_count + "]</span>&nbsp;/&nbsp;[<span style='background:" + background_color + "' title='Currently Logged-in'>" + total_login_count + "</span>]&nbsp;/&nbsp;[<span style='background:" + active_color + "' title='Active in the last 10 minutes'>" + total_active_count + "</span>]";
				
				var rows = document.getElementsByClassName("no_show");
				var arrLength = rows.length;
				for (var i = 0; i < arrLength; i++) {
					rows[i].style.display = "none";
				}
				
				function showUserRows(cus_id) {
					var rows = document.getElementsByClassName("no_show");
					var arrLength = rows.length;
					for (var i = 0; i < arrLength; i++) {
						rows[i].style.display = "none";
					}
					
					var show_columns = document.getElementsByClassName("show_column");
					var arrLength = show_columns.length;
					for (var i = 0; i < arrLength; i++) {
						show_columns[i].style.display = "";
					}
					
					var rows = document.getElementsByClassName("no_show_" + cus_id);
					var arrLength = rows.length;
					for (var i = 0; i < arrLength; i++) {
						rows[i].style.display = "";
					}
				}
				function showAllUsers() {
					var rows = document.getElementsByClassName("no_show");
					var arrLength = rows.length;
					for (var i = 0; i < arrLength; i++) {
						rows[i].style.display = "";
					}
				}
				</script>
            </div>
            <div id="list_customers" style="padding-top:10px; width:100%; visibility:hidden"></div>
        </div>
    </td>
  </tr>
</table>
<div id="panel_records" style="display:none;">
    <div class="hd" style="display:">Upload Documents</div>
    <div class="bd" style="margin:0px; padding-left:2px" id="event_td">
      <iframe id="upload_frame" scrolling="no" width="100%" height="125" src="" style="border:0px solid red"></iframe>
      <input type="hidden" name="cus_document" id="cus_document" value="" />
    </div>
    <div class="ft" style="display:none">&nbsp;</div>
</div>
<script language="javascript">
var company_timeout_id = false;
function scheduleSearch(order_id) {
	clearTimeout(company_timeout_id);
	company_timeout_id = setTimeout(function() {
		searchCustomer();
	}, 700);
}
function searchCustomer() {
	var keyword = document.getElementById("search_box").value;
	
	if (keyword.length < 3) {
		return;
	}
	var url = "customer_search.php?keyword=" + keyword;
	
	var r = new XMLHttpRequest();
	r.open("GET", url, true);
	r.onreadystatechange = function () {
	  if (r.readyState != 4 || r.status != 200) {
		return;
	  } else {
		data = r.responseText;
		document.getElementById("search_result").innerHTML = data;
	  }
	};
	r.send();
}
function showLogins() {
	document.getElementById("login_records").style.display = "";
}
function showList() {
	document.getElementById("login_records").style.display = "none";
	document.getElementById("list_customers").style.visibility = "visible";
	document.getElementById("show_list_link").style.display = "none";
	document.getElementById("hide_list_link").style.display = "";
}
function hideList() {
	document.getElementById("list_customers").style.visibility = "hidden";
	document.getElementById("show_list_link").style.display = "";
	document.getElementById("hide_list_link").style.display = "none";
}
</script>
<?php include ("yahoo.php"); ?>
<script language="javascript">
YAHOO.namespace("example.container");
var Dom = YAHOO.util.Dom;
var myDataTable;
var myDataSource;
var current_cus_id;
var confirmDelete = function(cus_id) {
	var confirmit=confirm("Are you sure you want to delete this Customer");
	if (confirmit==true) {
		deleteTheCustomer(cus_id);
	}
}
var refreshDataSource = function() {
	this.sentData = "";
	myDataSource.sendRequest(this.sentData, myDataTable.onDataReturnInitializeTable, myDataTable);
	
	myDataTable.onShow();
}
var deleteTheCustomer = function (cus_id) {
	//logEvent("delete customer");

	var sendDeleteUrl = "customer_delete.php";
	this.sentData = "cus_id=" + cus_id + "&suid=<?php echo $suid; ?>";
	//alert(sendDeleteUrl + '?' + this.sentData);
	var request = YAHOO.util.Connect.asyncRequest('POST', sendDeleteUrl,
	   {success: function(o){
			response = o.responseText;
			//mark it saved
			alert(response);
			refreshDataSource();
		},
	   failure: function(){
			   //
		   alert("failure");
		},
		after: function(){
		   //
		},
	   scope: this}, this.sentData);
}
var checkAddress = function (inhouse_id) {
	//logEvent("delete customer");

	var sendSetUrl = "../../matrix_address_lookup.php";
	this.sentData = "id=" + inhouse_id + "&suid=<?php echo $suid; ?>";
	//alert(sendSetUrl + '?' + this.sentData);
	var request = YAHOO.util.Connect.asyncRequest('POST', sendSetUrl,
	   {success: function(o){
			response = o.responseText;
			//mark it saved
			alert(response);
		},
	   failure: function(){
			   //
		   alert("failure");
		},
		after: function(){
		   //
		},
	   scope: this}, this.sentData);
}
var setMarketer = function (cus_id, set) {
	//logEvent("delete customer");

	var sendSetUrl = "set_marketer.php";
	this.sentData = "cus_id=" + cus_id + "&suid=<?php echo $suid; ?>"
	if (set==1) {
		this.sentData += "&administrator=<?php echo $administrator; ?>";
	}
	//alert(sendSetUrl + '?' + this.sentData);
	var request = YAHOO.util.Connect.asyncRequest('POST', sendSetUrl,
	   {success: function(o){
			response = o.responseText;
			//mark it saved
			alert(response);
			refreshDataSource();
		},
	   failure: function(){
			   //
		   alert("failure");
		},
		after: function(){
		   //
		},
	   scope: this}, this.sentData);
}
var setForm = function() {
	myDataTable.onShow();
	
	document.getElementById("search_box").focus();
}
var total_users = 0;
var total_invoiced = 0;
var total_paid = 0;
var init = function() {
	//panel position
	var xpos = 700;
	var ypos = 150;
	//alert(screen_width_full);
	var pos_xy = new Array(xpos,ypos);
	
	YAHOO.example.container.panel_records = new YAHOO.widget.Panel("panel_records", { width:"750px", height: "75px", visible:false, constraintoviewport:true, xy:pos_xy, modal:true } );
	YAHOO.example.container.panel_records.render();
	
	var formatImport = function(elCell, oRecord, oColumn, sData) {
		//check permissions
		var permissions = oRecord.getData("permissions");
		var lookfor = "i";
		var startpos = permissions.indexOf(lookfor);
		if (startpos > -1) {
			if (oRecord.getData("data_path")=="") {
				elCell.innerHTML = "<a href='import_basic.php?suid=<?php echo $suid; ?>&customer_id=" + oRecord.getData("parent_id") + "'>Basic&nbsp;Import</a>"
			}
			if (oRecord.getData("data_path")=="tritek") {
				elCell.innerHTML = "<a href='import_tritek.php?suid=<?php echo $suid; ?>&customer_id=" + oRecord.getData("parent_id") + "'>Import&nbsp;Tritek</a>"
			}
			if (oRecord.getData("data_path")=="A1") {
				elCell.innerHTML = "<a href='import_a1.php?suid=<?php echo $suid; ?>&customer_id=" + oRecord.getData("parent_id") + "'>Import&nbsp;A1</a>"
			}
			if (oRecord.getData("data_path")=="perfect") {
				elCell.innerHTML = "<a href='import_perfect.php?suid=<?php echo $suid; ?>&customer_id=" + oRecord.getData("parent_id") + "'>Import&nbsp;Perfect</a>"
			}
			if (oRecord.getData("data_path")=="Abacus") {
				elCell.innerHTML = "<a href='import_abacus.php?suid=<?php echo $suid; ?>&customer_id=" + oRecord.getData("parent_id") + "'>Import&nbsp;Abacus</a>"
			}
		}
		elCell.innerHTML += "&nbsp;-&nbsp;" + oRecord.getData("data_path");
	}
	var formatParent = function(elCell, oRecord, oColumn, sData) {
		var permissions = oRecord.getData("permissions");
		if (oRecord.getData("cus_name")==oRecord.getData("parent_name")) {
		elCell.innerHTML = "<a href='editor.php?admin_client=<?php echo $admin_client; ?>&suid=<?php echo $suid; ?>&cus_id=" + oRecord.getData("parent_id") + "'>" + oRecord.getData("parent_name") + "</a> " + permissions;
		}
	}
	var formatCustomer = function(elCell, oRecord, oColumn, sData) {
		//check permissions
		var permissions = oRecord.getData("permissions");
		var lookfor = "r";
		var startpos = permissions.indexOf(lookfor);
		var background;
		if (startpos<0) {
			//background = "style='background:orange; color:black'";
		}
		if (oRecord.getData("cus_name")!=oRecord.getData("parent_name")) {
			elCell.innerHTML = "<a href='editor.php?admin_client=<?php echo $admin_client; ?>&suid=<?php echo $suid; ?>&cus_id=" + oRecord.getData("cus_id") + "' " + background + ">" + oRecord.getData("cus_name") + "</a>";
		}
	}
	var formatLogin = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<a href='https://<?php echo $host; ?>/logon.php?user_name=" + oRecord.getData("eams_no") + "&pwd=" + oRecord.getData("pwd") + "' target='_blank'>Login</a>";
	}
	/*
	var formatUsers = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<a href='../users/index.php?cus_id=" + oRecord.getData("cus_id") + "&suid=<?php echo $suid; ?>'>Users</a>&nbsp;(" + oRecord.getData("user_count") + ")"; 
		
		total_users += Number(oRecord.getData("user_count"));
		document.getElementById("total_users").innerHTML = total_users + "&nbsp;|&nbsp;";
	}
	*/
	var formatDelete = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<a href='javascript:confirmDelete(\"" + oRecord.getData("cus_id") + "\")' style='color:red'>Delete</a>";
	}
	var formatXL = function(elCell, oRecord, oColumn, sData) {
		if (oRecord.getData("xl_filed")!="Y") {
			elCell.innerHTML = "<a href='javascript:setXL(\"" + oRecord.getData("cus_id") + "\")' style='color:orange' title='Click to indicate that you have entered this customer on the EAMS Spreadsheet'>Set</a>";
		} else {
			elCell.innerHTML = "<span style='background:green;color:white'>&#10003</span>";
		}
	}
	var formatMarketing = function(elCell, oRecord, oColumn, sData) {
		var marketer = oRecord.getData("marketer");
		var marketer_name = oRecord.getData("marketer_name");

		if (marketer != <?php echo $administrator; ?>) {
			elCell.innerHTML = "<a href='javascript:setMarketer(\"" + oRecord.getData("cus_id") + "\", 1)' style='color:orange' title='Click to assign <?php echo $administrator; ?> to this customer'>Set</a>";
			if (marketer!="") {
				elCell.innerHTML += "(" + marketer_name + ")";
			}
		} else {
			elCell.innerHTML = "<span style='background:green;color:white'>&#10003</span>";
			elCell.innerHTML += "&nbsp;<a href='javascript:setMarketer(\"" + oRecord.getData("cus_id") + "\", 0)' style='color:orange' title='Click to unassign <?php echo $administrator; ?> to this customer'>Unset</a>";
		}
	}
	var formatExport = function(elCell, oRecord, oColumn, sData) {
		if (oRecord.getData("inhouse_id")!="0") {
			elCell.innerHTML = "<span style='background:green;color:white'>&#10003</span>";
		} else {
			elCell.innerHTML = "";
		}
	}
	var formatNotes = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<a href='javascript:showNotes(" + oRecord.getData("cus_id") + ")'>Notes</a>";
	}
	var formatUploads = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<div><a href='javascript:showRecordsPanel(" + oRecord.getData("cus_id") + ")' title='Click to upload documents'>Upload</a></div>";
	}
	var formatInvoice = function(elCell, oRecord, oColumn, sData) {
		var cell = "<a href='invoices.php?cus_id=" + oRecord.getData("cus_id") + "' title='Click to list Invoices' target='_blank'>Invoices</a>";
		if (oRecord.getData("user_count") == 0) {
			cell = "<span style='color:red'>No active users</span>";
		}
		var blnInvoiced = (oRecord.getData("invoiced") > 0 && oRecord.getData("user_count"));
		if (blnInvoiced) {
			var cell = "<a href='invoices.php?filter=all&cus_id=" + oRecord.getData("cus_id") + "&invoice_id=" + oRecord.getData("invoiced") + "' title='Click to review Invoices' target='_blank'>Invoices</a>";
			cell += "<br>";
			cell += "&#10003;";
			cell += "&nbsp;|&nbsp;$";
			cell += oRecord.getData("invoiced_amount");
			var due = Number(oRecord.getData("invoiced_amount")) - Number(oRecord.getData("paids"));
			if (due < 1) {
				cell += "&nbsp;<span style='background:green;color:white;padding:2px'>paid&nbsp;&#10003;</span>";
			}
			total_invoiced += Number(oRecord.getData("invoiced_amount"));
			total_paid += Number(oRecord.getData("paids"));
			
			<?php if ($blnOwnerAdmin) { ?>
			var total_invoiced_content = "<a href='invoices_list.php'>List Invoices </a>&nbsp;$" + String(total_invoiced) + "&nbsp;<span color='red'>($" + total_paid + ")</span>&nbsp;|&nbsp;"; 
			document.getElementById("total_invoiced").innerHTML = total_invoiced_content;
			<?php } ?>
		}
		
		elCell.innerHTML = cell;
	}
	var formatCounts = function(elCell, oRecord, oColumn, sData) {
		var json_counts = oRecord.getData("json_counts");
		var json = JSON.parse(json_counts);
		var arrCount = [];
		if (+oRecord.getData("user_count") > 0) {
			total_users += Number(oRecord.getData("user_count"));
			var cell = oRecord.getData("user_count");
			cell += "&nbsp;@&nbsp;$";
			cell += oRecord.getData("user_rate") + "/month";
			arrCount.push("Active Users:" + cell);
			
			document.getElementById("total_users").innerHTML = total_users + "&nbsp;|&nbsp;";
		}
		if(typeof json.batchscan != "undefined") {
			arrCount.push("Batchscans:" + json.batchscan.count + " (" + json.batchscan.last + ")");
		}
		if(typeof json.sent != "undefined") {
			arrCount.push("Emails:" + json.sent.count + " (" + json.sent.last + ")");
		}
		elCell.innerHTML = arrCount.join("<br>");
	}
	///users/index.php?suid=014f96e0c85c86b
	var myColumnDefs = [
		{key:"cus_id", label:"ID", sortable:true, resizeable:false},
		{key:"parent_name", label:"Main Office", formatter:formatParent, sortable:true, resizeable:true},
		{key:"cus_name", label:"Sub Office", formatter:formatCustomer, sortable:true, resizeable:true},
		{key:"start_date", label:"Start"},
		{key:"cus_city", label:"City", sortable:true, resizeable:true},
		{key:"cus_zip", label:"Zip", sortable:true, resizeable:true},
		{key:"invoice", label:"Invoice", formatter:formatInvoice},
		{key:"json_counts", label:"Counts (last)",  formatter:formatCounts},
		{key:"Import Legacy Data", formatter:formatImport},
		{key:"data_source", label:"DB"},
		{key:"inhouse_id", label:"MatrixID"},
		{key:"", formatter:formatDelete}
	];
	
	var form_height_med = 600;
	form_height_med = form_height_med + "px";
	
	//list the data
	myDataSource = new YAHOO.util.DataSource("customer_list.php?admin_client=<?php echo $admin_client; ?>&owner_id=<?php echo $owner_id; ?>&suid=<?php echo $suid; ?>");
	myDataSource.responseType = YAHOO.util.DataSource.TYPE_TEXT;
	
	var myConfigs = {
		height:form_height_med
	};
	myDataSource.responseSchema = { 
		recordDelim: "\n",
		fieldDelim: "|",
		fields: ["cus_id","eams_no","cus_name_full","cus_name","cus_street","cus_city","cus_state","cus_zip","admin_client_id","password","xl_filed","pwd","inhouse_id","permissions","parent_id", "parent_name", "data_path", "user_count","json_counts","invoiced","invoiced_amount","paids","user_rate","start_date","data_source"]
	};
	
	myDataTable = new YAHOO.widget.ScrollingDataTable("list_customers", myColumnDefs,
						myDataSource, myConfigs);
	
	setTimeout("setForm()", 300);
		
	return {
		oDS: myDataSource,
		oDT: myDataTable
	};
}
var saveUpload = function(folder_name) {
	var cus_document = Dom.get("cus_document");
	mysentData = "suid=<?php echo $suid; ?>&the_cus_id=" + current_cus_id + "&cus_document=" + cus_document.value + "&folder_name=" + folder_name;
	var eamsURL = "save_upload.php";
	
	if (mysentData!='') {	
		//logEvent("about to send request");
		var request = YAHOO.util.Connect.asyncRequest('POST', eamsURL,
		   {success: function(o){
				response = o.responseText;
				//alert(response);
				//refresh the search
				hideRecordsPanel();
				refreshDataSource();
			},
		   failure: function(){
			   //
			   //alert("failure");
			},
		   after: function(){
			   //
			   //alert("after");
			},
		   scope: this}, mysentData);
		 //logEvent("appointment save done");
	}
}
var storeRecords = function(imagename, fieldname) {
	if (imagename!="") {
		var the_pdf = document.getElementById(fieldname);
		the_pdf.value = imagename;
	}
}
var hideRecordsPanel = function() {
	stored_case_id = "";
	current_cus_id = "";
	YAHOO.example.container.panel_records.hide();
	Dom.setStyle("panel_records" , "display", "none");
}
var showRecordsPanel = function(cus_id) {
	current_cus_id = cus_id;
	
	YAHOO.example.container.panel_records.show();
	
	var upload_frame = Dom.get("upload_frame");
	upload_frame.src = "upload_form.php?suid=<?php echo $suid; ?>&fieldname=cus_document&cus_id=" + cus_id;

	Dom.setStyle("panel_records" , "display", "");
}
var listAdmin = function() {
	var administrator = Dom.get("administrator");
	document.location.href="index.php?suid=<?php echo $suid; ?>&administrator=" + administrator.value;
}
var showNotes = function(cus_id) {
	document.location.href = "../notes/index.php?suid=<?php echo $suid; ?>&the_cus_id=" + cus_id;
}
YAHOO.util.Event.addListener(window, "load", init);
</script>
</body>
</html>
