window.negotiation_form = Backbone.View.extend({
	events:{
		"change #firm_selectInput":						"selectFirm",
		"click #negotiation_all_done":					"doTimeouts"
    },
	render: function () {
		var self = this;
		
		if (typeof this.template != "function") {
			var view = "negotiation_form";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		var negotiation = this.model.toJSON();
		
		var negotiation_date = this.model.get("negotiation_date");
		if (negotiation_date=="" || negotiation=="0000-00-00" || negotiation=="0000-00-00 00:00:00") {
			negotiation_date = "";
		} else {
			negotiation_date = moment(negotiation_date).format("MM/DD/YYYY");
		}
		this.model.set("negotiation_date", negotiation_date);
		
		//corporate source
		if (this.model.id < 1) {
			if ($("#carrier_neg").length > 0) {
				var corporation_id = document.location.hash.split("/")[2];
				this.model.set("corporation_id", corporation_id);
			}
		}
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
				
        return this;
	},
	selectFirm: function() {
		if ($("#firm_selectInput").val()!="") {
			var arrInfo = $("#firm_selectInput option:selected").text().split(" - ");
			$("#firmInput").val(arrInfo[0]);
			$("#negotiatorInput").val(arrInfo[1]);
		} else {
			$("#firmInput").val("");
			$("#negotiatorInput").val("");
		}
	},
	doTimeouts: function() {
		var self = this;
		
		datepickIt("#negotiation_dateInput", false);
		
		var theme = {
				theme: "facebook",
				hintText: "Search for Employees",
				onAdd: function(item) {
				},
				onDelete: function(item) {					
				}
		};
		//lookup employees
		$("#workerInput").tokenInput("api/user", theme);
		$(".token-input-list-facebook").css("width", "227px");
		
		var theoptions = {
			id: login_user_id, 
			name: login_username
		};
		
		if (this.model.get("worker")!="") {
			var the_worker = worker_searches.findWhere({nickname:this.model.get("worker")});
			
			if (typeof the_worker != "undefined") {
				var theoptions = {
					id: the_worker.id, 
					name: the_worker.get("user_name")
				};
			}
			
		}
		$("#workerInput").tokenInput("add", theoptions);
		
		//carrier select
		var parties = new Parties([], { case_id: current_case_id });
		parties.fetch({
			success: function(parties) {
				var carriers = parties.where({"type": "carrier"});
				var arrCarriers = ["<option value='' selected>Select from Carriers</option>"];
				var arrExaminers = [];
				_.each(carriers , function(carrier) {
					var thecarrier = carrier.get("company_name");
					var theexaminer = carrier.get("full_name");
					
					thecarrier = "<option value='" + carrier.get("corporation_id") + "'>" + thecarrier + " - " + theexaminer + "</option>";
					arrCarriers.push(thecarrier);
				});
				if (arrCarriers.length > 1) {
					$("#firm_selectInput").html(arrCarriers.join(""));
					
					if (self.model.get("corporation_id") > 0) {
						$("#firm_selectInput").val(self.model.get("corporation_id"));
						$("#firm_selectInput").trigger("change");
						//console.log(self.model.get("corporation_id"));
					}
				} else {
					$("#firm_selectInput_holder").hide();
				}
			}
		});
	}
});
var blnNewNegotiation = false;
window.negotiation_listing_view = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click .negotiation_category":					"filterByCategory",
		"click #negotiations_clear_search":				"clearSearch",
		"click .btn_negotiation":						"newNegotiation",
		"click .edit_negotiation":						"editNegotiation",
		"click .btn_firm_negotiation":					"newFirmNegotiation",
		"click .delete_negotiation":					"confirmdeleteNegotiation",
		"click #negotiation_listing_all_done":			"doTimeouts"		
	},
    render:function () {		
		var self = this;
		if (typeof this.template != "function") {
			var view = "negotiation_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
	   
		var negotiations = this.collection.toJSON();
		var mymodel = this.model.toJSON();

		var page_title = this.model.get("page_title");
		var embedded = this.model.get("embedded");
				
		_.each( negotiations, function(negotiation) {
			if (negotiation.negotiation_date!="" && negotiation.negotiation_date!="0000-00-00") {
				negotiation.negotiation_date = moment(negotiation.negotiation_date).format("MM/DD/YY")
			} else {
				negotiation.negotiation_date = "";
			}
			if (negotiation.case_name=="") {
				negotiation.case_name = negotiation.case_number;
			}
			if (negotiation.case_name=="") {
				negotiation.case_name = negotiation.file_number;
			}		
		});
		
		if (typeof mymodel.embedded == "undefined") {
			this.model.set("embedded", false);
		}
		var case_id = mymodel.case_id;
		
		try {
			$(this.el).html(this.template({
				negotiations: negotiations,
				page_title: page_title, 
				embedded: embedded
			}));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		
		blnNewCheck = false;
		
		return this;
    },
	doTimeouts: function(event) {
		if (this.collection.length==0) {
			var page_title = this.model.get("page_title");
			$(".negotiation_listing_" + page_title).hide();
			$("#" + page_title.toLowerCase() + "_holder").css("padding-bottom", "15px");
			
		}
		$(".negotiation_listing th").css("font-size", "1em");
		$(".negotiation_listing").css("font-size", "1.1em");
		
		$(".tablesorter").css("background", "url(../img/glass_dark.png)");
	},
	confirmdeleteNegotiation: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		//check for which box we're in
		var boxtype = "in";
		if (element.className.indexOf(" out") > -1) {
			boxtype = "out";
		}
		composeDelete(id, "negotiation");
	},
	newNegotiation:function (event) {
		var neg_type = "O";
		if (event.currentTarget.id=="new_demand") {
			neg_type = "D";
		}
		if (blnNewNegotiation) {
			return;
		}
		blnNewNegotiation = true;
		var element_id = "new_negotiation_-1";

		composeNegotiation(element_id, neg_type);
		
		var self = this;
		setTimeout(function() {
			blnNewNegotiation = false;
		}, 1200);
    },
	newFirmNegotiation:function (event) {
		var neg_type = "O";
		if (event.currentTarget.id.indexOf("new_demand") > -1) {
			neg_type = "D";
		}
		if (blnNewNegotiation) {
			return;
		}
		blnNewNegotiation = true;
		var element_id = "new_negotiation_-1";
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		
		composeNegotiation(element_id, neg_type, id);
		
		var self = this;
		setTimeout(function() {
			blnNewNegotiation = false;
		}, 1200);
    },
	editNegotiation:function (event) {
		if (blnNewNegotiation) {
			return;
		}
		blnNewNegotiation = true;
		
		var element = event.currentTarget;
		var element_id = element.id;
		
		//try promise here
        composeNegotiation(element.id);
		
		//then statement
		setTimeout(function() {
			blnNewNegotiation = false;
		}, 1200);
    },
	clearSearch: function() {
		$("#negotiations_searchList").val("");
		$( "#negotiations_searchList" ).trigger( "keyup" );
		$("#negotiations_searchList").focus();
	},
});