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
var stored_customer_events;
var corporations;
var parties;
var recent_occurences;
var recent_tasks;
var kaseKAIView;
var partyKaseView;
var occurencesView;
var current_case_id;
var current_calendar_id = -1;
var current_employee_calendars;

var current_calendar_view = "";
var current_calendar_start = "";
var current_calendar_end = "";
				
var calendar_element;
var calendar_event;
var word_templates;
var kase_letters;
var blnSearchingKases = false;
var blnAssigning = false;
var workingWeekTimes = [
	'06:00am', '06:30am', '07:00am', '07:30am', '08:00am', '08:30am',
	'09:00am', '09:30am', '10:00am', '10:30am', '11:00am', '11:30am', 
	'12:00pm', '12:30pm', '13:00pm', '13:30pm', '14:00pm',
	'14:30pm', '15:00pm', '15:30pm', '16:00pm', '16:30pm',
	'17:00pm', '17:30pm', '18:00pm', '18:30pm', '19:00pm'
];
var loading_image = '<div id="document_listing_loading" style="display:; text-align:center; font-size:0.8em" class="white_text"><i class="icon-spin4 animate-spin" style="font-size:6em; color:white"></i><br /><br />Loading <span id="loading_progress_span"></span>...</div>';

//get webmails, keep track of count
//var webmails = new WebmailCollection();
var webmails = new WebmailLimonade();
var blnReceiveWebmail = false;	//this is changed in kase_nav_bar.js
var refreshdocument_id = false;
window.Router = Backbone.Router.extend({
    routes: {
        "": "home",
		"home": "home",
		"search_docs": "searchDocs",
		"accident/:case_id": 									"kaseAccident",
		"notemobile/:case_id": 									"noteMobileView",
		"notemobilelist/:case_id": 								"listNotesMobileView",
		"taskmobile/:case_id": 									"taskMobileView",
		"taskmobilelist/:case_id": 								"listTasksMobileView",
		"eventmobile/:case_id": 								"eventMobileView",
		"eventmobilelist/:case_id": 							"listEventsMobileView",
		"accident/:case_id/:accident_type": 					"kaseAccidentDash",
		"personal_injury/:case_id": 							"kasePersonalInjury",
		"activity/:id": 										"kaseActivity",
		"activities/:user_id/:start_date/:end_date": 			"userActivity",
		"applicant/:id": 										"kaseApplicant",
		"archives/:case_number" : 								"listArchives",
		"archives_legacy/:case_id" : 							"listLegacyArchives",
		"billing/:id": 											"kaseChecks",
		"compose": 												"newMessage",
		"dailytask/:day":										"listTaskInboxToday",
		"dailytaskall/:day":									"listAllTaskInboxToday",
		"documentmobile/:case_id" : 									"documentMobileView",//solulab added function 11-4-2019
		"documents/:case_id" : 									"listDocuments",
		"document_search" : 									"searchDocuments",
		"edit/:id": 											"editKase",
		"eams_forms/:case_id" : 								"listEams",
		"eams_search":											"searchEAMS",
		"events": 												"listEvents",
		"contacts": 											"listContactEmails",
		"contacts/:id": 										"editContactEmail",
		"emailsettings":										"displayEmailSettings",
		"employee_kalendar":									"listEmployeeEvents",
		"exams/:case_id": 										"listExams",
		"forms" : 												"listEamsForms",
		"firmkalendar":											"kaseCustomerEvents",
		"ikalendar/:calendar_id/:sort_order":					"displayCalendarEvents",
		"inbox": 												"listInbox",
		"import" : 												"documentImport",
		"imports" : 											"listImports",
		"listkalendar/:calendar_id/:sort_order/:start/:end":	"listCalendarEvents",
		"listkustom/:calendar_id/:sort_order/:start/:end":		"displayCustomCalendarByDates",
		"inactive/:days":										"listInactives",
		"injury/:case_id" : 									"kaseInjury",
		"injury/:case_id/:injury_id" : 							"kaseSpecificInjury",
		"intakekalendar":										"kaseCustomerIntakes",
		"kalendar/:id": 										"kaseEvents",
		"kalendarbydate/:id/:start/:end":						"kaseEventsByDate",		
		"kalendars": 											"listCalendars",
		"kases/:id": 											"dashboardKase",
		"kasesdash/:id": 										"dashboardKaseByPartyOption",
		"kases": 												"listKases",
		"kaseslist/:corporation_id/:type": 						"listCompanyKases",
		"kases/events/edit/:case_id/:event_id/:day_date": 		"kaseEventDialog",
        "kases/kai/:id": 										"kaseKAI",
		"kases/related_cases/:id": 								"dashboardRelatedKase",
		"kontrol_panel/:id": 									"showKontrolPanel",
		"letters/:case_id" : 									"listKaseLetters",
		"lien/:case_id/:injury_id":								"showLien",
		"logout" : 												"logout",
		"messages/:message_id": 								"editMessage",
		"newinjury/:case_id":									"newInjury",
		"notes/:case_id" : 										"listNotes",
		"notes/:case_id/:notes_id/:type" : 						"editNote",
		"outbox": 												"listOutbox",
		"parties/:id" : 										"listParties",
		"parties/:case_id/:id/:type" : 							"editPartie",
		"partner_calendar":										"listPartnerEvents",
		"newpartie":	 										"newRoloPartie",
		"newpartie/:type":	 									"editRoloNewPartie",
		"personalkalendar":										"kasePersonalEvents",
		"phone/:case_id/:event_id": 							"kasePhone",
		"phoneinbox":											"listPhoneInbox",
		"prior_treatment/:case_id/:corporation_id/:person_id":	"priorTreatment",
		"qme/:zip":												"searchQME",
		"recentkases":											"listRecentKases",
		"rolodex/:id/:type" : 									"editRoloPartie",
		"rolodexperson/:id" : 									"editRoloPerson",
		"rolodex":												"listContacts",
		"settlement/:case_id/:injury_id":						"showSettlement",
		"settings": 											"listCustomerSetting",
		"subscribekalendar":									"showSubscriptionLink",
		"taskcompleted": 										"listTaskCompleted",
		"taskcompletedall/:day":								"listAllTaskCompleted",
		"taskinbox": 											"listTaskInbox",
		"taskoutbox": 											"listTaskOutbox",
		"tasks/:case_id": 										"listCaseTasks",		
		"taskbydates/:start/:end":								"kaseCustomerTasks",		
		"templates" : 											"listTemplates",
		"todo": 												"listEventsing",
		"unassigned": 											"documentUnassigned",
		"unassigneds" : 										"listUnassigneds",
		"userkalendar/:user_id":								"showUserCalendar",
		"usersettings": 										"listUserSetting",
		"upload/:id/:table_name": 								"documentUpload",
		"users" : 												"listUsers",
		"users/:id" : 											"editUser",
		"users/email/:user_id" : 								"userEmail",
		"vservices/:case_id":									"listServices",
		"webmail":												"listWebmails"
	},
    initialize: function () {
		//alert("working on this now");
		var self = this;
		$("#mobile_content").html("");
		var current_location = document.location.href;
		if (current_location.indexOf("?n=") > 0) {
			current_location = current_location.replace("?n=", "");
			if (window.history.replaceState) {
			   //prevents browser from storing history with each change:
			   window.history.replaceState(document.location.hash, $(document).find("title").text(), current_location);
			}
		}
		readCookie();
		writeCookie('origin', '');
		//keep track for next restart
		var cookie_customer_id = customerCookie();
		current_max_track_id = maxTrackCookie("event");
		if (typeof current_max_track_id == "undefined") {
			current_max_track_id = 0;
		}
		if (cookie_customer_id!=customer_id) {
			current_max_track_id = 0;
			//reset the events storage
			checkForChanges("event");
		} else {
			setTimeout(function() {
				clearStoredEvents(current_max_track_id, 0);
			}, 2500);
		}
		
		writeCookie('current_customer_id', customer_id, 24*60*60*1000);
		
		// Close the search dropdown on click anywhere in the UI
		$('body').click(function () {
			$('.dropdown').removeClass("open");
		});
		$('.dropdown').click(function () {
			$('.dropdown-toggle').dropdown("toggle");										  
		});
		
		//navigation
		var search_kases = new KaseRecentCollection();
		this.headerView = new kase_nav_bar_view_mobile({model: search_kases});
		$('.kase_header').html(this.headerView.render().el);								
		
		//left column navigation
		$('.left_sidebar').html(new kase_nav_left_view().render().el);								
		//alert("working on this now");
		$('#left_sidebar').hide();
		$("#mobile_content").hide();
		$("#search_results").removeClass("col-md-10");
		$("#search_results").addClass("col-md-12");
		$("#content").removeClass("col-md-10");
		$("#content").addClass("col-md-12");
		$("#content").css("margin-top", "65px");
		if ($("#content").css("margin-top") != "65px") {
			$("#content").css("margin-top", "0px");
		}
		$("#left_side_show").show();
		
		//recent events
		setTimeout(function() {
			//recent kases		
			$('#kases_recent').html(new kase_list_category_view({model: recent_kases}).render().el);
			$('#occurences_recent').html(new kase_list_task_view({model: recent_tasks}).render().el);
			self.recentOccurences();
			self.recentTasks();
		}, 1500);
		
		//start polling inbox
		//pollChat();
		
		//slight delay
		setTimeout(function() {
			checkInbox();
		}, 4300);
		
		//slight delay
		setTimeout(function() {
			checkTaskInbox();
		}, 5700);
		
		//slight delay
		setTimeout(function() {
			checkImports();
			//checkOrphanImports();
		}, 6300);
		setTimeout(function() {
			checkUnassigneds();
			//checkOrphanUnassigneds();
		}, 7300);
		
		//get the email now
		getMail();
		
		var event_counts = new EventCountCollection;
		event_counts.fetch({
			success: function (data) {
				var counts = data.toJSON();
	
				var theyear = moment().format("YYYY");
				var themonth = moment().format("M");
				var blnAdjusted = false;
				 _.each( counts, function(event_count) {
					 if (!blnAdjusted) {
						 if (event_count.event_year == theyear && event_count.event_month) {
							calendarContentHeight = (event_count.event_counts * 92 * 5) + 200;
							blnAdjusted = true;
						 }
					 }
				 });
			}
		});
    },
	change: function(trigger, args) { 
		executeMainChanges();
	},
    home: function () {
		//alert("working on this right now");
		executeMainChanges();
		this.clearSearchResults();
		
		$("#search_results").html("");
		$("#mobile_content").hide();
		$("#content").css("margin-top", "60px");
		$("#content").show();
		$("#content").html(loading_image);
		
		current_case_id = -1;
		readCookie();
		var self = this;
		
		
		$(document).attr('title', "Welcome to iKase");
		
		$("#mobile_content").html("");
		setTimeout(function() {
	//		alert("working on this for now");
			$('#left_sidebar').hide();
			var search_kases = new KaseCollection();
			$('#content').html(new dashboard_home_view_mobile({collection: search_kases}).render().el);
			$("#left_side_show").show();
			if (!blnSearched) {
				if ($("#content").css("top")=="0px") {
					$("#content").css("top", "00px");
				}
			
			
			} else {
				//reset it here
				//blnSearched = false;
			}
			
			//nav		
			self.headerView.select_menu('home-menu');
		}, 300);
		//$("#search_results").css("margin-top", "60px");
		
		var tasks = new TaskInboxCollection({day: todays_date, single_day: todays_date});
			
		tasks.fetch({
			success: function (data) {
				var task_listing_info = new Backbone.Model;
				task_listing_info.set("title", "Task Inbox");
				task_listing_info.set("receive_label", "Due Date");
				task_listing_info.set("homepage", true);
				task_listing_info.set("case_id", "");
				$("#task_mobile_home").html(new task_listing_mobile({collection: data, model: task_listing_info}).render().el);
				//$("#tasks_mobile").css("width", "460px");
			}
		});
		
		var occurences = new OccurenceCollection({start: todays_date, end: todays_date});
			
		occurences.fetch({
				success: function(data) {
					var kase = new Backbone.Model;
					kase.set("title", "Today's Events");
					kase.set("start", todays_date);
					kase.set("end", todays_date);
					kase.set("homepage", true);
					kase.set("case_id", "");
					var homepage = true;
					kase.set("event_class", "listing");
					
					$("#event_mobile_home").html(new event_listing_mobile({collection: occurences, model: kase}).render().el);
					//, homepage: kase.get("homepage"), title: kase.get("title"), case_id: kase.get("case_id"), start: kase.get("start"), end: kase.get("end")
					//$("#events_mobile").css("width", "460px");
				}
			}
		);
		$("#search_results").html('<ul role="tablist" class="nav nav-tabs mobile_tabs_home" style="width:100%; margin-right:auto; margin-left:15px"><li role="presentation" class="tasks_mobile_home active"><a href="#task_mobile_home" aria-controls="task_mobile_home" role="tab" data-toggle="tab" style="border-radius: 4px 4px 0 0; border-bottom:1px solid transparent border:1px solid white; height:38px; color:white; font-size:1.5em; padding-top:5px">Tasks&nbsp;&nbsp;<i style="color:#EDEDED; cursor:pointer;" class="glyphicon glyphicon-tasks" title=""></i></a></li><li role="presentation" class="events_mobile_home"><a href="#event_mobile_home" aria-controls="event_mobile_home" role="tab" data-toggle="tab" style="border-radius: 4px 4px 0 0; border-bottom:1px solid transparent border:1px solid white; height:38px; color:white; font-size:1.5em; padding-top:5px">Events&nbsp;&nbsp;<i style="color:#EDEDED; cursor:pointer;" class="glyphicon glyphicon-calendar" title=""></i></a></li></ul><div class="tab-content" style="margin-left:15px"><div role="tabpanel" class="tab-pane active" id="task_mobile_home" style="background:url(img/glass.png) repeat; color:#FFF">Daily Tasks</div><div role="tabpanel" class="tab-pane fade" id="event_mobile_home" style="background:url(img/glass.png) repeat; color:#FFF">Daily Events</div></div>');
		$("#search_results").show();
    },
	searchDocs: function () {
	//	alert("working on this right now");
		executeMainChanges();
		this.clearSearchResults();
		
		$("#search_results").html("");
		$("#mobile_content").hide();
		$("#content").css("margin-top", "60px");
		$("#content").show();
		$("#content").html(loading_image);
		
		current_case_id = -1;
		readCookie();
		var self = this;
		
		
		$(document).attr('title', "Welcome to iKase");
		
		$("#mobile_content").html("");
		setTimeout(function() {
		//	alert("working on this for now");
			$('#left_sidebar').hide();
			var search_kases = new KaseCollection();
			$('#content').html(new dashboard_home_view_mobile({collection: search_kases}).render().el);
			$("#left_side_show").show();
			if (!blnSearched) {
				if ($("#content").css("top")=="0px") {
					$("#content").css("top", "00px");
				}
			
			
			} else {
				//reset it here
				//blnSearched = false;
			}
			
			//nav		
			self.headerView.select_menu('home-menu');
		}, 300);
		//$("#search_results").css("margin-top", "60px");
		
		var tasks = new TaskInboxCollection({day: todays_date, single_day: todays_date});
			
		tasks.fetch({
			success: function (data) {
				var task_listing_info = new Backbone.Model;
				task_listing_info.set("title", "Task Inbox");
				task_listing_info.set("receive_label", "Due Date");
				task_listing_info.set("homepage", true);
				task_listing_info.set("case_id", "");
				$("#task_mobile_home").html(new task_listing_mobile({collection: data, model: task_listing_info}).render().el);
				//$("#tasks_mobile").css("width", "460px");
			}
		});
		
		var occurences = new OccurenceCollection({start: todays_date, end: todays_date});
			
		occurences.fetch({
				success: function(data) {
					var kase = new Backbone.Model;
					kase.set("title", "Today's Events");
					kase.set("start", todays_date);
					kase.set("end", todays_date);
					kase.set("homepage", true);
					kase.set("case_id", "");
					var homepage = true;
					kase.set("event_class", "listing");
					
					$("#event_mobile_home").html(new event_listing_mobile({collection: occurences, model: kase}).render().el);
					//, homepage: kase.get("homepage"), title: kase.get("title"), case_id: kase.get("case_id"), start: kase.get("start"), end: kase.get("end")
					//$("#events_mobile").css("width", "460px");
				}
			}
		);
		$("#search_results").html('<ul role="tablist" class="nav nav-tabs mobile_tabs_home" style="width:100%; margin-right:auto; margin-left:15px"><li role="presentation" class="tasks_mobile_home active"><a href="#task_mobile_home" aria-controls="task_mobile_home" role="tab" data-toggle="tab" style="border-radius: 4px 4px 0 0; border-bottom:1px solid transparent border:1px solid white; height:38px; color:white; font-size:1.5em; padding-top:5px">Tasks&nbsp;&nbsp;<i style="color:#EDEDED; cursor:pointer;" class="glyphicon glyphicon-tasks" title=""></i></a></li><li role="presentation" class="events_mobile_home"><a href="#event_mobile_home" aria-controls="event_mobile_home" role="tab" data-toggle="tab" style="border-radius: 4px 4px 0 0; border-bottom:1px solid transparent border:1px solid white; height:38px; color:white; font-size:1.5em; padding-top:5px">Events&nbsp;&nbsp;<i style="color:#EDEDED; cursor:pointer;" class="glyphicon glyphicon-calendar" title=""></i></a></li></ul><div class="tab-content" style="margin-left:15px"><div role="tabpanel" class="tab-pane active" id="task_mobile_home" style="background:url(img/glass.png) repeat; color:#FFF">Daily Tasks</div><div role="tabpanel" class="tab-pane fade" id="event_mobile_home" style="background:url(img/glass.png) repeat; color:#FFF">Daily Events</div></div>');
		$("#search_results").show();
    },
	documentImport: function() {
		executeMainChanges();
		current_case_id = -1;
		var mymodel = new Backbone.Model();
		mymodel.set("holder", "content");
		mymodel.set("import_type", "batchscan");
		$('#content').html(new import_remote_view({model: mymodel}).render().el);
		//$('#content').html(new import_view({model: mymodel}).render().el);
		
		$("#content").addClass("glass_header_no_padding");
	},
	listNotesMobileView: function(case_id) {
		showTabs(case_id);
		
		setTimeout(function() {
			$("#notes_mobile_link").trigger("click");
		}, 1000);
	},
	listTasksMobileView: function(case_id) {
		showTabs(case_id);
		
		setTimeout(function() {
			$("#tasks_mobile_link").trigger("click");
		}, 1000);
	},
	listEventsMobileView: function(case_id) {
		showTabs(case_id);
		
		setTimeout(function() {
			$("#events_mobile_link").trigger("click");
		}, 1000);
	},
	noteMobileView: function(case_id) {
		executeMainChanges();
		current_case_id = case_id;
		var new_note = new Note({notes_id: -1, case_id: case_id});
		new_note.set("holder", "content");
		new_note.set("case_id", case_id);
		$('#mobile_content').html(new notes_view_mobile({model: new_note}).render().el);
		$('#content').hide();
		$('#search_results').hide();
		$('#mobile_content').css("margin-top", "60px");
		$('#mobile_content').show();
		
		//$("#mobile_content").addClass("glass_header_no_padding");
	},
	taskMobileView: function(case_id) {
		executeMainChanges();
		current_case_id = case_id;
		
		var new_task = new Task({case_id: case_id});
		new_task.set("holder", "content");
		new_task.set("case_id", case_id);
		$('#mobile_content').html(new task_view_mobile({model: new_task}).render().el);
		$('#content').hide();
		$('#search_results').hide();
		$('#mobile_content').css("margin-top", "60px");
		$('#mobile_content').show();
	},
	eventMobileView: function(case_id) {
		executeMainChanges();
		current_case_id = case_id;
		var new_event = new Occurence({case_id: case_id});
		new_event.set("holder", "content");
		new_event.set("case_id", case_id);
		$('#mobile_content').html(new event_view_mobile({model: new_event}).render().el);
		$('#content').hide();
		$('#search_results').hide();
		$('#mobile_content').css("margin-top", "60px");
		$('#mobile_content').show();
		
		//$("#mobile_content").addClass("glass_header_no_padding");
	},
/* solulab code start 11-4-2019*/
	documentMobileView: function(case_id) {
		executeMainChanges();
		current_case_id = case_id;
		var new_document = new Document({case_id: case_id});
		new_document.set("holder", "content");
		new_document.set("case_id", case_id);
		$('#mobile_content').html(new document_view_mobile({model: new_document}).render().el);
		$('#content').hide();
		$('#search_results').hide();
		$('#mobile_content').css("margin-top", "60px");
		$('#mobile_content').show();
		
		//$("#mobile_content").addClass("glass_header_no_padding");
	},
	/* solulab code start end 11-4-2019*/
	documentUnassigned: function() {
		executeMainChanges();
		current_case_id = -1;
		var mymodel = new Backbone.Model();
		mymodel.set("holder", "content");
		mymodel.set("import_type", "unassigned");
		$('#content').html(new import_view({model: mymodel}).render().el);
		$("#content").addClass("glass_header_no_padding");
	},
	documentUpload:  function (case_id, table_name) {
		current_case_id = case_id;
		executeMainChanges();
		readCookie();
		var self = this;
		
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.documentUpload(case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
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
	clearSearchResults: function() {
		clearSearchResults();
	},
	dashboardKase: function (case_id) {
		current_case_id = case_id;
		
		showTabs(case_id);
    },
	dashboardKaseByPartyOption: function (case_id) {
		//clear out any search results
		this.clearSearchResults();
		executeMainChanges();
		
		readCookie();
		var self = this;
		
		if (case_id > -1) {
			var kase = kases.findWhere({case_id: case_id});
			if (typeof kase == "undefined") {
				//get it
				var kase =  new Kase({id: case_id});
				kase.fetch({
					success: function (kase) {
						if (kase.toJSON().uuid!="") {
							kases.add(kase);
							self.dashboardKaseByPartyOption(case_id);
						} else {
							//case does not exist, get out
							document.location.href = "#";
						}
						return;		
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
			if (current_case_id	!= case_id) {
				kase.set("header_only", true);
				$('#content').html(new kase_view({model: kase}).render().el);
			}
			
			$(document).attr('title', kase.get("full_name") + " vs " + kase.get("employer") + " :: " + kase.get("case_number") + " :: iKase");
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
				if ((parties.length < 2 && case_id > 0 && blnNoApplicant) || blnNoApplicant) {
					self.kaseApplicant(case_id);
					return;
				}
				if (parties.length == 2 && case_id > 0) {
					//is the first partie the applicant?  the second is always the venue
					if (parties.toJSON()[0].type=="applicant" && parties.toJSON()[1].type=="venue") {
						//we must have the employer next, at least for WCAB
						var kase_type = kase.get("case_type");
						var blnWCAB = isWCAB(kase_type);
						if (blnWCAB) {
							self.editPartie(case_id, -1, "employer");
						}
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
						var kase_type = kase.get("case_type");
						var blnWCAB = isWCAB(kase_type);
						if (blnWCAB) {
							self.editPartie(case_id, -1, "employer");
						}
						//$("#content").removeClass("glass_header_no_padding");
						//$('#content').html(new kase_view({model: kase}).render().el);
						hideEditRow();
						return;
					}
				}
				if (parties.length > 2 && case_id > 0) {
					//we have applicant, venue and 1 partie, we need employer
					employer_partie = parties.findWhere({"type": "employer"});
					var kase_type = kase.get("case_type");
					var blnWCAB = isWCAB(kase_type);
					if (blnWCAB) {
						if (typeof employer_partie == "undefined") {
							self.editPartie(case_id, -1, "employer");
							return;
						}		
					}
					//we have employer, we need injury
					var kase_dois = new KaseInjuryCollection({case_id: case_id});
					kase_dois.fetch({
						success: function(kase_dois) {
							var kase_type = kase.get("case_type");
							var blnWCAB = isWCAB(kase_type);
							if (blnWCAB) {
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
				$('#kase_content').html(new partie_option_cards_view({collection: parties, model: kase}).render().el);
				$("#kase_content").removeClass("glass_header_no_padding");
				hideEditRow();
			}
		});
		//if (blnShowHeader) {
			if (current_case_id!=case_id) {
				kase.set("header_only", true);
				$('#content').html(new kase_view({model: kase}).render().el);
			}
		//}
		current_case_id = case_id;
		
		setTimeout(function(){
			self.recentKases();	
		}, 700);
    },
	dashboardRelatedKase: function (case_id) {
		//clear out any search results
		this.clearSearchResults();
		//var case_id = current_case_id;
		executeMainChanges();
		readCookie();
		var self = this;
		
		if (case_id > -1) {
			var kase = kases.findWhere({case_id: case_id});
			if (typeof kase == "undefined") {
				//get it
				var kase =  new Kase({id: case_id});
				kase.fetch({
					success: function (kase) {
						if (kase.toJSON().uuid!="") {
							kases.add(kase);
							self.dashboardRelatedKase(case_id);
						} else {
							//case does not exist, get out
							document.location.href = "#";
						}
						return;		
					}
				});
				return;
			}
			
			if (current_case_id	!= case_id) {
				kase.set("header_only", true);
				$('#content').html(new kase_view({model: kase}).render().el);
			}
			
			$(document).attr('title', "Related Kases :: " + kase.get("full_name") + " vs " + kase.get("employer") + " :: " + kase.get("case_number") + " :: iKase");
			if (typeof kase == "undefined") {
				//case does not exist, get out
				document.location.href = "#";
				return;
			}
		} 
		var kase_dois = new KaseInjuryCollection({case_id: case_id});
		kase_dois.fetch({
			success: function(kase_dois) {
				$("#content").removeClass("glass_header_no_padding");
				$('#content').html(new kase_view({model: kase}).render().el);

				//related cases list view
				kase.set("holder", "kase_content");
				$('#kase_content').html(new dashboard_related_cases_view({collection: kase_dois, model: kase}).render().el);
				$("#kase_content").removeClass("glass_header_no_padding");
				hideEditRow();		
			}
		});
		
		current_case_id = case_id;
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
	listCompanyKases: function (corporation_id, corporation_type) {
		$("#content").html(""); 
		executeMainChanges();
		this.clearSearchResults();
		
		var corporation = new Corporation({id: corporation_id, type:corporation_type});
		corporation.fetch({
			success: function (corp) {
				var key = corp.toJSON().parent_corporation_uuid;
				var modifier = corporation_type;
				
				kase_searching = true;
				blnSearched = true;
				$('#ikase_loading').html(loading_image);
				var self = this;
				//var key = $('#srch-term').val();
				
				if (typeof key =="undefined") {
					return;
				}
				var my_kases = new KaseCollection();
				//look for modifiers
				
				blnSearchingKases = true;
				search_kases = my_kases.searchDB(key, modifier);
			}
		});
	},
	listKases: function () {
		executeMainChanges();
		this.clearSearchResults();
		
		$("#srch-term").trigger("dblclick");
		//return;
		$('#ikase_loading').html(loading_image);
		//executeMainChanges();
		current_case_id = -1;
		readCookie();
		$(document).attr('title', "Kases List");
		//var mycollection = new KaseCollection;
		var mymodel = new Backbone.Model();
		mymodel.set("key", "");
		//mymodel.set("sort_by", "last_name");
		$('#content').html(new kase_listing_view_mobile_mobile({collection: kases, model:mymodel}).render().el);
		setTimeout(function() {
			$("#kase_status_title").html("Recent Active");
		}, 700);
    },
	listInactives: function() {
		executeMainChanges();
		this.clearSearchResults();
		
		readCookie();
		var self = this;
		if ($("#search_results").html()!="") {
			$("#search_results").html("");
			$(".search #srch-term").val("");
		}
		$("#content").html(loading_image);
		var inactive_cases = new InactiveCasesCollection({days: 45});
		inactive_cases.fetch({
			success: function (data) {
				//then re-assign to calendar
				$("#content").html("");
				var mymodel = new Backbone.Model();
				mymodel.set("key", "");
				var inactiveView = new kase_listing_view_mobile({el: $("#content"), collection: inactive_cases, model: mymodel}).render();
				setTimeout(function() {
					$("#kase_status_title").html("Inactive");
				}, 700);
			}
		});
	},
	kasePhone: function (case_id, event_id) { 
		current_case_id = case_id;
		
		var self = this;
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.kasePhone(case_id, event_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
		kase.set("header_only", true);
		$('#content').html(new kase_view({model: kase}).render().el);
		setTimeout(function() {
			self.kaseEventTypeDialog(case_id, event_id, "phone_call");
		}, 100);
	},
	listCalendars: function () { 
		executeMainChanges();
		current_case_id = -1;
		readCookie();
		
		//fetch all messages
		calendars = new CalendarCollection();
		calendars.fetch({
			success: function (data) {
				$('#content').html(new calendar_listing_view({collection: data}).render().el);
				$("#content").removeClass("glass_header_no_padding");
			}
		});			
	},
	listCalendarEvents: function(calendar_id, sort_order, start, end) {
		executeMainChanges();
		this.clearSearchResults();
		
		current_calendar_id = calendar_id;
		//for the most part, mandatory calendars
		switch(sort_order) {
			case "-2":
				this.kaseCustomerEventsByDate(start, end);
				break;
			case "-1":
				this.kaseCustomerEventsByDate(start, end);
				break;
			case "0":
				this.kaseCustomerEventsByDate(start, end);
				break;
			case "4":
				this.kaseCustomerIntakes(start, end);
				break;
			case "5":
				//this.kasePersonalEvents();
				this.showUserCalendar(login_user_id, start, end);
				break;
			default:
				return;
				//this.displayCustomCalendarByDates(calendar_id, start, end);
		}
	},
	kaseCustomerEventsByDate: function (start, end) {		
		listCustomerEvents(start, end);
	},
	displayCustomCalendarByDates: function (case_id, start, end) {		
		var display_start = moment(start).format("MM/DD/YYYY");
		var display_end = moment(end).format("MM/DD/YYYY");
		
		if (display_start != display_end) {
			display_start += " through " + display_end;
		}
		//note the calendar id is really a case id
		occurences = new OccurenceCollection({case_id: case_id, start: start, end: end});
		occurences.fetch({
				success: function(data) {
					//then re-assign to calendar
					var kase = new Backbone.Model;
					kase.set("title", "Events: " + display_start);
					kase.set("case_id", case_id);
					kase.set("homepage", false);
					kase.set("event_class", "listing");
					kase.set("start", moment(start).format("MM/DD/YYYY"));
					kase.set("end", moment(end).format("MM/DD/YYYY"));
					occurencesView = new event_listing({el: $("#content"), collection: occurences, model:kase}).render();
				}
			}
		);
	},
	listPartnerEvents: function() {
		this.displayCalendarEvents("-1", "-99");
	},
	listEmployeeEvents: function() {
		this.displayCalendarEvents("-1", "-98");
	},
	displayCalendarEvents: function(calendar_id, sort_order) {
		this.clearSearchResults();
		
		$(document).attr('title', 'Kalendar');
		var self = this;
		if (sort_order < 1 && sort_order > -10) {
			if (typeof stored_customer_events == "undefined") {
				stored_customer_events = new OccurenceStoredCustomerCollection();
				var fetched_customer_events = new OccurenceCustomerCollection();
				
				stored_customer_events.fetch({
					success: function(stored_customer_events) {
						if (stored_customer_events.length==0) {
							fetched_customer_events.fetch({
								success: function(data) {
									var the_events = data.toJSON();
									_.each( the_events, function(customer_event) {
										var myOccurence = new Occurence({event_id: customer_event.id});
										myOccurence.set(customer_event);
										stored_customer_events.add(myOccurence);
										myOccurence.save();
									});
									self.displayCalendarEvents(calendar_id, sort_order);
								}
							});
						} else {
							self.displayCalendarEvents(calendar_id, sort_order);
						}
					}
				});
				return;
			}
		}
		current_calendar_id = calendar_id;
		//for the most part, mandatory calendars
		switch(sort_order) {
			case "-99":
				this.partnerEvents();
				break;
			case "-98":
				this.employeeEvents();
				break;
			case "-2":
				this.kaseCustomerEventsByWeek();
				break;
			case "-1":
				this.kaseCustomerEventsByDay();
				break;
			case "0":
				//Backbone.history.navigate('firmkalendar'); 
				this.kaseCustomerEvents();
				break;
			case "1":
				//Backbone.history.navigate('firmkalendar'); 
				this.kaseCustomerInhouseEvents();
				break;
			case "4":
				//Backbone.history.navigate('intakekalendar'); 
				this.kaseCustomerIntakes();
				break;
			case "5":
				Backbone.history.navigate('userkalendar/' + login_user_id);
				//this.kasePersonalEvents();
				this.showUserCalendar(login_user_id);
				break;
			default:
				this.displayCustomCalendar(calendar_id);
		}
	},
	partnerEvents:function() {
		executeMainChanges();
		readCookie();
		var self = this;
		//in event_module.js
		showPartnerKalendar('agendaDay');
	},
	employeeEvents:function() {
		executeMainChanges();
		readCookie();
		var self = this;
		//in event_module.js
		showEmployeeKalendar('month');
	},
	kaseCustomerEventsByDay:function() {
		executeMainChanges();
		readCookie();
		var self = this;
		//in event_module.js
		showCustomerKalendar('agendaDay');
	},
	kaseCustomerEventsByWeek:function() {
		executeMainChanges();
		readCookie();
		var self = this;
		//in event_module.js
		showCustomerKalendar('agendaWeek');
	},
	displayCustomCalendar: function(calendar_id) {
		executeMainChanges();
		readCookie();
		var self = this;
		
		var all_customer_custom = new OccurenceCalendarCollection({calendar_id: calendar_id});
		all_customer_custom.fetch({
			success: function (data) {
				//then re-assign to calendar
				$("#content").html("");
				var calendar_info = new Backbone.Model;
				//get calendar name
				var thecalendar = customer_calendars.findWhere({calendar_id: calendar_id});
				if (typeof thecalendar != "undefined") {
					calendar_info.set("title", thecalendar.get("calendar") + " Kalendar");
				} else {
					//THIS SHOULD NOT HAPPEN, ERROR?
					calendar_info.set("title", "Custom Calendar");
				}
				calendar_info.set("calendar_id", calendar_id);
				occurencesView = new custom_occurences_view({el: $("#content"), collection: all_customer_custom, model: calendar_info}).render();		
			}
		});
	},
	listInbox: function () { 
		this.clearSearchResults();
		executeMainChanges();
		current_case_id = -1;
		readCookie();
		$("#content").html(loading_image);
		
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
		this.clearSearchResults();
		executeMainChanges();
		
		current_case_id = -1;
		readCookie();
		$("#content").html(loading_image);
		
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
	listAllTaskCompleted: function (day) { 
		//var day = moment().format("YYYY-MM-DD")
		this.listTaskCompleted(day);
	},
	listTaskCompleted: function (day) { 
		if (typeof day == "undefined") {
			day = "";
		}
		this.clearSearchResults();
		executeMainChanges();
		
		current_case_id = -1;
		readCookie();
		
		$('#content').html(loading_image);
		
		if (day!="") {
			//fetch all tasks
			var theoptions = {day: day, single_day: "y", all_users: "y"};
		} else {
			//fetch all tasks
			var theoptions = "";
		}
		tasks = new CompletedTasks(theoptions);
		tasks.fetch({
			success: function (data) {
				$(document).attr('title', "Completed Tasks :: iKase");
				
				var task_listing_info = new Backbone.Model;
				var the_title = "Completed Tasks";
				if (day!="") {
					var display_day = moment(day).format("MM/DD/YYYY");
					the_title += " <input type='text' id='taskdayview_start_date' style='width:100px' value='" + display_day + "' onkeyup='mask(this, mdate);' onblur='mask(this, mdate);' />&nbsp;|&nbsp;" + assignee_filter;
				}
				task_listing_info.set("title", the_title);
				task_listing_info.set("receive_label", "Due Date");
				$('#content').html(new task_listing({collection: data, model: task_listing_info}).render().el);
				$("#content").removeClass("glass_header_no_padding");
			}
		});			
	},
	listExams: function (case_id) { 
		executeMainChanges();
		//current_case_id = -1;
		readCookie();
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.listExams(case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
		
		if (case_id!=current_case_id) {
			//show header for new case
			kase.set("header_only", true);
			$('#content').html(new kase_view({model: kase}).render().el);
		}
		$(document).attr('title', "vServices for Case ID: " + case_id + " :: iKase");
		current_case_id = case_id;
		//fetch all exams
		var exams = new ExamCollection({case_id: case_id});
		exams.fetch({
			success: function (data) {
				var exam_info = new Backbone.Model;
				exam_info.set("case_id", case_id);
				exam_info.set("holder", "kase_content");
				$('#kase_content').html(new exam_listing({collection: data, model:exam_info}).render().el);
				$("#kase_content").removeClass("glass_header_no_padding");
			}
		});			
	},
	listPhoneInbox: function () { 
		executeMainChanges();
		current_case_id = -1;
		readCookie();
		
		var messages = new NewPhoneCalls();
		messages.fetch({
			success: function (data) {
				if (data.length > 0) {
					var message_listing_info = new Backbone.Model;
					message_listing_info.set("title", "Phone Messages");
					message_listing_info.set("first_column_label", "From");
					message_listing_info.set("receive_label", "On");
					message_listing_info.set("homepage", false);
					message_listing_info.set("event_class", "messages");
					$('#content').html(new event_listing({collection: data, model: message_listing_info}).render().el);
				} else {
					$('#content').html("<span class='large_white_text'>No Phone Messages</span>");
				}
			}
		});
	},
	listTaskInbox: function () { 
		this.clearSearchResults();
		executeMainChanges();
		
		current_case_id = -1;
		readCookie();
		$("#content").html(loading_image);
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
	listAllTaskInboxToday: function (day) { 
		this.clearSearchResults();
		executeMainChanges();
		
		current_case_id = -1;
		readCookie();
		
		if (typeof day == "undefined") {
			day = moment().format("YYYY-MM-DD");
			display_day = moment().format("MM/DD/YYYY");
		} else {
			display_day = moment(day).format("MM/DD/YYYY");
		}
		$("#content").html(loading_image);
		//fetch all tasks
		tasks = new TaskInboxCollection({day: day, single_day: "y", all_users: "y"});
		tasks.fetch({
			success: function (data) {
				$(document).attr('title', "Task Inbox - All Employees " + display_day + " :: iKase");
				var task_listing_info = new Backbone.Model;
				task_listing_info.set("title", "All Employees - Task Inbox <input type='text' id='taskdayview_start_date' style='width:100px' value='" + display_day + "' onkeyup='mask(this, mdate);' onblur='mask(this, mdate);' />&nbsp;|&nbsp;" + assignee_filter);
				
				task_listing_info.set("receive_label", "Due Date");
				$('#content').html(new task_listing({collection: data, model: task_listing_info}).render().el);
				$("#content").removeClass("glass_header_no_padding");
			}
		});			
	},
	listTaskInboxToday: function (day) { 
		this.clearSearchResults();
		executeMainChanges();
		
		current_case_id = -1;
		readCookie();
		
		if (typeof day == "undefined") {
			day = moment().format("YYYY-MM-DD");
			display_day = moment().format("MM/DD/YYYY");
		} else {
			display_day = moment(day).format("MM/DD/YYYY");
		}
		$("#content").html(loading_image);
		//fetch all tasks
		tasks = new TaskInboxCollection({day: day, single_day: "y"});
		tasks.fetch({
			success: function (data) {
				$(document).attr('title', "Task Inbox " + display_day + " :: iKase");
				var task_listing_info = new Backbone.Model;
				task_listing_info.set("title", "Task Inbox <input type='text' id='taskdayview_start_date' style='width:100px' value='" + display_day + "' onkeyup='mask(this, mdate);' onblur='mask(this, mdate);' />");
				
				task_listing_info.set("receive_label", "Due Date");
				$('#content').html(new task_listing({collection: data, model: task_listing_info}).render().el);
				$("#content").removeClass("glass_header_no_padding");
			}
		});			
	},
	kaseCustomerTasks: function (start, end) {		
		readCookie();
		var self = this;
		var all_customer_tasks = new TaskCustomerCollection({start: start, end: end});
		all_customer_tasks.fetch({
			success: function (data) {
				//then re-assign to calendar
				$("#content").html("");
				var task_info = new Backbone.Model;
				task_info.set("start", moment(start).format("MM/DD/YYYY"));
				task_info.set("end", moment(end).format("MM/DD/YYYY"));
				task_info.set("title", "Tasks");
				$('#content').html(new task_listing({collection: all_customer_tasks, model:task_info}).render().el);
			}
		});
		
	},
	listCaseTasks: function (case_id) { 
		executeMainChanges();
		current_case_id = case_id;
		readCookie();
		
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.listCaseTasks(case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
		
		$(document).attr('title', "Tasks for Case ID: " + case_id + " :: iKase");
		//fetch all tasks
		tasks = new TaskInboxCollection({case_id: case_id});
		tasks.fetch({
			success: function (data) {
				$(document).attr('title', "Kase Tasks :: iKase");
				var task_listing_info = new Backbone.Model;
				task_listing_info.set("title", "Kase Tasks");
				task_listing_info.set("receive_label", "Due Date");
				$('#kase_content').html(new task_listing({collection: data, model: task_listing_info}).render().el);
				$("#kase_content").removeClass("glass_header_no_padding");
			}
		});			
	},
	displayEmailSettings: function () { 
		current_case_id = -1;
		readCookie();
		//this.model.user_id = this.model.get("user_id");
		//console.log(login_user_id);
		user = new User({user_id: login_user_id});
		user.fetch({
			success: function (data) {
				$('#content').html(new dashboard_email_view({model: user}).render().el);		
			}
		});
	},
	listCustomerSetting: function () { 
		this.clearSearchResults();
		executeMainChanges();
		
		current_case_id = -1;
		readCookie();
		
		//fetch all settings
		refreshCustomerSettings();			
	},
	listUserSetting: function () { 
		this.clearSearchResults();
		executeMainChanges();
		
		current_case_id = -1;
		readCookie();
		
		refreshUserSettings();			
	},
	listTaskOutbox: function () {
		this.clearSearchResults();
		executeMainChanges();
		
		current_case_id = -1;
		readCookie();
		$("#content").html(loading_image);
		
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
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.kaseEventDialog(case_id, event_id, day_date);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
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
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.kaseEventTypeDialog(case_id, event_id, event_kind);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
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
		this.clearSearchResults();
		
		current_case_id = case_id;
		current_calendar_id = -1;
		executeMainChanges();
		readCookie();
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.kaseEvents(case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
		
		$(document).attr('title', "Kalendar for Case ID: " + case_id + " :: iKase");
		//clean up button
		$("button.calendar").fadeOut(function() {
				$("button.information").fadeIn();
			});
		kase.set("header_only", true);
		$('#content').html(new kase_view({model: kase}).render().el);
		//function below is in event_module.js
		renderCalendar(kase);
	},
	kaseEventsByDate:function(case_id, start, end) {
		current_case_id = case_id;
		current_calendar_id = -1;
		executeMainChanges();
		readCookie();
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.kaseEventsByDate(case_id, start, end);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
		//clean up button
		$("button.calendar").fadeOut(function() {
				$("button.information").fadeIn();
			});
		kase.set("header_only", true);
		$('#content').html(new kase_view({model: kase}).render().el);
		
		renderCalendarListByDate(kase, start, end);
	},
	kaseCustomerEvents: function () {		
		executeMainChanges();
		readCookie();
		var self = this;
		
		showCustomerKalendar('month');
	},
	kaseCustomerInhouseEvents: function () {		
		executeMainChanges();
		readCookie();
		var self = this;
		
		
		showCustomerInhouseKalendar('month');
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
				occurencesView = new intake_cus_occurences_view({el: $("#content"), collection: all_customer_intakes}).render();			
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
	showUserCalendar: function (user_id) {
		//get the employee calendar id, make it the current calendar
		executeMainChanges();
		readCookie();
		var self = this;	
		var blnProceed = true;		
		if (typeof current_employee_calendars == "undefined") {
			blnProceed = false;
			current_employee_calendars = new PersonalCalendarCollection([]);
			current_employee_calendars.fetch({
				success: function(data) {
					self.showUserCalendar(user_id);
				}
			});
		}
		if (!blnProceed) {
			return;
		}
		var permissions = "readwrite";
		if (login_user_id!=user_id) {
		//get calendar permissions
			permissions = current_employee_calendars.findWhere({id: user_id}).get("permissions");
		}
		//get the employee calendar
		var thecalendar = customer_calendars.findWhere({sort_order: "5"});
		var calendar_id = -1;
		if (typeof thecalendar != "undefined") {
			calendar_id = thecalendar.get("calendar_id");
		}
		current_calendar_id = calendar_id;
		//get the user name
		var user = new User({user_id: user_id})
		user.fetch({
			success: function (user) {
				var all_user_events = new UserCalendar({user_id: user_id});
				all_user_events.fetch({
					success: function (data) {
						//then re-assign to calendar
						var empty_model = new Backbone.Model;				
						empty_model.set("calendar_name", user.get("user_name"));
						empty_model.set("user_id", user_id);
						empty_model.set("permissions", permissions);
						$("#content").html("");
						//do i have write permissions?
						occurencesView = new user_occurences_view({el: $("#content"), collection: all_user_events, model: empty_model}).render();
					}
				});
			}
		});
	},
	showSubscriptionLink: function() {
		var subscription_link = "<div class='white_text'>For Outlook/Google Calendar Sync, use this url:<br><br>https://v4.ikase.org/api/ikase_sync2.php?" + subscription_string + "</div>";
		$("#content").html(subscription_link);
	},
	kaseKAI: function (case_id) {
		executeMainChanges();
		current_case_id = case_id;
		
		readCookie();
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.kaseKAI(case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
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
	kaseChecks: function (case_id) {		
		executeMainChanges();
		readCookie();
		var self = this;
		
		//set current case
		current_case_id = case_id;	
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.kaseChecks(case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
		$(document).attr('title', "Payments for Case ID: " + case_id + " :: iKase");
		
		refreshChecks(kase);
	},
	kaseApplicant: function (case_id) {		
		this.clearSearchResults();
		
		current_case_id = case_id;
		executeMainChanges();
		readCookie();
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.kaseApplicant(case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
		if (kase.get("applicant_id") == null) {
			kase.set("applicant_id", -1);
		}
		
		var applicant = new Person({id: kase.get("applicant_id")});
		applicant.fetch({
			success: function (data) {
				var prefix = "";
				//if (data.id>-1) {
					kase.set("header_only", true);
					$('#content').html(new kase_view({model: kase}).render().el);
					prefix = "kase_";
				//}
				data.set("case_id", kase.get("case_id"));
				data.set("case_uuid", "");
				data.set("gridster_me", true);
				data.set("kase_type", kase.get("case_type"));
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
	kaseAccident: function (case_id) {		
		var case_id = current_case_id;
		this.clearSearchResults();
		executeMainChanges();
		readCookie();
		var self = this;
		var kase = kases.findWhere({case_id: case_id});
		//get the kase
		
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.kaseApplicant(case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}

		kase.set("header_only", true);
		$('#content').html(new kase_view({model: kase}).render().el);
		prefix = "kase_";
		
		var accident = new Accident({"case_id": case_id});
		accident.set("holder", "#" + prefix + "content");
		accident.fetch({
			success: function(accident_model) {
				the_new_accident = new accident_new_view({el: $("#" + prefix + "content"), model:accident}).render();
			}
		});
	},
	kaseAccidentDash: function (case_id, accident_type) {		
		var case_id = current_case_id;
		executeMainChanges();
		readCookie();
		var self = this;
		var kase = kases.findWhere({case_id: case_id});
		//get the kase
		
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.kaseApplicant(case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
		if ($.isNumeric(accident_type)) {
			var injury = dois.findWhere({injury_id: accident_type});
			//get the kase
			
			if (typeof injury == "undefined") {
				//get it
				var injury =  new Injury({id: case_id});
				injury.fetch({
					success: function (injury) {
						if (injury.toJSON().uuid!="") {
							//dois.add(injury);
							//self.kaseApplicant(case_id);
						} else {
							//case does not exist, get out
							document.location.href = "#";
						}
						return;		
					}
				});
				return;
			}
		}
		kase.set("header_only", true);
		var accident = new Accident({"case_id": case_id});
		var personal_injury = new PersonalInjury({"case_id": case_id});
		$('#content').html(new kase_view({model: kase}).render().el);
		prefix = "kase_";

		accident.set("accident_type", accident_type);
		accident.set("holder", "#" + prefix + "content");
		if (accident_type=="caraccident") { 
			accident_type = "accident";
		}
		var case_type = kase.get("case_type");
		if (case_type != "NewPI") {
			//dynamic view name, good job angel
			var dashboard_accidenttype_view = window["dashboard_" + accident_type + "_view"];
			
			accident.fetch({
				success: function(accident_model) {
						the_new_accident = new dashboard_accidenttype_view({el: $("#" + prefix + "content"), model:accident}).render();
				}
			});
		} else {
			
			/*personal_injury.fetch({
				success: function(personal_injury_model) {
						
				}
			});
			*/
			//personal_injury = "";
			prefix = "kase_";
			
			personal_injury.set("holder", "#" + prefix + "content");
			personal_injury.set("glass", "card_dark_7");
			//the_new_accident = new personal_injury_view({el: $("#" + prefix + "content"), model:personal_injury}).render();
			personal_injury.fetch({
				success: function(personal_injury) {
					$("#" + prefix + "content").html(new personal_injury_view({model: personal_injury}).render().el);
					}
			});
		}

	},
	kasePersonalInjury: function (case_id) {		
		var case_id = current_case_id;
		executeMainChanges();
		readCookie();
		var self = this;
		var kase = kases.findWhere({case_id: case_id});
		//get the kase
		
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.kaseApplicant(case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
		kase.set("header_only", true);
		var personal_injury = new PersonalInjury({"case_id": case_id});
		$('#content').html(new kase_view({model: kase}).render().el);
		prefix = "kase_";

		personal_injury.set("holder", "#" + prefix + "content");
		var case_type = kase.get("case_type");
		
		prefix = "kase_";
		
		personal_injury.set("holder", "#" + prefix + "content");
		personal_injury.set("glass", "card_dark_7");
		
		personal_injury.fetch({
			success: function(personal_injury) {
				$("#" + prefix + "content").html(new personal_injury_view({model: personal_injury}).render().el);
			}
		});

	},
	userActivity: function (user_id, start_date, end_date) {		
		//no current case right now
		current_case_id = "";
		this.clearSearchResults();
		executeMainChanges();
		readCookie();
		var self = this;
		
		var activities = new ActivityReportCollection([], {user_id: user_id, start_date: start_date, end_date: end_date});
		activities.fetch({
			success: function(data) {
				$(document).attr('title', "Activities Report :: iKase");
				var mymodel = new Backbone.Model();
				mymodel.set("report", true);
				mymodel.set("user_id", user_id);
				mymodel.set("start_date", start_date);
				mymodel.set("end_date", end_date);
				mymodel.set("holder", "content");
				$('#content').html(new activity_listing_view({collection: activities, model: mymodel}).render().el);
				$("#content").removeClass("glass_header_no_padding");
				hideEditRow();
			}
		});
	},
	kaseActivity: function (case_id) {		
		current_case_id = case_id;
		this.clearSearchResults();
		executeMainChanges();
		readCookie();
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.kaseActivity(case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
		kase.set("header_only", true);
		$('#content').html(new kase_view({model: kase}).render().el);
		
		var activities = new ActivitiesCollection([], {case_id: case_id});
		activities.fetch({
			success: function(data) {
				$(document).attr('title', "Activities for Case ID: " + case_id + " :: iKase");
				kase.set("holder", "#kase_content");
				$('#kase_content').html(new activity_listing_view({collection: activities, model: kase}).render().el);
				$("#kase_content").removeClass("glass_header_no_padding");
				hideEditRow();
			}
		});
	},
	kaseInjury: function (case_id) {		
		current_case_id = case_id;
		readCookie();
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.kaseInjury(case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
		kase.set("header_only", true);
		kase.set("new_injury", false);
		$('#content').html(new kase_view({model: kase}).render().el);
		$('#kase_content').html(new dashboard_injury_view({model: kase}).render().el);
		
		self.recentKases();
	},
	showKontrolPanel: function (case_id) {		
		this.clearSearchResults();
		
		current_case_id = case_id;
		readCookie();
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.showKontrolPanel(case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
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
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.kaseSpecificInjury(case_id, injury_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
		$(document).attr('title', "Injury for Case ID: " + case_id + " :: iKase");
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
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.newInjury(case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
		}
		kase.set("header_only", true);
		kase.set("new_injury", true);
		kase.set("injury_id", kase.id);
		$('#content').html(new kase_view({model: kase}).render().el);
		$('#kase_content').html(new dashboard_injury_view({model: kase}).render().el);
		$("#content").removeClass("glass_header_no_padding");
	},
	listParties: function (case_id) {	
		//clear out any search results
		this.clearSearchResults();
		
		current_case_id = case_id;
		executeMainChanges();
		readCookie();
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.listParties(case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
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
	listUnassigneds: function () {	
		current_case_id = -1;	
		
		this.clearSearchResults();
		executeMainChanges();
		
		readCookie();
		var self = this;
		//initial call
		var type = "";
		var my_stacks = new MyStacksByType([], {stack_type: 'unassigned'});
		my_stacks.fetch({
			success: function(data) {
				$(document).attr('title', "Document Notifications :: iKase");
				var mymodel = new Backbone.Model();
				mymodel.set("type", "unassigned");
				$('#content').html(new stack_listing_view({collection: data, model: mymodel}).render().el);
				$("#content").removeClass("glass_header_no_padding");
				hideEditRow();
			}
		});	
	},
	listImports: function () {	
		current_case_id = -1;	
		
		this.clearSearchResults();
		executeMainChanges();
		
		readCookie();
		var self = this;
		//initial call
		var type = "";
		//stacks = new Stacks([]);
		var my_stacks = new StacksByType([], {stack_type: 'batchscan'});
		my_stacks.fetch({
			success: function(data) {
				$(document).attr('title', "Imports :: iKase");
				var mymodel = new Backbone.Model();
				mymodel.set("type", "batchscan");
				$('#content').html(new stack_listing_view({collection: data, model: mymodel}).render().el);
				$("#content").removeClass("glass_header_no_padding");
				hideEditRow();
			}
		});	
	},
	listLegacyArchives: function(case_id) {
		executeMainChanges();
		readCookie();
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.listLegacyArchives(case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
		var kase_archives = new LegacyArchiveCollection([], { case_id: kase.get("case_id") });
		
		if (case_id!=current_case_id) {
			//show header for new case
			kase.set("header_only", true);
			$('#content').html(new kase_view({model: kase}).render().el);
		}
		
		//get the activity
		var activities = new ActivitiesCollection([], {case_id: case_id});
		activities.fetch({
			success: function(data) {
				var acts = data.toJSON();
				
				//get the archives
				kase_archives.fetch({
					success: function(data) {
						//cycle through the archives, assign event from activity
						var archives = data.toJSON();
						var arrCategories = [];
						_.each( archives, function(archive) {
							archive.description = "";
							archive.category = "";
							_.each( acts, function(activity) {
								var arrID = activity.activity_uuid.split("_");
								var caseno = arrID[0];
								var actno = arrID[1];
								
								if (archive.actno == actno) {
									archive.description = activity.activity;
									archive.category = activity.activity_category;
								}
								
								if (arrCategories.indexOf(activity.activity_category) < 0) {
									arrCategories.push(activity.activity_category);
								}
							});
						});
						//archiveFilter
						kase_archives.reset(archives);
						kase.set("holder", "kase_content"); 
						kase.set("archive_categories", arrCategories);
						$('#kase_content').html(new archive_legacy_listing_view({collection: kase_archives, model: kase}).render().el);
						$("#kase_content").removeClass("glass_header_no_padding");
						hideEditRow();
					}
				});
			}
		});
		
		//set current case
		current_case_id = case_id;	
	},
	listArchives: function (case_id) {	
		executeMainChanges();
		readCookie();
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.listArchives(case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
		var kase_archives = new ArchiveCollection([], { case_id: case_id });
		
		if (case_id!=current_case_id) {
			//show header for new case
			kase.set("header_only", true);
			$('#content').html(new kase_view({model: kase}).render().el);
		}
		kase_archives.fetch({
			success: function(data) {
				kase.set("holder", "kase_content"); 
				$('#kase_content').html(new archive_listing_view({collection: data, model: kase}).render().el);
				$("#kase_content").removeClass("glass_header_no_padding");
				hideEditRow();
			}
		});
		//set current case
		current_case_id = case_id;	
	},
	listDocuments: function (case_id) {	
		this.clearSearchResults();
		executeMainChanges();
		readCookie();
		var self = this;
		
		$(document).attr('title', "Documents for Case ID: " + case_id + " :: iKase");
		refreshDocuments(case_id);	
	},
	searchDocuments: function () {	
		//executeMainChanges();
		readCookie();
		var self = this;
		var mymodel = new Backbone.Model({"holder": "document_search"});
		$('#document_search').html(new document_search({model: mymodel}).render().el);
	},
	listTemplates: function (case_id) {	
		if (typeof case_id != "undefined") {
			current_case_id = case_id;	
		}
		this.clearSearchResults();
		executeMainChanges();
		readCookie();
		var self = this;
		
		word_templates = new WordTemplates([]);
		word_templates.fetch({
			success: function(data) {
				var empty_model = new Backbone.Model;
				empty_model.set("case_id", "-1");
				empty_model.set("uuid", "templates");
				empty_model.set("no_uploads", false);
				empty_model.set("holder", "content");
				$('#content').html(new template_listing_view({collection: data, model: empty_model}).render().el);
				$("#content").removeClass("glass_header_no_padding");
				hideEditRow();
			}
		});	
	},
	listNotes: function (case_id) {	
		this.clearSearchResults();
		
		executeMainChanges();
		current_case_id = case_id;	
		
		readCookie();
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.listNotes(case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
		$(document).attr('title', "Notes for Case ID: " + case_id + " :: iKase");
		var type = "";
		
		notes = new NoteCollection([], { case_id: case_id });
		kase.set("header_only", true);
		$('#content').html(new kase_view({model: kase}).render().el);
		$("#kase_content").html(loading_image);
		notes.fetch({
			success: function(data) {
				var note_list_model = new Backbone.Model;
				note_list_model.set("display", "full");
				note_list_model.set("partie_type", "note");
				note_list_model.set("partie_id", -1);
				note_list_model.set("case_id", case_id);
				$('#kase_content').html(new note_listing_view({collection: data, model: note_list_model}).render().el);
				$("#kase_content").removeClass("glass_header_no_padding");
				hideEditRow();
			}
		});	
			
	},
	showSettlement: function (case_id, injury_id) {
		//current_case_id = case_id;
		readCookie();
		var self = this;
		
		this.clearSearchResults();
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.showSettlement(case_id, injury_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
		kase.set("header_only", true);
		kase.set("injury_id", injury_id);
		
		if (case_id!=current_case_id) {
			kase.set("header_only", true);
			$('#content').html(new kase_view({model: kase}).render().el);
		}
		$('#kase_content').html(new dashboard_settlement_view({model: kase}).render().el);
		
		//set current case
		current_case_id = case_id;	
	},
	showLien: function (case_id, injury_id) {
		executeMainChanges();
		
		readCookie();
		$(document).attr('title', "Lien");
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.showLien(case_id, injury_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
		if (case_id!=current_case_id) {
			//show header for new case
			kase.set("header_only", true);
			$('#content').html(new kase_view({model: kase}).render().el);
		}

		var lien = new Lien({injury_id: injury_id});
		lien.fetch({
			success: function(data) {
				//data.set("lien_id", data.lien_id)
				if (data.length == 0) {
					data = new Lien({injury_id: injury_id});
				}
				data.set("holder", "kase_content");
				$('#kase_content').html(new lien_view({model: data}).render().el);
			}
		});
		//set current case
		current_case_id = case_id;	
	},
	listLetters: function (case_id) {
		executeMainChanges();
		current_case_id = case_id;
		
		readCookie();
		
		$(document).attr('title', "Letters for Case ID: " + case_id + " :: iKase");
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.listLetters(case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
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
				kase.set("holder", "kase_content");
				$('#kase_content').html(new letter_listing_view({collection: data, model: kase}).render().el);
				$("#kase_content").removeClass("glass_header_no_padding");
				
				hideEditRow();
			}
		});
	},
	listKaseLetters: function (case_id) {
		this.clearSearchResults();
		
		executeMainChanges();
		current_case_id = case_id;
		
		readCookie();
		$(document).attr('title', "Letters for Case ID: " + case_id + " :: iKase");
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.listKaseLetters(case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
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
				//if ($('#kase_content').length == 0) {
					kase.set("header_only", true);	
					$('#content').html(new kase_view({model: kase}).render().el);
				//}
				kase.set("no_uploads", true);
				$('#kase_content').html(new kase_letter_listing_view({collection: word_templates, model: kase}).render().el);
				$("#kase_content").removeClass("glass_header_no_padding");
				
				hideEditRow();
			}
		});
	},
	listEamsForms: function() {
		this.clearSearchResults();
		
		executeMainChanges();
		
		eamss = new EAMSFormCollection();
		eamss.fetch({
			success: function(data) {
				$(document).attr('title', "Manage EAMS Forms :: iKase");
				$('#content').html(new eams_form_listing({collection: data}).render().el);
				$("#content").removeClass("glass_header_no_padding");
				hideEditRow();
			}
		});	
	},
	listEams: function (case_id) {
		this.clearSearchResults();
		
		executeMainChanges();
		current_case_id = case_id;
		
		readCookie();
		$(document).attr('title', "EAMS Forms for Case ID: " + case_id + " :: iKase");
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.listEams(case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
		var type = "";
		eamss = new KaseEams([], { case_id: case_id });
		
		if ($('#kase_content').length == 0) {
			kase.set("header_only", true);	
			$('#content').html(new kase_view({model: kase}).render().el);
		}
		kase.set("no_uploads", true);
		eamses = new FormCollection();
		eamses.fetch({
			success: function(eamses) {
				kase.set("holder", "kase_content");
				$('#kase_content').html(new form_listing({collection: eamses, model: kase}).render().el);
				$("#kase_content").removeClass("glass_header_no_padding");
			}
		});
		
		hideEditRow();
	
	},
	editNote: function (case_id, notes_id) {	
		current_case_id = case_id;
			
		readCookie();
		var self = this;
		
		//get the kase if url direct referrer
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.editNote(case_id, notes_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
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
		users = new UserAllCollection([]);
		users.fetch({
			success: function(data) {
				$(document).attr('title', "Users :: iKase");
				$('#content').html(new user_listing_view({model: data}).render().el);
				$("#content").removeClass("glass_header_no_padding");
				hideEditRow();
			}
		});				
	},
	listServices: function(case_id) {
		executeMainChanges();
		
		
		readCookie();
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.listServices(case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}

			});
			return;
		}
		var type = "";
		if (current_case_id != case_id) {
			kase.set("header_only", true);
			$('#content').html(new kase_view({model: kase}).render().el);
		}
		$(document).attr('title', "Services for Case ID: " + case_id + " :: iKase");
		var vservices = new vServiceCollection();
		vservices.fetch({
			success: function(vservices) {
				kase.set("holder", "kase_content");
				$('#kase_content').html(new vservices_view({collection: vservices, model: kase}).render().el);
				$("#kase_content").removeClass("glass_header_no_padding");	
			}
		});
		
		current_case_id = case_id;	
	},
	listWebmails: function () {
		current_case_id = -1;
		executeMainChanges();		
		readCookie();
		var self = this;
		
		refreshWebmail();
	},
	listContacts: function () {
		current_case_id = -1;
		executeMainChanges();		
		readCookie();
		var self = this;
		
		//initial call
		var type = "";
		contacts = new ContactCollection([]);
		//contacts.fetch({
		//	success: function(contacts) {
				$(document).attr('title', "Contacts :: iKase");
				var mymodel = new Backbone.Model({"holder": "content"});
				$('#content').html(new rolodex_listing_view({collection: contacts, model: mymodel}).render().el);
				$("#content").removeClass("glass_header_no_padding");
				hideEditRow();
		//	}
		//});				
	},
	editUser: function (user_id) {		
		current_case_id = -1;
		readCookie();
		var self = this;
		
		if (user_id=="") {
			user_id = -1;
		}
		$(document).attr('title', "User :: iKase");
		var mymodel = new Backbone.Model({user_id: user_id, "holder": "content"});
		$('#content').html(new dashboard_user_view({model: mymodel}).render().el);
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
				email.set("holder", "user_panel");
				$('#user_panel').html(new email_view({model: email}).render().el);				
				$("#user_panel").addClass("glass_header_no_padding");
			}
		});	
	},
	searchQME: function(zip) {
		if (typeof zip == "undefined" || zip=="-1") {
			zip = "";
			current_case_id = "";
		}
		if (zip=="-2") {
			zip = "new";
		}
		var qme = new Backbone.Model();
		qme.set("holder", "kase_content");
		qme.set("zip", zip);
		var container = "kase_content";
		if (zip=="") {
			var container = "content";
			qme.set("holder", "content");
		}
		$('#' + container).html(new search_qme({model: qme}).render().el);	
		$('#' + container).removeClass("glass_header_no_padding");
	},
	searchEAMS: function() {
		var eams = new Backbone.Model();
		eams.set("holder", "kase_content");

		var container = "content";
		eams.set("holder", "content");
	
		$('#' + container).html(new search_eams({model: eams}).render().el);	
		$('#' + container).removeClass("glass_header_no_padding");
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
				//get it
				var kase =  new Kase({id: case_id});
				kase.fetch({
					success: function (kase) {
						if (kase.toJSON().uuid!="") {
							kases.add(kase);
							self.priorTreatment(case_id, corporation_id, person_id);
						} else {
							//case does not exist, get out
							document.location.href = "#";
						}
						return;		
					}
				});
				return;
			}
			case_id = kase.get("case_id");
			uuid = kase.get("case_uuid");
			person_id = kase.get("applicant_id");
		} else {
			//have to have a case id
			return false;
		}
		$(document).attr('title', "Prior Medical Provider :: iKase");
			
		//let's get the specific corporation by id.  if id < 0, we will get an empty corporation
		refreshPriorMedical(case_id, kase, person_id, corporation_id);
		self.recentKases();
	},
	editRoloNewPartie: function (corporation_type) {
		$("#gifsave").hide();
		readCookie();
		var self = this;
		
		//let's extract the claim if any
		var referred_out_claim = "";
		if (corporation_type.indexOf(":") > -1) {
			var arrTypeInfo = corporation_type.split(":");
			corporation_type = arrTypeInfo[0];
			referred_out_claim = arrTypeInfo[1];
		}
		
		//let's get the specific corporation by id.  if id < 0, we will get an empty corporation
		var corporation = new Corporation({id: -1, type:corporation_type});
		corporation.fetch({
			success: function (corp) {
				var blnShowHeader = true;
				//if this is a new partie, the query will return partie_type values
				corp.set("partie", corporation_type.capitalize());
				
				corp.set("gridster_me", true);
				corp.set("show_buttons", true);
				var data = new AdhocCollection([], {case_id: -1, corporation_id: -1});
				corp.adhocs.fetch({
					success:function (data) {
						if (data.toJSON().length==0) {
							data = new AdhocCollection([], {case_id: case_id, corporation_id: corporation_id});
						}
					}
				});
				data.set("holder", "content");
				$('#content').html(new partie_view({model: corp, collection: data}).render().el);				
				$('#content').addClass("glass_header_no_padding");
				showEditRow();
			}
		});
		
		/*
		corp.adhocs.fetch({
		success:function (data) {
		if (data.toJSON().length==0) {
			data = new AdhocCollection([], {case_id: case_id, corporation_id: corporation_id});
		}
		*/
		self.recentKases();
	},
	editPartie: function (case_id, corporation_id, corporation_type) {
		this.clearSearchResults();
		executeMainChanges();
		readCookie();
		var self = this;
		
		$("#gifsave").hide();
		if (case_id == -1 && corporation_id == -1 && corporation_type == 'new') {
			this.newPartie();
			return;
		}
		if (corporation_id == -2 && corporation_type == 'medical_provider') {
			this.priorTreatment(case_id, corporation_id, -1);
			return;
		}
		
		//let's extract the claim if any
		var referred_out_claim = "";
		if (corporation_type.indexOf(":") > -1) {
			var arrTypeInfo = corporation_type.split(":");
			corporation_type = arrTypeInfo[0];
			referred_out_claim = arrTypeInfo[1];
		}
		
		if (case_id > 0) {
			//not a roldex item
			if (corporation_id < 0 && corporation_type=="new") {
				self.kaseNewPartie(case_id);
				return;
			}
			//get the kase
			var kase = kases.findWhere({case_id: case_id});
			if (typeof kase == "undefined") {
				//get it
				var kase =  new Kase({id: case_id});
				kase.fetch({
					success: function (kase) {
						if (kase.toJSON().uuid!="") {
							kases.add(kase);
							self.editPartie(case_id, corporation_id, corporation_type);
						} else {
							//case does not exist, get out
							document.location.href = "#";
						}
						return;		
					}
				});
				return;
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
					blnShowHeader = (corporation_id > 0 || case_id > 0);
				}
				if (case_id > 0) {
					//only ids for active cases would get a header
					if (blnShowHeader) {
						if ($('#kase_content').length == 0 || current_case_id!=case_id) {
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
				
				corp.set("claims", kase.get("claims"));
				corp.set("referred_out_claim", referred_out_claim);
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
						corp.set("holder", "content");
						
						$(destination_content).html(new partie_view({model: corp, collection: data}).render().el);				
						$(destination_content).addClass("glass_header_no_padding");
						showEditRow();
					}
				});
			}
		});
		
		current_case_id = case_id;
		self.recentKases();
	},
	newRoloPartie: function () {
		$("#gifsave").hide();
			
		readCookie();
		var self = this;
		
		//let's get the specific corporation by id.  if id < 0, we will get an empty corporation
		var corporation = new Corporation({id: -1});
		corporation.fetch({
			success: function (corp) {
				var blnShowHeader = true;
				
				corp.set("gridster_me", true);
				corp.set("show_buttons", true);
				corp.set("holder", "content");
				$('#content').html(new parties_new_rolodex({model: corp}).render().el);
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
					blnShowHeader = (corporation_id > 0 || case_id > 0);
				}
				//make sure the data is there, if not there, redirect as new
				if (corp.get("company_name")=="" && corporation_id > 0) {
					alert("wrong2");
					return;
				}
				corp.set("partie", corporation_type.capitalize());
				corp.set("gridster_me", true);
				corp.set("show_buttons", true);
				corp.set("claims", "");
				corp.adhocs.fetch({
					success:function (data) {
						if (data.toJSON().length==0) {
							data = new AdhocCollection([], {corporation_id: corporation_id});
							
						}
						corp.set("holder", "content");
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
		
		//let's get the specific corporation by id.  if id < 0, we will get an empty corporation
		var person = new Person({id: person_id});
		person.fetch({
			success: function (person) {
				var blnShowHeader = true;
				
				//make sure the data is there, if not there, redirect as new
				if (person.get("last_name")=="" && person_id > 0) {
					alert("wrong2");
					return;
				}
				person.set("gridster_me", true);
				person.set("show_buttons", true);
				person.set("bln_contact", true);
				$("#content").html(new dashboard_person_view({model: person}).render().el);
			}
		});
		self.recentKases();
	},
	kaseNewPartie: function (case_id) {	
		executeMainChanges();
		
		current_case_id = case_id;
			
		readCookie();
		var self = this;
		
		//get the kase
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						self.kaseNewPartie(case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
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
		
		var mymodel = new Backbone.Model({"holder": "content"});
		newPartyKaseView = new parties_new_rolodex({el: $("#content"), model: mymodel}).render();
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
	listContactEmails: function (id) {
		executeMainChanges();
		readCookie();		
		
			//initial call
		var contacts = new PersonalContactsCollection();
		contacts.fetch({
			success: function (data) {
				contacts = new contact_listing_view({el: $("#content"), collection: contacts}).render();			
			}
		});			
    },
	editContactEmail: function (id) {
		executeMainChanges();
		readCookie();		
		
			//initial call
		var contact = new PersonalContact({contact_id: id});
		contact.fetch({
			success: function (data) {
				data.set("glass", "card_dark_1");
				data.set("id", id);
				contacting = new contact_view({el: $("#content"), model: data}).render();			
			}
		});			
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
	listRecentKases: function() {
		executeMainChanges();
		
		recent_kases.fetch({
			success: function(recent_kases) {
				var mymodel = new Backbone.Model();
				mymodel.set("recent", "Y");
				mymodel.set("key", "");
				$('#content').html(new kase_listing_view_mobile({collection: recent_kases, model: mymodel}).render().el);
				return;
			}
		});
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
var blnInitialSetting = true;	//did we go through the initial layout, in utilities in 
//load templates
//get rid of interoffice view
templateLoader.load([ "accident_view", "event_view", "partie_listing_choose", "chat_view", "dashboard_view", "dashboard_settlement_view", "calendar_view", "calendar_listing_view",  "kase_nav_bar_view", "kase_nav_left_view", "kase_list_category_view", "kase_home_view", "kase_edit_view", "kase_listing_view",  "kase_summary_view", "kase_view", "kase_header_view", "search_kase_view", "applicant_view", "person_image", "kai_view", "dialog_view", "eams_form_attach", "eams_form_listing", "eams_applicant_view", "eams_previouscases_view", "eams_hearings_view", "eams_bodyparts_view", "eams_events_view", "eams_parties_view", "event_list_view", "event_listing", "event_list_item_view", "document_upload_view", "document_listing_search", "document_listing_message", "dashboard_injury_view", "dashboard_home_view", "email_view", "injury_view", "note_listing_view", "notes_view", "partie_listing_view", "parties_new_view", "partie_view", "partie_cards_view", "user_listing_view", "dashboard_person_view", "injury_number_view", "injury_add_view", "message_view", "interoffice_view", "message_listing", "message_attach", "stack_listing_view", "document_listing_view", "task_listing", "new_kase_view", "chatting_view", "letter_attach", "setting_attach", "kase_list_task_view", "template_upload_view", "kase_control_panel", "kase_letter_listing_view", "vservice_view", "medical_specialties_select","rental_view", "multichat_messages", "property_damage_view", "car_passenger_view", "bulk_webmail_assign_view", "bulk_import_assign_view", "multichat", "dashboard_email_view", "red_flag_note_listing_view", "dashboard_related_cases_view", "bulk_date_change_view", "contact_listing_view", "contact_view", "personal_injury_view", "dashboard_home_view_mobile", "kase_nav_bar_view_mobile", "kase_listing_view_mobile", "note_listing_view_mobile", "task_listing_mobile", "event_listing_mobile", "notes_view_mobile", "task_view_mobile", "event_view_mobile", "document_view_mobile", "document_listing_view_mobile"],
    function () {
        app = new Router();
        Backbone.history.start();
	}
);

//modal display
$('.modal').on('shown.bs.modal', function() {
    $(this).find('.modal-dialog').css({
        'margin-top': "-330px",
        'margin-left': function () {
            return -($(this).outerWidth() / 2);
        }
    });
});

 // Tell jQuery to watch for any 401 or 403 errors and handle them appropriately
$.ajaxSetup({
    statusCode: {
        401: function(){
            logOut();
         
        },
        403: function() {
            // 403 -- Access denied
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
				document.location.href = "index.php";
			}
		}
	});
}
function refreshWebmail() {
	if (customer_id != 1033) {
		return;
	}
	
	$('#content').html("");
	$('#ikase_loading').html(loading_image + "<br><span class='white_text'>Contacting your mail server...  This will take a few seconds.</span>");
		
	//initial call
	var type = "";
	//webmails = new WebmailCollection();
	webmails = new WebmailLimonade();
	webmails.fetch({
		success: function(data) {
			if (typeof data.toJSON()[0].error == "string") {
				//blank, but tell them about the error
				
				alert("The email credentials entered in iKase are not currently valid");
				data = new WebmailLimonade();
			}
			$('#ikase_loading').html("");
			$(document).attr('title', "Webmail :: iKase");
			var mymodel = new Backbone.Model({"holder":"content"});
			$('#content').html(new webmail_listing_view({collection: data, model: mymodel}).render().el);
			$("#content").removeClass("glass_header_no_padding");
			hideEditRow();
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) { 
			$("#ikase_loading").html("Error: " + errorThrown); 
		} 
	});	
}
function refreshChecks(kase){
	if (typeof kase == "undefined") {
		var kase = kases.findWhere({case_id: current_case_id});
	}
	var case_id = kase.get("case_id");
	var kase_checks = new ChecksCollection([], { case_id: case_id });
	kase_checks.fetch({
		success: function(data) {
			kase.set("holder", "#kase_content");
			$('#kase_content').html(new check_listing_view({collection: data, model: kase}).render().el);
			$("#kase_content").removeClass("glass_header_no_padding");
			hideEditRow();
		}
	});
}
function refreshContacts(){
	var contacs = new ContactsCollection([], { case_id: case_id });
	contacs.fetch({
		success: function(data) {
			kase.set("holder", "#content");
			$('#content').html(new contact_listing_view({collection: data}).render().el);
			$("#content").removeClass("glass_header_no_padding");
			hideEditRow();
		}
	});
}
function refreshCalendars() {
	calendar = new CalendarCollection();
	calendar.fetch({
		success: function (data) {
			$(document).attr('title', "Calendars :: iKase");
			$('#content').html(new calendar_listing_view({collection: data}).render().el);
			$("#content").removeClass("glass_header_no_padding");
		}
	});
}
function refreshCustomerSettings() {
	customer_setting = new CustomerSettingCollection();
	customer_setting.fetch({
		success: function (data) {
			$(document).attr('title', "Customer Settings :: iKase");
			var mymodel = new Backbone.Model({"holder": "content"});
			$('#content').html(new customer_setting_listing({collection: data, model: mymodel}).render().el);
			$("#content").removeClass("glass_header_no_padding");
		}
	});
}
function refreshPriorMedical(case_id, kase, person_id, corporation_id) {
	if (typeof kase == "undefined") {
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			//get it
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.add(kase);
						refreshPriorMedical(case_id, kase, person_id, corporation_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
	}
	//document.location.href = "#parties/" + case_id + "/-2/" + type;
	//var corporation_id = -2;
	var corporation_type = "medical_provider";
	//case_id: case_id, 
	//DO NOT USE case_id IN THE OPTIONS 
	//BECAUSE you are looking for the corporation associated with APPLICANT
	var corporation = new Corporation({id: corporation_id, type:corporation_type});
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
			corp.set("claims", kase.get("claims"));
			corp.set("gridster_me", true);
			corp.set("show_buttons", true);
			//no adhocs for prior treatment per thomas
			corp.set("adhoc_fields", "assigned_to,doctor_type");
			var destination_content = "#kase_content";
			if (!blnShowHeader) {
				destination_content = "#content";
			}
			var corp_adhocs = new AdhocCollection([], {case_id: case_id, corporation_id: corporation_id});
			corp.set("holder", "content");
			$(destination_content).html(new partie_view({model: corp, collection: corp_adhocs}).render().el);
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
			data.set("holder", "content");
			$('#content').html(new user_setting_listing({collection: data}).render().el);
			$("#content").removeClass("glass_header_no_padding");
		}
	});
}
function refreshDocuments(case_id) {
	//get the kase
	var kase = kases.findWhere({case_id: case_id});
	if (typeof kase == "undefined") {
		//get it
		var kase =  new Kase({id: case_id});
		kase.fetch({
			success: function (kase) {
				if (kase.toJSON().uuid!="") {
					kases.add(kase);
					self.refreshDocuments(case_id);
				} else {
					//case does not exist, get out
					document.location.href = "#";
				}
				return;		
			}
		});
		return;
	}
	$('#kase_content').html(loading_image);
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
			empty_model.set("holder", "content");
			$('#content').html(new template_listing_view({collection: data, model: empty_model}).render().el);
			$("#content").removeClass("glass_header_no_padding");
			hideEditRow();
		}
	});
}
function composeCheck(element_id) {
	var check_id = -1;
	check_case_id = current_case_id;
	if (typeof element_id == "undefined") {
		check_id = -1;
	} else {
		var arrId = element_id.split("_");
		check_id = arrId[2]; 
	}
	var check = new Check({check_id: check_id, case_id: check_case_id});
	check.set("holder", "#myModalBody");
	$("#gifsave").hide();
	if (check_id < 1) {
		$("#myModalLabel").html("New Check");
		//check.set("check_title", "Task Assigned By " + login_username);
		check.set("transaction_date", moment().format('MM/DD/YYYY'));
		check.set("case_id", check_case_id);	
		
		$("#myModalBody").html(new check_form({model: check}).render().el);
	} else {
		var new_check = new Check({check_id: check_id, case_id: check_case_id});
		new_check.fetch({
			success: function (data) {
				data.set("check_id", data.id);
				$("#myModalBody").html(new check_form({model: data}).render().el);
				$("#myModalLabel").html("Edit Check");
				
			}
		});
	}
	
	$("#input_for_checkbox").hide();
	
	
	$("#modal_type").val("check");
	$("#modal_save_holder").html('<a title="Save Check" class="check save" onClick="saveCheckModal(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	$("#modal_save_holder").show();
	//$("#input_for_checkbox").html('&nbsp;');
	var theme = {theme: "check"};
	$(".check #case_idInput").tokenInput("api/kases/tokeninput", theme);
	
	$(".modal-header").css("background-image", "url('img/glass_edit_header_check.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_check.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_check.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	$("#myModal4 .modal-dialog").css("width", "600px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_edit_header_check.png')");
	$(".modal-content").css("background-image", "url('img/glass_edit_header_check.png')");
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
	//$(".modal-body").css("width", "100%"); 
	$("#myModal4 .modal-dialog").css("width", "600px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_edit_header_new.png')");
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
			if (kase.get("attorney_name")=="") {
				data.set("attorney_name", "<span style='font-size:0.7em'>no atty</span>");
			} else {
				data.set("attorney_name", kase.get("attorney_name"));
			}
			if (kase.get("worker_name")=="") {
				data.set("worker_name", "<span style='font-size:0.7em'>no worker</span>");
			} else {
				data.set("worker_name", kase.get("worker_name"));
			}
			data.set("holder", "myModalBody");
			$("#myModalBody").html(new letter_view({model: data}).render().el);
			$("#myModalLabel").html("Edit Letter");
		}
	});

	$("#input_for_checkbox").hide();
	
	$("#modal_save_holder").html('<input type="checkbox" id="parties_selectall" name="parties_selectall" value="Y" title="Check this box to Select all Parties" onclick="selectAllParties()" /><span class="white_text">Select all Parties</span>&nbsp;<a title="Save Letter" class="interoffice save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px; text-decoration:none">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$("#modal_type").val("letter");
	
	$(".modal-header").css("background-image", "url('img/glass_edit_header_task.png')");
	$("#myModalBody").css("background", "url('img/glass_edit_header_task.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModalBody").css("width", "100%");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_edit_header_task.png')");
	$("#myModal4 .modal-dialog").css("width", "1000px");
	//$("#myModal4").css("top", "10%");
	$(".modal-content").css("background-image", "url('img/glass_edit_header_task.png')");
}
function composeEamsForm(element_id) {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	var partieArray = element_id.split("_");
	var partie_array_type = partieArray[1];
	var eams_form_id = -1
	if (partieArray.length > 1) {
		eams_form_id = partieArray[1]
	}
	
	if (eams_form_id < 0) {
		var new_eams_form = new EAMSForm();
		new_eams_form.set("eams_form_id", eams_form_id);
		new_eams_form.set("name", "");
		new_eams_form.set("display_name", "");
		new_eams_form.set("status", "");
		new_eams_form.set("holder", "myModalBody");
		$("#myModalBody").html(new eams_form_view({model: new_eams_form}).render().el);
		$("#myModalLabel").html("New EAMS Form");
	} else {
		var new_eams_form = new EAMSForm({eams_form_id: eams_form_id});
		new_eams_form.fetch({
			success: function (data) {
				data.set("holder", "myModalBody");
				$("#myModalBody").html(new eams_form_view({model: data}).render().el);
				$("#myModalLabel").html("Edit EAMS Form - ID " + eams_form_id);
			}
		});
	}
	$("#apply_settings").hide();
	
	$("#modal_save_holder").html('<a title="Save EAMS Form" class="setting save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');

	$("#modal_type").val("eams_form");
	
	$(".modal-header").css("background-image", "url('img/glass_edit_header_task.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("width", "600px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_edit_header_task.png')");
	
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
	/*
	if (partieArray.length > 2) {
		modal_title = partieArray[2].capitalize();
	}
	*/
	modal_title = $("#" + element_id).html();
	var kase = kases.findWhere({case_id: case_id});
	$("#myModalLabel").html(modal_title);
	//always will have a template, at least for now

	var data = new Backbone.Model;
	data.set("case_id", case_id);
	data.set("case_name", kase.name());
	data.set("eams_form_name", $("#" + element_id.replace("eamsforms_", "eamsname_")).val());
	data.set("eams_display_name", modal_title);
	data.set("holder", "myModalBody");
	$("#myModalBody").html(new eams_view({model: data}).render().el);
	$("#input_for_checkbox").hide();
	
	$("#modal_save_holder").html('<a title="Save Eams" class="interoffice save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$("#modal_type").val("eams");
	
	$(".modal-header").css("background-image", "url('img/glass_edit_header_task.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_edit_header_task.png')");
	if (partieArray[2] == "lien") {
		$("#myModal4 .modal-dialog").css("width", "1300px");
		/*
		setTimeout(function() {
			$("#lien_form").css("display", "");
			$('.modal-dialog').css('top', '10%');
			$('.modal-dialog').css('margin-top', '50px');
		}, 700);
		*/
	} else {
		$("#myModal4 .modal-dialog").css("width", "600px");
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
function composeEamsImport(adj_number) {
	if (typeof adj_number == "undefined") {
		adj_number = "";
	}
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	$("#myModalLabel").html("Import EAMS Data");

	var data = new Backbone.Model;
	data.set("holder", "myModalBody");
	data.set("adj_number", adj_number);
	
	$("#myModalBody").html(new eams_scrape_view({model: data}).render().el);
	$("#input_for_checkbox").hide();
	
	//$("#modal_save_holder").html('<a title="Import Eams" class="eams_import save" onClick="importEams()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$("#modal_type").val("eams_import");
	
	$(".modal-header").css("background-image", "url('img/glass_edit_header_task.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_edit_header_task.png')");
	
	$("#myModal4 .modal-dialog").css("width", "1000px");
	$(".modal-content").css("background-image", "url('img/glass_edit_header_task.png')");
	$("#myModal4").modal("toggle");
}
var check_stack_id;
var check_unassigned_id;
var check_remote_id;
function checkRemoteImports() {
	check_remote_id = true;
	checkImports();
}
function checkImports(blnAfterImport) {
	if (typeof blnAfterImport == "undefined") {
		blnAfterImport = false;
	}
	clearTimeout(check_stack_id);
	var stacks = new StacksByType([], {stack_type: 'batchscan'});
	stacks.fetch({
		success: function(data) {
			if (data.length > 0) {
				$("#new_import_indicator").html(data.length);
				$("#new_import_indicator").fadeIn();
				
				//we might still be in import screen
				if ($("#batch_indicator").length > 0 && blnAfterImport) {
					$("#batch_indicator").html("Scan Process is now completed.  Please <a href='#imports' class='white_text' style='text-decoration:underline'>click here to review the imported documents</a><br>&nbsp;");
				}
			} else {
				$("#new_import_indicator").fadeOut();
			}
		}
	});
	var import_interval = 150011;
	if (check_remote_id) {
		import_interval = 15011;
	}
	check_stack_id = setTimeout(function() {
		checkImports();
	}, import_interval);
}
function checkOrphanImports() {
	var stacks = new StacksByType([], {stack_type: 'orphan_batchscan'});
	stacks.fetch({
		success: function(data) {
			if (data.length > 0) {
				var the_count = maxHundred(data.length);
				$("#orphan_import_indicator").html(the_count);
				$("#orphan_import_indicator").fadeIn();
			} else {
				$("#orphan_import_indicator").fadeOut();
			}
		}
	});
}
function checkOrphanUnassigneds() {
	var my_stacks = new MyStacksByType([], {stack_type: 'orphan_unassigned'});
	my_stacks.fetch({
		success: function(data) {
			if (data.length > 0) {
				$("#orphan_unassigned_indicator").html(data.length);
				$("#orphan_unassigned_indicator").fadeIn();
			} else {
				$("#orphan_unassigned_indicator").fadeOut();
			}
		}
	});
}
function checkUnassigneds() {
	clearTimeout(check_unassigned_id);
	var my_stacks = new MyStacksByType([], {stack_type: 'unassigned'});
	my_stacks.fetch({
		success: function(data) {
			if (data.length > 0) {
				$("#unassigned_indicator").html(data.length);
				$("#unassigned_indicator").fadeIn();
			} else {
				$("#unassigned_indicator").fadeOut();
			}
		}
	});
	check_unassigned_id = setTimeout(function() {
		checkUnassigneds();
	}, 177011);
}
var get_inbox_id;
function getMail() {
	return;
	
	clearTimeout(get_inbox_id);
	//gather the emails in db
	var getmails = new GetMail();	//{eword: eword}
	getmails.fetch({
		success: function(data) {
			console.log("getmails:" + data.length);
			//we're going to get this stuff, there is no need of feedback to the system
			//check again in 5 minutes
			get_inbox_id = setTimeout(function() {
				getMail();
			},300037);
		}
	});
}
var check_inbox_id;
function checkInbox() {
	$("#new_message_indicator").html("");
	
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
				//go through them all, if any is high priority, change to blinky
				var messages_list = new_messages.toJSON();
				var blnUrgent = false;
				_.each(messages_list , function(message) {
					if (message.priority=="high") {
						blnUrgent = true;
					}
				});
				$("#new_message_indicator").html(new_messages.length);
				if (blnUrgent) {
					//blink it
					flashWarning("new_message_indicator", true);
				} else {
					flashWarning("new_message_indicator", false);
					$("#new_message_indicator").css("background", "#06F");
				}
				if (!blnReceiveWebmail) {
					$("#new_message_indicator").fadeIn();
				}
				
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
				if (!blnReceiveWebmail) {
					$("#new_message_indicator").html("");
					$("#new_message_indicator").fadeOut();
				}
			}
			/*
			if (blnReceiveWebmail) {
				//now check webmail real quick to get count updated
				webmails = new WebmailCollection();	//{eword: eword}
				webmails.fetch({
					success: function(data) {
						//update new_message_indicator
						var new_messages = $("#new_message_indicator").html();
						if (new_messages=="") {
							new_messages = 0;
						}
						new_messages = Number(new_messages) + Number(data.length);
						$("#new_message_indicator").html(new_messages);
						if (new_messages > 0) {
							if (blnUrgent) {
								//blink it
								flashWarning("new_message_indicator", true);
							} else {
								flashWarning("new_message_indicator", false);
								$("#new_message_indicator").css("background", "#06F");
							}
							$("#new_message_indicator").fadeIn();
						} else {
							$("#new_message_indicator").html("");
							$("#new_message_indicator").fadeOut();
						}
					}
				});
			}
			*/
		}
	});
	
	var new_phone_messages = new NewPhoneCalls();
	new_phone_messages.fetch({
		success:function(data) {
			if (data.length > 0) {
				var messages_list = data.toJSON();
				var blnUrgent = false;
				_.each(messages_list , function(message) {
					if (message.event_priority=="high") {
						blnUrgent = true;
					}
				});
				
				$("#new_phone_indicator").html(new_phone_messages.length);
				
				if (blnUrgent) {
					//blink it
					flashWarning("new_phone_indicator", true);
				} else {
					flashWarning("new_phone_indicator", false);
					$("#new_phone_indicator").css("background", "#3C9");
				}
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
	}, (inbox_delay * 5));
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
	}, (task_delay * 5));
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
function assignCalendar(user_id, assignto_id) {
	if (isNaN(user_id)) {
		return false;
	}
	var url = 'api/calendar/assign';
	formValues = "calendar_id=" + user_id + "&user_id=" + assignto_id;
	//get the permissions
	var write_permission = "";
	if ($("#user_permissionwrite_" + user_id).prop("checked")) {
		write_permission = "write";
	}
	var read_permission = "";
	if ($("#user_permissionread_" + user_id).prop("checked")) {
		read_permission = "read";
	}
	formValues += "&permissions=" + read_permission + write_permission;
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
				$("#calendar_user_name_" + user_id).css("color", "green");
				$("#permission_holder_" + user_id).fadeIn();
				$("#user_permissionread_" + user_id).prop("checked", true);
				setTimeout(function() {
					$("#calendar_user_name_" + user_id).css("color", "white");
				}, 2500);
			}
		}
	});
}
function unassignCalendar(user_id, assignto_id) {
	if (isNaN(user_id)) {
		return false;
	}
	var url = 'api/calendar/unassign';
	formValues = "calendar_id=" + user_id + "&user_id=" + assignto_id;

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
				$("#calendar_user_name_" + user_id).css("color", "green");
				$("#permission_holder_" + user_id).fadeOut();
				setTimeout(function() {
					$("#calendar_user_name_" + user_id).css("color", "white");
				}, 2500);
			}
		}
	});
}
function composeCalendar(element_id) {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	var calendarArray = element_id.split("_");
	var calendar_id = -1;
	if (calendarArray.length == 3) {
		calendar_id = calendarArray[2];
	}
	if (calendar_id < 0) {
		var new_calendar = new Backbone.Model({new_calendar_id: calendar_id});
		new_calendar.set("calendar_id", calendar_id);
		new_calendar.set("calendar", "");
		new_calendar.set("active", "Y");
		new_calendar.set("sort_order", "");
		//new_calendar.set("dateandtime", moment().format('MM/DD/YYYY h:mm a'));
		$("#myModalBody").html(new calendar_view({model: new_calendar}).render().el);
		$("#myModalLabel").html("New Calendar");
	} else {
		var new_calendar = new Calendar({calendar_id: calendar_id});
		new_calendar.fetch({
			success: function (data) {
				data.set("calendar_id", data.id);
				$("#myModalBody").html(new calendar_view({model: data}).render().el);
				$("#myModalLabel").html("Edit Calendar");
			}
		});
	}
	
	$("#modal_save_holder").html('<a title="Save Calendar" class="calendar save" onClick="saveCalendarModal(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$("#modal_type").val("calendar");
	
	$(".modal-header").css("background-image", "url('img/glass_edit_header_task.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("width", "600px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-content").css("background-image", "url('img/glass_edit_header_task.png')");
}
function saveCalendarModal(event) {
	addForm(event, "calendar");
}
function composeSettlement(element_id) {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	var settlementArray = element_id.split("_");
	var settlement_id = settlementArray[22];
	var injury_id = settlementArray[3];
	
	var settlement = new Settlement({settlement_id: settlement_id, injury_id: injury_id});
	$("#input_for_checkbox").hide();
	$("#myModalLabel").html("Edit Settlement");
	$("#modal_save_holder").html('<a title="Save Settlement" class="settlement save" onClick="saveModal(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$("#apply_notes").hide();
	settlement.set("holder", "myModalBody");
	$("#myModalBody").html(new settlement_view({model: settlement}).render().el);
	$("#apply_notes").hide();
	$(".modal-header").css("background-image", "url('img/glass_edit_header_new.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("width", "800px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_edit_header_new.png')");

	$(".modal-content").css("background-image", "url('img/glass_edit_header_new.png')");
	$('#myModal4').modal('show');		
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
		message.set("source_message_id", source_message_id);
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
			case "vservice":
				//get the vservice_id
				var vservice_id = source_message_id;
				source_message_id = -1;
				//we will get email, name from form
				object_action = "Send Kase to " + $("#vservice_name_" + vservice_id).val();
				break;
			case "activity":
				//get the vservice_id
				var activity_id = source_message_id;
				source_message_id = -1;
				current_case_id = object_id[2];
				//we will get email, name from form
				object_action = "Send Activity to Client";
				break;
			case "partie":
				case_id = current_case_id;
				var partie_id = source_message_id;
				source_message_id = -1;
				//we will get email, name from form
				object_action = "Send Email to Partie";
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
	
	//<a title="Click to send to All Applicants" class="interoffice" onClick="allApplicants()" style="cursor:pointer"><i style="font-size:15px;color:#FFFFFF; text-decoration:none" class="glyphicon glyphicon-user"></i></a>&nbsp;
	
	$("#modal_save_holder").html('<span id="apply_notes_holder" class="white_text"><input type="checkbox" id="apply_notes">&nbsp;Apply to Notes</span>&nbsp;&nbsp;<a title="Send Message" class="interoffice save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-send" style="color:#000066; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	switch (reaction) {
		case "reply":
		case "replyall":
			var source_message = new Message({message_id: source_message_id});
			//get the source message info
			source_message.fetch({
				success: function (data) {
					message.set("message", "<br> <br>" + data.get("from") + " wrote:<br>" + data.get("message"));
					message.set("subject", "Re:" + data.get("subject"));
					message.set("thread_uuid", data.get("message_uuid"));
					message.set("case_id", data.get("case_id"));
					
					$("#myModalBody").html(new message_view({model: message}).render().el);
				}
			});	
			break;
		case "forward":
			var source_message = new Message({message_id: source_message_id});
			//get the source message info
			source_message.fetch({
				success: function (data) {
					message.set("message", "<br> <br>" + data.get("from") + " wrote:<br>" + data.get("message"));
					message.set("subject", "Fwd:" + data.get("subject"));
					message.set("thread_uuid", data.get("message_uuid"));
					message.set("case_id", data.get("case_id"));
					
					$("#myModalBody").html(new message_view({model: message}).render().el);
				}
			});	
			break;
		case "activity":
		//get the activity, build the subject
			var activity_id = object_id[1];
			var activity = new Activity();
			activity.set("id", activity_id);
			activity.fetch({
				success: function (data) {
					//need to build a demographics for the injury id
					var subject = "Information regarding your case with " + customer_name;
					message.set("subject", subject);
					
					//var kase = kases.findWhere({case_id: current_case_id});
					var kase =  new Kase({id: current_case_id});
					kase.fetch({
						success: function (kase) {
							if (kase.toJSON().uuid!="") {
								kases.add(kase);
																
								if (kase.get("applicant_salutation")=="") {
									kase.set("applicant_salutation", "Mr or Ms");
								}
								var themessage = "Hello " + kase.get("applicant_salutation") + " " + kase.get("first_name") + " " + kase.get("last_name") + " this email contains information regarding your case with " + customer_name + ". Please contact us at " + customer_phone + " if you have any questions or concerns regarding your case.";
								var activity_category = data.get("activity_category");
								activity_category = activity_category.replace("K", "C") + " Activity";
								themessage += "<br><br>" + activity_category + ":";
								themessage += "<br><br>" + data.get("activity");
			
								message.set("message", themessage);
								message.set("case_id", current_case_id);
								$("#myModalBody").html(new message_view({model: message}).render().el);
								
								$("#myModal4").modal("toggle");
							} else {
								//case does not exist, get out
								document.location.href = "#";
							}
							return;		
						}
					});
				}
			});
			break;
		case "vservice":
			//get the injury, build the subject
			var injury_id = object_id[2];
			var injury = new Injury({case_id: case_id});
			injury.set("id", injury_id);
			
			injury.fetch({
				success: function (data) {
					var data = data.toJSON();
					data.dates = "";
					if (isDate(data.start_date)) {
						data.dates = moment(data.start_date).format("MM/DD/YYYY");
						if (data.end_date!="") {
							if (isDate(data.end_date)) {
								data.dates += "-" + moment(data.end_date).format("MM/DD/YYYY") + " CT";
							}
						}
					}
					//need to build a demographics for the injury id
					var subject = "Referral from " + customer_name;
					message.set("subject", subject);
					
					var kase = kases.findWhere({case_id: current_case_id});
					var themessage = "<p>" + customer_name + "  is referring this case to your office:</p>" + kase.name() + "<br>DOI: " + data.dates + "<br>ADJ #:" + data.adj_number;
					message.set("message", themessage);
					
					$("#myModalBody").html(new message_view({model: message}).render().el);
					
					//because we're a vservice request
					//fetch the specialties, and populate
					var specialties = new SpecialtyCollection();
					specialties.fetch({
						success: function (specialties) {
							$("#follow_up_holder").hide();
							$("#priority_holder").html("Doctor");
							$("#priority_holder").css("background", "red");
							
							$("#priorityInput").hide();
							
							//now add the new select
							$("#medical_specialties_holder").html(new medical_specialties_select({collection: specialties}).render().el);
							$("#priority_holder").css("background", "red");
						}
					});
					
				}
			});
			break;
		case "partie":
			//are we dealing with applicant?
			var partie_type = "partie";
			var theid = "";
			if (source_message_id == "applicant") {
				theid = "-1";
			} else {
				theid = object_id[1];
			}
			//var blnApplicant = $("#" + theid).hasClass("compose_applicant");
			var kase = kases.findWhere({case_id: current_case_id});
			if (object_id[1] < 0) {
				if (customer_id == "1033") {
					var parties = new Parties([], { case_id: current_case_id});
					parties.fetch({
						success: function(data) {
							var arrRecipientInfo = [];
							var parties = data.toJSON();
							_.each( parties, function(partie) {
								if (partie.email!="" && partie.email!=null) {
									arrRecipientInfo.push(partie.email);
								}
							});
							message.set("recipient", arrRecipientInfo.join(";"));
							message.set("subject", kase.name());
							$("#myModalBody").html(new message_view({model: message}).render().el);
							
							$('#myModal4').modal('show');	
						}
					});
				}
			} else {
				message.set("recipient", $("#partie_" + theid).attr("value"));
				message.set("subject", kase.name());
				
				if (theid=="applicant") {
					 message.set("source_message_id", 1)
				}
				$("#myModalBody").html(new message_view({model: message}).render().el);
				
			}
			break;
		default:
			$("#myModalBody").html(new message_view({model: message}).render().el);
	}
	$(".modal-header").css("background-image", "url('img/glass_edit_header_new.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-body").css("overflow-x", "hidden");
	setTimeout(function() {
		//$('.modal-dialog').css('top', '40%');
		$("#token-input-message_ccInput").css("width", "225px"); 
		$("#token-input-message_ccInput").parent().css("width", "225px");
		$("#token-input-message_ccInput").parent().parent().css("width", "225px");
		
		$(".token-input-dropdown-facebook").css("width", "488px"); 
		//$(".token-input-dropdown-facebook").parent().css("width", "488px");
		//$(".token-input-dropdown-facebook").parent().parent().css("width", "488px");
		
		$("#token-input-case_fileInput").css("width", "488px"); 
		$("#token-input-case_fileInput").parent().css("width", "488px");
		$("#token-input-case_fileInput").parent().parent().css("width", "488px");
		
		$("#token-input-message_toInput").css("width", "488px"); 
		$("#token-input-message_toInput").parent().css("width", "488px");
		$("#token-input-message_toInput").parent().parent().css("width", "488px");
		
		$("#token-input-message_bccInput").css("width", "228px"); 
		$("#token-input-message_bccInput").parent().css("width", "228px");
		$("#token-input-message_bccInput").parent().parent().css("width", "228px");
		
		//if it's a vservice
		if (reaction=="vservice") {
			if ($("#vservice_email_" + vservice_id).length > 0) {
				$("#message_toInput").tokenInput("add", {
					id: $("#vservice_email_" + vservice_id).html(), 
					name: $("#vservice_email_" + vservice_id).html(),
					tokenLimit:1
				});
			}
			
		}
		
		if (reaction=="activity") {
			var kase = kases.findWhere({case_id: current_case_id});
			
			$("#message_toInput").tokenInput("add", {
				id: kase.get("applicant_id"), 
				name: kase.get("applicant_email"),
				tokenLimit:1
			});
		}
	}, 700);
	
	$("#myModal4 .modal-dialog").css("width", "600px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-content").css("background-image", "url('img/glass_edit_header_new.png')");
}
function composeExam(object_id, corp_id) {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	var exam_id = "-1";
	if (typeof object_id != "undefined") {
		object_id = object_id.split("_");
		if (object_id.length > 2) {
			exam_id = object_id[object_id.length - 2];
		}
		if (object_id.length > 1) {
			corp_id = object_id[1];
		}
	}
	//are we replying, replyingall, forwarding
	var object_action = "New Exam";
	var case_id = current_case_id;
	var exam = new Exam({id: exam_id});
	$("#modal_type").val("exam");
	if (exam_id < 0) {
		exam.set("case_id", current_case_id);
		exam.set("corp_id", corp_id);
		exam.set("holder", "myModalBody");
		$("#myModalLabel").html(object_action);
		$("#input_for_checkbox").show();
		$("#modal_save_holder").html('<a title="Save Exam" class="exam_view save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
		//$("#input_for_checkbox").html('&nbsp;');
		$("#myModalBody").html(new exam_view({model: exam}).render().el);
	} else {
		object_action = "Edit Exam";
		$("#modal_save_holder").html('<a title="Save Exam" class="exam_view save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
		exam.fetch({
			success: function (exam) {
				exam.set("holder", "myModalBody");
				$("#myModalBody").html(new exam_view({model: exam}).render().el);
				$("#myModalLabel").html(object_action);
			}
		});
	}
	$(".modal-header").css("background-image", "url('img/glass_edit_header_new.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_new.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	$("#myModal4 .modal-dialog").css("width", "600px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_edit_header_new.png')");
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
		$("#select_all_holder").css("margin-right", "50px");
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
	
	$("#myModal4 .modal-dialog").css("width", "600px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_edit_header_new.png')");
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
	if (task_id < 1) {
		$("#myModalLabel").html("New Task");
		task.set("from", login_username);
		//task.set("task_title", "Task Assigned By " + login_username);
		task.set("task_dateandtime", moment().format('MM/DD/YYYY h:mm a'));
		task.set("case_id", task_case_id);	
		task.set("holder", "myModalBody");	
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
				data.set("holder", "myModalBody");	
				$("#myModalBody").html(new task_view({model: data}).render().el);
				$("#myModalLabel").html("Edit Task - ID " + data.get("task_id"));
			}
		});
	}
	
	$("#input_for_checkbox").hide();
	
	
	$("#modal_type").val("task");
	$("#modal_save_holder").html('<a title="Save Task" class="task save" onClick="saveModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	var theme = {theme: "task"};
	$(".task #case_idInput").tokenInput("api/kases/tokeninput", theme);
	
	$(".modal-header").css("background-image", "url('img/glass_edit_header_task.png')");
	$("#myModalBody").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-footer").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-body").css("overflow-x", "hidden");
	
	$("#myModal4 .modal-dialog").css("width", "600px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_edit_header_task.png')");
	$(".modal-content").css("background-image", "url('img/glass_edit_header_task.png')");
}
function hideDelete() {
	$("#deleteModal").modal("toggle");
}
function composeDelete(element_id, table_name) {
	$("#deleteModalLabel").html("Delete");	
	$("#deleteModal #delete").html('YES');
	$("#deleteModalBody").html("Are you sure you want to delete " + table_name.replaceAll("_", " ").capitalizeWords() + " ID " + element_id + "?<div style='padding:5px; text-align:center'><a id='delete' href='javascript:deleteElement(event, \"" + element_id + "\", \"" + table_name + "\")' class='delete_yes white_icon' style='cursor:pointer'>YES</a></div><div style='padding:5px; text-align:center'><a href='javascript:hideDelete()' class='delete_no white_icon' style='cursor:pointer'>NO</a></div>");
	
	$("#input_for_checkbox").hide();
	
	
	$("#modal_type").val("delete");
	var modal_type = $("#modal_type").val();
	
	$("#modal_save_holder").html('<a title="Delete" class="delete" onClick="deleteModal()" style="cursor:pointer"><i class="glyphicon glyphicon-trash" style="color:#FA1616; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$(".modal-header").css("background", "url('img/glass_edit_header_delete.png')");
	$("#deleteModalBody").css("background", "url('img/glass_edit_header_delete.png')");
	$(".modal-footer").css("background", "url('img/glass_edit_header_delete.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#deleteModal .modal-dialog").css("width", "300px");
	$("#deleteModal .modal-dialog").css("background", "url('img/glass_edit_header_delete.png')");
	$(".modal-content").css("background", "url('img/glass_edit_header_delete.png')");
	$("#deleteModal").modal("toggle");
}
function composeDateChange(element_id, table_name) {
	$("#myModalLabel").html("Change Date");	
	var task = new Task({ids: element_id});
	$("#myModalBody").html(new bulk_date_change_view({model: task}).render().el);
	
	$("#input_for_checkbox").hide();
	
	$("#modal_type").val("date_change");
	var modal_type = $("#modal_type").val();
	
	$("#modal_save_holder").html('<a title="Save" class="save" onClick="saveBulkDateChangeModal()" style="cursor:pointer"><i class="glyphicon glyphicon-ok" style="color:#BFFF00; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$(".modal-header").css("background", "url('img/glass_edit_header_task.png')");
	$("#myModalBody").css("background", "url('img/glass_edit_header_task.png')");
	$(".modal-footer").css("background", "url('img/glass_edit_header_task.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("width", "500px");
	$("#myModal4 .modal-dialog").css("background", "url('img/glass_edit_header_task.png')");
	$(".modal-content").css("background", "url('img/glass_edit_header_task.png')");
	$("#myModal4").modal("toggle");
}
function composeDateChangeEvent(element_id, table_name) {
	$("#myModalLabel").html("Change Date");	
	var occurence = new Occurence({ids: element_id});
	$("#myModalBody").html(new bulk_date_change_view({model: occurence}).render().el);
	
	$("#input_for_checkbox").hide();
	
	$("#modal_type").val("date_change");
	var modal_type = $("#modal_type").val();
	
	$("#modal_save_holder").html('<a title="Save" class="save" onClick="saveBulkDateChangeEventModal()" style="cursor:pointer"><i class="glyphicon glyphicon-ok" style="color:#BFFF00; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$(".modal-header").css("background", "url('img/glass_edit_header_task.png')");
	$("#myModalBody").css("background", "url('img/glass_edit_header_task.png')");
	$(".modal-footer").css("background", "url('img/glass_edit_header_task.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("width", "500px");
	$("#myModal4 .modal-dialog").css("background", "url('img/glass_edit_header_task.png')");
	$(".modal-content").css("background", "url('img/glass_edit_header_task.png')");
	$("#myModal4").modal("toggle");
	//$("#myModal4").attr('data-easein', 'perspectiveUpIn');
	//var open = $("#myModal4").attr('data-easein');
	//$('.modal-dialog').velocity('callout.' + open);
	//$("#myModal4").modal("toggle");

}
function composeEmailAssign(element_id, table_name) {
	$("#myModalLabel").html("Assign " + element_id);
	var webmail = new Webmail({ids: element_id});
	webmail.set("holder", "myModalBody");
	
	$("#modal_save_holder").show();
	$("#gifsave").hide();
	
	$("#myModalBody").html(new bulk_webmail_assign_view({model: webmail}).render().el);
	$("#input_for_checkbox").hide();
	$("#modal_type").val("assign");
	var modal_type = $("#modal_type").val();
	
	$("#modal_save_holder").html('<a title="Save" class="save" onClick="saveBulkEmailAssignModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF66; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$(".modal-header").css("background", "url('img/glass_edit_header_assign.png')");
	$("#myModalBody").css("background", "url('img/glass_edit_header_assign.png')");
	$(".modal-footer").css("background", "url('img/glass_edit_header_assign.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("width", "550px");
	$("#myModal4 .modal-dialog").css("background", "url('img/glass_edit_header_assign.png')");
	$(".modal-content").css("background", "url('img/glass_edit_header_assign.png')");
	$("#myModal4").modal("toggle");
}
function composeImportAssign(element_id, table_name) {
	$("#myModalLabel").html("Assign " + element_id);
	console.log(element_id);
	var document_import = new Document({ids: element_id});
	document_import.set("holder", "myModalBody");
	$("#myModalBody").html(new bulk_import_assign_view({model: document_import}).render().el);
	//setTimeout(function() {
		//$("#case_idInput").tokenInput("api/kases/tokeninput", theme);
	//}, 2500);
	$("#input_for_checkbox").hide();
	
	$("#modal_type").val("assign");
	var modal_type = $("#modal_type").val();
	
	$("#modal_save_holder").html('<a title="Save" class="save" onClick="saveBulkImportAssignModal()" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF66; font-size:20px">&nbsp;</i></a>');
	//$("#input_for_checkbox").html('&nbsp;');
	$(".modal-header").css("background", "url('img/glass_edit_header_assign.png')");
	$("#myModalBody").css("background", "url('img/glass_edit_header_assign.png')");
	$(".modal-footer").css("background", "url('img/glass_edit_header_assign.png')");
	$(".modal-body").css("overflow-x", "hidden");
	$("#myModal4 .modal-dialog").css("width", "550px");
	$("#myModal4 .modal-dialog").css("background", "url('img/glass_edit_header_assign.png')");
	$(".modal-content").css("background", "url('img/glass_edit_header_assign.png')");
	$("#myModal4").modal("toggle");
}
function composeNewNote(element_id) {
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	var partieArray = element_id.split("_");
	var partie_array_type = partieArray[1];
	var case_id = current_case_id;
	var notes_id = -1;
	if (partieArray.length == 4 && partieArray[1] != "injurynote") {
		notes_id = partieArray[3];
	}
	if (partie_array_type=="note") {
		partie_array_type = "";
		if (partieArray.length > 2) {
			case_id = partieArray[2];
			if (partieArray.length > 3) {
				notes_id = partieArray[3];
			}
		}
	}
	if (partie_array_type=="injurynote") {
		partie_array_type = "injurynote";
		if (partieArray.length > 2) {
			case_id = partieArray[2];
			injury_id = partieArray[3];
		}
	}
	var modal_title = "";
	if (partieArray.length > 2 && partie_array_type != ""&& partie_array_type != "injurynote") {
		//modal_title = partieArray[1].capitalize();
		modal_title = partieArray[1].replace("~", " ").capitalizeWords();
	}
	
	if (notes_id < 0) {
			partie_array_type = partie_array_type.replace("~", "_");
			var new_note = new Backbone.Model({new_note_id: notes_id, partie_array_type: partie_array_type, partie_array_id: partieArray[2], case_id: case_id});
			new_note.set("dateandtime", moment().format('MM/DD/YYYY h:mm a'));
			new_note.set("entered_by", login_username);
			new_note.set("subject", "");
			new_note.set("callback_date", "");
			new_note.set("status", "general");
			new_note.set("note", "");
			new_note.set("partie_array_id", partieArray[2]);
			new_note.set("holder", "myModalBody");
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
				data.set("holder", "myModalBody");
				$("#myModalBody").html(new new_note_view({model: data}).render().el);
				$("#myModalLabel").html("Edit Note - ID " + data.id);
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
	$("#myModal4 .modal-dialog").css("width", "600px");
	$("#myModal4 .modal-dialog").css("background-image", "url('img/glass_edit_header_task.png')");
	/*
	setTimeout(function() {
		$('.modal-dialog').css('top', '0px');
		$('.modal-dialog').css('margin-top', '50px')
	}, 700);
	*/
	$(".modal-content").css("background-image", "url('img/glass_edit_header_task.png')");
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
var search_time_out = false;
function scheduleFind(obj, namespace, page, blnSearchSpans) {
	clearTimeout(search_time_out);
	search_time_out = setTimeout(function(){
		findIt(obj, namespace, page, blnSearchSpans);
	}, 1000);
}
function findIt(obj, namespace, page, blnSearchSpans) {
	//contacts
	var theobj = $("#" + obj.id);
	var val = $(theobj).val();
	if (namespace == "rolodex_listing") {
		var contacts = new ContactCollection([]);
		search_rolodex = contacts.searchDB(val);
		return;
	}
	if (typeof blnSearchSpans == "undefined") {
		blnSearchSpans = false;
	}
	var val = $.trim($(theobj).val()).replace(/ +/g, ' ').toLowerCase();
	var $rows = $('.' + namespace + ' .' + page + '_data_row');
	
	$(".date_row").hide();
	$rows.show().filter(function() {
		if (blnSearchSpans) {
			var text = $( '.search_' + page + '_item', $( this ) ).text ().replace(/\s+/g, ' ').toLowerCase();
		} else {
			var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
		}
		if (text.indexOf(val) > -1) {
			if (this.classList.length>=3) {
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
	$(".letter_row").hide();
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
function filterCustomerCalendar(this_view) {
	var thetype = $("#event_typeFilter").val();
	var all_customer_events = new CustomerByTypeEvents({type: thetype});
	all_customer_events.fetch({
		success: function (data) {
			//then re-assign to calendar
			this_view.collection.reset(data.toJSON());
		}
	});
}
function filterEvents(this_el, source) {
	var thetype = $("#event_typeFilter").val();
	var newSource = source + '/' + thetype;
	if (thetype=="") {
		var newSource = source;
	}
    this_el.fullCalendar('removeEventSource', source);
    this_el.fullCalendar('refetchEvents');
    this_el.fullCalendar('addEventSource', newSource)
    this_el.fullCalendar('refetchEvents');
    source = newSource;
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
	Backbone.history.navigate("documents/" + id, true);
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
					//kases.remove(kases.findWhere({case_id: id}));
					//$('#kases_recent').html(new kase_list_category_view({model: kases}).render().el);
					//document.location.href = "#kases";
					if (form_name=="event") {
						resetCalendarAfterSave();
					}
					
                }
            }
        });
}
function deleteElement(event, id, form_name, remove_item) {
	if (typeof remove_item == "undefined") {
		remove_item = "";
	}
	$("#deleteModal #delete").html('<i class="icon-spin4 animate-spin" style="font-size:1.5em"></i>');
	
	//$("#deleteModal").modal('toggle');
	
	if (form_name == "occurence") { 
		form_name = "event";
	}
	
	if (form_name == "prior_medical") { 
		form_name = "corporation";
		$('#deleteModal').modal('toggle');
	}
	if (form_name == "documents_import") { 
		form_name = "document";
	}
	var self = this;
	if (typeof event != "undefined") {
		event.preventDefault(); // Don't let this button submit the form
	}
	var switch_up = "";
	if (form_name == "tasking") { 
		switch_up = "now";
		form_name = "task";
	}
	var url = 'api/' + form_name + '/delete';
	formValues = "id=" + id + "&table_name=" + form_name;
	if (remove_item!="") {
		formValues += "&remove_item=" + remove_item;
	}
	if (switch_up == "now") { 
		switch_up = "";
		form_name = "tasking";
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
				if (typeof data.form_name != "undefined") {
					form_name = data.form_name;
				}
				//hide the grid containing the element
				if ($("#partie_nameGrid_" + id).length > 0) {
					$("#partie_nameGrid_" + id).fadeOut();
				}
				if (form_name == "forms") {
					return true;
				}
				if (form_name == "task" || form_name == "event" || form_name == "document" || form_name == "letter" || form_name == "notes" || form_name == "webmail" || form_name == "messages" || form_name == "kase") {
					if (form_name!="letter" && $("#letter_listing").length==0 && $("#stack_listing").length==0) {
						if (form_name != "tasking" && form_name != "task") {
							$('#deleteModal').modal('toggle');
						}
					}
					if ($("#stack_listing").length > 0) {
						form_name = "documents_import";
					}
					if (form_name == "tasking") { 
						switch_up = "now";
						form_name = "task";
					}
					//hide the row
					if (form_name=="event") {
						form_name = "occurence";
					}
					if (form_name=="document") {
						var arrIDs = id.split(", ");
						for(var i =0; i < arrIDs.length; i++) {	
							var the_id = arrIDs[i];
							$(".document_row_" + the_id).css("background", "red");
							setTimeout(function() {
								$(".document_row_" + the_id).fadeOut();
							}, 2500);
						}
						var mass_change = document.getElementById("mass_change");
						if (typeof mass_change != "undefined" && mass_change != null) {
							document.getElementById("mass_change").selectedIndex = 0;
						}
						if ($("#letter_listing").length==0 && $("#picture_holder").length==0) {
							form_name = "kase_document";
						}
						if($("#picture_holder").length > 0) {
							$("#picture_holder").html("<span style='background:red;color:white'>deleted</span>");
						}
						if ($("#letterholder_" + id).length>0) {
							$("#letterholder_" + id).css("background", "red");
							$('#deleteModal').modal('toggle');
						}
					}
					if (form_name=="notes") {
						$(".note_data_row_" + id).css("background", "red");
					}
					if (form_name=="messages") {
						$(".message_row_" + id).css("background", "red");
					}
					if (form_name=="kase") {
						$(".kase_data_row_" + id).css("background", "red");
						
						//now find the kase in kases and remove it
						var kase = kases.findWhere({case_id: id});
						if (typeof kase != "undefined") {
							kases.remove(kase);
						}
					}
					if (form_name=="documents_import") {
						var arrIDs = id.split(", ");
						for(var i =0; i < arrIDs.length; i++) {	
							var the_id = arrIDs[i];
							$(".document_row_" + the_id).css("background", "red");
						}
						//now hide the red ones
						setTimeout(function() {
							var arrIDs = id.split(", ");
							for(var k =0; k < arrIDs.length; k++) {	
								var row_id = arrIDs[k];
								$(".document_row_" + row_id).fadeOut();
							}
						}, 2500);
						document.getElementById("mass_change").selectedIndex = 0;
					}
					if (form_name=="webmail") {
						var arrIDs = id.split(", ");
						for(var i =0; i < arrIDs.length; i++) {	
							var the_id = arrIDs[i];
							$(".webmail_row_" + the_id).css("background", "red");
						}
						//now hide the red ones
						setTimeout(function() {
							var arrIDs = id.split(", ");
							for(var k =0; k < arrIDs.length; k++) {	
								var row_id = arrIDs[k];
								$(".webmail_row_" + row_id).fadeOut();
							}
						}, 2500);
						document.getElementById("mass_change").selectedIndex = 0;
					}
					$("." + form_name + "_row_" + id).css("background", "red");
					setTimeout(function() {
						//hide the processed row, no longer a batch scan
						if (form_name=="letter") {
							$("#letterholder_" + id).fadeOut();
						}
						if (form_name=="document" && $("#letter_listing").length>0) {
							$("#letterholder_" + id).fadeOut();
						}
						if (form_name=="notes") {
							$(".note_data_row_" + id).fadeOut();
						}
						if (form_name=="messages") {
							$(".message_row_" + id).fadeOut();
						}
						if (form_name=="kase") {
							$(".kase_data_row_" + id).fadeOut();
						}
						$("." + form_name + "_row_" + id).fadeOut();
					}, 2500);
				}
			}
		}
	});
	if (form_name=="document" && $("#letter_listing").length==0 && $("#stack_listing").length==0 && $("#picture_holder").length==0) {
		form_name = "kase_document";
	}
	if (form_name == "webmail") {
		var arrIDs = id.split(", ");
		var arrlength = arrIDs.length;
		if (arrlength > 1) {
			return true;
		}
	}
	if (form_name == "task" || form_name == "tasking") {
		if (switch_up == "") { 
			switch_up = "now";
			form_name = "task";
		}
	}
	$("." + form_name + "_row_" + id).css("background", "red");
	setTimeout(function() {
			//hide the processed row, no longer a batch scan
			$("." + form_name + "_row_" + id).fadeOut();
			if (form_name == "task" || form_name == "tasking") {
				if (switch_up == "now") { 
				switch_up = "";
				form_name = "tasking";
			}
		}
	}, 2500);
	
	if (form_name!="letter" && form_name!="event" && form_name!="task" && form_name!="tasking" && form_name!="corporation" && form_name!="notes" && form_name!="webmail" && form_name!="messages" && form_name!="kase" && form_name!="user" && form_name!="forms") {
		if ($("#letter_listing").length==0 && $("#picture_holder").length==0) {
			hideDelete();
		}
	}
	
	if (form_name=="injury") {
		var kase = kases.findWhere({id: id});
		if (typeof kase != "undefined") {
			kases.remove(kase);
		}
	}
	return true;
}
function closeElement(event, id, form_name) {
	var self = this;
	event.preventDefault(); // Don't let this button submit the form
	var url = 'api/' + form_name + '/update';
	formValues = "id=" + id + "&table_name=" + form_name + "&task_type=closed";

	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else { // If not
				var row_background = $(".task_row_" + id).css("background");
				$(".task_row_" + id).css("background", "green");
				$(".type_holder_" + id).html("closed");
				setTimeout(function() {
					//hide the processed row, no longer a batch scan
					$(".task_row_" + id).fadeOut();
				}, 2500);
			}
		}
	});
	return true;
}
function searchForm(event, subform) {
	var self = this;
	event.preventDefault(); // Don't let this button submit the form
	
	if (typeof subform == "undefined") {
		var form_name = $("#sub_category_holder").attr("class");
	} else {
		form_name = subform;
	}
	
	var formValues = $("#" + form_name + "_form").serialize();
	var find = "Input";
	var regEx = new RegExp(find, 'g');
	formValues = formValues.replace(regEx, '');
	var url = "api/kase/advancesearch";
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
				
				if (form_name == "kase") {
					var my_kases = new Backbone.Collection;
					my_kases.add(data)
					$("#myModal4").modal("toggle");
					$(document).attr('title', "Kases Search Results");
					var mymodel = new Backbone.Model();
					mymodel.set("key", "");
					mymodel.set("search_parameters", this.data);
					
					$('#content').html(new kase_listing_view_mobile({collection: my_kases, model:mymodel}).render().el);
					
					setTimeout(function() {
						$("#kase_status_title").html("Found Kases");
					}, 700);
				}
			}
		}
	});
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
			additionalFormValues = additionalFormValues.substr(additionalFormValues.indexOf("ageInput"));
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
	/*
	if (form_name == "contact") {
		var id = $(".contact #table_id").val();
		if (id > 0) {
			self.updateForm(event, "contact", "contact");
		}
		var first_name = $(".contact #first_nameInput").val();
		var last_name = $(".contact #last_nameInput").val();
		var full_address = $(".contact #full_addressInput").val();
		var phone = $(".contact #phoneInput").val();
		var notes = $(".contact #notesInput").val();
		//perform an ajax call to track views by current user
		var url = 'api/contact/add';
		formValues = "&table_name=contact&first_nameInput=" + first_name + "&last_nameInput=" + last_name + "&full_addressInput=" + full_address + "&phoneInput=" + phone + "&notesInput=" + notes;
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					document.location.href = "#contacts";
				}
			}
		});
	}
	*/
	//return;
	
	if (form_name=="user") {
		var activated = "N";
		if ($("#activatedInput").prop("checked")) {
			activated = "Y";
		}
		formValues += "&activated=" + activated;
	}
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
				if (form_name == "kase" || form_name == "event" || form_name == "setting" || form_name == "calendar" || form_name == "check") {
					if (customer_id != "1033") {
						$('#myModal4').modal('toggle');
					}
					$("#gifsave").hide();

					if (form_name == "setting") {
						$(".list_title").css("color","#45CC42");
						setTimeout(function() {
							refreshCustomerSettings();
						}, 555);
					}
					if (form_name == "calendar") {
						$(".list_title").css("color","#45CC42");
						setTimeout(function() {
							refreshCalendars();
						}, 555);
					}
					if (form_name == "check") {
						setTimeout(function() {
							var kase = kases.findWhere({case_id: current_case_id});
							refreshChecks(kase);
						}, 555);
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
				
				//insert the id, so we can update right away
				$("." + form_name + " #table_id").val(data.id);
				$("." + form_name + " #" + api_url + "_uuid").val(data.uuid);
				
				if (form_name == "kase") {
					current_case_id = data.id;
					//redirect to new applicant
					var new_kase = new Kase();
					$(".kase #table_id").val(data.id);
					$(".kase #case_id").val(data.id);
					//update the next case number
					customer_settings.set("case_number_next", data.case_number_next);
					if (customer_id == 1049) {
						//add task
						var task_url = 'api/task/add';
						
						var tasksformValues = "table_name=task";
						tasksformValues += "&task_title=re: Case review ";
						tasksformValues += "&task_descriptionInput=Case Review";
						tasksformValues += "&assignee=RLG";
						
						var todayDate = new Date();
						var someDate = new Date();
						var numberOfDaysToAdd = 120;
						someDate.setDate(todayDate.getDate() + numberOfDaysToAdd);
						
						while (someDate.getDay() == 0 || someDate.getDay() == 6) {
							someDate.setDate(someDate.getDate()+1)
						}
						
						tasksformValues += "&task_dateandtime=" + moment(someDate).format("MM/DD/YYYY") + " 08:30AM";
						tasksformValues += "&case_id=" + current_case_id;
						
						//return;
						$.ajax({
						url:task_url,
						type:'POST',
						dataType:"json",
						data: tasksformValues,
							success:function (data) {
								if(data.error) {  // If there is an error, show the error messages
									saveFailed(data.error.text);
								} else {
									//console.log("save_120");
								}
							}
						});
						
						var tasksformValues = "table_name=task";
						tasksformValues += "&task_title=POA task";
						tasksformValues += "&task_descriptionInput=POA task";
						tasksformValues += "&assignee=RLG";
						
						var todayDate = new Date();
						var someDate = new Date();
						var numberOfDaysToAdd = 230;
						someDate.setDate(todayDate.getDate() + numberOfDaysToAdd);
						while (someDate.getDay() == 0 || someDate.getDay() == 6) {
							someDate.setDate(someDate.getDate()+1)
						}
						
						tasksformValues += "&task_dateandtime=" + moment(someDate).format("MM/DD/YYYY") + " 08:30AM";
						tasksformValues += "&case_id=" + current_case_id;
						
						//return;
						$.ajax({
						url:task_url,
						type:'POST',
						dataType:"json",
						data: tasksformValues,
							success:function (data) {
								if(data.error) {  // If there is an error, show the error messages
									saveFailed(data.error.text);
								} else {
									//console.log("save_230");
								}
							}
						});
						
						if ($("#workerInput").val()!="") {
							var tasksformValues = "table_name=task";
							tasksformValues += "&task_title=introduce and remind of med appt";
							tasksformValues += "&task_descriptionInput=introduce and remind of med appt";
							tasksformValues += "&assignee=" + $("#workerInput").val();
							
							var todayDate = new Date();
							var someDate = new Date();
							var numberOfDaysToAdd = 10;
							someDate.setDate(todayDate.getDate() + numberOfDaysToAdd);
							while (someDate.getDay() == 0 || someDate.getDay() == 6) {
								someDate.setDate(someDate.getDate()+1)
							}
							
							tasksformValues += "&task_dateandtime=" + moment(someDate).format("MM/DD/YYYY") + " 08:30AM";
							tasksformValues += "&case_id=" + current_case_id;
							
							//return;
							$.ajax({
							url:task_url,
							type:'POST',
							dataType:"json",
							data: tasksformValues,
								success:function (data) {
									if(data.error) {  // If there is an error, show the error messages
										saveFailed(data.error.text);
									} else {
										//console.log("save_10_worker");
									}
								}
							});
						}
					}
					if ($("#workerInput").val() != "") {
						//add task
						var task_url = 'api/task/add';
						
						var tasksformValues = "table_name=task";
						tasksformValues += "&task_title=Send Records to Matrix";
						tasksformValues += "&task_descriptionInput=Send Request to Matrix for records needed  -  Employment, Claim, and All Medical, Cal Osha , etc.  30-day period has expired.  If you have received any records, please do not request to pickup from Location.";
						tasksformValues += "&assignee=" + $("#workerInput").val();
						
						var todayDate = new Date();
						var someDate = new Date();
						var numberOfDaysToAdd = 30;
						someDate.setDate(todayDate.getDate() + numberOfDaysToAdd);
						while (someDate.getDay() == 0 || someDate.getDay() == 6) {
							someDate.setDate(someDate.getDate()+1)
						}
						
						tasksformValues += "&task_dateandtime=" + moment(someDate).format("MM/DD/YYYY") + " 08:30AM";
						tasksformValues += "&case_id=" + current_case_id;
						
						//return;
						$.ajax({
						url:task_url,
						type:'POST',
						dataType:"json",
						data: tasksformValues,
							success:function (data) {
								if(data.error) {  // If there is an error, show the error messages
									saveFailed(data.error.text);
								} else {
									//
								}
							}
						});
					}
					new_kase.set("id", $(".kase #table_id").val());
					new_kase.fetch({
						success: function(kase) {
							kases.add(kase);
							$(".kase #table_uuid").val(kase.get("uuid"));
														//add homemedical if it's open
							if ($("#homemedical_form").length > 0) {
								//save the form after adding the case_id
								$(".homemedical#case_id").val(kase.get("case_id"));
								addForm(event, "homemedical", "homemedical");
							}
							
							setTimeout(function() {
								document.location.href="#applicant/" + kase.get("case_id");
							}, 10);
							/*
							setTimeout(function() {
								//using settimeout for async behavior, i want to save the venue as corporation in the background
								//this affects the new kase logic
								addForm(event, "kase_venue", "corporation");
							}, 2000);
							*/
						}
					});
					return true;
				}
				//if it's a homemedical, need to add it as a partie now
				if (form_name == "homemedical") {
					var corporation_id = $(".homemedical #corporation_id").val();
					//perform an ajax call to track views by current user
					var url = 'api/corporation/add';
					formValues = "case_id=" + current_case_id + "&table_name=corporation&company_nameInput=" + $(".homemedical #provider_nameInput").val() + "&homemedical_uuidInput=" + data.uuid + "&type=homemedical&corporation_id=" + corporation_id;
					
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
				if (form_name == "Defendant") {
					var corporation_id = $("#corporation_id").val();
					if (corporation_id == "" || corporation_id == "undefined") {
						corporation_id = "-1";
					}
					var corporations = new Corporations([], { case_id: current_case_id });
					corporations.fetch({
						success: function(corporations) {
							carrier_partie = corporations.findWhere({"type": "carrier"});
								if (typeof carrier_partie == "undefined") {
									document.location.href="#parties/" + current_case_id + "/-1/carrier";
									return true;
							}					
						}
					});
				}
				
				if (form_name == "Carrier") {
					document.location.href = "#kases/" + current_case_id;
					return;
					var kase = kases.findWhere({case_id: current_case_id});
					var kase_type = kase.get("case_type");
					if (kase_type == "Personal Injury") {
						document.location.href="#parties/" + current_case_id + "/-1/carrier";
					} else {
						document.location.href = "#kases/" + current_case_id;
					}
				}
				if (form_name == "person") {
					var case_id = $(".person #case_id").val();
					var kase = kases.findWhere({case_id: case_id});
					var kase_type = kase.get("case_type");
					kase.set("applicant_id", data.id);
					//check for an employer?
					var corporations = new Corporations([], { case_id: case_id });
					corporations.fetch({
						success: function(corporations) {
							var blnWCAB = isWCAB(kase_type);
							//if we have no parties and no applicant
							var next_partie_type = "employer";
							if (!blnWCAB) {
								next_partie_type = "defendant";
							}
							employer_partie = corporations.findWhere({"type": next_partie_type});
								if (typeof employer_partie == "undefined") {
									document.location.href="#parties/" + case_id + "/-1/" + next_partie_type;
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
							//add the kase to the kases
							kases.add(kase, {merge:true});
							//lookup the calendar id
							var thecalendar = customer_calendars.findWhere({calendar_id: calendar_id});
							var calendar_id = -1;
							if (typeof thecalendar != "undefined") {
								calendar_id = thecalendar.get("calendar_id");
							}
							eventformValues = "table_name=event";
							eventformValues += "&event_title=" + encodeURI(kase.get("name"));
							eventformValues += "&event_descriptionInput=Kase Intake by " + login_username;
							eventformValues += "&event_kind=intake";
							eventformValues += "&event_type=intake";
							eventformValues += "&calendar_id=" + calendar_id;
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
										//
									}
								}
							});
							//make sure we have doi
							var kase_dois = dois.findWhere({case_id: current_case_id});
							
							if (typeof kase_dois == "Object" || typeof kase_dois == "object") {
								var doi = kase_dois.toJSON();
								if (doi.start_date=="0000-00-00") {
									document.location.href = "#injury/" + current_case_id + "/" + doi.injury_id;
									return;
								}
							} else {
								var kase_dois = new KaseInjuryCollection({case_id: current_case_id});
								kase_dois.fetch({
									success: function(kase_dois) {
										if (kase_dois.length > 0) {
											document.location.href = "#injury/" + current_case_id + "/" + kase_dois.toJSON()[0].injury_id;
										} else {
											document.location.href = "#newinjury/" + current_case_id;
										}
									}
								});
								
								return true;
							}
						}
					});
						
					return true;
				}
				//this might be a new injury
				if (form_name == "injury") {
					//let's trigger the other save events
					$(".bodyparts #injury_id").val(data.id);
					$(".injury_number #injury_id").val(data.id);
					$(".additional_case_number #injury_id").val(data.id);
					
					$(".bodyparts .save").trigger("click");
					$(".injury_number .save").trigger("click");
					
					saveFormSuccessful(form_name);
					return true;
				}
				if (form_name == "injury_number") {
					$(".additional_case_number #table_id").val(data.id);
					$(".additional_case_number .save").trigger("click");
					
					saveFormSuccessful(form_name);
					return true;
				}
				if (form_name == "additional_case_number") {
					//do we have a carrier
					var corporations = new Corporations([], { case_id: case_id });
					corporations.fetch({
						success: function(corporations) {
							//if we have no parties and no applicant
							carrier_partie = corporations.findWhere({"type": "carrier"});
				
							if (typeof carrier_partie == "undefined") {
								//document.location.href="#parties/" + case_id + "/-1/carrier";
								document.location.href="#kases/" + case_id;
								return true;
							}
						}
					});
					//return true;
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
	if (form_name=="user") {
		var activated = "N";
		if ($("#activatedInput").prop("checked")) {
			activated = "Y";
		}
		formValues += "&activated=" + activated;
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
				$('.' + form_name + ' #gifsave').hide();
				$("#partie_type_holder").css("color", "#45CC42");
				$("#panel_title").css("color", "#45CC42");
				$(".kai #panel_title").css("color", "#45CC42");
				setTimeout(function() {
					$("#partie_type_holder").css("color", "white");
					$("#panel_title").css("color", "white");
					$(".kai #panel_title").css("color", "white");
				}, 3000);
				if (form_name == "kase" || form_name == "event" || form_name == "setting" || form_name == "calendar" || form_name == "check") {
					if (customer_id != "1033") {
						$('#myModal4').modal('toggle');
					}
					if (form_name == "setting") {
						$(".list_title").css("color","#45CC42");
						setTimeout(function() {
							refreshCustomerSettings();
						}, 555);
					}
					if (form_name == "calendar") {
						$(".list_title").css("color","#45CC42");
						setTimeout(function() {
							refreshCalendars();
						}, 555);
					}
					if (form_name == "kase") {
						if ($("#homemedical_form").length > 0) {
							//save the form after adding the case_id
							$(".homemedical#case_id").val($(".kase #table_id").val());
							addForm(event, "homemedical", "homemedical");
						}
					}
					if (form_name == "check") {
						setTimeout(function() {
							var kase = kases.findWhere({case_id: current_case_id});
							refreshChecks(kase);
						}, 555);
					}
				}
				//if it's a homemedical, need to add it as a partie now
				if (form_name == "homemedical") {
					var corporation_id = $(".homemedical #corporation_id").val();
					var company_name = $(".homemedical #provider_nameInput").val();
					var full_address = $(".homemedical #full_addressInput").val();
					var phone = $(".homemedical #phoneInput").val();
					//perform an ajax call to track views by current user
					var url = 'api/corporation/update';
					if (corporation_id==-1) {
						url = 'api/corporation/add';
					}
					formValues = "case_id=" + current_case_id + "&table_name=corporation&company_nameInput=" + company_name + "&full_addressInput=" + full_address + "&phoneInput=" + phone + "&homemedical_uuidInput=" + data.uuid + "&type=homemedical&corporation_id=" + corporation_id;
					
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
						var kase_dois = new KaseInjuryCollection({case_id: case_id});
						kase_dois.fetch({
							success: function(kase_dois) {
								if (kase_dois.length > 0) {
									document.location.href = "#injury/" + current_case_id + "/" + kase_dois.toJSON()[0].injury_id;
								} else {
									document.location.href = "#newinjury/" + current_case_id;
								}
							}
						});
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
					saveFormSuccessful(form_name);
					return true;
				}
				if (form_name == "injury_number") {
					$(".additional_case_number #table_id").val(data.id);
					if ($(".additional_case_number .save").css("visibility")!="hidden") {
						$(".additional_case_number .save").trigger("click");
						saveFormSuccessful(form_name);
						return;
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
								//document.location.href="#parties/" + current_case_id + "/-1/carrier";
								document.location.href="#kases/" + current_case_id;
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
			//},555);
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
		var arrWindow = window.location.href.split("#");
		if ($("#occurence_listing").length > 0 && arrWindow[1].indexOf("kalendarbydate") > -1) {
			if ($(".calendar_title").length > 0) {
				listCustomerEvents("", "", current_case_id);
			}
		} else {
			if ($(".calendar_title").length > 0) {
				resetCalendarAfterSave();
			}
		}
		//are we in home page
		
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
	var text_box = "";
	if (className == "personal_injury") {
		text_box = document.querySelectorAll("." + className + " #personal_injury_locationInput")[0];
	} else {
		text_box = document.querySelectorAll("." + className + " #full_addressInput")[0];
	}
	if (typeof google.maps.places != "undefined") {
		if (className == "personal_injury") {
			text_box = document.querySelectorAll("." + className + " #personal_injury_locationInput")[0];
		} else {
			text_box = document.querySelectorAll("." + className + " #full_addressInput")[0];
		}
		window["autocomplete" + className] = new google.maps.places.Autocomplete(text_box, { types: ['geocode'] });
		// Create the autocomplete object, restricting the search
		// to geographical location types.
		if (className != "personal_injury") {
			// populate the address fields in the form.
			google.maps.event.addListener(window["autocomplete" + className], 'place_changed', function() {
				setTimeout("fillInAddress('" + className + "')", 200);			
			});
		}
	}
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
	var administrative_area_level_1 = "";
	var postal_code = "";
	for (var i = 0; i < place.address_components.length; i++) {
		var addressType = place.address_components[i].types[0];
		//might be sublocality_level_1
		if (place.address_components[i].types.length > 1) {
			if (addressType == "sublocality_level_1" && place.address_components[i].types[1]=="sublocality") {
				addressType = "sublocality";
			}
		}
		if (componentForm[addressType]) {
		  var val = place.address_components[i][componentForm[addressType]];
		  document.getElementById(addressType + "_" + className).value = val;
		  
		  if (addressType=="neighborhood") {
			//sublocality = val;  
		  }	  
		  if (addressType=="sublocality") {
			sublocality = val;  
		  }
		  if (addressType=="administrative_area_level_1") {
			administrative_area_level_1 = val;  
		  }
		  if (addressType=="postal_code") {
			postal_code = val;  
		  }
		}
	}
	if (sublocality=="") {
		sublocality = document.getElementById("locality_" + className).value;
	}
	var text_box = document.querySelectorAll("." + className + " #full_addressInput")[0];
	var arrAddress = [];
	if (document.getElementById("street_number_" + className).value!="") {
		arrAddress[arrAddress.length] = document.getElementById("street_number_" + className).value + " " + document.getElementById("route_" + className).value;
		if (sublocality!="") {
			arrAddress[arrAddress.length] = sublocality;
		}
		arrAddress[arrAddress.length] = document.getElementById("administrative_area_level_1_" + className).value + " " + document.getElementById("postal_code_" + className).value;
		
	} else {
		sublocality = place.name
		arrAddress[arrAddress.length] = sublocality;
		arrAddress[arrAddress.length] = administrative_area_level_1;
	}
	var full_address = arrAddress.join(", ");
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
var showDeleteConfirm = function(element, leftpos) {
	if (typeof leftpos == "undefined") {
		leftpos = 300;
	}
	var rect = element.getBoundingClientRect();
	
	var scrollTop = document.documentElement.scrollTop?
					document.documentElement.scrollTop:document.body.scrollTop;
	var scrollLeft = document.documentElement.scrollLeft?                   
					 document.documentElement.scrollLeft:document.body.scrollLeft;
	elementTop = rect.top+scrollTop - 40;
	//elementTop = scrollTop - 20;
	elementLeft = rect.left+scrollLeft - leftpos;
	return [elementTop, elementLeft];
}
function saveBulkEmailAssignModal() {
	//event.preventDefault();
	var ids = $(".bulk_webmail_assign #ids").val();
	var arrIDs = ids.split(", ");
	var case_id = $("#case_idInput").val();
	if (case_id==""){
		alert("You must select a Kase.  Please type in the search box to bring up kases");
		return;
	}
	
	$("#input_for_checkbox").hide();
	$("#modal_save_holder").hide();
	$("#gifsave").css("margin-top", "-5px");
	$("#gifsave").show();
	
	var case_note = $("#notes_webmail").val();
	for (var int = 0; int < arrIDs.length; int++) {
		var theid = arrIDs[int];
	
		var webmail_message_id = $("#webmail_message_id_" + theid).val();
		var formValues = { 
			id: theid,
			message_id: webmail_message_id,
			case_id: case_id,
			type:"webmail",
			table_name:"message",
			table_attribute:"webmail",
			case_note: case_note
		};

		var url = "api/webmail/assign";
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
				} else { 
					var web_id = data.email_id;
					//hide the save button
					$("#webmail_save_" + web_id).hide();
					/*
					$("#webmail_save_" + web_id).fadeOut(function() {
						$("#disabled_webmail_save_" + web_id).fadeIn();
					});
					*/
					
					//get the color
					var back_color = $(".kase_webmail_row_" + web_id).css("background");
					//mark it all green
					$(".kase_webmail_row_" + web_id).css("background", "green");
					setTimeout(function() {
						//hide the processed row, no longer a batch scan
						$(".kase_webmail_row_" + web_id).fadeOut();
						//$(".kase_webmail_row_" + web_id).css("background", back_color);
					}, 1500);
				}
			}
		});
	}
	$("#myModal4").modal("toggle");
}
function saveBulkDateChangeModal() {
	//event.preventDefault();
	$("#input_for_checkbox").hide();
	$("#modal_save_holder").hide();
	$("#gifsave").css("margin-top", "-5px");
	$("#gifsave").show();
	
	var ids = $(".bulk_date_change #ids").val();
	var arrIDs = ids.split(", ");
	var dateandtime = $("#task_dateInput").val();
	for (var int = 0; int < arrIDs.length; int++) {
		var theid = arrIDs[int];
		var formValues = { 
			id: theid,
			dateandtime: dateandtime,
			type:"date_change",
			table_name:"task"
		};
		var url = "api/task/update/date";
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
				} else { 
					for (var int = 0; int < arrIDs.length; int++) {
						var theid = data.id;
						//get the color
						//$("#myModal4").modal("toggle");
						var back_color = $(".task_row_" + theid).css("background");
						//mark it all green
						$(".task_row_" + theid).css("background", "green");
						setTimeout(function() {
							//hide the processed row, no longer a batch scan
							//$(".task_row_" + theid).fadeOut();
							$(".task_row_" + theid).css("background", back_color);
						}, 1500);
						location.reload();
					};
					
				}
			}
		});
	}
	$("#myModal4").modal("toggle");
}
function saveBulkDateChangeEventModal() {
	//event.preventDefault();
	$("#input_for_checkbox").hide();
	$("#modal_save_holder").hide();
	$("#gifsave").css("margin-top", "-5px");
	$("#gifsave").show();
	
	var ids = $(".bulk_date_change #ids").val();
	var arrIDs = ids.split(", ");
	var dateandtime = $("#task_dateInput").val();
	for (var int = 0; int < arrIDs.length; int++) {
		var theid = arrIDs[int];
		var formValues = { 
			id: theid,
			dateandtime: dateandtime,
			type:"date_change",
			table_name:"event"
		};
		var url = "api/event/update/date";
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
				} else { 
					for (var int = 0; int < arrIDs.length; int++) {
						var theid = data.id;
						//get the color
						//$("#myModal4").modal("toggle");
						var back_color = $(".occurence_row_" + theid).css("background");
						//mark it all green
						$(".occurence_row_" + theid).css("background", "green");
						setTimeout(function() {
							//hide the processed row, no longer a batch scan
							//$(".task_row_" + theid).fadeOut();
							$(".occurence_row_" + theid).css("background", back_color);
							location.reload();
						}, 1500);
						
					};
					
				}
			}
		});
	}
	$("#myModal4").modal("toggle");
}
function saveBulkImportAssignModal() {
	//event.preventDefault();
	$("#input_for_checkbox").hide();
	$("#modal_save_holder").hide();
	$("#gifsave").css("margin-top", "-5px");
	$("#gifsave").show();
	
	var ids = $(".bulk_import_assign #ids").val();
	var arrIDs = ids.split(", ");
	var case_id = $("#case_idInput").val();
	for (var int = 0; int < arrIDs.length; int++) {
		var theid = arrIDs[int];
	
		var name = $("#stack_name_" + theid).val();
		//var case_uuid = $("#stack_case_id_" + theid).val();
		//show the two drop downs and the check box
		var type = $("#stack_type_" + theid).val();
		var category = $("#stack_category_" + theid).val();
		var subcategory = $("#stack_subcategory_" + theid).val();
		var stack_notify = $("#stack_notify_" + theid).val();
		var note = $("#stack_note_" + theid).val();
		
		var formValues = { 
			name: name, 
			document_id: theid, 
			type: type, 
			category: category, 
			subcategory: subcategory, 
			note: note,
			case_id: case_id
		};
		var url = "api/stacks/add";
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					console.log(data.error.text);
				} else { 
					//capture the current backcolor, make it green
					var back_color = $(".document_row_" + theid).css("background-color");
					//mark it all green
					//var ids = $(".bulk_import_assign #ids").val();
					var theid = ids.split(", ");
					for(var k =0; k < theid.length; k++) {	
						var row_id = theid[k];
						$(".document_row_" + row_id).css("background", "green");
					}
					setTimeout(function() {
						var theid = ids.split(", ");
						for(var k =0; k < theid.length; k++) {	
							var row_id = theid[k];
							//restore original backcolor
							$(".document_row_" + row_id).css("background", "url(https://v4.ikase.org/img/glass_row.png)");
						}
					}, 2500);
				}
			}
		});
	}
	$("#myModal4").modal("toggle");
}
function saveCheckModal(event) {
	$("#input_for_checkbox").hide();
	$("#modal_save_holder").hide();
	$("#gifsave").css("margin-top", "-5px");
	$("#gifsave").show();
	$blnValid = $("#check_form").parsley('validate');
		if (!$blnValid) {
			$(".parsley-error").css('border', '2px solid red');
			$(".parsley-error").css('z-index', '4205');
			$("#gifsave").hide();
			$("#modal_save_holder").show();
			$("#apply_notes").hide();
			return;
		}
	addForm(event, "check");
}
function saveModal() {
	$("#gifsave").show();
	$("#modal_save_holder").hide();
	$("#gifsave").css("margin-top", "-5px");
	if ($("#modal_type").val() == "letter") {
		var url = 'api/letter/create';
		var formValues = $("#letter_form").serialize();
		var any_ids = $("#any_ids").val();
		if (any_ids=="") {
			var arrAny = [""];
		} else {
			var arrAny = any_ids.split("|");
		}
		
		arrAny.forEach(function(any_id, index, array) {
			form_post_values = formValues + "&any_id=" + any_id;
			$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: form_post_values,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else { // If not
						if (index == array.length - 1) {
							$('#myModal4').modal('toggle');
						}
						var case_id = $(".letter #case_id").val();
						var template_name = $(".letter #template_name").val();
						/*
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
						*/
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
		});
	}
	if ($("#modal_type").val() == "eams") {
		//doi required
		var doi_id = $("#fields_holder #doi").val();
		if (doi_id=="") {
			$("#gifsave").hide();
			$("#modal_save_holder").show();
			
			$(".doi_cell").css("background", "red");
			return;
		}
		$(".eams #fields_holder").hide();
		$(".eams #loading").show();
		var url = 'api/pdf/create';
		var formValues = $("#eams_form").serialize();
		formValues += "&nopublish=y";
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
					/*
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
					*/
					$(".eams #fields_holder").show();
					$(".eams #loading").hide();
					var arrFile = data.file.split("/");
					//arrFile[arrFile.length - 1]
					$("#eams_form_name_holder").html("<a href='" + data.file + "' title='Click to open pdf' class='white_text' target='_blank'>Click to open generated PDF</a>");

					$("#eams_form_name_holder").css("border", "2px solid white");
					$("#eams_form_name_holder").css("background", "green");
				}
			}
		});
	}
	if ($("#modal_type").val() == "message") {
		var label_holder = $("#myModalLabel").html();
		$("#myModalLabel").html("Sending...");
		var blnReturn = false
		if ($(".interoffice #specialty").length > 0) {
			if ($(".interoffice #specialty").val()=="") {
				$(".interoffice #specialty").css("border", "2px red solid");
				blnReturn = true;
			}
		}
		
		if ($("#apply_tasks").length > 0) {
			if ($("#apply_tasks").prop("checked")) {
				if ($(".interoffice #callback_dateInput").val()=="") {
					$(".interoffice #callback_dateInput").css("border", "2px red solid");
					$(".interoffice #follow_up_holder").css("background", "red");
					blnReturn = true;
				}
				if ($("#task_assigneeInput").val()=="") {
					$(".interoffice #task_assigneeInput").css("border", "2px red solid");
					$(".interoffice #task_assignee_holder").css("background", "red");
					blnReturn = true;
				}
			}
		}
		var to_token_input = $("#message_toInput").val();

		if ($("#emailaddress_toInput").length > 0 && (to_token_input=="" || to_token_input.replaceAll(",", "") == "")) {
			to_token_input = $("#emailaddress_toInput").val();
		}

		if (to_token_input == "") {
			$("#message_to_td .token-input-list-facebook").css("border", "2px red solid");
			blnReturn = true;
		}
		
		if (blnReturn) {
			$("#gifsave").hide();
			$("#modal_save_holder").show();
			return;
		}
		var url = 'api/messages/add';
				
		if ($("#emailaddress_toInput").length > 0) {
			var to_value = $("#message_toInput").val();
			var arrTo = to_value.split(",");
			var email_value = $("#emailaddress_toInput").val();
			var arrEmailTo = email_value.split(",");
			arrTo = arrTo.concat(arrEmailTo);
			$("#message_toInput").val(arrTo.join(","));
		}
		if ($("#emailaddress_ccInput").length > 0) {
			var to_value = $("#message_ccInput").val();
			var arrTo = to_value.split(",");
			var email_value = $("#emailaddress_ccInput").val();
			var arrEmailTo = email_value.split(",");
			arrTo = arrTo.concat(arrEmailTo);
			$("#message_ccInput").val(arrTo.join(","));
		}
		if ($("#emailaddress_bccInput").length > 0) {
			var to_value = $("#message_bccInput").val();
			var arrTo = to_value.split(",");
			var email_value = $("#emailaddress_bccInput").val();
			var arrEmailTo = email_value.split(",");
			arrTo = arrTo.concat(arrEmailTo);
			$("#message_bccInput").val(arrTo.join(","));
		}
			
		var formValues = $("#interoffice_form").serialize();
		
		//attachments
		var arrAttach = [];
		var arrAttachments = $("#queue .filename").children();
		var arrayLength = arrAttachments.length;
		for (var i = 0; i < arrayLength; i++) {
			arrAttach[i] = arrAttachments[i].href.replace(hrefHost, "");
		}
		
		//we might have selected existing case documents
		var case_documents = $(".message_document");
		var attach_document_id = "";
		if (case_documents.length > 0) {
			var arrayLength = case_documents.length;
			var arrAttachCaseDocument = [];
			for (var i = 0; i < arrayLength; i++) {
				var case_document = case_documents[i];
				if (case_document.checked) {
					arrAttachCaseDocument.push(case_document.value);
				}
			}
			attach_document_id = arrAttachCaseDocument.join("|");
			formValues += "&attach_document_id=" + attach_document_id;
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
					} else {
						//maybe we are in notes
						if ($("#kase_content #note_listing").length > 0) {
							notes = new NoteCollection([], { case_id: current_case_id });
							$("#kase_content").html(loading_image);
							notes.fetch({
								success: function(data) {
									var note_list_model = new Backbone.Model;
									note_list_model.set("display", "full");
									note_list_model.set("partie_type", "note");
									note_list_model.set("partie_id", -1);
									note_list_model.set("case_id", current_case_id);
									$('#kase_content').html(new note_listing_view({collection: data, model: note_list_model}).render().el);
								}
							});
						}
					}
				}
			});	
		}
		
		if (document.getElementById("apply_tasks").checked) {
			
			var tasks_url = 'api/task/add';
			var tasksformValues = formValues.replace("table_name=message", "table_name=tasks");
			
			tasksformValues = tasksformValues.replace("messageInput=", "task_descriptionInput=");
			tasksformValues = tasksformValues.replace("subjectInput=", "task_titleInput=");
			if ($("#task_assigneeInput").length==0) {
				tasksformValues = tasksformValues.replace("message_toInput=", "assigneeInput=");
			} else {
				tasksformValues = tasksformValues.replace("message_toInput=", "ignore_meInput=");
				tasksformValues = tasksformValues.replace("task_assigneeInput=", "assigneeInput=");
			}
			tasksformValues = tasksformValues.replace("callback_dateInput=", "task_dateandtimeInput=");
			tasksformValues = tasksformValues.replace("attach_document_id=", "send_document_id=");
			tasksformValues = tasksformValues.replace("case_fileInput=", "case_id=");
			
			//tasksformValues += "&task_fromInput=" + login_username;
			
			//return;
			$.ajax({
			url:tasks_url,
			type:'POST',
			dataType:"json",
			data: tasksformValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					}
				}
			});	
		}
		
		if (document.getElementById("select_all_clients").checked) {
			formValues += "&select_all_clients=Y";
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
					$("#myModalLabel").html("Message Sent&nbsp;<span style='color:white;background:green'>&#10003;</span>");
					setTimeout(function() {
						//hide the modal
						$('#myModal4').modal('toggle');
					}, 1500);
					emptyBuffer();
				}
			}
		});
		//$('#myModal4').modal('toggle');
		//emptyBuffer();
	}
	if ($("#modal_type").val() == "eams_form") {
		var url = 'api/forms/add';
		var theid = $(".eams_form #table_id").val();
		if (theid > 0) {
			url = 'api/forms/update';
		}
		var formValues = $("#eams_form_form").serialize();
		
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
					eamss = new EAMSFormCollection();
					eamss.fetch({
						success: function(data) {
							$(document).attr('title', "Manage EAMS Forms :: iKase");
							$('#content').html(new eams_form_listing({collection: data}).render().el);
							$("#content").removeClass("glass_header_no_padding");
							hideEditRow();
						}
					});
				}
			}
		});
	}
	if ($("#modal_type").val() == "exam") {
		var url = 'api/exams/add';
		if ($("#exam_form #table_id").val()!="" && $("#exam_form #table_id").val()!="-1") {
			url = 'api/exams/update';
		}
		var formValues = $("#exam_form").serialize();
		
		//attachments
		//var arrAttach = [];
		//var arrAttachments = $("#queue .filename").children();
		//var arrayLength = arrAttachments.length;
		//for (var i = 0; i < arrayLength; i++) {
			//arrAttach[i] = arrAttachments[i].href.replace(hrefHost, "");
		//}
		//formValues += "&attachments=" + arrAttach.join("|");
		
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
					//refresh the screen
					exams = new ExamCollection({case_id: current_case_id});
					exams.fetch({
						success: function (data) {
							var exam_info = new Backbone.Model;
							exam_info.set("case_id", current_case_id);
							exam_info.set("holder", "kase_content");
							$('#kase_content').html(new exam_listing({collection: data, model:exam_info}).render().el);
							$("#kase_content").removeClass("glass_header_no_padding");
						}
					});
				}
			}
		});
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
					//kase task
					if ($(document).attr('title') == "Kase Tasks :: iKase") {
						//fetch all tasks
						tasks = new TaskInboxCollection({case_id: current_case_id});
						tasks.fetch({
							success: function (data) {
								var task_listing_info = new Backbone.Model;
								task_listing_info.set("title", "Kase Tasks");
								task_listing_info.set("receive_label", "Due Date");
								$('#kase_content').html(new task_listing({collection: data, model: task_listing_info}).render().el);
								$("#kase_content").removeClass("glass_header_no_padding");
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
		//we might have selected existing case documents
		var case_documents = $(".message_document");
		var attach_document_id = "";
		if (case_documents.length > 0) {
			var arrayLength = case_documents.length;
			var arrAttachCaseDocument = [];
			for (var i = 0; i < arrayLength; i++) {
				var case_document = case_documents[i];
				if (case_document.checked) {
					arrAttachCaseDocument.push(case_document.value);
				}
			}
			attach_document_id = arrAttachCaseDocument.join("|");
			formValues += "&attach_document_id=" + attach_document_id;
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
									note_list_model.set("case_id", current_case_id);
									//$("#note_listing").parent().parent().parent()
									//$('#kase_content').html(new note_listing_view({collection: data, model: note_list_model}).render().el);
									var content_id = $("#note_listing").parent().parent().parent()[0].id;
									$('#' + content_id).html(new note_listing_view({collection: data, model: note_list_model}).render().el);
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
									note_list_model.set("case_id", current_case_id);
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
								note_list_model.set("case_id", current_case_id);
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

function selectAllParties () {
	$(".parties_option").prop("checked", $("#parties_selectall").prop("checked"));
	
	$(".event_partie").prop("checked", $("#parties_selectall").prop("checked"));
}
function calendarFadeIn() {
	if ($(".calendar_title").length > 0) {
		$(".calendar_title").fadeIn(function(){
			var theleft = (window.innerWidth / 2) + 200;
			$(".calendar_print").css("top", $(".calendar_title").css("top"));
			$("#calendar_filter").css("left", theleft - 520);
			$("#calendar_print").css("left", theleft + 125);
			$("#calendar_list").css("left", theleft + 175);
			
			$("#calendar_attorney_filter").css("left", theleft - 740);
		});
	}
}
function showLoading() {
	$("#kase_mobile").html(loading_image);
	$("#notes_mobile").html(loading_image);
	$("#tasks_mobile").html(loading_image);
	$("#events_mobile").html(loading_image);
}
window.onresize = function(event) {
	calendarFadeIn();
};