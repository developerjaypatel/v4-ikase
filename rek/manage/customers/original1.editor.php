<?php
include("../../../api/manage_session.php");
session_write_close();

if (!isset($_SESSION["user_plain_id"])) {
	die("no id");
}
include ("../../../text_editor/ed/functions.php");
include ("../../../text_editor/ed/datacon_rek.php");


$cus_id = passed_var("cus_id");
$admin_client = passed_var("admin_client");

if ($cus_id>0) {
	$query = "SELECT  `customer_id` cus_id, `parent_customer_id` parent_cus_id, `eams_no`, 
	`cus_barnumber`,
	`cus_name`, `cus_name_first`, `cus_name_middle`, `cus_name_last`, 
	`cus_street`, `cus_city`, `cus_state`, `cus_zip`, `cus_county`, 
	`cus_ip`, `admin_client`, `password`, `cus_email`, `cus_type`, `cus_phone`, `cus_fax`, `data_source`, `data_path`, `permissions`, `inhouse_id`, `office_manager_first`, `office_manager_last`, `office_manager_middle`, `office_manager_phone`, `office_manager_email`, cus_fedtax_id, cus_uan, corporation_rate, user_rate
	FROM `rek`.rek_customer 
	WHERE customer_id = '" . $cus_id . "'";
	$result = mysql_query($query, $r_link) or die("unable to run query<br />" .$query . "<br>" .  mysql_error());
	$int = 0;
	$cus_id = mysql_result($result, $int, "cus_id");
	$eams_no = mysql_result($result, $int, "eams_no");
	$parent_cus_id = mysql_result($result, $int, "parent_cus_id");
	$cus_eams_no = mysql_result($result, $int, "eams_no");
	$cus_barnumber = mysql_result($result, $int, "cus_barnumber");
	$cus_name = mysql_result($result, $int, "cus_name");
	$cus_name_first = mysql_result($result, $int, "cus_name_first");
	$cus_name_middle = mysql_result($result, $int, "cus_name_middle");
	$cus_name_last = mysql_result($result, $int, "cus_name_last");
	$cus_street = mysql_result($result, $int, "cus_street");
	$cus_city = mysql_result($result, $int, "cus_city");
	$cus_state = mysql_result($result, $int, "cus_state");
	$cus_zip = mysql_result($result, $int, "cus_zip");
	$cus_county = mysql_result($result, $int, "cus_county");
	$password = mysql_result($result, $int, "password");
	$cus_email = mysql_result($result, $int, "cus_email");
	$cus_phone = mysql_result($result, $int, "cus_phone");
	$cus_fax = mysql_result($result, $int, "cus_fax");
	$cus_type = mysql_result($result, $int, "cus_type");
	$cus_ip = mysql_result($result, $int, "cus_ip");
	$cus_fedtax_id = mysql_result($result, $int, "cus_fedtax_id");
	$cus_uan = mysql_result($result, $int, "cus_uan");
	$data_source = mysql_result($result, $int, "data_source");
	$data_path = mysql_result($result, $int, "data_path");
	$data_path = str_replace("/", "\\", $data_path);
	
	$permissions = mysql_result($result, $int, "permissions");
	$inhouse_id = mysql_result($result, $int, "inhouse_id");
	//$jetfile_id = mysql_result($result, $int, "jetfile_id");
	
	$user_rate = mysql_result($result, $int, "user_rate");
	$corporation_rate = mysql_result($result, $int, "corporation_rate");
	
	$office_manager_first = mysql_result($result, $int, "office_manager_first");
	$office_manager_middle = mysql_result($result, $int, "office_manager_middle");
	$office_manager_last = mysql_result($result, $int, "office_manager_last");
	$office_manager_email = mysql_result($result, $int, "office_manager_email");
	$office_manager_phone = mysql_result($result, $int, "office_manager_phone");
	
} else {
	
}
$blnRead = (strpos($permissions, "r")!==false || $cus_id=="");
$blnWrite = (strpos($permissions, "w")!==false || $cus_id=="");
$blnExport = (strpos($permissions, "e")!==false || $cus_id=="");
$blnImport = (strpos($permissions, "i")!==false || $cus_id=="");
$blnBilling = (strpos($permissions, "b")!==false || $cus_id=="");
//drop down for venue

$query = "SELECT  `customer_id` cus_id,`eams_no`, `cus_name`, `cus_name_first`, `cus_name_middle`, `cus_name_last`, `cus_street`, `cus_city`, `cus_state`, `cus_zip`, `admin_client`, `password`, `xl_filed`, `cus_email`
FROM rek_customer WHERE 1";
$query .= " ORDER BY `cus_name`";

//echo $query . "<br />";

$result = mysql_query($query, $r_link) or die("unable to run query<br />" .$query . "<br>" .  mysql_error());
$numbs = mysql_numrows($result);

if ($cus_id == "") {
	$selected = " selected";
}
$the_row = "<option value=''" . $selected .">Select from the list</option>";
$arrCustomerRows[] = $the_row;
for ($int=0;$int<$numbs;$int++) {
	$the_cus_id = mysql_result($result, $int, "cus_id");	
	$the_cus_name = mysql_result($result, $int, "cus_name");
	
	$selected = "";
	if ($the_cus_id == $parent_cus_id) {
		$selected = " selected";
	}
	$the_row = "<option value='" . $the_cus_id . "'" . $selected .">" . $the_cus_name . "</option>";
	
	//die($the_row);
	//$the_row = str_replace(" ", "&nbsp;", $the_row);
	$arrCustomerRows[] = $the_row;
}

$query_notes = "SELECT cn . *, cnc . * FROM rek.rek_notes cn INNER JOIN rek_notes_customer cnc ON cn.customer_id = cnc.customer_id AND cn.notes_uuid = cnc.notes_uuid WHERE cnc.customer_id =" . $cus_id;
$query_notes .= " ORDER BY `dateandtime` DESC";

//echo $query_notes . "<br />";

$result_notes = mysql_query($query_notes, $r_link) or die("unable to run query_notes<br />" .$query_notes . "<br>" .  mysql_error());
$numbs_notes = mysql_numrows($result_notes);
$arrRows = array();
for ($int=0;$int<$numbs_notes;$int++) {
	$customer_id = mysql_result($result_notes, $int, "customer_id");	
	$notes_id = mysql_result($result_notes, $int, "notes_id");
	$type = mysql_result($result_notes, $int, "type");	
	$subject = mysql_result($result_notes, $int, "subject");
	$note = mysql_result($result_notes, $int, "note");	
	//$enter_by = mysql_result($result_notes, $int, "enter_by");
	$enter_by = "";
	$status = mysql_result($result_notes, $int, "status");
	$dateandtime = mysql_result($result_notes, $int, "dateandtime");	
	
	$row = "<tr><td align='left' valign='top'><a href='#'>" . $notes_id . "</a></td><td align='left' valign='top'>" . $type . "</td><td align='left' valign='top'>" . $subject. "</td><td align='left' valign='top'>" . $note . "</td><!--<td align='left' valign='top'>" . $enter_by . "</td>--></tr>";
	$arrRows[] = $row;
}

//users
$query = "SELECT COUNT(`user_id`) `user_count`
FROM rek_user WHERE customer_id = " . $cus_id;
$result_users = mysql_query($query, $r_link) or die("unable to run query<br />" .$query . "<br>" .  mysql_error());
$user_count = mysql_result($result_users, 0, "user_count");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Customer Information</title></head>
<style>
.yui-skin-sam th {
	font-size:0.87em;
	font-weight:bold;
}
.yui-skin-sam td {
	font-size:0.87em;
}
.yui-skin-sam .yui-dt .yui-dt-col-attorney_name {
	width: 250px;
	text-align: left;
}
.yui-skin-sam .yui-dt .yui-dt-col-note_date {
	width: 80px;
	text-align: left;
	font-size:0.77em;
	vertical-align:top;	
}
.yui-skin-sam .yui-dt .yui-dt-col-entered_by {
	width: 78px;
	text-align: left;
	font-size:0.77em;
	vertical-align:top;	
}
.yui-skin-sam .yui-dt .yui-dt-col-note {
	width: 328px;
	text-align: left;
	font-size:0.77em;
	text-align:left;
	vertical-align:top;
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
<input type="hidden" value="<?php echo $cus_id; ?>" name="cus_id" id="cus_id" />
<input type="hidden" value="<?php echo $suid; ?>" name="suid" id="suid" />
<input type="hidden" value="<?php echo $admin_client; ?>" name="admin_client" id="admin_client" />
  <table width="980" border="0" align="center" cellpadding="2" cellspacing="0">
      <tr>
        <td colspan="2" bgcolor="#CCCCCC">REK</td>
      </tr>
      <tr>
        <td colspan="2" bgcolor="#CCCCCC"><div style="float:right"><a href="index.php?admin_client=<?php echo $admin_client; ?>&suid=<?php echo $suid; ?>">Customers</a></div>
            <strong>Customer Information</strong></td>
      </tr>
    </table>
    <table width="980" border="0" align="center" cellpadding="3" cellspacing="0">
      <tr>
        <td colspan="6"><hr color="#000000" />        </td>
      </tr>
      
      <tr>
        <td colspan="6" bgcolor="#EDEDED">
        	<div style="float:right">
            	<?php if ($blnImport && $data_path=="tritek") { ?>
				<a href="import_tritek.php?customer_id=<?php echo $cus_id; ?>&suid=<?php echo $suid; ?>" target="_blank">Import Tritek</a>&nbsp;|&nbsp;
                <?php } ?>
                <?php if ($blnImport && $data_path=="A1") { ?>
				<a href="import_a1.php?customer_id=<?php echo $cus_id; ?>&suid=<?php echo $suid; ?>" target="_blank">Import A1</a>&nbsp;|&nbsp;
                <?php } ?>
                <?php if ($blnImport && $data_path=="Abacus") { ?>
				<a href="import_abacus.php?customer_id=<?php echo $cus_id; ?>&suid=<?php echo $suid; ?>" target="_blank">Import A1</a>&nbsp;|&nbsp;
                <?php } ?>
                <?php if ($blnImport && $data_path=="perfect") { ?>
				<a href="import_perfect.php?customer_id=<?php echo $cus_id; ?>&suid=<?php echo $suid; ?>" target="_blank">Import Perfect</a>&nbsp;|&nbsp;
                <?php } ?>
                <a href="invoices.php?filter=all&cus_id=<?php echo $cus_id; ?>">Invoices</a></div>
        		<strong>Customer Information</strong>
                &nbsp;|&nbsp;
                <a href="../users/index.php?session_id=<?php echo $_SESSION["user"]; ?>&cus_id=<?php echo $cus_id; ?>&suid=<?php echo $suid; ?>" style="background:black; padding:3px; color:white; font-size:1.2em">Users</a>
            </td>
      </tr>
      <tr style="display:">
        <td colspan="6" align="left" valign="top" nowrap="nowrap" bgcolor="#EDEDED"><label>
          <select name="cus_type" id="cus_type" tabindex="1">
            <option value="Legal Firm" selected="selected">Legal Firm</option>
            <option value="Copy Service">Copy Service</option>
            <option value="Collection Agency">Collection Agency</option>
          </select>
        </label>
        <br />
        Customer Type</td>
      </tr>
      <tr>
        <td colspan="6" nowrap="nowrap" bgcolor="#EDEDED"><hr color="#000000" /></td>
      </tr>
      <tr>
        <td colspan="6" nowrap="nowrap" bgcolor="#EDEDED">
        	<div style="float:right;">
          	<input name="import" type="checkbox" value="i"<?php if ($blnImport) { echo " checked"; } ?> />
          Import<br />
Permissions 
			<div>	
            	<input name="billing" type="checkbox" value="b"<?php if ($blnBilling) { echo " checked"; } ?> />
          Billing
            </div>
			</div>
			<div style="float:right"><select id="parent_cus_id" name="parent_cus_id" style="display:" tabindex="3">
          <?php echo implode("\r\n", $arrCustomerRows); ?>
        </select>
          <br />
        Parent Company</div>
            <input name="cus_name" type="text" class="insurance_info carrier " id="cus_name" value="<?php echo $cus_name; ?>" size="50" autocomplete="off" tabindex="2" />&nbsp;<?php if ($cus_id!="") { echo "ID:&nbsp;" . $cus_id; } ?>
          <br />
Customer Name (Please leave blank spaces between numbers, names or words)</td>
      </tr>
      <tr>
        
        <td colspan="4" nowrap="nowrap" bgcolor="#EDEDED"><input name="cus_street" type="text" class="insurance_info carrier " id="cus_street" value="<?php echo $cus_street; ?>" size="50" />
          <br />
Customer Street Address/PO Box (Please leave blank spaces between numbers, names or words)</td>
        
        <td colspan="6" nowrap="nowrap" bgcolor="#EDEDED">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="2" bgcolor="#EDEDED">Zip Code:
        <input name="cus_zip" type="text" class="insurance_info carrier " id="cus_zip" value="<?php echo $cus_zip; ?>" size="5" placeholder="zip" onkeyup="noAlpha(this);sendZip(this, 'cus_', '')" autocomplete="off" /></td>
        <td width="15%" align="left" nowrap="nowrap" bgcolor="#EDEDED">City: <input name="cus_city" type="text" class="insurance_info carrier " id="cus_city" value="<?php echo $cus_city; ?>" /></td>
        <td width="27%" bgcolor="#EDEDED">State:
        <input name="cus_state" type="text" class="insurance_info carrier " id="cus_state" value="<?php echo $cus_state; ?>" size="2" /></td>
        <td colspan="2" align="left" bgcolor="#EDEDED">County:
        <input name="cus_county" type="text" class="insurance_info carrier " id="cus_county" value="<?php echo $cus_county; ?>" /></td>
      </tr>
      <tr>
        <td colspan="6" bgcolor="#EDEDED"><input name="cus_name_first" type="text" id="cus_name_first" value="<?php echo $cus_name_first; ?>" placeholder="First Name" /> <input name="cus_name_middle" type="text" id="cus_name_middle" value="<?php echo $cus_name_middle; ?>" placeholder="Middle Name" /> <input name="cus_name_last" type="text" id="cus_name_last" value="<?php echo $cus_name_last; ?>" placeholder="Last Name" /></td>
      </tr>
      <tr>
        <td colspan="6" bgcolor="#EDEDED">Customer First, Middle, Last Name</td>
      </tr>
      <tr bgcolor="#EDEDED">
        <td width="6%" bgcolor="#EDEDED">Phone:</td>
        <td width="20%" bgcolor="#EDEDED"><input name="cus_phone" type="text" id="cus_phone" value="<?php echo $cus_phone; ?>"  onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" size="14" placeholder="XXX-XXX-XXXX" /></td>
        <td align="left" bgcolor="#EDEDED" nowrap="nowrap">
        	<div style="float:right">Jet ID:</div>
        Fax:
        <input name="cus_fax" type="text" id="cus_fax" value="<?php echo $cus_fax; ?>"  onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" size="9" placeholder="XXX-XXX-XXXX" /></td>
        <td bgcolor="#EDEDED" align="left"><input name="jetfile_id" type="text" id="jetfile_id" value="<?php echo $jetfile_id; ?>" size="5" placeholder="" /></td>
        <td colspan="2" bgcolor="#EDEDED">Data:
        <input name="data_source" type="text" id="data_source" value="<?php echo $data_source; ?>" size="14" /></td>
      </tr>
      <tr bgcolor="#EDEDED">
        <td bgcolor="#EDEDED">Email:</td>
        <td bgcolor="#EDEDED">
        <input name="cus_email" type="text" id="cus_email" value="<?php echo $cus_email; ?>" size="35" />
        <br />
        <?php if ($cus_email=="") { ?>&nbsp;<span style="color:red">required for EAMS lookups</span><?php } ?>
        </td>
        <td align="right" bgcolor="#EDEDED">
        	<div style="float:left">
            Tax ID:&nbsp;<input name="cus_fedtax_id" type="text" id="cus_fedtax_id" value="<?php echo $cus_fedtax_id; ?>" size="8" placeholder="" />
            </div>
            Matrix ID:
        </td>
        <td bgcolor="#EDEDED">
          <input name="inhouse_id" type="text" id="inhouse_id" value="<?php echo $inhouse_id; ?>" size="5" placeholder="" />
          <?php if ($inhouse_id==0) { ?>&nbsp;<span style="color:red">required for Matrix Export</span><?php } ?></td>
        <td colspan="2" bgcolor="#EDEDED">Legacy:<select id="data_path" name="data_path" >
        	<option value="" <?php if ($data_path=="") { echo "selected"; } ?>>Select from List</option>
            <option value="A1" <?php if ($data_path=="A1") { echo "selected"; } ?>>A1</option>
            <option value="Abacus" <?php if ($data_path=="Abacus") { echo "selected"; } ?>>Abacus</option>
            <option value="tritek" <?php if ($data_path=="tritek") { echo "selected"; } ?>>Tritek</option>
            <option value="perfect" <?php if ($data_path=="perfect") { echo "selected"; } ?>>Perfect</option>
        </select>
        </td>
      </tr>
      <tr>
        <td bgcolor="#EDEDED">EAMS #:</td>
        <td bgcolor="#EDEDED"><input name="eams_no" type="text" id="eams_no" value="<?php echo $eams_no; ?>" size="35" placeholder="XXXXXXX" /></td>
        <td align="right" bgcolor="#EDEDED">UAN:</td>
        <td bgcolor="#EDEDED">
          <input name="cus_uan" type="text" id="cus_uan" value="<?php echo $cus_uan; ?>" size="14" /></td>
        <td colspan="2" bgcolor="#EDEDED">Bar #:
        <input name="cus_barnumber" type="text" id="cus_barnumber" value="<?php echo $cus_barnumber; ?>" size="14" /></td>
      </tr>
      <tr>
        <td colspan="6"><hr color="#000000" />        </td>
      </tr>
      <tr>
        <td colspan="3" nowrap="nowrap"><input name="office_manager_first" type="text" id="office_manager_first" value="<?php echo $office_manager_first; ?>" placeholder="First Name" />
          <input name="office_manager_middle" type="text" id="office_manager_middle" value="<?php echo $office_manager_middle; ?>" placeholder="Middle Name" />
        <input name="office_manager_last" type="text" id="office_manager_last" value="<?php echo $office_manager_last; ?>" placeholder="Last Name" /></td>
        <td><div style="float:left">IP Addresses</div><br />one ip address per line</td>
        <td width="3%">&nbsp;</td>
        <td width="20%">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="3">
        	
        	Office Manager  First, Middle, Last Name</td>
        <td colspan="3" rowspan="3" align="left" valign="top"><textarea name="cus_ip" id="cus_ip" cols="45" rows="5" placeholder="White List of IP Addresses (one ip address per line)"><?php echo $cus_ip; ?></textarea></td>
      </tr>
      <tr>
        <td>Phone:</td>
        <td><input name="office_manager_phone" type="text" id="office_manager_phone" value="<?php echo $office_manager_phone; ?>"  onkeypress="mask(this, mphone);" onblur="mask(this, mphone);" size="14" placeholder="XXX-XXX-XXXX" /></td>
        <td align="right">&nbsp;</td>
      </tr>
      <tr>
        <td>Email:</td>
        <td><input name="office_manager_email" type="text" id="office_manager_email" value="<?php echo $office_manager_email; ?>" size="35" /></td>
        <td align="right">&nbsp;</td>
      </tr>
       <tr>
        <td colspan="6"><hr color="#000000" /></td>
      </tr>
      <tr>
        <td nowrap="nowrap">Rate per User: (<?php echo $user_count; ?>)</td>
        <td><input name="user_rate" type="text" id="user_rate" value="<?php echo $user_rate; ?>" size="3" autocomplete="off" /> => <?php echo $user_rate * $user_count; ?></td>
        <td align="right">Corporation Rate: <input name="corporation_rate" type="text" id="corporation_rate" value="<?php echo $corporation_rate; ?>" size="3" /></td>
      </tr>
      <tr>
        <td colspan="6"><hr color="#000000" /></td>
      </tr>
      <tr>
        <td colspan="6" align="left"><input type="submit" name="submit" id="submit" value="Save" /></td>
      </tr>
  </table>
</form>
<br/><br/>
  <table style="width:980px; font-size:1.5em" align="center" border="0">
	<tr style="font-weight:bold;">
	  <td style="background:#097EE1;" colspan="5">
		<span style="font-weight:bold; font-size:1.8em; color: white">Notes</span><div style="float:right;"><a href="note_editor.php?cus_id=<?php echo $cus_id; ?>&note_id=-1" style="color: white;">New Note</a></div>
	  </td>
	</tr>
	<tr style="background:#CCC; font-weight:bold;">
	  <td style="background:#CCC; font-weight:bold;">
		Notes ID
	  </td>
	  <td style="background:#CCC; font-weight:bold;">
		Type
	  </td>
	  <td style="background:#CCC; font-weight:bold;">
		Subject
	  </td>
	  <td style="background:#CCC; font-weight:bold;">
		Note
	  </td>
	  <td style="background:#CCC; font-weight:bold;">
		Entered By
	  </td>
	</tr>
	<?php echo implode("\r\n", $arrRows); ?>
  </table>
  <br/><br/>
<?php include ("yahoo.php"); ?>
<script language="javascript">
YAHOO.namespace("example.container");
var Dom = YAHOO.util.Dom;
var Event = YAHOO.util.Event;
var myCarrierDataSource;
var myCarrierDataTable;
<?php if ($cus_id!="") { ?>
var myDataSource;
var myDataTable;
<?php } ?>
var myNoteDataSource;
var myNoteDataTable;
var myUploadDataSource;
var myUploadDataTable;
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
		var person_name = Dom.get("cus_name");
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
	mysentData = "attorney_id=" + id + "&cus_id=<?php echo $cus_id; ?>";
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
					var firm_name = Dom.get("attorney_firm_name");
					firm_name.value = arrData[1];
					var first_name = Dom.get("attorney_name_first");
					first_name.value = arrData[8];
					var middle_name = Dom.get("attorney_name_middle");
					middle_name.value = arrData[9];
					var last_name = Dom.get("attorney_name_last");
					last_name.value = arrData[10];
					var phone = Dom.get("attorney_phone");
					phone.value = arrData[3];
					var attorney_fax = Dom.get("attorney_fax");
					attorney_fax.value = arrData[4];
					var attorney_email = Dom.get("attorney_email");
					attorney_email.value = arrData[5];
					//default attorney
					var default_attorney = Dom.get("default_attorney");
					if (arrData[7]=="Y") {
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
var showFirm = function(eams_no, type) {
	if (eams_no=="") {
		return;
	}
	mysentData = "type=reps&query=" + eams_no;
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
					var eams_no = Dom.get("eams_no");
					eams_no.value = arrData[0];
					var name = Dom.get("cus_name");
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
					var zip_code = Dom.get("cus_zip");
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
	var search_item = Dom.get("cus_name");
	var the_value = search_item.value;
	if (the_value=="Search") {
		search_item.value = "";
		Dom.setStyle(search_item, "color", "black");
	}
}

var init = function() {
	
	YAHOO.example.container.panel_records = new YAHOO.widget.Panel("panel_records", { width:"650px", height: "75px", visible:false, constraintoviewport:true, modal:true } );
	YAHOO.example.container.panel_records.render();
	
	<?php if ($cus_id!="") { ?>
	var formatActive = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<a href='javascript:changeStatus(\"" + oRecord.getData("attorney_id") + "\",\"" + oRecord.getData("active") + "\")' title='Click to change active status'>" + oRecord.getData("active") + "</a>";
	}
	var formatDelete = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = "<a href='javascript:deleteAttorney(\"" + oRecord.getData("attorney_id") + "\")' title='Click to delete Contact' style='color:red'>Del</a>";
	}
	var formatFirm = function(elCell, oRecord, oColumn, sData) {
		//edit mode
		elCell.innerHTML = "<a href='javascript:showAttorneyInfo(" + oRecord.getData("attorney_id") + ")'>" + oRecord.getData("firm_name") + "</a>";
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
	
	<?php if ($cus_id!="") { ?>
	///users/index.php?suid=014f96e0c85c86b
	var myColumnDefs = [
		{key:"attorney_id", width:"50px", label:"ID", sortable:true, resizeable:false},
		{key:"firm_name", formatter:formatFirm, width:"350px", label:"Office", sortable:true, resizeable:true},
		{key:"attorney_name", formatter:formatAttorney, width:"350px", label:"Attorney", sortable:true, resizeable:true},
		{key:"phone", width:"350px", label:"Phone", sortable:false, resizeable:false},
		{key:"fax", width:"350px", label:"Fax", sortable:false, resizeable:false},
		{key:"email", width:"350px", label:"Email", sortable:false, resizeable:false},
		{key:"default", width:"20px", label:"Default Attorney", formatter:formatDefault},
		{key:"active", width:"20px", label:"Active", formatter:formatActive},
		{key:"delete", width:"20px", label:"Delete", formatter:formatDelete}
	];
	
	form_height_med = 200;
	form_height_med = form_height_med + "px";
	
	//list the data
	myDataSource = new YAHOO.util.DataSource("attorney_list.php?cus_id=<?php echo $cus_id; ?>");
	myDataSource.responseType = YAHOO.util.DataSource.TYPE_TEXT;
	
	var myConfigs = {
		height:form_height_med
	};
	myDataSource.responseSchema = { 
		recordDelim: "\n",
		fieldDelim: "|",
		fields: ["attorney_id","firm_name","attorney_name","phone", "fax", "email", "active","default_attorney"]
	};
	
	
	myDataTable = new YAHOO.widget.ScrollingDataTable("list_attorneys", myColumnDefs,
						myDataSource, myConfigs);
	
	var formatNote = function(elCell, oRecord, oColumn, sData) {
		var note = oRecord.getData("note");
		elCell.innerHTML = note.replace("_", "\r\n");
	}
	
	var myNoteColumnDefs = [
		{key:"note_date", label:"date", width:"100px", sortable:true, resizeable:true},
		{key:"entered_by", label:"by", width:"100px", sortable:true, resizeable:true},
		{key:"note", formatter:formatNote, width:"150px", sortable:false, resizeable:true}
	];
		
	//list the data
	myNoteDataSource = new YAHOO.util.DataSource("../notes/note_list.php?suid=<?php echo $suid; ?>&the_cus_id=<?php echo $cus_id; ?>");
	myNoteDataSource.responseType = YAHOO.util.DataSource.TYPE_TEXT;
	
	myNoteDataSource.responseSchema = { 
		recordDelim: "\n",
		fieldDelim: "|",
		fields: ["note","note_id","note_date","entered_by"]
	};
	
	form_height_med = "200px";
	
	myNoteDataTable = new YAHOO.widget.ScrollingDataTable("list_notes", myNoteColumnDefs,
						myNoteDataSource, {height:form_height_med});
						
	var formatUpload = function(elCell, oRecord, oColumn, sData) {
		var upload = "<a href='../../uploads/<?php echo $cus_id; ?>/" + oRecord.getData("upload_type") + "/" + oRecord.getData("upload") + "' target='_blank' title='Click to review uploaded document'>" + oRecord.getData("upload") + "</a>";
		elCell.innerHTML = upload;
	}
	
	var myUploadColumnDefs = [
		{key:"upload", formatter:formatUpload, width:"150px", sortable:false, resizeable:true},
		{key:"upload_date", label:"date", width:"100px", sortable:true, resizeable:true},
		{key:"upload_type", label:"type", sortable:true, resizeable:true}
	];
		
	//list the data
	myUploadDataSource = new YAHOO.util.DataSource("uploads_list.php?suid=<?php echo $suid; ?>&the_cus_id=<?php echo $cus_id; ?>");
	myUploadDataSource.responseType = YAHOO.util.DataSource.TYPE_TEXT;
	
	myUploadDataSource.responseSchema = { 
		recordDelim: "\n",
		fieldDelim: "|",
		fields: ["upload","upload_id","upload_date","upload_type"]
	};
	
	form_height_med = "200px";
	
	myUploadDataTable = new YAHOO.widget.ScrollingDataTable("list_uploads", myUploadColumnDefs,
						myUploadDataSource, {height:form_height_med});
						
	return {
		oDS: myDataSource,
		oDT: myDataTable
	};
	<?php } ?>	
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
	upload_frame.src = "https://www.dmsroi.com/uploadify/upload_form.php?suid=<?php echo $suid; ?>&fieldname=cus_document&cus_id=<?php echo $cus_id; ?>";

	Dom.setStyle("panel_records" , "display", "");
}
var refreshUploadDataSource = function() {
	this.sentData = "";
	myUploadDataSource.sendRequest(this.sentData, myUploadDataTable.onDataReturnInitializeTable, myUploadDataTable);
	
	myDataTable.onShow();
}
var storeRecords = function(imagename, fieldname) {
	if (imagename!="") {
		var the_pdf = document.getElementById(fieldname);
		the_pdf.value = imagename;
	}
}
var saveUpload = function(folder_name) {
	var cus_document = Dom.get("cus_document");
	mysentData = "suid=<?php echo $suid; ?>&the_cus_id=<?php echo $cus_id; ?>&cus_document=" + cus_document.value + "&folder_name=" + folder_name;
	var eamsURL = "save_upload.php";
	
	if (mysentData!='') {	
		//logEvent("about to send request");
		var request = YAHOO.util.Connect.asyncRequest('POST', eamsURL,
		   {success: function(o){
				response = o.responseText;
				//alert(response);
				//refresh the search
				hideRecordsPanel();
				refreshUploadDataSource();
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
<?php if ($cus_id!="") { ?>
var refreshAttorneyDataSource = function() {
	myDataSource.sendRequest(this.sentData, myDataTable.onDataReturnInitializeTable, myDataTable);
	
	myDataTable.onShow();
}

var changeDefault = function(id, status) {
	var clearUrl = "change_default.php";
	mysentData = "cus_id=<?php echo $cus_id; ?>&status=" + status + "&id=" + id;
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
	mysentData = "cus_id=<?php echo $cus_id; ?>&status=Y&id=" + id;
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
<script language="javascript" type="text/javascript" src="../../js/mask_phone.js"></script>
</body>
</html>