//events_pack.php
window.Occurence = Backbone.Model.extend({

    urlRoot:"api/events",

    initialize:function (options) {
        this.id = options.event_id;
    },
	defaults : {
		"id" : -1,
		"uuid": "",
		"case_uuid": "",
		"event_name": "",
		"event_date": "",
		"case_name": "",
		"case_stored_name": "",
		"event_duration": "60",
		"event_description": "",
		"event_first_name": "",
		"event_last_name": "",
		"event_dateandtime": "",
		"event_end_time": "",
		"full_address":"",
		"event_title": "",
		"assignee": "",
		"event_email": "",
		"event_hour": "",
		"glass": "",
		"event_type": "",
		"event_kind": "",
		"event_from": "",
		"event_priority": "",
		"end_date": "",
		"completed_date": "",
		"callback_date": "",
		"callback_completed": "",
		"color": "",
		"off_calendar":"N",
		"reminder_id1": "-1",
		"reminder_type1": "",
		"reminder_interval1": "",
		"reminder_span1": "",
		"reminder_id2": "-1",
		"reminder_type2": "",
		"reminder_interval2": "",
		"reminder_span2": "",
		"glass":"card_dark_4",
		"allDay":false,
		"grid_it":false,
		"gridster_me":false
	},
	label: function () {
        return this.get("event_first_name") + " " + this.get("event_last_name");
    }
});

window.OccurenceCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.case_id = options.case_id;
		if (typeof options.start != "undefined") {
			if (options.start!="") {
				this.start = options.start;
				this.end = options.end;
			}
		}
	 },
	model: Occurence,
	url: function() {
		if (typeof this.start == "undefined") {
			return 'api/kase/events/' + this.case_id;
		} else {
			return 'api/kase/events/dates/' + this.case_id + '/' + this.start + '/' + this.end;
		}
	}
});
window.OccurenceCustomerCollection = Backbone.Collection.extend({
	initialize: function(options) {		
		this.show_all = false;
	 	if (typeof options != "undefined") {
			this.start = options.start;
			this.end = options.end;
			if (typeof options.show_all != "undefined") {
				this.show_all = options.show_all;
			}
		}
	},
	model: Occurence,
	url: function() {
		if (typeof this.start == "undefined") {
			return 'api/customer/events';
		} else {
			if (this.show_all) {
				return 'api/customer/events/alldates/' + this.start + '/' + this.end;
			} else {
				return 'api/customer/events/dates/' + this.start + '/' + this.end;
			}
		}
	}
});
window.OccurenceStoredCustomerCollection = Backbone.Collection.extend({
	localStorage: new Backbone.LocalStorage("cus-events-backbone"),
	initialize: function(options) {		
	 	if (typeof options != "undefined") {
			this.start = options.start;
			this.end = options.end;
		}
	},
	model: Occurence,
	url: function() {
		if (typeof this.start == "undefined") {
			return 'api/customer/events';
		} else {
			return 'api/customer/events/dates/' + this.start + '/' + this.end;
		}
	}
});
window.OccurenceCustomerInhouseCollection = Backbone.Collection.extend({
	initialize: function(options) {
		if (typeof options != "undefined") {
			this.start = options.start;
			this.end = options.end;
		}
	 },
	model: Occurence,
	url: function() {
		if (typeof this.start == "undefined") {
			return 'api/customer/inhouse';
		} else {
			return 'api/customer/inhouse/dates/' + this.start + '/' + this.end;
		}
	}
});
window.CustomerIntakeCollection = Backbone.Collection.extend({
	initialize: function(options) {
		if (typeof options != "undefined") {
			this.start = options.start;
			this.end = options.end;
		}
	 },
	model: Occurence,
	url: function() {
		if (typeof this.start == "undefined") {
			return 'api/customer/intakes';
		} else {
			return 'api/customer/intakes/dates/' + this.start + '/' + this.end;
		}
	}
});
window.CustomerByAssigneeEvents = Backbone.Collection.extend({
	initialize: function(options) {
		this.worker = options.worker;
		this.start = "";
		this.end = "";
		if (typeof options.start != "undefined") {
			this.start = options.start;
			this.end = options.end;
		}
	 },
	model: Occurence,
	url: function() {
		if (this.start == "") {
			return 'api/customer/events/assignee/' + this.worker;
		} else {
			return 'api/customer/events/assigneebydate/' + this.worker + '/' + this.start + '/' + this.end;
		}
	}
});
window.CustomerByTypeByAssigneeEvents = Backbone.Collection.extend({
	initialize: function(options) {
		this.worker = options.worker;
		this.type = options.type;
		this.start = "";
		this.end = "";
		if (typeof options.start != "undefined") {
			this.start = options.start;
			this.end = options.end;
		}
		//no empties
		if (this.type == " " || this.type == "") {
			this.type = "_";
		}
		if (this.worker == " " || this.worker == "") {
			this.worker = "_";
		}
	 },
	model: Occurence,
	url: function() {
		if (this.start == "") {
			return 'api/customer/events/assigneebytype/' + this.type.replaceAll(" ", "_") + '/' + this.worker;
		} else {
			return 'api/customer/events/assigneebytypebydate/' + this.type.replaceAll(" ", "_") + '/' + this.worker + '/' + this.start + '/' + this.end;
		}
	}
});

window.CustomerByWorkerEvents = Backbone.Collection.extend({
	initialize: function(options) {
		this.worker = options.worker;
	 },
	model: Occurence,
	url: function() {
		return 'api/customer/events/worker/' + this.worker;
	}
});
window.CustomerByAttorneyEvents = Backbone.Collection.extend({
	initialize: function(options) {
		this.attorney = options.attorney;
	 },
	model: Occurence,
	url: function() {
		return 'api/customer/events/attorney/' + this.attorney;
	}
});
window.CustomerByTypeEvents = Backbone.Collection.extend({
	initialize: function(options) {
		this.type = options.type;
		this.start = "";
		this.end = "";
		if (typeof options.start != "undefined") {
			this.start = options.start;
			this.end = options.end;
		}
	 },
	model: Occurence,
	url: function() {
		if (this.start == "") {
			return 'api/customer/events/type/' + this.type.replaceAll(" ", "_");
		} else {
			return 'api/customer/events/typebydate/' + this.type.replaceAll(" ", "_") + '/' + this.start + '/' + this.end;
		}
	}
});
window.CustomerByCaseTypeEvents = Backbone.Collection.extend({
	initialize: function(options) {
		this.case_type = options.case_type;
		this.type = "_";
		this.start = "_";
		this.end = "_";
		if (typeof options.type != "undefined") {
			this.type = options.type;
			this.type = this.type.replaceAll(" ", "_");
		}
		if (typeof options.start != "undefined") {
			this.start = options.start;
			this.end = options.end;
		}
	 },
	model: Occurence,
	url: function() {
		if (this.type == "") {
			this.type = "_";
		}
		if (this.start == "") {
			this.start = "_";
		}
		if (this.end == "") {
			this.end = "_";
		}
		return 'api/customer/events/casetype/' + this.case_type + '/' + this.type + '/' + this.start + '/' + this.end;
	}
});

window.OccurencePersonalCollection = Backbone.Collection.extend({
	initialize: function() {
	 },
	model: Occurence,
	url: 'api/personal/events'
});

window.UserCalendar = Backbone.Collection.extend({
	initialize: function(options) {
		this.user_id = options.user_id;
		if (typeof options != "undefined") {
			if (typeof options.start != "undefined") {
				this.start = options.start;			
				this.end = options.end;
			}
		}
	 },
	model: Occurence,
	url: function() {
		if (typeof this.start == "undefined") {
			this.start = "";
			this.end = "";
		}
		if (this.start == "") {
			return 'api/events/userkalendar/' + this.user_id;
		} else {
			return 'api/events/userkalendar/dates/' + this.user_id + '/' + this.start + '/' + this.end;
		}
	}
});
window.EmployeeCalendar = Backbone.Collection.extend({
	initialize: function(options) {
		if (typeof options != "undefined") {
			if (typeof options.start != "undefined") {
				this.start = options.start;
				this.end = options.end;
			}
		}
	 },
	model: Occurence,
	url: function() {
		if (typeof this.start == "undefined") {
			return 'api/events/employeekalendar';
		} else {
			return 'api/events/employeekalendar/dates/' + this.start + '/' + this.end;
		}
	}
});
window.PartnerCalendar = Backbone.Collection.extend({
	initialize: function(options) {
		if (typeof options != "undefined") {
			if (typeof options.start != "undefined") {
				this.start = options.start;
				this.end = options.end;
			}
		}
	 },
	model: Occurence,
	url: function() {
		if (typeof this.start == "undefined") {
			return 'api/events/partnerkalendar';
		} else {
			return 'api/events/partnerkalendar/dates/' + this.start + '/' + this.end;
		}
	}
});

window.OccurenceCalendarCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.calendar_id = options.calendar_id;
	 },
	model: Occurence,
	url: function() {
		return 'api/events/ikalendar/' + this.calendar_id;
	}
});

window.RecentOccurenceCollection = Backbone.Collection.extend({
	initialize: function(options) {
	 },
	model: Occurence,
	url: 'api/events/recent'
});
window.UpcomingEvents = Backbone.Collection.extend({
	initialize: function(options) {
	 },
	model: Occurence,
	url: 'api/events/upcoming'
}); 
window.CourtCalendarEvents = Backbone.Collection.extend({
	initialize: function(options) {
		this.byuser = false;
		if (typeof options != "undefined") {
			if (typeof options.byuser != "undefined") {
				this.byuser = true;
			}
		}
	 },
	model: Occurence,
	url: function() {
		var suffix = "";
		if (this.byuser) {
			suffix = "user";
		}//only pendings for now
		return 'api/events/courtcalendarpending' + suffix;
	}
}); 
window.UpcomingKaseEvents = Backbone.Collection.extend({
	initialize: function(options) {
		this.case_id = options.case_id;
	 },
	model: Occurence,
	url: function() {
		return 'api/kase/upcoming/' + this.case_id;
	}
}); 
window.AllKaseEvents = Backbone.Collection.extend({
	initialize: function(options) {
		this.case_id = options.case_id;
	 },
	model: Occurence,
	url: function() {
		return 'api/events/allkase/' + this.case_id;
	} 
}); 
window.FutureKaseEvents = Backbone.Collection.extend({
	initialize: function(options) {
		this.case_id = options.case_id;
	 },
	model: Occurence,
	url: function() {
		return 'api/events/futurekase/' + this.case_id;
	} 
}); 
window.NewPhoneCalls = Backbone.Collection.extend({
	initialize: function(options) {
		this.showall = false;
		this.start = "";
		this.end = "";
		if (typeof options != "undefined") {
			if (typeof options.showall != "undefined") {
				this.showall = options.showall;
			}
			if (typeof options.start != "undefined") {
				this.start = options.start;
			}
			if (typeof options.end != "undefined") {
				this.end = options.end;
			}
		}
	 },
	model: Occurence,
	url: function() {
		if (this.showall) {
			if (this.start == "") {
				return 'api/callsall';
			} else {
				return 'api/callsbydate/' + this.start + "/" + this.end;
			}
		} else {
			return 'api/callsnew';
		}
	  },
});
window.KasePhoneCalls = Backbone.Collection.extend({
	initialize: function(options) {
		this.case_id = options.case_id;
	  },
	  url: function() {
		return 'api/kase/phone_calls/' + this.case_id;
	  },
	model: Occurence
});
window.InjuryAppearances = Backbone.Collection.extend({
	initialize: function(options) {
		this.injury_id = options.injury_id;
	  },
	  url: function() {
		return 'api/injury/appearances/' + this.injury_id;
	  },
	model: Occurence
});
window.EventLastChange = Backbone.Model.extend({
    urlRoot:"api/lastchange/events",
    initialize:function (options) {
       
    },
	defaults : {
		"max_track_id":"0"
	}
});
window.OccurenceCustomerChangedCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.max_track_id = options.max_track_id;
	  },
	  url: function() {
		return 'api/latestchanges/events/' + this.max_track_id;
	  },
	model: Occurence
});
window.EventCount = Backbone.Model.extend({
	defaults : {
		"event_year": "",
		"event_month": "",
		"event_counts": "",
	}
});
window.EventCountCollection = Backbone.Collection.extend({
	initialize: function() {
	  },
	  url: function() {
		return 'api/customer/eventscount';
	  },
	model: EventCount
});