window.Calendar = Backbone.Model.extend({
	urlRoot:"api/calendar",
	initialize: function(options) {
		if (typeof options != "undefined") {
			this.id = options.calendar_id;
		}
	  },
	defaults : {
		"calendar_id" : "",
		"calendar": "",
		"sort_order": "",
		"active": "Y",
		"deleted": "N"
	}
});
window.CalendarCollection = Backbone.Collection.extend({
    initialize: function(options) {		
	 },
	model: Calendar,
    url:"api/calendar"
});
window.PersonalCalendarCollection = Backbone.Collection.extend({
    initialize: function(options) {		
	 },
	model: Calendar,
    url:"api/personalcalendars"
});
window.BlockedDate = Backbone.Model.extend({
	urlRoot:"api/blocked",
	initialize: function(options) {
		
	  },
	defaults : {
		"blocked_id" : "",
		"start_date": "",
		"end_date": "",
		"recurring_count": "0",
		"recurring_span": "",
		"deleted": "N"
	}
});
window.BlockedDateCollection = Backbone.Collection.extend({
    initialize: function(options) {		
	 },
	model: BlockedDate,
    url:"api/blockedactive"
});