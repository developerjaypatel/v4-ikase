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
			if (typeof options.stack_id != "undefined") {
				this.stack_id = options.stack_id;
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
		"file_number":"",
		"injury_number":"",
		"injury_type":"",
		"main_injury_type":"",
		"special_instructions":"",
		"case_description":"",
		"full_name":"",
		"ssn":"",
		"adj_number":"" , 
		"case_date":moment().format('MM/DD/YYYY'),
		"filing_date":"0000-00-00",
		"terminated_date":"",
		"case_type":"WCAB",
		"case_sub_type":"",
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
		"closed_date": "",
		"statute_limitation": "",
		"ct_dates_note":"",
		"medical":"" , 
		"td":"",
		"rehab": "",
		"edd": "",
		"name":"",
		"attorney_name": "",
		"worker_name":"",
		"interpreter_needed":"",
		"file_location":"",
		"case_language":"",
		"claims":"",
		"rating_age":"",
		"lien_filed":"",
		"holder":"",
		"injury_type":"",
		"personal_injury_date": "",
		"sub_in":"",
		"file_number":"",
		"list_title":"",
		"jetfile_id":"",
		"jetfile_dor_id":"",
		"jetfile_dore_id":"",
		"jetfile_lien_id":"",
		"app_filing_id":"",
		"dor_filing_id":"",
		"dore_filing_id":"",
		"lien_filing_id":""
	},
	label: function () {
        return this.get("first_name") + " " + this.get("last_name") + " vs " + this.get("employer") + " / " + this.get("start_date");
    }
	,
	name: function () {
		if (this.get("case_name") != "") {
			var thename = this.get("case_name");
		} else {
			var thename = this.get("full_name") + " vs " + this.get("employer");
			if (this.get("first_name")!="") {
				thename = this.get("first_name") + " " + this.get("last_name") + " vs " + this.get("employer");
			}
			if (this.get("start_date")!="00/00/0000") {
				thename += " " + this.get("start_date");
			}
		}
		
		if (typeof thename !== "undefined") {
			if (thename.trim()=="vs") {
				thename = this.get("file_number");
			}
		}
        return thename;
    },
	rename:function() {
		var url = "api/kase/rename";
		var formValues = "case_id=" + this.get("case_id");
		$.ajax({
            url:url,
			type:'POST',
            dataType:"json",
			data: formValues,
            success:function (data) {
				if (data.success) {
					setTimeout(function() {
						if ($("#summary_kase_name").length > 0) {
							$("#summary_kase_name").html(data.case_name);
						}
					}, 1500);
				}
            }
        });
	},
	display: function () {
        var thename = this.get("first_name") + " " + this.get("last_name") + " vs " + this.get("employer");
		if (this.get("start_date")!="00/00/0000") {
			thename += " / " + this.get("start_date");
		}
		return thename;
    }
});
var kase_url = "";
window.KaseCollection = Backbone.Collection.extend({
    model: Kase,
    url:"api/kases",
	initialize:function (options) {
		//the kases returned will be for current customer only, see pak
		this.company_name = "";
		if (typeof options != "undefined") {
			if (typeof options.company_name != "undefined") {
				this.company_name = options.company_name;
			}
		}
	},
	searchCollection:function (key) {
		return this.filter(function(kase){ 
			var search_string = kase.get("name") + "|" + kase.get("venue_abbr")  + "|" +  kase.get("ssn")  + "|" +  kase.get("start_date")  + "|" +  kase.get("dob")  + "|" +  kase.get("adj_number")  + "|" +  kase.get("occupation");
			//blnReturn = _.values(kase.toJSON()).toString().toLowerCase().indexOf(key.toLowerCase()) > -1;
			blnReturn = search_string.toLowerCase().indexOf(key.toLowerCase()) > -1
			return blnReturn;
		});
    },
	searchDB:function(key, modifier, blnRolodex) {
		if (typeof blnRolodex == "undefined") {
			blnRolodex = false;
		}
		key = key.replaceAll("/", "-");
		key = key.replaceAll(" ", "_");
		key = key.replaceTout("*", "~");
		var url = "api/kases/search/" + key;
		if (typeof modifier != "undefined") {
			if (modifier != "sol") {
				if (modifier=="closed" && key=="") {
					//all closed orders
					var url = "api/closedkases";
				} else {
					var url = "api/kases/mine/" + key + "/" + modifier;
				}
			} else {
				var url = "api/kases/mine/" + moment(key).format("YYYY-MM-DD") + "/" + modifier;
			}
			if (blnRolodex) {
				var url = "api/kases/rolodex/" + key + "/" + modifier;
			}
		}
		kase_url = url;
        var self = this;
        $.ajax({
            url:url,
            dataType:"json",
            success:function (data) {
				listed_kases = data.length;
				self.reset(data);
				if (data.length > 0) {
					//main kases may have all of it
					if (typeof kases != "undefined") {
						kases.add(data);
					}
					showDBResults(self, key);
					var company_name = self.company_name;
					if (company_name!="") {
						setTimeout(function() {
							$("#search_results").css("margin-top", "60px");
							//$("#kase_status_title").html(corporation_type.replaceAll("_", " ").capitalizeWords());
							$("#kase_status_title").html("`" + company_name.trim() + "`");
						}, 2700)
					}
				} else {
					$('#ikase_loading').html("");
					$('#search_results').html('<span class="large_white_text">No kases found for this search</span>');
				}
            }
        });
	}
});
window.KaseEmployeeCollection = Backbone.Collection.extend({
    model: Kase,
    url: function() {
		var api = "api/kases/employee/" + this.name.replaceAll(" ", "+") + "/" + this.partie_type;
		kase_url = api;
		return api;
	},
	initialize:function (options) {
		//the kases returned will be for current customer only, see pak
		this.name = options.name;
		this.partie_type = options.partie_type;
	}
});
window.IntakeCollection = Backbone.Collection.extend({
    model: Kase,
	initialize:function (options) {
		this.filter = "";
		if (typeof options != "undefined") {
			if (typeof options.filter != "undefined") {
				this.filter = options.filter;
			}
		}
		this.type = "";
		if (typeof options != "undefined") {
			if (typeof options.type != "undefined") {
				this.type = options.type;
			}
			if (this.filter=="") {
				this.filter = "_";
			}
		}
		this.letter = "";
		if (typeof options != "undefined") {
			if (typeof options.letter != "undefined") {
				if (options.letter!="") {
					this.letter = options.letter;
				}
			}
			if (this.filter=="") {
				this.filter = "_";
			}
		}
	},
    url: function() {
		kase_url = "api/intakes";
		if (this.filter!="") {
			kase_url += "filtered/" + this.filter;
			if (this.type=="") {
				this.type = "_";
			}
		}
		if (this.letter!="") {
			kase_url = kase_url.replace("intakes/", "intakesbyletter/");
		}
		kase_url += "/" + this.type;
		return kase_url;
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
			$('#search_results').html(new kase_listing_view({collection: my_kases, model: mymodel}).render().el);
		} else {
			$('#content').html(new kase_listing_view({collection: my_kases, model: mymodel}).render().el);
		}
	}
}
window.KaseRecentCollection = Backbone.Collection.extend({
    model: Kase,
    url:"api/kases/recent",
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
window.ClientReportMonthCollection = Backbone.Collection.extend({
    model: ReferralReportMonth,
    url:"api/clients/bymonth",
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
    url: function() {
		if (this.statute_search) {
			return "api/kases/lastmonth";
		}
		return "api/kases/last";
	},
	initialize:function (options) {
		this.statute_search = false;
		//the kases returned will be for current customer only, see pak
		if (typeof options != "undefined") {
			if (typeof options.statute_search != "undefined") {
				this.statute_search = options.statute_search;
			}
		}
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
		this.referring_type = "";
		if (typeof options.referring !="undefined") {
			this.referring = options.referring;
		}
		if (typeof options.referring_type !="undefined") {
			this.referring_type = options.referring_type;
		}
	},
	url: function() {
		if (this.referring!="") {
			this.referring = this.referring.replaceTout("'", "%27");
			var return_url = 'api/kases/referralsbymonth/' + this.year + '/' + this.month + '/' + this.referring;
			if (this.referring_type=="client") {
				return_url = 'api/kases/clientsbymonth/' + this.year + '/' + this.month + '/' + this.referring;	
			}
			kase_url = return_url;
			return return_url;
		} else {
			var api = 'api/kases/listbymonth/' + this.year + '/' + this.month;
			kase_url = api;
			return api;
		}
	},
	model: Kase
});
window.InactiveCasesCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.days = options.days;
	},
	url: function() {
		var api = 'api/inactive/' + this.days;
		kase_url = api;
		return api;
	},
	model: Kase
});
window.KaseWCCollection = Backbone.Collection.extend({
	initialize: function(options) {
	},
	url: function() {
		var api = 'api/wkases';
		kase_url = api;
		return api;
	},
	model: Kase
});
window.KasePICollection = Backbone.Collection.extend({
	initialize: function(options) {
	},
	url: function() {
		var api = 'api/pikases';
		kase_url = api;
		return api;
	},
	model: Kase
});
window.KaseOpenCollection = Backbone.Collection.extend({
	initialize: function(options) {
	},
	url: function() {
		var api = 'api/openkases';
		kase_url = api;
		return api;
	},
	model: Kase
});
window.KaseClosedCollection = Backbone.Collection.extend({
	initialize: function(options) {
	},
	url: function() {
		var api = 'api/closedkases';
		kase_url = api;
		return api;
	},
	model: Kase
});
window.KaseNoTasksCollection = Backbone.Collection.extend({
	initialize: function(options) {
	},
	url: function() {
		var api = 'api/notaskskases';
		kase_url = api;
		return api;
	},
	model: Kase
});
window.KaseNoWorkersCollection = Backbone.Collection.extend({
	initialize: function(options) {
	},
	url: function() {
		var api = 'api/noworkerkases';
		kase_url = api;
		return api;
	},
	model: Kase
});
window.KaseAllCollection = Backbone.Collection.extend({
	initialize: function(options) {
	},
	url: function() {
		var api = 'api/allkases';
		kase_url = api;
		return api;
	},
	model: Kase
});
window.KaseNextCollection = Backbone.Collection.extend({
	initialize: function(options) {
	},
	url: function() {
		return 'api/nextkases';
	},
	model: Kase
});
window.KasePreviousCollection = Backbone.Collection.extend({
	initialize: function(options) {
	},
	url: function() {
		return 'api/previouskases';
	},
	model: Kase
});
window.KasePageCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.start = options.start;
	},
	url: function() {
		return 'api/opensomekases/' + this.start;
	},
	model: Kase
});
window.KaseEmailCollection = Backbone.Collection.extend({
	initialize: function(options) {
	},
	url: function() {
		//kases with applicant with email
		return 'api/emailkases';
	},
	model: Kase
});
window.UnattendedCount = Backbone.Model.extend({
	urlRoot: 'api/unattendedcount',
	defaults: {
		"case_count":0
	}
});
window.UnattendedCountAll = Backbone.Model.extend({
	urlRoot: 'api/unattendedcountall',
	defaults: {
		"case_count":0
	}
});
window.Unattendeds = Backbone.Collection.extend({
	initialize: function(options) {
	},
	url: function() {
		//kases with applicant with email
		return 'api/unattendeds';
	},
	model: Kase
});
window.UnattendedsAll = Backbone.Collection.extend({
	initialize: function(options) {
	},
	url: function() {
		//kases with applicant with email
		return 'api/unattendedsall';
	},
	model: Kase
});
window.InactiveCount = Backbone.Model.extend({
	urlRoot: 'api/inactivecount',
	defaults: {
		"case_count":0
	}
});
window.Inactives = Backbone.Collection.extend({
	initialize: function(options) {
	},
	url: function() {
		//kases with applicant with email
		return 'api/inactives';
	},
	model: Kase
});
window.InactiveTypeCount = Backbone.Model.extend({
	initialize: function(options) {
		this.type = options.type;
	},
	url: function() {
		return 'api/inactivecount/' + this.type;
	},
	defaults: {
		"case_count":0
	}
});
window.InactiveTypeSuboutCount = Backbone.Model.extend({
	initialize: function(options) {
		this.type = options.type;
	},
	url: function() {
		return 'api/inactivesuboutcount/' + this.type;
	},
	defaults: {
		"case_count":0
	}
});
window.InactivesByType = Backbone.Collection.extend({
	initialize: function(options) {
		this.type = options.type;
	},
	url: function() {
		return 'api/inactives/' + this.type;
	},
	model: Kase
});
window.InactivesSuboutByType = Backbone.Collection.extend({
	initialize: function(options) {
		this.type = options.type;
	},
	url: function() {
		return 'api/inactivesub/' + this.type;
	},
	model: Kase
});
window.Billables = Backbone.Collection.extend({
	initialize: function() {
	},
	url: function() {
		return 'api/kases/billables';
	},
	model: Kase
});
window.KaseWorkerCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.user_id = options.user_id;
		this.case_status = "";
		if (typeof options.case_status != "undefined") {
			this.case_status = options.case_status;
		}
	},
	url: function() {
		var api = "api/kases_worker/" + this.user_id;
		if (this.case_status!="") {
			api = "api/kases_worker_status/" + this.user_id + "/" + this.case_status;
		}
		return api;
	},
	model: Kase
});

//matrix
window.MatrixOrder = Backbone.Model.extend({
	urlRoot:"api/kases/matrixorderinfo",
	initialize:function () {
	},
	defaults: {
		"order_id":  				-1,
		"assigned_date":			"",
		"actual_assigned_date":		"0000-00-00",
		"applicant":				"",
		"employer":					""
	}
});
window.MatrixOrderCollection = Backbone.Collection.extend({
	initialize: function(options) {
		this.applicant = options.applicant;
	},
	url: function() {
		return 'api/kases/matrixsearch/' + this.applicant.replaceAll(" ", "_");
	},
	model: MatrixOrder
});
window.MatrixOrderData = Backbone.Model.extend({
	//urlRoot:"api/kases/matrixlinkinfo",
	url: function() {
		return 'api/kases/matrixlinkinfo/' + this.id + "/" + this.case_id;
	},
	initialize:function (options) {
		this.case_id = options.case_id;
	},
	defaults: {
		"id":  						-1,
		"copyon_name":				"",
		"employer":					"",
		"actual_assigned_date":		"",
		"invoice_count":			0,
		"sum_balance_due":			0,
		"min_invoice_date":			"",
		"max_invoice_date":			"",
		"min_invoice_date":			"",
		"sixty_invoice_date":		"",
		"max_service_date":			"",
		"days_service_date":		0
	}
});
window.SSNClaim = Backbone.Model.extend({
	url: function() {
		return 'api/kases/claim/' + this.case_id;
	},
	initialize:function (options) {
		this.case_id = options.case_id;
	},
	defaults: {
		"id":  						-1,
		"claim_id":					"",
		"claim_info":				""
	}
});
window.KaseSummary = Backbone.Model.extend({
	initialize: function() {

	},
	defaults : {
		"user_id":"",
		"user_name":"",
		"nickname":"",
		"kase_count":0
	}
});
window.KaseSummaries = Backbone.Collection.extend({
	initialize: function(options) {
		
	 },
	model: KaseSummary,
	url: function() {
		return 'api/kases_workersummary';
	  },
});


window.KaseLetterCount = Backbone.Model.extend({
	initialize: function() {

	},
	defaults : {
		"first_letter":"",
		"case_count":0
	}
});
window.KaseLetterCounts = Backbone.Collection.extend({
	initialize: function() {
		
	 },
	model: KaseLetterCount,
	url: function() {
		return 'api/casecountbyletter';
	  },
});