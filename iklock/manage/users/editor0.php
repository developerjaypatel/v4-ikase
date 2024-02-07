<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>
<?php
include("../eamsjetfiler/datacon.php");
include("../eamsjetfiler/functions.php");

$cus_id = passed_var("cus_id");
include("../logon_check.php");
$user_id = passed_var("user_id");
if ($user_id!="") {
	$query = "SELECT  `user_id`,`user_name`, `password`, `user_email`, `day_start`, `day_end`, `level`, `job`,
	`user_logon`, `user_first_name`, `user_last_name`
	FROM tbl_user 
	WHERE 1";
	$query .= " AND user_id = '" . $user_id . "'";
	$query .= " AND cus_id = '" . $cus_id . "'";
    $row = DB::runOrDie($query)->fetch();
}
$user_id = "";
$user_name = "";
$password = "";
$user_email = "";
$level = "";
$job = "";
$user_logon = "";
$user_first_name = "";
$user_last_name = "";

if (isset($row)) {
	$user_id = $row->user_id;
	$user_name = $row->user_name;
	$password = $row->password;
	$user_email = $row->user_email;
	$level = $row->level;
	$job = $row->job;
	$start_time = $row->day_start;
	$end_time = $row->day_end;
	if ($start_time == "") {
		$start_time = "08:00AM";
		$end_time = "05:00PM";
	}
	$user_logon = $row->user_logon;
	if ($user_logon=="") {
		$user_logon = $user_email;
	}
	$user_first_name = $row->user_first_name;
	$user_last_name = $row->user_last_name;
	
	if ($user_first_name=="" && $user_last_name == "") {
		$arrUserName = explode(" ", $user_name);
		//die(print_r($arrUserName));
		if (count($arrUserName)>1) {
			$user_last_name = $arrUserName[count($arrUserName) - 1];
			$user_first_name = str_replace(" " . $user_last_name, "", $user_name);
		} else {
			$user_first_name = $user_name;
			$user_last_name = "";
		}
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>User Information</title>
</head>

<body class="yui-skin-sam">
<form action="update.php" method="post" enctype="multipart/form-data" name="form1" id="form1">
<input type="hidden" value="<?php echo $cus_id; ?>" name="cus_id" id="cus_id" />
<input type="hidden" value="<?php echo $user_id; ?>" name="user_id" id="user_id" />
<input type="hidden" value="<?php echo $suid; ?>" name="suid" id="suid" />
    <table width="980" border="0" align="center" cellpadding="2" cellspacing="0">
      <tr>
        <td colspan="2" bgcolor="#CCCCCC"><img src="../../images/jetfile_logo.png" alt="EAMS JetFile" width="354" height="45" /></td>
      </tr>
      <tr>
        <td colspan="2" bgcolor="#CCCCCC"><div style="float:right"><a href="index.php?cus_id=<?php echo $cus_id; ?>&suid=<?php echo $suid; ?>"> Users</a></div>
            <strong>User Information</strong></td>
      </tr>
    </table>
    <table width="980" border="0" align="center" cellpadding="3" cellspacing="0">
      <tr>
        <td colspan="3"><hr color="#000000" />        </td>
      </tr>
      
      <tr>
        <td width="333" align="left" valign="bottom" nowrap="nowrap">
            <input name="user_name" type="hidden" id="user_name" value="<?php echo $user_name; ?>" size="50" autocomplete="off" tabindex="0" />
            <input name="user_first_name" type="text" id="user_first_name" value="<?php echo $user_first_name; ?>" size="50" autocomplete="off" tabindex="1" />
			 <br />
		First Name</td>
        <td colspan="2" align="left" valign="bottom" nowrap="nowrap"><input name="user_last_name" type="text" id="user_last_name" value="<?php echo $user_last_name; ?>" size="50" autocomplete="off" tabindex="1" />
          <br />
Last Name</td>
      </tr>
      
      <tr>
        <td align="left" valign="bottom" nowrap="nowrap"><input name="user_email" type="text" id="user_email" value="<?php echo $user_email; ?>" size="50" tabindex="6" />
          <br />
Email</td>
        <td width="113" align="left" valign="bottom" nowrap="nowrap"><select name="level" id="level" tabindex="2">
          <option value="admin"<?php if ($level=="admin") { echo " selected"; } ?>>Admin</option>
          <option value="User"<?php if ($level=="user" || $level=="") { echo " selected"; } ?>>User</option>
        </select>
          <br />
Role </td>
        <td width="516" align="left" valign="bottom" nowrap="nowrap"><select name="job" id="job" tabindex="3">
          <option value="attorney"<?php if ($job=="attorney"|| $job=="") { echo " selected"; } ?>>Attorney</option>
          <option value="staff"<?php if ($job=="staff" ) { echo " selected"; } ?>>Office Staff</option>
        </select>
          <br />
Job</td>
      </tr>
      <tr>
        <td colspan="3" align="left" valign="top"><hr color="#000000" /></td>
      </tr>
      <tr>
        <td colspan="2" align="left" valign="bottom">
        <div style="width:190px; float:right">
            <div id="end_time_holder" style="width:135px; border:0px solid #FFFFFF">Through
              <input name="end_time" type="text" class="form_field" id="end_time" style="width:56px" onFocus="collapseIt();showDropDown('end_time')" value="<?php echo $end_time; ?>" tabindex="5">
                <div id="bContainer_end_time" style="display:none" class="drop_down_container">
                    <select name="time_select_end" id="time_select_end" onchange="assignTime(this, 'end_time')">
                      <option value="">Select Time</option>
                      <option value="06:00">6:00AM</option>
                      <option value="06:30">6:30AM</option>
                      <option value="07:00">7:00AM</option>
                      <option value="07:30">7:30AM</option>
                      <option value="08:00">8:00AM</option>
                      <option value="08:30">8:30AM</option>
                      <option value="09:00">9:00AM</option>
                      <option value="09:30">9:30AM</option>
                      <option value="10:00">10:00AM</option>
                      <option value="10:30">10:30AM</option>
                      <option value="11:00">11:00AM</option>
                      <option value="11:30">11:30AM</option>
                      <option value="12:00">12:00PM</option>
                      <option value="12:30">12:30PM</option>
                      <option value="13:00">01:00PM</option>
                      <option value="13:30">01:30PM</option>
                      <option value="14:00">02:00PM</option>
                      <option value="14:30">02:30PM</option>
                      <option value="15:00">03:00PM</option>
                      <option value="15:30">03:30PM</option>
                      <option value="16:00">04:00PM</option>
                      <option value="16:30">04:30PM</option>
                      <option value="17:00">05:00PM</option>
                      <option value="17:30">05:30PM</option>
                      <option value="18:00">06:00PM</option>
                      <option value="18:30">06:30PM</option>
                      <option value="19:00">07:00PM</option>
                      <option value="19:30">07:30PM</option>
                      <option value="20:00">08:00PM</option>
                    </select>
                </div> 
	        </div>
        </div> 
        <div style="width:240px; float:left">
            <div id="start_time_holder" style="width:225px; border:0px solid #FFFFFF">
                Authorized Times From 
                  <input name="start_time" type="text" class="form_field" id="start_time" style="width:56px" onFocus="collapseIt();showDropDown('start_time')" value="<?php echo $start_time; ?>" tabindex="4">
                <div id="bContainer_start_time" style="display:none" class="drop_down_container">
                    <select name="time_select_start" id="time_select_start" onchange="assignTime(this, 'start_time')">
                      <option value="">Select Time</option>
                      <option value="06:00">6:00AM</option>
                      <option value="06:30">6:30AM</option>
                      <option value="07:00">7:00AM</option>
                      <option value="07:30">7:30AM</option>
                      <option value="08:00">8:00AM</option>
                      <option value="08:30">8:30AM</option>
                      <option value="09:00">9:00AM</option>
                      <option value="09:30">9:30AM</option>
                      <option value="10:00">10:00AM</option>
                      <option value="10:30">10:30AM</option>
                      <option value="11:00">11:00AM</option>
                      <option value="11:30">11:30AM</option>
                      <option value="12:00">12:00PM</option>
                      <option value="12:30">12:30PM</option>
                      <option value="13:00">01:00PM</option>
                      <option value="13:30">01:30PM</option>
                      <option value="14:00">02:00PM</option>
                      <option value="14:30">02:30PM</option>
                      <option value="15:00">03:00PM</option>
                      <option value="15:30">03:30PM</option>
                      <option value="16:00">04:00PM</option>
                      <option value="16:30">04:30PM</option>
                      <option value="17:00">05:00PM</option>
                      <option value="17:30">05:30PM</option>
                      <option value="18:00">06:00PM</option>
                      <option value="18:30">06:30PM</option>
                      <option value="19:00">07:00PM</option>
                      <option value="19:30">07:30PM</option>
                      <option value="20:00">08:00PM</option>
                    </select>
                </div> 
	        </div>
        </div>        </td>
        <td valign="bottom">&nbsp;</td>
      </tr>
      
      <tr>
        <td colspan="3"><hr color="#000000" />        </td>
      </tr>
      <tr>
        <td colspan="2" nowrap="nowrap"><input type="text" id="user_logon" name="user_logon" value="<?php echo $user_logon; ?>" />
          <input name="same_email" id="same_email" type="checkbox" value="Y" style="display:<?php if ($user_logon!="") { echo "none"; } ?>" />
          <span class="instructions" style="display:<?php if ($user_logon!="") { echo "none"; } ?>">click to make <strong>Logon same  as Email Address</strong></span><br />
        User Logon<br /></td>
        <td><input name="password" type="text" id="password" value="<?php echo $password; ?>" size="7" tabindex="7" />
        <br />
        Password</td>
      </tr>
      <tr>
        <td><input type="submit" name="submit" id="submit" value="Save" tabindex="8" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
  </table>
</form>
<?php include ("../yahoo.php"); ?>
<script language="javascript">
var Dom = YAHOO.util.Dom;
var Event = YAHOO.util.Event;
var sameEmail = function() {
	var same_email = Dom.get("same_email");
	if (same_email.checked) {
		//get info from employer address into injury address
		var user_logon = Dom.get("user_logon");
		var user_email = Dom.get("user_email");
		
		user_logon.value = user_email.value;
	}
}
var assignTime = function(obj, destination) {

	var the_time = Dom.get(destination);
	var the_value = obj.value;
	var arrTime = the_value.split(":");
	var the_hour = arrTime[0];
	var the_minute = arrTime[1];
	var the_ampm = "AM";
	if (the_hour>12) {
		the_hour = the_hour - 12;
		the_ampm = "PM";
	}
	if (the_hour==12) {
		the_ampm = "PM";
	}
	
	the_time.value = the_hour + ":" + the_minute + the_ampm;
	collapseIt(destination);
}
var showDropDown = function(destination) {
	var Dom = YAHOO.util.Dom
	Dom.setStyle("bContainer_" + destination, "display", "");
	Dom.setStyle(destination + "_holder", "height", "45px");
	var oRange;
	var the_destination = Dom.get(destination);
	var the_value = the_destination.value;
	
	oRange = the_destination.createTextRange();
	oRange.moveStart("character", 0);
	oRange.moveEnd("character", the_value.length);
	oRange.select();
	the_destination.focus();
	return;
}
var collapseIt = function(destination) {
	var elements =  Dom.getElementsByClassName('drop_down_holder', 'div');
	Dom.setStyle(elements, "height", "30px");
	var elements =  Dom.getElementsByClassName('drop_down_container', 'div');
	Dom.setStyle(elements, "display", "none");
}
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
		var person_name = Dom.get("user_name");
		var the_value = person_name.value;
		if (the_value == "") {
			Dom.setStyle("list_" + "user_searches", "display", "none");
			//alert("back hide");
		}
	}
	return;
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
					var name = Dom.get("user_name");
					name.value = arrData[1];
					var street = Dom.get("user_street");
					street.value = arrData[2];
					if (arrData[3]!="") {
						street.value += " " + arrData[3];
					}
					var city = Dom.get("user_city");
					city.value = arrData[4];
					var state = Dom.get("user_state");
					state.value = arrData[5];
					var zip_code = Dom.get("user_zip");
					zip_code.value = arrData[6];
					
					hideInfo(type);
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
var eamsLookup = function (type) {
	//alert("artLookupping");
	
	var search_item = Dom.get("user_name");
	var the_value = search_item.value;
	
	if (the_value != "") {
		Dom.setStyle("list_" + "user_searches", "display", "");
	} else {
		//alert("hide me");
		Dom.setStyle("list_" + "user_searches", "display", "none");
		return;
	}
	if (the_value.length<3) {
		return;
	}
	this.sentData = "&query=" + the_value;		
	myCarrierDataSource.sendRequest(this.sentData, myCarrierDataTable.onDataReturnInitializeTable, myCarrierDataTable);
	myCarrierDataTable.onShow();
	//alert("refreshed");
}
var emptySearch = function (type) {
	var search_item = Dom.get("user_name");
	var the_value = search_item.value;
	if (the_value=="Search") {
		search_item.value = "";
		Dom.setStyle(search_item, "color", "black");
	}
}

var init = function() {
	Event.addListener("same_email", "change", sameEmail);
}

var hideInfo = function(type) {
	Dom.setStyle("list_" + "user_searches", "display", "none");
	//alert("hidden");
}
var logEvent = function (msg, status) {
	YAHOO.log(msg, status);
}
YAHOO.util.Event.addListener(window, "load", init);
</script>
</body>
</html>
