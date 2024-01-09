window.Router = Backbone.Router.extend({
    routes: {
		"event/:occurence_id": 				"printEvent",
		"ikalendar/:calendar_id":		"listCalendarEvents",
		"task/:task_id": 					"printTask",
		"message/:message_id": 		"printMessage",
		"memo/:message_id": 		"printMemo",
		"taskinbox":			"printInboxTasks",
		"taskdayinbox/:day":	"printDayInboxTasks",
		"taskoutbox":			"printOutboxTasks",
		"taskdayoutbox/:day":	"printDayOutboxTasks"
	},
    initialize: function () {
		var self = this;
    },
	listCalendarEvents: function(calendar_id, sort_order) {
		current_calendar_id = calendar_id;
		//for the most part, mandatory calendars
		switch(sort_order) {
			case "0":
				this.kaseCustomerEvents();
				break;
			case "4":
				this.kaseCustomerIntakes();
				break;
			case "5":
				//this.kasePersonalEvents();
				this.showUserCalendar(login_user_id);
				break;
			default:
				this.displayCustomCalendar(calendar_id);
		}
	},
	kaseCustomerEvents: function () {		
		executeMainChanges();
		readCookie();
		var self = this;
		var all_customer_events = new OccurenceCustomerCollection();
		all_customer_events.fetch({
			success: function (data) {
				//then re-assign to calendar
				$("#content").html("");
				occurencesView = new kase_occurences_print_view({el: $("#content"), collection: all_customer_events, model:data}).render();
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
	printMessage: function(message_id) {
		var message = new Message({message_id: message_id});
		message.fetch({
			success: function (data) {
				case_name = data.get("case_name");
				if (case_name == null) {
					case_name = "";
				}
				data.set("case_name", case_name)
				$('#content').html(new message_print_view1({model: data}).render().el);			
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
	printInboxTasks: function () { 
		//fetch all messages
		tasks = new TaskInboxCollection();
		tasks.fetch({
			success: function (data) {
				var task_listing_info = new Backbone.Model;
				task_listing_info.set("title", "Task Inbox");
				task_listing_info.set("receive_label", "Due Date");
				$('#content').html(new task_print_listing({collection: data, model: task_listing_info}).render().el);
			}
		});			
	},
	printDayInboxTasks: function (day) { 
		//fetch all messages
		tasks = new TaskInboxCollection({day: day});
		tasks.fetch({
			success: function (data) {
				var task_listing_info = new Backbone.Model;
				task_listing_info.set("title", "Task Inbox");
				task_listing_info.set("receive_label", "Due Date");
				$('#content').html(new task_print_listing({collection: data, model: task_listing_info}).render().el);
			}
		});			
	},
	printOutboxTasks: function () { 
		//fetch all messages
		tasks = new TaskOutboxCollection();
		tasks.fetch({
			success: function (data) {
				var task_listing_info = new Backbone.Model;
				task_listing_info.set("title", "Task Outbox");
				task_listing_info.set("receive_label", "Due");
				$('#content').html(new task_print_listing({collection: data, model: task_listing_info}).render().el);
			}
		});			
	},
	printDayOutboxTasks: function (day) { 
		//fetch all messages
		tasks = new TaskOutboxCollection({day: day});
		tasks.fetch({
			success: function (data) {
				var task_listing_info = new Backbone.Model;
				task_listing_info.set("title", "Task Outbox");
				task_listing_info.set("receive_label", "Due");
				$('#content').html(new task_print_listing({collection: data, model: task_listing_info}).render().el);
			}
		});			
	}
});

//load templates
//get rid of interoffice view
templateLoader.load(["event_print_view", "task_print_view", "task_print_listing", "message_print_view", "message_print_view1", "kase_occurances_print_view"],
    function () {
        app = new Router();
        Backbone.history.start();
	}
);