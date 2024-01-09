// The Template Loader. Used to asynchronously load templates located in separate .html files
window.templateLoader = {

    load: function(views, callback) {

        var deferreds = [];
		var extension;
        $.each(views, function(index, view) {
			if (window[view]) {
				extension = 'html';
				if (view=="webmail_listing_view" || view=="thread_listing" || view=="check_form" || view=="costs_view" || view=="check_listing_view" || view=="home_medical_view" || view=="activity_listing_view" || view=="archive_listing_view" || view=="partie_listing_choose" || view=="partie_listing_event" || view=="chat_view" || view=="kase_list_category_view" || view=="kase_list_report" || view=="kase_list_applicant_report" || view=="calendar_view" || view=="import_view" || view=="applicant_view" || view=="dialog_view" || view=="dashboard_view" || view=="dashboard_settlement_view" || view=="dashboard_injury_view" || view=="dashboard_user_view" || view=="document_listing_view" || view=="document_listing_search" || view=="document_listing_message" || view=="document_upload_view" || view=="eams_applicant_view" || view=="eams_previouscases_view" || view=="eams_bodyparts_view" || view=="eams_parties_view" || view=="eams_events_view" || view=="eams_hearings_view" || view=="eams_scrape_view" || view=="eams_view" || view=="eams_form_listing" || view=="eams_form_view" || view=="eams_form_attach" || view=="eams_listing_view" || view=="injury_view" || view=="bodyparts_view" || view=="person_view" || view=="kai_view" || view=="notes_view" || view=="parties_view" || view=="partie_cards_view" || view=="parties_new_view" || view=="partie_view" || view=="kase_listing_view" || view=="kase_header_view" || view=="kase_edit_view" || view=="kase_summary_view" || view=="kase_nav_bar_view" || view=="kase_nav_left_view" || view=="search_kase_view" || view=="email_view" || view=="signature_view" || view=="user_view" || view=="dashboard_person_view"  || view=="dashboard_home_view" || view=="person_image" || view=="injury_number_view" || view=="injury_add_view" || view=="message_view" || view=="message_listing" || view=="note_listing_view" || view=="message_attach" || view=="interoffice_view" || view=="stack_listing_view" || view=="task_view" || view=="task_listing" || view=="task_print_stack_listing" || view=="new_kase_view" || view=="new_note_view" || view=="chatting_view" || view=="event_view" || view=="event_listing" || view=="setting_view" || view=="letter_view" || view=="letter_listing_view" || view=="letter_attach" || view=="setting_attach" || view=="kase_list_task_view" || view=="template_listing_view" || view=="template_upload_view" || view=="note_print_view" || view=="task_print_view" || view=="task_print_listing" || view=="message_print_view" || view=="message_print_view1" || view=="message_print_listing" || view=="rolodex_listing_view" || view=="user_listing_view" || view=="user_setting_listing" || view=="parties_new_rolodex" || view=="kase_control_panel" || view=="form_listing" || view=="kase_letter_listing_view" || view=="prior_treatment_listing_view" || view=="kase_occurences_print_view" || view=="event_listing_print" || view=="settlement_view" || view=="prior_referral_view" || view=="exam_view" || view=="exam_listing" || view=="document_search" || view=="lien_view" || view=="kases_report" || view=="clients_report" || view=="referrals_report" || view =="vservice_view" || view =="vservices_view" || view =="dashboard_accident_view" || view =="accident_view" || view=="medical_specialties_select" || view=="payments_print_listing" || view=="rental_view" || view=="multichat" || view=="multichat_messages" || view=="reset_password_view" || view=="property_damage_view" || view=="car_passenger_view" || view=="accident_new_view" || view=="accident_new_view" || view=="dashboard_slipandfall_view" || view=="dashboard_motorcycle_view" || view=="dashboard_naturalcause_view" || view=="bulk_webmail_assign_view" || view=="bulk_import_assign_view" || view=="dashboard_email_view" || view=="red_flag_note_listing_view" || view=="dashboard_related_cases_view" || view=="bulk_date_change_view" || view=="contact_listing_view" || view=="contact_view" || view=="personal_injury_view" || view=="activity_print_listing_view" || view=="activity_print_summary_view" || view=="note_print_listing_view" || view=="med_index_report" || view=="personal_injury_general_view" || view=="personal_injury_dogbite_view" || view=="personal_injury_slipandfall_view" || view=="financial_view" || view=="personal_injury_image" || view=="new_legal_view" || view=="coa_view" || view=="coa_listing_view" || view=="partie_kai_view" || view=="billing_listing_view" || view=="activity_view" || view=="invoice_listing_view" || view=="invoice_print_listing_view" || view=="user_listing_print_view" || view=="billing_view" || view=="activity_bill_view") {
					extension = 'php';
				}				
				// || view=="billing_listing_view"
				deferreds.push($.get('templates/' + view + '.' + extension, function(data) {
					window[view].prototype.template = _.template(data);
				}, 'html'));
			
            } else {
                console.log(view + " not found");
				if (view=="kase_view.php") {
					$("#content").html("There was a problem loading pages.  Please reload");
					return;
				}
            }
        });

        $.when.apply(null, deferreds).done(callback);
    }
};
function loadTemplate(view, extension, self) {
	$.get('templates/' + view + '.' + extension, function(data) {
		window[view].prototype.template = _.template(data);
		var new_view = window[view];
		//console.log(self);
		self.model.set("template_loaded", true);
		var holder_id = self.model.get("holder");
		
		if (typeof holder_id=="undefined") {
			console.log(view + " has no holder");
			return;
		}
		//in case i forgot
		if (document.location.pathname.indexOf("v9") > -1 && view == "event_view") {
			holder_id = "." + holder_id.replace(".", "");
		} else {
			holder_id = "#" + holder_id.replace("#", "");
		}
		$(holder_id).html(new new_view({collection: self.collection, model: self.model}).render().el);
	}, 'html')
	.fail(function() {
		console.log( "template " + view + "." + extension + " could not be loaded" );
		
		if (view=="kase_view") {
			alert("There was a problem loading pages.  Please reload");
		}
	  });
}
String.prototype.alphaNumeric = function() {
	return this.replace(/[^a-z0-9]/gi,'');
}
String.prototype.firstLetters = function() {
	var thestring = this;
	var arrString = thestring.split(" ");
	var arrFirstLetters = [];
	arrayLength = arrString.length;
	if (arrayLength > 1) {
		for (var i = 0; i < arrayLength; i++) {
			var theword = arrString[i];
			var thefirstletter = theword.substring(0, 1);
			arrFirstLetters[arrFirstLetters.length] = thefirstletter;
		}
	} else {
		arrFirstLetters[arrFirstLetters.length] = this;
	}
	return arrFirstLetters.join("");
}
String.prototype.capitalize = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
}
String.prototype.capitalizeWords = function(lower) {
	return this.toLowerCase().replace(/^.|\s\S/g, function(a) { return a.toUpperCase(); });
};
String.prototype.capitalizeAllWords = function() {
	var words = this.split(/(\s|-)+/),
        output = [];

    for (var i = 0, len = words.length; i < len; i += 1) {
		if (typeof words[i][0] != "undefined") {
        	output.push(words[i][0].toUpperCase() +
                    words[i].toLowerCase().substr(1));
		}
    }

    return output.join('');
}
function fixMarty(s) {
  return (""+s).replace(/Mc(.)/g, function(m, m1) {
    return 'Mc' + m1.toUpperCase();
  });
}
String.prototype.replaceTout = function(findString, replaceString) {
	var thestring = this.toString();
	if (findString != replaceString) {	
		while (thestring.indexOf(findString) > -1) {
			thestring = thestring.replace(findString, replaceString);
		}
	}
	
	return thestring;
}
String.prototype.replaceAll = function(find, replace) {
  return this.replace(new RegExp(find, 'g'), replace);
}
String.prototype.ssnFormat = function() {
	var ssn = this;
	ssn = this.substring(0,3) + "-" + this.substring(3,5) + "-" + this.substring(5,9);
	return ssn;
}
var noAlpha = function (obj){
	reg = /[^0-9.,]/g;
	obj.value =  obj.value.replace(reg,"");
}
var isAlpha = function(str) {
	reg = /^[a-zA-Z]*$/i;
	var val =  str.replace(reg,"");
	
	return (val!=str);
}
var validEAMSName = function(value) {
	reg = /^[a-z0-9_ .-]*$/i;
	value =  value.replace(reg,"");
	return value;
}
var highLight = function(str, key) {
	if (key=="" || key==null || typeof key == "undefined") {
		return str;
	}
	if (str==null) {
		return "";	
	}
	str = str.replace(key, "<~>" + key + "</~>");
	//in case its a capitalized word
	str = str.replace(key.capitalizeWords(), "<~>" + key.capitalizeWords() + "</~>");
	//in case of McSomething
	//str = fixMarty(str.toString().toLowerCase().replace("mc", "Mc"));
	//str = str.replace(fixMarty(key.replace("mc", "Mc")), "<~>" + fixMarty(key.replace("mc", "Mc")) + "</~>");
	str = str.replaceAll("<~", "<span class='highlighted_text' style='font-weight:bold; color:yellow'");
	str = str.replaceAll("~", "span");
	return str;
}
String.prototype.getComplete = function(len) {
	var str = this;
	if (str.length > len) {
		var i = str.indexOf(" ", len);
		return str.substring(0, i);
	}
	return str;
}
String.prototype.getAge = function(d2) {
	if (!d2) {
		d2 = new Date();
	}
	//d2 = new Date();
	var thevalue = this.valueOf();
	if (thevalue=="") {
		return "";
	}
	d1 =  new Date(moment(thevalue));
    var diff = d2.getTime() - d1.getTime();
	
	if (isNaN(diff)) {
		return "";
	} else {
    	return Math.floor(diff / (1000 * 60 * 60 * 24 * 365.25));
	}
	//return moment(this.valueOf()).fromNow(true).replace(" years", "");
}
String.prototype.cleanString = function() {
	return this.replace(/[\[\]|&;$%@"<>()+,]/g, "");
}
String.prototype.numbersOnly = function() {
	return this.replace(/[^0-9.]/g, "");
}
//remove html altogether
function removeHtml(str) {
	var regex = /(<([^>]+)>)/ig;
	var result = str.replace(regex, "");
	return result;
}
// Use the browser's built-in functionality to quickly and safely escape the
// string
function escapeHtml(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
};
 
// UNSAFE with unsafe strings; only use on previously-escaped ones!
function unescapeHtml(escapedStr) {
    var div = document.createElement('div');
    div.innerHTML = escapedStr;
    var child = div.childNodes[0];
    return child ? child.nodeValue : '';
};
//remove all html except links
function linksOnly(str) {
	str=str.replace(/<br>/gi, "\n");
	str=str.replace(/<p.*>/gi, "\n");
	str=str.replace(/<a.*href="(.*?)".*>(.*?)<\/a>/gi, " $2 (Link->$1) ");
	str=str.replace(/<(?:.|\s)*?>/g, "");
	
	return str;
}
function executeChanges() {
	if ($("#kase_content").length > 0) {
		$("#kase_content").html("<table align='center'><tr><td align='center' style=''><i class='icon-spin4 animate-spin' style='font-size:6em; color:white'></i><td><tr></table");
	}
	if ($("#search_results").height() == "1px") {
		$("#content").css("margin-top", "0px")
	}
	if ($("#content").css("margin-top") == "10px") {
		$("#content").css("margin-top", "0px")
	}
	if ($("#content").css("margin-top") != "65px") {
		$("#content").css("margin-top", "0px")
	}
	//if ($("#content").css("margin-top") == "65px") {
		//$("#content").css("margin-top", "0px")
	//}
	$("#content").removeClass("fc");
	$("#content").removeClass("fc-touch");
	$("#content").removeClass("fc-ltr");
	$("#content").removeClass("fc-unthemed");
	//fc fc-touch fc-ltr fc-unthemed
	$("#search_results").html("");
	$("#page_title").fadeOut();
};
function executeMainChangesKal() {
	//reset batchscan interval
	check_remote_id = false;
	
	if (document.location.pathname.indexOf("v9") > -1 || document.location.pathname.indexOf("v8") > -1 || document.location.pathname.indexOf("v7") > -1) {
		if (!$("#myModal4").hasClass("in")) {
			$("#myModalBody").html("");
		}
		$("#search_document").hide();

		if ($("#content").css("margin-top") == "65px") {
			$("#content").css("margin-top", "0px")
		}
		if ($("#content").css("margin-top") == "65px") {
			$("#content").css("margin-top", "0px")
		}
		if (!blnSearched) {
			if ($("#content").css("top") == "0px") {
				$("#content").css("top", "60px");
			}
		} else {
			if ($("#content").css("top") == "60px") {
			//	$("#content").css("top", "0px");
			}
		}
		
		$(".token-input-dropdown-person").hide();
		$(".token-input-list-facebook").hide();
		$(".token-input-dropdown-facebook").hide();
		$("#search_modifiers").fadeOut();
		$("#page_title").fadeOut();
		
		//calendar reset
		current_calendar_view = "";
		current_calendar_start = "";
		current_calendar_end = "";
		//$("#srch-term").blur();
	}
	if (document.location.pathname.indexOf("v9") > -1) {
		if (!kase_searching) {
			if ($("#content").css("top") == "0px") {
				$("#content").css("top", "0px");
			}
		} else {
			if (blnSearched) {
				
					$("#content").css("top", "0px");
					$("#kase_content").css("margin-top", "0px");
				
			}
		}
		if (!blnSearched) {
			if ($("#content").css("top") == "0px") {
				$("#content").css("top", "0px");
			}
		} else {
			if ($("#content").css("top") == "60px") {
				$("#content").css("top", "0px");
			}
		}
	}
	if ($("#srch-term").val()!="") {
		$("#srch-term").val("");
		$("#srch-term").trigger("blur");
	}
}
function getCurrentCaseID() {
	if (current_case_id==-1) {
		//catchup
		var hash = document.location.hash;
		var arrLocs = ["kase", "parties", "notes", "letters", "exams", "eams_forms", "payments", "injury", "tasks", "kalendarlist", "documents"];
		for (var i = 0; i < arrLocs.length; i++) {
			if (hash.indexOf("#" + arrLocs[i] + "/")==0) {
				current_case_id = document.location.hash.split("/")[1];
				break;
			}
		}
	}
}
function executeMainChanges() {
	//new kase warning
	if (typeof current_case_id == "undefined") {
		current_case_id = -1;
	}
	
	//however
	getCurrentCaseID();
	
	//rolodex
	if (current_rolodex_search!="") {
		if (document.location.hash.indexOf("#rolodex/") < 0 && document.location.hash.indexOf("#rolodex") < 0) {
			current_rolodex_search = "";
		}
	}
	
	if (typeof composeNewNote != "undefined") {		
		var leaving_case_id = current_case_id;
		
		setTimeout(function() {
			//for 1109
			if (blnLeavingKaseWarning) {
				if (leaving_case_id != -1 && document.location.hash.indexOf(leaving_case_id) < 0) {
					if (!blnKaseChangesDetected) {
						blnChangingKase = true;
						composeNewNote("open_note_" + leaving_case_id + "_-1");
						blnChangingKase = false;
						$("#myModal4").modal("toggle");
						//return false;
					}
				}
			}
			//this is the only place where blnKaseChangesDetected is reset.  set to true in addForm, updateForm
			blnKaseChangesDetected = false;
		}, 800);
	}
	
	//reset batchscan interval
	check_remote_id = false;
	//intake changes
	$("#content").css("background", "none");
	document.getElementById("content").style.removeProperty("height");
	
	$("#new_kase").css("color", "black");
	$("#intake_kase").css("color", "black");
	
	$("#content").removeClass("fc");
	$("#content").removeClass("fc-touch");
	$("#content").removeClass("fc-ltr");
	$("#content").removeClass("fc-unthemed");
	$("#content").removeClass("glass_header_no_padding");
	
	//if (document.location.pathname.indexOf("v9") > -1 || document.location.pathname.indexOf("v8") > -1 || document.location.pathname.indexOf("v7") > -1) {
		if (!$("#myModal4").hasClass("in")) {
			$("#myModalBody").html("");
		}
		$("#search_document").hide();
		
		if ($("#content").css("margin-top") == "65px") {
			$("#content").css("margin-top", "0px")
		}
		if (!blnSearched) {
			if ($("#content").css("top") == "0px") {
				$("#content").css("top", "60px");
			}
		} else {
			if ($("#content").css("top") == "60px") {
			//	$("#content").css("top", "0px");
			}
		}
		
		if ($("#content").css("top") == "auto") {
			$("#content").css("top", "60px");
		}
		if (document.location.pathname.indexOf("inactive") > -1  || document.location.hash=="#kases" || document.location.hash=="#kasesclosed" || document.location.hash=="#recentkases") {
			$("#content").css("top", "60px");
		}
		
		$(".token-input-dropdown-person").hide();
		$(".token-input-list-facebook").hide();
		$(".token-input-dropdown-facebook").hide();
		$(".token-input-dropdown-task").hide();
		
		$("#search_modifiers").hide();
		$("#page_title").fadeOut();
		
		//calendar reset
		current_calendar_view = "";
		current_calendar_start = "";
		current_calendar_end = "";
	//}
	if (document.location.pathname.indexOf("v9") > -1) {
		if (!kase_searching) {
			if ($("#content").css("top") == "0px") {
				$("#content").css("top", "0px");
			}
		} else {
			if (blnSearched) {
				
					$("#content").css("top", "0px");
					$("#kase_content").css("margin-top", "0px");
				
			}
		}
		if (!blnSearched) {
			if ($("#content").css("top") == "0px") {
				$("#content").css("top", "0px");
			}
		} else {
			if ($("#content").css("top") == "60px") {
				$("#content").css("top", "0px");
			}
		}
	}
	if ($("#srch-term").val()!="") {
		$("#srch-term").val("");
		$("#srch-term").trigger("blur");
	}
	$("#search_results").css("margin-top", "0px");
	
	//make sure we have the right current case id
	if (current_case_id == -1) {
		var hash = document.location.hash;
		if (hash.indexOf("#kases/") > -1 || hash.indexOf("#parties/") > -1 || hash.indexOf("#notes/") > -1 || hash.indexOf("#tasks/") > -1 || hash.indexOf("#documtents/") > -1 || hash.indexOf("#injury/") > -1 || hash.indexOf("#activity/") > -1 || hash.indexOf("#billing/") > -1 || hash.indexOf("#payments/") > -1 || hash.indexOf("#eams_forms/") > -1 || hash.indexOf("#letters/") > -1) {
			var arrHash = hash.split("/");
			current_case_id = arrHash[1];
		}
	}
	
	minimizeBatchscanPanel();
	minimizeKasePreviewPanel();
	closeDocumentPreviewPanel()
	
	//reset level
	url = 'api/levels';
	
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else { // If not
				blnAdmin = (data.level==1);
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			// report error
			console.log(errorThrown);
		}
	});
	
	pingMe();
	
	return true;
}
function minimizeBatchscanPanel() {
	if ($("#batchscan_info_holder").length > 0) {
		if ($("#batchscan_info_holder").css("display")=="block") {
			$("#batchscan_info_holder").css("top", (window.innerHeight - 100) +"px");
			$("#batchscan_info_holder").css("height", "70px");
			$("#batchscan_info_holder").css("opacity", ".2");
		}
	}
}
function minimizeKasePreviewPanel() {
	if ($("#kase_feedback_holder").length > 0) {
		$("#kase_feedback_holder").fadeOut();
	}
}
function closeDocumentPreviewPanel() {
	if (document.getElementById("document_info_holder") != null) {
		$("#document_info_holder").fadeOut();
		$("#document_feedback_text").html("");
	}
}
Array.prototype.unique = function() {
    var unique = [];
    for (var i = 0; i < this.length; i++) {
        if (unique.indexOf(this[i]) == -1) {
            unique.push(this[i]);
        }
    }
    return unique;
};
function inArray(needle, haystack) {
    var length = haystack.length;
    for(var i = 0; i < length; i++) {
        if(haystack[i] == needle) return true;
    }
    return false;
}
function timeSince(date) {

    var seconds = Math.floor((new Date() - date) / 1000);

    var interval = Math.floor(seconds / 31536000);

    var plural = "s";
	if (interval == 1) {
        plural = "";
    }
    if (interval >= 1) {
        return interval + " year" + plural;
    }
    interval = Math.floor(seconds / 2592000);
	plural = "s";
	if (interval == 1) {
        plural = "";
    }
    if (interval >= 1) {
        return interval + " month" + plural;
    }
    interval = Math.floor(seconds / 86400);
	plural = "s";
	if (interval == 1) {
        plural = "";
    }
    if (interval >= 1) {
        return interval + " day" + plural;
    }
    interval = Math.floor(seconds / 3600);
	plural = "s";
	if (interval == 1) {
        plural = "";
    }
    if (interval >= 1) {
        return interval + " hour" + plural;
    }
    interval = Math.floor(seconds / 60);
    if (interval > 1) {
        return interval + " minutes";
    }
    return Math.floor(seconds) + " seconds";
}
var isDate = function(date) {
	var the_return = ( (new Date(date) !== "Invalid Date" && !isNaN(new Date(date)) ) && isNaN(date));
	if (the_return) {
		//check on the year
		var the_date = new Date(date);
		var the_year = the_date.getFullYear();
		if (the_year < 1800) {
			the_return = false;
		}
	}
    return the_return;
}
function formatDollar(amount) {
	var dollar = Number(amount).toLocaleString("us", "currency");
	//decimals
	var arrAmount = dollar.split(".");
	if (arrAmount.length==2) {
		var decimal = arrAmount[1];
		if (decimal.length==1) {
			arrAmount[1] += "0";
		}
	}
	if (arrAmount.length==1) {
		arrAmount.push("00");
	}
	
	return arrAmount.join(".");
}
function assignEmailKase(thread_id, message_id) {
	composeEmailAssign(message_id, "webmail", thread_id);
}
function acceptEmailKase(thread_id, message_id) {
	if ($("#accept_" + thread_id).length > 0) {
		$("#accept_" + thread_id).trigger("click");
		$("#hide_preview_pane").trigger("click");
	}
}
function acceptMessageKase(message_id) {
	if ($("#accept_" + message_id).length > 0) {
		$("#accept_" + message_id).trigger("click");
		$("#hide_preview_pane").trigger("click");
	}
}
function emptyBuffer(customer_id) { 
	var url = 'https://v2.ikase.org/api/buffer';
	if (typeof customer_id != "undefined") {
		 url = 'https://v2.ikase.org/api/buffer?customer_id=' + customer_id;
	}
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else { // If not
				//need notification
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			// report error
			console.log(errorThrown);
		}
	});
}
function emptyReminderBuffer(customer_id) { 
	var url = 'https://v2.ikase.org/api/reminders/buffer?customer_id=' + customer_id;
	
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else { // If not
				//need notification
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			// report error
			console.log(errorThrown);
		}
	});
}
function gridsterById (gridster_id) {
	var gridster = [];
	var widget_base_dimensions = [230, 44];
	
	//special case
	if (gridster_id == "gridster_priors") {
		widget_base_dimensions = [465, 220];
	}
	if (gridster_id.indexOf("gridster_settlement_list")==0 || gridster_id == "gridster_contact") {
		widget_base_dimensions = [300, 44];
	}
	if (gridster_id == "gridster_personal_injury") {
		widget_base_dimensions = [270, 44];
	}
	if (gridster_id == "gridster_parties_cards2") {
		widget_base_dimensions = [230, 44];
		$(function () {
			gridster[1] = $("#gridster_parties_cards2 ul").gridster({
				namespace: '#gridster_parties_cards2',
				widget_margins: [5, 5],
				widget_base_dimensions: [275, 220],
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[1].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						//var serial = gridster[1].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			if (gridster[1]!=null) {
				gridster[1].disable();
			}
			$("#gridster_parties_cards2").fadeIn();
		})
	}
	if (gridster_id == "gridster_accident") {
		widget_base_dimensions = [230, 44];
		$(function () {
			gridster[1] = $("#gridster_accident ul").gridster({
				namespace: "#" + gridster_id,
				widget_margins: [2, 2],
				widget_base_dimensions: widget_base_dimensions,
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						//var serial = gridster[0].serialize();
					}
				}
				}).data('gridster');
			$("#" + gridster_id).fadeIn();
		});
		//alert(gridster_id)
	}
	if (gridster_id == "gridster_accident_details") {
		widget_base_dimensions = [330, 84];
		$(function () {
			gridster[1] = $("#gridster_accident_details ul").gridster({
				namespace: "#" + gridster_id,
				widget_margins: [2, 2],
				widget_base_dimensions: widget_base_dimensions,
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						//var serial = gridster[0].serialize();
					}
				}
				}).data('gridster');
			$("#" + gridster_id).fadeIn();
		});
		//alert(gridster_id)
	}
	if (gridster_id == "gridster_account_selection") {
		widget_base_dimensions = [400, 44];
		$(function () {
			gridster[1] = $("#gridster_account_selection ul").gridster({
				namespace: "#" + gridster_id,
				widget_margins: [2, 2],
				widget_base_dimensions: widget_base_dimensions,
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						//var serial = gridster[0].serialize();
					}
				}
				}).data('gridster');
			$("#" + gridster_id).fadeIn();
		});
		//alert(gridster_id)
	}
	if (gridster_id == "gridster_disability") {
		widget_base_dimensions = [280, 44];
		$(function () {
			gridster[1] = $("#gridster_disability ul").gridster({
				namespace: "#" + gridster_id,
				widget_margins: [2, 2],
				widget_base_dimensions: widget_base_dimensions,
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						//var serial = gridster[0].serialize();
					}
				}
				}).data('gridster');
			$("#" + gridster_id).fadeIn();
		});
		//alert(gridster_id)
	}
	if (gridster_id == "gridster_acc_details") {
		widget_base_dimensions = [162, 44];
		$(function () {
			gridster[1] = $("#gridster_acc_details ul").gridster({
				namespace: "#" + gridster_id,
				widget_margins: [2, 2],
				widget_base_dimensions: widget_base_dimensions,
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						//var serial = gridster[0].serialize();
					}
				}
				}).data('gridster');
			$("#" + gridster_id).fadeIn();
		});
		//alert(gridster_id)
	}
	if (gridster_id == "gridster_acc_info_details") {
		widget_base_dimensions = [162, 44];
		$(function () {
			gridster[1] = $("#gridster_acc_info_details ul").gridster({
				namespace: "#" + gridster_id,
				widget_margins: [2, 2],
				widget_base_dimensions: widget_base_dimensions,
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[1].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						//var serial = gridster[0].serialize();
					}
				}
				}).data('gridster');
			$("#" + gridster_id).fadeIn();
		});
		//alert(gridster_id)
	}
	if (gridster_id == "gridster_financial") {
		widget_base_dimensions = [230, 44];
		$(function () {
			gridster[1] = $("#gridster_financial ul").gridster({
				namespace: "#" + gridster_id,
				widget_margins: [2, 2],
				widget_base_dimensions: widget_base_dimensions,
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						//var serial = gridster[0].serialize();
					}
				}
				}).data('gridster');
			$("#" + gridster_id).fadeIn();
		});
		//alert(gridster_id)
	}
	if (gridster_id == "gridster_bodyparts") {
		widget_base_dimensions = [230, 60];
	}
	if (gridster_id == "gridster_car_passenger") {
		widget_base_dimensions = [465, 220];
	}
	if (gridster_id == "gridster_related_cases") {
		widget_base_dimensions = [465, 450];
	}
	if (gridster_id == "gridster_password") {
		widget_base_dimensions = [250, 100];
	}
	if (gridster_id == "gridster_signature") {
		widget_base_dimensions = [465, 42];
	}
	if (gridster_id == "gridster_vservices_cards") {
		widget_base_dimensions = [430, 224];
		$(function () {
			gridster[0] = $("#" + gridster_id + " ul").gridster({
				namespace: "#" + gridster_id,
				widget_margins: [2, 2],
				widget_base_dimensions: widget_base_dimensions,
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						//var serial = gridster[0].serialize();
					}
				}
				}).data('gridster');
			$("#" + gridster_id).fadeIn();
		});
	}
	if (gridster_id == "gridster_property_damage") {
		widget_base_dimensions = [230, 44];
		$(function () {
			gridster[0] = $("#" + gridster_id + " ul").gridster({
				namespace: "#" + gridster_id,
				widget_margins: [2, 2],
				widget_base_dimensions: widget_base_dimensions,
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						//var serial = gridster[0].serialize();
					}
				}
				}).data('gridster');
			$("#" + gridster_id).fadeIn();
		});
	}
	if (gridster_id != "gridster_property_damage" && gridster_id != "gridster_vservices_cards") {
		$(function () {
			gridster[0] = $("#" + gridster_id + " ul").gridster({
				namespace: "#" + gridster_id,
				widget_margins: [2, 2],
				widget_base_dimensions: widget_base_dimensions,
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						//var serial = gridster[0].serialize();
					}
				}
				}).data('gridster');
			if (gridster[0]!=null) {	
				gridster[0].disable();
			}
			$("#" + gridster_id).fadeIn();
		});
	}
}
function gridsterIt (gridster_index) {
	if (typeof gridster_index == "undefined") {
		gridster_index = -1;
	}
	var gridster = [];
	$(function () {
		//kompany applicant gridster
		if (gridster_index < 0 || gridster_index==0) {
			gridster[0] = $("#gridster_tall ul").gridster({
				namespace: '#gridster_tall',
				widget_margins: [2, 2],
				widget_base_dimensions: [230, 40],
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						var serial = gridster[0].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster_tall").fadeIn();
		}
		//kase gridster header
		if (gridster_index < 0 || gridster_index==1) {
			gridster[1] = $("#gridster_flat ul").gridster({
				namespace: '#gridster_flat',
				widget_margins: [2, 2],
				widget_base_dimensions: [126, 45],
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						var serial = gridster[1].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			
			gridster[1].options.max_rows = 1;
			//$("#gridster_flat ul").css("z-index", "9");
			$("#gridster_flat").fadeIn();
		}
		//kai gridster
		if (gridster_index==2) {
			gridster[2] = $("#gridster_kai ul").gridster({
				namespace: '#gridster_kai',
				widget_margins: [5, 5],
				widget_base_dimensions: [260, 55],
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						var serial = gridster[2].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster_kai").fadeIn();
		}
		//dialog gridster
		if (gridster_index==3) {
			gridster[3] = $("#gridster_dialog ul").gridster({
				namespace: '#gridster_dialog',
				widget_margins: [5, 5],
				widget_base_dimensions: [260, 55],
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						var serial = gridster[3].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster_dialog").fadeIn();
		}
		//parties gridster
		if (gridster_index==4) {
			gridster[4] = $("#gridster_parties ul").gridster({
				namespace: '#gridster_parties',
				widget_margins: [5, 5],
				widget_base_dimensions: [230, 35],
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						var serial = gridster[4].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster_parties").fadeIn();
		}
	
		//notes gridster
		if (gridster_index==5) {
			gridster[5] = $("#gridster_notes ul").gridster({
				namespace: '#gridster_notes',
				widget_margins: [7, 5],
				widget_base_dimensions: [260, 55],
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						var serial = gridster[5].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster_notes").fadeIn();
		}
		
		//parties_new gridster
		if (gridster_index==6) {
			gridster[6] = $("#gridster_parties_new ul").gridster({
				namespace: '#gridster_parties_new',
				widget_margins: [5, 5],
				widget_base_dimensions: [275, 40],
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						var serial = gridster[6].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			if (gridster[6]!=null) {
				gridster[6].disable();
			}
			$("#gridster_parties_new").fadeIn();
		}
		if (gridster_index==61) {
			gridster[61] = $("#gridster_offices_new ul").gridster({
				namespace: '#gridster_offices_new',
				widget_margins: [5, 5],
				widget_base_dimensions: [175, 35],
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						var serial = gridster[61].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster_offices_new").fadeIn();
		}
		if (gridster_index==66) {
			gridster[66] = $("#gridster_parties_contact ul").gridster({
				namespace: '#gridster_parties_contact',
				widget_margins: [5, 5],
				widget_base_dimensions: [225, 75],
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						var serial = gridster[6].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster_parties_contact").fadeIn();
		}
		
		if (gridster_index==67) {
			gridster[67] = $("#gridster_lostincome ul").gridster({
				namespace: '#gridster_lostincome',
				widget_margins: [5, 5],
				widget_base_dimensions: [275, 35],
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						var serial = gridster[6].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster_lostincome").fadeIn();
		}
	
		//parties_cards gridster
		if (gridster_index==7) {
			gridster[7] = $("#gridster_parties_cards ul").gridster({
				namespace: '#gridster_parties_cards',
				widget_margins: [5, 5],
				widget_base_dimensions: [275, 260],
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						var serial = gridster[7].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			if (gridster[7]!=null) {
				gridster[7].disable();
			}
			$("#gridster_parties_cards").fadeIn();
		}
		//user gridster
		if (gridster_index==8) {
			gridster[8] = $("#gridster_user ul").gridster({
				namespace: '#gridster_user',
				widget_margins: [2, 2],
				widget_base_dimensions: [230, 40],
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						var serial = gridster[8].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster_user").fadeIn();
		}
		
		if (gridster_index==81) {
			gridster[81] = $("#gridster_user_details ul").gridster({
				namespace: '#gridster_user_details',
				widget_margins: [2, 2],
				widget_base_dimensions: [230, 40],
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						var serial = gridster[81].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster_user_details").fadeIn();
		}
		//person gridster
		if (gridster_index==9) {
			gridster[9] = $("#gridster_person ul").gridster({
				namespace: '#gridster_person',
				widget_margins: [2, 2],
				widget_base_dimensions: [55, 38],
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						var serial = gridster[8].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			if (gridster[9]!=null) {
				gridster[9].disable();
			}
			$("#gridster_person").fadeIn();
		}
		//accident gridster
		if (gridster_index==10) {
			gridster[10] = $("#gridster_accident ul").gridster({
				namespace: '#gridster_accident',
				widget_margins: [2, 2],
				widget_base_dimensions: [55, 44],
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						var serial = gridster[10].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster_accident").fadeIn();
		}
		
		//accident gridster
		if (gridster_index==11) {
			gridster[11] = $("#gridster_financial ul").gridster({
				namespace: '#gridster_financial',
				widget_margins: [2, 2],
				widget_base_dimensions: [374, 44],
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						var serial = gridster[11].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster_financial").fadeIn();
		}
		//disability gridster
		if (gridster_index==12) {
			gridster[12] = $("#gridster disability ul").gridster({
				namespace: '#gridster_disability',
				widget_margins: [2, 2],
				widget_base_dimensions: [230, 40],
				serialize_params: function($w, wgd) {
					return {
						id: wgd.el[0].id,
						col: wgd.col,
						row: wgd.row
					};
				},
				draggable: {
					stop: function(event, ui){ 
						// your events here
						var serial = gridster[12].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster disability").fadeIn();
		}
	});
		
	$('#content').fadeIn();
}
var original = document.title;
var flash_timeout;

window.flashTitle = function (newMsg, howManyTimes) {
    function step() {
        document.title = (document.title == original) ? newMsg : original;

        if (--howManyTimes > 0) {
            flash_timeout = setTimeout(step, 1500);
        };
    };

    howManyTimes = parseInt(howManyTimes);

    if (isNaN(howManyTimes)) {
        howManyTimes = 5;
    };

    cancelFlashTitle(flash_timeout);
    step();
};

window.cancelFlashTitle = function () {
    clearTimeout(flash_timeout);
    document.title = original;
};
var warning_id;
var hellow_warning_id;
var hello_count = 0;
function flashHello(element_id, on) {
	clearTimeout(hellow_warning_id);
	
	var warning_element = document.getElementById(element_id);
	
	if (warning_element == null) {
		hellow_warning_id = setTimeout(function() {
			flashHello(element_id, true);
		}, 900);
		return;
	}
	var color_off = "#336";
	var color_off_rgb = "rgb(51, 51, 102)";
	var color_on = "#999999";

	if (on) {
		hello_count++;
		if (warning_element.style.background == color_off || warning_element.style.background == color_off_rgb) {
			warning_element.style.background = color_on;
		} else {
			warning_element.style.background = color_off;
			
			if (hello_count > 10) {
				return;
			}
		}
		hellow_warning_id = setTimeout(function() {
			flashHello(element_id, true);
		}, 900);
	} else {
		warning_element.style.background = "#222";
	}
	return;
}
function flashWarning(element_id, on) {
	clearTimeout(warning_id);
	var warning_element = document.getElementById(element_id);
	
	var color_off = "white";
	var color_off_rgb = color_off;
	var color_on = "red";
	if (element_id == "new_message_indicator") {
		color_off = "#06F";
		color_off_rgb = "rgb(0, 102, 255)";
	}
	if (element_id == "new_phone_indicator") {
		color_off = "#3C9";
		color_off_rgb = "rgb(51, 204, 153)";
	}
	if (on) {
		if (warning_element.style.background == color_off || warning_element.style.background == color_off_rgb) {
			warning_element.style.background = color_on;
		} else {
			warning_element.style.background = color_off;
		}
		warning_id = setTimeout(function() {
			flashWarning(element_id, true);
		}, 500);
	}
	return;
}
var showAttachmentPreview = function(form_name, event, filename, case_id, customer_id, message_type) {
	if(typeof message_type == "undefined") {
		message_type = "";
	}
	hidePreview();
	clearTimeout(show_attachment_preview_id);
	
	///$("#" + form_name + "_preview_panel").css("border", "1px solid red");
	//$("#" + form_name + "_preview_panel").fadeIn();
	//return;
	var element = event.currentTarget;
	var arrID = element.id.split("_");
	var note_id = arrID[arrID.length - 1];
	
	if (element!=null) {
		var rect = element.getBoundingClientRect();
		
		var scrollTop = document.documentElement.scrollTop?
						document.documentElement.scrollTop:document.body.scrollTop;
		var scrollLeft = document.documentElement.scrollLeft?                   
						 document.documentElement.scrollLeft:document.body.scrollLeft;
		elementTop = rect.top+scrollTop - 40;
		//elementTop = scrollTop - 20;
		elementLeft = rect.left+scrollLeft - 100;
		if (elementLeft < 0) {
			elementLeft = 30;
		}
		
		if ($("#note_listing").length > 0) {
			elementLeft = 120;
		}
		//elementLeft += 100;
		//console.log("top", rect.top + " + " + scrollTop);
		
		if (message_type.indexOf("email") > -1) {
			var arrFiles = filename.split("|");
			var arrLinks = [];
			arrFiles.forEach(function(element, index, array) { 
				//console.log(element); 
				element = element.trim();
				element = element.replace("https:///uploads", "uploads");
				var uppos = element.indexOf("uploads/" + customer_id);
				if (uppos < 0) {
					var strpos = element.indexOf("attachments");
					if (strpos < 0) {
						//element = "attachments/" + customer_id + "/" + login_user_id + "/" + element;
						//var attach_link = "https://www.ikase.org/uploads/" + customer_id + "/webmail_previews/" + login_user_id + "/" + element;
						var attach_link = "api/preview_attach.php?file=" +  encodeURIComponent(element) + "&case_id=" + case_id;
					} else {
						var attach_link = "https://www.ikase.xyz/ikase/gmail/ui/" + element;
					}
				} else {
					var attach_link = "https://v2.ikase.org/" + element;
				}
				
				var arrLink = element.split("/");
				filename = arrLink[arrLink.length - 1];
				filename = filename.replaceAll("%20", " ")
				filename = filename.replaceAll(" ", "&nbsp;");
				filename = filename.replaceAll("-", "&#8209;");
				
				if (filename!="") {
					var preview_img = "";
					if (message_type.indexOf("2016") > -1 && (filename.indexOf(".pdf") > -1 || filename.indexOf(".jpg") > -1 || filename.indexOf(".png") > -1)) {
						preview_img = " onmouseover='showImportedPreview(this, \"" + attach_link + "\")' onmouseout='hideThumbnailPreview()'";
					}
					panel_html = "<div><a href='" + attach_link + "' target='_blank' class='" + form_name + "_preview_link' style='background:white;color:blue; padding:3px' title='Click to review document'" + preview_img + ">" + filename + "</a></div>";
					arrLinks.push(panel_html);
				}
			});
			
			panel_html = arrLinks.join("");
			
		} else {
			//clean up
			filename = filename.replaceAll("https:///uploads", "../uploads");
			filename = filename.replaceAll("../uploads/", "uploads/");
			filename = filename.replaceAll("https:uploads/", "uploads/");
			if (case_id!="" && case_id!="-1") {
				filename = filename.replaceAll("uploads/" + customer_id + "/" + case_id + "/", "");
			}
			filename = filename.replaceAll("uploads/" + customer_id + "/", "");
			filename = filename.replaceAll(";", "|");
			filename = filename.replaceAll(",", "|");
			var arrFiles = filename.split("|");
			var arrayLength = arrFiles.length;
			var panel_html;
			var arrLinks = [];
			for (var i = 0; i < arrayLength; i++) {
				filename = arrFiles[i];
				var strpos = filename.indexOf("attachments");
				display_filename = filename;
				display_filename = display_filename.replaceAll("%20", "&nbsp;");
				var delete_link = '<div style="float:right">&nbsp;&nbsp;<i style="font-size:15px; color:#FF3737; cursor:pointer" class="glyphicon glyphicon-trash detach_doc" id="detach_' + note_id + '_' + i + '" title="Click to detach this document from the Note"></i></div>';
				
				if (strpos > -1) {
					panel_html = "<div id='note_attachment_holder_" + note_id + "_" + i + "'><a href='https://www.ikase.xyz/ikase/gmail/ui/";
					var arrFileStructure = filename.split("/");
					display_filename = arrFileStructure[arrFileStructure.length - 1];
				} else {
					panel_html = "<div id='note_attachment_holder_" + note_id + "_" + i + "' class='note_attachment_holder'><a href='uploads/preview.php?file=uploads/" + customer_id + "/";
					
					if (case_id!="" && case_id!="-1") {
						panel_html += case_id + "/";
					}
				}
				panel_html += encodeURIComponent(filename) + "' target='_blank' class='" + form_name + "_preview_link' style='background:white;color:blue' id='note_attachment_" + note_id + "_" + i + "'>" + display_filename + "</a>&nbsp;" + delete_link + "</div>";
				
				arrLinks[arrLinks.length] = panel_html;
			};
			panel_html = "<div style='width:auto'>" + arrLinks.join("") + "</div>";
		}
		if (document.location.hash.indexOf("#parties")==0) {
			elementTop -= 180;
		}
		if (document.location.hash.indexOf("#payments")==0) {
			elementTop -= 200;
			elementLeft += 150;
		}
		if (document.location.hash.indexOf("#settlement")==0) {
			elementTop -= 270;
			elementLeft += 130;
		}
		$("#" + form_name + "_preview_panel").html(panel_html);
		$("#" + form_name + "_preview_panel").css({display: "", top: elementTop, left: elementLeft, position:'absolute'});
		$("#" + form_name + "_preview_panel").show();
	}
}
var hideMessagePreview = function() {
	show_attachment_preview_id = setTimeout(function() {
			$(".attach_preview_panel").fadeOut();
	}, 1500);
}
var preview_id = false;
var schedulePreview = function(event, filename, customer_id, thumbnail_folder) {
	clearTimeout(preview_id);
	
	preview_id = setTimeout(function() {
		documentPreview(event, filename, customer_id, thumbnail_folder);
	}, 500);
}
var showPreviewThumbnail = function(event, filename) {
	var element = event.currentTarget;
	if (element!=null) {
		var rect = element.getBoundingClientRect();
		
		var scrollTop = document.documentElement.scrollTop?
						document.documentElement.scrollTop:document.body.scrollTop;
		var scrollLeft = document.documentElement.scrollLeft?                   
						 document.documentElement.scrollLeft:document.body.scrollLeft;
		//elementTop = rect.top+scrollTop - 170;
		elementTop = scrollTop - 10;
		elementLeft = rect.left+scrollLeft - 150;
		if (elementLeft > 500) {
			elementLeft = 350;
		}
		
		filename = filename.replace("/thumbnail/", "/medium/");
		
		//console.log("top", rect.top + " + " + scrollTop);
		$("#preview_panel").html("<img src='" + filename + "' style='border:1px solid black'>");
		$("#preview_panel").css({display: "", top: elementTop, left: elementLeft, position:'absolute'});
		$("#preview_panel").css("margin-left", "250px");
	}
}
var documentPreview = function(event, filename, customer_id, thumbnail_folder, document_type, document_date, parent_document_uuid) {
	if (typeof thumbnail_folder == "undefined") {
		thumbnail_folder = "";
	}
	
	//fix the date if need be
	var arrDateTime = document_date.split(" ");
	var arrDateElements = arrDateTime[0].split("/");
	if (arrDateElements[2].length == 2) {
		arrDateElements[2] = "20" + arrDateElements[2];
	}
	var new_date = arrDateElements.join("/") + " " + arrDateTime[1];
	if (arrDateTime.length == 3) {
		new_date += " " +arrDateTime[2];
	}
	document_date = new_date;
	
	var preview = "uploads/" + customer_id + "/";
	if (thumbnail_folder=="0" || thumbnail_folder.indexOf("pdfimage")==0) {
		preview = "pdfimage/" + customer_id + "/";
		var arrFileName = filename.split(".");
		var extension = arrFileName[arrFileName.length - 1];
		var new_extension = extension;
		if (extension=="pdf" || extension=="PDF" || extension=="tif" || extension=="TIF") {
			new_extension = "jpg";
		}
		arrFileName.pop();
		filename = arrFileName.join(".") + "." + new_extension;
		thumbnail_folder = "";
	}
	if (thumbnail_folder!="") {
		if (filename.indexOf("_") > -1 && thumbnail_folder.indexOf("/") == -1 && isNaN(thumbnail_folder)) {
			//var arrExtension = filename.split(".");
			//var extension = arrExtension[arrExtension.length - 1];
			var arrFileName = filename.split("_");
			first_page = arrFileName[arrFileName.length - 2] - 1;
			//get rid of last 2 members of array
			arrFileName.pop();
			arrFileName.pop();
			filename = arrFileName.join("_") + "_" + first_page + ".png";
		}
		if (thumbnail_folder.indexOf("/") > -1) {
			var arrFileName = filename.split(".");
			var extension = arrFileName[arrFileName.length - 1];
			var new_extension = extension;
			if (extension=="pdf" || extension=="PDF" || extension=="tif" || extension=="TIF") {
				new_extension = "jpg";
			}
			arrFileName.pop();
			filename = arrFileName.join(".") + "." + new_extension;
		}
		
		preview += thumbnail_folder + "/";
		
		//identify batshcanned files
		if (parent_document_uuid!="") {
			if (!isNaN(parent_document_uuid)) {
				//that's the original batchid
				//after jan 27 2017
				//var d1 = new Date(moment(document_date.split(" ")[0]).format("YYYY-MM-DD"));
				var d1 = new Date(document_date);
				var d2 = new Date("2018-01-27");
				
				if (d1 > d2) {
					document_type = "batchscan3";
				}
			}
		}
		
		//batch scans are imported
		if (document_type!="batchscan3") {
			if (!isNaN(thumbnail_folder) && thumbnail_folder!="") {
				var arrFileName = filename.split("_");
				first_page = arrFileName[arrFileName.length - 2];
				
				var d1 = new Date(document_date);
				var d2 = new Date("2018-02-05 08:00 PM");
				
				if (d1 > d2) {
					//one less
					first_page =  Number(first_page) - 1;
				}
				//get rid of last 2 members of array
				arrFileName.pop();
				arrFileName.pop();
				filename = arrFileName.join("_") + "-" + first_page + ".png";
				var preview = "uploads/" + customer_id + "/imports/" + thumbnail_folder + "/";	
			}
		}
		
		if (document_type=="batchscan3") {
			var arrFileName = filename.split("_");
			if (arrFileName.length > 1) {
				first_page = arrFileName[arrFileName.length - 2];
				//var d1 = new Date(moment(document_date).format("YYYY-MM-DD"));
				var d1 = new Date(document_date);
				var d2 = new Date("2018-02-05 08:00 PM");
				
				if (d1 > d2) {
					//one less
					first_page =  Number(first_page) - 1;
				}
				//get rid of last 2 members of array
				arrFileName.pop();
				arrFileName.pop();
				filename = arrFileName.join("_") + "-" + first_page + ".png";
			}
			preview = "scans/" + customer_id + "/" + moment(document_date.split(" ")[0]).format("YYYYMMDD") + "/";
			filename = filename.replace(".png", ".jpg");
		}
	}
	preview += filename;
	
	//it has to jpg/png
	var arrPreview = preview.split(".");
	var extension = arrPreview[arrPreview.length - 1];
	extension = extension.toLowerCase();
	if (extension!="jpg" && extension!="png") {
		preview = "img/no_preview.gif";
	}
	var element = event.currentTarget;
	if (element!=null) {
		var rect = element.getBoundingClientRect();
		
		var scrollTop = document.documentElement.scrollTop?
						document.documentElement.scrollTop:document.body.scrollTop;
		var scrollLeft = document.documentElement.scrollLeft?                   
						 document.documentElement.scrollLeft:document.body.scrollLeft;
		//elementTop = rect.top+scrollTop - 170;
		elementTop = scrollTop - 10;
		elementLeft = rect.left+scrollLeft - 150;
		if (elementLeft > 500) {
			elementLeft = 350;
		}
		//console.log("top", rect.top + " + " + scrollTop);
		$("#preview_panel").html("<img src='" + preview + "' style='border:1px solid black'>");
		$("#preview_panel").css({display: "", top: elementTop, left: elementLeft, position:'absolute'});
		$("#preview_panel").css("margin-left", "250px");
	}
}
var showImportedPreview = function(element, src, u1, u2, customer_id, qualifier) {
	if (typeof qualifier == "undefined") {
		qualifier = "";
	}
	//clear the thumbnail fadeout, so there is no flicker if they are scrolling down list of images
	clearTimeout(thumbnail_timeout_id);
	var preview = src;

	if (element!=null) {
		var rect = element.getBoundingClientRect();
		
		var scrollTop = document.documentElement.scrollTop?
						document.documentElement.scrollTop:document.body.scrollTop;
		var scrollLeft = document.documentElement.scrollLeft?                   
						 document.documentElement.scrollLeft:document.body.scrollLeft;
		//elementTop = rect.top+scrollTop - 170;
		//elementTop = 70;
		elementTop = scrollTop - 20;
		elementLeft = rect.left+scrollLeft - 100;
		if (elementLeft > 500) {
			elementLeft = 350;
		}
		
		//specific
		var specific = "";
		if (preview.indexOf("webmail_previews") > -1) {
			specific = "threadimage_";
			elementLeft = rect.left+scrollLeft - 10;
			if (elementLeft > 500) {
				elementLeft = 400;
			}
		}
		
		if (elementLeft < 0) {
			elementLeft = 20;
		}
		if (elementTop < 0) {
			elementTop = 80;
		}
		//preview from panel
		if (element.className == "kase_attach_preview_link") {
			elementLeft = 20;
			elementTop = 80;
		}
		if (qualifier=="activity_") {
			elementLeft += 120;
		}
		$("#" + specific + "preview_panel").html("<img src='" + preview.replace(".pdf", ".jpg") + "' style='border:1px solid black'>");
		$("#" + specific + "preview_panel").css({top: elementTop, left: elementLeft, position:'absolute'});
		$("#" + specific + "preview_panel").css("margin-left", "250px");
		$("#" + specific + "preview_panel").fadeIn();
	}
}
function imageExists(image_url){

    var http = new XMLHttpRequest();

    http.open('HEAD', image_url, false);
    http.send();

    return http.status != 404;

}
var documentThumbnail = function(filename, customer_id, thumbnail_folder, case_id, document_type, document_date, parent_document_uuid) {
	if (typeof thumbnail_folder == "undefined") {
		thumbnail_folder = "";
	}
	if (typeof case_id == "undefined") {
		case_id = "";
	}
	var preview = "uploads/" + customer_id + "/";
	if (case_id!="" && thumbnail_folder=="") {
		preview += case_id + "/";
	}
	//clean up
	filename = filename.replace(".PDF.pdf", ".pdf")
		
	//fix the date if need be
	var arrDateTime = document_date.split(" ");
	var arrDateElements = arrDateTime[0].split("/");
	if (arrDateElements[2].length == 2) {
		arrDateElements[2] = "20" + arrDateElements[2];
	}
	var new_date = arrDateElements.join("/") + " " + arrDateTime[1];
	if (arrDateTime.length == 3) {
		new_date += " " + arrDateTime[2];
	}
	document_date = new_date;
	var blnExists = false;
	if (thumbnail_folder=="0" || thumbnail_folder.indexOf("pdfimage")==0) {
		preview = "pdfimage/" + customer_id + "/";
		var arrFileName = filename.split(".");
		var extension = arrFileName[arrFileName.length - 1];
		var new_extension = extension;
		if (extension=="pdf" || extension=="PDF" || extension=="tif" || extension=="TIF") {
			new_extension = "jpg";
		}
		arrFileName.pop();
		filename = arrFileName.join(".") + "." + new_extension;
		thumbnail_folder = "";
		
		blnExists = (imageExists(preview + filename));
	}
	
	if (!blnExists) {
		//identify batshcanned files
		if (parent_document_uuid!="") {
			if (!isNaN(parent_document_uuid)) {
				//that's the original batchid
				//var d1 = new Date(moment(document_date.split(" ")[0]).format("YYYY-MM-DD"));
				var d1 = new Date(document_date);
				var d2 = new Date("2018-01-27");
				
				if (d1 > d2) {
					document_type = "batchscan3";
				}
			}
		}
		if (filename.indexOf("scans\\") > -1 || document_type=="batchscan3") {
			//new batchscans
			var arrPath = filename.split("\\");
			var filename = arrPath[arrPath.length - 1];
			
			var arrFileName = filename.split("_");
			first_page = arrFileName[arrFileName.length - 2];
			//var d1 = new Date(moment(document_date).format("YYYY-MM-DD"));
			var d1 = new Date(document_date);
			var d2 = new Date("2018-02-05 08:00 PM");
			
			if (d1 > d2) {
				//one less
				first_page =  Number(first_page) - 1;
			}
			//get rid of last 2 members of array
			arrFileName.pop();
			arrFileName.pop();
			filename = arrFileName.join("_") + "-" + first_page + ".jpg";
			if (document_type=="batchscan3") {
				preview = "scans/" + customer_id + "/" + moment(document_date.split(" ")[0]).format("YYYYMMDD") + "/";
			} else {
				preview = "scans/" + customer_id + "/" + thumbnail_folder + "/";
			}
		} else {
			if (thumbnail_folder!="" && thumbnail_folder!=null) {
				if (filename.indexOf("_") > -1 && thumbnail_folder.indexOf("/") == -1 && isNaN(thumbnail_folder)) {
					//var arrExtension = filename.split(".");
					//var extension = arrExtension[arrExtension.length - 1];
					var arrFileName = filename.split("_");
					first_page = arrFileName[arrFileName.length - 2] - 1;
					//get rid of last 2 members of array
					arrFileName.pop();
					arrFileName.pop();
					filename = arrFileName.join("_") + "_" + first_page + ".png";
				}
				if (thumbnail_folder.indexOf("/") > -1) {
					var arrFileName = filename.split(".");
					var extension = arrFileName[arrFileName.length - 1];
					var new_extension = extension;
					if (extension=="pdf" || extension=="PDF" || extension=="tif" || extension=="TIF") {
						new_extension = "jpg";
					}
					arrFileName.pop();
					filename = arrFileName.join(".") + "." + new_extension;
				}
				preview += thumbnail_folder + "/";
				//batch scans are imported
				if (!isNaN(thumbnail_folder)) {
					var arrFileName = filename.split("_");
					first_page = arrFileName[arrFileName.length - 2];
					//get rid of last 2 members of array
					arrFileName.pop();
					arrFileName.pop();
					filename = arrFileName.join("_") + "-" + first_page + ".png";
					var preview = "uploads/" + customer_id + "/imports/" + thumbnail_folder + "/";	
				}
			}
		}
	}
	preview = preview.replace("medium/", "thumbnail/");
	preview += filename;
	
	if (preview.indexOf("eams_app") > -1) {
			preview = "uploads/" + customer_id + "/" + case_id + "/jetfiler/" + filename;	
	}
	if (preview.indexOf(".jpg") < 0 && preview.indexOf(".png") < 0) {
		//try just the thumbnail folder
		var new_preview = preview.replace("/" + case_id, "/" + case_id + "/thumbnail").replace(".pdf", ".jpg");
		if (imageExists(new_preview)) {
			preview = new_preview;
		}
	}
	
	//it has to jpg/png
	var arrPreview = preview.split(".");
	var extension = arrPreview[arrPreview.length - 1];
	extension = extension.toLowerCase();
	var blnExists = (imageExists(preview));
	
	if (!blnExists && (extension=="jpg" || extension=="png")) {
		//default
		preview = "img/no_preview.gif";
		//it could be that an actual image was uploaded to the main customer folder
		var new_preview = "uploads/" + customer_id + "/" + filename;	
		var blnExists = (imageExists(new_preview));
		if (blnExists) {
			preview = new_preview;
		}
	}
	if ((extension!="jpg" && extension!="png") || !blnExists) {
		preview = "img/no_preview.gif";
	}
	return preview;
}
var closeDocument = function() {
	$("#view_document").css({display: "none"});
}
var showDocument = function(filepath) {
	hidePreview();
	window.open('../templates/preview.php?file=<? echo $_GET["case_id"]; ?>/' + filepath, 'Preview')
}
var showCloudArchive = function(filepath) {
	hidePreview();
	filepath = filepath.replace("+", "_");
	window.open('http://kustomweb.xyz/cloud/archive.php?path=' + filepath, 'Preview');
}
var thumbnail_timeout_id = false;
var hideThumbnailPreview = function() {
	thumbnail_timeout_id = setTimeout(function() {
		$("#threadimage_preview_panel").fadeOut();
	}, 700);
}
var hidePreview = function() {
	$("#preview_panel").css({display: "none"});
	$(".attach_preview_panel").css({display: "none"});
}
var showPreview = function(event, filename, time_stamp, pages, customer_id, preview_panel) {
	if (typeof preview_panel == "undefined") {
		preview_panel = "";
	}
	var first_page = pages - 1;
	if (pages.indexOf("-") > -1) {
		first_page = pages.split("-")[0] - 1;
	}
	if (time_stamp!="") {
		var preview = "uploads/" + customer_id + "/" + time_stamp + "/" + filename + "_" + first_page + ".png";
	} else {
		var preview = "uploads/" + customer_id + "/thumbnails/" + filename + "_" + first_page + ".png";
		/*
		if (first_page != "-1") {
			var preview = "uploads/" + customer_id + "/thumbnail/" + filename + "_" + first_page + ".png";
		} else {
			var preview = "uploads/" + customer_id + "/thumbnail/" + filename.replace(".pdf", ".jpg");
		}
		*/
	}
	//console.log(preview);
	
	var element = event.currentTarget;
	if (element!=null) {
		var rect = element.getBoundingClientRect();
		
		var scrollTop = document.documentElement.scrollTop?
						document.documentElement.scrollTop:document.body.scrollTop;
		var scrollLeft = document.documentElement.scrollLeft?                   
						 document.documentElement.scrollLeft:document.body.scrollLeft;
		elementTop = rect.top+scrollTop - 170;
		//elementTop = 70;
		elementLeft = rect.left+scrollLeft + 50;
		if (elementLeft > 500) {
			elementLeft = 350;
		}
		
		$("#" + preview_panel + "preview_panel").html("<img src='" + preview + "' style='border:1px solid black'>");
		$("#" + preview_panel + "preview_panel").css({display: "", top: elementTop, left: elementLeft, position:'absolute'});
	}
}
var documentsUploaded = function(case_id) {
	document.location.href = "#documents/" + case_id;
}
var openDocumentDetails = function(document_uuid) {
	$("#expand_document_" + document_uuid).fadeOut(function() {
		$("#shrink_document_" + document_uuid).fadeIn();
	});
	$("#document_link_" + document_uuid).fadeOut(function() {
		$("#description_" + document_uuid).fadeIn();
	});
	$("#document_details_" + document_uuid).fadeIn();
}
var closeDocumentDetails = function(document_uuid) {
	$("#shrink_document_" + document_uuid).fadeOut(function() {
		$("#expand_document_" + document_uuid).fadeIn();
	});
	$("#document_link_" + document_uuid).fadeIn(function() {
		$("#description_" + document_uuid).fadeOut();
	});
	$("#document_details_" + document_uuid).fadeOut();
}
function fillIFrame(iframe_id, html) {
	var doc = document.getElementById(iframe_id).contentWindow.document;
	doc.open();
	doc.write('<html<body>' + html + '</body></html>');
	doc.close();
}
function limitText(limitField, limitNum) {
	var field_value = limitField.value;
	var statement_length = $("#" + limitField.id.replace("Input", "_length"));
	statement_length.html(field_value.length);
	
	if (field_value.length > limitNum) {
		//limitField.value = limitField.value.substring(0, limitNum);
		statement_length.html("<span style='background:white; color:red; font-weight:bold'>" + field_value.length + "</span>");
	}
}
function clearSearchResults() {
	$("#search_modifiers").fadeOut();
	$("#search_results").html("");
	$("#search_open_cases").prop("checked", true);
	blnSearchingKases = false;
}
var filterCount = 0;
function filterKases(filter_type) {
	var theworker = $("#kases_worker_filter");
	var val_worker = $.trim($(theworker).val()).replace(/ +/g, ' ').toLowerCase();
	
	var theattorney = $("#kases_attorney_filter");
	var val_attorney = $.trim($(theattorney).val()).replace(/ +/g, ' ').toLowerCase();
	
	//update the session object with latest kase filters for printing purposes
	var url = 'api/kases/filters';
	formValues = "val_attorney=" + val_attorney + "&val_worker=" + val_worker;
	if (val_worker=="") {
		$(".letter_row").show();
	} else {
		$(".letter_row").hide();
	}
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else { // If not
				//need notification
				//console.log(data.attorney + " - " + data.worker);
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			// report error
			console.log(errorThrown);
		}
	});
	
	if (val_worker!="" && val_attorney!="") {
		filterListKases();
		return;
	}
	filterCount = 0;
	var $rows = $('.kase_data_row');
	var the_kind = "kases_" + filter_type + "_filter";
	var theobj = $("#kases_" + filter_type + "_filter");
	var val = $.trim($(theobj).val()).replace(/ +/g, ' ').toLowerCase();
	$rows.show().filter(function() {
		//var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
		var text = $( '.' + filter_type + '_name', $( this ) ).text ().replace(/\s+/g, ' ').toLowerCase();
		
		if (text.indexOf(val) > -1) {
			filterCount++;
		}
		return !~text.indexOf(val);
	}).hide();
	
	$("#active_kase_count").html("(" + filterCount + ")");
	if (filter_type=="worker" && val_attorney!="") {
		//can only be here if worker is empty
		filterKases("attorney");
	}
	if (filter_type=="attorney" && val_worker!="") {
		//can only be here if attorney is empty
		filterKases("worker");
	}
}
function filterListKases() {
	var $rows = $('.kase_data_row');
	var theworker = $("#kases_worker_filter");
	var val_worker = $.trim($(theworker).val()).replace(/ +/g, ' ').toLowerCase();
	
	var theattorney = $("#kases_attorney_filter");
	var val_attorney = $.trim($(theattorney).val()).replace(/ +/g, ' ').toLowerCase();
	filterCount = 0;
	$rows.show().filter(function() {
		//var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
		var worker_text = $( '.worker_name', $( this ) ).text ().replace(/\s+/g, ' ').toLowerCase();
		var attorney_text = $( '.attorney_name', $( this ) ).text ().replace(/\s+/g, ' ').toLowerCase();
		
		if (worker_text.indexOf(val_worker) > -1 && attorney_text.indexOf(val_attorney) > -1) {
			filterCount++;
		}
		return !~worker_text.indexOf(val_worker) || !~attorney_text.indexOf(val_attorney);
	}).hide();
	
	$("#active_kase_count").html("(" + filterCount + ")");
}
function reportActivity(href) {
	document.location.href = href;
}
function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
function postForm(path, params, method, target) {
	/*
	params = {"doctor_ids": doctor_ids, "bodyparts": bodyparts};
	postForm(path, params, "post", "_blank");
	*/
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
function saveDraftObject(obj) {
	//document.getElementById("apply_tasks").disabled = obj.checked;
	$("#apply_tasks").prop("checked", false);
	$("#apply_notes").prop("checked", false);
	
	$("#apply_tasks").prop("disabled", obj.checked);
	$("#apply_notes").prop("disabled", obj.checked);
}
function replyMessage(element_id) {
	$("#myModal4").modal("toggle");
	composeMessage(element_id);
}
function printEams() {
	var html = $(".modal-content").html();
	//clean up
	html = html.replace('class="close"', 'class="close" style="display:none"');
	html = html.replace('<head></head>', '<head><title>EAMS ADJ Lookup Report</title><style>td { font-size:0.8em;}</style></head>');
	html = html.replace('id="previouscases_scrape_holder" style="background:orange;', 'id="previouscases_scrape_holder" style="display:none;background:orange;');
	html = html.replace('id="page_header" style="display:none', 'id="page_header" style="display:');
	htmlWindow(html);
}
function htmlWindow(html) {
	var w = window.open();
	
	$(w.document.body).html(html);
}
function iframeRef( frameRef ) {
    return frameRef.contentWindow
        ? frameRef.contentWindow.document
        : frameRef.contentDocument
}
function validateForm(form_name) {
	var blnValid = true;
	var form_inputs = $("#" + form_name + "_form .input_class");
	for (var i = 0; i < form_inputs.length; i++) {
		var form_input = form_inputs[i];
		if (form_input.tagName=="INPUT" || form_input.tagName=="SELECT") {
			if (form_input.required) {
			//if ($("#" + form_input.id).prop("required")) {
				var border = form_input.style.border;
				if (form_input.value=="") {
					blnValid = false;					
					border = "2px solid red";
					form_input.style.border = border;
					//make sure it's visible...
					form_input.style.display = "";
					break;
				}
			}
		}
	}
	return blnValid;
}
function markRequiredRed(form_name) {
	var arrDom = $("#" + form_name + "_form .input_class");
	var arrLength = arrDom.length;
	for (var i = 0; i < arrLength; i++) {
		var element = arrDom[i];
		border = element.style.border;
		blnRed = false;
		if (element.nodeName=="INPUT" || element.nodeName=="SELECT") {
			if (element.required && element.value=="") {
				border = "2px solid red";
				blnRed = true;
			}
			element.style.border = border;
			
			if (blnRed) {
				//however, it could be a token input
				var element_parentNode_id = element.parentNode.id;
				if (element_parentNode_id!="") {
					var arrChildren = document.getElementById(element_parentNode_id).children;
					var arrChildLength = arrChildren.length;
					for (var j = 0; i < arrChildLength; j++) {
						var child_element = arrChildren[j];
						if (typeof child_element != "undefined") {
							if (child_element.nodeName=="UL") {
								child_element.style.border = border;
								//only do it once
								break;
							}
						}
					}
				}
			}
		}
	}
}
var scrollTable = function (event) {
	//return;
	if (customer_id != 1049) {
		return;
	}
	if ($("#kase_listing").length == 0) {
		return;
	}
	if ($("#loading_row_top").length > 0) {
		return;
	}
	if (kase_pages < 1 || listed_kases < kases_limit) {
		return;
	}
	if ($("#kase_listing tr").length < 10) {
		return;
	}
	
	var arrPageLinks = [];
	for(var i = 0; i < (kase_pages + 1); i++) {
		arrPageLinks.push("<a href='javascript:showKasePage(" + i + ")' style='background:white; color:black; padding:2px; text-decoration:none'>" + (i + 1) + "</a>");
	}
	var next_previous_link = "<tr id='loading_row_top'><td colspan='13'><span style='color:white'>Pages:</span>&nbsp;" + arrPageLinks.join("&nbsp;|&nbsp;") + "&nbsp;|&nbsp;<a href='javascript:showPreviousPage()' style='color:white; text-decoration:none'><<</a>&nbsp;&nbsp;<a href='javascript:showNextPage()' style='color:white; text-decoration:none'>>></a></td></tr>";
	$("#kase_listing").prepend(next_previous_link);
	next_previous_link = next_previous_link.replace("loading_row_top", "loading_row");
	$("#kase_listing").append(next_previous_link);
	return;
	
	var previous_link = "";
	if (typeof start_kases == "undefined") {
		start_kases = kases_limit;
	}
	previous_kases = start_kases - kases_limit - 1;
	
	if (current_page > 0) {
		previous_link = "<a href='javascript:showPreviousPage()' style='color:white; text-decoration:none'>Previous Page (" + (current_page) + ")</a>&nbsp;|&nbsp;";
	} else {
		start_kases = kases_limit;
	}
	
	if ($("#loading_row").length > 0) {
		return;
	}
	if ($("#kase_listing").length > 0 && listed_kases >= kases_limit) {
		
			
		var next_previous_link = "<tr id='loading_row_top'><td colspan='13'>" + previous_link + "<a href='javascript:showNextPage()' style='color:white; text-decoration:none'>Next Page (" + (current_page + 1) + ")</a></td></tr>";
		
		if ($("#loading_row_top").length == 0) {
			$("#kase_listing").prepend(next_previous_link);
		}
		
		if (blnGettingMore) {
			return;
		}
		if ($("#loading_row").length == 0) {
			var doc_height = $(document).height(); 
			var scroll = $(window).scrollTop();
			
			if (scroll > (doc_height * .95)) {
				blnGettingMore = true;
				next_previous_link = next_previous_link.replace("loading_row_top", "loading_row");
				$("#kase_listing").append(next_previous_link);
				
				blnGettingMore = false;
			}
		}
	}
	// Do something
	//console.log("scroll:" + scroll);
}
var blnLookingForKases = false;
function showKasePage(page_number) {
	//maybe we have it in memory already
	if (typeof arrKasePage[page_number] != "undefined") {
		$('#content').html(arrKasePage[page_number]);
		//that's all we need
		return;
	}
	$('#content').html(loading_image);
	if (page_number == 0) {
		var prev_kases = kases;
		var mymodel = new Backbone.Model();
		mymodel.set("key", "");
		$('#content').html(new kase_listing_view({collection: kases, model:mymodel}).render().el);
		
		var url = "api/currentkases/-1";
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			data: "",
			success:function (data) {
					//console.log(data.success);
				}
			}
		);
	} else {
		var prev_kases = new KasePageCollection({start: (page_number * kases_limit)});
		prev_kases.fetch({
			success: function (prev_kases) {
				if (listed_kases > 0) {
					var mymodel = new Backbone.Model();
					mymodel.set("key", "");
					$('#content').html(new kase_listing_view({collection: prev_kases, model:mymodel}).render().el);
					//cache the result
					setTimeout(function() {
						if (typeof arrKasePage[page_number] == "undefined") {
							arrKasePage[page_number] = $('#content').html();
						}
					}, 1007);
				}
			}
		});
	}
}
function showPreviousPage() {
	current_page--;
	if (current_page < 0) {
		current_page = 0;
	}
	
	//maybe we have it in memory already
	if (typeof arrKasePage[current_page] != "undefined") {
		$('#content').html(arrKasePage[current_page]);
		//that's all we need
		return;
	}
	$('#content').html(loading_image);
	if (current_page == 0) {
		var prev_kases = kases;
		var mymodel = new Backbone.Model();
		mymodel.set("key", "");
		$('#content').html(new kase_listing_view({collection: kases, model:mymodel}).render().el);
		
		var url = "api/currentkases/-1";
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			data: "",
			success:function (data) {
					//console.log(data.success);
				}
			}
		);
	} else {
		var url = "api/currentkases/" + (current_page * kases_limit);
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			data: "",
			success:function (data) {
					var prev_kases = new KasePreviousCollection();
					prev_kases.fetch({
						success: function (prev_kases) {
							if (listed_kases > 0) {
								var mymodel = new Backbone.Model();
								mymodel.set("key", "");
								$('#content').html(new kase_listing_view({collection: prev_kases, model:mymodel}).render().el);
								//cache the result
								setTimeout(function() {
									if (typeof arrKasePage[current_page] == "undefined") {
										arrKasePage[current_page] = $('#content').html();
									}
								}, 1007);
							}
						}
					});
				}
			}
		);
	}
}
function showNextPage() {
	current_page++;
	//maybe we have it in memory already
	if (typeof arrKasePage[current_page] != "undefined") {
		$('#content').html(arrKasePage[current_page]);
		//that's all we need
		return;
	}
	$('#content').html(loading_image);
	var url = "api/currentkases/" + (current_page * kases_limit);
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			data: "",
			success:function (data) {
				var next_kases = new KaseNextCollection();
				next_kases.fetch({
					success: function (next_kases) {
						if (listed_kases > 0) {
							var mymodel = new Backbone.Model();
							mymodel.set("key", "");
							//mymodel.set("additional_rows", true);
							//mymodel.set("sort_by", "last_name");
							//$("#wrap").append("<div id='additional_rows' style='display:none'>nick</div>")
							//increment the new start point
							//start_kases = Number(start_kases) + Number(kases_limit) + 1;
							$('#content').html(new kase_listing_view({collection: next_kases, model:mymodel}).render().el);
							//cache the result
							setTimeout(function() {
								if (typeof arrKasePage[current_page] == "undefined") {
									arrKasePage[current_page] = $('#content').html();
								}
							}, 1007);
						}
					}
				});
			}
		});
	/*
	var additional_rows = $('#additional_rows').html();
	$("#additional_rows").html("");
	additional_rows = additional_rows.substr(0, additional_rows.length - 6);
	additional_rows = additional_rows.substr(5);
	if (additional_rows!="") {
		$("#kase_listing").append(additional_rows);
		$("#loading_row").remove();
	} else {
		setTimeout(function() {
			showNextPage();
		}, 1000);
	}
	*/
}
var search_timeoutid = false;
//mapping solution
function lightenMe(obj) {
	obj.style.background = "#FFF";
}
function darkenMe(obj) {
	obj.style.background = "#EDEDED";
}
function addressClick(obj, className) {
	var element = obj;
	var arrElement = element.id.split("_");
	var place_id = arrElement[arrElement.length - 1];
	
	var data = $("#data_" + place_id).val();
	var jdata = JSON.parse(data);
	
	$(".address_fields").val("");
	var sublocality = "";
	var administrative_area_level_1 = "";
	var postal_code = "";
	for (var i = 0; i < jdata.length; i++) {
		var addressType = jdata[i].types[0];
		//might be sublocality_level_1
		if (jdata[i].types.length > 1) {
			if (addressType == "sublocality_level_1" && jdata[i].types[1]=="sublocality") {
				addressType = "sublocality";
			}
		}
		if (typeof jdata[i].long_name == "string") {
		  var val = jdata[i].long_name;
		   if (addressType=="administrative_area_level_1") {
				val = jdata[i].short_name;
				administrative_area_level_1 = val;  
		  }
		  document.getElementById(addressType + "_" + className).value = val;
		  
		  if (addressType=="neighborhood") {
			sublocality = val;  
			document.getElementById("city_" + className).value = sublocality;
		  }	  
		  if (addressType=="sublocality") {
			sublocality = val;  
			document.getElementById("city_" + className).value = sublocality;
		  }
		 
		  if (addressType=="postal_code") {
			postal_code = val;  
		  }
		}
	}
	if (sublocality=="") {
		sublocality = document.getElementById("locality_" + className).value;
	}
	var text_box = $("." + className + " #full_addressInput");
	var arrAddress = [];
	if (document.getElementById("street_number_" + className).value!="") {
		arrAddress[arrAddress.length] = document.getElementById("street_number_" + className).value + " " + document.getElementById("route_" + className).value;
		if (sublocality!="") {
			arrAddress[arrAddress.length] = sublocality;
		}
		arrAddress[arrAddress.length] = document.getElementById("administrative_area_level_1_" + className).value + " " + document.getElementById("postal_code_" + className).value;
		
	} else {
		sublocality = jdata[0].long_name + ", " + jdata[1].long_name; 
		arrAddress[arrAddress.length] = sublocality;
		arrAddress[arrAddress.length] = administrative_area_level_1;
	}
	
	var full_address = arrAddress.join(", ");
	//text_box.value = full_address;
	text_box.val(full_address);
	
	document.getElementById("city_" + className).value = sublocality;
	document.getElementById("street_" + className).value = document.getElementById("street_number_" + className).value + " " + document.getElementById("route_" + className).value;
	
	document.getElementById('map_results_holder').innerHTML = "";
	document.getElementById('map_results_holder').style.display = "none";
	
	$("#address_fields_holder").fadeIn();
}
function hideResults() {
	setTimeout(function() {
		//we need a slight delay to allow for processing of info before hiding
		$('#map_results_holder').hide();
	}, 300);
}
function lookupPIBingMaps(event) {
	var className = "personal_injury";
	var element = event.currentTarget;
	var address = element.value;
	var data_output = "bing_results_1";
	if (element.id=="personal_injury2_locationInput") {
		data_output = "bing_results_2";
	}
	lookupBingMaps(className, data_output, address);
}
function lookupBingMaps (className, data_output, address) {
	if (typeof data_output == "undefined") {
		data_output = "bing_results";
	}
	if (typeof className == "undefined") {
		className = "";
	}
	clearTimeout(search_bing_id);
	
	if (className.indexOf("#") < 0) {
		className = "." + className;
	}
	
	$(className + " #manual_address").html('<i class="icon-spin4 animate-spin" style="font-size:2em; color:white"></i>');
	
	if (typeof address == "undefined") {
		var address = $(className + " #full_addressInput").val();
	}
	if (address.length < 3) {
		$(className + " #manual_address").html("no&nbsp;lookup");
		return;
	}
	
	search_bing_id = setTimeout(function() {
		
		var url = "../api/bing/search";
		var formValues = "search=" + encodeURIComponent(address);
		
		$.ajax({
			url:url,
			type:'POST',
			data: formValues,
			dataType:"json",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					var arrLength = data.length;
					if (arrLength > 0) {
						var arrRows = [];
						for(var i = 0; i < arrLength; i++) {
							var datum = data[i];
							var row = "<div id='bing_address_name_" + i + "' class='bing_address' style='cursor:pointer; text-decoration:underline; color:blue'>" + datum.name + "</div><textarea id='bing_address_data_" + i + "'style='display:none'>" + JSON.stringify(datum.address) + "</textarea>";
							arrRows.push(row);
						}
						$(className + " #" + data_output).html(arrRows.join(""));
						$(className + " #" + data_output).show();
						
						$(className + " #manual_address").html("no&nbsp;lookup");
					}
				}
			}
		});
	}, 1500);
}
function selectBingAddress(event, partie, data_output) {
	if (typeof data_output == "undefined") {
		data_output = "bing_results";
	}
	var element = event.currentTarget;
	var address_id = element.id.replace("bing_address_name_", "");
	var bing_address_data = $("#bing_address_data_" + address_id).val();
	if (bing_address_data!="") {
		var jdata = JSON.parse(bing_address_data);
		
		$("#street_" + partie).val(jdata.addressLine);
		$("#city_" + partie).val(jdata.locality);
		$("#administrative_area_level_1_" + partie).val(jdata.adminDistrict);
		$("#postal_code_" + partie).val(jdata.postalCode);
		
		$("#" + partie + "_form #full_addressInput").val(jdata.formattedAddress);
	}
	
	$("#" + partie + "_form #" + data_output).hide();
	$("#" + partie + "_form #" + data_output).html("");
	
	if (document.location.hash=="#intake") {
		$("#" + partie + "_form #full_addressInput").trigger("blur")
	}
}
function searchAddress(className) {
	clearTimeout(search_timeoutid);
	hideResults();
	
	document.getElementById('map_results_holder').innerHTML = "";
	var full_address = $("." + className + " #full_addressInput").val();
	
	if (full_address=="") {
		return;
	}
	search_timeoutid = setTimeout(function() {
		if (!blnBingSearch) {
			getGoogleAddresses(className);
		} else {
			//selectBingAddress(event, className, "map_results_holder");
			lookupBingMaps(className, "map_results_holder");
		}
	}, 600);
}
// This example retrieves autocomplete predictions programmatically from the
// autocomplete service, and displays them as an HTML list.

// This example requires the Places library. Include the libraries=places
// parameter when you first load the API. For example:
// <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places">

function getGoogleAddresses(className) {
	var displaySuggestions = function(predictions, status) {
	  if (status != google.maps.places.PlacesServiceStatus.OK) {
		//alert(status);
		document.getElementById('map_results_holder').style.display = "";
		document.getElementById('map_results_holder').innerHTML = "Nothing found";
		return;
	  }
		
		var map = new google.maps.Map(document.getElementById('map'));
		service = new google.maps.places.PlacesService(map);
			
	  predictions.forEach(function(prediction) {
		  if (typeof prediction.place_id != "undefined") {					  
			  //let's get some details
			  var request = {
				  placeId: prediction.place_id
				};
			
				
				service.getDetails(request, callback);
				
				function callback(place, status) {
				  if (status == google.maps.places.PlacesServiceStatus.OK) {
					var full_address = $("." + className + " #full_addressInput").val();
					full_address = full_address.capitalizeWords();
					$('#map_results_holder').append("<div><a id='place_id_" + place.id + "' class='address_place' style='cursor:pointer' onclick='addressClick(this, \"person\")' onmouseover='darkenMe(this)' onmouseout='lightenMe(this)'>" + place.formatted_address.replace(full_address, "<span style='font-weight:bold'>" + full_address + "</span>") + "</a><textarea id='data_" + place.id + "' style='display:none'>" + JSON.stringify(place.address_components) + "</textarea></div>");
					document.getElementById('map_results_holder').style.display = "";
				  }
				}
			}
		});
	};
	var full_address = $("." + className + " #full_addressInput").val();
	var request = {
		input: full_address,
		componentRestrictions: {country: 'us'},
	};
	var service = new google.maps.places.AutocompleteService();
	
	service.getQueryPredictions(request, displaySuggestions);
}
function monitorJetFilings() {
	var url = 'api/jetfile/monitor';
	
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else { // If not
				//need notification
				console.log("monitoring done");
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			// report error
			console.log(errorThrown);
		}
	});
}
//obsolete, use displaySavedInfo
function redrawEditScreen(formValues, form_name) {
	var arrValues = formValues.split("&");
	for(var i =0, len = arrValues.length; i < len; i++) {
		var thevalue = arrValues[i];
		var arrValuePair = thevalue.split("=");
		field_name = arrValuePair[0];
		field_name = field_name.replace("Input", "");
		fieldvalue = arrValuePair[1];
		//self.model.set(field_name, fieldvalue);
		
		if ($("." + form_name + " #" + field_name + "Input").length != 0) {
			var blnSkipField = false;
			//occupation
			if (field_name=="occupation") {
				blnSkipField = true;
				if (!isNaN($("." + form_name + " #" + field_name + "Input").val())) {
					//this is a lookup
					$("." + form_name + " #" + field_name + "Span").html($("." + form_name + " #occupation_title").val());
				}
			}
			if(form_name=="lien") {
				if(field_name=="worker") {
					blnSkipField = true;
					
					//show worker full name
					if (!isNaN($("." + form_name + " #" + field_name + "Input").val())) {
						//this is a lookup
						$("." + form_name + " #" + field_name + "Span").html($("." + form_name + " #worker_full_name").val());
					}

				}
			}
			if (field_name=="password" || field_name=="email_pwd") {
				blnSkipField = true;
			}
			if (!blnSkipField) {
				//treat text fields one way
				if ($("." + form_name + " #" + field_name + "Input").prop("type")!= "select-one") {
					//check for checkbox
					if ($("." + form_name + " #" + field_name + "Input").prop("type")!= "checkbox") {						
						//special case for lookups
						var blnResetSpan = true;
						if ((field_name=="company_name" && $.isNumeric($("." + form_name + " #" + field_name + "Input").val())) || (field_name=="full_name" && $.isNumeric($("." + form_name + " #" + field_name + "Input").val()))) {
							blnResetSpan = false;
						}
						if (blnResetSpan) {
							$("." + form_name + " #" + field_name + "Span").html(escapeHtml($("." + form_name + " #" + field_name + "Input").val()));
						}
					} else {
						//it is a checkbox
						span_value = $("." + form_name + " #" + field_name + "Input").is(':checked');
						if (span_value) {
							span_value = "<span style='font-family:Wingdings; color:white'>ü</span>";
						} else {
							span_value = '';
						}
						$("." + form_name + " #" + field_name + "Span").html(span_value);
					}
				} else {
					//text value of the drop down
					var dropdown_text = $("." + form_name + " #" + field_name + "Input :selected").text();
					dropdown_text = dropdown_text.split(" - ")[0];
					if (dropdown_text=="Select from List") {
						dropdown_text = "";
					}
					$("." + form_name + " #" + field_name + "Span").html(escapeHtml(dropdown_text));
				}
			}
		}
		//toggleFormEdit(form_name);
	}
}

function redrawJsonScreen(formValues) {
	var arrValues = formValues.split("&");
	var the_len = arrValues.length;
	for(var i =0; i < the_len; i++) {
		var thevalue = arrValues[i];
		var arrValuePair = thevalue.split("=");
		field_name = arrValuePair[0];
		fieldvalue = arrValuePair[1];
		//self.model.set(field_name, fieldvalue);
		if (fieldvalue.indexOf("{") > -1) {
			var jdata = JSON.parse(fieldvalue);
			var len = jdata.length;
			for(var j =0; j < len; j++) {
				var current_data = jdata[j];
				if (typeof current_data.name == "undefined") {
					continue;
				}
				var input_id = current_data.name;
				var input_value = current_data.value;
				
				if ($("." + input_id).is(':checkbox')) {
					if ($("." + input_id).is(':checked')) {
						$("#" + input_id.replace("Input", "Span")).html("Y");
					}
				} else {
					$("#" + input_id.replace("Input", "Span")).html($("#" + input_id).val());
				}
			}
		}
	}
}
function addMinutes(date, minutes) {
    return new Date(date.getTime() + minutes*60000);
}
var overdue_tasks_count = -1;
function maxHundred(val) {
	var the_count = val;
	if (the_count >= 100) {
		the_count = "99+";
	}
	return the_count;
}
function firmAccountCounts() {
	var accounts = new AccountCollection();
	accounts.fetch({
		success: function(data) {
			var accounts = data.toJSON();
			_.each( accounts, function(account) {
				var jdata = JSON.parse(account.account_info);
				var account_type = account.account_type;
				var account_id  = account.account_id;
				
				$("#" + account_type + "_account_indicator").html("&nbsp;&#10003;<input type='hidden' id='" + account_type + "_account_id' value='" + account_id + "' />");
			});
		}
	});
}
function overdueTasksCount(blnFirm) {
	if (typeof blnFirm == "undefined") {
		blnFirm = false;
	}
	var url = 'api/overduetaskscount';
	if (blnFirm) {
		url = 'api/overduefirmtaskscount';
	}
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else { // If not
				if (!blnFirm) {
					overdue_tasks_count = data.count;
					var the_count = maxHundred(data.count);
					
					//need notification
					$("#overdue_task_count").html(the_count);
					if (data.count > 0) {
						$(".overdue_tasks_indicator").html(the_count);
						$(".overdue_tasks_indicator").fadeIn();
						if ($("#homepage_overdue_tasks_notification").length > 0) {
							var width = 22;
							if (data.count > 99) {
								width = 32;
							}
							$("#homepage_overdue_tasks_notification").html("<div style='float:left;width:45px;'><a href='#taskoverdue' class='white_text'><div style='background: red; color: white; border:1px solid white; padding-left:2px; padding-right:2px; width:" + width + "px; text-align:center'>" + overdue_tasks_count + "</div></a></div>You have Overdue Tasks");
						}
						
						var url = "api/setting/byname";
						formValues = "name=Overdue Warning";
						$.ajax({
							url:url,
							type:'POST',
							dataType:"json",
							data: formValues,
							success:function (data) {
								if(data.error) {  // If there is an error, show the error messages
									saveFailed(data.error.text);
								}
								else { // If not, send them back to the home page
									if(data != false) {
										//popup
										if (blnDisplayOverdues) {
											composeOverdueTasks(the_count);
										}
									}
								}
							}
						});
					} else {
						$(".overdue_tasks_indicator").html("");
						$(".overdue_task_count").html("");
						$(".overdue_tasks_indicator").fadeOut();
						if ($("#homepage_overdue_tasks_notification").length > 0) {
							$("#homepage_overdue_tasks_notification").html("You have NO Overdue Tasks");
						}
					}
				
					//check again in 5 minutes
					setTimeout(function() {
						 overdueTasksCount();
					}, 300011);
				} else {
					if (data.count > 0) {
						if (customer_id != 1064) {	//per thomas 4/20/2018
							$("#welcome_to_ikase").html("<span style='background:cadetblue; color:white; padding:2px'>There are " + data.count + " Overdue Firm Tasks</span>&nbsp;<a href='#taskfirmoverdue' class='white_text' style='font-size: 0.5em'>click here to review</a>");
							$("#welcome_to_ikase").fadeIn();
							setTimeout(function() {
								$("#welcome_to_ikase").fadeOut();
							}, 15500);
						}
						var the_count = "";
						if (data.count > 0) {
							the_count = maxHundred(data.count);
						}
						$("#firm_overdue_task_count").html(the_count)
					} else {
						$("#welcome_to_ikase").fadeOut();
					}
				}
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			// report error
			console.log(errorThrown);
		}
	});
}
function cleanupNote(note) {
	if (typeof note == "undefined") {
		return "";
	}
	note = note.replaceAll('<p class="MsoNormal"', '<p');
	note = note.replaceAll('class="MsoNormal"', '');
	note = note.replaceAll('class="MsoNormal" style="margin-bottom:0in;margin-bottom:.0001pt;line-height:', '');
	note = note.replaceAll('style="margin-bottom:0in;margin-bottom:.0001pt;line-height:', '');
	note = note.replaceAll('normal">', '>');
	note = note.replaceTout('background-color: rgb(255, 255, 255);', '');
	note = note.replaceTout('background-color: rgb(25, 25, 25);', '');
		
	return note;	
}
function copyToClipboard(that){
	//does not work
	var clipboard_info = document.getElementById("clipboard_info");
	clipboard_info.value = that;
	clipboard_info.select();
	
	try {
        var status = document.execCommand('copy');
        if(!status){
            console.error("Cannot copy text");
        }else{
            console.log("The text is now on the clipboard");
        }
    } catch (err) {
        console.log('Unable to copy.');
    }
}
function showKaseAbstract(the_model) {
	//alert(the_model.case_id);
	if (typeof the_model.get("case_id") == "undefined") {
		var case_id = document.location.hash.split("/")[1];
	} else {
		var case_id = the_model.get("case_id");
	}
	var kase = kases.findWhere({case_id: case_id});

	if (!kase) { 
		return; 
	}
	the_model.set("panel_title", "Kase Summary");
	var kase_type = kase.get("case_type");
	//var kase_adj = this.model.get("adj_number");
	
	var blnWCAB = ((kase_type.indexOf("Worker") > -1) || (kase_type.indexOf("WC") > -1) || (kase_type.indexOf("W/C") > -1));
	var blnWCABDefense = (kase_type.indexOf("WCAB_Defense") == 0);
	
	the_model.set("case_type", kase_type);
					
	var claims = kase.get("claims").replace("Claims: ", "");
	var arrClaims = claims.split("|");
	var arrayLength = arrClaims.length;
	for (var i = 0; i < arrayLength; i++) {
		var claim = arrClaims[i].split("~")[0];
		var inhouse = "N";
		if (typeof arrClaims[i].split("~")[1] != "undefined") {
			inhouse = arrClaims[i].split("~")[1];
		}
		switch(claim) {
			case "3P":
				if (inhouse!="Y") {
					claim = "<span id='" + claim + "Holder' style='background:red'><a href='#parties/" + the_model.get("case_id") + "/-1/referredout_attorney:3P' title='Click to add a Referred Out Attorney' class='white_text'>Third Party</a></span>";
				} else {
					claim = "Third Party (In House)";
				}
				break;
			case "SER":
				if (inhouse!="Y") {
					claim = "<span id='" + claim + "Holder' style='background:red'><a href='#parties/" + the_model.get("case_id") + "/-1/referredout_attorney:SER' title='Click to add a Referred Out Attorney' class='white_text'>Serious and Willful</a></span>";
				} else {
					claim = "SER (In House)";
				}
				break;
			default:
				if (inhouse!="Y") {
					claim = "<span id='" + claim + "Holder' style='background:red'><a href='#parties/" + the_model.get("case_id") + "/-1/referredout_attorney:" + claim + "' title='Click to add a Referred Out Attorney' class='white_text'>" + claim + "</a></span>";
				} else {
					claim = claim + " (In House)";
				}
		}
		arrClaims[i] = claim;
	}
	if (arrayLength == 0) {
		the_model.set("claims_display", "none");
	} else {
		the_model.set("claims_display", "");
	}
	the_model.set("claims_values", "Claims: " + arrClaims.join("; "));
	$('#kase_abstract_holder').html(new kase_abstract_view({model: the_model}).render().el);
}
function saveFormFailed(text) {
	alert("The Save operation has failed:" + text);
}
function pingMe() {
	var url = 'api/session/verify';
	
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		success:function (data) {
			blnValidSession = data.valid;
			if(!data.valid) {
				document.location.href = "#conflict";
				return false;
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			// report error
			console.log(errorThrown);
			return false;
		}
	});
}
function cleanPhone(phone) {
	//because of mask, I have to have a good phone number
	//remove parentheses
	phone = phone.replaceTout("(", "");
	phone = phone.replaceTout(")", "");
	phone = phone.replaceTout(" ", "-");
	
	return phone;
}
function isWCAB(kase_type) {
	return ((kase_type.indexOf("Worker") > -1) || (kase_type.indexOf("WC") > -1 || kase_type.indexOf("W/C") > -1) || blnPatient);
}
function displaySavedInfo(formValues, form_name) {
	// If no errors, go  back to read mode
	var arrValues = formValues.split("&");
	var ssn1 = "";
	var ssn2 = "";
	var ssn3 = "";
	for(var i =0, len = arrValues.length; i < len; i++) {
		var thevalue = arrValues[i];
		var arrValuePair = thevalue.split("=");
		field_name = arrValuePair[0];
		fieldvalue = arrValuePair[1];
		
		//ssn is triple set
		if (field_name.indexOf("ssn")==0) {
			if (field_name=="ssn1") {
				var ssn1 = String(fieldvalue);
			}
			if (field_name=="ssn2") {
				var ssn2 = String(fieldvalue);
			}
			if (field_name=="ssn3") {
				var ssn3 = String(fieldvalue);
			}
			continue;
		}
		//personal injury exception
		if (field_name == "personal_injury_description") {
			field_name = "personal_injury_accident_description";
		}
		if ($("." + form_name + " #" + field_name + "Input").length != 0) {
			//occupation
			var blnSkipField = false;
			if (field_name=="occupation") {
				if (!isNaN($("." + form_name + " #" + field_name + "Input").val())) {
					//this is a lookup
					$("." + form_name + " #" + field_name + "Span").html($("." + form_name + " #occupation_title").val());
				}
				blnSkipField = true;
			}
			
			if(form_name=="lien") {
				if(field_name=="worker") {
					blnSkipField = true;
					
					//show worker full name
					if (!isNaN($("." + form_name + " #" + field_name + "Input").val())) {
						//this is a lookup
						$("." + form_name + " #" + field_name + "Span").html($("." + form_name + " #worker_full_name").val());
					}

				}
			}
			//never show password
			if (field_name=="password" || field_name=="email_pwd") {
				blnSkipField = true;
			}
			if (!blnSkipField) {
				//treat text fields one way
				if ($("." + form_name + " #" + field_name + "Input").prop("type")!= "select-one") {
					if (field_name!="note" && field_name!="signature") {
						//check for checkbox
						if ($("." + form_name + " #" + field_name + "Input").prop("type")!= "checkbox") {						
							$("." + form_name + " #" + field_name + "Span").html(escapeHtml($("." + form_name + " #" + field_name + "Input").val()));
						} else {
							//it is a checkbox
							span_value = $("." + form_name + " #" + field_name + "Input").is(':checked');
							if (span_value) {
								span_value = "<span style='font-family:Wingdings; color:white'>ü</span>";
							} else {
								span_value = '';
							}
							$("." + form_name + " #" + field_name + "Span").html(span_value);
						}
					} else {
						//the note is not escaped, except for script
						var note_value = $("." + form_name + " #" + field_name + "Input").val();
						var stringOfHtml = note_value;
						var html = $(stringOfHtml);
						html.find('script').remove();
						
						if (field_name=="note") {
							note_value = html.wrap("<div>").parent().html(); // have to wrap for html to get the outer element
						}

						$("." + form_name + " #" + field_name + "Span").html(note_value);
					}
				} else {
					//text value of the drop down
					var dropdown_text = $("." + form_name + " #" + field_name + "Input :selected").text();
					dropdown_text = dropdown_text.split(" - ")[0];
					if (dropdown_text.indexOf("Select") > -1) {
						dropdown_text = "";
					}
					$("." + form_name + " #" + field_name + "Span").html(escapeHtml(dropdown_text));
				}
			}
		}
		//toggleFormEdit(form_name);
		//special case
		if ($("#full_addressInput").length > 0) {
			if ($("#full_addressInput").val()=="") {
				var partie_type = $("#partie_type").val();
				var arrAddress = [];
				
				var street = $("#street_" + partie_type).val();
				if (street!="") {
					arrAddress.push(street);
				}
				var suite = $("#suite_" + partie_type).val();
				if (suite!="") {
					arrAddress.push(suite);
				}
				var city = $("#city_" + partie_type).val();
				if (city!="") {
					arrAddress.push(city);
				}
				var state = $("#administrative_area_level_1_" + partie_type).val();
				if (state!="") {
					arrAddress.push(state);
				}
				var zip = $("#postal_code_" + partie_type).val();
				if (zip!="") {
					arrAddress.push(zip);
				}
				
				$("#full_addressSpan").html(arrAddress.join(", "));
			}
		}
	}
}
function getRandomArbitrary(min, max) {
  return Math.random() * (max - min) + min;
}
function makeRandomString(length) {
  var text = "";
  var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

  for (var i = 0; i < length; i++)
    text += possible.charAt(Math.floor(Math.random() * possible.length));

  return text;
}
function answerQuestion(event) {
	var element = event.currentTarget;
	event.preventDefault();
	
	var answer_id = element.id.replace("question", "answer");
	$("#" + answer_id).fadeIn();
	
	setTimeout(function() {
		$("#" + answer_id).fadeOut();
	}, 5500);
}
function resetCurrentContent(form_name) {
	if (typeof form_name=="undefined") {
		//empty out all the cached content variables
		current_dash_content = "";
		current_parties_content = "";
		current_case_notes = undefined;
		current_case_activities = undefined;
		current_case_documents = undefined;
	} else {
		switch(form_name) {
			case "document":
				current_case_documents = undefined;
				break;
			case "notes":
				current_case_notes = undefined;
				break;
			case "activity":
				current_case_activities = undefined;
				break;
		}
	}
	//console.log("current cache cleared");
}