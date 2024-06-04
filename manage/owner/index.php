<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

if($_SERVER['SERVER_NAME']=="v2.starlinkcms.com")
{
  $application = "StarLinkCMS";
  $application_url = "https://v2.starlinkcms.com/";
  $application_logo = "logo-starlinkcms.png";
}
else
{
  $application = "iKase";
  $application_url = "https://v2.ikase.org/";
  $application_logo = "ikase_logo.png";
}
?>
<?php
include ("../../text_editor/ed/functions.php");
include ("../../text_editor/ed/datacon.php");

$cus_id = -1;

$admin_client = passed_var("admin_client");
$administrator = passed_var("administrator");
if ($host!="" && $admin_client=="") {

}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="nofollow,noindex" />
<title>Matrix Employees</title>
</head>
<style>
.yui-dt td {
	font-family:arial;
	font-size:9pt; 
	vertical-align:top;	
}
.yui-skin-sam .yui-dt .yui-dt-col-cus_id {
	width: 5px;
	text-align: right;
}
.yui-skin-sam .yui-dt .yui-dt-col-export {
	width: 100px;
	text-align: right;
}
</style>
<body class="yui-skin-sam">
<table width="1280" border="0" align="center" cellpadding="2" cellspacing="0">
  <tr>
    <td colspan="2" bgcolor="#CCCCCC"><img src="../../img/<?= $application_logo; ?>" alt="<?= $application; ?>" height="90" /></td>
  </tr>
  <tr>
    <td colspan="2" bgcolor="#CCCCCC">
    	<div style="float:right">
        	<a href="../customers/index.php?suid=<?php echo $suid; ?>">List of Customers</a>
            <a href="editor.php?suid=<?php echo $suid; ?>">New Administrator</a>
            
        </div>
        <strong>List of Administrators</strong>    </td>
  </tr>
  <tr>
  	<td colspan="2" align="left">
    	<div align="left">
            <div id="list_owners" style="padding-top:10px"></div>
        </div>
    </td>
  </tr>
</table>
<?php include ("yahoo.php"); ?>
<script language="javascript">
var Dom = YAHOO.util.Dom;
var myDataTable;
var myDataSource;
var confirmDelete = function(admin_id) {
	var confirmit=confirm("Are you sure you want to delete this Employee");
	if (confirmit==true) {
		deleteTheOwner(admin_id);
	}
}
var refreshDataSource = function() {
	this.sentData = "";
	myDataSource.sendRequest(this.sentData, myDataTable.onDataReturnInitializeTable, myDataTable);
	
	myDataTable.onShow();
}
var deleteTheOwner = function (admin_id) {
	//logEvent("delete owner");

	var sendDeleteUrl = "owner_delete.php";
	this.sentData = "admin_id=" + admin_id + "&suid=<?php echo $suid; ?>";
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
var setForm = function() {
	myDataTable.onShow();
}
var init = function() {
	var formatEmployee = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<a href='editor.php?admin_client=<?php echo $admin_client; ?>&suid=<?php echo $suid; ?>&admin_id=" + oRecord.getData("owner_id") + "'>" + oRecord.getData("name") + "</a>";
	}
	var formatLastLogin = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = oRecord.getData("dateandtime");
	}
	var formatDelete = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<a href='javascript:confirmDelete(\"" + oRecord.getData("owner_id") + "\")' style='color:red'>Delete</a>";
	}

	///users/index.php?suid=014f96e0c85c86b
	var myColumnDefs = [
		{key:"owner_id", width:"50px", label:"ID", sortable:true, resizeable:false},
		{key:"name", width:"520px", label:"Employee", formatter:formatEmployee, sortable:true, resizeable:true},
		{key:"admin_client", label:"Logon", sortable:true, resizeable:true},
		{key:"owner_email", label:"Email", sortable:true, resizeable:true},
		{key:"dateandtime", label:"Last Login", formatter:formatLastLogin},
		{key:"", formatter:formatDelete, label:"Delete"}
	];
	
	var form_height_med = 600;
	form_height_med = form_height_med + "px";
	
	//list the data
	myDataSource = new YAHOO.util.DataSource("owner_list.php?admin_client=<?php echo $admin_client; ?>&owner_id=<?php echo $owner_id; ?>&suid=<?php echo $suid; ?>");
	myDataSource.responseType = YAHOO.util.DataSource.TYPE_TEXT;
	
	var myConfigs = {
		height:form_height_med
	};
	myDataSource.responseSchema = { 
		recordDelim: "\n",
		fieldDelim: "|",
		fields: ["owner_id","admin_client","name","owner_email","dateandtime"]
	};
	
	myDataTable = new YAHOO.widget.ScrollingDataTable("list_owners", myColumnDefs,
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
