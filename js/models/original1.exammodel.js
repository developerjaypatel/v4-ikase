window.Exam = Backbone.Model.extend({
	url: function() {
		return 'api/exam/' + this.id;
	  },
	initialize: function(options) {
		if (typeof options != "undefined") {
			this.id = options.id;
		}
	  },
	defaults : {
		"id" : -1,
		"exam_id" : -1,
		"exam_uuid": "",
		"exam_dateandtime":"",
		"exam_status":"",
		"exam_type":"",
		"specialty":"",
		"requestor":"",
		"comments":"",
		"document_id":"",
		"document_date":"",
		"document_name":"",
		"document_filename":"",
		"permanent_stationary":"",
		"fs_date":"",
		"gridster_me":true
	}
});
window.ExamCollection = Backbone.Collection.extend({
    initialize: function(options) {
		this.case_id = options.case_id;
	 },
	model: Exam,
	url: function() {
		var thereturn = 'api/exams/' + this.case_id;
		return thereturn;
	}
});