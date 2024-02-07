<style>
body {
	margin-top:0px;
	font-family:Georgia, "Times New Roman", Times, serif;
	font-size:12pt;
}
.required {
	background-color:#CCFFFF;
}
.instructions {
	font-style:italic;
	font-size:9pt;
	background-color:#FFFFCA;
}
textarea {
	text-transform: uppercase;
}
</style>
<link rel="stylesheet" type="text/css" href="../../build/fonts/fonts-min.css" />
<link rel="stylesheet" type="text/css" href="../../build/datatable/assets/skins/sam/datatable.css" />
<!-- Dependencies -->
<script src="../../build/yahoo/yahoo-min.js"></script>
<!-- Dom Source file -->
<script src="../../build/dom/dom-min.js"></script>
<!-- Event source file -->
<script src="../../build/event/event-min.js" ></script>
<!--connection-->
<script type="text/javascript" src="../../build/connection/connection-min.js"></script>
<script type="text/javascript" src="../../build/json/json-min.js"></script>
<!--datasource-->
<script src="../../build/datasource/datasource-min.js"></script>
<!--datatable-->
<script type="text/javascript" src="../../build/yahoo-dom-event/yahoo-dom-event.js"></script>
<script type="text/javascript" src="../../build/element/element-min.js"></script>
<script type="text/javascript" src="../../build/datatable/datatable-min.js"></script>
<script type="text/javascript" src="../../build/event-delegate/event-delegate-min.js"></script>
<script type='text/javascript' src='../../mask.js'></script>
<script language="javascript">
var Dom = YAHOO.util.Dom;
var Event = YAHOO.util.Event;
var isDate = function  (value) {
	return (!isNaN (new Date (value).getYear () ) ) ;
}
function trim(s)
{
	var l=0; var r=s.length -1;
	while(l < s.length && s[l] == ' ')
	{	l++; }
	while(r > l && s[r] == ' ')
	{	r-=1;	}
	return s.substring(l, r+1);
}
var releaseMe = function() {
	//noSpecial(this);
	if(this.value!=""){
		Dom.removeClass(this, "required");
	} else {
		Dom.addClass(this, "required");
	}
}
var cleanMe = function() {
	noSpecial(this);
}
var enableSave = function() {
	var requireds = YAHOO.util.Dom.getElementsByClassName('required');
	var submit_button = Dom.get("submit");
	for(element in requireds) {
		var element_value = trim(requireds[element].value);
		
		//alert(requireds[element].id + " => " + requireds[element].value);
		if (element_value == "" || element_value == "mm/dd/yyyy") {
			submit_button.disabled = true;
			Dom.setStyle("required_guide", "display", "");
			
			return;
		}
	}
	submit_button.disabled = false;
	Dom.setStyle("required_guide", "display", "none");
	return;
}
var sendZip = function (obj, name, attribute, showField) {
	logEvent("sendZipping");
	//var obj = Dom.get(objName);
	var the_value = obj.value;
	var fullfield;
	if (showField) {
		fullfield = 1;
	} else {
		fullfield = 0;
	}
	logEvent("full:" + fullfield);
	this.cache = name + "|" + attribute + "|" + fullfield;
	logEvent(this.cache);
	//look for the zip after 5
	if (the_value.length == 5) {
		//alert("check it");
		//do a remote call
		var sendZipUrl = "check_zip.php";
		this.sentData = "query=" + the_value;
		logEvent(sendZipUrl + '?' + this.sentData);
		var request = YAHOO.util.Connect.asyncRequest('POST', sendZipUrl,
		   {success: function(o){
				var cache = this.cache;
				var arrCache = cache.split("|");
				var name = arrCache[0];
				var attribute = arrCache[1];
				var thefield = arrCache[2];
				if (thefield==0) {
					thefield = "Field";
				} else {
					thefield = "";
				}
				thefield = "";
				attribute = "";
				// = this.sentData;
				response = o.responseText;
				//mark it saved
				logEvent(response);
				var arrData = response.split("|");
				var stateField = Dom.get(name + "state" + thefield + attribute);
				stateField.value = arrData[1];
				logEvent("state:" + name + "city" + thefield + attribute);
				var cityField = Dom.get(name + "city" + thefield + attribute);
				cityField.value = arrData[0];
				logEvent("zip done");
				enableSave();
			},
		   after: function(){
			   //
			},
		   scope: this}, this.sentData);
	}
}
var logEvent = function (msg, status) {
	YAHOO.log(msg, status);
}
function noAlpha(obj){
	reg = /[^0-9.,]/g;
	obj.value =  obj.value.replace(reg,"");
}
function noSpecial(obj){
	<?php if ($ip_address=="72.87.128.38") { ?>
		//alert(obj.id);
	<?php } ?>
	reg = /[^0-9a-zA-Z ]/g;
	obj.value =  obj.value.replace(reg,"");
}
function validCharacters(f) {
	!(/^[A-z—Ò0-9\s]*$/i).test(f.value)?f.value = f.value.replace(/[^A-z—Ò0-9\s]/ig,''):null;
	<?php if ($ip_address=="72.87.128.38") { ?>
	<?php } ?>
}
var hrefSub = function(destination, getname, getvariable) {
	var theaction = "";
	var cartform = Dom.get("cartform");	
	theaction = String(destination) + ".php";
	//alert(theaction);
	//return;
	if (typeof getname != "undefined") {
		theaction += "?" + getname + "=" + getvariable;
	}
	if (theaction!="") {	
		cartform.action = theaction;
		cartform.submit();
	}
	return;
}
</script>
<form id="cartform" name="cartform" action="" enctype="multipart/form-data" method="post">
<input type="hidden" name="cus_id" value="<?php echo $cus_id; ?>" />
</form>