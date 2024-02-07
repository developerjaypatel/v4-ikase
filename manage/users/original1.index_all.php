<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>
<?php
include("../eamsjetfiler/datacon.php");
include("../eamsjetfiler/functions.php");

$cus_id = passed_var("cus_id");
include("../logon_check.php");

$sql = "SELECT cus_name FROM tbl_customer 
WHERE cus_id = '" . $cus_id . "'";
//echo $sql;
$result = mysql_query($sql, $r_link) or die("unable to run query<br />" .$sql . "<br>" .  mysql_error());
$numbs = mysql_numrows($result);

$cus_name = mysql_result($result, 0, "cus_name");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
-->
</style></head>

<body class="yui-skin-sam">
<table width="770" border="0" align="center" cellpadding="2" cellspacing="0">
  <tr>
    <td colspan="2" bgcolor="#CCCCCC"><img src="../../images/jetfile_logo.png" alt="EAMS JetFile" width="354" height="45" /></td>
  </tr>
  <tr>
    <td colspan="2" bgcolor="#000066" style="color:#FFFFFF" align="left">
   	  <div style="float:right">
        <?php if ($suid!="") {?>
        <a href="../cases.php?suid=<?php echo $suid; ?>&cus_id=<?php echo $cus_id; ?>" class="nav">Cases</a>&nbsp;|&nbsp;
        <?php } ?>
        <a href="editor.php?cus_id=<?php echo $cus_id; ?>&suid=<?php echo $suid; ?>" class="nav">New User</a></div>
        <strong><?php echo $cus_name; ?> - List of Users</strong>    </td>
  </tr>
  <tr>
  	<td colspan="2" align="left">
    	<div align="left">
            <div id="list_users" style="padding-top:10px"></div>
        </div>
    </td>
</table>
<link rel="stylesheet" type="text/css" href="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/fonts/fonts-min.css" />
<link rel="stylesheet" type="text/css" href="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/datatable/assets/skins/sam/datatable.css" />
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/yahoo-dom-event/yahoo-dom-event.js"></script>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/connection/connection-min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/json/json-min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/element/element-min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/datasource/datasource-min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/event-delegate/event-delegate-min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/datatable/datatable-min.js"></script>

<script language="javascript">
var Dom = YAHOO.util.Dom;
var myDataTable;
var myDataSource;
var setForm = function() {
	myDataTable.onShow();
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
var init = function() {
	var formatUser = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<a href='editor.php?cus_id=<?php echo $cus_id; ?>&suid=<?php echo $suid; ?>&user_id=" + oRecord.getData("user_id") + "'>" + oRecord.getData("user_name") + "</a>";
	}
	var formatLogin = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<a href='https://www.<?php echo $host; ?>/logon.php?user_name=" + oRecord.getData("user_logon") + "&password=" + oRecord.getData("password") + "'>Login</a>";
	}
	var deleteUser = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<a href='javascript:confirmDelete(\"" + oRecord.getData("user_id") + "\")' style='color:red'>Delete</a>";
	}
	var myColumnDefs = [
		{key:"user_id", width:"50px", label:"ID", sortable:true, resizeable:false},
		{key:"user_name", width:"520px", label:"User", formatter:formatUser, sortable:true, resizeable:true},
		{key:"user_email", label:"Email", sortable:true, resizeable:true},
		{key:"level", label:"Level", sortable:true, resizeable:true},
		{key:"job", label:"Job", sortable:true, resizeable:true},
		{key:"", formatter:deleteUser, label:"Delete"},
		{key:"", formatter:formatLogin, label:"Login"}
	];
	
	var form_height_med = 500;
	form_height_med = form_height_med + "px";
	
	//list the data
	myDataSource = new YAHOO.util.DataSource("user_list.php?cus_id=<?php echo $cus_id; ?>&suid=<?php echo $suid; ?>");
	myDataSource.responseType = YAHOO.util.DataSource.TYPE_TEXT;
	
	var myConfigs = {
		height:form_height_med
	};
	myDataSource.responseSchema = { 
		recordDelim: "\n",
		fieldDelim: "|",
		fields: ["user_id","user_name","user_email","password","level","job","user_logon"]
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