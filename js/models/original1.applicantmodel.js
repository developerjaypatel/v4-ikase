window.Applicant = Backbone.Model.extend({
	urlRoot:"api/applicants",
	initialize:function () {
		
	}
});

window.ApplicantCollection = Backbone.Collection.extend({
    model: Applicant,
    url:"api/applicants",

});