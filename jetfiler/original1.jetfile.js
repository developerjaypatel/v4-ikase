var saveFailed = function(msg) {
	
	alert("There was an error. Please contact support with details of this issue: \r\n\r\n" + msg);
}
var sendZip = function (obj, name, attribute) {
	var the_value = obj.value;
	
	//look for the zip after 5
	if (the_value.length == 5) {	
		var url = "../api/checkzip/" + the_value;
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			success:function (data) {
				$("#" + name + "state" + attribute).val(data.state_prefix);
				$("#" + name + "city" + attribute).val(data.city);
				enableSave();
				if (name == "injury") {
					var obj = document.getElementById(name + "state" + attribute);
					saveIkaseData(obj, 'injury', 'state');
					var obj = document.getElementById(name + "zip_code" + attribute);
					saveIkaseData(obj, 'injury', 'zip');
					var obj = document.getElementById(name + "city" + attribute);
					saveIkaseData(obj, 'injury', 'city');
				}
			}
		});
	}
}
var showSaveLink = function(event) {
	var element_id = event.target.id;
	
	//bodyparts
	if (element_id.indexOf("body_part") > -1) {
		element_id = "body_part";
	}
	$("#save_" + element_id + "_holder").fadeIn(
		function() {
			$("#save_" + element_id).show();
		}
	);
}
var showSaveDisagreementLink = function() {
	var element_id = "disagreement";
	showSaveLinkByID(element_id);
	
	enablePage3();
}
var showSaveOtherCasesLink = function() {
	var element_id = "other_cases";
	showSaveLinkByID(element_id);
}
var showSaveMedicalTreatmentLink = function() {
	var element_id = "medical_treatment";
	showSaveLinkByID(element_id);
}
var showSaveEarningsLink = function() {
	var element_id = "earnings";
	showSaveLinkByID(element_id);
}
var showSaveDisabilityLink = function() {
	var element_id = "disability";
	showSaveLinkByID(element_id);
}
var showSaveCompensationLink = function() {
	var element_id = "compensation";
	showSaveLinkByID(element_id);
}
var showSaveLinkByID = function(element_id) {
	$("#save_" + element_id + "_holder").fadeIn(
		function() {
			$("#save_" + element_id).show();
		}
	);
}
var showSavePageLink = function(event) {
	var element_id = event.target.name;
	$("#save_" + element_id + "_holder").fadeIn(
		function() {
			$("#save_" + element_id).show();
		}
	);
}
var saveHeaderInjury = function(event) {
	var element_id = event.target.id.replace("save_", "");
	var element = document.getElementById(element_id);
	event.preventDefault();
	
	if (element_id=="case_number") {
		//correct length and prefix
		if (!checkADJ()) {
			return;
		}
		element_id = "adj_number";
	}
	saveIkaseData(element, "injury", element_id);
}
var saveHeaderApplicant = function(event) {
	var element_id = event.target.id.replace("save_", "");
	var element = document.getElementById(element_id);
	event.preventDefault();
	saveIkaseData(element, "person", element_id);
}
var showSaveApplicantLink = function(event) {
	$("#save_applicant_holder").fadeIn(function() {
		$("#save_applicant").show();
	});
}
var triggerSaveApplicant = function(event) {
	if (document.getElementById("save_applicant_holder").style.display!="none") {
		saveApplicant(event);
	}
}
var saveApplicant= function(event) {
	event.preventDefault();
	
	var applicant_inputs = document.getElementsByClassName("applicant_input");
	var arrLength = applicant_inputs.length;
	for(var i = 0; i < arrLength; i++) {
		var element = applicant_inputs[i];
		var element_id = element.id;
		element_id = element_id.replace("applicant_", "");
		element_id = element_id.replace("zip_code", "zip");
		saveIkaseData(element, "person", element_id);
	}
	
	$("#save_applicant_holder").fadeOut(function() {
		$("#save_applicant").hide();
	});
}
var saveInjuryStory = function(event) {
	event.preventDefault();
	var element = document.getElementById("explanation");
	
	saveIkaseData(element, "injury", "explanation");
}
var saveBodypart = function(event) {
	event.preventDefault();
	//get all the body parts, save them
	var arrValues = [];
	for(var i = 1; i < 11; i++) {
		arrValues.push("bodypart" + i + "=" + $("#body_part" + i).val());
	}
	var injury_id = $("#injury_id").val();
	var formValues = "injury_id=" + injury_id + "&" + arrValues.join("&");
	
	var url = "../api/bodyparts/add";
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			$("#feedback_bodyparts").html('Saved&nbsp;&#10003;');
			
			setTimeout(
				function() {
					$("#feedback_bodyparts").html('');
					$("#save_body_part_holder").fadeOut();
				}, 2500
			);
		}
	});
}
var saveVenue = function(event) {
	event.preventDefault();
	var element = document.getElementById("letter_office_code");
	//saveIkaseData(element, "kase", element.id);
}
var saveIkaseData = function(obj, table_name, field) {
	obj.style.background = "orange";
	var case_id = document.getElementById("case_id").value;
	var injury_id = document.getElementById("injury_id").value;
	var table_id = "";
	
	var field_id = "#feedback_" + obj.id;
	
	$(field_id).html('Saving...');
	$(field_id).show();
	
	var button_id = "save_" + obj.id; 
	$("#" + button_id).hide();
	
	switch(table_name) {
		case "person":
			table_id = document.getElementById("person_id").value;
			break;
		case "injury":
			if (table_id == "") { 
				if (typeof document.getElementById("injury_id") != "undefined") {
					table_id = document.getElementById("injury_id").value;
				}
			}
			break;
	}
	var formValues = "table_name=" + table_name + "&table_id=" + table_id + "&case_id=" + case_id;
	if (field != "ssn") {
		formValues += "&" + field + "=" + obj.value;
	} else {
		//break up the ssn into 3 chuncks
		var ssn = obj.value;
		var ssn1 = ssn.substr(0, 3);
		var ssn2 = ssn.substr(4, 2);
		var ssn3 = ssn.substr(5, 4);
		
		formValues += "&ssn1=" + ssn1;
		formValues += "&ssn2=" + ssn2;
		formValues += "&ssn3=" + ssn3;
	}
	var url = "../api/" + table_name + "/update";
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			obj.style.background = "green";
			
			setTimeout(
				function() {
					obj.style.background = "";
				}, 2500
			);
			
			$(field_id).html('Saved&nbsp;&#10003;');
			
			setTimeout(
				function() {
					$(field_id).html('');
					$(field_id).fadeOut();
				}, 2500
			);
		}
	});
}
var pageOne = function() {
	var path = "app_1_2.php";
	goToPage(path);
}
var pageTwo = function() {
	var path = "app_3_4.php";
	goToPage(path);
}
var pageUploads = function() {
	var path = "upload_app_for_adj.php";
	goToPage(path);
}
var goToPage = function(path) {
	var case_id = document.getElementById("case_id").value;
	var injury_id = document.getElementById("injury_id").value;
	var jetfile_id = document.getElementById("jetfile_id").value;
	var params = [];
	params["case_id"] = case_id;
	params["injury_id="] = injury_id;
	params["jetfile_id"] = jetfile_id;
	var method = "POST";
	
	//console.log(params);
	//return;
	postForm(path, params, method, "_self");
}
var enableSave = function() {
	var requireds = $('.required');
	var submit_button = $(".submit");
	var first_i = -1;
	
	for(var i =0; i < requireds.length; i++) {
		var element_value = requireds[i].value.trim();		
		requireds[i].style.border = "";
		if (element_value == "" || element_value == "mm/dd/yyyy") {
			requireds[i].style.background = "pink";
			//console.log(requireds[i].id);
			if (first_i < 0) {
				first_i = i;
			}
		} else {
			requireds[i].disabled = false;
			requireds[i].style.border = "2px solid green";
			requireds[i].style.background = "none";
		}
	}
	
	if (first_i > -1) {
		submit_button.prop("disabled", true);
		
		//cannot proceed
		if (document.getElementById("proceed_2") != null) {
			document.getElementById("proceed_2").style.display = "none";
			document.getElementById("proceed_1").style.display = "none";
		}
		$(".required_guide").css("display", "");
		var statement = "Please fill out all Required Fields";
		var required_field = requireds[first_i].id.replace("_", " ").capitalizeWords();
		$(".required_guide").html(statement + " - " + required_field);
		requireds[first_i].style.border = "2px solid red";
		requireds[first_i].focus();
		return;
	}
	
	submit_button.prop("disabled", false);
	$(".required_guide").css("display", "none");
	
	if ($("#jetfile_feedback").length > 0) {
		//we can only file once everything is green
		checkApplicant();
	}
	if ($("#dor_feedback").length > 0) {
		//we can only file once everything is green
		checkDOR();
	}
	if ($("#dore_feedback").length > 0) {
		//we can only file once everything is green
		checkDORE();
	}
	return;
}
var releaseMe = function() {
	//noSpecial(this);
	var obj_id = this.id;
	var obj = $("#" + obj_id);
	
	if(obj.val()!=""){
		obj.css("background", "");
	} else {
		obj.css("background", "pink");
	}
	//enableSave();
}
var cleanMe = function() {
	noSpecial(this);
}
function noAlpha(obj){
	reg = /[^0-9.,]/g;
	obj.value =  obj.value.replace(reg,"");
}
function noAlphaComma(obj){
	reg = /[^0-9.]/g;
	obj.value =  obj.value.replace(reg,"");
}
function noSpecial(obj){
	reg = /[^0-9a-zA-Z ]/g;
	obj.value =  obj.value.replace(reg,"");
}
function validCharacters(f) {
	!(/^[A-zÑñ0-9\s]*$/i).test(f.value)?f.value = f.value.replace(/[^A-zÑñ0-9\s]/ig,''):null;
}
function isDate(dateStr) {

	var datePat = /^(\d{1,2})(\/|-)(\d{1,2})(\/|-)(\d{4})$/;
	var matchArray = dateStr.match(datePat); // is the format ok?
	
	if (matchArray == null) {
		//alert("Please enter date as either mm/dd/yyyy or mm-dd-yyyy.");
		return false;
	}
	
	month = matchArray[1]; // p@rse date into variables
	day = matchArray[3];
	year = matchArray[5];
	
	if (month < 1 || month > 12) { // check month range
		//alert("Month must be between 1 and 12.");
		return false;
	}
	
	if (day < 1 || day > 31) {
		//alert("Day must be between 1 and 31.");
		return false;
	}
	
	if ((month==4 || month==6 || month==9 || month==11) && day==31) {
		//alert("Month "+month+" doesn`t have 31 days!")
		return false;
	}
	
	if (month == 2) { // check for february 29th
		var isleap = (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0));
		if (day > 29 || (day==29 && !isleap)) {
			//alert("February " + year + " doesn`t have " + day + " days!");
			return false;
		}
	}
	//alert(dateStr + " is valid");
	return true; // date is valid
}
var clearInjuryAddress = function() {
	alert("The Injury Location must be in California!");
	var injury_addresses = document.getElementsByClassName('doi_address');
	for(element in injury_addresses) {
		injury_addresses[element].value = "";
	}
}
var checkInjuryCA = function() {
	var injury_state = document.getElementById("injury_state");
	var injury_state_value = injury_state.value;
	if (injury_state_value.length==2) {
		//alert(injury_state_value);
		if (injury_state_value!="CA") {
			clearInjuryAddress();
			return;
		}
	}
}
var enableInjuryDates = function(fieldlist) {
	var injury_dates = document.getElementsByClassName('injury_date');
	for(var i = 0; i < injury_dates.length; i++) {
		injury_dates[i].disabled = true;
		injury_dates[i].value = "";
		//jquery
		var field = $("#" + injury_dates[i].id);
		field.removeClass("required");
		field.css("background", "");
	}
	var arrFields = fieldlist.split("|");
	for(int=0;int<arrFields.length;int++) {
		fieldname = arrFields[int];
		var field = document.getElementById(fieldname);
		field.disabled = false;
		
		field = $("#" + field.id);
		//swith to jquery
		field.addClass("required");
		
		field.on("keyup", releaseMe);
	}
	//is save still allowed
	enableSave();
}
var checkDOB = function() {
	if (client_birth_date=="") {
		enableSave()
		return;
	} else  {
		var specific_injury = document.getElementById("specific_injury");
		var cumulative_injury = document.getElementById("cumulative_injury");
		var blnProceed = false;
		if (specific_injury.checked) {
			blnProceed = true;
			var thedate = document.getElementById('specific_injury_date');
		}
		if (cumulative_injury.checked) {
			//alert("ck");
			blnProceed = true;
			var thedate = document.getElementById('ct_injury_start_date');
		} else {
			//alert("no ck");
		}
		if (blnProceed) {
			if (thedate.value=="" || thedate.value=="mm/dd/yyyy") {
				blnProceed = false;
			}
		} 
		if (!blnProceed) {
			enableSave()
			return;
		}
		blnProceed = true;
		
		if (!isDate(thedate.value)) {
			blnProceed = false;
		}
		//alert(getTime(ct_injury_start_date));
		if (blnProceed) {
			var start_date= new Date(client_birth_date).getTime();
			var end_date= new Date(thedate.value).getTime();
			
			if (eval(end_date - start_date)<0){		
				alert("Injury Date cannot before DOB");
				thedate.value = "";
				thedate.focus();
				return;
			}
	
		} else {
			return;
		}
		//alert("here");
		enableSave();
	}
}
var checkDateSpan = function() {
	var ct_injury_start_date = document.getElementById("ct_injury_start_date");
	var ct_injury_end_date = document.getElementById("ct_injury_end_date");
	var blnProceed = true;
	if (!isDate(ct_injury_start_date.value) || !isDate(ct_injury_end_date.value)) {
		blnProceed = false;
	}
	//alert(getTime(ct_injury_start_date));
	if (blnProceed) {
		var start_date= new Date(ct_injury_start_date.value).getTime();
		var end_date= new Date(ct_injury_end_date.value).getTime();
		
		if (eval(end_date - start_date)<0){		
			alert("End Date cannot before Start Date");
			ct_injury_end_date.value = "";
			return;
		}
		saveInjury(ct_injury_end_date, 'end_date');
	}
}
var checkADJ = function() {
	var case_number = document.getElementById("case_number");
	//see if the first 3 characters are ADJ
	var adj_number = case_number.value;
	if (adj_number!="") {
		if (adj_number.length > 2) {
			var first_three = adj_number.substr(0, 3);
			//alert(first_three);
			if (first_three!="ADJ" && first_three!="adj") {
				alert("The ADJ Number must start with the letters A D J");
				case_number.value = "";
				return false;
			}
		}
		if (adj_number.length > 3) {
			var pure_number = adj_number.substring(3);
			//alert(adj_number + " - " + pure_number);
			if (isNaN(pure_number)) {
				alert("The ADJ Number consists of the letters A D J followed by at least 7 numbers");
				case_number.value = "ADJ";
				return false;
			}
		}
	}
	
	return true;
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
		var person_name = document.getElementById(type + "_name");
		var the_value = person_name.value;
		if (the_value == "") {
			document.getElementById("list_" + type + "_searches").style.display = "none";
		}
	}
	return;
}
var checkPDF = function(obj_id) {
	var obj = document.getElementById("file_up_" + obj_id);
	var holder = document.getElementById("holder_" + obj_id);
	//alert(obj.value);
	//break up the value by ., look for extension
	var arrFile = obj.value.split(".");
	var extension = arrFile[arrFile.length-1];
	if (extension!="pdf" && extension!="PDF") {
		alert("PDF documents only please.");
		//clearFileInputField(obj.id)
		holder.innerHTML = '<input type="file" name="file_up_' + obj_id + '" id="file_up_' + obj_id + '" tabindex="0" style="color:#000000" class="required" onchange="checkPDF(' + obj_id + ')" />';
	} else {
		enableSave();
	}
}
var setDisplayStyle = function(element_id, property, value) {
	if (typeof document.getElementById(element_id) != "undefined" && document.getElementById(element_id)!=null) {
		document.getElementById(element_id).style.display = value;
	}
}