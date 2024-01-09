var current_calendar_id;
var current_sort_order;
var current_kase;
var calendar_attorney_filter = "", calendar_list_button = "", calendar_print_button = "", calendar_filter = "";

window.Router = Backbone.Router.extend({
    routes: {
			"activity/:case_id":										"printActivity",
			"activity_case/:case_id":									"printActivityNoFile",
			"activities":												"lastActivity",
			"activity_list/:user_id/:start_date/:end_date":				"listActivities",
			"activity_summary/:start_date/:end_date":					"summaryActivity",
			"clientreport/bymonth":										"listClientsByMonth",
			"clientreport/byref/:client":								"listCasesByClient",
			"courtkalendar":											"listCourtCalendarEvents",
			"employee_calendar/:start/:end":							"listEmployeeEvents",
			"employers":												"listEmployers",
			"event/:occurence_id": 										"printEvent",
			"exams/:case_id":											"listMedIndex",
			"firmkalendar/:type/:start/:end":							"listFirmCalendarEventsByType",
			"ikalendar/:calendar_id/:sort_order/:start/:end":			"listCalendarEvents",
			"ikalview/:start/:end/:view":								"kaseCustomerEventsCalendar",
			"invoices/:case_id/:invoice_id":							"printInvoices",
			"kasereport/bymonth":										"listKasesByMonth",
			"kasereport/emails":										"listEmailKases",
			"kasereport/opens":											"listOpenKases",
			"kasereport/notasks":										"listKasesNoTasks",
			"kasereport/closeds":										"listClosedKases",
			"kasereport/alls":											"listAllKases",
			"kasekalendar/:case_id/:start/:end":						"listKaseEvents",
			"kases/last":												"listLastKases",
			"kasessummary/:user_id":									"listSummaryKases",
			"kases/active":												"listActiveKases",
			"kases/intakes/:type/:status/:letter":						"listIntakes",
			"kases/individual/:atty/:worker":							"listIndividualKasesByWorker",
			"kases/activefilter/:atty/:worker":							"listActiveKasesByWorker",
			"kases/activealphafilter/:atty/:worker/:letter":			"listActiveKasesByWorkerByLetter",
			"kasessummary":												"showKaseSummary",
			"listkalendar/:type/:worker/:start/:end":					"displayAssigneeCalendar",
			"medicalsummary/:case_id":									"listMedicalSummary",
			"message/:message_id": 										"printMessage",
			"messages/day/:day": 										"printDayMessages",
			"messagesout/day/:day": 									"printDayOutMessages",
			"memo/:message_id": 										"printMemo",
			"note/:note_id": 											"printNote",
			"notes/:case_id":											"printNotes",
			"partner_calendar/:start/:end":								"listPartnerEvents",
			"payments/:case_id":										"listPaymentsPrint",
			"receipts/:case_id":										"listReceiptsPrint",
			"referralreport/bymonth":									"listReferralsByMonth",
			"referralreport/byref/:ref":								"listReferralsByRef",
			"settlements/bydoctor/:doctors":							"listSettlementsByDoctor",
			"task/:task_id": 											"printTask",
			"taskinbox":												"printInboxTasks",
			"taskweekinbox/:week/:year":								"printWeekInboxTasks",
			"taskbydates/:start/:end":									"kaseCustomerTasks",
			"taskbydatescompleted/:start/:end":							"kaseCustomerCompletedTasks",
			"taskbydatesout/:start/:end":								"kaseCustomerOutTasks",
			"taskoutbox":												"printOutboxTasks",
			"taskdayinbox/:day":										"printDayInboxTasks",
			"kasetasksbyday/:case_id/:day":								"printDayKaseTasks",
			"kasetasks/:case_id":										"printKaseTasks",
			"kasetaskbydates/:start/:end/:case_id":						"printKaseTasksByDate",
			"taskuserinbox/:user_id/:user_name":						"printUserInboxTasks",
			"taskuserinbox/:user_id/:user_name/:day":					"printUserInboxTasksByDate",
			"taskuserinbox/:user_id/:user_name/:day/:end":				"printUserInboxTasksByDateRange",
			"taskoverdues/:nickname/:user_id/:user_name":				"printTaskOverdueByUser",
			"taskdaycompleted/:day":									"printDayCompletedTasks",
			"taskdaycompletedall/:day":									"printDayCompletedTasksAll",
			"taskdayinboxall/:day":										"printDayInboxTasksAll",
			"taskdayoutbox/:day":										"printDayOutboxTasks",
			"taskoverdue":												"printTaskOverdue",
			"taskfirmoverdue":											"printFirmTaskOverdue",
			"tasksummary":												"printTaskSummary",
			"thread/:thread_id": 										"printThread",
			"users" : 													"listUsers",
			"user/tracksummary/:id":									"listUserTrackSummary",
			"user/tracksummarybydate/:id/:start_date/:end_date":		"listUserTrackSummaryByDate"
	},
    initialize: function () {
		var self = this;
    },
	listKasesByMonth: function() {
		var kasesbymonth = new KaseReportMonthCollection();
		kasesbymonth.fetch({
			success: function(kasesbymonth) {
				$('#content').html(new kases_report({collection: kasesbymonth, model:""}).render().el);
				return;
			}
		}); 
	},
	listEmailKases: function() {
		$('#content').html('<div style="width:770px;margin-left:auto;margin-right:auto; background:#C5FEFD; text-align:center; padding:5px">This report may take a few seconds to generate, because it is listing all  kases with email for applicant.<br>We appreciate your patience.<br><i class="icon-spin4 animate-spin" style="font-size:1em; color:black"></i></div>');
		setTimeout(function() {
			var kaseslast = new KaseEmailCollection();
			kaseslast.fetch({
				success: function(kaseslast) {
					var mymodel = new Backbone.Model;
					mymodel.set("year", "Kases w/Email");
					mymodel.set("monthname", "");
					$('#content').html(new kase_list_report({collection: kaseslast, model:mymodel}).render().el);
					return;
				}
			}); 
		}, 700);
	},
	listOpenKases: function() {
		$('#content').html('<div style="width:770px;margin-left:auto;margin-right:auto; background:#C5FEFD; text-align:center; padding:5px">This report may take a few seconds to generate, because it is listing all open kases.<br>We appreciate your patience.<br><i class="icon-spin4 animate-spin" style="font-size:1em; color:black"></i></div>');
		setTimeout(function() {
			var kaseslast = new KaseOpenCollection();
			kaseslast.fetch({
				success: function(kaseslast) {
					var mymodel = new Backbone.Model;
					mymodel.set("year", "Open Kases");
					mymodel.set("monthname", "");
					
					$('#content').html(new kase_list_report({collection: kaseslast, model:mymodel}).render().el);
					return;
				}
			}); 
		}, 700);
	},
	listClosedKases: function() {
		$('#content').html('<div style="width:770px;margin-left:auto;margin-right:auto; background:#C5FEFD; text-align:center; padding:5px">This report may take a few seconds to generate, because it is listing all open kases.<br>We appreciate your patience.<br><i class="icon-spin4 animate-spin" style="font-size:1em; color:black"></i></div>');
		setTimeout(function() {
			var kaseslast = new KaseClosedCollection();
			kaseslast.fetch({
				success: function(kaseslast) {
					var mymodel = new Backbone.Model;
					mymodel.set("year", "Closed Kases");
					mymodel.set("monthname", "");
					$('#content').html(new kase_list_report({collection: kaseslast, model:mymodel}).render().el);
					return;
				}
			}); 
		}, 700);
	},
	listKasesNoTasks: function() {
		$('#content').html('<div style="width:770px;margin-left:auto;margin-right:auto; background:#C5FEFD; text-align:center; padding:5px">This report may take a few seconds to generate, because it is listing all open kases.<br>We appreciate your patience.<br><i class="icon-spin4 animate-spin" style="font-size:1em; color:black"></i></div>');
		setTimeout(function() {
			var kaseslast = new KaseNoTasksCollection();
			kaseslast.fetch({
				success: function(kaseslast) {
					var mymodel = new Backbone.Model;
					mymodel.set("year", "Kases w/o Tasks");
					mymodel.set("monthname", "");
					$('#content').html(new kase_list_report({collection: kaseslast, model:mymodel}).render().el);
					return;
				}
			}); 
		}, 700);
	},
	listAllKases: function() {
		$('#content').html('<div style="width:770px;margin-left:auto;margin-right:auto; background:#C5FEFD; text-align:center; padding:5px">This report may take a few seconds to generate, because it is listing many kases.<br>We appreciate your patience.<br><i class="icon-spin4 animate-spin" style="font-size:1em; color:black"></i></div>');
		setTimeout(function() {
			var kaseslast = new KaseAllCollection();
			kaseslast.fetch({
				success: function(kaseslast) {
					var mymodel = new Backbone.Model;
					mymodel.set("year", "All Kases");
					mymodel.set("monthname", "");
					$('#content').html(new kase_list_report({collection: kaseslast, model:mymodel}).render().el);
					return;
				}
			}); 
		}, 700);
	},
	listSummaryKases: function(user_id) {
		var kaseslast = new KaseLastCollection();
		kaseslast.fetch({
			success: function(kaseslast) {
				var mymodel = new Backbone.Model;
				var the_employee = worker_searches.findWhere({id: user_id});
				mymodel.set("worker", the_employee.get("nickname"));
				mymodel.set("year", "Summary Kases - " + the_employee.get("user_name"));
				mymodel.set("monthname", "");
				$('#content').html(new kase_list_report({collection: kaseslast, model:mymodel}).render().el);
				return;
			}
		}); 
	},
	listLastKases: function() {
		var kaseslast = new KaseLastCollection();
		kaseslast.fetch({
			success: function(kaseslast) {
				var mymodel = new Backbone.Model;
				mymodel.set("year", "");
				mymodel.set("monthname", "");
				$('#content').html(new kase_list_report({collection: kaseslast, model:mymodel}).render().el);
				return;
			}
		}); 
	},
	listEmployers: function() {
		var employers = new Employers();
		employers.fetch({
			success: function(data) {
				var mymodel = new Backbone.Model;
				$('#content').html(new employers_report({collection: data, model:mymodel}).render().el);
				return;
			}
		}); 
	},
	listMedicalSummary: function(case_id) {
		//first fetch the kase to get the applicant
		
		var medical_billings = new MedicalSummaryCollection({case_id: case_id});
		medical_billings.fetch({
			success: function(data) {
				var kase =  new Kase({case_id: case_id});
				kase.fetch({
					success: function (kase) {
						var my_model = new Backbone.Model;
						my_model.set("holder", "content");
						my_model.set("case_id", case_id);
						my_model.set("partie_id", "");
						my_model.set("embedded", false);
						var file_number = kase.get("file_number");
						if (file_number=="") {
							file_number = kase.get("case_number");
						}
						my_model.set("case_name", file_number + " - " + kase.get("case_name"))	
						
						$('#content').html(new medical_summary_listing_print({collection: data, model: my_model}).render().el);	
					}
				});
			}
		});
	},
	listIntakes: function(type, status, letter) {
		var my_kases = new IntakeCollection({filter: status, type: type, letter: letter});
		my_kases.fetch({
			success: function (data) {
				var mymodel = new Backbone.Model;
				$('#content').html(new intake_listing_report({collection: data, model:mymodel}).render().el);
				return;
			}
		}); 
	},
	listActiveKasesByWorkerByLetter: function(atty, worker, letter) {
		if (atty=="_") {
			atty = "";
		}
		if (worker=="_") {
			worker = "";
		}
		this.listActiveKases(atty, worker, letter);
	},
	listActiveKasesByWorker:function(atty, worker) {
		if (atty=="_") {
			atty = "";
		}
		if (worker=="_") {
			worker = "";
		}
		this.listActiveKases(atty, worker);
	},
	listIndividualKasesByWorker:function(atty, worker) {
		if (atty=="_") {
			atty = "";
		}
		if (worker=="_") {
			worker = "";
		}
		this.listActiveKases(atty, worker, "", true);
	},
	listActiveKases: function(atty, worker, letter, individual_cases) {
		if (typeof letter == "undefined") {
			letter = "";
		}
		if (typeof atty == "undefined") {
			atty = "";
		}
		if (typeof worker == "undefined") {
			worker = "";
		}
		if (typeof individual_cases == "undefined") {
			individual_cases = false;
		}
		if (customer_id==1109) {
			individual_cases = true;
		}
		//var kasesreport = new KaseReportCollection();
		var the_options;
		if (arrSearch.length > 0) {
			if (arrSearch[0].indexOf("Sol Startdate") == 0) {
				the_options = {statute_search: true};
			}
		}
		var kasesreport = new KaseLastCollection(the_options);
		kasesreport.fetch({
			success: function(kasesreport) {
				var mymodel = new Backbone.Model;
				mymodel.set("year", "now");
				mymodel.set("monthname", "now");
				mymodel.set("atty", atty);
				mymodel.set("worker", worker);
				mymodel.set("letter", letter);
				mymodel.set("individual_cases", individual_cases);
				//mymodel.set("new_title", "Client Kase List");
				$('#content').html(new kase_list_report({collection: kasesreport, model:mymodel}).render().el);
				return;
			}
		}); 
	},
	listMedIndex: function(case_id) {
		var exams = new ExamCollection({case_id: case_id});
		exams.fetch({
			success: function(exams) {
				var mymodel = new Backbone.Model;
				
				$('#content').html(new med_index_report({collection: exams, model:mymodel}).render().el);
				return;
			}
		}); 
	},
	listClientsByMonth: function() {
		var clientsbymonth = new ClientReportMonthCollection();
		clientsbymonth.fetch({
			success: function(clientsbymonth) {
				var client_model = new Backbone.Model;
				client_model.set("client", "");
				client_model.set("showall", "");
				$('#content').html(new clients_report({collection: clientsbymonth, model:client_model}).render().el);
				return;
			}
		}); 
	},
	listReferralsByMonth: function() {
		var referralsByMonth = new ReferralReportMonthCollection();
		referralsByMonth.fetch({
			success: function(referralsByMonth) {
				var referring_model = new Backbone.Model;
				referring_model.set("referring", "");
				referring_model.set("showall", "");
				$('#content').html(new referrals_report({collection: referralsByMonth, model:referring_model}).render().el);
				return;
			}
		}); 
	},
	listReferralsByRef: function(ref) {
		var referralsByMonth = new ReferralReportMonthCollection();
		ref = ref.replaceAll("_", " ");
		referralsByMonth.fetch({
			success: function(referralsByMonth) {
				var referralsbyref = referralsByMonth.where({referring: ref});
				referralsByMonth.reset(referralsbyref);
				var referring_model = new Backbone.Model;
				referring_model.set("referring", " - " + ref);
				referring_model.set("showall", "<a href='#referralreport/bymonth' style='text-decoration:underline; cursor:pointer; font-weight:normal; font-size:0.9em'>show all</a>");
				$('#content').html(new referrals_report({collection: referralsByMonth, model:referring_model}).render().el);
				return;
			}
		}); 
	},
	listCasesByClient: function(ref) {
		var referralsByMonth = new ClientReportMonthCollection();
		ref = ref.replaceAll("_", " ");
		referralsByMonth.fetch({
			success: function(referralsByMonth) {
				var referralsbyref = referralsByMonth.where({referring: ref});
				referralsByMonth.reset(referralsbyref);
				var referring_model = new Backbone.Model;
				referring_model.set("client", " - " + ref);
				referring_model.set("showall", "<a href='#clientreport/bymonth' style='text-decoration:underline; cursor:pointer; font-weight:normal; font-size:0.9em'>show all</a>");
				$('#content').html(new clients_report({collection: referralsByMonth, model:referring_model}).render().el);
				return;
			}
		}); 
	},
	listSettlementsByDoctor: function(doctors) {
		var doctorSettlements = new SettlementsByDoctorCollection({doctors: doctors});
		doctorSettlements.fetch({
			success: function(doctorSettlements) {
				var my_model = new Backbone.Model;
				$('#content').html(new settlements_by_doctor_report({collection: doctorSettlements, model:my_model}).render().el);
				return;
			}
		}); 
	},
	listUserTrackSummaryByDate: function (user_id, start_date, end_date) {
		this.listUserTrackSummary(user_id, start_date, end_date);
	},
	listUserTrackSummary: function (user_id, start_date, end_date) {
		if (typeof start_date == "undefined") {
			start_date = "";
			end_date = "";
		}
		var self = this;
		
		//initial call
		var type = "";
		var summs = new LoginSummaries({user_id: user_id, start_date: start_date, end_date: end_date});
		summs.fetch({
			success: function(data) {
				$(document).attr('title', "Employee Usage and Activity Summary :: iKase");
				var my_model = new Backbone.Model;
				my_model.set("holder", "content");
				my_model.set("user_id", user_id);
				my_model.set("start_date", start_date);
				my_model.set("end_date", end_date);
				var the_employee = worker_searches.findWhere({id: user_id});
				my_model.set("employee", the_employee.get("user_name"));
				$('#content').html(new user_login_summary_list({collection: data, model: my_model}).render().el);
			}
		});				
	},
	listUsers: function () {
		if (!blnAdmin) {
			return;
		}
		var self = this;
		
		//initial call
		var type = "";
		var users = new UserAllCollection([]);
		users.fetch({
			success: function(data) {
				$(document).attr('title', "Users :: iKase");
				$('#content').html(new user_listing_print_view({model: data}).render().el);
			}
		});				
	},
	showKaseSummary: function () { 
		//fetch all kases
		var kase_summaries = new KaseSummaries();
		kase_summaries.fetch({
			success: function (data) {
				$(document).attr('title', "Cases Summary :: iKase");
				var kase_listing_info = new Backbone.Model;
				kase_listing_info.set("title", "Cases Summary");
				kase_listing_info.set("holder", "content");
				$('#content').html(new kase_summary_listing({collection: data, model: kase_listing_info}).render().el);
			}
		});			
	},
	listKaseEvents: function(case_id, start, end) {
		this.displayCustomCalendar(case_id, start, end);
	},
	listEmployeeEvents: function(start, end) {
		this.listCalendarEvents("-1", "-98", start, end);
	},
	listPartnerEvents: function(start, end) {
		this.listCalendarEvents("-1", "-99", start, end);
	},
	listCourtCalendarEvents: function() {
		var court_events = new CourtCalendarEvents();
		court_events.fetch({
			success: function (data) {
				//then re-assign to calendar
				var calendar_info = new Backbone.Model;
				var the_title = "Court Calendar Events";				
				var the_container = "#content";
				calendar_info.set("title", the_title);
				calendar_info.set("homepage", false);
				calendar_info.set("event_class", "listing");
				calendar_info.set("worker", "");
				calendar_info.set("thetype", "");
				calendar_info.set("type", " - Court Calendar");
				calendar_info.set("start", "");
				calendar_info.set("end", "");
				
				occurencesView = new event_listing_print({el: $("#content"), collection: court_events, model: calendar_info}).render();
			}
		});
	},
	listFirmCalendarEventsByType: function(calendar_type, start, end) {
		if (calendar_type.indexOf("wc") > -1) {
			calendar_type = "wc";
		}
		var blnCaseType = false;
		if (calendar_type == "wc" || calendar_type=="pi"){
			calendar_type = calendar_type.toUpperCase();
			blnCaseType = true;
		}
		if (calendar_type == "_"){
			calendar_type = "";
		}
		readCookie();
		var self = this;
		if (calendar_type == "") {
			var all_customer_events = new OccurenceCustomerCollection({start: start, end: end, show_all: true});
		} else {
			var arrHash = document.location.hash.split("/");
			var case_type = arrHash[1];
			var start = arrHash[2];
			var end = arrHash[3];
			var all_customer_events = new CustomerByCaseTypeEvents({case_type: case_type, type: "", start: start, end: end});
		}
		all_customer_events.fetch({
			success: function (data) {
				//then re-assign to calendar
				$("#content").html("");
				var calendar_info = new Backbone.Model;
				calendar_info.set("start", moment(start).format("MM/DD/YYYY"));
				calendar_info.set("end", moment(end).format("MM/DD/YYYY"));
				
				if (calendar_type == "WC" || calendar_type=="PI"){
					//go through each event, make sure it's either wc or not
					/*
					var cus_events = all_customer_events.toJSON();
					var arrLength = cus_events.length;
					var type_events = [];
					for(var i = 0; i < arrLength; i++) {
						var cus_event = cus_events[i];
						var kase_type = cus_event.case_type;
						if (kase_type==null) {
							kase_type = "";
						}
						var blnWCAB = isWCAB(kase_type);
						if (calendar_type == "WC" && blnWCAB) {
							type_events.push(cus_event);
						}
						if (calendar_type == "PI" && !blnWCAB) {
							type_events.push(cus_event);
						}
					}
					*/
					//var type_events = all_customer_events.where({case_type : calendar_type});
					var type_events = all_customer_events.toJSON();
				} else {
					if (calendar_type!="") {
						var type_events = all_customer_events.where({event_type : calendar_type});
					} else {
						var type_events = all_customer_events.toJSON();
					}
				}
				var the_collection = new Backbone.Collection(type_events);
				calendar_info.set("calendar_title", calendar_type + " Calendar");
			
				occurencesView = new event_listing_print_new({el: $("#content"), collection: the_collection, model: calendar_info}).render();
			}
		});
	},
	listCalendarEvents: function(calendar_id, sort_order, start, end) {
		current_calendar_id = calendar_id;
		current_sort_order = sort_order;
		//for the most part, mandatory calendars
		switch(sort_order) {
			case "-99":
				this.partnerEvents(start, end);
				break;
			case "-98":
				this.employeeEvents(start, end);
				break;
			case "-2":
				this.kaseCustomerEvents(start, end);
				break;
			case "-1":
				this.kaseCustomerEvents(start, end);
				break;
			case "0":
				this.kaseCustomerEvents(start, end);
				break;
			case "1":
				this.kaseCustomerInhouseEvents(start, end);
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
				//this.displayCustomCalendar(calendar_id, start, end);
		}
	},
	displayCustomCalendar: function (case_id, start, end) {		
		//note the calendar id is really a case id
		occurences = new OccurenceCollection({case_id: case_id, start: start, end: end});
		occurences.fetch({
				success: function(data) {
					//then re-assign to calendar
					var kase = new Backbone.Model;
					kase.set("case_id", case_id);
					kase.set("start", moment(start).format("MM/DD/YYYY"));
					kase.set("end", moment(end).format("MM/DD/YYYY"));
					occurencesView = new event_listing_print({el: $("#content"), collection: occurences, model:kase}).render();
				}
			}
		);
	},
	displayAssigneeCalendar: function (type, worker, start, end) {		
		//note the calendar id is really a case id
		//occurences = new OccurenceCollection({case_id: case_id, start: start, end: end});
		type = type.trim();
		worker = worker.trim();
		if (worker=="_") {
			worker="";
		}
		if (type=="_") {
			type="";
		}
		if (worker!="" && type=="") {
			var occurences = new CustomerByAssigneeEvents({worker: worker, start: start, end: end});
		}
		if (worker=="" && type!="") {
			var occurences = new CustomerByTypeEvents({type: type, start: start, end: end});
		}
		if (worker!="" && type!="") {
			var occurences = new CustomerByTypeByAssigneeEvents({worker: worker, type: type, start: start, end: end});
		} 
		if (worker=="" && type=="") {
			var occurences = new OccurenceCustomerCollection({start: start, end: end});
		}
		occurences.fetch({
				success: function(data) {
					//then re-assign to calendar
					var kase = new Backbone.Model;
					if (worker!="") {
						worker = " for " + worker;
					}
					if (type!="") {
						type = " - " + type.replaceAll("_", " ");
					}
					kase.set("worker", worker);
					kase.set("type", type);
					kase.set("start", moment(start).format("MM/DD/YYYY"));
					kase.set("end", moment(end).format("MM/DD/YYYY"));
					occurencesView = new event_listing_print({el: $("#content"), collection: occurences, model:kase}).render();
				}
			}
		);
	},
	employeeEvents:function(start, end) {
		var current_date = moment().format("MM/DD/YYYY");
		var partner_events = new EmployeeCalendar({start: start, end: end});		
		partner_events.fetch({
			success: function (partner_events) {
				//then re-assign to calendar
				$("#content").html("");
				var calendar_info = new Backbone.Model;
				calendar_info.set("start", moment(start).format("MM/DD/YYYY"));
				calendar_info.set("end", moment(end).format("MM/DD/YYYY"));
				calendar_info.set("calendar_title", "Employee Calendar");
				occurencesView = new event_listing_print({el: $("#content"), collection: partner_events, model: calendar_info}).render();
			}
		});
	},
	partnerEvents:function(start, end) {
		var current_date = moment().format("MM/DD/YYYY");
		var partner_events = new PartnerCalendar({start: start, end: end});		
		partner_events.fetch({
			success: function (partner_events) {
				//then re-assign to calendar
				$("#content").html("");
				var calendar_info = new Backbone.Model;
				calendar_info.set("start", moment(start).format("MM/DD/YYYY"));
				calendar_info.set("end", moment(end).format("MM/DD/YYYY"));
				calendar_info.set("calendar_title", "Partner Calendar");
				occurencesView = new event_listing_print({el: $("#content"), collection: partner_events, model: calendar_info}).render();
			}
		});
	},
	kaseCustomerEvents: function (start, end) {		
		readCookie();
		var self = this;
		var all_customer_events = new OccurenceCustomerCollection({start: start, end: end, show_all: false});
		all_customer_events.fetch({
			success: function (data) {
				//then re-assign to calendar
				$("#content").html("");
				var calendar_info = new Backbone.Model;
				calendar_info.set("start", moment(start).format("MM/DD/YYYY"));
				calendar_info.set("end", moment(end).format("MM/DD/YYYY"));
				
				var occurencesView = new event_listing_print_new({el: $("#content"), collection: all_customer_events, model: calendar_info}).render();
				//var occurencesView = new event_listing_print({el: $("#content"), collection: all_customer_events, model: calendar_info}).render();
			}
		});
		
	},
	kaseCustomerEventsCalendar: function (start, end, view) {		
		readCookie();
		var self = this;
		var all_customer_events = new OccurenceCustomerCollection({start: start, end: end, show_all: false});
		all_customer_events.fetch({
			success: function (data) {
				//then re-assign to calendar
				$("#content").html("");
				var calendar_info = new Backbone.Model;
				calendar_info.set("start", moment(start).format("MM/DD/YYYY"));
				calendar_info.set("end", moment(end).format("MM/DD/YYYY"));
				calendar_info.set("view", view);
				var the_collection = all_customer_events;
				occurencesView = new kase_cus_occurences_view({el: $("#content"), collection: the_collection, model:calendar_info}).render();
			}
		});
		
	},
	kaseCustomerOutTasks: function (start, end) {		
		this.kaseCustomerTasks(start, end, "out");
	},
	kaseCustomerCompletedTasks: function (start, end) {		
		this.kaseCustomerTasks(start, end, "in", true);
	},
	printKaseTasksByDate: function (start, end, case_id) {		
		this.kaseCustomerTasks(start, end, "in", false, case_id);
	},
	kaseCustomerTasks: function (start, end, inout, blnCompleted, case_id) {		
		readCookie();
		if (typeof inout == "undefined") {
			inout = "";
		}
		if (typeof blnCompleted == "undefined") {
			blnCompleted = false;
		}
		if (typeof case_id == "undefined") {
			case_id = -1;
		}
		var box = "Task Inbox :: ";
		if (inout == "out") {
			box = "Task Outbox";
		}
		if (blnCompleted) {
			box += " :: Completed";
		}
		var self = this;
		var all_customer_tasks = new TaskCustomerCollection({start: start, end: end, inout: inout, completed: blnCompleted, case_id: case_id});
		all_customer_tasks.fetch({
			success: function (data) {
				//then re-assign to calendar
				$("#content").html("");
				var task_info = new Backbone.Model;
				if (start!="2000-01-01") {
					task_info.set("start", box + " :: " + moment(start).format("MM/DD/YYYY"));
					task_info.set("end", moment(end).format("MM/DD/YYYY"));
				} else {
					task_info.set("start", "TASKS");
					task_info.set("end", box);
				}
				task_info.set("holder", "content");
				$('#content').html(new task_print_listing({collection: all_customer_tasks, model:task_info}).render().el);
			}
		});
		
	},
	listPaymentsPrint: function (case_id) {		
		readCookie();
		var self = this;
		var kase_checks = new ChecksCollection([], { case_id: case_id, ledger:"OUT" });
		var kase = new Backbone.Model();
		kase.set("case_id", case_id);
		kase.set("title", "Payments");
		kase_checks.fetch({
			success: function(data) {
				payments_print_listing = new payments_print_listing({el: $("#content"), collection: kase_checks, model:kase}).render();
			}
		});
		
	},
	listReceiptsPrint: function (case_id) {		
		readCookie();
		var self = this;
		var kase_checks = new ChecksCollection([], { case_id: case_id, ledger:"IN" });
		var kase = new Backbone.Model();
		kase.set("case_id", case_id);
		kase.set("title", "Receipts");
		kase_checks.fetch({
			success: function(data) {
				payments_print_listing = new payments_print_listing({el: $("#content"), collection: kase_checks, model:kase}).render();
			}
		});
		
	},
	kaseCustomerInhouseEvents: function (start, end) {		
		readCookie();
		var self = this;
		
		var all_inhouse_events = new OccurenceCustomerInhouseCollection({start: start, end: end});
		all_inhouse_events.fetch({
			success: function (data) {
				//then re-assign to calendar
				$("#content").html("");
				var calendar_info = new Backbone.Model;
				calendar_info.set("start", moment(start).format("MM/DD/YYYY"));
				calendar_info.set("end", moment(end).format("MM/DD/YYYY"));
				calendar_info.set("calendar_title", "In-Office Appearances");
				occurencesView = new event_listing_print({el: $("#content"), collection: all_inhouse_events, model: calendar_info}).render();
			}
		});
	},
	kaseCustomerIntakes: function (start, end) {		
		readCookie();
		var self = this;
		
		var all_inhouse_events = new CustomerIntakeCollection({start: start, end: end});
		all_inhouse_events.fetch({
			success: function (data) {
				//then re-assign to calendar
				$("#content").html("");
				var calendar_info = new Backbone.Model;
				calendar_info.set("start", moment(start).format("MM/DD/YYYY"));
				calendar_info.set("end", moment(end).format("MM/DD/YYYY"));
				occurencesView = new event_listing_print({el: $("#content"), collection: all_inhouse_events, model: calendar_info}).render();
			}
		});
	},
	showUserCalendar: function (user_id, start, end) {		
		readCookie();
		var self = this;
		
		var user_events = new UserCalendar({user_id: user_id, start: start, end: end});
		user_events.fetch({
			success: function (data) {
				//then re-assign to calendar
				$("#content").html("");
				var calendar_info = new Backbone.Model;
				calendar_info.set("start", moment(start).format("MM/DD/YYYY"));
				calendar_info.set("end", moment(end).format("MM/DD/YYYY"));
				occurencesView = new event_listing_print({el: $("#content"), collection: user_events, model: calendar_info}).render();
			}
		});
	},
	printNote: function(note_id) {
		var note = new Note({notes_id: note_id});
		note.fetch({
			success: function (data) {
				var kase =  new Kase({case_id: data.get("case_id")});
				kase.fetch({
					success: function (kase) {
						var kase_type = kase.get("case_type");
						var blnWCAB = isWCAB(kase_type);
						var case_name = kase.get("first_name") + " " + kase.get("last_name") + " vs " + kase.get("employer");
						if (!blnWCAB) {
							case_name = kase.get("first_name") + " " + kase.get("last_name") + " vs " + kase.get("defendant");
						}
						data.set("case_name", case_name)
						data.set("report_title", "Note");
						$('#content').html(new note_print_view({model: data}).render().el);					
					}
				});
			}
		});	
	},
	printTask: function(task_id) {
		var task = new Task({task_id: task_id});
		task.fetch({
			success: function (data) {
				case_name = data.get("case_name");
				if (case_name == null) {
					case_name = "";
				}
				data.set("case_name", case_name)
				data.set("title", "TASK");
				$('#content').html(new task_print_view({model: data}).render().el);			
			}
		});	
	},
	printEvent: function(occurence_id) {
		var occurence = new Occurence({occurence_id: occurence_id});
		occurence.fetch({
			success: function (data) {
				case_name = data.get("case_name");
				if (case_name == null) {
					case_name = "";
				}
				data.set("case_name", case_name)
				$('#content').html(new event_print_view({model: data}).render().el);			
			}
		});	
	},
	printDayMessages: function (day) { 
		//fetch all messages
		messages = new MessagesDayCollection({day: day});
		messages.fetch({
			success: function (data) {
				var mymodel = new Backbone.Model();
				mymodel.set("title", customer_name + " :: " + login_username + " Intbox " + moment(day).format("MM/DD/YYYY"));
				$('#content').html(new message_print_listing({collection: data, model: mymodel}).render().el);
			}
		});	
	},
	printDayOutMessages: function (day) { 
		//fetch all messages
		messages = new MessagesDayCollection({day: day, box: "out"});
		messages.fetch({
			success: function (data) {
				var mymodel = new Backbone.Model();
				mymodel.set("title", customer_name + " :: " + login_username + " Outbox " + moment(day).format("MM/DD/YYYY"));
				$('#content').html(new message_print_listing({collection: data, model: mymodel}).render().el);
			}
		});	
	},
	printMessage: function(message_id) {
		var message = new Message({message_id: message_id});
		message.fetch({
			success: function (data) {
				var kase =  new Kase({case_id: data.get("case_id")});
				kase.fetch({
					success: function (kase) {
						data.set("case_name", kase.get("case_number") + " - " + kase.get("name"))	
						$('#content').html(new message_print_view1({model: data}).render().el);				
					}
				});
				return;
			}
		});	
	},
	printThread: function(thread_id) {
		messages = new ThreadMessagesCollection({thread_id: thread_id});
		messages.fetch({
			success: function (data) {
				if (data.length > 0) {
					//get the kase
					var the_messages = data;
					var case_id = data.toJSON()[0].case_id;
					//fetch the case
					var kase =  new Kase({case_id: case_id});
					kase.fetch({
						success: function (kase) {
							$('#content').html(new message_print_listing({collection: the_messages, model:kase}).render().el);
						}
					});
				} else {	
					var kase =  new Kase();			
					$('#content').html(new message_print_listing({collection: data, model:kase}).render().el);
				}
			}
		});	
	},
	printMemo: function(message_id) {
		var message = new Message({message_id: message_id});
		message.fetch({
			success: function (data) {
				case_name = data.get("case_name");
				if (case_name == null) {
					case_name = "";
				}
				data.set("case_name", case_name)
				$('#content').html(new message_print_view({model: data}).render().el);			
			}
		});	
	},
	printInboxTasks: function (case_id) {
		if (typeof case_id == "undefined") {
			case_id = "";
		}
		//fetch all tasks
		tasks = new TaskInboxCollection({case_id: case_id});
		tasks.fetch({
			success: function (data) {
				var task_listing_info = new Backbone.Model;
				task_listing_info.set("title", "Tasks");
				if (case_id != -1 && case_id != "") {
					task_listing_info.set("title", "Kase Tasks List");
				}
				task_listing_info.set("inout", "in");
				task_listing_info.set("receive_label", "Assigned");
				$('#content').html(new task_print_stack_listing({collection: data, model: task_listing_info}).render().el);
			}
		});			
	},
	printActivityNoFile: function (case_id) { 
		this.printActivity(case_id, true);
	},
	printActivity: function (case_id, file_access) { 
		if (typeof file_access == "undefined") {
			file_access = false;
		}
		var self = this;
		if (typeof current_kase == "undefined") {
			current_kase =  new Kase({id: case_id});
			current_kase.fetch({
				success: function (kase) {
					self.printActivity(case_id, file_access);
					return;
				}
			});
			return;
		}
		//fetch all tasks
		var activities = new ActivitiesCollection([], {case_id: case_id, file_access: file_access});
		activities.fetch({
			success: function(data) {
				$(document).attr('title', "Activities for Case ID: " + case_id + " :: iKase");
				
				if (customer_id=="1121") {
					current_kase.set("holder", "content");
					$('#content').html(new activity_print_log({collection: activities, model: current_kase}).render().el);
				} else {
					$('#content').html(new activity_print_listing_view({collection: activities, model: current_kase}).render().el);
				}
			}
		});
	},
	printInvoices: function (case_id, invoice_id) { 
		var self = this;
		if (typeof current_kase == "undefined") {
			current_kase =  new Kase({id: case_id});
			current_kase.fetch({
				success: function (kase) {
					self.printInvoices(case_id, invoice_id);
					return;
				}
			});
			return;
		}
		
		var invoice_activities = new InvoiceCollectionACT([], {invoice_id: invoice_id});
		invoice_activities.fetch({
			success: function(data) {
				current_kase.set("invoice_id", invoice_id);
				//current_kase.set("invoice_id", invoice_id);
				console.log(current_kase.toJSON());
				$(document).attr('title', "Invoice for Case ID: " + case_id + " :: iKase");
				console.log(current_kase.toJSON()["invoice_id"]);
				$('#content').html(new activity_print_listing_view({collection: invoice_activities, model: current_kase}).render().el);
			}
		});
		
	},
	lastActivity: function (case_id) { 
		//fetch all tasks
		var activities = new LastActivities();
		activities.fetch({
			success: function(data) {
				$(document).attr('title', "Activities Report :: iKase");
				var mymodel = new Backbone.Model;
				$('#content').html(new activity_print_listing_view({collection: activities, model: mymodel}).render().el);
			}
		});
	},
	listActivities: function(user_id, start_date, end_date) {
		var activities = new ActivityReportCollection([], {user_id: user_id, start_date: start_date, end_date: end_date});
		activities.fetch({
			success: function(data) {
				$(document).attr('title', "Activities Report :: iKase");
				var mymodel = new Backbone.Model;
				mymodel.set("report", true);
				mymodel.set("user_id", user_id);
				mymodel.set("start_date", start_date);
				mymodel.set("end_date", end_date);
				$('#content').html(new activity_print_listing_view({collection: activities, model: mymodel}).render().el);
			}
		});
	},
	summaryActivity: function(start_date, end_date) {
		var activities = new SummaryActivities({start_date: start_date, end_date: end_date});
		activities.fetch({
			success: function(data) {
				$(document).attr('title', "Activities Report :: iKase");
				var mymodel = new Backbone.Model;
				mymodel.set("start_date", start_date);
				mymodel.set("end_date", end_date);
				$('#content').html(new activity_print_summary_view({collection: activities, model: mymodel}).render().el);
			}
		});
	},
	printNotes: function (case_id) { 
		//fetch all tasks
		var notes = new NoteCollection([], {case_id: case_id});
		notes.fetch({
			success: function(data) {
				$('#content').html(new note_print_listing_view({collection: notes}).render().el);
			}
		});
	},
	printUserInboxTasks: function(user_id, user_name) {
		user_name = user_name.replaceAll("_", " ");
		var tasks = new TaskInboxCollection({ user_id: user_id });
		tasks.fetch({
			success: function (data) {
				var task_listing_info = new Backbone.Model;
				task_listing_info.set("title", user_name + " - Tasks List");
				task_listing_info.set("user_id", user_id);
				task_listing_info.set("inout", "in");
				task_listing_info.set("receive_label", "Assigned");
				
				$('#content').html(new task_print_stack_listing({collection: data, model: task_listing_info}).render().el);
			}
		});
	},
	printUserInboxTasksByDateRange: function(user_id, user_name, day, end_day) {
		user_name = user_name.replaceAll("_", " ");
		var tasks = new TaskInboxCollection({ user_id: user_id, day: day, end_day: end_day });
		tasks.fetch({
			success: function (data) {
				var task_listing_info = new Backbone.Model;
				if (moment(end_day).format("YYYY")!="2200") {
					task_listing_info.set("title", user_name + " - Tasks List (" + moment(day).format("MM/DD/YYYY") + " - " + moment(end_day).format("MM/DD/YYYY") + ")");
				} else {
					task_listing_info.set("title", user_name + " - Tasks List (All)");
				}
				task_listing_info.set("user_id", user_id);
				task_listing_info.set("inout", "in");
				task_listing_info.set("receive_label", "Assigned");
				
				$('#content').html(new task_print_stack_listing({collection: data, model: task_listing_info}).render().el);
			}
		});
	},
	printUserInboxTasksByDate: function(user_id, user_name, day) {
		user_name = user_name.replaceAll("_", " ");
		var tasks = new TaskInboxCollection({ user_id: user_id, day: day });
		tasks.fetch({
			success: function (data) {
				var task_listing_info = new Backbone.Model;
				task_listing_info.set("title", user_name + " - Tasks List (" + moment(day).format("MM/DD/YYYY") + ")");
				task_listing_info.set("user_id", user_id);
				task_listing_info.set("inout", "in");
				task_listing_info.set("receive_label", "Assigned");
				
				$('#content').html(new task_print_stack_listing({collection: data, model: task_listing_info}).render().el);
			}
		});
	},
	printDayCompletedTasks: function (day) { 
		//this.printDayInboxTasks(day, "y");
		var theoptions = {day: day, single_day: "y", all_users: "n"};
		var tasks = new CompletedTasks(theoptions);
		tasks.fetch({
			success: function (data) {
				var task_listing_info = new Backbone.Model;
				task_listing_info.set("title", "Completed Tasks List - Day View&nbsp;|&nbsp;");
				task_listing_info.set("inout", "in");
				task_listing_info.set("receive_label", "Assigned");
				
				$('#content').html(new task_print_stack_listing({collection: data, model: task_listing_info}).render().el);
			}
		});
	},
	printDayCompletedTasksAll: function (day) { 
		//this.printDayInboxTasks(day, "y");
		var theoptions = {day: day, single_day: "y", all_users: "y"};
		var tasks = new CompletedTasks(theoptions);
		tasks.fetch({
			success: function (data) {
				var task_listing_info = new Backbone.Model;
				task_listing_info.set("title", "Completed Tasks List - Day View&nbsp;|&nbsp;" + assignee_filter);
				task_listing_info.set("inout", "in");
				task_listing_info.set("receive_label", "Assigned");
				
				$('#content').html(new task_print_stack_listing({collection: data, model: task_listing_info}).render().el);
			}
		});
	},
	printKaseTasks: function (case_id) { 
		this.printInboxTasks(case_id);
	},
	printDayKaseTasks: function (case_id, day) { 
		this.printDayInboxTasks(day, "y", case_id);
	},
	printDayInboxTasksAll: function (day) { 
		this.printDayInboxTasks(day, "y");
	},
	printDayInboxTasks: function (day, all_users, case_id) {
		if (typeof all_users == "undefined") {
			all_users = "";
		}
		if (typeof case_id == "undefined") {
			case_id = -1;
		}
		//fetch all tasks
		tasks = new TaskInboxCollection({day: day, single_day: "y", all_users: all_users, case_id: case_id});
		tasks.fetch({
			success: function (data) {
				var day_string = moment(day).format("MM/DD/YYYY");
				var task_listing_info = new Backbone.Model;
				if (all_users=="y") {
					task_listing_info.set("title", "Tasks List - " + day_string + "&nbsp;|&nbsp;" + assignee_filter);
				} else {
					task_listing_info.set("title", "Tasks List - " + day_string);
				}
				
				if (case_id != -1 && case_id != "") {
					task_listing_info.set("title", "Kase Tasks List - " + day_string);
				}
				task_listing_info.set("inout", "in");
				task_listing_info.set("receive_label", "Assigned");
				
				$('#content').html(new task_print_stack_listing({collection: data, model: task_listing_info}).render().el);
				//$('#content').html(new task_print_listing({collection: data, model: task_listing_info}).render().el);
			}
		});			
	},
	printTaskOverdueByUser: function(nickname, user_id, user_name) {
		user_name = user_name.replaceAll("_", " ");
		
		var tasks = new TaskInboxCollection({ nickname: nickname });	//nickname means that we're getting the user's overdues, for now
		tasks.fetch({
			success: function (data) {
				var task_listing_info = new Backbone.Model;
				task_listing_info.set("title", user_name + " - Overdue Tasks List");
				
				task_listing_info.set("user_id", user_id);
				task_listing_info.set("inout", "in");
				task_listing_info.set("receive_label", "Assigned");
				
				$('#content').html(new task_print_stack_listing({collection: data, model: task_listing_info}).render().el);
			}
		});
	},
	printTaskOverdue: function() {
		//fetch all tasks
		var tasks = new TaskOverdueCollection();
		tasks.fetch({
			success: function (data) {
				var task_listing_info = new Backbone.Model;
				task_listing_info.set("title", "Overdue Tasks");
				task_listing_info.set("inout", "in");
				task_listing_info.set("receive_label", "Assigned");
				task_listing_info.set("user_id", login_user_id);
				$('#content').html(new task_print_stack_listing({collection: data, model: task_listing_info}).render().el);
				//$('#content').html(new task_print_listing({collection: data, model: task_listing_info}).render().el);
			}
		});	
	},
	printFirmTaskOverdue: function() {
		//fetch all tasks
		var tasks = new TaskOverdueCollection({blnFirm: true});
		tasks.fetch({
			success: function (data) {
				data.comparator = "assignee";
				data.sort();
				
				var task_listing_info = new Backbone.Model;
				task_listing_info.set("title", "Overdue Tasks List");
				task_listing_info.set("inout", "in");
				task_listing_info.set("receive_label", "Assigned");
				$('#content').html(new task_print_stack_listing({collection: data, model: task_listing_info}).render().el);
				//$('#content').html(new task_print_listing({collection: data, model: task_listing_info}).render().el);
			}
		});	
	},
	printWeekInboxTasks: function (week, year) { 
		//fetch all tasks
		var tasks = new TaskInboxCollection({week: week, year: year});
		tasks.fetch({
			success: function (data) {
				
				var simple = new Date(year, 0, 1 + (week - 1) * 7);
				var dow = simple.getDay();
				var ISOweekStart = simple;
				
				if (dow <= 4){
					ISOweekStart.setDate(simple.getDate() - simple.getDay() + 1);
				} else {
					ISOweekStart.setDate(simple.getDate() + 8 - simple.getDay());
				}
				
				var formatted_start_date = moment(ISOweekStart).format("MM/DD/YYYY");
				simple = new Date(year, 0, 1 + (week - 1) * 7);
				var end_week_date = simple.getDate() + 1;
				//alert(end_week_date);
				var formatted_end_date = moment(simple).format("MM/DD/YYYY");
				alert(formatted_start_date);
				alert(formatted_end_date);
				//return;
				
				var task_listing_info = new Backbone.Model;
				task_listing_info.set("title", "TASKS LIST - Week View");
				task_listing_info.set("inout", "in");
				task_listing_info.set("receive_label", "Assigned");
				$('#content').html(new task_print_stack_listing({collection: data, model: task_listing_info}).render().el);
			}
		});			
	},
	printOutboxTasks: function () { 
		//fetch all tasks
		tasks = new TaskOutboxCollection();
		tasks.fetch({
			success: function (data) {
				var task_listing_info = new Backbone.Model;
				task_listing_info.set("title", "Task Outbox");
				task_listing_info.set("inout", "out");
				task_listing_info.set("receive_label", "Due");
				$('#content').html(new task_print_stack_listing({collection: data, model: task_listing_info}).render().el);
			}
		});			
	},
	printDayOutboxTasks: function (day) { 
		//fetch all tasks
		tasks = new TaskOutboxCollection({day: day});
		tasks.fetch({
			success: function (data) {
				var task_listing_info = new Backbone.Model;
				task_listing_info.set("title", "Task Outbox - " + moment(day).format("MM/DD/YYYY"));
				task_listing_info.set("inout", "out");
				task_listing_info.set("receive_label", "Due");
				task_listing_info.set("user_id", login_user_id);
				$('#content').html(new task_print_stack_listing({collection: data, model: task_listing_info}).render().el);
			}
		});			
	},
	printTaskSummary: function() {
		var task_summaries = new TaskSummaries();
		task_summaries.fetch({
			success: function (data) {
				$(document).attr('title', "Tasks Summary :: iKase");
				var task_listing_info = new Backbone.Model;
				task_listing_info.set("title", "Tasks Summary");
				task_listing_info.set("holder", "content");
				$('#content').html(new task_summary_listing_print({collection: data, model: task_listing_info}).render().el);
				$("#content").removeClass("glass_header_no_padding");
			}
		});	
	}
});

//load templates
//get rid of interoffice view
//"event_print_view", 
templateLoader.load(["clients_report", "note_print_view", "task_print_view", "task_print_listing", "task_print_stack_listing", "message_print_view", "message_print_view1", "message_print_listing", "kase_occurences_print_view", "event_listing", "event_listing_print", "kases_report", "kases_report", "referrals_report", "kase_list_report", "kase_list_applicant_report", "payments_print_listing", "activity_print_listing_view", "activity_print_summary_view", "note_print_listing_view", "med_index_report, invoice_print_listing_view", "user_listing_print_view"],
    function () {
        app = new Router();
        Backbone.history.start();
	}
);