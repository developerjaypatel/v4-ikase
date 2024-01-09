// The Template Loader. Used to asynchronously load templates located in separate .html files
window.templateLoader = {

    load: function(views, callback) {

        var deferreds = [];
		var extension;
        $.each(views, function(index, view) {
			if (window[view]) {
				extension = 'html';
				if (view=="webmail_listing_view" || view=="check_form" || view=="costs_view" || view=="check_listing_view" || view=="home_medical_view" || view=="activity_listing_view" || view=="archive_listing_view" || view=="partie_listing_choose" || view=="partie_listing_event" || view=="chat_view" || view=="kase_list_category_view" || view=="kase_list_report" || view=="calendar_view" || view=="import_view" || view=="applicant_view" || view=="dialog_view" || view=="dashboard_view" || view=="dashboard_settlement_view" || view=="dashboard_injury_view" || view=="dashboard_user_view" || view=="document_listing_view" || view=="document_listing_search" || view=="document_listing_message" || view=="document_upload_view" || view=="eams_applicant_view" || view=="eams_previouscases_view" || view=="eams_bodyparts_view" || view=="eams_parties_view" || view=="eams_events_view" || view=="eams_hearings_view" || view=="eams_scrape_view" || view=="eams_view" || view=="eams_form_listing" || view=="eams_form_view" || view=="eams_form_attach" || view=="eams_listing_view" || view=="injury_view" || view=="bodyparts_view" || view=="person_view" || view=="kai_view" || view=="notes_view" || view=="parties_view" || view=="partie_cards_view" || view=="parties_new_view" || view=="partie_view" || view=="kase_listing_view" || view=="kase_header_view" || view=="kase_edit_view" || view=="kase_summary_view" || view=="kase_nav_bar_view" || view=="kase_nav_left_view" || view=="search_kase_view" || view=="email_view" || view=="signature_view" || view=="user_view" || view=="dashboard_person_view"  || view=="dashboard_home_view" || view=="person_image" || view=="injury_number_view" || view=="injury_add_view" || view=="message_view" || view=="message_listing" || view=="note_listing_view" || view=="message_attach" || view=="interoffice_view" || view=="stack_listing_view" || view=="task_view" || view=="task_listing" || view=="task_print_stack_listing" || view=="new_kase_view" || view=="new_note_view" || view=="chatting_view" || view=="event_view" || view=="event_listing" || view=="setting_view" || view=="letter_view" || view=="letter_listing_view" || view=="letter_attach" || view=="setting_attach" || view=="kase_list_task_view" || view=="template_listing_view" || view=="template_upload_view" || view=="note_print_view" || view=="task_print_view" || view=="task_print_listing" || view=="message_print_view" || view=="message_print_view1" || view=="message_print_listing" || view=="rolodex_listing_view" || view=="user_listing_view" || view=="user_setting_listing" || view=="parties_new_rolodex" || view=="kase_control_panel" || view=="form_listing" || view=="kase_letter_listing_view" || view=="prior_treatment_listing_view" || view=="kase_occurences_print_view" || view=="event_listing_print" || view=="settlement_view" || view=="prior_referral_view" || view=="exam_view" || view=="exam_listing" || view=="document_search" || view=="lien_view" || view=="kases_report" || view=="referrals_report" || view =="vservice_view" || view =="vservices_view" || view =="dashboard_accident_view" || view =="accident_view" || view=="medical_specialties_select" || view=="payments_print_listing" || view=="rental_view" || view=="multichat" || view=="multichat_messages" || view=="reset_password_view" || view=="property_damage_view" || view=="car_passenger_view" || view=="accident_new_view" || view=="accident_new_view" || view=="dashboard_slipandfall_view" || view=="dashboard_motorcycle_view" || view=="dashboard_naturalcause_view" || view=="bulk_webmail_assign_view" || view=="bulk_import_assign_view" || view=="dashboard_email_view" || view=="red_flag_note_listing_view" || view=="dashboard_related_cases_view" || view=="bulk_date_change_view" || view=="contact_listing_view" || view=="contact_view" || view=="personal_injury_view" || view=="activity_print_listing_view" || view=="activity_print_summary_view" || view=="note_print_listing_view" || view=="med_index_report" || view=="dashboard_home_view_mobile" || view=="kase_nav_bar_view_mobile" || view=="kase_listing_view_mobile" || view=="note_listing_view_mobile" || view=="task_listing_mobile" || view=="event_listing_mobile" || view=="notes_view_mobile" || view=="task_view_mobile" || view=="event_view_mobile" || view=="document_view_mobile" || view=="document_listing_view_mobile") {
					extension = 'php';
				}				
				deferreds.push($.get('templates/' + view + '.' + extension, function(data) {
					window[view].prototype.template = _.template(data);
				}, 'html'));
			
            } else {
                console.log(view + " not found");
            }
        });

        $.when.apply(null, deferreds).done(callback);
    }
};
function loadTemplate(view, extension, self) {
	$.get('templates/' + view + '.' + extension, function(data) {
		window[view].prototype.template = _.template(data);
		var new_view = window[view];
		self.model.set("template_loaded", true);
		var holder_id = self.model.get("holder");
		//in case i forgot
		if (document.location.pathname.indexOf("v9") > -1 && view == "event_view") {
			holder_id = "." + holder_id.replace(".", "");
		} else {
			holder_id = "#" + holder_id.replace("#", "");
		}
		$(holder_id).html(new new_view({collection: self.collection, model: self.model}).render().el);
	}, 'html')
	.fail(function() {
		alert( "template " + view + "." + extension + " could not be loaded" );
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
		while (thestring.indexOf(findString) > 0) {
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
	str = str.replaceAll("<~", "<span style='font-weight:bold; color:yellow'");
	str = str.replaceAll("~", "span");
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
function executeChanges() {
	if ($("#kase_content").length > 0) {
		$("#kase_content").html("<table align='center'><tr><td align='center' style=''><i class='icon-spin4 animate-spin' style='font-size:6em; color:white'></i><td><tr></table");
	}
	$("#content").css("top", "0px");
	$("#mobile_content").html("");
	$("#search_results").html("");
	$("#page_title").fadeOut();
};
function executeMainChanges() {
	//reset batchscan interval
	check_remote_id = false;
	$("#mobile_content").html("");
	if (!$("#myModal4").hasClass("in")) {
		$("#myModalBody").html("");
	}
	$("#search_document").hide();
	
	
	$("#content").css("top", "0px");
	
	
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
	
	if ($("#srch-term").val()!="") {
		$("#srch-term").val("");
		$("#srch-term").trigger("blur");
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
function gridsterById (gridster_id) {
	var gridster = [];
	var widget_base_dimensions = [230, 44];
	
	//special case
	if (gridster_id == "gridster_priors") {
		widget_base_dimensions = [465, 220];
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
	if (gridster_id == "gridster_bodyparts") {
		widget_base_dimensions = [230, 60];
	}
	if (gridster_id == "gridster_car_passenger") {
		widget_base_dimensions = [465, 220];
	}
	if (gridster_id == "gridster_related_cases") {
		widget_base_dimensions = [465, 450];
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
	if (gridster_id == "gridster_password") {
		widget_base_dimensions = [250, 100];
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
			$("#" + gridster_id).fadeIn();
		});
	}
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
var showAttachmentPreview = function(form_name, event, filename, case_id, customer_id) {
	clearTimeout(show_attachment_preview_id);
	
	///$("#" + form_name + "_preview_panel").css("border", "1px solid red");
	//$("#" + form_name + "_preview_panel").fadeIn();
	//return;
	var element = event.currentTarget;
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
		//console.log("top", rect.top + " + " + scrollTop);
		//var arrFilename = filename.split("/");
		//filename = arrFilename[arrFilename.length - 1];
		
		//clean up
		filename = filename.replaceAll("../uploads/", "uploads/");
		filename = filename.replaceAll("https:uploads/", "uploads/");
		
		if (case_id!="") {
			filename = filename.replaceAll("uploads/" + customer_id + "/" + case_id + "/", "");
		} 
		filename = filename.replaceAll("uploads/" + customer_id + "/", "");
		
		var arrFiles = filename.split("|");
		var arrayLength = arrFiles.length;
		var panel_html;
		var arrLinks = [];
		for (var i = 0; i < arrayLength; i++) {
			filename = arrFiles[i];
			panel_html = "<div><a href='uploads/preview.php?file=uploads/" + customer_id + "/";
			
			if (case_id!="") {
				panel_html += case_id + "/";
			}
			panel_html += filename + "' target='_blank' class='" + form_name + "_preview_link' style='background:white;color:blue'>" + filename + "</a></div>";
			arrLinks[arrLinks.length] = panel_html;
		};
		panel_html = arrLinks.join("");
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
var documentPreview = function(event, filename, customer_id, thumbnail_folder) {
	if (typeof thumbnail_folder == "undefined") {
		thumbnail_folder = "";
	}
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
		
		//batch scans are imported
		if (!isNaN(thumbnail_folder)) {
			var arrFileName = filename.split("_");
			first_page = arrFileName[arrFileName.length - 2] - 1;
			//get rid of last 2 members of array
			arrFileName.pop();
			arrFileName.pop();
			filename = arrFileName.join("_") + "_0.png";
			var preview = "uploads/" + customer_id + "/imports/" + thumbnail_folder + "/";	
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
		elementTop = scrollTop - 20;
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
var showImportedPreview = function(element, src) {
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
		elementLeft = rect.left+scrollLeft - 150;
		if (elementLeft > 500) {
			elementLeft = 350;
		}
		
		$("#preview_panel").html("<img src='" + preview.replace(".pdf", ".jpg") + "' style='border:1px solid black'>");
		$("#preview_panel").css({display: "", top: elementTop, left: elementLeft, position:'absolute'});
		$("#preview_panel").css("margin-left", "250px");
	}
}
var documentThumbnail = function(filename, customer_id, thumbnail_folder, case_id) {
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
			first_page = arrFileName[arrFileName.length - 2] - 1;
			//get rid of last 2 members of array
			arrFileName.pop();
			arrFileName.pop();
			filename = arrFileName.join("_") + "_0.png";
			var preview = "uploads/" + customer_id + "/imports/" + thumbnail_folder + "/";	
		}
	}
	preview = preview.replace("medium/", "thumbnail/");
	preview += filename;
	
	//it has to jpg/png
	var arrPreview = preview.split(".");
	var extension = arrPreview[arrPreview.length - 1];
	extension = extension.toLowerCase();
	if (extension!="jpg" && extension!="png") {
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
var hidePreview = function() {
	$("#preview_panel").css({display: "none"});
}
var showPreview = function(event, filename, time_stamp, pages, customer_id) {
	var first_page = pages - 1;
	if (pages.indexOf("-") > -1) {
		first_page = pages.split("-")[0] - 1;
	}
	if (time_stamp!="") {
		var preview = "uploads/" + customer_id + "/" + time_stamp + "/" + filename + "_" + first_page + ".png";
	} else {
		var preview = "uploads/" + customer_id + "/thumbnails/" + filename + "_" + first_page + ".png";
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
		
		$("#preview_panel").html("<img src='" + preview + "' style='border:1px solid black'>");
		$("#preview_panel").css({display: "", top: elementTop, left: elementLeft, position:'absolute'});
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
	var url = 'https://v2.ikase.org/api/kases/filters';
	formValues = "val_attorney=" + val_attorney + "&val_worker=" + val_worker;
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
}
function reportActivity(href) {
	document.location.href = href;
}
function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
function navigateMobile(hash) {
	hash = hash.trim();
	//alert("mob");
	if (document.location.hash.indexOf(hash) < 0) {
			window.history.pushState(null, null, hash);
		}
}
function showTabs(case_id) {
	$("#content").fadeOut(function() {
		var kase = kases.findWhere({case_id: case_id});
		var case_name = "";
		if (typeof kase != "undefined") {
			case_name = kase.toJSON().name;
		}
		$("#search_results").hide();
		$("#mobile_content").css("margin-top", "60px");
		
		if (document.location.hash.indexOf("#kases/" + case_id) < 0) {
			window.history.pushState(null, null, "#kases/" + case_id);
		}
		
		$("#mobile_content").html('<ul role="tablist" class="nav nav-tabs mobile_tabs" style="width:95%; margin-right:auto; margin-left:15px"><li role="presentation" class="kase_mobile active"><a href="#kase_demograph" id="demographics_mobile_link" onclick="navigateMobile(\'#kases/' + case_id + '\')" aria-controls="kase_demograph" role="tab" data-toggle="tab" style="border-radius: 4px 4px 0 0; border-bottom:1px solid transparent border:1px solid white; height:38px; color:white">Demographics&nbsp;&nbsp;<i style="font-size:1em; color:#EDEDED; cursor:pointer;" class="glyphicon glyphicon-list-alt" title=""></i></a></li><li role="presentation" class="notes_mobile"><a href="#notes_mobile" id="notes_mobile_link" onclick="navigateMobile(\'#notemobilelist/' + case_id + '\')" aria-controls="notes" role="tab" data-toggle="tab" style="border-radius: 4px 4px 0 0; border-bottom:1px solid transparent border:1px solid white; height:38px; color:white">Notes&nbsp;&nbsp;<i style="font-size:1em; color:#EDEDED; cursor:pointer;" class="glyphicon glyphicon-bookmark" title=""></i></a></li><li role="presentation" class="tasks_mobile"><a href="#tasks_mobile" id="tasks_mobile_link" onclick="navigateMobile(\'#taskmobilelist/' + case_id + '\')" aria-controls="tasks" role="tab" data-toggle="tab" style="border-radius: 4px 4px 0 0; border-bottom:1px solid transparent border:1px solid white; height:38px; color:white">Tasks&nbsp;&nbsp;<i style="font-size:1em; color:#EDEDED; cursor:pointer;" class="glyphicon glyphicon-tasks" title=""></i></a></li><li role="presentation" class="events_mobile"><a href="#events_mobile" id="events_mobile_link" onclick="navigateMobile(\'#eventmobilelist/' + case_id + '\')" aria-controls="events" role="tab" data-toggle="tab" style="border-radius: 4px 4px 0 0; border-bottom:1px solid transparent border:1px solid white; height:38px; color:white">Events&nbsp;&nbsp;<i style="font-size:1em; color:#EDEDED; cursor:pointer;" class="glyphicon glyphicon-calendar" title=""></i></a></li><li role="presentation" class="docs_mobile"><a href="#docs_mobile" aria-controls="docs" role="tab" data-toggle="tab" style="border-radius: 4px 4px 0 0; border-bottom:1px solid transparent border:1px solid white; height:38px; color:white">Docs&nbsp;&nbsp;<i style="font-size:1em; color:#EDEDED; cursor:pointer;" class="glyphicon glyphicon-calendar" title=""></i></a></li></ul><div class="tab-content" style="margin-left:15px"><div role="tabpanel" class="tab-pane active" id="kase_demograph" style="background:url(img/glass.png) repeat; color:#FFF"><iframe id="demograph_iframe" width="98%" height="550px"></iframe></div><div role="tabpanel" class="tab-pane fade" id="notes_mobile" style="background:url(img/glass.png) repeat; color:#FFF"></div><div role="tabpanel" class="tab-pane fade" id="tasks_mobile" style="background:url(img/glass.png) repeat; color:#FFF">tasks</div><div role="tabpanel" class="tab-pane fade" id="events_mobile" style="background:url(img/glass.png) repeat; color:#FFF">events</div><div role="tabpanel" class="tab-pane fade" id="docs_mobile" style="background:url(img/glass.png) repeat; color:#FFF">documents</div></div>');
		
		$("#mobile_content").prepend("<div style='color: white; width: 100%; margin-left: auto; margin-right: auto; text-align: center; font-size: 1.5em;'>" + case_name + "</div>");
		$('.mobile_tabs a:first').tab('show');
		$('.mobile_tabs a').click(function (e) {
			e.preventDefault()
			
			$(this).tab('show');
			
		});
		setTimeout(function() {
			$('.mobile_tabs a:first').tab('show');
		},500);
		$("#mobile_content").fadeIn(function() {
			/*
			$('html, body').animate({
					scrollTop: $("#mobile_content").offset().top
			}, 2000);
			*/
			$('.mobile_tabs').addClass('case_' + case_id);
			$("#demograph_iframe").attr("src", "reports/demographics_sheet_mobile.php?case_id=" + case_id);
			
			setTimeout(function() {
				var notes = new NoteCollection([], { case_id: case_id });
				
				notes.fetch({
					success: function(data) {
						var note_list_model = new Backbone.Model;
						note_list_model.set("display", "full");
						note_list_model.set("partie_type", "note");
						note_list_model.set("partie_id", -1);
						note_list_model.set("case_id", case_id);
						$("#notes_mobile").html(new note_listing_view_mobile({collection: data, model: note_list_model}).render().el);
						$("#notes_mobile").css("width", "460px");
						$('.note_listing').css("margin-left", "5px");
						$("#glass_header").css("margin-left", "5px");
						$("#glass_header").removeClass("glass_header");
					}
				});	
			}, 2000);
			var tasks = new TaskInboxCollection({case_id: case_id});
			
			tasks.fetch({
				success: function (data) {
					var task_listing_info = new Backbone.Model;
					task_listing_info.set("title", "Task Inbox");
					task_listing_info.set("receive_label", "Due Date");
					task_listing_info.set("homepage", true);
					task_listing_info.set("case_id", case_id);
					$("#tasks_mobile").html(new task_listing_mobile({collection: data, model: task_listing_info}).render().el);
					$("#tasks_mobile").css("width", "460px");
				}
			});	
			
			var occurences = new OccurenceCollection({case_id: case_id});
			
			occurences.fetch({
					success: function(data) {
						var kase = new Backbone.Model;
						kase.set("title", "Events");
						kase.set("case_id", case_id);
						kase.set("homepage", true);
						kase.set("event_class", "listing");
						
						$("#events_mobile").html(new event_listing_mobile({collection: occurences, model: kase, homepage: true, title: "Events", case_id: case_id}).render().el);
						$("#events_mobile").css("width", "460px");
					}
				}
			);
			
			
			kase_documents = new DocumentCollection([], { case_id: case_id });
			
			kase_documents.fetch({
				success: function(data) {
					var kase = new Backbone.Model;
					kase.set("case_id", case_id);
					$("#docs_mobile").html(new document_listing_view_mobile({collection: data, model: kase}).render().el);
					$("#docs_mobile").removeClass("glass_header_no_padding");
				}
			});
			//set current case
			current_case_id = case_id;
			//$("#notes_mobile").html();
			$('.mobile_tabs a:first').tab('show');
		});
	});
}
function saveMobileNote(event, case_id) {
	event.preventDefault();
	var url = 'api/notes/add';
	var formValues = $("#note_mobile_form").serialize();
	
	$.ajax({
	url:url,
	type:'POST',
	dataType:"json",
	data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else { // If not
				var case_id = $("#case_id").val();
				$("#mobile_content").hide();
				document.location.href = "#notemobilelist/" + case_id;
			}
		}
	});
}
function saveMobileTask(event, case_id) {
	event.preventDefault();
	var url = 'api/task/add';
	var formValues = $("#task_mobile_form").serialize();
	
	$.ajax({
	url:url,
	type:'POST',
	dataType:"json",
	data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else { // If not
				var case_id = $("#case_id").val();
				$("#mobile_content").hide();
				document.location.href = "#taskmobilelist/" + case_id;
			}
		}
	});
}
function saveMobileEvent(event, case_id) {
	event.preventDefault();
	var url = 'api/event/add';
	var formValues = $("#event_mobile_form").serialize();
	
	$.ajax({
	url:url,
	type:'POST',
	dataType:"json",
	data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else { // If not
				var case_id = $("#case_id").val();
				$("#mobile_content").hide();
				document.location.href = "#eventmobilelist/" + case_id;
			}
		}
	});
}
function isWCAB(kase_type) {
	//return ((kase_type.indexOf("Worker") > -1) || (kase_type.indexOf("WC") > -1 || kase_type.indexOf("W/C") > -1) || blnPatient);
	return ((kase_type.indexOf("Worker") > -1) || (kase_type.indexOf("WC") > -1 || kase_type.indexOf("W/C") > -1)); // solulab changes
}