// The Template Loader. Used to asynchronously load templates located in separate .html files
window.templateLoader = {

    load: function(views, callback) {

        var deferreds = [];
		var extension;
        $.each(views, function(index, view) {
			if (window[view]) {
				extension = 'php';
						
				// || view=="billing_listing_view"
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
		//console.log(self);
		self.model.set("template_loaded", true);
		var holder_id = self.model.get("holder");
		//in case i forgot
		if(holder_id.indexOf(".") < 0 && holder_id.indexOf("#") < 0) {
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
		$("#myModalBody").html("");
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
function executeMainChanges() {
	//reset batchscan interval
	check_remote_id = false;
	$("#content").removeClass("fc");
	$("#content").removeClass("fc-touch");
	$("#content").removeClass("fc-ltr");
	$("#content").removeClass("fc-unthemed");
	//if (document.location.pathname.indexOf("v9") > -1 || document.location.pathname.indexOf("v8") > -1 || document.location.pathname.indexOf("v7") > -1) {
		$("#myModalBody").html("");
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
		
		$("#search_modifiers").fadeOut();
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
function assignEmailKase(thread_id, message_id) {
	composeEmailAssign(message_id, "webmail", thread_id);
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
		widget_base_dimensions = [230, 44];
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
						var serial = gridster[6].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster_parties_new").fadeIn();
		}
	
		//parties_cards gridster
		if (gridster_index==7) {
			gridster[7] = $("#gridster_parties_cards ul").gridster({
				namespace: '#gridster_parties_cards',
				widget_margins: [5, 5],
				widget_base_dimensions: [275, 220],
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
			$("#gridster_parties_cards").fadeIn();
		}
		//notes gridster
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
						//var attach_link = "https://v2.ikase.org/uploads/" + customer_id + "/webmail_previews/" + login_user_id + "/" + element;
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
			var arrFiles = filename.split("|");
			var arrayLength = arrFiles.length;
			var panel_html;
			var arrLinks = [];
			for (var i = 0; i < arrayLength; i++) {
				filename = arrFiles[i];
				var strpos = filename.indexOf("attachments");
				display_filename = filename;
				if (strpos > -1) {
					panel_html = "<div><a href='https://www.ikase.xyz/ikase/gmail/ui/";
					var arrFileStructure = filename.split("/");
					display_filename = arrFileStructure[arrFileStructure.length - 1];
				} else {
					panel_html = "<div><a href='uploads/preview.php?file=uploads/" + customer_id + "/";
					
					if (case_id!="" && case_id!="-1") {
						panel_html += case_id + "/";
					}
				}
				panel_html += filename + "' target='_blank' class='" + form_name + "_preview_link' style='background:white;color:blue'>" + display_filename + "</a></div>";
				
				arrLinks[arrLinks.length] = panel_html;
			};
			panel_html = "<div style='width:auto'>" + arrLinks.join("") + "</div>";
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
		if (!isNaN(thumbnail_folder) && thumbnail_folder!="") {
			var arrFileName = filename.split("_");
			first_page = arrFileName[arrFileName.length - 2];
			//get rid of last 2 members of array
			arrFileName.pop();
			arrFileName.pop();
			filename = arrFileName.join("_") + "-" + first_page + ".png";
			var preview = "uploads/" + customer_id + "/imports/" + thumbnail_folder + "/";	
		}
	}
	preview += filename;
	
	//it has to jpg/png
	var arrPreview = preview.split(".");
	var extension = arrPreview[arrPreview.length - 1];
	extension = extension.toLowerCase();
	if (extension!="jpg" && extension!="png") {
		preview = "img/spacer.gif";
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
			first_page = arrFileName[arrFileName.length - 2];
			//get rid of last 2 members of array
			arrFileName.pop();
			arrFileName.pop();
			filename = arrFileName.join("_") + "-" + first_page + ".png";
			var preview = "uploads/" + customer_id + "/imports/" + thumbnail_folder + "/";	
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
function saveDraft(obj) {
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
		document.getElementById('map_results_holder').style.display = "none";
	}, 300);
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
		getGoogleAddresses(className);
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
function redrawEditScreen(formValues, form_name) {
	var arrValues = formValues.split("&");
	for(var i =0, len = arrValues.length; i < len; i++) {
		var thevalue = arrValues[i];
		var arrValuePair = thevalue.split("=");
		field_name = arrValuePair[0];
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
	for(var i =0, len = arrValues.length; i < len; i++) {
		var thevalue = arrValues[i];
		var arrValuePair = thevalue.split("=");
		field_name = arrValuePair[0];
		fieldvalue = arrValuePair[1];
		//self.model.set(field_name, fieldvalue);
		if (fieldvalue.indexOf("{") > -1) {
			var jdata = JSON.parse(fieldvalue);
			for(var i =0, len = jdata.length; i < len; i++) {
				var current_data = jdata[i];
				var input_id = current_data.name;
				var input_value = current_data.value;
				
				$("#" + input_id.replace("Input", "Span")).html($("#" + input_id).val());
			}
			continue;
		}
	}
}
function addMinutes(date, minutes) {
    return new Date(date.getTime() + minutes*60000);
}
var overdue_tasks_count = -1;
function maxHundred(val) {
	var the_count = val;
	if (the_count > 100) {
		the_count = "99+";
	}
	return the_count;
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
					$("#overdue_task_count").html("&nbsp;(" + the_count + ")");
					if (data.count > 0) {
						$("#overdue_tasks_indicator").html(the_count);
						$("#overdue_tasks_indicator").fadeIn();
						if ($("#homepage_overdue_tasks_notification").length > 0) {
							$("#homepage_overdue_tasks_notification").html("You have <a href='#taskoverdue' class='white_text'><span style='background: rgb(255, 102, 0); padding-left:2px; padding-right:2px; border:1px solid white;'>" + overdue_tasks_count + "</span> Overdue Tasks</a>");
						}
					} else {
						$("#overdue_tasks_indicator").html("");
						$("#overdue_task_count").html("");
						$("#overdue_tasks_indicator").fadeOut();
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
						$("#welcome_to_ikase").html("<span style='background:orange; color:white; padding:2px'>There are " + data.count + " Overdue Firm Tasks</span>&nbsp;<a href='#taskfirmoverdue' class='white_text' style='font-size: 0.5em'>click here to review</a>");
						$("#welcome_to_ikase").fadeIn();
						setTimeout(function() {
							$("#welcome_to_ikase").fadeOut();
						}, 15500);
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
function saveFailed(error) {
	alert("System Error:" + error);
	console.log(error);
}
function loadJsCssFile(filename, filetype){
    if (filetype=="js"){ //if filename is a external JavaScript file
        var fileref=document.createElement('script')
        fileref.setAttribute("type","text/javascript")
        fileref.setAttribute("src", filename)
    }
    else if (filetype=="css"){ //if filename is an external CSS file
        var fileref=document.createElement("link")
        fileref.setAttribute("rel", "stylesheet")
        fileref.setAttribute("type", "text/css")
        fileref.setAttribute("href", filename)
    }
    if (typeof fileref!="undefined")
        document.getElementsByTagName("head")[0].appendChild(fileref)
}