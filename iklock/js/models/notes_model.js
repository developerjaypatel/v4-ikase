window.EmployeeNotes = Backbone.Model.extend({
	urlRoot:"api/checks",
	initialize:function (options) {
		if (typeof options.check_id != "undefined") {
			this.id = options.check_id;
		}
	},
	defaults : {
		"id" : 				-1,
		"notes_id": 		-1,
		"notes": 			"",
		"time_stamp":		"",
		"user_name":		"",
		"callback_date":	"",
		"contact":			"",
		"status":			"",
		"deleted":			"N",
		"customer_id":		0
	}
});
window.EmployeeNotesCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.user_id = options.user_id;
	},
	url: function() {
		return 'api/employee/notes/' + this.user_id;
	},
	model:Check
});