//global collections
var search_kases;
var notes;
var noteKaseView;
var new_kase;
var new_applicant;
var new_partie;
var new_note;
var applicants;
var occurences;
var corporations;
var parties;
var recent_occurences;
var recent_tasks;
var kaseKAIView;
var partyKaseView;
var occurencesView;
var current_case_id;
var calendar_element;
var calendar_event;
var word_templates;
var kase_letters;
var workingWeekTimes = [
	'06:00am', '06:30am', '07:00am', '07:30am', '08:00am', '08:30am',
	'09:00am', '09:30am', '10:00am', '10:30am', '11:00am', '11:30am', 
	'12:00pm', '12:30pm', '13:00pm', '13:30pm', '14:00pm',
	'14:30pm', '15:00pm', '15:30pm', '16:00pm', '16:30pm',
	'17:00pm', '17:30pm', '18:00pm', '18:30pm', '19:00pm'
];
window.Router = Backbone.Router.extend({
    routes: {
        "": "home",
		"applicant/:id": 										"kaseApplicant",
		"compose": 												"newMessage",
		"documents/:case_id" : 									"listDocuments",
		"edit/:id": 											"editKase",
		"eams_forms/:case_id" : 								"listEams",
		"events": 												"listEvents",
		"firmkalendar":											"kaseCustomerEvents",
		"injury/:case_id" : 									"kaseInjury",
		"injury/:case_id/:injury_id" : 							"kaseSpecificInjury",
		"intakekalendar":										"kaseCustomerIntakes",
		"newinjury/:case_id":									"newInjury",
		"import" : 												"documentImport",
		"imports" : 											"listImports",
		"inbox": 												"listInbox",
		"kalendar/:id": 										"kaseEvents",		
		"kases/:id": 											"dashboardKase",
		"kases" : 												"listKases",
		"kases/events/edit/:case_id/:event_id/:day_date": 		"kaseEventDialog",
        "kases/kai/:id": 										"kaseKAI",
		"messages/:message_id": 								"editMessage",
		"kontrol_panel/:id": 									"showKontrolPanel",
		"kaseletters/:case_id":									"listKaseLetters",
		"letters/:case_id" : 									"listKaseLetters",
		"logout" : 												"logout",
		"notes/:case_id" : 										"listNotes",
		"notes/:case_id/:notes_id/:type" : 						"editNote",
		"outbox": 												"listOutbox",
		"parties/:id" : 										"listParties",
		"parties/:case_id/:id/:type" : 							"editPartie",
		"personalkalendar":										"kasePersonalEvents",
		"phone/:case_id/:event_id": 							"kasePhone",
		"prior_treatment/:case_id/:corporation_id/:person_id":	"priorTreatment",
		"rolodex/:id/:type" : 									"editRoloPartie",
		"rolodexperson/:id" : 									"editRoloPerson",
		"rolodex":												"listContacts",
		"settings": 											"listCustomerSetting",
		"taskinbox": 											"listTaskInbox",
		"taskoutbox": 											"listTaskOutbox",		
		"templates" : 											"listTemplates",
		"todo": 												"listEventsing",
		"usersettings": 										"listUserSetting",
		"upload/:id/:table_name": 								"documentUpload",
		"users" : 												"listUsers",
		"users/:id" : 											"editUser",
		"users/email/:user_id" : 								"userEmail"
	},
    initialize: function () {
		var self = this;
		readCookie();
		writeCookie('origin', '');
		
		// Close the search dropdown on click anywhere in the UI
		$('body').click(function () {
			$('.dropdown').removeClass("open");
		});
		$('.dropdown').click(function () {
			$('.dropdown-toggle').dropdown("toggle");										  
		});
		
		//navigation
		var search_kases = kases.clone();
		this.headerView = new kase_nav_bar_view({model: search_kases});
		$('.kase_header').html(this.headerView.render().el);								
		
		//left column navigation
		$('.left_sidebar').html(new kase_nav_left_view().render().el);								
		//self.kaseNavLeftView = new kase_nav_left_view();
		//$('.left_sidebar').html(self.kaseNavLeftView.render().el);
		
		//recent kases		
		$('#kases_recent').html(new kase_list_category_view({model: recent_kases}).render().el);
		$('#occurences_recent').html(new kase_list_task_view({model: recent_tasks}).render().el);
		$('#left_sidebar').hide();
		$("#search_results").removeClass("col-md-10");
		$("#search_results").addClass("col-md-12");
		$("#content").removeClass("col-md-10");
		$("#content").addClass("col-md-12");
		$("#left_side_show").show();
		
		//recent events
		setTimeout(function() {
			self.recentOccurences();
		}, 500);
		setTimeout(function() {
			self.recentTasks();
		}, 500);
		
		//start polling inbox
		checkChat();
		//slight delay
		setTimeout(function() {
			checkInbox();
		}, 1300);
		
		//slight delay
		setTimeout(function() {
			checkTaskInbox();
		}, 1700);
    },
	change: function(trigger, args) { 
		//alert("closed for move");
		//document.location.href = "index.html";
		//this happens everytime router is invoked
		/*var routeData = trigger.split(":");
		var self = this;
		if (routeData[0] === "route") {
		   // do whatever here.  
		   // routeData[1] will have the route name
		   self.execute();
		}*/
		executeMainChanges();
	},
	
    home: function () {
		current_case_id = -1;
		readCookie();
		var self = this;
		executeMainChanges();
		
		// Since the home view never changes, we instantiate it and render it only once
        /*
		if (!this.kaseHomeView) {
            this.kaseHomeView = new kase_home_view();
            this.kaseHomeView.render();
        } else {
            this.kaseHomeView.delegateEvents(); // delegate occurences when the view is recycled
        }
		*/
		//content
		//$("#content").html(this.kaseHomeView.el);
		$(document).attr('title', "Welcome to iKase");
		/*recent_kases.fetch({
			success: function(recent_kases) {
				$('#search_results').html(new kase_listing_view({collection: recent_kases}).render().el);
				return;
			}
		});
		
		$("#search_results").removeClass("col-md-10");
		$("#search_results").addClass("col-md-12");
		$("#content").removeClass("col-md-10");
		$("#content").addClass("col-md-12");
		*/
		$('#left_sidebar').hide();
		$('#content').html(new dashboard_home_view().render().el);
		$("#left_side_show").show();
		
		//nav
		setTimeout(function() {
			self.headerView.select_menu('home-menu');
		}, 300);
    },
	documentImport: function() {
		executeMainChanges();
		current_case_id = -1;
		$('#content').html(new import_view().render().el);
		$("#content").addClass("glass_header_no_padding");
	},
	documentUpload:  function (case_id, table_name) {
		current_case_id = case_id;
		executeMainChanges();
		readCookie();
		var self = this;
		
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			var kase = new Kase();
			kase.set("id", $(".kase #table_id").val());
			kase.fetch({
				success: function(kase) {
					kases.add(kase);
					self.documentUpload(case_id);
				}
			});
		}
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		var mymodel = kases.findWhere({case_id: case_id});
		mymodel.set({table_name: table_name});
		// Since the home view never changes, we instantiate it and render it only once
		this.DocumentUploadView = new document_upload_view({model:mymodel});
		this.DocumentUploadView.render();

		$("#upload_documents").html(this.DocumentUploadView.el);
	},
	editKase: function (case_id) {
		current_case_id = case_id;
		
		readCookie();
		var self = this;
		
		var kase = kases.findWhere({case_id: case_id});
		kase.set("header_only", true);
		$('#content').html(new kase_view({model: kase}).render().el);			
		//content
		$("#kase_content").html(new kase_edit_view({model: kase}).render().el);
	},
	dashboardKase: function (case_id) {
		current_case_id = case_id;
		executeMainChanges();
		readCookie();
		var self = this;
		
		if (case_id > -1) {
			var kase = kases.findWhere({case_id: case_id});
			if (typeof kase == "undefined") {
				var kase = new Kase();
				kase.set("id", case_id);
				kase.fetch({
					success: function(kase) {
						kases.add(kase);
						self.dashboardKase(case_id);
					}
				});
				return;
			}
			//perform an ajax call to track views by current user
			var url = 'api/kase/view';
			formValues = "id=" + case_id;
	
			$.ajax({
				url:url,
				type:'POST',
				dataType:"json",
				data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						self.recentKases();
					}
				}
			});
			
			$(document).attr('title', kase.get("case_number") + " :: " + kase.get("full_name") + " vs " + kase.get("employer") + " :: iKase");
			if (typeof kase == "undefined") {
				//case does not exist, get out
				document.location.href = "#";
				return;
			}
			//var kase =  new Kase({id: id});
			var applicant_id = kase.get("applicant_id");
			//we have a kase, do we have an applicant yet
			if (applicant_id==null) {
				var applicant_id = -1;
			}
		} else {
			$(document).attr('title', "New Kase :: iKase");
			//no kase, no applicant
			var kase = new Kase();
			case_number_next = String(customer_settings.get("case_number_next"));
			if (customer_settings.get("case_number_next") < 10) {
				case_number_next = "000" + case_number_next;
			}
			if (customer_settings.get("case_number_next") < 100) {
				case_number_next = "00" + case_number_next;
			}
			if (customer_settings.get("case_number_next") < 1000) {
				case_number_next = "0" + case_number_next;
			}
			kase.set("case_number", customer_settings.get("case_number_prefix") + case_number_next);
			var applicant_id = -1;
		}
		kase.set("applicant_id", applicant_id);
		//we need to set up the kase view and subviews
		var type = "";
		parties = new Parties([], { case_id: case_id, case_uuid: kase.get("uuid"), panel_title: "Dashboard" });
		parties.fetch({
			success: function(parties) {
				//if we have no parties (except for venue, always venue with new kase) 
				//and no applicant
				var blnNoApplicant = false;
				if (parties.length > 0) {
					blnNoApplicant = (parties.toJSON()[0].type!="applicant");
				}
				if ((parties.length < 2 && case_id > 0) || blnNoApplicant) {
					kase.set("header_only", true);
					$('#content').html(new kase_view({model: kase}).render().el);
					self.kaseApplicant(case_id);
					return;
				}
				if (parties.length == 2 && case_id > 0) {
					//is the first partie the applicant?  the second is always the venue
					if (parties.toJSON()[0].type=="applicant" && parties.toJSON()[1].type=="venue") {
						//we must have the employer next, at least for WCAB
						self.editPartie(case_id, -1, "employer");
						//$("#content").removeClass("glass_header_no_padding");
						//$('#content').html(new kase_view({model: kase}).render().el);
						hideEditRow();
						return;
					}
				}
				if (parties.length == 2 && case_id > 0) {
					//is the first partie the applicant?  the second is always the venue
					if (parties.toJSON()[0].type=="applicant" && parties.toJSON()[1].type=="venue") {
						//we must have the employer next, at least for WCAB
						self.editPartie(case_id, -1, "employer");
						//$("#content").removeClass("glass_header_no_padding");
						//$('#content').html(new kase_view({model: kase}).render().el);
						hideEditRow();
						return;
					}
				}
				if (parties.length > 2 && case_id > 0) {
					//we have applicant, venue and 1 partie, we need employer
					employer_partie = parties.findWhere({"type": "employer"});
					
					if (typeof employer_partie == "undefined") {
						self.editPartie(case_id, -1, "employer");
						return;
					}
					
					//we have employer, we need injury
					var kase_dois = new KaseInjuryCollection({case_id: case_id});
					kase_dois.fetch({
						success: function(kase_dois) {
							if (kase_dois.length == 0) {
								self.newInjury(case_id);
								return;
							} else {
								var doi = kase_dois.toJSON()[0];
								if (doi.start_date=="0000-00-00") {
									self.kaseSpecificInjury(case_id, doi.injury_id);
									return;
								}
							}
						}
					});
				}
				if (parties.length > 0 || case_id==-1) {
					kase.set("header_only", true);
				}
				$("#content").removeClass("glass_header_no_padding");
				$('#content').html(new kase_view({model: kase}).render().el);
				//$("#kase_content").html("<table align='center'><tr><td align='center' style=''><i class='icon-spin4 animate-spin' style='font-size:6em; color:white'></i><td><tr></table");
				if (parties.length == 0) {
					//we don't want the dashboard for a new kase without parties
					return;
				}
				
				//remove Venue from dashboard
				if (parties.panel_title=="Dashboard") {
					venue_partie = parties.findWhere({"type": "venue"});
					parties.remove(venue_partie);
				}
				//cards view
				
				$('#kase_content').html(new partie_cards_view({collection: parties, model: kase}).render().el);
				$("#kase_content").removeClass("glass_header_no_padding");
				hideEditRow();
			}
		});
		self.recentKases();	
    },
	newKase: function () {
		current_case_id = -1;
		readCookie();
		var self = this;
		var blnProceed = true;
		
		
		var kase = new Kase();
		kase.set("header_only",true);
		//we need to set up the kase view and subviews
		$("#content").removeClass("glass_header_no_padding");
		$('#content').html(new kase_view({model: kase}).render().el);
    },
	listKases: function () {
		executeMainChanges();
		current_case_id = -1;
		readCookie();
		$(document).attr('title', "Kases List");
		$('#content').html(new kase_listing_view({collection: kases, model:""}).render().el);
    },
	kasePhone: function (case_id, event_id) { 
		current_case_id = case_id;
		
		var self = this;
		var kase = kases.findWhere({case_id: case_id});
		kase.set("header_only", true);
		$('#content').html(new kase_view({model: kase}).render().el);
		setTimeout(function() {
			self.kaseEventTypeDialog(case_id, event_id, "phone_call");
		}, 100);
	},
	listInbox: function () { 
		executeMainChanges();
		current_case_id = -1;
		readCookie();
		
		//fetch all messages
		messages = new InboxCollection();
		messages.fetch({
			success: function (data) {
				$(document).attr('title', "Inbox :: iKase");
				var message_listing_info = new Backbone.Model;
				message_listing_info.set("title", "Inbox");
				message_listing_info.set("first_column_label", "From");
				message_listing_info.set("receive_label", "Received");
				$('#content').html(new message_listing({collection: data, model: message_listing_info}).render().el);
				$("#content").removeClass("glass_header_no_padding");
			}
		});			
	},
	listOutbox: function () { 
		executeMainChanges();
		current_case_id = -1;
		readCookie();
		
		//fetch all messages
		messages = new OutboxCollection();
		messages.fetch({
			success: function (data) {
				$(document).attr('title', "Outbox :: iKase");
				var message_listing_info = new Backbone.Model;
				message_listing_info.set("title", "Outbox");
				message_listing_info.set("receive_label", "Sent");
				message_listing_info.set("first_column_label", "To");
				$('#content').html(new message_listing({collection: data, model: message_listing_info}).render().el);
				$("#content").removeClass("glass_header_no_padding");
			}
		});			
	},
	listTaskInbox: function () { 
		executeMainChanges();
		current_case_id = -1;
		readCookie();
		
		//fetch all tasks
		tasks = new TaskInboxCollection();
		tasks.fetch({
			success: function (data) {
				$(document).attr('title', "Task Inbox :: iKase");
				var task_listing_info = new Backbone.Model;
				task_listing_info.set("title", "Task Inbox");
				task_listing_info.set("receive_label", "Due Date");
				$('#content').html(new task_listing({collection: data, model: task_listing_info}).render().el);
				$("#content").removeClass("glass_header_no_padding");
			}
		});			
	},
	listCustomerSetting: function () { 
		current_case_id = -1;
		readCookie();
		
		//fetch all settings
		refreshCustomerSettings();			
	},
	listUserSetting: function () { 
		current_case_id = -1;
		readCookie();
		
		refreshUserSettings();			
	},
	listTaskOutbox: function () {
		executeMainChanges();
		current_case_id = -1;
		readCookie();
		
		//fetch all tasks
		tasks = new TaskOutboxCollection();
		tasks.fetch({
			success: function (data) {
				$(document).attr('title', "Task Outbox :: iKase");
				var task_listing_info = new Backbone.Model;
				task_listing_info.set("title", "Task Outbox");
				task_listing_info.set("receive_label", "Due");
				$('#content').html(new task_listing({collection: data, model: task_listing_info}).render().el);
				$("#content").removeClass("glass_header_no_padding");
			}
		});			
	},
	editMessage: function (message_id) { 
		current_case_id = -1;
		
		readCookie();
		
		if (typeof message_id == "undefined" || message_id=="" || message_id<0) {
			return false;
		}
		
		message = new Message({message_id: message_id});
		$(document).attr('title', "Interoffice :: iKase");		
		message.fetch({
			success: function (data) {
				message.set("gridster_me", true);
				$('#content').html(new interoffice_view({model: message}).render().el);
				$("#content").removeClass("glass_header_no_padding");
			}
		});	
	
	},
	newMessage: function () { 
		current_case_id = -1;
		readCookie();
		
		if (typeof message_id == "undefined" || message_id=="") {
			return false;
		}
		
		message = new Message({message_id: -1});
		$(document).attr('title', "Interoffice :: iKase");
		$("#input_for_checkbox").show();
		$("#myModalLabel").html("New Interoffice");
		$("#modal_save_holder").html('<a title="Send Message" class="interoffice save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-send" style="color:#000066; font-size:20px">&nbsp;</i></a>');
		$("#myModalBody").html(new message_view({model: message}).render().el)
		
	},
	kaseEventDialog: function (case_id, event_id, day_date) {
		current_case_id = case_id;

		readCookie();
		var self = this;
		
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		//console.log(kase.get("full_name"));
		occurences = new OccurenceCollection({case_id: case_id});
		occurences.fetch({
				success: function(data) {
					if (event_id > -1) {
						var occurence = occurences.get(event_id);
					} else {
						//no kase, no applicant
						var occurence = new Occurence({case_id: case_id});
					}
					occurence.set("case_number", kase.get("case_number"));
					occurence.set("case_id",case_id);
					occurence.set("case_uuid", kase.get("uuid"));
					occurence.set("gridster_me",true);
					if (kase.get("full_name") != "") {
						occurence.set("title", kase.get("full_name") + " vs " + kase.get("employer"));
						
					} else {
						occurence.set("title", kase.get("full_name") + " vs " + kase.get("employer"));
						
					}
					var event_dateandtime = moment.utc(Number[day_date]);
					var d = new Date(); 
					d.setTime(day_date);
					moment(d).format("MM/DD/YYYY");
					occurence.set("event_dateandtime", d);
					//empty the content holder
					$("#kase_content").html("&nbsp;");
					//then re-assign to calendar
					var occurenceDialogView = new dialog_view({el: $("#kase_content"), collection: occurences, model:occurence}).render();
				}
		});
	},
	kaseEventTypeDialog: function (case_id, event_id, event_kind) {
		current_case_id = case_id;
		
		readCookie();
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		occurences = new OccurenceCollection({case_id: case_id});
		occurences.fetch({
				success: function(data) {
					if (event_id > -1) {
						var occurence = occurences.get(event_id);
					} else {
						//no kase, no applicant
						var occurence = new Occurence({case_id: case_id});
					}
					occurence.set("case_id", case_id);
					occurence.set("event_kind", event_kind);
					if (event_kind == "phone_call" && event_id==-1) {
						if (kase.get("full_name") != "") {
							occurence.set("title", kase.get("employer"));
						} else {
							occurence.set("title", kase.get("employer"));
						}
						occurence.set("id", -1);
						occurence.set("event_dateandtime", moment().format("MM/DD/YYYY hh:mm:ss a"));
						occurence.set("event_name", login_username);
					}
					if (kase.get("full_name") != "") {
						occurence.set("title", kase.get("employer"));
					} else {
						occurence.set("title", kase.get("employer"));
					}
					occurence.set("case_number", kase.get("case_number"));
					occurence.set("case_uuid", kase.get("uuid"));
					occurence.set("gridster_me",true);
					//empty the content holder
					$("#kase_content").html("&nbsp;");
					//then re-assign to calendar
					var occurenceDialogView = new dialog_view({el: $("#kase_content"), collection: occurences, model:occurence}).render();
				}
		});
	},
	kaseEvents: function (case_id) {		
		current_case_id = case_id;
		executeMainChanges();
		readCookie();
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		//clean up button
		$("button.calendar").fadeOut(function() {
				$("button.information").fadeIn();
			});
		kase.set("header_only", true);
		$('#content').html(new kase_view({model: kase}).render().el);
		//function below is in event_module.js
		renderCalendar(kase);
	},
	kaseCustomerEvents: function () {		
		executeMainChanges();
		readCookie();
		var self = this;
		
		
		showKalendar('month');
	},
	kaseCustomerIntakes: function () {		
		executeMainChanges();
		readCookie();
		var self = this;
		
		
		var all_customer_intakes = new CustomerIntakeCollection();
		all_customer_intakes.fetch({
			success: function (data) {
				//then re-assign to calendar
				$("#content").html("");
				occurencesView = new intake_cus_occurences_view({el: $("#content"), collection: all_customer_intakes, model:data}).render();			
			}
		});
	},
	kasePersonalEvents: function () {
		executeMainChanges();
		readCookie();
		var self = this;	
		var all_customer_events = new OccurenceCustomerCollection();
		all_customer_events.fetch({
			success: function (data) {
				var personal_events = new OccurenceCustomerCollection(); 
				_.each(data.toJSON(), function(customer_event) {
					var blnPersonalFrom = false;
					blnPersonalFrom = (customer_event.event_from==login_username);
					blnPersonalAssignee = (customer_event.assignee.indexOf(login_nickname) > -1);	
					
					//found a match if it was from me, or assigned to me
					if (blnPersonalAssignee || blnPersonalFrom) {
						personal_events.add(customer_event);
					}
				});
				
				//then re-assign to calendar				
				$("#content").html("");
				occurencesView = new kase_personal_occurences_view({el: $("#content"), collection: personal_events}).render();
				
			}
		});
	},
	kaseKAI: function (case_id) {
		executeMainChanges();
		current_case_id = case_id;
		
		readCookie();
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		var applicant = new Applicant({id: kase.get("applicant_id")});
		applicant.fetch({
			success: function (data) {
				data.set("case_id", kase.get("case_id"));
				data.set("case_uuid", kase.get("uuid"));
				
				kaseKAIView = new kai_view({el: $("#kase_content"), model:data}).render();
				$("#kase_content").addClass("glass_header_no_padding");
			}
		});
	},
	kaseApplicant: function (case_id) {		
		current_case_id = case_id;
		executeMainChanges();
		readCookie();
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			var kase = new Kase();
			kase.set("id", case_id);
			kase.fetch({
				success: function(kase) {
					kases.add(kase);
					self.kaseApplicant(case_id);
				}
			});
			return;
		}
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		if (kase.get("applicant_id") == null) {
			kase.set("applicant_id", -1);
		}
		var applicant = new Person({id: kase.get("applicant_id")});
		applicant.fetch({
			success: function (data) {
				var prefix = "";
				if (data.id>-1) {
					kase.set("header_only", true);
					$('#content').html(new kase_view({model: kase}).render().el);
					prefix = "kase_";
				}
				data.set("case_id", kase.get("case_id"));
				data.set("case_uuid", "");
				data.set("gridster_me", true);
				
				the_applicant = new dashboard_person_view({el: $("#" + prefix + "content"), model:data}).render();
				/*
				if (data.id>-1) {
					the_applicant = new dashboard_person_view({el: $("#kase_content"), model:data}).render();
				} else {
					the_applicant = new dashboard_person_view({el: $("#content"), model:data}).render();
				}
				*/
			}
		});
		self.recentKases();
	},
	kaseInjury: function (case_id) {		
		current_case_id = case_id;
		readCookie();
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		kase.set("header_only", true);
		kase.set("new_injury", false);
		$('#content').html(new kase_view({model: kase}).render().el);
		$('#kase_content').html(new dashboard_injury_view({model: kase}).render().el);
		
		self.recentKases();
	},
	showKontrolPanel: function (case_id) {		
		current_case_id = case_id;
		readCookie();
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		kase.set("header_only", true);
		$('#content').html(new kase_view({model: kase}).render().el);
		$('#kase_content').html(new kase_control_panel({model: kase}).render().el);
		
		self.recentKases();
	},
	kaseSpecificInjury: function (case_id, injury_id) {		
		current_case_id = case_id;
		executeMainChanges();
		readCookie();
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		kase.set("header_only", true);
		kase.set("new_injury", false);
		kase.set("injury_id", injury_id);
		$('#content').html(new kase_view({model: kase}).render().el);
		$('#kase_content').html(new dashboard_injury_view({model: kase}).render().el);
	},
	newInjury: function (case_id) {		
		current_case_id = case_id;
		executeMainChanges();
		readCookie();
		var self = this;
		
		kases.fetch({
			success: function (data) {
				//get the kase
				var kase = data.findWhere({case_id: case_id});
				if (typeof kase == "undefined") {
					//case does not exist, get out
					document.location.href = "#";
					return;
				}
				kase.set("header_only", true);
				kase.set("new_injury", true);
				$('#content').html(new kase_view({model: kase}).render().el);
				$('#kase_content').html(new dashboard_injury_view({model: kase}).render().el);
				$("#content").removeClass("glass_header_no_padding");
			}
		});

	},
	listParties: function (case_id) {	
		current_case_id = case_id;
		executeMainChanges();
		readCookie();
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		var type = "";
		//parties = new Corporations([], { case_id: id });
		parties = new Parties([], { case_id: case_id, panel_title: "Parties" });
		parties.fetch({
			success: function(data) {
				if ($('#kase_content').length == 0) {
					kase.set("header_only", true);
					$('#content').html(new kase_view({model: kase}).render().el);
				}
				if (parties.length == 0) {
					self.editPartie(case_id, -1, "new");
					return;
				}
				$('#kase_content').html(new partie_cards_view({collection: data, model: kase}).render().el);
				$("#kase_content").removeClass("glass_header_no_padding");
				
				hideEditRow();
			}
		});	
	},
	listImports: function () {	
		current_case_id = -1;	
		readCookie();
		var self = this;
		//initial call
		var type = "";
		stacks = new Stacks([]);
		stacks.fetch({
			success: function(data) {
				$(document).attr('title', "Imports :: iKase");
				$('#content').html(new stack_listing_view({collection: data, model: new Backbone.Model}).render().el);
				$("#content").removeClass("glass_header_no_padding");
				hideEditRow();
			}
		});	
	},
	listDocuments: function (case_id) {	
		executeMainChanges();
		readCookie();
		var self = this;
		
		refreshDocuments(case_id);	
	},
	listTemplates: function (case_id) {	
		if (typeof case_id != "undefined") {
			current_case_id = case_id;	
		}
		
		readCookie();
		var self = this;
		
		word_templates = new WordTemplates([]);
		word_templates.fetch({
			success: function(data) {
				var empty_model = new Backbone.Model;
				empty_model.set("case_id", "-1");
				empty_model.set("uuid", "templates");
				empty_model.set("no_uploads", false);
				$('#content').html(new template_listing_view({collection: data, model: empty_model}).render().el);
				$("#content").removeClass("glass_header_no_padding");
				hideEditRow();
			}
		});	
	},
	listNotes: function (case_id) {	
		executeMainChanges();
		current_case_id = case_id;	
		
		readCookie();
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		var type = "";
		
		notes = new NoteCollection([], { case_id: case_id });
		kase.set("header_only", true);
		$('#content').html(new kase_view({model: kase}).render().el);
		notes.fetch({
			success: function(data) {
				var note_list_model = new Backbone.Model;
				note_list_model.set("display", "full");
				note_list_model.set("partie_type", "note");
				note_list_model.set("partie_id", -1);
				$('#kase_content').html(new note_listing_view({collection: data, model: note_list_model}).render().el);
				$("#kase_content").removeClass("glass_header_no_padding");
				hideEditRow();
			}
		});	
			
	},
	listLetters: function (case_id) {
		executeMainChanges();
		current_case_id = case_id;
		
		readCookie();
		$(document).attr('title', "Letters");
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		var type = "";
		word_templates = new WordTemplates([]);
		word_templates.fetch({
			success: function(data) {
			}
		});
		letters = new KaseLetters([], { case_id: case_id });
		letters.fetch({
			success: function(data) {
				if ($('#kase_content').length == 0) {
					kase.set("header_only", true);	
					$('#content').html(new kase_view({model: kase}).render().el);
				}
				kase.set("no_uploads", true);
				$('#kase_content').html(new letter_listing_view({collection: data, model: kase}).render().el);
				$("#kase_content").removeClass("glass_header_no_padding");
				
				hideEditRow();
			}
		});
	},
	listKaseLetters: function (case_id) {
		executeMainChanges();
		current_case_id = case_id;
		
		readCookie();
		$(document).attr('title', "Letters");
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		var type = "";
		blnWordTemplates = false;
		
		if (typeof word_templates == "undefined") {
			word_templates = new WordTemplates([]);
			word_templates.fetch({
				success: function(data) {
					blnWordTemplates = true;
					self.listKaseLetters(case_id);
					return;
				}
			});	
		} else {
			blnWordTemplates = true;
		}
		if (!blnWordTemplates) {
			return;
		}
		kase_letters = new KaseLetters([], { case_id: case_id });
		kase_letters.fetch({
			success: function(data) {
				if ($('#kase_content').length == 0) {
					kase.set("header_only", true);	
					$('#content').html(new kase_view({model: kase}).render().el);
				}
				kase.set("no_uploads", true);
				$('#kase_content').html(new kase_letter_listing_view({collection: word_templates, model: kase}).render().el);
				$("#kase_content").removeClass("glass_header_no_padding");
				
				hideEditRow();
			}
		});
	},
	listEams: function (case_id) {
		executeMainChanges();
		current_case_id = case_id;
		
		readCookie();
		$(document).attr('title', "Eams");
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		var type = "";
		eamss = new KaseEams([], { case_id: case_id });
		
		//eamss.fetch({
		//	success: function(eamss) {
				if ($('#kase_content').length == 0) {
					kase.set("header_only", true);	
					$('#content').html(new kase_view({model: kase}).render().el);
				}
				kase.set("no_uploads", true);
				eamses = new FormCollection();
				eamses.fetch({
					success: function(eamses) {
						$('#kase_content').html(new form_listing({collection: eamses, model: kase}).render().el);
						$("#kase_content").removeClass("glass_header_no_padding");
					}
				});
				
				hideEditRow();
		//	}
		//});
		/*
		eamss = new EamsCollection();
		eamss.fetch({
			success: function(data) {
				$(document).attr('title', "Eams :: iKase");
				$('#content').html(new eams_listing_view({collection: data}).render().el);
				$("#content").removeClass("glass_header_no_padding");
				hideEditRow();
			}
		});	
		*/	
	},
	editNote: function (case_id, notes_id) {	
		current_case_id = case_id;
			
		readCookie();
		var self = this;
		
		//get the kase if url direct referrer
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		//initial call
		var type = "";
		if (notes_id=="") {
			notes_id = -1;
		} 
		var note = new Note({notes_id: notes_id, case_id: case_id});
		note.fetch({
			success: function (data) {
				//alert(notes_id);
				//data.set("case_id", case_id);
				data.set("case_uuid", kase.get("uuid"));
				noteKaseView = new notes_view({el: $("#kase_content"), model:data}).render();
				$("#kase_content").addClass("glass_header_no_padding");
				showEditRow();
			}
		});
			
	},
	listUsers: function () {
		current_case_id = -1;
		executeMainChanges();		
		readCookie();
		var self = this;
		
		//initial call
		var type = "";
		users = new UserCollection([]);
		users.fetch({
			success: function(data) {
				$(document).attr('title', "Users :: iKase");
				$('#content').html(new user_listing_view({model: data}).render().el);
				$("#content").removeClass("glass_header_no_padding");
				hideEditRow();
			}
		});				
	},
	listContacts: function () {
		current_case_id = -1;
		executeMainChanges();		
		readCookie();
		var self = this;
		
		//initial call
		var type = "";
		contacts = new ContactCollection([]);
		contacts.fetch({
			success: function(data) {
				$(document).attr('title', "Contacts :: iKase");
				$('#content').html(new rolodex_listing_view({collection: data}).render().el);
				$("#content").removeClass("glass_header_no_padding");
				hideEditRow();
			}
		});				
	},
	editUser: function (user_id) {		
		current_case_id = -1;
		readCookie();
		var self = this;
		
		if (user_id=="") {
			user_id = -1;
		}
		$(document).attr('title', "User :: iKase");
		$('#content').html(new dashboard_user_view({model: {user_id: user_id}}).render().el);
	},
	userEmail: function (user_id) {	
		current_case_id = -1;	
		readCookie();
		var self = this;
		
		if (user_id=="") {
			user_id = -1;
		} 
				//let's get the specific corporation by id.  if id < 0, we will get an empty corporation
		var email = new Email({user_id: user_id});
		email.fetch({
			success: function (email) {
				email.set("gridster_me", true);
				$('#user_panel').html(new email_view({model: email}).render().el);				
				$("#user_panel").addClass("glass_header_no_padding");
			}
		});	
	},
	priorTreatment: function (case_id, corporation_id, person_id) {
		var corporation_type = "medical_provider";
		$("#gifsave").hide();
		if (case_id == -1 && corporation_id == -1 && corporation_type == 'new') {
		//	this.newPartie();
		//	return;
		}
		current_case_id = case_id;
			
		readCookie();
		var self = this;
	
		if (case_id > 0) {
			//get the kase
			var kase = kases.findWhere({case_id: case_id});
			if (typeof kase == "undefined") {
				//case does not exist, get out
				document.location.href = "#";
			}
			case_id = kase.get("case_id");
			uuid = kase.get("case_uuid");
		} else {
			uuid = "";
		}
		//let's get the specific corporation by id.  if id < 0, we will get an empty corporation
		var corporation = new Corporation({id: corporation_id, case_id: case_id, type:corporation_type});
		corporation.fetch({
			success: function (corp) {
				var blnShowHeader = true;
				if (case_id > 0) {
					//only ids for active cases would get a header
					if (blnShowHeader) {
						if ($('#kase_content').length == 0) {
							kase.set("header_only", true);
							$('#content').html(new kase_view({model: kase}).render().el);
						}
					}
				}
				//if this is a new partie, the query will return partie_type values
				corp.set("partie", corporation_type.capitalize());
				corp.set("case_id", case_id);
				corp.set("person_id", person_id);
				corp.set("case_uuid", uuid);
				corp.set("gridster_me", true);
				corp.set("show_buttons", true);
				corp.adhocs.fetch({
					success:function (data) {
						if (data.toJSON().length==0) {
							data = new AdhocCollection([], {case_id: case_id, corporation_id: corporation_id});
						}
						var destination_content = "#kase_content";
						if (!blnShowHeader) {
							destination_content = "#content";
						}
						$(destination_content).html(new partie_view({model: corp, collection: data}).render().el);				
						$(destination_content).addClass("glass_header_no_padding");
						showEditRow();
					}
				});
			}
		});
		self.recentKases();
	},
	editPartie: function (case_id, corporation_id, corporation_type) {
		$("#gifsave").hide();
		if (case_id == -1 && corporation_id == -1 && corporation_type == 'new') {
			this.newPartie();
			return;
		}
		current_case_id = case_id;
			
		readCookie();
		var self = this;
	
		if (case_id > 0) {
			//not a roldex item
			if (corporation_id < 0 && corporation_type=="new") {
				self.kaseNewPartie(case_id);
				return;
			}
			//get the kase
			var kase = kases.findWhere({case_id: case_id});
			if (typeof kase == "undefined") {
				//case does not exist, get out
				document.location.href = "#";
			}
			case_id = kase.get("case_id");
			uuid = kase.get("case_uuid");
		} else {
			uuid = "";
		}
		//let's get the specific corporation by id.  if id < 0, we will get an empty corporation
		var corporation = new Corporation({id: corporation_id, case_id: case_id, type:corporation_type});
		corporation.fetch({
			success: function (corp) {
				var blnShowHeader = true;
				if (corporation_type == "employer") {
					blnShowHeader = (corporation_id > 0);
				}
				if (case_id > 0) {
					//only ids for active cases would get a header
					if (blnShowHeader) {
						if ($('#kase_content').length == 0) {
							kase.set("header_only", true);
							$('#content').html(new kase_view({model: kase}).render().el);
						}
					}
				}
				//make sure the data is there, if not there, redirect as new
				if (corp.get("company_name")=="" && corporation_id > 0) {
					self.editPartie(case_id, -1, corporation_type);
					return;
				}
				//if this is a new partie, the query will return partie_type values
				corp.set("partie", corporation_type.capitalize());
				corp.set("case_id", case_id);
				corp.set("case_uuid", uuid);
				corp.set("gridster_me", true);
				corp.set("show_buttons", true);
				corp.adhocs.fetch({
					success:function (data) {
						if (data.toJSON().length==0) {
							data = new AdhocCollection([], {case_id: case_id, corporation_id: corporation_id});
						}
						var destination_content = "#kase_content";
						if (!blnShowHeader) {
							destination_content = "#content";
						}
						$(destination_content).html(new partie_view({model: corp, collection: data}).render().el);				
						$(destination_content).addClass("glass_header_no_padding");
						showEditRow();
					}
				});
			}
		});
		self.recentKases();
	},
	editRoloPartie: function (corporation_id, corporation_type) {
		executeMainChanges();
		readCookie();
		var self = this;
		if (corporation_id < 0 && corporation_type=="new") {
			alert("wrong");
			return;
		}
		
		//let's get the specific corporation by id.  if id < 0, we will get an empty corporation
		var corporation = new Corporation({id: corporation_id, type:corporation_type});
		corporation.fetch({
			success: function (corp) {
				var blnShowHeader = true;
				if (corporation_type == "employer") {
					blnShowHeader = (corporation_id > 0);
				}
				//make sure the data is there, if not there, redirect as new
				if (corp.get("company_name")=="" && corporation_id > 0) {
					alert("wrong2");
					return;
				}
				corp.set("partie", corporation_type.capitalize());
				corp.set("gridster_me", true);
				corp.set("show_buttons", true);
				corp.adhocs.fetch({
					success:function (data) {
						if (data.toJSON().length==0) {
							data = new AdhocCollection([], {corporation_id: corporation_id});
							
						}
						$("#content").html(new partie_view({model: corp, collection: data}).render().el);				
						$("#content").addClass("glass_header_no_padding");
						showEditRow();
					}
				});
			}
		});
		self.recentKases();
	},
	editRoloPerson: function (person_id) {	
		executeMainChanges();
		readCookie();
		var self = this;
		if (person_id < 0) {
			alert("wrong");
			return;
		}
		
		//let's get the specific corporation by id.  if id < 0, we will get an empty corporation
		var person = new Person({id: person_id});
		person.fetch({
			success: function (pers) {
				var blnShowHeader = true;
				
				//make sure the data is there, if not there, redirect as new
				if (pers.get("last_name")=="" && person_id > 0) {
					alert("wrong2");
					return;
				}
				pers.set("gridster_me", true);
				pers.set("show_buttons", true);
				pers.set("bln_contact", true);
				$("#content").html(new dashboard_person_view({model: pers}).render().el);
			}
		});
		self.recentKases();
	},
	kaseNewPartie: function (case_id) {	
		current_case_id = case_id;
			
		readCookie();
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//case does not exist, get out
			document.location.href = "#";
			return;
		}
		
		//get the parties for the case, and then 
		parties = new Parties([], { case_id: case_id, case_uuid: kase.get("uuid") });
		parties.fetch({
			success: function(parties) {
				kase.set("header_only", true);
				$('#content').html(new kase_view({model: kase}).render().el);
				newPartyKaseView = new parties_new_view({el: $("#kase_content"), model:kase, parties: parties}).render();
				$("#kase_content").addClass("glass_header_no_padding");		
				hideEditRow();
			}
		});
	},
	newPartie: function () {	
		readCookie();
		var self = this;
				
		newPartyKaseView = new parties_new_rolodex({el: $("#content"), }).render();
		$("#content").addClass("glass_header_no_padding");		
		hideEditRow();
	},
	listEvents: function (id) {
		executeMainChanges();
		readCookie();
		
		if (typeof id == "undefined") {
			id = "";
		}
					
		if (typeof occurences == "undefined") {
			//initial call
			occurences = new OccurenceCollection({case_id: id});
			occurences.fetch({
				success: function (data) {
					occurencesView = new kaseOccurencesView({el: $("#content"), collection: occurences}).render();			
				}
			});			
		} else {
			$('#content').html(new kase_occurences_view({collection: occurences}).render().el);
		}
		
        this.kaseNavBarView.select_menu('events-list-menu');
    },
	listEventsing: function (id) {
		//alert("here");
		readCookie();
		//the id must be the event owner id
		if (typeof id == "undefined") {
			id = "";
		}
		
		if (typeof occurences == "undefined") {
			//initial call
			occurences = new OccurenceCollection({case_id: id});
			//go get the data from server
			occurences.fetch({
				success: function (data) {
					// Note that we could also 'recycle' the same instance of EmployeeFullView
					// instead of creating new instances
					$('#content').html(new event_listing_view({model: data}).render().el);
					
				}
			});
		} else {
			$('#content').html(new event_listing_view({model: occurences}).render().el);
		}
		
        this.kaseNavBarView.select_menu('todo-list-menu');
    },
	recentKases: function() {
		recent_kases.fetch({
			success: function(recent_kases) {
				$('#kases_recent').html(new kase_list_category_view({model: recent_kases}).render().el);
				return;
			}
		});
	},
	recentTasks: function() {
		recent_tasks.fetch({
			success: function(recent_tasks) {
				$('#occurences_recent').html(new kase_list_task_view({model: recent_tasks}).render().el);
				return;
			}
		});
	},
	recentOccurences: function() {
		readCookie();
		var self = this;
		if (typeof recent_occurences == "undefined") {
			//initial call
			recent_occurences = new RecentOccurenceCollection();
			//go get the data from server
			recent_occurences.fetch({
				success: function (data) {
					// Note that we could also 'recycle' the same instance of EmployeeFullView
					// instead of creating new instances
					$('#occurences_recent').html(new event_list_view({model: data}).render().el);
					
					//kalendar
					self.summaryKalendar();
				}
			});
		} else {
			$('#occurences_recent').html(new event_list_view({model: recent_occurences}).render().el);
		}
	},
	summaryKalendar:function(event) {
		readCookie();
		var summaryOccurencesView = new kaseOccurencesSummaryView({el: $("#summaryKalendar"), collection: recent_occurences}).render();
	},
    logout: function() {
        logOut();
    }
});

//load templates
//get rid of interoffice view
templateLoader.load(["chat_view", "dashboard_view", "import_view", "kase_nav_bar_view", "kase_nav_left_view", "kase_list_category_view", "kase_home_view", "kase_edit_view", "kase_listing_view",  "kase_summary_view", "kase_view", "kase_header_view", "applicant_view", "person_view", "person_image", "kai_view", "dialog_view", "eams_listing_view", "eams_view", "event_list_view", "event_listing", "event_list_item_view", "document_upload_view", "dashboard_injury_view", "dashboard_home_view", "injury_view", "bodyparts_view", "note_listing_view", "notes_view", "partie_listing_view", "parties_new_view", "partie_view", "partie_cards_view", "user_listing_view", "dashboard_user_view", "user_view", "email_view", "signature_view", "dashboard_person_view", "injury_number_view", "injury_add_view", "message_view", "interoffice_view", "message_listing", "message_attach", "stack_listing_view", "document_listing_view", "task_view", "task_listing", "new_kase_view", "new_note_view", "chatting_view", "event_view", "customer_setting_listing", "setting_view", "user_setting_listing", "letter_view", "letter_listing_view", "letter_attach", "setting_attach", "kase_list_task_view", "template_upload_view", "template_listing_view", "rolodex_listing_view", "parties_new_rolodex", "kase_control_panel", "form_listing", "kase_letter_listing_view", "prior_treatment_listing_view"],
    function () {
        app = new Router();
        Backbone.history.start();
	}
);

//modal display
$('.modal').on('shown.bs.modal', function() {
    $(this).find('.modal-dialog').css({
        'margin-top': function () {
			var outerH = $(this).outerHeight();
			if (outerH <600) {
				outerH = (outerH / 2) + 75;
			} else {
				outerH = (outerH / 2);
			}
            return -(outerH);
        },
        'margin-left': function () {
            return -($(this).outerWidth() / 2);
        }
    });
});

 // Tell jQuery to watch for any 401 or 403 errors and handle them appropriately
$.ajaxSetup({
    statusCode: {
        401: function(){
            // Redirec the to the login page.
            //document.location.href = 'index.html';
			//$('#logoutLink').hide();
			logOut();
         
        },
        403: function() {
            // 403 -- Access denied
            //document.location.href = '#denied';
			logOut();
        }
    }
});
function logOut() {
	$('.alert-error').hide(); // Hide any errors on a new submit
	var url = 'api/logout';

	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: "",
		success:function (data) {
			$("#logged_in").val('');
			//clear the cookie
			writeCookie('sess_id', '');
			if (typeof current_url == "undefined") {
				current_url = '';
			}
			writeCookie('origin', current_url);
			if(data.error) {  // If there is an error, show the error messages
				$('.alert-error').text(data.error.text).show();
			}
			else { // If not, send them back to the home page
				document.location.href = "index.html";
			}
		}
	});
}
function refreshCustomerSettings() {
	customer_setting = new CustomerSettingCollection();
	customer_setting.fetch({
		success: function (data) {
			$(document).attr('title', "Customer Settings :: iKase");
			$('#content').html(new customer_setting_listing({collection: data}).render().el);
			$("#content").removeClass("glass_header_no_padding");
		}
	});
}
function refreshUserSettings() {
	//fetch all settings
	user_setting = new UserSettingCollection();
	user_setting.fetch({
		success: function (data) {
			console.log(data.toJSON());
			$(document).attr('title', "User Settings :: iKase");
			$('#content').html(new user_setting_listing({collection: data}).render().el);
			$("#content").removeClass("glass_header_no_padding");
		}
	});
}
function refreshDocuments(case_id) {
	//get the kase
	var kase = kases.findWhere({case_id: case_id});
	if (typeof kase == "undefined") {
		//case does not exist, get out
		document.location.href = "#";
		return;
	}
	kase_documents = new DocumentCollection([], { case_id: case_id });
	
	if (case_id!=current_case_id) {
		//show header for new case
		kase.set("header_only", true);
		$('#content').html(new kase_view({model: kase}).render().el);
	}
	kase_documents.fetch({
		success: function(data) {
			$('#kase_content').html(new document_listing_view({collection: data, model: kase}).render().el);
			$("#kase_content").removeClass("glass_header_no_padding");
			hideEditRow();
		}
	});
	//set current case
	current_case_id = case_id;	
}
function refreshTemplates() {
	var self = this;		
	var word_templates = new WordTemplates([]);
	word_templates.fetch({
		success: function(data) {
			var empty_model = new Backbone.Model;
			empty_model.set("case_id", "");
			empty_model.set("uuid", "templates");
			empty_model.set("no_uploads", false);
			$('#content').html(new template_listing_view({collection: data, model: empty_model}).render().el);
			$("#content").removeClass("glass_header_no_padding");
			hideEditRow();
		}
	});
}
function composeNote(args) {
	var message = new Note({notes_id: -1, case_id: current_case_id});
	//$("#myModal4").css("background", "url(img/glass_modal.png)");
	
	$("#myModalLabel").html("New Message");
	$("#input_for_checkbox").hide();
	$("#modal_save_holder").html('<a title="Send Message" class="interoffice save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-send" style="color:#000066; font-size:20px">&nbsp;</i></a>');
	$("#myModalBody").html(new message_view({model: message}).render().el);
	$(".modal-header").css("background-image", "url('img/glass_edit_header_new.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-body").css("overflow-x", "hidden"); 
	$(".modal-dialog").css("width", "600px");
	$(".modal-dialog").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-content").css("background-image", "url('img/glass_edit_header_new.png')");
}
function composeLetter(element_id) {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	var partieArray = element_id.split("_");
	var partie_array_type = partieArray[1];
	var case_id = current_case_id;
	var template_id = -1;
	if (partie_array_type=="letter") {
		partie_array_type = "";
		if (partieArray.length > 2) {
			case_id = partieArray[2];
		}
		if (partieArray.length > 3) {
			template_id = partieArray[3];
			//get template info
			var word_template = word_templates.get(template_id);
		}
	}
	var modal_title = "";
	if (partieArray.length > 2 && partie_array_type != "") {
		modal_title = partieArray[1].capitalize();
	}
	var kase = kases.findWhere({case_id: case_id});
	//always will have a template, at least for now

	var template_letter = new Document({id: template_id, case_id: case_id});
	template_letter.fetch({
		success: function (data) {
			data.set("case_name", kase.name());
			$("#myModalBody").html(new letter_view({model: data}).render().el);
			$("#myModalLabel").html("Edit Letter");
		}
	});

	$("#input_for_checkbox").hide();
	
	$("#modal_save_holder").html('<input type="checkbox" id="parties_selectall" name="parties_selectall" value="Y" title="Check this box to Select all Parties" onclick="selectAllParties()" /><span class="white_text">Select all Parties</span>&nbsp;<a title="Save Letter" class="interoffice save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$("#modal_type").val("letter");
	
	$(".modal-header").css("background-image", "url('img/glass_edit_header_task.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$(".modal-dialog").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-dialog").css("width", "1000px");
	/*
	setTimeout(function() {
		$('.modal-dialog').css('top', '0px');
		$('.modal-dialog').css('margin-top', '50px')
	}, 700);
	*/
	$(".modal-content").css("background-image", "url('img/glass_edit_header_task.png')");
}
function composeEams(element_id) {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	var partieArray = element_id.split("_");
	var partie_array_type = partieArray[1];
	var case_id = current_case_id;
	case_id = partieArray[1];
	var modal_title = "";
	if (partieArray.length > 2) {
		modal_title = partieArray[2].capitalize();
	}
	var kase = kases.findWhere({case_id: case_id});
	$("#myModalLabel").html("Generate " + modal_title);
	//always will have a template, at least for now

	var data = new Backbone.Model;
	data.set("case_id", case_id);
	data.set("case_name", kase.name());
	data.set("eams_form_name", partieArray[2]);
	$("#myModalBody").html(new eams_view({model: data}).render().el);
	$("#input_for_checkbox").hide();
	
	$("#modal_save_holder").html('<a title="Save Eams" class="interoffice save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$("#modal_type").val("eams");
	
	$(".modal-header").css("background-image", "url('img/glass_edit_header_task.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$(".modal-dialog").css("background-image", "url('img/glass_edit_header_task.png')");
	if (partieArray[2] == "lien") {
		$(".modal-dialog").css("width", "1300px");
		/*
		setTimeout(function() {
			$("#lien_form").css("display", "");
			$('.modal-dialog').css('top', '10%');
			$('.modal-dialog').css('margin-top', '50px');
		}, 700);
		*/
	} else {
		$(".modal-dialog").css("width", "600px");
		$("#lien_form").css("display", "none");
		/*
		setTimeout(function() {
			$('.modal-dialog').css('top', '20%');
			$('.modal-dialog').css('margin-top', '50px');
		}, 700);
		*/
	}
	
	$(".modal-content").css("background-image", "url('img/glass_edit_header_task.png')");
}

var check_inbox_id;
function checkInbox() {
	clearTimeout(check_inbox_id);
	//get messages
	var inbox_delay = "";
	if (customer_settings.get("inbox_delay") == "") {
		inbox_delay = user_settings.get("inbox_delay");
	} else {
		inbox_delay = customer_settings.get("inbox_delay");
	}
	if (inbox_delay=="" || typeof inbox_delay == "undefined") {
		inbox_delay = 60000;
	}
	var customer_setting_delay = new CustomerSetting();
	customer_setting_delay.fetch({
		success:function(data) {
			//console.log(data);		
		}
	});
	var new_messages = new NewMessages();
	new_messages.fetch({
		success:function(data) {
			if (new_messages.length > 0) {
				$("#new_message_indicator").html(new_messages.length);
				$("#new_message_indicator").fadeIn();
				
				
				if ($(document).attr('title')=="Input :: iKase") {
					//only if we are in the inbox
					messages = new InboxCollection();
					messages.fetch({
						success: function (data) {
							var message_listing_info = new Backbone.Model;
							message_listing_info.set("title", "Inbox");
							message_listing_info.set("receive_label", "Received");
							$('#content').html(new message_listing({collection: data, model: message_listing_info}).render().el);
							$("#content").removeClass("glass_header_no_padding");
						}
					});
				}
			} else {
				$("#new_message_indicator").html("");
				$("#new_message_indicator").fadeOut();
			}
		}
	});
	
	var new_phone_messages = new NewPhoneCalls();
	new_phone_messages.fetch({
		success:function(data) {
			if (new_phone_messages.length > 0) {
				$("#new_phone_indicator").html(new_phone_messages.length);
				$("#new_phone_indicator").fadeIn();
				
				
				if ($(document).attr('title')=="Input :: iKase") {
					//only if we are in the inbox
					messages = new InboxCollection();
					messages.fetch({
						success: function (data) {
							var message_listing_info = new Backbone.Model;
							message_listing_info.set("title", "Inbox");
							message_listing_info.set("receive_label", "Received");
							$('#content').html(new message_listing({collection: data, model: message_listing_info}).render().el);
							$("#content").removeClass("glass_header_no_padding");
						}
					});
				}
			} else {
				$("#new_phone_indicator").html("");
				$("#new_phone_indicator").fadeOut();
			}
		}
	});
	check_inbox_id = setTimeout(function() {
		checkInbox();
	}, inbox_delay);
}
function setInboxIndicator(message_count) {
	if (message_count > 0) {
		$("#new_message_indicator").html(message_count);
		$("#new_message_indicator").fadeIn();
		
		if ($(document).attr('title')=="Input :: iKase") {
			//only if we are in the inbox
			messages = new InboxCollection();
			messages.fetch({
				success: function (data) {
					var message_listing_info = new Backbone.Model;
					message_listing_info.set("title", "Inbox");
					message_listing_info.set("receive_label", "Received");
					$('#content').html(new message_listing({collection: data, model: message_listing_info}).render().el);
					$("#content").removeClass("glass_header_no_padding");
				}
			});
		}
	} else {
		$("#new_message_indicator").html("");
		$("#new_message_indicator").fadeOut();
	}
}

function setInboxIndicator(message_count) {
	if (message_count > 0) {
		$("#new_message_indicator").html(message_count);
		$("#new_message_indicator").fadeIn();
		
		if ($(document).attr('title')=="Input :: iKase") {
			//only if we are in the inbox
			messages = new InboxCollection();
			messages.fetch({
				success: function (data) {
					var message_listing_info = new Backbone.Model;
					message_listing_info.set("title", "Inbox");
					message_listing_info.set("receive_label", "Received");
					$('#content').html(new message_listing({collection: data, model: message_listing_info}).render().el);
					$("#content").removeClass("glass_header_no_padding");
				}
			});
		}
	} else {
		$("#new_message_indicator").html("");
		$("#new_message_indicator").fadeOut();
	}
}

var check_task_inbox_id;
function checkTaskInbox() {
	//not for nick
	if (login_nickname == "ng") {
		return;
	}
	clearTimeout(check_task_inbox_id);
	var task_delay = "";
	if (customer_settings.get("task_delay") == "") {
		task_delay = user_settings.get("task_delay");
	} else {
		task_delay = customer_settings.get("task_delay");
	}
	if (task_delay=="" || typeof task_delay == "undefined") {
		task_delay = 60000;
	}
	//get tasks
	var new_tasks = new NewTasks();
	new_tasks.fetch({
		success:function(data) {
			if (new_tasks.length > 0) {
				$("#new_task_indicator").html(new_tasks.length);
				$("#new_task_indicator").fadeIn();
				
				if ($(document).attr('title')=="Input :: iKase") {
					//only if we are in the task_inbox
					tasks = new TaskInboxCollection();
					tasks.fetch({
						success: function (data) {
							var task_listing_info = new Backbone.Model;
							task_listing_info.set("title", "TaskInbox");
							task_listing_info.set("receive_label", "Received");
							$('#content').html(new task_listing({collection: data, model: task_listing_info}).render().el);
							$("#content").removeClass("glass_header_no_padding");
						}
					});
				}
			} else {
				$("#new_task_indicator").html("");
				$("#new_task_indicator").fadeOut();
			}
		}
	});
	check_task_inbox_id = setTimeout(function() {
		checkTaskInbox();
	}, task_delay);
}
function setTaskInboxIndicator(task_count) {
	if (task_count > 0) {
		$("#new_task_indicator").html(task_count);
		$("#new_task_indicator").fadeIn();
		
		if ($(document).attr('title')=="Input :: iKase") {
			//only if we are in the task_inbox
			tasks = new TaskInboxCollection();
			tasks.fetch({
				success: function (data) {
					var task_listing_info = new Backbone.Model;
					task_listing_info.set("title", "TaskInbox");
					task_listing_info.set("receive_label", "Received");
					$('#content').html(new task_listing({collection: data, model: task_listing_info}).render().el);
					$("#content").removeClass("glass_header_no_padding");
				}
			});
		}
	} else {
		$("#new_task_indicator").html("");
		$("#new_task_indicator").fadeOut();
	}
}

function composeMessage(object_id) {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	if (typeof object_id == "undefined") {
		object_id = "";
	}
	//are we replying, replyingall, forwarding
	var reaction = "";
	var object_action = "New Message";
	var case_id = current_case_id;
	var message = new Message({message_id: -1});
	message.set("source_message_id", object_id);
	message.set("case_id", current_case_id);
	//process the root if any
	if (object_id != "") {
		object_id = object_id.split("_");
		reaction = object_id[0];
		source_message_id = object_id[1];
		message.set("document_id", "");
		//not valid for forwarding scans
		if (object_id[0]!="sendstack") {
			message.set("case_id", current_case_id);
		} else {
			case_id = "";
		}
		switch (reaction) {
			case "compose":
				object_action = "Compose Message";
				case_id = source_message_id;
				message.set("case_id", case_id);
				source_message_id = -1;
				break;
			case "reply":
				object_action = "Reply to Message";
				break;
			case "replyall":
				object_action = "Reply to Message - All";
				break;
			case "forward":
				object_action = "Forward Message";
				break;
			case "senddocument":
				object_action = "Send Document";
				message.set("source_message_id", "");
				message.set("document_id",object_id[1]);
				break;
			case "sendstack":
				object_action = "Send Scan";
				message.set("source_message_id", "");
				message.set("document_id",object_id[1]);
				break;
		}
	}
	message.set("reaction", reaction);
	
	$("#myModalLabel").html(object_action);
	$("#input_for_checkbox").show();
	/*
	if (typeof document.getElementById("apply_notes") != "undefined") {
		document.getElementById("apply_notes").checked = false;
	}
	*/
	$("#modal_type").val("message");

	$("#modal_save_holder").html('<span id="apply_notes_holder"><input type="checkbox" id="apply_notes">&nbsp;Apply to Notes</span>&nbsp;<a title="Send Message" class="interoffice save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-send" style="color:#000066; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$("#myModalBody").html(new message_view({model: message}).render().el);
	$(".modal-header").css("background-image", "url('img/glass_edit_header_new.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-body").css("overflow-x", "hidden");
	setTimeout(function() {
		//$('.modal-dialog').css('top', '40%');
		$("#token-input-message_ccInput").css("width", "225px"); 
		$("#token-input-message_ccInput").parent().css("width", "225px");
		$("#token-input-message_ccInput").parent().parent().css("width", "225px");
		
		$("#token-input-message_bccInput").css("width", "225px"); 
		$("#token-input-message_bccInput").parent().css("width", "225px");
		$("#token-input-message_bccInput").parent().parent().css("width", "225px");
	}, 700);
	
	$(".modal-dialog").css("width", "600px");
	$(".modal-dialog").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-content").css("background-image", "url('img/glass_edit_header_new.png')");
}
function composeClientMessage(object_id) {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	if (typeof object_id == "undefined") {
		object_id = "";
	}
	
	//are we replying, replyingall, forwarding
	var reaction = "";
	var object_action = "New Marketing Message";
	var message = new Message({message_id: -1, case_id: -1});
	message.set("source_message_id", object_id);
	//process the root if any
	if (object_id != "") {
		object_id = object_id.split("_");
		reaction = object_id[0];
		source_message_id = object_id[1];
		message.set("document_id", "");
		switch (reaction) {
			case "reply":
				object_action = "Reply to Message";
				break;
			case "replyall":
				object_action = "Reply to Message - All";
				break;
			case "forward":
				object_action = "Forward Message";
				break;
			case "senddocument":
				object_action = "Send Document";
				message.set("source_message_id", "");
				message.set("document_id",object_id[1]);
				break;
		}
	}
	message.set("reaction", reaction);
	
	$("#myModalLabel").html(object_action);
	$("#input_for_checkbox").show();
	/*
	if (typeof document.getElementById("apply_notes") != "undefined") {
		document.getElementById("apply_notes").checked = false;
	}
	*/
	$("#modal_type").val("message");
	$("#modal_save_holder").html('<a title="Send Message" class="interoffice save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-send" style="color:#000066; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$("#myModalBody").html(new message_view({model: message}).render().el);
	$(".modal-header").css("background-image", "url('img/glass_edit_header_new.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-body").css("overflow-x", "hidden");
	setTimeout(function() {
		//$('.modal-dialog').css('top', '37%');
		$("#token-input-message_toInput").css("width", "388px"); 
		$("#token-input-message_toInput").parent().css("width", "388px");
		$("#token-input-message_toInput").parent().parent().css("width", "388px");
		$("#select_all_holder").show();
		
		$("#token-input-message_ccInput").css("width", "225px"); 
		$("#token-input-message_ccInput").parent().css("width", "225px");
		$("#token-input-message_ccInput").parent().parent().css("width", "225px");
		
		$("#token-input-message_bccInput").css("width", "225px"); 
		$("#token-input-message_bccInput").parent().css("width", "225px");
		$("#token-input-message_bccInput").parent().parent().css("width", "225px");
	}, 700);
	$("#messageInput").cleditor({
		width:550,
		height: 150,
		controls:     // controls to add to the toolbar
				  "bold italic underline | font size " +
				  "style | color highlight"
	});
	
	$(".modal-dialog").css("width", "600px");
	$(".modal-dialog").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-content").css("background-image", "url('img/glass_edit_header_new.png')");
}
function composeTask(element_id) {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	var task_id = -1;
	if (typeof element_id == "undefined") {
		task_id = -1;
		task_case_id = current_case_id;
	} else {
		var partieArray = element_id.split("_");
		task_id = partieArray[2]; 
		task_case_id = partieArray[3]; 
	}
	var task = new Task({task_id: task_id, case_id: task_case_id});
	if (task_id < 0) {
		$("#myModalLabel").html("New Task");
		task.set("from", login_username);
		//task.set("task_title", "Task Assigned By " + login_username);
		task.set("task_dateandtime", moment().format('MM/DD/YYYY h:mm a'));
		task.set("case_id", current_case_id);	
		
		$("#myModalBody").html(new task_view({model: task}).render().el);
	} else {
		var new_task = new Task({task_id: task_id, case_id: task_case_id});
		new_task.fetch({
			success: function (data) {
				data.set("task_id", data.id);
				data.set("case_id", "");
				var task_dateandtime = data.get("task_dateandtime");
				if (task_dateandtime!="" && task_dateandtime!="0000-00-00 00:00:00") {
					task_dateandtime = moment(task_dateandtime).format("MM/DD/YYYY h:mma");
				} else {
					task_dateandtime = "";
				}
				data.set("task_dateandtime", task_dateandtime);
				var end_date = data.get("end_date");
				if (end_date!="" && end_date!="0000-00-00 00:00:00") {
					end_date = moment(end_date).format("MM/DD/YYYY");
				} else {
					end_date = "";
				}
				data.set("end_date", end_date);
				
				var callback_date = data.get("callback_date");
				if (callback_date!="" && callback_date!="0000-00-00 00:00:00") {
					callback_date = moment(callback_date).format("MM/DD/YYYY h:mma");
				} else {
					callback_date = "";
				}
				data.set("callback_date", callback_date);
				
				$("#myModalBody").html(new task_view({model: data}).render().el);
				$("#myModalLabel").html("Edit Task");
			}
		});
	}
	
	$("#input_for_checkbox").hide();
	/*
	if (typeof document.getElementById("apply_notes") != "undefined") {
		document.getElementById("apply_notes").checked = false;
	}
	setTimeout(function(){
		initializeGoogleAutocomplete(".modal_input");
	}, 1000);
	*/
	
	$("#modal_type").val("task");
	$("#modal_save_holder").html('<a title="Save Task" class="task save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	var theme = {theme: "task"};
	$(".task #case_idInput").tokenInput("api/kases/tokeninput", theme);
	
	$(".modal-header").css("background-image", "url('img/glass_edit_header_task.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-body").css("overflow-x", "hidden");
	/*
	setTimeout(function() {
		$('.modal-dialog').css('top', '0px');
		$('.modal-dialog').css('margin-top', '50px')
	}, 700);
	*/
	$(".modal-dialog").css("width", "600px");
	$(".modal-dialog").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-content").css("background-image", "url('img/glass_edit_header_task.png')");
}
function composeNewNote(element_id) {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	var partieArray = element_id.split("_");
	var partie_array_type = partieArray[1];
	var case_id = current_case_id;
	if (partieArray[3] == "") {
		var notes_id = -1;
	} else { 
		notes_id = partieArray[3];
	}
	if (partie_array_type=="note") {
		partie_array_type = "";
		if (partieArray.length > 2) {
			case_id = partieArray[2];
		}
	}
	var modal_title = "";
	if (partieArray.length > 2 && partie_array_type != "") {
		modal_title = partieArray[1].capitalize();
	}
	
	if (notes_id < 0) {
			var new_note = new Backbone.Model({new_note_id: notes_id, partie_array_type: partie_array_type, partie_array_id: partieArray[2], case_id: case_id});
			new_note.set("dateandtime", moment().format('MM/DD/YYYY h:mm a'));
			new_note.set("entered_by", login_username);
			new_note.set("callback_date", "");
			new_note.set("status", "general");
			new_note.set("note", "");
			new_note.set("partie_array_id", partieArray[2]);
			$("#myModalBody").html(new new_note_view({model: new_note}).render().el);
			$("#myModalLabel").html("New " + modal_title + " Note");
		
	} else {
		var new_note = new Note({notes_id: notes_id, case_id: case_id});
		new_note.fetch({
			success: function (data) {
				data.set("new_note_id", data.id);
				data.set("partie_array_type", data.get("type"));
				data.set("partie_array_id", "");
				var callback_date = data.get("callback_date");
				if (callback_date!="" && callback_date!="0000-00-00 00:00:00") {
					callback_date = moment(callback_date).format("MM/DD/YY h:mma");
				} else {
					callback_date = "";
				}
				data.set("callback_date", callback_date);
				$("#myModalBody").html(new new_note_view({model: data}).render().el);
				$("#myModalLabel").html("Edit Note");
			}
		});
	}
	$("#input_for_checkbox").hide();
	
	$("#modal_save_holder").html('<a title="Save Note" class="interoffice save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$("#modal_type").val("note");
	
	$(".modal-header").css("background-image", "url('img/glass_edit_header_task.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$(".modal-dialog").css("width", "600px");
	$(".modal-dialog").css("background-image", "url('img/glass_edit_header_task.png')");
	/*
	setTimeout(function() {
		$('.modal-dialog').css('top', '0px');
		$('.modal-dialog').css('margin-top', '50px')
	}, 700);
	*/
	$(".modal-content").css("background-image", "url('img/glass_edit_header_task.png')");
}
function showKalendar(view, current_date) {
	var all_customer_events = new OccurenceCustomerCollection();
	all_customer_events.fetch({
		success: function (data) {
			//then re-assign to calendar
			$("#content").html("");
			var calendar_info = new Backbone.Model;
			calendar_info.set("view", view);
			calendar_info.set("current_date", "");
			if (typeof current_date != "undefined") {
				calendar_info.set("current_date", current_date);
			}
			occurencesView = new kase_cus_occurences_view({el: $("#content"), collection: all_customer_events, model:calendar_info}).render();			
		}
	});
}
function datepickIt (object_id, blnTimepicker) {
	if (typeof blnTimepicker == "undefined") {
		blnTimepicker = true;
	}
	$(object_id).datetimepicker({
		timepicker:blnTimepicker, 
		format:'m/d/Y h:iA',
		mask:false,
		onChangeDateTime:function(dp,$input){
			//alert($input.val());
		}
	});	
}

function tableSortIt (object_id, size) {
	if (typeof size == "undefined") {
		size = 100;
	}
	if (object_id == "stack_listing") {
		$("#" + object_id)
			.tablesorter({widthFixed: true, widgets: ['zebra']}) 
			/*
			.tablesorterPager({
				sortList: [[1,0]], 
				container: $("#pager"), 
				size: size,
				headers: { 2: { sorter: false}, 3: {sorter: false} }
			});
			*/ 
	} else {
		$("#" + object_id)
			.tablesorter({widthFixed: true, widgets: ['zebra']}) 
			/*
			.tablesorterPager({
				container: $("#pager"),
				size: size
			}); 
			*/
	}
}

function findIt(obj, namespace, page) {
	var $rows = $('.' + namespace + ' .' + page + '_data_row');
	var theobj = $("#" + obj.id);
	var val = $.trim($(theobj).val()).replace(/ +/g, ' ').toLowerCase();
	$(".date_row").hide();
	$rows.show().filter(function() {
		var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
		if (text.indexOf(val) > -1) {
			if (this.classList.length==3) {
				if (this.classList[2].indexOf("row") > -1) {
					var row_id = this.classList[2];
					$("." + row_id).show();
				}
			}
		}
		
		return !~text.indexOf(val);
	}).hide();
	
	//special case
	if (val == "") {
		$(".date_row").show();
	}
}
function filterIt(obj, namespace, page) {
	var $rows = $('.' + namespace + ' .' + page + '_data_row');
	var the_kind = obj.id.replace("Filter", "");
	var theobj = $("#" + obj.id);
	var val = $.trim($(theobj).val()).replace(/ +/g, ' ').toLowerCase();
	$(".date_row").hide();
	
	$rows.show().filter(function() {
		//var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
		var text = $( '.note_' + the_kind + '_cell', $( this ) ).text ().replace(/\s+/g, ' ').toLowerCase();
		if (text.indexOf(val) > -1) {
			if (this.classList.length==3) {
				if (this.classList[2].indexOf("row") > -1) {
					var row_id = this.classList[2];
					$("." + row_id).show();
				}
			}
		}
		
		return !~text.indexOf(val);
	}).hide();
	
	//special case
	if (val == "") {
		$(".date_row").show();
	}
}

function tokenIt(object_id, model_name) {	
	//"api/php-example.php"
	var search_collection;
	switch(model_name) {
		case "employee":
			search_collection = new EmployeeTypeCollection();
			break;
	}

	//go get the data from server
	search_collection.fetch({
		success: function (data) {
				//console.log(data);
				$("#" + object_id).tokenInput(data.toJSON(), {
					theme: "facebook"
				});
		}
	});
}
function gridsterDashboard() {
	var gridster = [];
	$(function () {
		gridster[0] = $("#gridster_flat_dashboard ul").gridster({
		namespace: '#gridster_flat_dashboard',
		widget_margins: [2, 2],
		widget_base_dimensions: [126, 40],
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
	
		gridster[0].options.max_rows = 1;
		//$("#gridster_flat ul").css("z-index", "9");
		$("#gridster_flat_dashboard").fadeIn();
	});
}
function gridsterById (gridster_id) {
	var gridster = [];
	$(function () {
		gridster[0] = $("#" + gridster_id + " ul").gridster({
			namespace: "#" + gridster_id,
			widget_margins: [2, 2],
			widget_base_dimensions: [230, 44],
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
		$("#" + gridster_id).fadeIn();
	});
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
						var serial = gridster[8].serialize();
						//console.log(JSON.stringify(serial));
					}
				}
				}).data('gridster');
			$("#gridster_person").fadeIn();
		}
	});
		
	$('#content').fadeIn();
}

function toggleFormEdit(master_class) {
	//get all the editing fields, and toggle them back
	$("." + master_class + " .editing").toggleClass("hidden");
	
	$("." + master_class + " .span_class").removeClass("editing");
	$("." + master_class + " .input_class").removeClass("editing");
	
	$("." + master_class + " .span_class").toggleClass("hidden");
	$("." + master_class + " .input_class").toggleClass("hidden");
	$("." + master_class + " .input_holder").toggleClass("hidden");
	$(".button_row." + master_class).toggleClass("hidden");
	$(".edit_row." + master_class).toggleClass("hidden");
	
}
function hideEditRow() {
	$(".edit_row .kase").fadeOut();
}
function showEditRow() {
	$(".edit_row .kase").fadeIn();
}
function hideUpload() {
	Backbone.history.navigate("#documents/" + id, true);
}
function isTouchDevice() {
	var ua = navigator.userAgent;
	var isTouchDevice = (
		ua.match(/iPad/i) ||
		ua.match(/iPhone/i) ||
		ua.match(/iPod/i) ||
		ua.match(/Android/i)
	);

	return isTouchDevice;
}

function editField(field_object, master_class) {
	var theclass = "";
	if (typeof master_class == "undefined") {
		master_class = "";
	}
	//if element dblclicked is a group holder (ie:Email group)
	
	if (master_class!="") {
		//get the name from the object
		if (typeof field_object.id == "undefined") {
			var field_name = field_object.attr("id");
			field_name = field_name.replace("Grid", "");
			//let's get the class
			theclass = field_object.parent().parent().attr("class");
		} else {
			var field_name = field_object.id;
			field_name = field_name.replace("Grid", "");
			//let's get the class
			theclass = field_object.parentElement.parentElement.className;
		}
		var arrClass = theclass.split(" ");
		theclass = "." + arrClass[0] + "." + arrClass[1];
		field_name = theclass + " #" + field_name;
		//edit the field, only if it is not already in editing mode
		//show the input, hide the span
		$(".span_class" + master_class).toggleClass("hidden");
		$(".input_class" + master_class).toggleClass("hidden");
		if (master_class!="") {
			$(field_name + "Save").toggleClass("hidden");
		}
		$(".span_class" + master_class).addClass("editing");
		$(".input_class" + master_class).addClass("editing");
		$(field_name + "Save").addClass("editing");
		
		//get out, no more after this for masters
		return;
	}
	if (master_class=="") {
		if (typeof field_object != "undefined") {
			//get the name from the object
			if (typeof field_object.id == "undefined") {
				var field_name = field_object.attr("id");
				field_name = field_name.replace("Grid", "");
				//let's get the class
				theclass = field_object.parent().parent().attr("class");
			} else {
				var field_name = field_object.id;
				field_name = field_name.replace("Grid", "");
				//let's get the class
				theclass = field_object.parentElement.parentElement.className;
			}
			var arrClass = theclass.split(" ");
			theclass = "." + arrClass[0] + "." + arrClass[1];
			field_name = theclass + " #" + field_name;
		} else {
			//field_name = "#" + field_name;
			return;
		}
		if ($(field_name + "Span").hasClass("editing") && $(".editing")) {
			return;
		}
		//edit the field, only if it is not already in editing mode
		if ($(field_name + "Span").hasClass("editing")) {
			//turn it all off
			$(field_name + "Span").toggleClass("hidden");
			$(field_name + "Input").toggleClass("hidden");
			$(field_name + "Save").toggleClass("hidden");
			
			$(field_name + "Span").removeClass("editing");
			$(field_name + "Input").removeClass("editing");
			$(field_name + "Save").removeClass("editing");
			return;
		}
		
		//show the input, hide the span
		$(field_name + "Span").toggleClass("hidden");
		$(field_name + "Input").toggleClass("hidden");
		$(field_name + "Save").toggleClass("hidden");
		
		$(field_name + "Span").addClass("editing");
		$(field_name + "Input").addClass("editing");
		$(field_name + "Save").addClass("editing");
		
	}
}
function updateNote(notes_value) {
	$("#noteInput").val(notes_value);
}
function deleteForm(event, subform) {
	var self = this;
	event.preventDefault(); // Don't let this button submit the form
	
	if (typeof subform == "undefined") {
		var form_name = $("#sub_category_holder").attr("class");
	} else {
		form_name = subform;
	}
	
	var id = $('.' + form_name + ' #table_id').val();
	$('.' + form_name + ' .alert-error').hide(); // Hide any errors on a new submit
        var url = 'api/' + form_name + '/delete';
		formValues = "id=" + id + "&table_name=" + form_name;

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
					kases.remove(kases.findWhere({case_id: id}));
					$('#kases_recent').html(new kase_list_category_view({model: kases}).render().el);
					document.location.href = "#kases";
                }
            }
        });
}
function deleteElement(event, id, form_name) {
	var self = this;
	event.preventDefault(); // Don't let this button submit the form
	var url = 'api/' + form_name + '/delete';
	formValues = "id=" + id + "&table_name=" + form_name;

	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else { // If not
				//hide the grid containing the element
				if ($("#partie_nameGrid_" + id).length > 0) {
					$("#partie_nameGrid_" + id).fadeOut();
				}
			}
		}
	});
	return true;
}
function addForm(event, subform, api_url) {
	if (subform=="chat") {
		if (event.keyCode!=13 && typeof event.keyCode != "undefined") {
			return false;
		}
	}
	
	var self = this;
	event.preventDefault(); // Don't let this button submit the form
	
	if (typeof subform == "undefined") {
		var form_name = $("#sub_category_holder").attr("class");
	} else {
		form_name = subform;
	}
	
	if (typeof api_url == "undefined") {
		api_url = form_name.toLowerCase();
	}
	
	//validate the form, except for venue (part of kase save)
	if (form_name != "kase_venue") {
		$blnValid = $("#" + form_name + "_form").parsley('validate');
		//for now
		//$blnValid = true;
		var id = $("." + form_name + " #table_id").val();
		if (id < 0) {
			//special case for person
			if (form_name=="person") {
				if ($blnValid) {
					//we need to save the additional info as well
					$blnValid = $("#kai_form").parsley('validate');
				}
			}
		}
		//get out if invalid			
		if (!$blnValid) {
			if (form_name=="person") {
				form_name = "person_view";
			}
				//$("." + form_name + " .alert-warning").show();
				//$("." + form_name + " .alert-text").html("Please fill in the required fields in the correct format.");
				//$("." + form_name + " .alert-warning").html("");
			
			$("." + form_name + " .custom-error-message").css('display', 'none');
			$("." + form_name + " .token-input-list-person").css('border', 'solid 1px red');
			//$("." + form_name + " .input_class parsley-validated parsley-error").css('border', 'red');
			$("#gifsave").hide();
			
			return false;
			
		}
		$("#partie_type_holder").css("color", "#45CC42");
		$("#panel_title").css("color", "#45CC42");
		$(".kai #panel_title").css("color", "#45CC42");
		setTimeout(function() {
			$("#partie_type_holder").css("color", "white");
			$("#panel_title").css("color", "white");
			$(".kai #panel_title").css("color", "white");
		}, 3000);
		$( "." + form_name + " .reset" ).trigger( "click" );
		
		$("." + form_name + " .alert-warning").hide();
		$("." + form_name + " .alert-error").hide(); // Hide any errors on a new submit
		
		var id = $("." + form_name + " #table_id").val();
	
		if (id > 0) {
			updateForm(event, subform, api_url);
			return true;
		}
		
		$("." + form_name + " #gifsave").show();
		//break up any setting
		if (form_name == "setting") {
			api_url = "setting/" + $("#setting_type").val();
		}
		if (form_name == "additional_case_number") {
			api_url = "injury_number";
		}
		var url = "api/" + api_url + "/add";
		var formValues = $("#" + form_name + "_form").serialize();
		var find = "Input";
		var regEx = new RegExp(find, 'g');
		formValues = formValues.replace(regEx, '');
		
		//special case for person
		if (form_name=="person") {
			//we need to save the additional info as well
			var additionalFormValues = $("#kai_form").serialize();
			additionalFormValues = additionalFormValues.substr(additionalFormValues.indexOf("titleInput"));
			additionalFormValues = additionalFormValues.replace(regEx, '');
			formValues += "&" + additionalFormValues;
		}
	} else {
		//save venue as corporation, it will be attched to case
		var id = -1;
		var parent_corporation_uuid = $(".kase #venueInput").val();
		var case_id = $(".kase #table_id").val();
		var case_uuid = $(".kase #table_uuid").val();
		
		var venue = venues.findWhere({"venue_uuid": parent_corporation_uuid});
		
		formValues = "table_name=corporation&type=venue&adhoc_fields=&case_id=" + case_id + "&case_uuid=" + case_uuid + "&parent_corporation_uuid=" + parent_corporation_uuid + "&company_name=" + venue.get("venue") + "&street=" + venue.get("address1") + "&suite=" + venue.get("address2") + "&city=" + venue.get("city") + "&state=CA&zip=" + venue.get("zip") + "&phone=" + venue.get("phone") + "&preferred_name=" + venue.get("venue_abbr") + "&full_address=" + venue.get("address1") + (" " + venue.get("address2")).trim() + ", " + venue.get("city") + ", CA " + venue.get("zip");
		
		var url = "api/corporation/add";
	}
	
	streetValues = "";
	if ($("#street_" + form_name).length > 0) {
		//get the address details
		var streetValues = "&street=" + $("#street_" + form_name).val();
		streetValues += "&city=" + $("#city_" + form_name).val();
		streetValues += "&state=" + $("#administrative_area_level_1_" + form_name).val();
		streetValues += "&zip=" + $("#postal_code_" + form_name).val();
	}
	formValues += streetValues;
	
	//return;
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				console.log(data.error.text);
				saveFormFailed(data.error.text);
			} else { 
				$("#gifsave").hide();
				if (form_name == "kase" || form_name == "event" || form_name == "setting") {
					$('#myModal4').modal('toggle');
					if (form_name == "setting") {
						$(".list_title").css("color","#45CC42");
						setTimeout(function() {
							refreshCustomerSettings();
						}, 1500);
					}
					//refresh kalendar
					/*
					if (document.location.href.indexOf("#kalendar") > 0) {
						var kase = kases.findWhere({case_id: current_case_id});
						renderCalendar(kase);
					}
					*/
				}
				if (form_name != "chat") {
					// redraw the screen
					var arrValues = formValues.split("&");
					for(var i =0, len = arrValues.length; i < len; i++) {
						var thevalue = arrValues[i];
						var arrValuePair = thevalue.split("=");
						field_name = arrValuePair[0];
						fieldvalue = arrValuePair[1];
						//self.model.set(field_name, fieldvalue);
						
						if ($("." + form_name + " #" + field_name + "Input").length != 0) {
							if (field_name!="password") {
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
				
				//insert the id, so we can update right away
				$("." + form_name + " #table_id").val(data.id);
				$("." + form_name + " #" + api_url + "_uuid").val(data.uuid);
				
				if (form_name == "kase") {
					//redirect to new applicant
					var new_kase = new Kase();
					$(".kase #table_id").val(data.id);
					$(".kase #case_id").val(data.id);
					//update the next case number
					customer_settings.set("case_number_next", data.case_number_next);
					
					new_kase.set("id", $(".kase #table_id").val());
					new_kase.fetch({
						success: function(kase) {
							kases.add(kase);
							$(".kase #table_uuid").val(kase.get("uuid"));
							/*
							if (!$(".kase .save").hasClass("hidden")) {
								$('#content').html(new kase_view({model: kase}).render().el);
							}
							*/
							setTimeout(function() {
								document.location.href="#applicant/" + kase.get("case_id");
							}, 10);
							setTimeout(function() {
								//using settimeout for async behavior, i want to save the venue as corporation in the background
								//this affects the new kase logic
								addForm(event, "kase_venue", "corporation");
							}, 2000);
						}
					});
					return true;
				}
				if (form_name == "person") {
					var case_id = $(".person #case_id").val();
					var kase = kases.findWhere({case_id: case_id});
					kase.set("applicant_id", data.id);
					//check for an employer?
					var corporations = new Corporations([], { case_id: case_id });
					corporations.fetch({
						success: function(corporations) {
							//if we have no parties and no applicant
							employer_partie = corporations.findWhere({"type": "employer"});
				
							if (typeof employer_partie == "undefined") {
								document.location.href="#parties/" + case_id + "/-1/employer";
								return true;
							}
							//make sure we have doi
							var kase_dois = dois.findWhere({case_id: case_id});
							
							if (typeof kase_dois == "Object" || typeof kase_dois == "object") {
								var doi = kase_dois.toJSON();
								if (doi.start_date=="0000-00-00") {
									document.location.href = "#injury/" + case_id + "/" + doi.injury_id;
									return;
								}
							} else {
								document.location.href = "#newinjury/" + case_id;
								return true;
							}					
						}
					});
				}
				//we need injury
				if (form_name == "Employer") {
					
					var event_url = 'api/event/add';
					//eventformValues = formValues.replace("table_name=message", "table_name=event");
					//var kase = kases.findWhere({case_id: current_case_id});
					var kase = new Kase();
					kase.set("id", current_case_id);
					kase.fetch({
						success: function(kase) {
							eventformValues = "table_name=event";
							eventformValues += "&event_title=" + encodeURI(kase.get("name"));
							eventformValues += "&event_descriptionInput=Kase Intake by " + login_username;
							eventformValues += "&event_kind=intake";
							eventformValues += "&event_type=intake";
							eventformValues += "&event_dateandtime=" + moment().format("MM/DD/YYYY h:mm a");
							eventformValues += "&end_date=" + moment().format("MM/DD/YYYY h:mm a");
							eventformValues += "&case_id=" + current_case_id;
							
							//return;
							$.ajax({
							url:event_url,
							type:'POST',
							dataType:"json",
							data: eventformValues,
								success:function (data) {
									if(data.error) {  // If there is an error, show the error messages
										saveFailed(data.error.text);
									} else {
										//document.location.href="#firmkalendar";
									}
								}
							});
						}
					});
						
					
					var case_id = $(".partie #case_id").val();
					//make sure we have doi
					var kase_dois = dois.findWhere({case_id: case_id});
					
					if (typeof kase_dois == "Object" || typeof kase_dois == "object") {
						var doi = kase_dois.toJSON();
						if (doi.start_date=="0000-00-00") {
							document.location.href = "#injury/" + case_id + "/" + doi.injury_id;
							return;
						}
					} else {
						document.location.href = "#newinjury/" + case_id;
						return true;
					}
				}
				//this might be a new injury
				if (form_name == "injury") {
					//let's trigger the other save events
					$(".bodyparts #injury_id").val(data.id);
					$(".injury_number #injury_id").val(data.id);
					$(".additional_case_number #injury_id").val(data.id);
					
					$(".bodyparts .save").trigger("click");
					$(".injury_number .save").trigger("click");
				}
				if (form_name == "injury_number") {
					$(".additional_case_number #table_id").val(data.id);
					$(".additional_case_number .save").trigger("click");
				}
				if (form_name == "additional_case_number") {
					//do we have a carrier
					var corporations = new Corporations([], { case_id: case_id });
					corporations.fetch({
						success: function(corporations) {
							//if we have no parties and no applicant
							carrier_partie = corporations.findWhere({"type": "carrier"});
				
							if (typeof carrier_partie == "undefined") {
								document.location.href="#parties/" + case_id + "/-1/carrier";
								return true;
							}
						}
					});
					//if not, need to go new partie carrier screen
					//parties/case_id/-1/carrier
				}
				//hide animation
				if (form_name != "kase_venue") {
					//special case
					saveFormSuccessful(form_name);
				} else {
					return true;
				}
				if (form_name == "chat") {
					//erase the message
					$('#chatInput').val('').blur();
					renderChat(data.success);
					$(".chat #thread_id").val(data.success);
					$("#thread_id_holder").html(data.success);
				}
				
				return true;
			}
		}
	});
	
	//this might be a phone message
	if (formValues.indexOf("event_kind=phone_call") > -1) {

			var url = 'api/messages/add';
			formValues = formValues.replace("&end_date=", "");
			formValues = formValues.replace("&full_address=&event_type=phone_call", "");
			formValues = formValues.replace("event_kind=", "message_type=");
			formValues = formValues.replace("assignee=", "message_to=");
			formValues = formValues.replace("event_title=", "subject=");
			formValues = formValues.replace("event_from=", "from=");
			formValues = formValues.replace("table_name=event", "table_name=message");
			//formValues = formValues.replace("event_dateandtime=", "dateandtime=");
			//formValues = formValues.replace("case_fileInput=", "case_id=");
			formValues = formValues.replace("event_description=", "messageInput=");
			$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					}
				}
			});	
	}
}
	
function updateForm(event, subform, api_url) {
	event.preventDefault(); // Don't let this button submit the form
	var self = this;
	
	if (typeof subform == "undefined") {
		var form_name = $("#sub_category_holder").attr("class");
	} else {
		form_name = subform;
	}
	if (typeof api_url == "undefined") {
		api_url = form_name.toLowerCase();
	}
	
	$('.' + form_name + ' #gifsave').show();
	if (form_name == "setting") {
		api_url = "setting/" + $("#setting_type").val();
	}
	if (form_name == "additional_case_number") {
		api_url = "injury_number";
	}
	var url = "api/" + api_url + "/update";
	
	var formValues = $("#" + form_name + "_form").serialize();
	var find = "Input";
	var regEx = new RegExp(find, "g");
	formValues = formValues.replace(regEx, "");
	streetValues = "";
	if ($("#street_" + form_name).length > 0) {
		//get the address details
		var streetValues = "&street=" + $("#street_" + form_name).val();
		streetValues += "&city=" + $("#city_" + form_name).val();
		streetValues += "&state=" + $("#administrative_area_level_1_" + form_name).val();
		streetValues += "&zip=" + $("#postal_code_" + form_name).val();
	}
	formValues += streetValues;
	
	//apply changes to parent to all children
	if ($("#confirm_apply_decide").length > 0) {
		if ($("#confirm_apply_decide").val() == "Y") {
			formValues += "&confirm_apply_decide=" + $("#confirm_apply_decide").val();
		}
	}
	$.ajax({
		url:url,
		type:"POST",
		dataType:"json",
		data: formValues,
		success:function (data) {
			$("#gifsave").hide();
			if(data.error) {  // If there is an error, show the error messages
				console.log("error" + data.error.text);
				saveFormFailed(data.error.text);
				$("#gifsave").hide();
			}
			else { 
				$("#gifsave").hide();
				$("#partie_type_holder").css("color", "#45CC42");
				$("#panel_title").css("color", "#45CC42");
				$(".kai #panel_title").css("color", "#45CC42");
				setTimeout(function() {
					$("#partie_type_holder").css("color", "white");
					$("#panel_title").css("color", "white");
					$(".kai #panel_title").css("color", "white");
				}, 3000);
				if (form_name == "kase" || form_name == "event" || form_name == "setting") {
					$('#myModal4').modal('toggle');
					if (form_name == "setting") {
						$(".list_title").css("color","#45CC42");
						setTimeout(function() {
							refreshCustomerSettings();
						}, 1500);
					}
				}
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
					
					if ($("." + form_name + " #" + field_name + "Input").length != 0) {
						//never show password
						if (field_name!="password") {
							//treat text fields one way
							if ($("." + form_name + " #" + field_name + "Input").prop("type")!= "select-one") {
								if (field_name!="note") {
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
									
									note_value = html.wrap("<div>").parent().html(); // have to wrap for html to get the outer element

									$("." + form_name + " #" + field_name + "Span").html(note_value);
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
				//we need injury
				if (form_name == "Employer") {
					var case_id = $(".partie #case_id").val();
					//make sure we have doi
					var kase_dois = dois.findWhere({case_id: case_id});
					
					//if (typeof injury_partie == "undefined") {
					if (typeof kase_dois == "Object" || typeof kase_dois == "object") {
						var doi = kase_dois.toJSON();
						if (doi.start_date=="0000-00-00") {
							document.location.href = "#injury/" + case_id + "/" + doi.injury_id;
							return;
						}
					} else {
						document.location.href = "#newinjury/" + case_id;
						return true;
					}
				}
				//ssn special case
				if (form_name=="person") {
					$("." + form_name + " #ssnSpan").html(ssn1 + ssn2 + ssn3);
				}
				
				if (form_name == "injury") {
					//let's trigger the other save events
					$(".bodyparts #injury_id").val(data.id);
					$(".injury_number #injury_id").val(data.id);
					$(".additional_case_number #injury_id").val(data.id);
					if ($(".bodyparts .save").css("visibility")!="hidden") {
						$(".bodyparts .save").trigger("click");
					}
					if ($(".injury_number .save").css("visibility")!="hidden") {
						$(".injury_number .save").trigger("click");
					}
				}
				if (form_name == "injury_number") {
					$(".additional_case_number #table_id").val(data.id);
					if ($(".additional_case_number .save").css("visibility")!="hidden") {
						$(".additional_case_number .save").trigger("click");
					}
				}
				if (form_name == "additional_case_number") {
					//do we have a carrier
					var corporations = new Corporations([], { case_id: current_case_id });
					corporations.fetch({
						success: function(corporations) {
							//if we have no parties and no applicant
							carrier_partie = corporations.findWhere({"type": "carrier"});
				
							if (typeof carrier_partie == "undefined") {
								document.location.href="#parties/" + current_case_id + "/-1/carrier";
								return true;
							}
						}
					});
					//if not, need to go new partie carrier screen
					//parties/case_id/-1/carrier
				}
				saveFormSuccessful(form_name);
				return true;
			}
		}
	});
}
function saveFormSuccessful(form_name) {
	$("." + form_name + " #gifsave").hide();
	//$("." + form_name + " .alert-success").fadeIn(function() { 
		//setTimeout(function() {
				//$("." + form_name + " .alert-success").fadeOut();
			//},1500);
	//});
	
	//refresh recent kases
	checkKases();
	
	if (form_name == "Employer"){
		if ($(".Employer")[0].parentElement.parentElement.id=="employer_holder") {
			//now go to partie screen
			var id = $("." + form_name + " #case_id").val();
			document.location.href = "#parties/" + id;
		}
	}
	if (form_name == "injury"){
		var case_id = $("." + form_name + " #case_id").val();
		var corporations = new Corporations([], { case_id: case_id });
		corporations.fetch({
			success: function(corporations) {
				//check for carrier
				carrier_partie = corporations.findWhere({"type": "carrier"});
	
				if (typeof carrier_partie == "undefined") {
					document.location.href="#parties/" + case_id + "/-1/carrier";
					return true;
				}
			}
		});
	}
	
	if (form_name=="event") {
		if (typeof current_case_id != "undefined" && current_case_id > 0) {
			var kase = kases.findWhere({case_id: current_case_id});
			$("#kase_content").removeClass("glass_header_no_padding");
			renderCalendar(kase);
		}
		//are we in firmkalendar mode
		if (window.location.href.indexOf("#firmkalendar") > -1) {
			var all_customer_events = new OccurenceCustomerCollection();
			all_customer_events.fetch({
				success: function (data) {
					//then re-assign to calendar
					$("#content").html("");
					occurencesView = new kase_cus_occurences_view({el: $("#content"), collection: all_customer_events, model:data}).render();
				}
			});
		}
		//are we in home page
		var arrWindow = window.location.href.split("#");
		if (arrWindow[1]=="") {
			$('#content').html(new dashboard_home_view().render().el);
		}
	}
}
function saveFailed(text) {
	$('.alert-error').text(text)
	$(".alert-error").fadeIn(function() { 
		setTimeout(function() {
				$(".alert-error").fadeOut();
			},1500);
	});
	
}
function showAuto() {
	$("#autocomplete_completed").show();
}
function kaseComplete(form_name, obj_selector) {
	new AutoCompleteKaseView({
		input: $("." + form_name + " #" + obj_selector),
		form_name:form_name,
		model: kases,
		onSelect: function (model) {
			//clean display in the search box
			$("." + form_name + " #" + obj_selector).val(model.display());
		}
	}).render();
}
function eamsComplete(form_name, obj_selector) {
	new AutoCompleteEAMSView({
		input: $("." + form_name + " #" + obj_selector),
		form_name:form_name,
		model: eams_carriers,
		onSelect: function (model) {
			//clean display in the search box
			$("." + form_name + " #" + obj_selector).val(model.display());
			//eams number - THERE MUST BE AN ADHOC eams_id FIELD ON THE FORM
			$("#" + form_name.toLowerCase() + "_eams_ref_numberInput").val(model.get("eams_ref_number"));
			//address
			var the_street = model.get("street_1");
			if (model.get("street_2")!="") {
				the_street += ", " + model.get("street_2");
			}
			$("." + form_name + " #full_addressInput").val(the_street + ", " + model.get("city") + ", " + model.get("state") + " " + model.get("zip_code"));
		}
	}).render();
}
function repsComplete(form_name, obj_selector) {
	new AutoCompleteRepsView({
		input: $("." + form_name + " #" + obj_selector),
		form_name:form_name,
		model: eams_reps,
		onSelect: function (model) {
			//clean display in the search box
			$("." + form_name + " #" + obj_selector).val(model.display());
			//eams number - THERE MUST BE AN ADHOC eams_id FIELD ON THE FORM
			$("#" + form_name.toLowerCase() + "_eams_ref_numberInput").val(model.get("eams_ref_number"));
			//address
			var the_street = model.get("street_1");
			if (model.get("street_2")!="") {
				the_street += ", " + model.get("street_2");
			}
			$("." + form_name + " #full_addressInput").val(the_street + ", " + model.get("city") + ", " + model.get("state") + " " + model.get("zip_code"));
		}
	}).render();
}
function attorneyComplete(form_name, obj_selector) {
	new AutoCompleteView({
		input: $("." + form_name + " #" + obj_selector),
		form_name:form_name,
		model: attorney_searches,
		onSelect: function (model) {
			//clean display in the search box
			$("." + form_name + " #" + obj_selector).val(model.display());
			//address
		}
	}).render();
}
function workerComplete(form_name, obj_selector) {
	new AutoCompleteWorkerView({
		input: $("." + form_name + " #" + obj_selector),
		form_name:form_name,
		model: worker_searches,
		onSelect: function (model) {
			//clean display in the search box
			$("." + form_name + " #" + obj_selector).val(model.display());
			//address
		}
	}).render();
}
function workerEventComplete(form_name, obj_selector) {
	new AutoCompleteWorkerEventView({
		input: $("." + form_name + " #" + obj_selector),
		form_name:form_name,
		model: worker_searches,
		onSelect: function (model) {
			//clean display in the search box
			$("." + form_name + " #" + obj_selector).val(model.display());
			$(".event_dialog #event_descriptionInput").focus();
			//address
		}
	}).render();
}
function specialtyComplete(form_name, obj_selector) {
	new AutoCompleteSpecialtyView({
		input: $("." + form_name + " #" + obj_selector),
		form_name:form_name,
		model: medical_specialties,
		onSelect: function (model) {
			//clean display in the search box
			$("." + form_name + " #" + obj_selector).val(model.display());
			//address
		}
	}).render();
}
function zipLookup(event) {
	event.preventDefault();
	var element = event.currentTarget;
	var form_name = $("#sub_category_holder").attr("class");
	
	$.zipLookup(                                            
		$("." + form_name + " #" + element.id).val(),                                      
		function(cityName, stateName, stateShortName){     
			$("." + form_name + " #cityInput").val(cityName);            
			$("." + form_name + " #stateInput").val(stateName);  
			$("." + form_name + " #stateshortInput").val(stateShortName);
		},
		function(errMsg){                                   
			$('.message').html("Error: " + escapeHtml(errMsg));         
	});
}

var placeSearch, autocompleteEmployer, autocompleteCarrier, autocompleteDefense;
var componentForm = {
	street_number: 'short_name',
	route: 'long_name',
	locality: 'long_name',
	neighborhood: 'long_name',
	sublocality: 'long_name',
	administrative_area_level_1: 'short_name',
	country: 'long_name',
	postal_code: 'short_name'
};
function initializeGoogleAutocomplete(className) {
	var text_box = document.querySelectorAll("." + className + " #full_addressInput")[0];
	window["autocomplete" + className] = new google.maps.places.Autocomplete(text_box, { types: ['geocode'] });
  // Create the autocomplete object, restricting the search
  // to geographical location types.
	
  // populate the address fields in the form.
  google.maps.event.addListener(window["autocomplete" + className], 'place_changed', function() {
    setTimeout("fillInAddress('" + className + "')", 200);
	
	
  });
}

// [START region_fillform]
function fillInAddress(className) {
  // Get the place details from the autocomplete object.
	var place = window["autocomplete" + className].getPlace();
	
	for (var component in componentForm) {
		document.getElementById(component + "_" + className).value = '';
		document.getElementById(component + "_" + className).disabled = false;
	}
	
	// Get each component of the address from the place details
	// and fill the corresponding field on the form.
	var sublocality = "";
	for (var i = 0; i < place.address_components.length; i++) {
		var addressType = place.address_components[i].types[0];
		if (componentForm[addressType]) {
		  var val = place.address_components[i][componentForm[addressType]];
		  document.getElementById(addressType + "_" + className).value = val;
		  
		  if (addressType=="neighborhood") {
			//sublocality = val;  
		  }	  
		  if (addressType=="sublocality") {
			sublocality = val;  
		  }
		}
	}
	if (sublocality=="") {
		sublocality = document.getElementById("locality_" + className).value;
	}
	var text_box = document.querySelectorAll("." + className + " #full_addressInput")[0];
	var full_address = document.getElementById("street_number_" + className).value + " " + document.getElementById("route_" + className).value + ", " + sublocality + ", " + document.getElementById("administrative_area_level_1_" + className).value + " " + document.getElementById("postal_code_" + className).value;
	text_box.value = full_address;
	
	document.getElementById("city_" + className).value = sublocality;
	document.getElementById("street_" + className).value = document.getElementById("street_number_" + className).value + " " + document.getElementById("route_" + className).value;
	if (className != "event") {
		if (document.querySelectorAll("." + className + " #suiteInput").length > 0) {
			document.querySelectorAll("." + className + " #suiteInput")[0].focus();
		}
	}
}
// [END region_fillform]

// [START region_geolocation]
// Bias the autocomplete object to the user's geographical location,
// as supplied by the browser's 'navigator.geolocation' object.
function geolocate() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
      var geolocation = new google.maps.LatLng(
          position.coords.latitude, position.coords.longitude);
      autocomplete.setBounds(new google.maps.LatLngBounds(geolocation,
          geolocation));
    });
  }
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
		var preview = "D:/uploads/" + customer_id + "/" + time_stamp + "/" + filename + "_" + first_page + ".png";
	} else {
		var preview = "D:/uploads/" + customer_id + "/thumbnails/" + filename + "_" + first_page + ".png";
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
var show_attachment_preview_id;
var freezeMessagePreview = function(event) {
	event.preventDefault();
	clearTimeout(show_attachment_preview_id);
	var element = event.currentTarget;
	var theid = element.id.split("_")[1];
	$("#message_preview_panel").show();
}
var freezeTaskPreview = function(event) {
	event.preventDefault();
	clearTimeout(show_attachment_preview_id);
	var element = event.currentTarget;
	var theid = element.id.split("_")[1];
	$("#task_preview_panel").show();
}
var showDeleteConfirm = function(element) {
	var rect = element.getBoundingClientRect();
	
	var scrollTop = document.documentElement.scrollTop?
					document.documentElement.scrollTop:document.body.scrollTop;
	var scrollLeft = document.documentElement.scrollLeft?                   
					 document.documentElement.scrollLeft:document.body.scrollLeft;
	elementTop = rect.top+scrollTop - 40;
	//elementTop = scrollTop - 20;
	elementLeft = rect.left+scrollLeft - 300;
	return [elementTop, elementLeft];
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
		elementLeft = rect.left+scrollLeft - 300;
		//console.log("top", rect.top + " + " + scrollTop);
		filename = filename.replaceAll("D:/uploads/" + customer_id + "/", "");
		var arrFiles = filename.split("|");
		var arrayLength = arrFiles.length;
		var panel_html;
		var arrLinks = [];
		for (var i = 0; i < arrayLength; i++) {
			filename = arrFiles[i];
			panel_html = "<div><a href='D:/uploads/" + customer_id + "/";
			
			if (case_id!="") {
				panel_html += case_id + "/";
			}
			panel_html += filename + "' target='_blank' class='" + form_name + "_preview_link'>" + filename + "</a></div>";
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
	var preview = "D:/uploads/" + customer_id + "/";
	if (thumbnail_folder!="") {
		if (filename.indexOf("_") > -1 && thumbnail_folder.indexOf("/") == -1) {
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
			if (extension=="pdf" || extension=="PDF") {
				new_extension = "jpg";
			}
			arrFileName.pop();
			filename = arrFileName.join(".") + "." + new_extension;
		}
		preview += thumbnail_folder + "/";
	}
	preview += filename;
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
var documentThumbnail = function(filename, customer_id, thumbnail_folder, case_id) {
	if (typeof thumbnail_folder == "undefined") {
		thumbnail_folder = "";
	}
	if (typeof case_id == "undefined") {
		case_id = "";
	}
	var preview = "D:/uploads/" + customer_id + "/";
	if (case_id!="" && thumbnail_folder=="") {
		preview += case_id + "/";
	}
	if (thumbnail_folder!="") {
		if (filename.indexOf("_") > -1 && thumbnail_folder.indexOf("/") == -1) {
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
			if (extension=="pdf" || extension=="PDF") {
				new_extension = "jpg";
			}
			arrFileName.pop();
			filename = arrFileName.join(".") + "." + new_extension;
		}
		preview += thumbnail_folder + "/";
	}
	preview = preview.replace("medium/", "thumbnail/");
	preview += filename;
	
	return preview;
}
var closeDocument = function() {
	$("#view_document").css({display: "none"});
}
var showDocument = function(filepath) {
	hidePreview();
	window.open('../templates/preview.php?file=<? echo $_GET["case_id"]; ?>/' + filepath, 'Preview')
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
function saveModal() {
	$("#gifsave").show();
	$("#modal_save_holder").hide();
	$("#gifsave").css("margin-top", "-5px");
	
	if ($("#modal_type").val() == "letter") {
		var url = 'api/letter/create';
		var formValues = $("#letter_form").serialize();
		
		$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else { // If not
					$('#myModal4').modal('toggle');
					var case_id = $(".letter #case_id").val();
					var template_name = $(".letter #template_name").val();
					
					//add a note
					var url = 'api/notes/add';
					
					formValues = "table_name=notes";
					var letter = data.success;
					var arrLetter = letter.split("/");
					var letter_link = "<a href='" + letter + ".docx' title='Click to open letter' class='white_text' target='_blank'>" + arrLetter[arrLetter.length - 1] + ".docx</a>";
					
					var note = "Letter " + letter_link + " created by " + login_username + " on " + moment().format("MM/DD/YYYY h:mA") + " from template " + String.fromCharCode(34) + template_name + String.fromCharCode(34);
					formValues += "&noteInput=" + note;
					formValues += "&case_id=" + case_id;
					formValues += "&subject=Letter Added";
					formValues += "&dateandtime=" + moment().format("MM/DD/YYYY h:mmA");
					
					//return;
					$.ajax({
					url:url,
					type:'POST',
					dataType:"json",
					data: formValues,
						success:function (data) {
							if(data.error) {  // If there is an error, show the error messages
								saveFailed(data.error.text);
								$('#myModal4').modal('toggle');
							}
						}
					});
					//store the search element if any
					var current_search;
					if ($("#letter_searchList").val()!="") {
						current_search = $("#letter_searchList").val();
					}
								
					
					kase_letters = new KaseLetters([], { case_id: case_id });
					kase_letters.fetch({
						success: function(data) {
							var kase = kases.findWhere({case_id: case_id});
							kase.set("no_uploads", true);
							$('#kase_content').html(new kase_letter_listing_view({collection: word_templates, model: kase}).render().el);
							$("#kase_content").removeClass("glass_header_no_padding");
							
							setTimeout(function(){
								$("#letter_searchList").val(current_search);
								$( "#letter_searchList" ).trigger( "keyup" );
							}, 500);
						}
					});
				}
			}
		});
	}
	if ($("#modal_type").val() == "eams") {
		var url = 'api/pdf/create';
		var formValues = $("#eams_form").serialize();
		
		$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else { // If not
					var case_id = $(".eams #case_id").val();
					var eams_form_name = $(".eams #eams_form_name").val();
					
					//add a note
					var url = 'api/notes/add';
					
					formValues = "table_name=notes";
					
					var note = "EAMS Form " + String.fromCharCode(34) + eams_form_name + String.fromCharCode(34) + " created by " + login_username + " on " + moment().format("MM/DD/YYYY h:mA");
					formValues += "&noteInput=" + note;
					formValues += "&case_id=" + case_id;
					formValues += "&subject=EAMS Form Filled";
					formValues += "&dateandtime=" + moment().format("MM/DD/YYYY h:mmA");
					
					//return;
					$.ajax({
					url:url,
					type:'POST',
					dataType:"json",
					data: formValues,
						success:function (data) {
							if(data.error) {  // If there is an error, show the error messages
								saveFailed(data.error.text);
							}
						}
					});
					var arrFile = data.file.split("/");
					//arrFile[arrFile.length - 1]
					$("#eams_form_name_holder").html("<a href='" + data.file + "' title='Click to open pdf' class='white_text' target='_blank'>Click to open generated PDF</a>");
					$("#eams_form_name_holder").css("border", "2px solid white");
					$("#eams_form_name_holder").css("background", "green");
					//window.open(data.file, "EAMS");
					//$('#myModal4').modal('toggle');
					/*
					//store the search element if any
					var current_search;
					if ($("#letter_searchList").val()!="") {
						current_search = $("#letter_searchList").val();
					}
					$('#myModal4').modal('toggle');			
					
					letters = new KaseLetters([], { case_id: case_id });
					letters.fetch({
						success: function(data) {
							var kase = kases.findWhere({case_id: case_id});
							kase.set("no_uploads", true);
							$('#kase_content').html(new letter_listing_view({collection: data, model: kase}).render().el);
							$("#kase_content").removeClass("glass_header_no_padding");
							
							setTimeout(function(){
								$("#letter_searchList").val(current_search);
								$( "#letter_searchList" ).trigger( "keyup" );
							}, 500);
						}
					});
					*/
				}
			}
		});
	}
	if ($("#modal_type").val() == "message") {
		var to_token_input = $("#message_toInput").val();
		if (to_token_input == "") {
			$("#message_to_td .token-input-list-facebook").css("border", "red 1px solid");
			return;
		}
	
		var url = 'api/messages/add';
		var formValues = $("#interoffice_form").serialize();
		
		//attachments
		var arrAttach = [];
		var arrAttachments = $("#queue .filename").children();
		var arrayLength = arrAttachments.length;
		for (var i = 0; i < arrayLength; i++) {
			arrAttach[i] = arrAttachments[i].href.replace(hrefHost, "");
		}
		formValues += "&attachments=" + arrAttach.join("|");
		
		if (document.getElementById("apply_notes").checked) {
			
			var notes_url = 'api/notes/add';
			notesformValues = formValues.replace("table_name=message", "table_name=notes");
			notesformValues = notesformValues.replace("messageInput=", "noteInput=");
			notesformValues = notesformValues.replace("case_fileInput=", "case_id=");
			
			//return;
			$.ajax({
			url:notes_url,
			type:'POST',
			dataType:"json",
			data: notesformValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					}
				}
			});	
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
				//hide the modal
					//$('#myModal4').modal('toggle');
					emptyBuffer();
				}
			}
		});
		$('#myModal4').modal('toggle');
		//emptyBuffer();
	}
	if ($("#modal_type").val() == "task") {
		var id = $(".task #table_id").val();
		var url = 'api/task/add';
		if (id > 0) {
			url = 'api/task/update';
		}

		var formValues = $("#task_form").serialize();
		
		//attachments
		var arrAttach = [];
		var arrAttachments = $("#queue .filename").children();
		var arrayLength = arrAttachments.length;
		for (var i = 0; i < arrayLength; i++) {
			arrAttach[i] = arrAttachments[i].href.replace(hrefHost, "");
		}
		formValues += "&attachments=" + arrAttach.join("|");
		
		$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else { // If not
				//hide the modal
					$('#myModal4').modal('toggle');
					emptyBuffer();
					
					if ($(document).attr('title') == "Task Inbox :: iKase") {
						//fetch all tasks
						tasks = new TaskInboxCollection();
						tasks.fetch({
							success: function (data) {
								var task_listing_info = new Backbone.Model;
								task_listing_info.set("title", "Task Inbox");
								task_listing_info.set("receive_label", "Due Date");
								$('#content').html(new task_listing({collection: data, model: task_listing_info}).render().el);
							}
						});
					}
					if ($(document).attr('title') == "Task Outbox :: iKase") {
						//fetch all tasks
						tasks = new TaskOutboxCollection();
						tasks.fetch({
							success: function (data) {
								var task_listing_info = new Backbone.Model;
								task_listing_info.set("title", "Task Inbox");
								task_listing_info.set("receive_label", "Due Date");
								$('#content').html(new task_listing({collection: data, model: task_listing_info}).render().el);
							}
						});
					}
				}
			}
		});
	}
	if ($("#modal_type").val() == "note") {
		var id = $(".new_note #table_id").val();
		var url = 'api/notes/add';
		if (id > 0) {
			url = 'api/notes/update';
		}
		var formValues = $("#new_note_form").serialize();
		
		//attachments
		var arrAttach = [];
		var arrAttachments = $("#queue .filename").children();
		var arrayLength = arrAttachments.length;
		for (var i = 0; i < arrayLength; i++) {
			arrAttach[i] = arrAttachments[i].href.replace(hrefHost, "");
		}
		formValues += "&attachments=" + arrAttach.join("|");
		
		
		$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else { // If not
				//hide the grid containing the element
					$('#myModal4').modal('toggle');
					if ($("#note_listing").length > 0) {
						if ($("#partie_notes").length==0) { 
							//refresh quick notes
							notes = new NoteCollection([], { case_id: current_case_id });
							notes.fetch({
								success: function(data) {
									var note_list_model = new Backbone.Model;
									note_list_model.set("display", "full");
									note_list_model.set("partie_type", "note");
									note_list_model.set("partie_id", -1);
									$('#kase_content').html(new note_listing_view({collection: data, model: note_list_model}).render().el);
								}
							});
						} else {
							var partie_type = $("#partie_notes").attr("class")
							var partie_notes = new NotesByType([], {type: partie_type, case_id: current_case_id});
							partie_notes.fetch({
								success: function(data) {
									var note_list_model = new Backbone.Model;
									note_list_model.set("display", "sub");
									note_list_model.set("partie_id", $("#table_id").val());
									note_list_model.set("partie_type", partie_type);
									$('#partie_notes').html(new note_listing_view({collection: data, model: note_list_model}).render().el);
								}
							});
						}
					}
					if ($("#noteSpan").length > 0) { 
						//refresh quick notes
						var quick_notes = new NotesByType([], {type: "quick", case_id: current_case_id});
						quick_notes.fetch({
							success: function(data) {	
								var notes = data.toJSON();
								var arrNotes = [];
								 _.each(notes , function(quicknote) {
									arrNotes[arrNotes.length] = "<div class='quicknote_row' id='quicknote_" + quicknote.notes_id + "'><div style='font-size:0.8em'>" + moment(quicknote.dateandtime).format("MM/DD/YY h:mm a") + " by <span style='font-style:italic;'" + quicknote.entered_by + "</span></div><div>" + quicknote.note + "</div></div>"; 
								 });
								quick_note = arrNotes.join("\r\n");
								$("#noteSpan").html(quick_note);
							}
						});
					}
					//partie list
					if ($("#partie_notes").length > 0) {
						var partie_type = $("#partie_notes").attr("class");
						var partie_notes = new NotesByType([], {type: partie_type, case_id: current_case_id});
						partie_notes.fetch({
							success: function(data) {	
								var note_list_model = new Backbone.Model;
								note_list_model.set("display", "sub");
								note_list_model.set("partie_type", partie_type);
								note_list_model.set("partie_id", $("." + partie_type + " #table_id").val());
								$('.' + partie_type + '#partie_notes').html(new note_listing_view({collection: data, model: note_list_model}).render().el);	
								$('.' + partie_type + '#partie_notes').fadeIn();
							}
						});
					}
				}
			}
		});
	}
	$("#modal_save_holder").show();
	$("#gifsave").hide();
}
function emptyBuffer() {
	var url = 'api/buffer';
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		data: "",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else { // If not
					//need notification
				}
			}
		});
}
function selectAllParties () {
	$(".parties_option").prop("checked", $("#parties_selectall").prop("checked"));
}