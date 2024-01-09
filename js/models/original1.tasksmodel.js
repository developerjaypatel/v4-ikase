window.Task = Backbone.Model.extend({
	initialize: function(options) {
		this.id = options.task_id;
		this.case_id = options.case_id;
	},
	defaults : {
		"id" : -1,
		"deleted":"N",
		"glass":"",
		"task_type": "",	//this is actually the Status
		"type_of_task": "standard",	//this is actually the type of task, useful for workflows
		"task_description": "",
		"case_id": "",
		"task_priority": "normal",
		"full_address": "",
		"assignee": "",
		"cc":"",
		"task_name": "",
		"task_title": "",
		"task_dateandtime": "",
		"end_date": "",
		"callback_date": "",
		"doi_id":"",
		"doi_start":"",
		"doi_end":"",
		"gridster_me": false
	},
  	urlRoot: function() {
		return 'api/tasks/';
	}
});
window.TaskCollection = Backbone.Collection.extend({
	initialize: function(options) {
		
	 },
	model: Task,
	url: function() {
		return 'api/tasks';
	  },
});
window.TaskInboxCollection = Backbone.Collection.extend({
	initialize: function(options) {
		//default
		this.day = "";
		this.end_day = "";
		this.single_day = "";
		this.week = "";
		this.year = "";
		this.case_id = "";
		this.all_users = "";
		this.user_id = "";
		this.nickname = "";
		this.blnClosed = false;
		this.blnDeleted = false;
		
		if (typeof options != "undefined") {
			if (typeof options.day!="undefined") {
				this.day = options.day;
			}
			if (typeof options.end_day!="undefined") {
				this.end_day = options.end_day;
			}
			if (typeof options.user_id!="undefined") {
				this.user_id = options.user_id;
			}
			if (typeof options.nickname!="undefined") {
				this.nickname = options.nickname;
			}
			if (typeof options.single_day!="undefined") {
				this.single_day = options.single_day;
			}
			if (typeof options.week!="undefined") {
				this.week = options.week;
			}
			if (typeof options.year!="undefined") {
				this.year = options.year;
			}
			if (typeof options.case_id!="undefined") {
				this.case_id = options.case_id;
			}
			if (typeof options.all_users!="undefined") {
				this.all_users = options.all_users;
			}
			if (typeof options.blnClosed!="undefined") {
				this.blnClosed = options.blnClosed;
			}
			if (typeof options.blnDeleted!="undefined") {
				this.blnDeleted = options.blnDeleted;
			}
		}
	 },
	model: Task,
	url: function() {
		var theurl = 'api/taskinbox';
		var blnURL = false;
		if (this.case_id != "" && this.case_id != -1) {
			if (this.day=="") {
				theurl = 'api/taskcaseinbox/' + this.case_id;
				if (this.blnClosed) {
					theurl = 'api/taskcaseinboxclosed/' + this.case_id;
				}
				if (this.blnDeleted) {
					theurl = 'api/taskcaseinboxdeleted/' + this.case_id;
				}
			} else {
				theurl = 'api/taskcasedayinbox/' + this.case_id + '/' + this.day;
			}
			blnURL = true;
		}
		if (!blnURL) {
			if (this.nickname!="") {
				//might have to get more sophisticated eventually, but that's all we need right now
				theurl = "api/overdueusertasks/" + this.nickname;
				blnURL = true;
			}
		}
		if (!blnURL) {
			if (this.user_id != "") {
				theurl = 'api/tasksuser/' + this.user_id;
				if (this.day != "") {
					theurl += "/" + this.day;
				}
				if (this.end_day!="" && this.end_day!=this.day) {
					theurl += '/' + this.end_day;
				}
				blnURL = true;
			}
		}
		if (!blnURL) {
			if (this.day != "" && (this.case_id =="" || this.case_id==-1)) {
				theurl = 'api/taskdayinbox/' + this.day;
				if (this.single_day != "") {
					theurl = 'api/tasksinledayinbox/' + this.day;
				}
				if (this.all_users == "y") {
					theurl = 'api/tasksingledayinboxall/' + this.day;
				}
			}
			if (this.week != "" && this.year != "") {
				theurl = 'api/taskweekinbox/' + this.week + '/' + this.year;
			}
		}
		return theurl;
	  },
});
window.NewTasks = Backbone.Collection.extend({
	initialize: function(options) {
		
	 },
	model: Task,
	url: function() {
		return 'api/taskinboxnew';
	  },
});
window.CompletedTasks = Backbone.Collection.extend({
	initialize: function(options) {
		this.day = "";
		this.all_users = "";
		if (typeof options != "undefined") {
			if (typeof options.day!="undefined") {
				this.day = options.day;
			}
			if (typeof options.all_users!="undefined") {
				this.all_users = options.all_users;
			}
		}
	 },
	model: Task,
	url: function() {
		if (this.day == "" && this.all_users != "y") {
			return 'api/taskcompleted';
		} else {
			return 'api/tasksingledaycompletedall/' + this.day;
		}
	  },
});
window.TaskOutboxCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.day = "";
		this.case_id = "";
		if (typeof options != "undefined") {
			this.day = options.day;
			if (typeof options.case_id!="undefined") {
				this.case_id = options.case_id;
			}
		}
	 },
	model: Task,
	url: function() {
		var theurl = 'api/taskoutbox';
		if (this.case_id != "") {
			theurl = 'api/taskcaseoutbox/' + this.case_id;
		} else {			
			if (this.day != "") {
				theurl = 'api/taskdayoutbox/' + this.day;
			}
		}
		
		return theurl;
	  },
});
window.TaskRecentCollection = Backbone.Collection.extend({
    model: Task,
    url:"api/tasks/recent",
	initialize:function () {
		//the kases returned will be for current customer only, see pak
	}
});
window.TaskOverdueCollection = Backbone.Collection.extend({
    model: Task,
	initialize:function (options) {
		//the kases returned will be for current customer only, see pak
		this.blnFirm = false;
		if (typeof options != "undefined") {
			if (typeof options.blnFirm != "undefined") {
				this.blnFirm = options.blnFirm;
			}
		}
	},
    url: function() {
		var api = "api/overduetasks";
		if (this.blnFirm) {
			api = "api/overduefirmtasks";
		}
		return api;
	}
});
window.TaskFirmOverdueCollection = Backbone.Collection.extend({
    model: Task,
    url:"api/overduefirmtasks",
	initialize:function () {
		//the kases returned will be for current customer only, see pak
	}
});
window.TaskCustomerCollection = Backbone.Collection.extend({
	initialize: function(options) {		
		this.inout = "";
		this.completed = false;
		this.case_id = -1;
	 	if (typeof options != "undefined") {
			this.start = options.start;
			this.end = options.end;
		}
		if (typeof options.inout != "undefined") {
			this.inout = options.inout;
		}
		if (typeof options.completed != "undefined") {
			this.completed = options.completed;
		}
		if (typeof options.case_id != "undefined") {
			this.case_id = options.case_id;
		}
	},
	model: Task,
	url: function() {
		var api = 'api/tasksbydates/' + this.start + '/' + this.end;
		if (this.inout=="out") {
			api = 'api/taskoutboxbydate/' + this.start + '/' + this.end;
		}
		if (this.completed) {
			api = 'api/tasksbydatescompleted/' + this.start + '/' + this.end;
		}
		
		if (this.case_id !="") {
			api = api.replace("api/", "api/kase");
			api += "/" + this.case_id;
		}
		return api;
	}
});
window.TaskTrack = Backbone.Model.extend({
	initialize: function() {

	}
});
window.TaskTracks = Backbone.Collection.extend({
	initialize: function(options) {
		this.task_id = options.task_id
	 },
	model: TaskTrack,
	url: function() {
		return 'api/taskhistory/' + this.task_id;
	  },
});
window.TaskSummary = Backbone.Model.extend({
	initialize: function() {

	},
	defaults : {
		"user_id":"",
		"user_name":"",
		"nickname":"",
		"task_count":0,
		"overdues":0,
		"oldest_task":"",
		"newest_task":""
	}
});
window.TaskSummaries = Backbone.Collection.extend({
	initialize: function(options) {
		
	 },
	model: TaskSummary,
	url: function() {
		return 'api/tasksummary';
	  },
});
var arrDeletedTaskType = [];
window.TaskTypesCollection = Backbone.Collection.extend({
	initialize: function(models, options) {
	},
	url: function() {
		var api = "api/task_types";
		return api;
	},
	model:CostCategory
});