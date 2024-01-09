window.search_kase_view = Backbone.View.extend({
    initialize:function () {
        //console.log('Initializing Search View');
    },

    events:{
       "change #case_statusInput": 			"showStatusDates",
	   "click .set_cases_dates":			"setCaseDates",
	   "click .set_statute_dates":			"setStatuteDates",
	   "click #search_kase_all_done":		"doTimeouts"
    },
    render:function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "myModalBody");
			var view = "search_kase_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	    }
		var self = this;
		if (typeof this.model == "undefined") {
			this.model = new Kase({id: -1});
		}
		
        try {
			$(this.el).html(this.template());
		}
		catch(err) {
			var view = "search_kase_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
        return this;
    },
	setCaseDates: function(event) {
		var element_id = event.currentTarget.id;
		var link_id = element_id.replace("_cases", "");
		switch(link_id) {
			case "this_month":
				var start_date = moment().startOf('month').format("MM/DD/YYYY");
				var end_date = moment().format("MM/DD/YYYY");
				break;
			case "last_month":
				var prev_month = moment().subtract(1, 'months')._d;
				var start_date = moment(prev_month).startOf('month').format("MM/DD/YYYY");
				var end_date = moment(prev_month).endOf('month').format("MM/DD/YYYY");
				break;
			case "six_month":
				var prev_month = moment().subtract(6, 'months')._d;
				var start_date = moment(prev_month).startOf('month').format("MM/DD/YYYY");
				var end_date = moment().format("MM/DD/YYYY");
				break;
		}
		
		$("#case_dateInput").val(start_date);
		$("#case_throughdateInput").val(end_date);
	},
	setStatuteDates: function(event) {
		var element_id = event.currentTarget.id;
		var link_id = element_id.replace("_statute", "");
		switch(link_id) {
			case "this_month":
				var start_date = moment().startOf('month').format("MM/DD/YYYY");
				var end_date = moment().endOf('month').format("MM/DD/YYYY");
				break;
			case "next_month":
				var next_month = moment().add(1, 'months')._d;
				var start_date = moment(next_month).startOf('month').format("MM/DD/YYYY");
				var end_date = moment(next_month).endOf('month').format("MM/DD/YYYY");
				break;
			case "six_month":
				var start_date = moment().startOf('month').format("MM/DD/YYYY");
				var next_month = moment().add(6, 'months')._d;
				var end_date = moment(next_month).endOf('month').format("MM/DD/YYYY");
				break;
		}
		
		$("#sol_startdateInput").val(start_date);
		$("#sol_enddateInput").val(end_date);
	},
	doTimeouts: function() {
		datepickIt("#case_dateInput", false);
		datepickIt("#case_throughdateInput", false);
		
		datepickIt("#sol_startdateInput", false);
		datepickIt("#sol_enddateInput", false);
		
		var theme = {
				theme: "facebook",
				hintText: "Search for Employees",
				onAdd: function(item) {
				},
				onDelete: function(item) {					
				}
		};
		//lookup employees
		$("#employeeInput").tokenInput("api/user", theme);
		$(".token-input-list-facebook").css("width", "227px");
		
		$("#employeeInput").tokenInput("add", {
			id: login_user_id, 
			name: login_username
		});
	},
	showStatusDates: function(event) {
		//$("#status_dates_holder").fadeIn();
	}
});
function setActiveCases(obj) {
	if (obj.checked) {
		$("#case_statusInput").val("Active");
	} else {
		$("#case_statusInput").val("");
	}
}
