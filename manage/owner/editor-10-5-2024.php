<?php
//print_r($_SERVER);
include ("../../text_editor/ed/functions.php");
include ("../../text_editor/ed/datacon.php");

$admin_id = passed_var("admin_id");

if ($admin_id>0) {
	$query = "SELECT  `owner_id`, `admin_client`, `name`, `owner_email`, `url`, `password`, `pwd`, `session_id`, `dateandtime`, `ip_address`
	FROM cse_owner
	WHERE owner_id = ?";
	$row = DB::runOrDie($query, $admin_id)->fetch();
    $owner_id = $row->owner_id;
	$admin_client = $row->admin_client;
	$name = $row->name;
	$owner_email = $row->owner_email;
}

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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Administrator Information</title>
</head>
<style>
.yui-skin-sam .yui-dt .yui-dt-col-attorney_name {
	width: 250px;
	text-align: left;
}
.form_label {
	float:left;
	display:block;
	width:7.55em;
}
</style>
<body class="yui-skin-sam">
<script type="text/javascript" src="../../typecast_1.4.js"></script>
<script type="text/javascript" src="../../typecast.config.js"></script>
<form action="update.php" method="post" enctype="multipart/form-data" name="form1" id="form1">
<input type="hidden" value="<?php echo $admin_id; ?>" name="admin_id" id="admin_id" />
<input type="hidden" value="<?php echo $owner_id; ?>" name="owner_id" id="owner_id" />
<input type="hidden" value="<?php echo $suid; ?>" name="suid" id="suid" />
  <table width="980" border="0" align="center" cellpadding="2" cellspacing="0">
      <tr>
        <td colspan="2" bgcolor="#CCCCCC"><img src="../../img/<?=$application_logo; ?>" alt="<?=$application;?>" height="90" /></td>
      </tr>
      <tr>
        <td colspan="2" bgcolor="#CCCCCC"><div style="float:right"><a href="index.php?admin_client=<?php echo $admin_client; ?>&suid=<?php echo $suid; ?>">Administrators</a></div>
            <strong>Administrator Information</strong></td>
      </tr>
    </table>
    <table width="980" border="0" align="center" cellpadding="3" cellspacing="0">
      <tr>
        <td colspan="6"><hr color="#000000" />        </td>
      </tr>
      
      <tr>
        <td colspan="6" bgcolor="#EDEDED"><strong>Administrator Information</strong></td>
      </tr>
      <tr>
        <td colspan="6" nowrap="nowrap" bgcolor="#EDEDED">
        <div style="float:right; display:none">
          <input name="read" type="checkbox" value="r"<?php if ($blnRead) { echo " checked"; } ?> />
          List      &nbsp;|&nbsp;
<input name="write" type="checkbox" value="w"<?php if ($blnWrite) { echo " checked"; } ?> />
File<?php if($host=="cajetfile.com") { ?>&nbsp;|&nbsp;<input name="export" type="checkbox" value="e"<?php if ($blnExport) { echo " checked"; } ?> />Auto-export<?php } ?><br />
Permissions </div>
        <input name="name" type="text" class="insurance_info carrier " id="name" value="<?php echo $name; ?>" size="50" autocomplete="off" tabindex="2" />
          <br />
 Name</td>
      </tr>
      <tr>
        
        <td colspan="4" nowrap="nowrap" bgcolor="#EDEDED"><input name="admin_client" id="admin_client" type="text" value="<?php echo $admin_client; ?>" class="insurance_info carrier " tabindex="3" />
        <br />
        User Name</td>
        
        <td colspan="6" nowrap="nowrap" bgcolor="#EDEDED"><input name="password" type="text" id="password" size="14" tabindex="4" />
        <br />
        Password</td>
      </tr>
      <tr>
        <td colspan="2" bgcolor="#EDEDED"><input name="owner_email" type="text" class="insurance_info carrier " id="owner_email" value="<?php echo $owner_email; ?>" size="30" placeholder="email" autocomplete="off" /></td>
        <td width="5%" align="right" bgcolor="#EDEDED">&nbsp;</td>
        <td width="23%" bgcolor="#EDEDED">&nbsp;</td>
        <td width="7%" align="right" bgcolor="#EDEDED">&nbsp;</td>
        <td width="24%" bgcolor="#EDEDED">&nbsp;</td>
      </tr>
      <tr>
        <td width="10%" bgcolor="#EDEDED">Email</td>
        <td width="31%" bgcolor="#EDEDED">&nbsp;</td>
        <td align="right" bgcolor="#EDEDED">&nbsp;</td>
        <td bgcolor="#EDEDED">&nbsp;</td>
        <td align="right" bgcolor="#EDEDED">&nbsp;</td>
        <td bgcolor="#EDEDED">&nbsp;</td>
      </tr>
      <?php if ($host != "kustomweb.com") { ?>
      <?php if ($owner_id!="") { ?>
      <?php } ?>
      <?php } ?>
      <tr>
        <td colspan="6" align="left"><input type="submit" name="submit" id="submit" value="Save" /></td>
      </tr>
      <?php if ($host != "kustomweb.com") { ?>
      <?php if ($owner_id!="") { ?>
      <tr>
        <td colspan="6">&nbsp;</td>
      </tr>
      <?php } ?> <?php } ?>
  </table>
</form>
<?php include ("yahoo.php"); ?>
<script language="javascript">
var Dom = YAHOO.util.Dom;
var Event = YAHOO.util.Event;
var myCarrierDataSource;
var myCarrierDataTable;
<?php if ($owner_id!="") { ?>
var myDataSource;
var myDataTable;
<?php } ?>
var typeSearch = function(e, type) {
	if(window.event) // IE
	{
		keynum = e.keyCode
	}
	else if(e.which) // Netscape/Firefox/Opera
	{
		keynum = e.which
	}
	
	if (keynum == 8) {
		var person_name = Dom.get("name");
		var the_value = person_name.value;
		if (the_value == "") {
			Dom.setStyle("list_" + "cus_searches", "display", "none");
			//alert("back hide");
		}
	}
	return;
}
var showAttorneyInfo = function(id) {
	if (id=="") {
		return;
	}
	mysentData = "attorney_id=" + id + "&owner_id=<?php echo $owner_id; ?>";
	var eamsURL = "attorney_list.php";
	//alert(eamsURL + '?' + mysentData);		
	
	if (mysentData!='') {	
		//logEvent("about to send request");
		var request = YAHOO.util.Connect.asyncRequest('POST', eamsURL,
		   {success: function(o){
				response = o.responseText;
				//alert(response);
				if (response != "") {
					var arrData = response.split("|");
					var attorney_id = Dom.get("attorney_id");
					attorney_id.value = arrData[0];
					var first_name = Dom.get("attorney_name_first");
					first_name.value = arrData[7];
					var middle_name = Dom.get("attorney_name_middle");
					middle_name.value = arrData[8];
					var last_name = Dom.get("attorney_name_last");
					last_name.value = arrData[9];
					var phone = Dom.get("attorney_phone");
					phone.value = arrData[2];
					var attorney_fax = Dom.get("attorney_fax");
					attorney_fax.value = arrData[3];
					var attorney_email = Dom.get("attorney_email");
					attorney_email.value = arrData[4];
					//default attorney
					var default_attorney = Dom.get("default_attorney");
					if (arrData[6]=="Y") {
						default_attorney.checked = true;
					}
					Dom.get("contact_prefix").innerHTML = "Edit";
					Dom.setStyle("new_contact_holder", "display", "");
				}
				//logEvent("saved");
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
var newContact = function() {
	var attorney_id = Dom.get("attorney_id");
	attorney_id.value = "";
	var first_name = Dom.get("attorney_name_first");
	first_name.value = "";
	var middle_name = Dom.get("attorney_name_middle");
	middle_name.value = "";
	var last_name = Dom.get("attorney_name_last");
	last_name.value = "";
	var phone = Dom.get("attorney_phone");
	phone.value = "";
	var attorney_fax = Dom.get("attorney_fax");
	attorney_fax.value = "";
	var attorney_email = Dom.get("attorney_email");
	attorney_email.value = "";
	//default attorney
	var default_attorney = Dom.get("default_attorney");
	default_attorney.checked = false;
	
	Dom.get("contact_prefix").innerHTML = "New";
	Dom.setStyle("new_contact_holder", "display", "none");
}
var showFirm = function(admin_client, type) {
	if (admin_client=="") {
		return;
	}
	mysentData = "type=reps&query=" + admin_client;
	var eamsURL = "../../check_eams.php";
	//alert(eamsURL + '?' + mysentData);		
	
	if (mysentData!='') {	
		//alert("about to send request");
		var request = YAHOO.util.Connect.asyncRequest('POST', eamsURL,
		   {success: function(o){
				response = o.responseText;
				//alert(response);
				if (response != "") {
					var arrData = response.split("|");
					var admin_client = Dom.get("admin_client");
					admin_client.value = arrData[0];
					var name = Dom.get("name");
					name.value = arrData[1];
					var street = Dom.get("cus_street");
					street.value = arrData[2];
					if (arrData[3]!="") {
						street.value += " " + arrData[3];
					}
					var city = Dom.get("cus_city");
					city.value = arrData[4];
					var state = Dom.get("cus_state");
					state.value = arrData[5];
					var zip_code = Dom.get("owner_email");
					zip_code.value = arrData[6];
					
					var phone = Dom.get("cus_phone");
					phone.value = arrData[7];
					
					hideInfo();
				}
				//logEvent("saved");
			},
		   failure: function(){
			   //
			   alert("failure");
			},
		   after: function(){
			   //
			   //alert("after");
			},
		   scope: this}, mysentData);
		 //logEvent("appointment save done");
	}
}

var emptySearch = function (type) {
	var search_item = Dom.get("name");
	var the_value = search_item.value;
	if (the_value=="Search") {
		search_item.value = "";
		Dom.setStyle(search_item, "color", "black");
	}
}

var init = function() {
	<?php if ($owner_id!="") { ?>
	var formatActive = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<a href='javascript:changeStatus(\"" + oRecord.getData("attorney_id") + "\",\"" + oRecord.getData("active") + "\")' title='Click to change active status'>" + oRecord.getData("active") + "</a>";
	}
	var formatDelete = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<a href='javascript:deleteAttorney(\"" + oRecord.getData("attorney_id") + "\")' title='Click to delete Contact' style='color:red'>Del</a>";
	}
	var formatAttorney = function(elCell, oRecord, oColumn, sData) {
		//edit mode
		elCell.innerHTML = "<a href='javascript:showAttorneyInfo(" + oRecord.getData("attorney_id") + ")'>" + oRecord.getData("attorney_name") + "</a>";
	}
	var formatDefault = function(elCell, oRecord, oColumn, sData) {
		if (oRecord.getData("default_attorney")=="N") {
			elCell.innerHTML = "<a href='javascript:changeDefault(\"" + oRecord.getData("attorney_id") + "\",\"" + oRecord.getData("default_attorney") + "\")' title='Click to change default attorney'>Make Default</a>";
		} else {
			elCell.innerHTML = oRecord.getData("default_attorney");
		}
		//elCell.innerHTML = "Edit";
	}
	<?php } ?>		
	
	<?php if ($owner_id!="") { ?>
	///users/index.php?suid=014f96e0c85c86b
	var myColumnDefs = [
		{key:"attorney_id", width:"50px", label:"ID", sortable:true, resizeable:false},
		{key:"attorney_name", formatter:formatAttorney, width:"350px", label:"Doctor", sortable:true, resizeable:true},
		{key:"phone", width:"350px", label:"Phone", sortable:false, resizeable:false},
		{key:"fax", width:"350px", label:"Fax", sortable:false, resizeable:false},
		{key:"email", width:"350px", label:"Email", sortable:false, resizeable:false},
		{key:"default", width:"20px", label:"Default Doctor", formatter:formatDefault},
		{key:"active", width:"20px", label:"Active", formatter:formatActive},
		{key:"delete", width:"20px", label:"Delete", formatter:formatDelete}
	];
	
	form_height_med = 200;
	form_height_med = form_height_med + "px";
	
	//list the data
	myDataSource = new YAHOO.util.DataSource("attorney_list.php?owner_id=<?php echo $owner_id; ?>");
	myDataSource.responseType = YAHOO.util.DataSource.TYPE_TEXT;
	
	var myConfigs = {
		height:form_height_med
	};
	myDataSource.responseSchema = { 
		recordDelim: "\n",
		fieldDelim: "|",
		fields: ["attorney_id","attorney_name","phone", "fax", "email", "active","default_attorney"]
	};
	
	
	myDataTable = new YAHOO.widget.ScrollingDataTable("list_attorneys", myColumnDefs,
						myDataSource, myConfigs);
	
	return {
		oDS: myDataSource,
		oDT: myDataTable
	};
	<?php } ?>	
}
<?php if ($owner_id!="") { ?>
var refreshAttorneyDataSource = function() {
	myDataSource.sendRequest(this.sentData, myDataTable.onDataReturnInitializeTable, myDataTable);
	
	myDataTable.onShow();
}

var changeDefault = function(id, status) {
	var clearUrl = "change_default.php";
	mysentData = "owner_id=<?php echo $owner_id; ?>&status=" + status + "&id=" + id;
	//alert(mysentData);
	if (mysentData!='') {	
		//logEvent("about to send request");
		var request = YAHOO.util.Connect.asyncRequest('POST', clearUrl,
		   {success: function(o){
				response = o.responseText;
				//alert(response);
				refreshAttorneyDataSource();
				//alert("cleared");
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
var deleteAttorney = function(id, status) {
	var clearUrl = "delete_attorney.php";
	mysentData = "owner_id=<?php echo $owner_id; ?>&status=Y&id=" + id;
	//alert(mysentData);
	if (mysentData!='') {	
		//logEvent("about to send request");
		var request = YAHOO.util.Connect.asyncRequest('POST', clearUrl,
		   {success: function(o){
				response = o.responseText;
				alert(response);
				refreshAttorneyDataSource();
				//alert("cleared");
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
<?php } ?>
var hideInfo = function() {
	Dom.setStyle("list_cus_searches", "display", "none");
	//alert("hidden");
}
var logEvent = function (msg, status) {
	YAHOO.log(msg, status);
}
YAHOO.util.Event.addListener(window, "load", init);
</script>
<script language="javascript" type="text/javascript" src="../../mask_phone.js"></script>
</body>
</html>
