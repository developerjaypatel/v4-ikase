<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('../../../shared/legacy_session.php');
session_write_close();

if (!isset($_SESSION["user_plain_id"])) {
	die("no id");
}
include ("../../api/connection.php");

$cus_id = passed_var("cus_id", "post");

if ($cus_id>0) {
	$sql = "SELECT  `customer_id` cus_id, `parent_customer_id` parent_cus_id, `eams_no`, 
	`cus_barnumber`,
	`cus_name`, `cus_name_first`, `cus_name_middle`, `cus_name_last`, 
	`cus_street`, `cus_city`, `cus_state`, `cus_zip`, `cus_county`, 
	`cus_ip`, `admin_client`, `password`, `cus_email`, `cus_type`, `cus_phone`, `cus_fax`, `data_source`, `data_path`, `permissions`, `inhouse_id`, `jetfile_id`, `office_manager_first`, `office_manager_last`, `office_manager_middle`, `office_manager_phone`, `office_manager_email`, cus_fedtax_id, cus_uan, corporation_rate, user_rate
	FROM `ikase`.cse_customer 
	WHERE customer_id = '" . $cus_id . "'";
	//$result = DB::runOrDie($query);
    try {
		$customer = DB::select($sql);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}

	$cus_id = $customer->cus_id;
	$eams_no = $customer->eams_no;
	$parent_cus_id = $customer->parent_cus_id;
	$cus_eams_no = $customer->eams_no;
	$cus_barnumber = $customer->cus_barnumber;
	$cus_name = $customer->cus_name;
	$cus_name_first = $customer->cus_name_first;
	$cus_name_middle = $customer->cus_name_middle;
	$cus_name_last = $customer->cus_name_last;
	$cus_street = $customer->cus_street;
	$cus_city = $customer->cus_city;
	$cus_state = $customer->cus_state;
	$cus_zip = $customer->cus_zip;
	$cus_county = $customer->cus_county;
	$password = $customer->password;
	$cus_email = $customer->cus_email;
	$cus_phone = $customer->cus_phone;
	$cus_fax = $customer->cus_fax;
	$cus_type = $customer->cus_type;
	$cus_ip = $customer->cus_ip;
	$cus_fedtax_id = $customer->cus_fedtax_id;
	$cus_uan = $customer->cus_uan;
	$data_source = $customer->data_source;
	$data_path = $customer->data_path;
	$data_path = str_replace("/", "\\", $data_path);
	
	$permissions = $customer->permissions;
	$inhouse_id = $customer->inhouse_id;
	$jetfile_id = $customer->jetfile_id;
	
	$user_rate = $customer->user_rate;
	$corporation_rate = $customer->corporation_rate;
	
	$office_manager_first = $customer->office_manager_first;
	$office_manager_middle = $customer->office_manager_middle;
	$office_manager_last = $customer->office_manager_last;
	$office_manager_email = $customer->office_manager_email;
	$office_manager_phone = $customer->office_manager_phone;
	
} else {
	
}
$blnRead = (strpos($permissions, "r")!==false || $cus_id=="");
$blnWrite = (strpos($permissions, "w")!==false || $cus_id=="");
$blnExport = (strpos($permissions, "e")!==false || $cus_id=="");
$blnImport = (strpos($permissions, "i")!==false || $cus_id=="");
$blnBilling = (strpos($permissions, "b")!==false || $cus_id=="");
//drop down for venue

$sql = "SELECT  `customer_id` cus_id,`eams_no`, `cus_name`, `cus_name_first`, `cus_name_middle`, `cus_name_last`, `cus_street`, `cus_city`, `cus_state`, `cus_zip`, `admin_client`, `password`, `xl_filed`, `cus_email`
FROM `customer` 
WHERE 1";
$sql .= "
ORDER BY `cus_name`";

try {
	$customers = DB::select($sql);
} catch(PDOException $e) {
	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
}
$selected = "";
if ($cus_id == "") {
	$selected = " selected";
}
$the_row = "<option value=''" . $selected .">Select from the list</option>";
$arrCustomerRows[] = $the_row;
foreach($customers as $customer) {
	$the_cus_id = $customer->cus_id;	
	$the_cus_name = $customer->cus_name;
	
	$selected = "";
	if ($the_cus_id == $parent_cus_id) {
		$selected = " selected";
	}
	$the_row = "<option value='" . $the_cus_id . "'" . $selected .">" . $the_cus_name . "</option>";
	
	//die($the_row);
	//$the_row = str_replace(" ", "&nbsp;", $the_row);
	$arrCustomerRows[] = $the_row;
}

$sql = "SELECT cn . *, cnc . * 
FROM iklock.notes cn 
INNER JOIN iklock.notes_customer cnc 
ON cn.customer_id = cnc.customer_id 
AND cn.notes_uuid = cnc.notes_uuid 
WHERE cnc.customer_id =" . $cus_id;
$sql .= " ORDER BY `dateandtime` DESC";

try {
	$notes = DB::select($sql);
} catch(PDOException $e) {
	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
}

$arrRows = array();
foreach($notes as $note) {
	$customer_id = $note->customer_id;	
	$notes_id = $note->notes_id;
	$type = $note->type;	
	$subject = $note->subject;
	$note = $note->note;	
	//$enter_by = $note->enter_by;
	$enter_by = "";
	$status = $note->status;
	$dateandtime = $note->dateandtime;	
	
	$row = "<tr><td align='left' valign='top'><a href='#'>" . $notes_id . "</a></td><td align='left' valign='top'>" . $type . "</td><td align='left' valign='top'>" . $subject. "</td><td align='left' valign='top'>" . $note . "</td><!--<td align='left' valign='top'>" . $enter_by . "</td>--></tr>";
	$arrRows[] = $row;
}

//users
$sql = "SELECT COUNT(`user_id`) `user_count`
FROM `user` WHERE customer_id = " . $cus_id;
//$result_users = DB::runOrDie($query);
try {
	$stmt = DB::run($sql);
	$user = $stmt->fetchObject();
} catch(PDOException $e) {
	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
}
$user_count = $user->user_count;
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
        <td colspan="2" bgcolor="#CCCCCC"><img src="../../img/ikase_logo_login.png" alt="iKase" height="32" /></td>
      </tr>
      <tr>
        <td colspan="2" bgcolor="#CCCCCC"><div style="float:right"><a href="index.php?admin_client=<?php echo $admin_client; ?>">Customers</a></div>
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
				<a href="import_tritek.php?customer_id=<?php echo $cus_id; ?>" target="_blank">Import Tritek</a>&nbsp;|&nbsp;
                <?php } ?>
                <?php if ($blnImport && $data_path=="A1") { ?>
				<a href="import_a1.php?customer_id=<?php echo $cus_id; ?>" target="_blank">Import A1</a>&nbsp;|&nbsp;
                <?php } ?>
                <?php if ($blnImport && $data_path=="Abacus") { ?>
				<a href="import_abacus.php?customer_id=<?php echo $cus_id; ?>" target="_blank">Import A1</a>&nbsp;|&nbsp;
                <?php } ?>
                <?php if ($blnImport && $data_path=="perfect") { ?>
				<a href="import_perfect.php?customer_id=<?php echo $cus_id; ?>" target="_blank">Import Perfect</a>&nbsp;|&nbsp;
                <?php } ?>
                <a href="invoices.php?filter=all&cus_id=<?php echo $cus_id; ?>">Invoices</a></div>
        		<strong>Customer Information</strong>
                &nbsp;|&nbsp;
                <a href="../users/index.php?cus_id=<?php echo $cus_id; ?>" style="background:black; padding:3px; color:white; font-size:1.2em">Users</a>
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
<script language="javascript" type="text/javascript" src="../../js/mask_phone.js"></script>
</body>
</html>
