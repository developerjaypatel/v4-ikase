<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

include("../../eamsjetfiler/datacon.php");
include("../../eamsjetfiler/functions.php");

$suid = passed_var("suid");
//make sure we have the right logon
include("../../logon_check.php");

$the_cus_id = passed_var("the_cus_id");
if (!is_numeric($the_cus_id)) {
	die();
}
$sql = "SELECT cus_name_first, cus_name_last, cus_name, cus_street, cus_city, cus_state, cus_zip FROM tbl_customer 
WHERE cus_id = " . $the_cus_id;
//echo $sql;
$result = mysql_query($sql, $r_link) or die("unable to run query<br />" .$sql . "<br>" .  mysql_error());
$numbs = mysql_numrows($result);

$cus_name = mysql_result($result, 0, "cus_name");
$cus_name_first = mysql_result($result, 0, "cus_name_first");
$cus_name_last = mysql_result($result, 0, "cus_name_last");

$the_cus_street = mysql_result($result, 0, "cus_street");
$the_cus_city = mysql_result($result, 0, "cus_city");
$the_cus_state = mysql_result($result, 0, "cus_state");
$the_cus_zip = mysql_result($result, 0, "cus_zip");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Add Note</title>
</head>
<style>
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
label {
	font-weight:bold;
}
</style>
<body class="yui-skin-sam">
<div style="float:right">
	<div id="list_notes" style="width:300px"></div>
</div>
<form id="mainForm">
        <input type="hidden" id="cus_id" name="the_cus_id" value="<?php echo $the_cus_id; ?>" />
        <input type="hidden" id="suid" name="suid" value="<?php echo $suid; ?>" />
<div class="informations" style="width:40em; background:white; padding-left:5px">
    <div>
        <label for="customer" id="customer_label">Customer:</label>
        <span id="customer"><?php echo $cus_name; ?></span>
    </div>
    <div>
    <label for="note" id="note_label">Note:</label><br />
        <input type="hidden" id="note_id" name="note_id" value="" />
        <textarea name="note" rows="10" id="note" style="width:25em;text-transform:none"></textarea>
    </div>
    <div>
   	  <input type="button" name="FormButton" id="FormButton" value="Save" /><span id="status_feedback"></span>
    </div>
</div>
</form>
<?php include ("yahoo.php"); ?>
<?php 
$debug = false;
?>
<script language="javascript">
var Dom = Dom;
var noteSave = function() {
	myform = Dom.get("mainForm");
	YAHOO.util.Connect.setForm(myform);
  	// make the initial cache of the form data
	mysentData = YAHOO.util.Connect._sFormData;
	var saveNoteUrl = "note_save.php";
	
	//logEvent("form gotten");
	var the_saved = Dom.get("status_feedback");
	the_saved.innerHTML = "<em>saving...</em>";
				
	if (mysentData!='') {	
		//alert(saveNoteUrl + '?' + mysentData);
		//return;
		var the_button = Dom.get("FormButton");
		the_button.disabled = true;
		//the_button.src = "images/button_images/save_bubble_disabled.png";
		the_button.src = "images/loading.gif";
		
		//logEvent("about to send request");
		var request = YAHOO.util.Connect.asyncRequest('POST', saveNoteUrl,
		   {success: function(o){
				response = o.responseText;
				//alert(response);
				//mark it saved
				var the_saved = Dom.get("status_feedback");
				the_saved.innerHTML = " saved!";
				//var login_language = Dom.get("login_language");
				var login_language = "english";
				var the_button = Dom.get("FormButton");
				the_button.disabled = false;
				//load the appointment id into the id
				//var arrID = response.split("|");
				
				//var note_id = Dom.get("note_id");
				//note_id.value = arrID[0];
				
				Dom.get("note").value = "";
				refreshDataSource();
				//logEvent("saved");
				setTimeout("hideInfo()", 2500);
			},
		   failure: function(){
			   //
			   alert("failure");
			},
		   after: function(){
			   //
			   alert("after");
			},
		   scope: this}, mysentData);
		 //logEvent("appointment save done");
	}
};
var refreshDataSource = function() {
	this.sentData = "";
	myNoteDataSource.sendRequest(this.sentData, myNoteDataTable.onDataReturnInitializeTable, myNoteDataTable);
	
	myDataTable.onShow();
}
var hideInfo = function() {
	var the_saved = Dom.get("status_feedback");
	the_saved.innerHTML = "&nbsp;";
}
var setFocus = function () { 
	var note = Dom.get("note");
	note.focus();
}
var printStatus = function() {
	var patient_id = Dom.get("patient_id");
	var case_id = Dom.get("case_id");
	parent.showStatus(patient_id.value, case_id.value);
}
var myNoteDataSource;
var myNoteDataTable;
var init = function() {
	
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
	myNoteDataSource = new YAHOO.util.DataSource("note_list.php?suid=<?php echo $suid; ?>&the_cus_id=<?php echo $the_cus_id; ?>");
	myNoteDataSource.responseType = YAHOO.util.DataSource.TYPE_TEXT;
	
	myNoteDataSource.responseSchema = { 
		recordDelim: "\n",
		fieldDelim: "|",
		fields: ["note","note_id","note_date","entered_by"]
	};
	
	form_height_med = "200px";
	
	myNoteDataTable = new YAHOO.widget.ScrollingDataTable("list_notes", myNoteColumnDefs,
						myNoteDataSource, {height:form_height_med});
						
	YAHOO.util.Event.addListener("FormButton", "click", noteSave);
	setTimeout("setFocus()", 500);
}
YAHOO.util.Event.addListener(window, "load", init);
</script>
</body>
</html>
