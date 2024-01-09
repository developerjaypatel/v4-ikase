function openKasePreviewPanel(case_id) {
	var panel_height = "700";
	var yoffset = 1373;
	//var info_holder = "kase_info_holder_" + case_id;
	var info_holder = "kase_info_holder";
	var case_number = $("#link_" + case_id).html();
	if (document.getElementById(info_holder) == null) {
		$.jsPanel({
			id:			info_holder,
			title:    	"Kase Preview - " + case_number,
			size:		{ width: "950", height: panel_height },
			
			selector: 	"#kase_feedback_holder",
			position: 	"bottom right",
			controls: {
				maximize: "disable"
			},
			content: 	"<div id='kase_feedback_text' style='margin-left:5px'>Loading Kase Info ...</div>",
			theme:    	"primary"
		});
	} else {
		//make sure it's positioned correctly
		$("#kase_holder.jsPanel").css("top", "0px");
		$("#kase_holder.jsPanel").css("left", "0px");
		$("#" + info_holder).fadeIn();
	}
	$("#" + info_holder + " .jsPanel-content").css("background", "white");
	$("#kase_feedback_holder").fadeIn();
	/*
	display: block; top: 77px; left: 24px; opacity: 1; z-index: 102; position: absolute; width: 1004px; height: 689px;
	*/
	var newTop =  ((document.documentElement.scrollTop - 500) + "px");
	if (document.documentElement.scrollTop == 0) {
		newTop = (window.innerHeight - yoffset);
	}
	$("#" + info_holder).css("top", newTop + "px");
	
	$("#" + info_holder).css("left", (window.innerWidth - 1000) + "px");
	$("#" + info_holder).css("width", "950px");
	$("#" + info_holder).css("height", (window.innerHeight - 205) + "px")
	$("#" + info_holder + " .jsPanel-hdr").css("background", "#3366CC");
	
	setTimeout(function() {
		$("#kase_feedback_text").html("<iframe src='reports/demographics_sheet.php?case_id=" + case_id + "' width='100%' height='" + panel_height + "px' frameborder='0' scrolling='yes' style='display:none' id='kase_preview_frame_" + case_id + "'></iframe>");
		$("#kase_preview_frame_" + case_id).show();
	}, 500);
}
var check_kase_id;
function checkKases() {
	clearTimeout(check_kase_id);
	/*
	kases.fetch({
		success: function (data) {
			//$('#kases_recent').html(new kase_list_category_view({model: data}).render().el);
		}
	});
	
	//get dois
	dois.fetch({
		success: function (data) {
			//
		}
	});
	*/
	
	recent_kases.fetch({
		success: function(recent_kases) {
			
			return;
		}
	});
	/*
	check_kase_id = setTimeout(function() {
		checkKases();
	}, 90000);
	*/
}
function saveRelatedModal(event) {
	$("#modal_save_holder").hide();
	$(".modal #gifsave").css("margin-top", "-5px");
	$(".modal #gifsave").show();
	
	//associate the case and the injury id
	var injury_id = $(".related #injury_id").val();
	if (injury_id=="undefined" || injury_id=="") {
		return false;
	}
	var case_id = current_case_id;
	blnKaseChangesDetected = true;
	
	var url = "api/kase/relate";
	var formValues = "case_id=" + case_id + "&injury_id=" + injury_id;
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else { // If not, send them back to the home page
				resetCurrentContent();
				
				$(".modal #gifsave").hide();
				$("#modal_save_holder").show();
				$("#myModalLabel").css("color", "lime");
				
				setTimeout(function() {
					$('#myModal4').modal('toggle');	
					resetCurrentContent();
					window.Router.prototype.fetchKase(current_case_id, true);
					
				}, 1500);
			}
		}
	});
}
function saveIntake(event) {
	if ($("#case_typeInput").val()=="WCAB") {
		if ($("#full_nameInput").val()=="") {
			$("#full_nameInput").css("border", "2px solid red");
			alert("Client Name is required for WC intake");
			return;
		}
		if ($("#start_dateInput").val()=="") {
			$("#start_dateInput").css("border", "2px solid red");
			alert("DOI is required for WC intake");
			return;
		}
	}
	
	if ($("#case_typeInput").val()=="NewPI") {
		if ($("#personal_injury_dateInput").val()=="") {
			$("#personal_injury_dateInput").css("border", "2px solid red");
			alert("Injury Date is required for PI intake");
			return;
		}
		var representing = $("#representingInput").val().capitalize();
		
		if ($("#" + representing + " #company_nameInput").val()=="") {
			$("#" + representing + " #company_nameInput").css("border", "2px solid red");
			alert(representing + " Name is required for PI intake");
			return;
		}
	}
	
	$("#intake_save").hide();
	$("#intake_gifsave").show();
	
	if (current_case_id == -1) {
		current_case_id = $("#kase_form #id").val();
	}
	if (blnAutoSave) {
		setTimeout(function() {
			//go to the dashboard for the new case
			document.location.href = "#kases/" + current_case_id;
		}, 2500);
		return;
	}
	addForm(event, "intake");
}
function saveKaseModal(event) {
	$("#input_for_checkbox").hide();
	$("#modal_save_holder").hide();
	$("#gifsave").css("margin-top", "-5px");
	$("#gifsave").show();
	
	addForm(event, "kase");
}
function searchKaseModal(event) {
	$("#input_for_checkbox").hide();
	$("#modal_save_holder").hide();
	$("#gifsave").css("margin-top", "-5px");
	$("#gifsave").show();
	
	searchForm(event, "kase");
}
function composeKase(event, next_case_number) {
	if (typeof next_case_number == "undefined") {
		next_case_number = "";
	}
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	
	var options = {};
	var reaction = "";
	var object_action = "New Kase";
	var kase = new Kase();
	
	if (next_case_number=="") {
		//no kase, no applicant
		var case_number_next = String(customer_settings.get("case_number_next"));
	} else {
		var case_number_next = next_case_number;
	}
	var blnModified = false;
	if (case_number_next < 10) {
		case_number_next = "000" + case_number_next;
		blnModified = true;
	}
	if (case_number_next < 100 && !blnModified) {
		case_number_next = "00" + case_number_next;
		blnModified = true;
	}
	if (case_number_next < 1000 && !blnModified) {
		case_number_next = "0" + case_number_next;
		blnModified = true;
	}
	kase.set("file_number", customer_settings.get("case_number_prefix") + case_number_next);
	//editable
	var case_editable = "N";
	if (typeof customer_settings.get("editable") != "undefined") {
		case_editable = customer_settings.get("editable");
	}
	kase.set("case_editable", case_editable); 
	kase.set("applicant_id", -1);
	kase.set("language", "");
	kase.set("intake_request", false);
	kase.set("interpreter_needed", "N");
	
	$("#myModalLabel").html(object_action);
	$("#input_for_checkbox").hide();
	$("#modal_type").val("kase");
	//openHomeMedical()
	$("#modal_save_holder").html('<a id="slide_special" style="color:white; cursor:pointer; " onClick="openSpecialInstructions()"  title="Click to enter Special Instructions">Special Instructions</a>&nbsp;|&nbsp;<a id="slide_homemedical" style="color:white; cursor:pointer; " onClick="openHomeMedical()"  title="Click to enter Home Medical information">Home Medical</a>&nbsp;&nbsp;<button title="Save Kase" class="kase save btn btn-primary btn-sm" onClick="saveKaseModal(event)" style="cursor:pointer">Save</button>&nbsp;&nbsp;');
	//$("#input_for_checkbox").html('&nbsp;');
	$("#myModalBody").html(new new_kase_view({model: kase}).render().el);
	$(".modal-header").css("background-image", "url('img/glass_edit_header_new.png')");
	$("#myModalBody").css("background", "url('img/glass_edit_header_new.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	setTimeout(function() {
		//$('.modal-dialog').css('top', '0px');
		//$('.modal-dialog').css('margin-top', '50px')
	}, 700);
	
	$(".modal-dialog").css("width", "800px");
	$(".modal-dialog").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-content").css("background-image", "url('img/glass_edit_header_new.png')");
	
	$('#myModal4').modal(options);
}
function composeIntake(event, next_case_number) {
	if (typeof next_case_number == "undefined") {
		next_case_number = "";
	}
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	
	var options = {};
	var reaction = "";
	var object_action = "New Kase";
	var kase = new Kase();
	
	if (next_case_number=="") {
		//no kase, no applicant
		var case_number_next = String(customer_settings.get("case_number_next"));
	} else {
		var case_number_next = next_case_number;
	}
	var blnModified = false;
	if (case_number_next < 10) {
		case_number_next = "000" + case_number_next;
		blnModified = true;
	}
	if (case_number_next < 100 && !blnModified) {
		case_number_next = "00" + case_number_next;
		blnModified = true;
	}
	if (case_number_next < 1000 && !blnModified) {
		case_number_next = "0" + case_number_next;
		blnModified = true;
	}
	kase.set("file_number", customer_settings.get("case_number_prefix") + case_number_next);
	//editable
	var case_editable = "N";
	if (typeof customer_settings.get("editable") != "undefined") {
		case_editable = customer_settings.get("editable");
	}
	kase.set("case_editable", case_editable); 
	kase.set("applicant_id", -1);
	kase.set("language", "");
	kase.set("holder", "content");
	kase.set("interpreter_needed", "N");
	kase.set("intake_request", true);
	
	$("#content").html(new new_kase_view({model: kase}).render().el);
}
function composeKaseSearch() {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	
	var kase = new Kase({id: -1});
	kase.set("holder", "myModalBody");
	kase.set("language", "english");
	$("#input_for_checkbox").hide();
	$("#myModalLabel").html("Search Kases");
	$("#modal_save_holder").html('<a title="Search Kase" class="kase save" onClick="searchKaseModal(event)" style="cursor:pointer"><i class="glyphicon glyphicon-search" style="color:yellow; font-size:20px">&nbsp;</i></a>');
	
	
	//$("#input_for_checkbox").html('&nbsp;');
	$("#apply_notes").hide();
	$("#myModalBody").html(new search_kase_view({model: kase}).render().el);
	$("#apply_notes").hide();
	$(".modal-header").css("background-image", "url('img/glass_edit_header_new.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	
	$(".modal-dialog").css("width", "900px");
	$(".modal-dialog").css("background-image", "url('img/glass_edit_header_new.png')");

	$(".modal-content").css("background-image", "url('img/glass_edit_header_new.png')");
	
	$(".modal-title").prepend('<div style="float:right;margin-right: 20px;font-size: 0.8em;"><input type="checkbox" onChange="setActiveCases(this)">Active Cases Only</div>');
	$('#myModal4').modal('show');		
}
function composeKaseEdit(element_id) {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	var kaseArray = element_id.split("_");
	if (kaseArray.length > 3) { 
		var case_array_id = kaseArray[3];
	} else {
		var case_array_id = kaseArray[2];
	}
	// console.log(kase);
	
	var kase = kases.findWhere({case_id: case_array_id});
	if (typeof kase == "undefined") {
		//get it
		var kase =  new Kase({id: case_array_id});
		kase.fetch({
			success: function (kase) {
				if (kase.toJSON().uuid!="") {
					// console.log('true');
					kases.remove(kase.id);
					kases.add(kase);
					composeKaseEdit(element_id);
				}
				// console.log('false');
				return;		
			}
		});
		return;
	}
   	// JAY last code 2
	// console.log('after main if');
	
	var mykase = kase.clone();
	mykase.set("language", mykase.get("case_language"));
	
	//editable
	var case_editable = "N";
	if (typeof customer_settings.get("editable") != "undefined") {
		case_editable = customer_settings.get("editable");
	}
	mykase.set("case_editable", case_editable); 
	mykase.set("holder", "myModalBody");
	// console.log(mykase);
	
	$("#input_for_checkbox").hide();
	$("#myModalLabel").html("Edit Kase");
	$("#modal_save_holder").html('<a id="slide_special" style="color:white; cursor:pointer; " onClick="openSpecialInstructions()"  title="Click to enter Special Instructions">Special Instructions</a><span id="modal_special_menus_separator">&nbsp;|&nbsp</span><a id="slide_homemedical" style="border:0px yellow solid; color:white; cursor:pointer;" onClick="openHomeMedical()">Home Medical</a>&nbsp;&nbsp;&nbsp;<button title="Save Kase" class="kase save btn btn-primary btn-sm" onClick="saveKaseModal(event)" style="cursor:pointer">Save</button>&nbsp;&nbsp;&nbsp;&nbsp;');
	//$("#input_for_checkbox").html('&nbsp;');
	$("#apply_notes").hide();
	$("#myModalBody").html(new new_kase_view({model: mykase}).render().el);
	$("#apply_notes").hide();
	$(".modal-header").css("background-image", "url('img/glass_edit_header_new.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	
	$(".modal-dialog").css("width", "800px");
	$(".modal-dialog").css("background-image", "url('img/glass_edit_header_new.png')");

	$(".modal-content").css("background-image", "url('img/glass_edit_header_new.png')");
	$('#myModal4').modal('show');		
}
function composeRelated(element_id) {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	var kaseArray = element_id.split("_");
	
	var related_model = new Backbone.Model();
	
	related_model.set("holder", "myModalBody");
	$("#myModalLabel").html("New Related Case");
	$("#modal_save_holder").html('<a title="Save Related Kase" class="related save" onClick="saveRelatedModal(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$("#apply_notes").hide();
	$("#myModalBody").html(new related_view({model: related_model}).render().el);
	$("#apply_notes").hide();
	$(".modal-header").css("background-image", "url('img/glass_edit_header_new.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	
	$(".modal-dialog").css("width", "800px");
	$(".modal-dialog").css("background-image", "url('img/glass_edit_header_new.png')");

	$(".modal-content").css("background-image", "url('img/glass_edit_header_new.png')");
	$('#myModal4').modal('show');		
}
function openHomeMedical(homemedical_id) {
	var homemedical = new HomeMedical({case_id: current_case_id});
	homemedical.fetch({
		success: function (homemedical) {
			$("#side_holder").show();
			homemedical.set("holder", "homemedical");
			$('#homemedical').html(new home_medical_view({model: homemedical}).render().el);
			$("#slide_homemedical").fadeOut(function() {
				$("#modal_special_menus_separator").hide();
			});
		}
	});
	if (typeof homemedical_id == "undefined") {
		homemedical_id = -1
	}
	homemedical.set("id", homemedical_id);
	homemedical.set("homemedical_id", homemedical_id);
	homemedical.set("case_id", $(".kase #table_id").val());
}
function openSpecialInstructions() {
	$('.modal-dialog').animate({width:1300, marginLeft:"-600px"}, 1100, 'easeInSine', 
		function() {
			//run this after animation
			$("#side_holder").show();
			$('#special_instructions_holder').fadeIn()
		}
	);
	$("#slide_special").fadeOut(function() {
		$("#modal_special_menus_separator").hide();
	});
}
function saveSSN(case_id) {
	var url = "api/kase/claim";
	var formValues = $("#claim_form").serialize();
	formValues += "&case_id=" + case_id;
	
	var blnIntake =  (document.location.hash.indexOf("#intake") > -1);
	if (blnIntake) {
		if (blnAutoSave) {
			$("#phone_intake_feedback_div").html("Autosaving...");
		}
	}
	blnKaseChangesDetected = true;
	
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else { // If not, send them back to the home page
				resetCurrentContent();
				//intake ?
				/*
				var blnIntake =  (document.location.hash.indexOf("#intake") > -1);;
				if (blnIntake) {
					//save the applicant
					$(".person #case_id").val(current_case_id);
					$(".button_row .save").trigger("click");
					return;
				}
				*/
				if (blnIntake) {
					if (blnAutoSave) {
						$("#ssn_claim_title").css("color", "lime")
						$("#phone_intake_feedback_div").html("Autosaved&nbsp;&#10003;");
						setTimeout(function() {
							$("#ssn_claim_title").css("color", "white")
							$("#phone_intake_feedback_div").html("");
							$("#claim_id").val(data.claim_id);
						}, 2500);
					} 
					return;
				}
				var blnDisability =  (document.location.hash.indexOf("#disabilities") > -1);
				if (blnDisability) {
					$("#panel_title").css("color", "lime");
					$("#gifsave").hide();
					$(".button_row.ssn_claim").toggleClass("hidden");
					
					setTimeout(function() {
						$("#panel_title").css("color", "white");
					}, 2500);
					
					return;
				}
			}
		}
	});
}
function saveSurgery() {
	var url = "api/surgery/save";
	var formValues = $("#surgery_form").serialize();
	formValues += "&case_id=" + current_case_id;
	
	blnKaseChangesDetected = true;
	
	$('#myModal4').modal('toggle');	
	
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else { // If not, send them back to the home page
				resetCurrentContent();
				
				var blnDisability =  (document.location.hash.indexOf("#disabilities") > -1);;
				if (blnDisability) {
					//refresh the list of surgeries
					
					var surgeries = new SurgeryCollection({ case_id: current_case_id });
					surgeries.fetch({
						success: function(data) {
							if (data.length > 0) {
								var surgery = new Backbone.Model;
								surgery.set("embedded", true);
								surgery.set("case_id", current_case_id);
								surgery.set("holder", "surgery_holder");
								
								$('#surgery_holder').html(new surgery_listing_view({collection: data, model: surgery}).render().el);
								$("#surgery_holder").show();
							}
						}
					});
				}
			}
		}
	});
}
function sendThirdPartyWarning() {
	var kase = kases.findWhere({case_id: current_case_id});
	var worker = kase.get("worker");
	var supervising_attorney = kase.get("supervising_attorney");
	var arrWorkers = [];
	if (worker!="") {
		arrWorkers.push(worker);
	}
	if (supervising_attorney!="") {
		arrWorkers.push(supervising_attorney);
	}
	if (arrWorkers.length > 0) {
		var sent_to = arrWorkers.join(";");
		var case_number = kase.get("case_number");
		if (case_number=="") {
			case_number = kase.get("file_number");
		}
		var note = "Case " + case_number + " was closed by " + login_nickname + ". There is a Third Party Attorney associated with the case. Please make sure to include the Attorney in the final settlement";
		
		//send interoffice
		var formValues = { 
			table_name : "message",
			message_to : sent_to,
			messageInput: note,
			case_id: current_case_id,
			send_document_id: "",
			subject: "Case " + case_number + " - Case Closed - Third Party Reminder",
			from: login_username,
			notification: "N"
		};
		var url = "api/messages/add";
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else { 
				}
			}
		});
	}
}