window.Note = Backbone.Model.extend({
	url: function() {
		return 'api/notes/' + this.id;
	},
	initialize: function(options) {
		this.id = options.notes_id;
    this.case_id = options.case_id;
	},
	defaults: {
		"attachment_link":  "",
		"subject":			"",
		"short_note":		"",
		"start_date":		"",
		"end_date":		"",
    "task_id":			"-1",
    "dateandtime":"" // solulab code change 22-04-2019
	}
});
window.NoteCollection = Backbone.Collection.extend({
  initialize: function(models, options) {
    this.case_id = options.case_id;
  },
  url: function() {
    return 'api/notes/kases/' + this.case_id;
  },
  model: Note,
});
window.NoteCollectionDash = Backbone.Collection.extend({
  initialize: function(models, options) {
    this.case_id = options.case_id;
  },
  url: function() {
    return 'api/notes/dash/' + this.case_id;
  },
  model: Note,
});
window.RedFlagCollection = Backbone.Collection.extend({
  initialize: function(models, options) {
    this.case_id = options.case_id;
  },
  url: function() {
    return 'api/notes/redflag/' + this.case_id;
  },
  model: Note,
});
window.NotesByType = Backbone.Collection.extend({
  initialize: function(models, options) {
    this.case_id = options.case_id;
	this.type = options.type;
  },
  url: function() {
    return 'api/notes/' + this.type + '/' + this.case_id;
  },
  model: Note,
});
window.InjuryNotesByType = Backbone.Collection.extend({
  initialize: function(models, options) {
    this.injury_id = options.injury_id;
	this.type = options.type;
  },
  url: function() {
    return 'api/injurynotes/' + this.type + '/' + this.injury_id;
  },
  model: Note,
});