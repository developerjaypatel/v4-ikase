<?php
include("../../api/manage_session.php");
session_write_close();

//die(print_r($_SESSION));
include("sec.php");

$blnOwnerAdmin = ($_SESSION["user_role"] == "owner" && ($_SESSION["user_id"] == 11 || $_SESSION["user_id"] == 12));
//die(print_r($_SESSION));

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

include ("../../api/connection.php");

$cus_id = -1;

$admin_client = passed_var("admin_client");
$administrator = passed_var("administrator");

$sql = "SELECT `owner_id`, `admin_client`, `name`, `owner_email`, `url`, `password`, `pwd`, `session_id`, `dateandtime`, `ip_address`
FROM `owner`
WHERE 1
AND `owner_id` > 4
ORDER BY `name`";
//die($query);
//$result = mysql_query($query, $r_link) or die("unable to run query<br />" .$sql . "<br>" .  mysql_error());
//$numbs = mysql_numrows($result);

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);  
	$stmt->execute();
	$owners = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
}

$arrOptions = array();
$selected = "";
if ($administrator=="") {
	$selected = " selected";
	$administrator = 0;
}
$option = "<option value=''" . $selected . ">Select Marketer</option>";
$arrOptions[] = $option;
//for ($int=0;$int<$numbs;$int++) {
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

//let's get customers

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
    <td colspan="2" bgcolor="#CCCCCC"><img src="../../img/iklock_logo.png" alt="iKlock" /></td>
  </tr>
  <tr>
    <td colspan="2" bgcolor="#CCCCCC">
    	<div style="float:right">
        	<a href="../../index.php">Logout</a>
            <a href="../owner/index.php">List of Administrators</a>&nbsp;|&nbsp;
            <a href="../cards/index.php">Cards</a>&nbsp;|&nbsp;
            <a href="editor.php">New Customer</a>
        </div>
        <div style="float:right">Current Active Users: <span id="total_users"></span>&nbsp;<span id="total_invoiced"></span></div>
        <strong>List of Customers</strong>    </td>
  </tr>
  <tr>
  	<td colspan="2" align="left">
    	<div align="left">
        	<div>
                <a href="javascript:showList()" id="show_list_link">Show List</a>
                <a href="javascript:hideList()" id="hide_list_link" style="display:none">Hide List</a>
                <div style="display:inline-block">
                    <input type="text" id="search_box" placeholder="Search for customer" onkeyup="scheduleSearch()" />
                    <div id="search_result"></div>
                </div>
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
function postForm(path, params, method, target) {
    method = method || "post"; // Set method to post by default if not specified.
	target = target || "_blank"; // Set method to post by default if not specified.
	
    // The rest of this code assumes you are not using a library.
    // It can be made less wordy if you use one.
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);
	form.setAttribute("target", target);

    for(var key in params) {
        if(params.hasOwnProperty(key)) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);

            form.appendChild(hiddenField);
         }
    }

    document.body.appendChild(form);
    form.submit();
}
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
function getCustomers() {
	var url = "customer_list.php";
	
	var r = new XMLHttpRequest();
	r.open("GET", url, true);
	r.onreadystatechange = function () {
	  if (r.readyState != 4 || r.status != 200) {
		return;
	  } else {
		data = r.responseText;
		document.getElementById("list_customers").innerHTML = data;
		addCustomerListeners();
	  }
	};
	r.send();
}
function addCustomerListeners() {
	var classname = document.getElementsByClassName("edit_customer");
	
	for (var i = 0; i < classname.length; i++) {
		classname[i].addEventListener('click', editCustomer, false);
	}
}
function editCustomer(event) {
	var element = event.currentTarget;
	event.preventDefault();
	var cus_id = element.id.replace("edit_customer_", "");
	
	var url = "editor.php";
	var params = {cus_id: cus_id};
	postForm(url, params, "post", "_self");
}
function showList() {
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
<script language="javascript">
var current_cus_id;
var confirmDelete = function(cus_id) {
	var confirmit=confirm("Are you sure you want to delete this Customer");
	if (confirmit==true) {
		deleteTheCustomer(cus_id);
	}
}
var refreshDataSource = function() {
	//rewrite
}
var deleteTheCustomer = function (cus_id) {
	//rewrite in vanilla
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
	//rewrite in vanilla

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
var setForm = function() {
	myDataTable.onShow();
	
	document.getElementById("search_box").focus();
}
var total_users = 0;
var total_invoiced = 0;
var total_paid = 0;
var init = function() {
	getCustomers();
}
var saveUpload = function(folder_name) {
	//rewrite in vanilla
	
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

var listAdmin = function() {
	var administrator = Dom.get("administrator");
	document.location.href="index.php?suid=<?php echo $suid; ?>&administrator=" + administrator.value;
}
var showNotes = function(cus_id) {
	document.location.href = "../notes/index.php?suid=<?php echo $suid; ?>&the_cus_id=" + cus_id;
}
window.addEventListener("load", function load(event){
	init();
});
</script>
</body>
</html>
