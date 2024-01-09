window.Reminder = Backbone.Model.extend({
	urlRoot:"api/reminder",
	initialize: function(options) {
		this.event_id;
	}
});
window.ReminderCollection = Backbone.Collection.extend({
  initialize: function(models, options) {
    this.event_id = options.event_id;
  },
  url: function() {
    return 'api/reminders/' + this.event_id;
  },
  model: Reminder,
});