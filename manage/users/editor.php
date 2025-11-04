<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>
<?php
include ("../../text_editor/ed/functions.php");
include ("../../text_editor/ed/datacon.php");

if($_SERVER['SERVER_NAME']=="v2.starlinkcms.com")
{
  $application = "StarLinkCMS";
  $application_logo = "logo-starlinkcms.png";
  $application_url = "https://v2.starlinkcms.com/";
}
else
{
  $application = "iKase";
  $application_logo = "ikase_logo.png";
  $application_url = "https://v4.ikase.org/";
}

$cus_id = passed_var("cus_id");

$user_id = passed_var("user_id");
if ($user_id!="") {
	$query = "SELECT  `user_id`,`user_name`, `user_email`, `nickname`, `day_start`, `day_end`, `level`, `job`, `days_of_week`, `dow_times`,
	`user_logon`, `user_first_name`, `user_last_name`, `user_type`, activated, barno
	FROM cse_user 
	WHERE 1";
	$query .= " AND user_id = '" . $user_id . "'";
	$query .= " AND customer_id = '" . $cus_id . "'";

    $row = DB::runOrDie($query)->fetch();
}
$user_id = "";
$user_name = "";
$days_of_week = "";
$dow_times = "";
$user_email = "";
$barno = "";
$nickname = "";
$level = "";
$job = "";
$user_type = 2;
$user_logon = "";
$user_first_name = "";
$user_last_name = "";
$activated = "N";

if (isset($row)) {
	$user_id = $row->user_id;
	$user_name = $row->user_name;
	$user_type = $row->user_type;
	$user_email = $row->user_email;
	$barno = $row->barno;
	$nickname = $row->nickname;
	$level = $row->level;
	$job = $row->job;
	$days_of_week = $row->days_of_week;
	$start_time = $row->day_start;
	$end_time = $row->day_end;
	$activated = $row->activated;
	if ($start_time == "") {
		$start_time = "08:00AM";
		$end_time = "05:00PM";
	}
	$arrDOW = explode("|", $days_of_week);
	if (count($arrDOW)==1) {
		$arrDOW = array("1","2","3","4","5","6");
		$days_of_week = "1|2|3|4|5|6";
	}
	
	$dow_times = $row->dow_times;
	$arrDOWTimes = explode("|", $dow_times);
	if (count($arrDOWTimes)==1) {
        $arrDOWTimes = array_fill(0, 6, "{$start_time}-{$end_time}");
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

$result = DB::runOrDie("SELECT * FROM `cse_job` ORDER BY blurb");

$selected    = $job == ""? " selected" : "";
$job_options = "<option value=''" . $selected . ">Select from List</option>";
while ($row = $result->fetch()) {
    $selected    = $job == $row->job? " selected" : "";
    $job_options .= "<option value='{$row->job}'{$selected}>{$row->job}</option>";
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
        <td colspan="2" bgcolor="#CCCCCC">
        	<img src="../../img/<?=$application_logo;?>" alt="<?=$application;?>" height="90" />
        </td>
      </tr>
      <tr>
        <td colspan="2" bgcolor="#CCCCCC"><div style="float:right"><a href="index.php?cus_id=<?php echo $cus_id; ?>&suid=<?php echo $suid; ?>"> Users</a></div>
            <strong>User Information</strong></td>
      </tr>
    </table>
<table width="980" border="0" align="center" cellpadding="2" cellspacing="0">
    <tr>
        <td colspan="3"><hr color="#000000" />        </td>
</tr>
      
      <tr>
        <td align="left" valign="bottom" nowrap="nowrap">
            <input name="user_name" type="hidden" id="user_name" value="<?php echo $user_name; ?>" size="50" autocomplete="off" tabindex="0" />
            <input name="user_first_name" type="text" id="user_first_name" value="<?php echo $user_first_name; ?>" size="50" autocomplete="off" tabindex="1" />
			 <br />
		First Name</td>
        <td colspan="2" align="left" valign="bottom" nowrap="nowrap"><input name="user_last_name" type="text" id="user_last_name" value="<?php echo $user_last_name; ?>" size="50" autocomplete="off" tabindex="1" />
          <br />
Last Name</td>
      </tr>
      
      <tr>
        <td align="left" valign="bottom" nowrap="nowrap"><input name="user_email" type="text" id="user_email" value="<?php echo $user_email; ?>" size="50" tabindex="2" />
          <br />
Email</td>
        <td align="left" valign="bottom" nowrap="nowrap">&nbsp;</td>
        <td align="left" valign="bottom" nowrap="nowrap">&nbsp;</td>
      </tr>
      <tr>
        <td align="left" valign="bottom" nowrap="nowrap"><input name="barno" type="number" id="barno" value="<?php echo $barno; ?>" size="50" tabindex="2" />
          <br /> 
          Bar No.
</td>
        <td width="113" align="left" valign="bottom" nowrap="nowrap"><select name="level" id="level" tabindex="3">
          <option value="1"<?php if ($level=="admin" || $user_type==1) { echo " selected"; } ?>>Admin</option>
          <option value="2"<?php if ($level=="user" || $level=="" || $user_type==2) { echo " selected"; } ?>>User</option>
          <option value="3"<?php if ($level=="masteradmin" || $level=="" || $user_type==3) { echo " selected"; } ?>>Master Admin</option>
        </select>
          <br />
Role </td>
        <td align="left" valign="bottom" nowrap="nowrap"><select name="job" id="job" tabindex="4">
        	<?php echo $job_options; ?>
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
        <td colspan="2" align="left" valign="bottom"><table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td colspan="6" bgcolor="#CCCCCC"><b>Days of Week :</b></td>
          </tr>
          <tr>
            <td align="left" bgcolor="#EDEDED"><em>Mon</em></td>
            <td align="left"><em>Tue</em></td>
            <td align="left" bgcolor="#EDEDED"><em>Wed</em></td>
            <td align="left"><em>Thu</em></td>
            <td align="left" bgcolor="#EDEDED"><em>Fri</em></td>
            <td align="left"><em>Sat</em></td>
          </tr>
          <tr>
            <td align="left" bgcolor="#EDEDED"><input name="days_of_week1" type="checkbox" id="days_of_week1" value="1"<?php if(strpos($days_of_week, "1")!==false || $user_id=="") { echo " checked"; } ?> /></td>
            <td align="left"><input name="days_of_week2" type="checkbox" id="days_of_week2" value="2"<?php if(strpos($days_of_week, "2")!==false  || $user_id=="") { echo " checked"; } ?> /></td>
            <td align="left" bgcolor="#EDEDED"><input name="days_of_week3" type="checkbox" id="days_of_week3" value="3"<?php if(strpos($days_of_week, "3")!==false  || $user_id=="") { echo " checked"; } ?> /></td>
            <td align="left"><input name="days_of_week4" type="checkbox" id="days_of_week4" value="4"<?php if(strpos($days_of_week, "4")!==false  || $user_id=="") { echo " checked"; } ?> /></td>
            <td align="left" bgcolor="#EDEDED"><input name="days_of_week5" type="checkbox" id="days_of_week5" value="5"<?php if(strpos($days_of_week, "5")!==false  || $user_id=="") { echo " checked"; } ?> /></td>
            <td align="left"><input name="days_of_week6" type="checkbox" id="days_of_week6" value="6"<?php if(strpos($days_of_week, "6")!==false  || $user_id=="") { echo " checked"; } ?> /></td>
          </tr>
          <tr>
                  <td align="left" bgcolor="#EDEDED"><input name="dow_times1" type="text" id="dow_times1" size="17" value="<?php echo $arrDOWTimes[0]; ?>" /></td>
                  <td align="left"><input name="dow_times2" type="text" id="dow_times2" size="17" value="<?php echo $arrDOWTimes[1]; ?>" /></td>
                  <td align="left" bgcolor="#EDEDED"><input name="dow_times3" type="text" id="dow_times3" size="17" value="<?php echo $arrDOWTimes[2]; ?>" /></td>
                  <td align="left"><input name="dow_times4" type="text" id="dow_times4" size="17" value="<?php echo $arrDOWTimes[3]; ?>" /></td>
                  <td align="left" bgcolor="#EDEDED"><input name="dow_times5" type="text" id="dow_times5" size="17" value="<?php echo $arrDOWTimes[4]; ?>" /></td>
                  <td align="left"><input name="dow_times6" type="text" id="dow_times6" size="17" value="<?php echo $arrDOWTimes[5]; ?>" /></td>
          </tr>
        </table>
        </td>
        <td valign="bottom">&nbsp;</td>
</tr>
      
      <tr>
        <td colspan="3"><hr color="#000000" />        </td>
</tr>
      <tr>
        <td colspan="2" nowrap="nowrap">
        	<div style="float:right; margin-right:50px"><input value="<?php echo $nickname; ?>" name="nickname" id="nickname" placeholder="Nickname" size="4" /><br />Nickname</div>
        	<input type="text" id="user_logon" name="user_logon" value="<?php echo $user_logon; ?>" />
          <input name="same_email" id="same_email" type="checkbox" value="Y" style="display:<?php if ($user_logon!="") { echo "none"; } ?>" />
          <span class="instructions" style="display:<?php if ($user_logon!="") { echo "none"; } ?>">click to make <strong>Logon same  as Email Address</strong></span><br />
        User Logon<br /></td>
        <td><input name="password" type="text" id="password" size="7" tabindex="7" autocomplete="off" />
        <br />
        Password</td>
      </tr>
      <tr>
        <td><input type="button" name="submit1" id="submit1" value="Save" tabindex="8" onclick="checkPassword()" /></td>
        <td>&nbsp;</td>
        <td><input type="checkbox" name="activated" id="activated" value="Y" <?php if ($activated=="Y") { echo "checked"; } ?> />
        <label for="activated">Active</label></td>
    </tr>
  </table>
</form>
<?php include ("yahoo.php"); ?>
<script language="javascript">
var checkPassword = function(){
  if(form1.password.value.length < 5)
  {
    //alert("Password must be at least 5 characters in length");
    //form1.password.focus();
	  document.form1.submit();
  }
  else
  {
    document.form1.submit();
  }
}
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
