<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>
<?php
include("../../eamsjetfiler/datacon.php");
include("../../eamsjetfiler/functions.php");

$the_cus_id = passed_var("the_cus_id");
include("../logon_check.php");

$sql = "SELECT cus_name_first, cus_name_last, cus_name, cus_street, cus_city, cus_state, cus_zip FROM tbl_customer 
WHERE cus_id = " . $the_cus_id;
$row = DB::runOrDie($sql)->fetch();

$cus_name       = $row->cus_name;
$cus_name_first = $row->cus_name_first;
$cus_name_last  = $row->cus_name_last;
$the_cus_street = $row->cus_street;
$the_cus_city   = $row->cus_city;
$the_cus_state  = $row->cus_state;
$the_cus_zip    = $row->cus_zip;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="nofollow,noindex" />
<title>Notes</title>
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
.yui-skin-sam .yui-dt .yui-dt-col-note_name {
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
<table width="1080" border="0" align="center" cellpadding="2" cellspacing="0">
  <tr>
    <td colspan="2" bgcolor="#CCCCCC">
    	<div style="float:right">
        	<?php
			if ($owner_id > 0) { 
				if ($host=="dmsroi.com") {
					$cus_name = "DMS Custodian Master Login<br />" . $owner_name;
				}
			}
			echo "<span style='font-size:14pt'>" . $cus_name . "</span>";
			if ($the_cus_id > 0 && $owner_id == 0) {
				echo "<br /><span style='font-size:1.1em'>";
				if ($note_name!="") {
					echo "Welcome " . $note_name . "</span>";
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
        <img src="../../images/logo_admin.png" alt="DMS Custodian" width="41" height="26" />
    </td>
  </tr>
  <tr>
    <td colspan="2" bgcolor="#000066" style="color:#FFFFFF" align="left">
   	  <div style="float:right">
        <?php if ($suid!="") {?>
        <a href="../customers/index.php?suid=<?php echo $suid; ?>" class="nav">Customers</a>
        <?php } ?>
        <?php if ($edit=="y") {?>
        &nbsp;|&nbsp;<a href="../customers/editor.php?suid=<?php echo $suid; ?>&cus_id=<?php echo $the_cus_id; ?>" class="nav">Return to Customer Screen</a>
        <?php } ?>
        </div>
        <strong><?php echo $cus_name; ?> - Notes</strong>    </td>
  </tr>
  <tr>
  	<td colspan="2" align="left">
    	<iframe src="note.php?suid=<?php echo $suid; ?>&the_cus_id=<?php echo $the_cus_id; ?>" width="100%" height="300px" scrolling="no" frameborder="0"></iframe>
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
var confirmDelete = function(note_id) {
	var confirmit=confirm("Are you sure you want to delete this Note");
	if (confirmit) {
		deleteTheNote(note_id);
	}
}
var deleteTheNote = function (note_id) {
	//alert("delete user");

	var sendDeleteUrl = "note_delete.php";
	this.sentData = "note_id=" + note_id;
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
	var formatNote = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<a href='editor.php?cus_id=<?php echo $the_cus_id; ?>&suid=<?php echo $suid; ?>&note_id=" + oRecord.getData("note_id") + "'>" + oRecord.getData("note_name") + "</a>";
	}
	var formatLogin = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<a href='../logon.php?note_name=" + oRecord.getData("note_logon") + "&pwd=" + oRecord.getData("pwd") + "'>Login</a>";
	}
	var deleteNote = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<a href='javascript:confirmDelete(\"" + oRecord.getData("note_id") + "\")' style='color:red'>Delete</a>";
	}
	var myColumnDefs = [
		{key:"note_id", width:"50px", label:"ID", sortable:true, resizeable:false},
		{key:"note_name", width:"520px", label:"Note", formatter:formatNote, sortable:true, resizeable:true},
		{key:"note_email", label:"Email", sortable:true, resizeable:true},
		{key:"level", label:"Level", sortable:true, resizeable:true},
		{key:"job", label:"Job", sortable:true, resizeable:true},
		{key:"", formatter:deleteNote, label:"Delete"},
		{key:"", formatter:formatLogin, label:"Login"}
	];
	
	var form_height_med = 500;
	form_height_med = form_height_med + "px";
	
	//list the data
	myDataSource = new YAHOO.util.DataSource("note_list.php?cus_id=<?php echo $the_cus_id; ?>&suid=<?php echo $suid; ?>");
	myDataSource.responseType = YAHOO.util.DataSource.TYPE_TEXT;
	
	var myConfigs = {
		height:form_height_med
	};
	myDataSource.responseSchema = { 
		recordDelim: "\n",
		fieldDelim: "|",
		fields: ["note_id","note_name","note_email","password","level","job","note_logon","pwd"]
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
