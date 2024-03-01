<?php
require_once('../../shared/legacy_session.php');

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

include ("../../text_editor/ed/functions.php");
include ("../../text_editor/ed/datacon.php");

if (count($_SESSION)==0) {
	header("location:../../index.php");
}

include("../customers/sec.php");

$cus_id = passed_var("cus_id");
$host = $_SERVER['HTTP_HOST'];

if (!is_numeric($cus_id)) {
	die();
}
$row = DB::runOrDie("SELECT cus_name_first, cus_name_last, cus_name, cus_street, cus_city, cus_state, cus_zip 
FROM ikase.cse_customer WHERE customer_id = " . $cus_id)->fetch();
$cus_name = $row->cus_name;
$cus_name_first = $row->cus_name_first;
$cus_name_last = $row->cus_name_last;
$the_cus_street = $row->cus_street;
$the_cus_city = $row->cus_city;
$the_cus_state = $row->cus_state;
$the_cus_zip = $row->cus_zip;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="nofollow,noindex" />
<title>Users</title>
<style type="text/css">
<!--
a.nav:link {
	color: #FFFFFF;
}
a.nav:visited {
	color: #FFFFFF;
}
a.nav:hover {
	color: #FFFFFF;
}
.yui-skin-sam .yui-dt .yui-dt-col-user_name {
	width: 150px;
	text-align: left;
}
body {
	background-color: #069;
	margin-top:10px;
}
-->
</style></head>

<body class="yui-skin-sam">
<?php //echo $_SESSION["user_customer_id"] . " // " . $_SESSION["user_plain_id"] . " // " . $_SESSION["user"]; ?>
<table width="770" border="0" align="center" cellpadding="2" cellspacing="0">
  <tr>
    <td colspan="2" bgcolor="#CCCCCC">
    	<div style="float:right">
        	<?php
			if ($owner_id > 0) { 
				$cus_name = "iKase Master Login<br />" . $owner_name;
			}
			echo "<span style='font-size:14pt'>" . $cus_name . "</span>";
			if ($cus_id > 0 && $owner_id == 0) {
				echo "<br /><span style='font-size:1.1em'>";
				if ($user_name!="") {
					echo "Welcome " . $user_name . "</span>";
				} else {
					echo "Welcome " . $cus_name_first . " " . $cus_name_last;
				}
				echo "</span>";
				echo "<br /><span style='font-size:0.8em'>" . ucwords(strtolower($the_cus_street)) . "<br />" . ucwords(strtolower($the_cus_city)) . ", " . $the_cus_state . " " . $the_cus_zip;
				if ($the_cus_phone!="") {
					echo "<br />" . $the_cus_phone;
				}
				echo "</span>"; 
				
			}
			?>
        </div>
        <img src="../../img/ikase_logo.png" alt="iKase" height="90" />
    </td>
  </tr>
  <tr>
    <td colspan="2" bgcolor="#000066" style="color:#FFFFFF" align="left">
   	  <div style="float:right">
      	<a href="../customers/index.php?suid=<?php echo $suid; ?>" class="nav">List of Customers</a>
        <a href="editor.php?cus_id=<?php echo $cus_id; ?>&suid=<?php echo $suid; ?>" class="nav">New User</a>
      </div>
      <strong><?php echo $cus_name; ?> - List of Users</strong><span id="user_counts"></span>
    </td>
  </tr>
  <tr>
  	<td colspan="2" align="left">
    	<div align="left">
            <div id="list_users" style="padding-top:10px"></div>
        </div>
    </td>
</table>
<?php //die($cus_id . " - cus"); ?>
<link rel="stylesheet" type="text/css" href="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/fonts/fonts-min.css" />
<link rel="stylesheet" type="text/css" href="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/datatable/assets/skins/sam/datatable.css" />
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/yahoo-dom-event/yahoo-dom-event.js"></script>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/connection/connection-min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/json/json-min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/element/element-min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/datasource/datasource-min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/event-delegate/event-delegate-min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/datatable/datatable-min.js"></script>
<script src="../../js/cookies.js"></script>
<script language="javascript">
var Dom = YAHOO.util.Dom;
var myDataTable;
var myDataSource;
var setForm = function() {
	myDataTable.onShow();
	
	document.getElementById("user_counts").innerHTML = "&nbsp;&nbsp;Users:" + user_count + "&nbsp;|&nbsp;Active Users:" + active_count + "&nbsp;|&nbsp;Inactive Users:" + inactive_count;
}
var refreshDataSource = function() {
	this.sentData = "";
	myDataSource.sendRequest(this.sentData, myDataTable.onDataReturnInitializeTable, myDataTable);
	
	myDataTable.onShow();
}
var confirmDelete = function(user_id) {
	var confirmit=confirm("Are you sure you want to delete this User");
	if (confirmit) {
		deleteTheUser(user_id);
	}
}
var deleteTheUser = function (user_id) {
	//alert("delete user");

	var sendDeleteUrl = "user_delete.php";
	this.sentData = "user_id=" + user_id;
	//alert(sendDeleteUrl + '?' + this.sentData);
	var request = YAHOO.util.Connect.asyncRequest('POST', sendDeleteUrl,
	   {success: function(o){
			response = o.responseText;
			//mark it saved
			//alert(response);
			refreshDataSource();
		},
		failure: function(){
			   //
			   //alert("failure");
			},
	   after: function(){
		   //
		},
	   scope: this}, this.sentData);
}
var activateUser = function (user_id) {
	//alert("delete user");

	var sendDeleteUrl = "user_activate.php";
	this.sentData = "user_id=" + user_id;
	//alert(sendDeleteUrl + '?' + this.sentData);
	var request = YAHOO.util.Connect.asyncRequest('POST', sendDeleteUrl,
	   {success: function(o){
			response = o.responseText;
			//mark it saved
			//alert(response);
			refreshDataSource();
		},
		failure: function(){
			   //
			   //alert("failure");
			},
	   after: function(){
		   //
		},
	   scope: this}, this.sentData);
}
var inactivateUser = function (user_id) {
	//alert("delete user");

	var sendDeleteUrl = "user_inactivate.php";
	this.sentData = "user_id=" + user_id;
	//alert(sendDeleteUrl + '?' + this.sentData);
	var request = YAHOO.util.Connect.asyncRequest('POST', sendDeleteUrl,
	   {success: function(o){
			response = o.responseText;
			//mark it saved
			//alert(response);
			refreshDataSource();
		},
		failure: function(){
			   //
			   //alert("failure");
			},
	   after: function(){
		   //
		},
	   scope: this}, this.sentData);
}
var userLogin = function (user_id) {
	//alert("delete user");

	var loginUrl = "../../api/masterlogin";
	this.sentData = "user_id=" + user_id;
	//alert(sendDeleteUrl + '?' + this.sentData);
	var request = YAHOO.util.Connect.asyncRequest('POST', loginUrl,
	   {success: function(o){
			response = o.responseText;
			var data = JSON.parse(response);
			document.location.href = "https://<?php echo $host; ?>/v<?php echo $version_number; ?>.php?session_id=" + data.session_id + "&old_session_id=<?php echo $_SESSION["user"]; ?>&masterlogin=";
		},
		failure: function(){
			   //
			   //alert("failure");
			},
	   after: function(){
		   //
		},
	   scope: this}, this.sentData);
}
var active_count = -1;
var inactive_count = 0;
var user_count = -1;
var init = function() {
	var formatUser = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<a href='editor.php?cus_id=<?php echo $cus_id; ?>&suid=<?php echo $suid; ?>&user_id=" + oRecord.getData("user_id") + "'>" + oRecord.getData("user_name") + "</a>";
	}
	var formatLogin = function(elCell, oRecord, oColumn, sData) {
		//elCell.innerHTML = "<a href='http://<?php echo $host; ?>'>Login</a>";
		elCell.innerHTML = "<a href='javascript:userLogin(" + oRecord.getData("user_id") +")' style='background:black; color:white; padding:2px'>Login</a>";
	}
	var formatActive = function(elCell, oRecord, oColumn, sData) {
		if (oRecord.getData("activated")=="Y") {
			elCell.innerHTML = "<a href='javascript:inactivateUser(" + oRecord.getData("user_id") +")' title='Click to Inactivate this user' style='text-decoration:none;'><span style='background:green;color:white;padding:1px'>Active</span></a>";
			active_count++;
		} else {
			elCell.innerHTML = "<a href='javascript:activateUser(" + oRecord.getData("user_id") +")' title='Click to Activate this user' style='text-decoration:none;'><span style='background:red;color:white;padding:1px'>Inactive</span></a>";
			inactive_count++;
		}
		user_count++;
	}
	var deleteUser = function(elCell, oRecord, oColumn, sData) {
		if (oRecord.getData("deleted")=="Y") {
			elCell.innerHTML = "<span style='background:red;color:white;'>Deleted</span>";
		} else {
			elCell.innerHTML = "<a href='javascript:confirmDelete(\"" + oRecord.getData("user_id") + "\")' style='color:red'>Delete</a>";
		}
	}
	var myColumnDefs = [
		{key:"user_id", width:"50px", label:"ID", sortable:true, resizeable:false},
		{key:"user_name", width:"520px", label:"User", formatter:formatUser, sortable:true, resizeable:true},
		{key:"", formatter:formatLogin, label:"Login"},
		{key:"user_email", label:"Email", sortable:true, resizeable:true},
		{key:"nickname", label:"Nickname", sortable:true, resizeable:true},
		{key:"level", label:"Level", sortable:true, resizeable:true},
		{key:"job", label:"Job", sortable:true, resizeable:true},
		{key:"active", formatter:formatActive, label:"Active"},
		{key:"", formatter:deleteUser, label:"Delete"}
	];
	
	var form_height_med = 500;
	form_height_med = form_height_med + "px";
	
	//list the data
	myDataSource = new YAHOO.util.DataSource("user_list.php?cus_id=<?php echo $cus_id; ?>&suid=<?php echo $suid; ?><?php if ($race_id!="") { ?>&race_id=<?php echo $race_id; ?><?php } ?>");
	myDataSource.responseType = YAHOO.util.DataSource.TYPE_TEXT;
	
	var myConfigs = {
		height:form_height_med
	};
	myDataSource.responseSchema = { 
		recordDelim: "\n",
		fieldDelim: "|",
		fields: ["user_id","user_name","user_email","password","level","job","user_logon","pwd","nickname","deleted","activated"]
	};
	
	
	myDataTable = new YAHOO.widget.ScrollingDataTable("list_users", myColumnDefs,
						myDataSource, myConfigs);
	
	setTimeout("setForm()", 300);
				
	return {
		oDS: myDataSource,
		oDT: myDataTable
	};
}
YAHOO.util.Event.addListener(window, "load", init);
</script>
</body>
</html>
