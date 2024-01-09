window.rolodex_listing_view = Backbone.View.extend({

    initialize:function () {
        //this.collection.on("change", this.render, this);
		//this.collection.on("add", this.render, this);
    },
	events: {
		"change #rolodex_typeFilter":				"filterContacts",
		"click #compose_message":					"newMessage",
		"click .letter_click":						"letterClick",
		"dblclick .letter_click":					"letterDblClick",
		"click #new_party":							"addParty",
		"click #new_person":						"addPerson",
		"click #rolodex_show_all":					"showAll",
		"click #rolodex_clear_search":				"clearSearch",
		"click .compose_new_envelope":				"newEnvelope",
		/*"click .list_kases":						"listKases",*/
		"click #label_search_rolodex":				"Vivify",
		"click #rolodex_searchList":				"Vivify",
		"focus #rolodex_searchList":				"Vivify",
		"blur #rolodex_searchList":					"unVivify",
		"change .relate_box":						"showRelateButton",
		"click #relate_roldex":						"relateParties"
	},
    render:function () {		
		var self = this;
		var contacts = this.collection.toJSON();
		var arrTypes = [];
		var contact_filter_options = "";
		var blnSkip = false;
		var arrContacts = [];
		var intCounter = 0;
		var show_contacts = new ContactCollection();
		_.each( contacts, function(contact) {
			contact.eams_warning = "";
			if (contact.partie_type.indexOf("eams") > -1) {
				contact.eams_warning = "<br /><span style='background:orange; color:black; font-size:0.7em'>EAMS companies cannot be edited.</span>";
			}
			
			//phone, cell and fax
			var phone = contact.phone;
			var employee_phone = contact.employee_phone;
			if (employee_phone!="" && phone == "") {
				phone = employee_phone;
				contact.phone = phone;
			}
			var cell = contact.cell_phone;
			var employee_cell = contact.employee_cell;
			if (employee_cell!="" && cell == "") {
				cell = employee_cell;
				contact.cell_phone = cell;
			}
			var fax = contact.fax;
			var employee_fax = contact.employee_fax;
			if (employee_fax!="" && fax == "") {
				fax = employee_fax;
				contact.fax = fax;
			}
			
			blnSkip = false;
			contact.id = intCounter;
			intCounter++;
			//are we dealing with eams company
			if (contact.partie_type.indexOf("eams_")==0) {
				//sometimes doubles from all eams lists
				if (arrContacts.indexOf(contact.aka) < 0) {
					arrContacts.push(contact.aka);
					show_contacts.add(contact);
				} else {
					//repeat eams
					//contacts.remove(contact);
					blnSkip == true;
				}
			}
			
			if (!blnSkip) {
				if (arrContacts.indexOf(contact.corporation_id) < 0) {
					arrContacts.push(contact.corporation_id);
					show_contacts.add(contact);
				}
				if (contact.partie_type!="") {
					if (arrTypes.indexOf(contact.partie_type) < 0) {
						arrTypes[arrTypes.length] = contact.partie_type;
						contact_filter_options +='\r\n<option value="' + contact.partie_type + '">' + contact.partie_type.capitalizeWords() + '</option>';
					}
				}
			}
		});
		contacts = show_contacts.toJSON();
		
		try {
			$(this.el).html(this.template({contacts: contacts, contact_filter_options: contact_filter_options}));
		}
		catch(err) {
			var view = "rolodex_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		setTimeout(function() {
			$("#search_rolodex_loading").hide();
			var current_letter = "";
			$(".letter_click").css("color","#000");
			$(".letter_click").css("background","#E2A624");
			$(".letter_click").css("cursor","context-menu");
			$(".letter_click").addClass("turned_off");
			$("#" + current_letter).css("cursor","pointer");
			_.each( contacts, function(contact) {
				//we might have a new letter
				var the_letter = contact.display_name.charAt(0);
				if (current_letter != the_letter) {
					current_letter = the_letter;
					if ( the_letter.toUpperCase() != the_letter.toLowerCase() ) {
						$("#" + current_letter).css("color","white");
						$("#" + current_letter).css("background","url(../../img/glass_modal_low.png)");
						//$("#" + current_letter).css("cursor","pointer");
						$("#" + current_letter).removeClass("turned_off");
					}
				}
			});
		}, 500);
		
		tableSortIt("rolodex_listing");
		
		setTimeout(function() {
			if (current_rolodex_search!="") {
				$("#rolodex_searchList").val(current_rolodex_search);
				$("#rolodex_searchList").trigger("keyup");
				
				current_rolodex_search = "";
			}
		}, 1234);
		
		return this;
    },
	showRelateButton: function(event) {
		$("#relate_roldex").fadeIn();
		
		//however, at min 2
		var relate_boxes = $(".relate_box");
		var arrLength = relate_boxes.length;
		var check_count = 0;
		blnDisabled = true;
		for (var i  = 0; i < arrLength; i++) {
			var relate_box = relate_boxes[i];
			if (relate_box.checked) {
				check_count++;
			}
			if (check_count > 1) {
				blnDisabled = false;
				break;
			}
		}
		
		document.getElementById("relate_roldex").disabled = blnDisabled;
	},
	relateParties: function(event) {
		$("#relate_roldex").hide();
		
		//however, at min 2
		var relate_boxes = $(".relate_box");
		var arrLength = relate_boxes.length;
		var check_count = 0;
		var main_id = -1;
		var main_person_corporation = "";
		var arrRelated = [];
		for (var i  = 0; i < arrLength; i++) {
			var relate_box = relate_boxes[i];
			if (relate_box.checked) {
				check_count++;
				if (check_count > 0) {
					var sub_id = relate_box.id.replace("relate_", "");
					var person_corporation = "";
					//embedded type+id
					if (sub_id.indexOf("P")==0) {
						person_corporation = "person";
					}
					if (sub_id.indexOf("C")==0) {
						person_corporation = "corporation";
					}
					
					var id = sub_id.replace("P", "").replace("C", "");
	
					if (check_count == 1) {
						main_id = id;
						main_person_corporation = person_corporation;
					}
					arrRelated.push({"related_id": id, "rolodex_type": person_corporation});
				}
			}
		}
		if (arrRelated.length > 0) {
			var related_json = JSON.stringify(arrRelated);
			//console.log(related_json);
			//return;
			var url = 'api/rolodex/relate';
			var formValues = "main_id=" + main_id;	//don't really need this concept anymore
			formValues += "&type=" + main_person_corporation;	//don't really need this concept anymore
			formValues += "&related=" + encodeURIComponent(related_json);
			
			$.ajax({
				url:url,
				type:'POST',
				dataType:"json",
				data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						//console.log(data.toJSON);
						for (var i  = 0; i < arrLength; i++) {
							var relate_box = relate_boxes[i];
							if (relate_box.checked) {
								var sub_id = relate_box.id.replace("relate_", "");
								$("#contact_name_holder_" + sub_id).css("background","green");
							}
						}
						
						setTimeout(function() {
							$(".contact_name_holder").css("background", "");
						}, 2500);
					}
				}
			});
		}
		
	},
	unVivify: function(event) {
		var textbox = $("#rolodex_searchList");
		var label = $("#label_search_rolodex");
		
		if (textbox.val() == "") {
			label.animate({color: "#999", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#rolodex_searchList");
		var label = $("#label_search_rolodex");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#notes_searchList').focus();
		}
	},
	newEnvelope: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		generateEnvelope(element.id);
	},
	filterContacts: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		filterIt(element, "rolodex_listing", "user");
	},
	newMessage: function(event) {
		event.preventDefault();
		composeMessage();
	},
	clearSearch: function() {
		$("#rolodex_searchList").val("");
		$( "#rolodex_searchList" ).trigger( "keyup" );
		$("#rolodex_typeFilter").html('<option value=""></option>');
		$(".letter_row").hide();
		$(".user_data_row").hide();
		$( "#rolodex_searchList" ).focus();
	},
	addParty: function(event) {
		event.preventDefault();
		document.location.href = "#newpartie";
	},
	
	addPerson: function(event) {
		event.preventDefault();
		document.location.href = "#rolodexperson/-1";
	},
	showAll: function(){
		var _alphabets = $('.alphabet > a');
		_alphabets.removeClass("active");
		var _contentRows = $('#rolodex_listing tbody tr');
		$("#rolodex_show_all").addClass("active");
		_contentRows.fadeIn(400);
	},
	letterDblClick: function(event) {
		var element = event.currentTarget;
		_text = $("#" + element.id).html();
		//show all for that letter
		var obj = document.getElementById("rolodex_searchList");
		obj.value = _text;
		scheduleFind(obj, 'rolodex_listing', 'contact', false, true);
	},
	letterClick: function(event) {
		var element = event.currentTarget;
		_text = $("#" + element.id).html();
		
		if ($("#" + element.id).hasClass("turned_off")) {
			return;
		}
		var _alphabets = $('.alphabet > a');
		var _contentRows = $('#rolodex_listing tbody tr');
		var _count = 0;
		_alphabets.removeClass("active");
		
		$("#" + element.id).addClass("active");
		
		_contentRows.hide();
		/*
		_contentRows.each(function (i) {
			var _cellText = $(this).children('td').eq(0).text();
			//if (RegExp('^' + _text).test(_cellText)) {
			if (_cellText.indexOf(_text) > -1) {
				_count += 1;
				$(this).fadeIn(400);
			}
		});
		*/
		$("." + element.id).fadeIn(400);
	}
});
function editRolodex(href) {
	current_rolodex_search = $("#rolodex_searchList").val();
	document.location.href = href;
}