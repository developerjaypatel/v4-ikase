// The Template Loader. Used to asynchronously load templates located in separate .html files
window.templateLoader = {

    load: function(views, callback) {

        var deferreds = [];
		var extension;
        $.each(views, function(index, view) {
			if (window[view]) {
				extension = 'html';
				if (view=="chat_view" || view=="kase_list_category_view" || view=="calendar_view" || view=="import_view" || view=="applicant_view" || view=="dialog_view" || view=="dashboard_view" || view=="dashboard_injury_view" || view=="dashboard_user_view" || view=="document_listing_view" || view=="document_upload_view" || view=="eams_view" || view=="eams_listing_view" || view=="injury_view" || view=="bodyparts_view" || view=="person_view" || view=="kai_view" || view=="notes_view" || view=="parties_view" || view=="partie_cards_view" || view=="parties_new_view" || view=="partie_view" || view=="kase_listing_view" || view=="kase_header_view" || view=="kase_edit_view" || view=="kase_summary_view" || view=="kase_nav_bar_view" || view=="kase_nav_left_view" || view=="email_view" || view=="signature_view" || view=="user_listing_view" || view=="user_view" || view=="dashboard_person_view"  || view=="dashboard_home_view" || view=="person_image" || view=="injury_number_view" || view=="injury_add_view" || view=="message_view" || view=="message_listing" || view=="note_listing_view" || view=="message_attach" || view=="interoffice_view" || view=="stack_listing_view" || view=="task_view" || view=="task_listing" || view=="new_kase_view" || view=="new_note_view" || view=="chatting_view" || view=="event_view" || view=="event_listing" || view=="setting_view" || view=="letter_view" || view=="letter_listing_view" || view=="letter_attach" || view=="setting_attach" || view=="kase_list_task_view" || view=="template_listing_view" || view=="template_upload_view" || view=="task_print_view" || view=="task_print_listing" || view=="message_print_view" || view=="message_print_view1" || view=="rolodex_listing_view" || view=="user_setting_listing" || view=="parties_new_rolodex" || view=="kase_control_panel" || view=="form_listing" || view=="kase_letter_listing_view" || view=="prior_treatment_listing_view" || view=="kase_occurences_print_view" || view=="lien_view") {
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
var highLight = function(str, key) {
	if (key=="" || key==null || typeof key == "undefined") {
		return "";
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
String.prototype.getAge = function() {
	return moment(this.valueOf()).fromNow(true).replace(" years", "");
}
String.prototype.cleanString = function() {
	return this.replace(/[\[\]|&;$%@"<>()+,]/g, "");
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
	$("#search_results").html("");
	$("#page_title").fadeOut();
};
function executeMainChanges() {
	if ($("#kase_content").length > 0) {
		$("#kase_content").html("<table align='center'><tr><td align='center' style=''><i class='icon-spin4 animate-spin' style='font-size:6em; color:white'></i><td><tr></table>");
	}
	$("#search_results").html("");
	$("#page_title").fadeOut();
}
function inArray(needle, haystack) {
    var length = haystack.length;
    for(var i = 0; i < length; i++) {
        if(haystack[i] == needle) return true;
    }
    return false;
}
function isDate(val) {
    var d = new Date(val);
    return !isNaN(d.valueOf());
}