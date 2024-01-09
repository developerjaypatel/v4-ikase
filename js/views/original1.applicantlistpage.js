window.ApplicantListPageView = Backbone.View.extend({

    initialize:function () {
        this.model.on("change", this.render, this);
    },

    render:function () {		
		var self = this;
		
		this.model.bind("reset", this.render, this);
		$(this.el).html(this.template({applicants: this.model.toJSON()}));
		
		//setTimeout("tableSortIt()", 100);
		setTimeout("listApplicants()", 100);
		
		return this;
    }

});

function listApplicants() {
	var options = {
	  valueNames: [ 'applicant_uuid', 'applicant_title' ]
	};
	
	var applicantList = new List('applicant_listing', options);
}

/*
function tableSortIt () {
	$("#kase_list_table").tablesorter(); 
}
*/