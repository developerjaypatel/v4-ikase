var search_timeout_id;
var blnFocusing = false;
var blnSearched = false;
var kase_searching = false;
window.kase_nav_bar_view = Backbone.View.extend({

    initialize: function () {
		//console.log(this.model.toJSON());
    },
    render: function () {
		blnFocusing = false;
        kase_searching = false;
		
		$(this.el).html(this.template());
		
		setTimeout(function() {
			if (user_data_path == "A1") {
				if (user_worker.get("job")=="Partner" || user_worker.get("user_id")==916) {
					$(".partner_calendar_holder").show();
				}
			}
		}, 2000);
		
		//hide webmail if they have not entered email settings		
		setTimeout(function() {
			var email = new Email({user_id: login_user_id, email_id: 'all5'});
			email.fetch({
				success: function (email) {
					if (email.get("email_pwd")=="") {
						if (email.get("email_name").indexOf("@gmail.com") < 0) {
							//we will assume for now that if there is no password, no webmail
							$(".webmail_menu").hide();
						}
					} else {
						//we can get webmail, let's check it when checking inbox
						//this is defaulted on app.js
						//blnReceiveWebmail = true;
					}
				}
			});
				
		}, 1000);
	
        return this;
    },
    events: {
		"click #search_filter":			"showModifiers",
        "dblclick #srch-term": 			"showAll",
		"keyup #srch-term": 			"scheduleSearch",
        "keypress #srch-term": 			"onkeypress",
		"click .search_modifier":		"focusSearch",
		"click #logoutLink": 			"logOut",
		"click #compose_message": 		"newMessage",
		"click #compose_clients": 		"newClientMessage",
		"click #compose_task": 			"newTask",
		"click #new_import_indicator":	"listImports",
		"click #new_my_document":	    "listMydocuments",
		"click #unassigned_indicator":	"listUnassigned",
		"click #list_chat":				"answerChat",
		"click #new_chat":				"newChat",
		"click #new_phone_message":		"newPhone",
		"click #letters":				"listLetters",
		"click #new_kase":				"newCase",
		"click #clear_search":			"clearSearch",
		"click #new_phone_indicator":	"showPhoneInbox",
		"click #eams_import":			"importEams",
		"click #new_chat_indicator":	"openChat",
		"click #search_kases":			"searchAdvancedCase",
		"click #refresh_firm_calendar":	"refreshFirmCalendar",
		"click #label_search":			"Vivify",
		"click #srch-term":				"Vivify",
		"focus #srch-term":				"Vivify",
		"blur #srch-term":				"unVivify",
		"click #refresh_webmail":		"refreshWebmail"
	},
	refreshWebmail: function(event) {
		window.Router.prototype.loginEmail();
	},
	unVivify: function(event) {
		var textbox = $("#srch-term");
		var label = $("#label_search");
		
		if (textbox.val() == "") {
			label.animate({color: "#CCC", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#srch-term");
		var label = $("#label_search");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#notes_searchList').focus();
		}
	},
	importEams: function(event) {
		event.preventDefault();
		composeEamsImport();
	},
	newPhone: function(event) {
		event.preventDefault();
		composePhone();
	},
	showPhoneInbox: function(event) {
		event.preventDefault();
		document.location = "#phoneinbox";
	},
	showAllLetters: function(){
		var _alphabets = $('.alphabet > a');
		_alphabets.removeClass("active");
		var _contentRows = $('#kase_listing tbody tr');
		$("#kase_show_all").addClass("active");
		_contentRows.fadeIn(400);
	},
	refreshFirmCalendar: function() {
		clearStoredEvents(current_max_track_id, 0);
	},
	clearSearch: function() {
		kase_searching = false;
		var key = $('#srch-term').val();
		key = "";
		$('#srch-term').val(key);
		$("#search_results").html("");
		if ($('#content #list_kases_header').html() == "") {
			$("#search_results").html("");
			$('#content').html(new kase_listing_view({collection: kases, model: ""}).render().el);
			
		} else {
			$("#search_results").html("");
		}
		$("#search_open_cases").prop("checked", true);
		blnSearchingKases = false;
		this.showAllLetters();
		$("#srch-term").focus();
	},
	focusSearch: function(event) {
		blnFocusing = true;
		$("#srch-term").focus();
		showModifiers();
				
		if ($("#srch-term").val()!="") {
			//schedule a new search
			this.scheduleSearch(event);
		}
	},
	showModifiers: function() {
		if (blnFocusing == true) {
			blnFocusing = false;
			return;
		}
		$("#search_modifiers").fadeIn();
	},
	listLetters: function(event) {
		event.preventDefault();
		listLetters();
	},
	newMessage: function(event) {
		event.preventDefault();
		composeMessage();
	},
	newClientMessage: function(event) {
		event.preventDefault();
		composeClientMessage();
	},
	newTask: function(event) {
		event.preventDefault();
		composeTask();
	},
	listImports: function() {
		event.preventDefault();
		document.location.href = "#imports";
	},
	listUnassigned: function() {
		event.preventDefault();
		document.location.href = "#unassigneds";
	},
	newChat: function(event) {
		event.preventDefault();
		composeChat();
	},
	answerChat: function(event) {
		var self = this;

		//create a chat id and use
		var url = "api/chat/save";
		formValues = "chat_id=0";
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					var chat_id = data.chat_id;
					self.openChat(event, chat_id, "new");
				}
			}
		});
	},
	scheduleSearch: function(event) {
		var self = this;
		clearTimeout(search_timeout_id);
		
		if (kase_searching == true) {
			return;
		}
		
		search_timeout_id = setTimeout(function() {
			self.searchKases(event);
		}, 700);
	},
	searchKases: function (event) {
		kase_searching = true;
		blnSearched = true;
		$('#ikase_loading').html(loading_image);
		var self = this;
		var key = $('#srch-term').val();
		
		if (typeof key =="undefined") {
			return;
		}
		//look for modifiers
		var modifiers = $(".search_modifier");
		var search_started = false;
		modifiers.each(function() {
			if ($(this).prop("checked")) {
				var modifier_id = $(this)[0].id;
				modifier_id = modifier_id.replace("search_", "");
				modifier_id = modifier_id.replace("_cases", "");
				
				if (modifier_id != "open") {
					var my_kases = new KaseCollection();
					blnSearchingKases = true;
					search_kases = my_kases.searchDB(key, modifier_id);
					kase_searching = false;
					search_started = true;
					return;
				}
			}
		});
		if (search_started) {
			return;
		}
		if (this.model.length == 0) {
			this.model = kases.clone();
		}
		
		if (event.keyCode === 8) { // backspace key pressed
			if ($('#srch-term').val()=="") {
				$('#ikase_loading').html('');
				if ($("#list_kases_header").length==0 || $("#list_kases_header").parent().parent()[0].id=="search_results") {
					$("#search_results").html("");
				} else {
					$('#content').html(new kase_listing_view({collection: kases, model: ""}).render().el);
				}
				kase_searching = false;
				return;
			}
        }
		event.preventDefault();
        var key = $('#srch-term').val();
		key = key.toString().cleanString();
		$('#srch-term').val(key);
		if (key.length > 0) {
			if (key.length > 2) {
				//this.model.searchCollection(key)
				var my_kases = new KaseCollection();
								
				//may not have found anything				
				//let's do an actual search, it might be an old or closed record
				blnSearchingKases = true;
				search_kases = my_kases.searchDB(key);
				//I NEED A PROMISE HERE!!
				//if data is found, it will tack-on later on, messy for now
				$('#ikase_loading').html('');
				var mymodel = new Backbone.Model();
				mymodel.set("key", key);
				if ($("#list_kases_header").length==0 || $("#list_kases_header").parent().parent()[0].id=="search_results") {
					$('#search_results').html(new kase_listing_view({collection: my_kases, model: mymodel}).render().el);
					$("#search_results").css("top", "60px");
				} else {
					$('#content').html(new kase_listing_view({collection: my_kases, model: mymodel}).render().el);
				}
			}
			
			setTimeout(function() {
				$('#ikase_loading').html('');
				$("#kase_status_title").html("Found");
			}, 700);
			kase_searching = false;
			return;
			//give them a chance to type, so search only when they stopped typing
			/*
			CHECK LATER IF THIS IS ACTUALLY FASTER, THOUGH I DOUBT IT
			clearTimeout(search_timeout_id);
			search_timeout_id = setTimeout(function() {
				self.model.findByName(key);
				$('#content').html(new kase_listing_view({model: self.model}).render().el);
			}, 500);
			*/
			
			//let's use underscore to find the right records
			/*
			var my_kases = kases.filter(function(kase){ 
				var blnReturn;
				var first_name = kase.get('first_name').toLowerCase();
				blnReturn = (first_name.indexOf(key) > -1);
				return blnReturn;
			});
			
			var key = key.toLowerCase();
			blnFound = false;
			var my_kases = new KaseCollection();
			_.each( kases.toJSON(), function(kase) {
				//if (kase.first_name.toLowerCase().indexOf(key) > -1 || kase.last_name.toLowerCase().indexOf(key) > -1 || kase.employer.toLowerCase().indexOf(key) > -1) {
				if (_.values(kase).toString().toLowerCase().indexOf(key) > -1) {
					my_kases.add(kase);
				}
			});
			
			$('#content').html(new kase_listing_view({model: my_kases}).render().el);
			*/
		} else {
			kase_searching = false;
			$('#ikase_loading').html('');
			var mymodel = new Backbone.Model();
			mymodel.set("key", "");
			$('#content').html(new kase_listing_view({collection: kases, model: mymodel}).render().el);
		}
    },
	showAll:function(event) {
		kase_searching = false;
		return;
		//turned off for now
		$('#ikase_loading').html(loading_image);
		$("#search_results").html("");
		event.preventDefault();
		//no current case
		current_case_id = -1;
		Backbone.history.navigate('#kases');
		$(document).attr('title', "Kases List");
		var mymodel = new Backbone.Model();
		$('#content').html(new kase_listing_view({collection: kases, model: mymodel}).render().el);
	},
    onkeypress: function (event) {
        if (event.keyCode === 13) { // enter key pressed
            event.preventDefault();
        }
    },
    select_menu: function(menuItem) {
        $('.nav li').removeClass('active');
        $('.' + menuItem).addClass('active');
    },
	newCase:function(event) {
		composeKase(event)
	},
	searchAdvancedCase:function(event) {
		composeKaseSearch(event);
	},
	openChat: function(event, chat_id, action) {
		var element = event.currentTarget;
		if (typeof chat_id == "undefined") {
			var chat_id = element.childNodes[0].id;
			chat_id = chat_id.split("_")[2];
		}
		if (typeof action == "undefined") {
			action = "";
		}
		var title = "Chat - " + chat_id;
		
		title += "&nbsp;<iframe src='templates/stopwatch.html' width='50px' height='22px' frameborder='0' scrolling='no' allowtransparency='1'></iframe>";
	
		$.jsPanel({
			id:			"chat_panel_" + chat_id,
			title:    	title,
			size:		{ width: "380", height: "480" },
			
			selector: 	"#content",
			position: 	"top right",
			controls: {
				maximize: "disable"
			},
			content: 	"<div id='chat_messages_" + chat_id + "' style='float:left; border:1px solid black; overflow-y:auto; height:350px; width:380px; padding-right:2px; padding-top:2px; font-size:1.2em; vertical-align:bottom'></div><div id='chat_content_" + chat_id + "'></div>",
			theme:    	"primary"
		});
		
		//move it over slightly if there is already one open
		//$(".jsPanel").css("margin-right", "-100px");
		if ($(".jsPanel").length > 1) {
			var currleft = $("#chat_panel_" + chat_id).css("left");
			var currtop = $("#chat_panel_" + chat_id).css("top");
			var newleft = ($(".jsPanel").length / 100) - 400;
			if ($(".jsPanel").length > 2) {
				if ($(".jsPanel").length > 3) {
					newleft = ($(".jsPanel").length / 100) - 400;
				} else {
					newleft = ($(".jsPanel").length / 100);
				}
			}
			var newtop = $(".jsPanel").length / 100;
			if ($(".jsPanel").length > 2) {
				if ($(".jsPanel").length > 3) {
					newtop = ($(".jsPanel").length / 100) + 320;
				} else {
					newtop = ($(".jsPanel").length / 100) + 320;
				}
			}
			
			$("#chat_panel_" + chat_id).css("left", newleft + Number(currleft.replace("px", "")));
			$("#chat_panel_" + chat_id).css("top", newtop + Number(currtop.replace("px", "")));
			$("#chat_panel_" + chat_id).css("margin-right", "10px");
		}
		$("#chat_panel_" + chat_id).on("jspanelbeforeclose", function (event, id) {
			if (chat_id == 0) {
				chat_id = $("#chat_id").val();
			}
			var url = "api/chat/save";
			//send one last chat announcing closing
			formValues = "chat_id=" + chat_id;
			formValues += "&chat_to=" + $(".chat_" + chat_id + " #chat_toInput").val();
			formValues += "&message=" + encodeURIComponent(login_username + " has left the chat");
		
			$.ajax({
				url:url,
				type:'POST',
				dataType:"json",
				data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						//clearTimeout(chat_timeout_id);
					}
				}
			});
			//and then close polling
			clearTimeout(chat_timeout_id);
		});
		
		if (chat_id > 0) {
			cancelFlashTitle();
			//start listening now
			clearTimeout(chat_timeout_id);
			chat_timeout_id = setTimeout(function(){
				getChat(chat_id);
			}, 2000);
		}
		var chat = new Chat({chat_id: chat_id});
		var from_id = $("#chat_indicator_" + chat_id).data("from-id");
		var from = $("#chat_indicator_" + chat_id).data("from");
		chat.fetch({
			success: function (chat) {
				chat.set("from_id", from_id);
				chat.set("from", from);
				$("#chat_content_" + chat_id).html(new multichat({model: chat}).render().el);
			}
		});
		if (action != "new") {
			$("#new_chat_indicator").html("");
			$("#new_chat_indicator").fadeOut();
		}
	},
	logOut: function(event) {
		event.preventDefault();
		document.location.href = "index.php?logout=";
		//set the current url
		//current_url = window.location.hash;
		//document.location.href = "#logout";
	},
	});