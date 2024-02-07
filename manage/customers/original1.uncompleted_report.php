<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>
<?php
include("../../eamsjetfiler/datacon.php");
include("../../eamsjetfiler/functions.php");

$cus_id = -1;
include("../../logon_check.php");

$admin_client = passed_var("admin_client");
if ($host!="" && $admin_client=="") {
	if($host=="cajetfile.com") {
		$admin_client = "1001001";
	} else {
		$admin_client = "8599040";
		$admin_client = "7757577";	
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Customers</title>
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
    <td colspan="2" bgcolor="#CCCCCC"><img src="../../images/jetfile_logo.png" alt="EAMS JetFile" width="354" height="45" /></td>
  </tr>
  <tr>
    <td colspan="2" bgcolor="#CCCCCC">
    	<div style="float:right">
        <?php if ($suid!="") {?>
        <a href="../../cases.php?suid=<?php echo $suid; ?>&cus_id=-1">Return to Cases</a>&nbsp;|&nbsp;
        <?php } ?>
        <a href="editor.php?admin_client=<?php echo $admin_client; ?>&suid=<?php echo $suid; ?>">New Customer</a></div>
        <strong>List of Customers</strong>    </td>
  </tr>
  <tr>
  	<td colspan="2" align="left">
    	<div align="left">
            <div id="list_customers" style="padding-top:10px"></div>
        </div>
    </td>
  </tr>
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
var confirmDelete = function(cus_id) {
	var confirmit=confirm("Are you sure you want to delete this Customer");
	if (confirmit) {
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
var setXL = function (cus_id) {
	//logEvent("delete customer");

	var sendSetUrl = "set_xl.php";
	this.sentData = "cus_id=" + cus_id + "&suid=<?php echo $suid; ?>";
	//alert(sendSetUrl + '?' + this.sentData);
	var request = YAHOO.util.Connect.asyncRequest('POST', sendSetUrl,
	   {success: function(o){
			response = o.responseText;
			//mark it saved
			//alert(response);
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
	var formatCustomer = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<a href='editor.php?admin_client=<?php echo $admin_client; ?>&suid=<?php echo $suid; ?>&cus_id=" + oRecord.getData("cus_id") + "'>" + oRecord.getData("cus_name") + "</a>";
	}
	var formatLogin = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<a href='https://www.<?php echo $host; ?>/logon.php?user_name=" + oRecord.getData("eams_no") + "&pwd=" + oRecord.getData("pwd") + "' target='_blank'>Login</a>";
	}
	var formatUsers = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<a href='https://www.<?php echo $host; ?>/users/index.php?cus_id=" + oRecord.getData("cus_id") + "&suid=<?php echo $suid; ?>' target='_blank'>Users</a>";
	}
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
	var formatExport = function(elCell, oRecord, oColumn, sData) {
		if (oRecord.getData("inhouse_id")!="0") {
			elCell.innerHTML = "<span style='background:green;color:white'>&#10003</span>";
		} else {
			elCell.innerHTML = "";
		}
	}
	var formatID = function(elCell, oRecord, oColumn, sData) {
		if (oRecord.getData("inhouse_id")=="0") {
			elCell.innerHTML = "";
		} else {
			elCell.innerHTML = "<a href='javascript:checkAddress(" + oRecord.getData("inhouse_id") + ")'>" + oRecord.getData("inhouse_id") + "</a>";
		}
	}
	///users/index.php?suid=014f96e0c85c86b
	var myColumnDefs = [
		{key:"cus_id", width:"50px", label:"ID", sortable:true, resizeable:false},
		{key:"eams_no", width:"50px", label:"EAMS #", sortable:true, resizeable:false},
		{key:"cus_name", width:"520px", label:"Customer", formatter:formatCustomer, sortable:true, resizeable:true},
		{key:"", label:"XL", formatter:formatXL},
		{key:"export", label:"Order Form", formatter:formatExport},
		{key:"cus_city", label:"City", sortable:true, resizeable:true},
		{key:"cus_zip", label:"Zip", sortable:true, resizeable:true},
		{key:"inhouse_id", formatter:formatID, label:"Matrix ID", sortable:true, resizeable:true},
		{key:"", formatter:formatLogin, label:"Login"},
		{key:"", formatter:formatUsers, label:"Users"},
		{key:"", formatter:formatDelete, label:"Delete"}
	];
	
	var form_height_med = 600;
	form_height_med = form_height_med + "px";
	
	//list the data
	myDataSource = new YAHOO.util.DataSource("customer_list.php?admin_client=<?php echo $admin_client; ?>");
	myDataSource.responseType = YAHOO.util.DataSource.TYPE_TEXT;
	
	var myConfigs = {
		height:form_height_med
	};
	myDataSource.responseSchema = { 
		recordDelim: "\n",
		fieldDelim: "|",
		fields: ["cus_id","eams_no","cus_name_full","cus_name","cus_street","cus_city","cus_state","cus_zip","admin_client_id","password","xl_filed","pwd","inhouse_id"]
	};
	
	myDataTable = new YAHOO.widget.ScrollingDataTable("list_customers", myColumnDefs,
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
