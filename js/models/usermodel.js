window.User = Backbone.Model.extend({
	url:function() {
		if (this.nickname!="") {
			return "api/fetchnickname/" + this.nickname;
		}
		return "api/user/" + this.id;
	},
	initialize: function(options) {
		this.nickname = "";
		if (typeof options != "undefined") {
			this.id = options.user_id;
			if (typeof options.nickname != "undefined") {
				this.nickname = options.nickname;
			}
		}
		
	  },
	defaults : {
		"id" : -1,
		"uuid": "",
		"user_id": "",
		"user_name": "",
		"user_logon": "",
		"user_first_name": "",
		"user_last_name": "",
		"nickname": "",
		"job": "",
		"role": "",
		"personal_calendar": "",
		"cis_id":"",
		"calendar_color": "",
		"job_uuid": "",
		"user_type": "",
		"user_email": "",
		"user_cell": "",
		"rate": "",
		"tax": "",
		"activated": "",
		"adhoc":"",
		"gridster_me": false
	},
	name: function () {
        return this.get("user_first_name") + " " + this.get("user_last_name");
    },
	caseCount: function(job) {
		var self = this;
		
		var url = "api/kases/getall/" + this.id + "/" + job;
		
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					if (data.success) {
						if (data.case_count > 0) {
							$("#delete_" + job + "_cases").show();
							$("#delete_" + job + "_cases").append("&nbsp;(" + data.case_count + ")");
							$("#delete_cases_holder").fadeIn();
							$("#delete_cases_holder").css("left", "670px");
						} else {
							$("#delete_" + job + "_cases").hide();
						}
					}
				}
			}
		});
	},
	deleteAllKases: function(job) {
		var self = this;
		if (!blnAdmin) {
			return;
		}
		var url = "api/kases/clearall";
		var formValues = "user_id=" + this.id + "&job=" + job;
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#delete_" + job + "_cases_feedback").html("ALL Kases were deleted for " + self.name() + "&nbsp;&#10003;");
					$("#delete_" + job + "_cases_feedback").css("{background: lime, color:black, padding: 2px");
					
					setTimeout(function() {
						$("#delete_" + job + "_cases_feedback").fadeOut();
					}, 2500);
					
					self.caseCount(job);
				}
			}
		});
	}
});
window.UserCollection = Backbone.Collection.extend({
    initialize: function(options) {
		//this.case_id = options.case_id;
	 },
	model: User,
    url:"api/user"
});
window.CurrentUserCollection = Backbone.Collection.extend({
    initialize: function(options) {
		//this.case_id = options.case_id;
	 },
	model: User,
    url:"api/currentusers"
});
window.UserAllCollection = Backbone.Collection.extend({
    initialize: function(options) {
		//this.case_id = options.case_id;
	 },
	model: User,
    url:"api/users"
});
window.EventUsers = Backbone.Collection.extend({
    initialize: function(model, options) {
		this.event_id = options.event_id;
		this.type = options.type;
	 },
	model: User,
    url: function() {
		return "api/user/events/" + this.event_id + "/" + this.type;
	}
});
window.MessageUsers = Backbone.Collection.extend({
    initialize: function(model, options) {
		this.message_id = options.message_id;
		this.type = options.type;
	 },
	model: User,
    url: function() {
		return "api/user/messages/" + this.message_id + "/" + this.type;
	}
});
window.TaskUsers = Backbone.Collection.extend({
    initialize: function(model, options) {
		this.task_id = options.task_id;
		this.type = options.type;
	 },
	model: User,
    url: function() {
		return "api/user/tasks/" + this.task_id + "/" + this.type;
	}
});
window.LoginSummary = Backbone.Model.extend({
	url:function() {
		return "";
	},
	initialize: function(options) {
		
	  },
	defaults : {
		"id" : -1,
		"user_name": "",
		"login_date": "",
		"dayw": "",	//day of week
		"dow": "",	//day of week numerical
		"logout": "",
		"last_track": "",
		"estimated_logout": "",
		"last_view": "",
		"spent_time": "",
		"case_count":"",
		"activity_count": ""
	}
});
window.LoginSummaries = Backbone.Collection.extend({
    initialize: function(options) {
		this.user_id = options.user_id;
		this.start_date = "";
		this.end_date = "";
		if (typeof options.start_date != "undefined") {
			this.start_date = options.start_date;
			this.end_date = options.end_date;
		}	
	 },
	model: LoginSummary,
    url: function() {
		if (this.start_date == "") {
			return "api/user/tracksummary/" + this.user_id;
		} else {
			return "api/user/tracksummarybydate/" + this.user_id + "/" + this.start_date + "/" + this.end_date;
		}
	}
});