window.Kase = Backbone.Model.extend({
	urlRoot:"api/kases",
	initialize:function (options) {
		if (typeof options != "undefined") {
			if (typeof options.case_id != "undefined") {
				this.id = options.case_id;
			}
			if (typeof options.id != "undefined") {
				this.id = options.id;
			}
		}
		//what does this mean?
		//this.toJSON().host = appHost;
		
		//this.notes = new NoteCollection();
        //this.notes.url = 'api/notes/kase/' + this.id;
	},
	defaults: {
		"header_only":  false,
		"id":-1,
		"uuid": "" , 
		"case_id":"",
		"injury_id":"",
		"dob":"",
		"employer":"",
		"employer_id":"",
		"defendant":"",
		"defendant_id":"",
		"case_number":"" , 
		"injury_number":"",
		"full_name":"",
		"ssn":"",
		"adj_number":"" , 
		"case_date":moment().format('MM/DD/YYYY'),
		"terminated_date":"",
		"case_type":"WCAB", 
		"venue_uuid":"", 
		"venue":"", 
		"supervising_attorney":"",
		"attorney":"",
		"worker":"",
		"venue_abbr":"",
		"case_status":"open" , 
		"rating":"",
		"case_substatus":"" , 
		"case_subsubstatus":"",
		"submittedOn": moment().format('MM/DD/YYYY') , 
		"applicant_id":"-1" , 
		"applicant_uuid":"",
		"applicant_phone":"",
		"applicant_email":"",
		"applicant_full_address":"",
		"applicant_salutation":"",
		"start_date": "",
		"end_date": "",
		"ct_dates_note":"",
		"medical":"" , 
		"td":"",
		"rehab": "",
		"edd": "",
		"name":"",
		"attorney_name": "",
		"worker_name":"",
		"interpreter_needed":"",
		"case_language":"",
		"claims":"",
		"rating_age":"",
		"lien_filed":"",
		"holder":"",
		"injury_type":"",
		"list_title":""
	},
	label: function () {
        return this.get("first_name") + " " + this.get("last_name") + " vs " + this.get("employer") + " / " + this.get("start_date");
    }
	,
	name: function () {
		var thename = this.get("full_name") + " vs " + this.get("employer");
		if (this.get("first_name")!="") {
			thename = this.get("first_name") + " " + this.get("last_name") + " vs " + this.get("employer");
		}
		if (this.get("start_date")!="00/00/0000") {
			thename += " " + this.get("start_date");
		}
        return thename;
    }
	,
	display: function () {
        var thename = this.get("first_name") + " " + this.get("last_name") + " vs " + this.get("employer");
		if (this.get("start_date")!="00/00/0000") {
			thename += " / " + this.get("start_date");
		}
		return thename;
    }
});

window.KaseCollection = Backbone.Collection.extend({
    model: Kase,
    url:"api/kases",
	initialize:function () {
		//the kases returned will be for current customer only, see pak
	},
	searchCollection:function (key) {
		return this.filter(function(kase){ 
			var search_string = kase.get("name") + "|" + kase.get("venue_abbr")  + "|" +  kase.get("ssn")  + "|" +  kase.get("start_date")  + "|" +  kase.get("dob")  + "|" +  kase.get("adj_number")  + "|" +  kase.get("occupation");
			//blnReturn = _.values(kase.toJSON()).toString().toLowerCase().indexOf(key.toLowerCase()) > -1;
			blnReturn = search_string.toLowerCase().indexOf(key.toLowerCase()) > -1
			return blnReturn;
		});
    },
	searchDB:function(key, modifier) {
		key = key.replaceAll("/", "-");
		key = key.replaceAll(" ", "_");
		var url = "api/kases/search/" + key;
		if (typeof modifier != "undefined") {
			if (modifier != "sol") {
				var url = "api/kases/mine/" + key + "/" + modifier;
			} else {
				var url = "api/kases/mine/" + moment(key).format("YYYY-MM-DD") + "/" + modifier;
			}
		}
		
        var self = this;
        $.ajax({
            url:url,
            dataType:"json",
            success:function (data) {
				self.reset(data);
				//main kases may have all of it
				if (typeof kases != "undefined") {
					kases.add(data);
				}
                showDBResults(self, key);
            }
        });
	}
});
function showDBResults(my_kases, key) {
	if (!blnSearchingKases) {
		return;
	}
	$('#ikase_loading').html("");
	if (my_kases.length>0) {
		var mymodel = new Backbone.Model();
		mymodel.set("key", key);
		
		if ($("#list_kases_header").length==0 || $("#list_kases_header").parent().parent()[0].id=="search_results") {
			$('#search_results').html(new kase_listing_view_mobile({collection: my_kases, model: mymodel}).render().el);
			$("#search_results").show();
		} else {
			$('#content').html(new kase_listing_view_mobile({collection: my_kases, model: mymodel}).render().el);
		}
	}
}
window.KaseRecentCollection = Backbone.Collection.extend({
    model: Kase,
    url:"api/kases/recent",
	initialize:function () {
		//the kases returned will be for current customer only, see pak
	}
});
window.ReferralReportMonth = Backbone.Model.extend({
	defaults: {
		"referring_id":"-1",
		"referral":"",
		"case_year":"",
		"case_month":"",
		"case_count":"0"
	}
});
window.ReferralReportMonthCollection = Backbone.Collection.extend({
    model: ReferralReportMonth,
    url:"api/referrals/bymonth",
	initialize:function () {
		//the kases returned will be for current customer only, see pak
	}
});
window.KaseReportMonth = Backbone.Model.extend({
	defaults: {
		"case_year":"",
		"case_month":"",
		"case_count":"0"
	}
});
window.KaseReportMonthCollection = Backbone.Collection.extend({
    model: KaseReportMonth,
    url:"api/kases/bymonth",
	initialize:function () {
		//the kases returned will be for current customer only, see pak
	}
});
window.KaseLastCollection = Backbone.Collection.extend({
    model: Kase,
    url:"api/kases/last",
	initialize:function () {
		//the kases returned will be for current customer only, see pak
	}
});
window.KaseReportCollection = Backbone.Collection.extend({
    model: Kase,
    url:"api/kases/report",
	initialize:function () {
		
	}
});
window.KaseListByMonthCollection = Backbone.Collection.extend({
    initialize:function (options) {
		//the kases returned will be for current customer only, see pak
		this.year = options.year;
		this.month = options.month;
		this.referring = "";
		if (typeof options.referring !="undefined") {
			this.referring = options.referring;
		}
	},
	url: function() {
		if (this.referring!="") {
			this.referring = this.referring.replaceTout("'", "%27");
			return 'api/kases/referralsbymonth/' + this.year + '/' + this.month + '/' + this.referring;
		} else {
			return 'api/kases/listbymonth/' + this.year + '/' + this.month;
		}
	},
	model: Kase
});
window.InactiveCasesCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.days = options.days;
	},
	url: function() {
		return 'api/inactive/' + this.days;
	},
	model: Kase
});
window.KaseOpenCollection = Backbone.Collection.extend({
	initialize: function(options) {
	},
	url: function() {
		return 'api/openkases';
	},
	model: Kase
});