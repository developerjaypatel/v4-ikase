<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

include ("../../text_editor/ed/functions.php");
include ("../../text_editor/ed/datacon.php");

$cus_id = -1;

if($_SERVER['SERVER_NAME']=="starlinkcms.com")
{
  $application = "StarLinkCMS";
  $application_url = "https://starlinkcms.com/";
  $application_logo = "logo-starlinkcms.png";
}
else
{
  $application = "iKase";
  $application_url = "https://v2.ikase.org/";
  $application_logo = "ikase_logo.png";
}

$admin_client = passed_var("admin_client");
$administrator = passed_var("administrator");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="nofollow,noindex" />
<title>Cards</title>
</head>
<style>
.yui-dt td {
	font-family:arial;
	font-size:9pt; 
	vertical-align:top;	
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
<table width="1280" border="0" align="center" cellpadding="2" cellspacing="0">
  <tr>
    <td colspan="2" bgcolor="#CCCCCC"><img src="../../img/<?= $application_logo; ?>" alt="<?= $application; ?>" height="90" /></td>
  </tr>
  <tr>
    <td colspan="2" bgcolor="#CCCCCC">
    	<div style="float:right">
        	<a href="../../index.html">Logout</a>&nbsp;|&nbsp;
            <a href="../owner/index.php?suid=<?php echo $suid; ?>">List of Administrators</a>&nbsp;|&nbsp;
            <a href="javascript:getEmails()">Export Emails</a>&nbsp;|&nbsp;
            <a href="card_list.php?suid=<?php echo $suid; ?>&labels=y" target="_blank">Export Labels</a>
        </div>
        <strong>List of Cards</strong>    </td>
  </tr>
  <tr>
  	<td colspan="2" align="left">
    	<div align="left">
            <div id="list_cards" style="padding-top:10px; width:100%"></div>
        </div>
    </td>
  </tr>
</table>
<div id="list_emails_holder" style="display:none; position:absolute; top: 120px; width:100%; margin-left:auto; margin-right:auto; text-align:center">
	<div style="width:700px; margin-left:auto; margin-right:auto; border:1px solid black; background:#EDEDED; padding:5px">
        <div class="hd" style="display:; text-align:left">
            <div style="float:right"><a href="javascript:copyList()">select list</a>&nbsp;|&nbsp;<a href="javascript:hideEmails()">close</a></div>
            <h2>Emails</h2>
        </div>
        <div class="bd" style="margin:0px; padding-left:2px" id="event_td">
          <textarea style="width:680px; height:200px; text-transform:lowercase" rows="6" id="list_emails"></textarea>
        </div>
        <div class="ft" style="display:none">&nbsp;</div>
    </div>
</div>
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
var getEmails = function (inhouse_id) {
	//logEvent("delete customer");

	var theUrl = "card_list.php";
	this.sentData = "admin_client=<?php echo $admin_client; ?>&owner_id=<?php echo $owner_id; ?>&suid=<?php echo $suid; ?>&show_emails=y";
	//alert(sendSetUrl + '?' + this.sentData);
	var request = YAHOO.util.Connect.asyncRequest('POST', theUrl,
	   {success: function(o){
			response = o.responseText.toLowerCase();
			//mark it saved
			Dom.get("list_emails").value = response;
			Dom.get("list_emails_holder").style.display = "";
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
var hideEmails = function() {
	Dom.get("list_emails_holder").style.display = "none";
}
var copyList = function() {
	Dom.get("list_emails").select();
}
var setForm = function() {
	myDataTable.onShow();
}
var init = function() {
	//panel position
	var xpos = 700;
	var ypos = 150;
	//alert(screen_width_full);
	var pos_xy = new Array(xpos,ypos);
	
	var formatEmail = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<a href='mailto:" + oRecord.getData("email") + "'>" + oRecord.getData("email") + "</a>";
	}
	var formatName = function(elCell, oRecord, oColumn, sData) {
		if (oRecord.getData("last_name")!="") {
			elCell.innerHTML = oRecord.getData("last_name") + ", " + oRecord.getData("first_name");
		} else {
			elCell.innerHTML = oRecord.getData("first_name");
		}
	}
	var formatAddress = function(elCell, oRecord, oColumn, sData) {
		var arrAddress = [];
		if ( oRecord.getData("street")!="") {
			arrAddress[arrAddress.length] =  oRecord.getData("street");
		}
		if ( oRecord.getData("street2")!="") {
			arrAddress[arrAddress.length] =  oRecord.getData("street2");
		}
		if ( oRecord.getData("city")!="") {
			arrAddress[arrAddress.length] =  oRecord.getData("city");
		}
		if ( oRecord.getData("state")!="") {
			arrAddress[arrAddress.length] =  oRecord.getData("state");
		}
		if ( oRecord.getData("zip")!="") {
			arrAddress[arrAddress.length] =  oRecord.getData("zip");
		}
		elCell.innerHTML = arrAddress.join(", ");
	}
	///users/index.php?suid=014f96e0c85c86b
	var myColumnDefs = [
		{key:"name", label:"Name", formatter:formatName, sortable:true, resizeable:true},
		{key:"email", label:"Email", formatter:formatEmail, sortable:true, resizeable:true},
		{key:"phone", label:"Phone"},
		{key:"address", label:"City", formatter:formatAddress, sortable:true, resizeable:true}
	];
	
	var form_height_med = 600;
	form_height_med = form_height_med + "px";
	
	//list the data
	myDataSource = new YAHOO.util.DataSource("card_list.php?admin_client=<?php echo $admin_client; ?>&owner_id=<?php echo $owner_id; ?>&suid=<?php echo $suid; ?>");
	myDataSource.responseType = YAHOO.util.DataSource.TYPE_TEXT;
	
	var myConfigs = {
		height:form_height_med
	};
	myDataSource.responseSchema = { 
		recordDelim: "\n",
		fieldDelim: "|",
		fields: ["first_name","last_name","job","company","email","street","street2","city","state","zip","phone","mobile","fax"]
	};
	
	myDataTable = new YAHOO.widget.ScrollingDataTable("list_cards", myColumnDefs,
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
