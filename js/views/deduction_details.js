window.deduction_form = Backbone.View.extend({
	events:{
		"click #deduction_view_done":					"doTimeouts"
    },
	render: function () {
		var self = this;
		
		if (typeof this.template != "function") {
			var view = "deduction_form";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		var deduction = this.model.toJSON();
		var balance = Number(deduction.amount) - Number(deduction.payment) - Math.abs(Number(deduction.adjustment));
		this.model.set("balance", balance);
		
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
				
        return this;
	},
	doTimeouts: function() {
	}
});
window.cost_form = Backbone.View.extend({
	events:{
		"click #deduction_all_done":					"doTimeouts"
    },
	render: function () {
		var self = this;
		
		if (typeof this.template != "function") {
			var view = "deduction_form";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		var deduction = this.model.toJSON();
		//deduction.amount = "150.00";
		var balance = Number(deduction.amount) - Number(deduction.payment) - Math.abs(Number(deduction.adjustment));
		this.model.set("balance", balance);
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
				
        return this;
	},
	doTimeouts: function() {
		alert("here");
		$("#amountInput").val("150.00");
	}
});
var blnNewDeduction = false;
window.deduction_listing_view = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click .deduction_category":					"filterByCategory",
		"click #deductions_clear_search":				"clearSearch",
		"click #new_deduction":							"newDeduction",
		"click .new_deduction":							"newFlatCost",
		"click .edit_deduction":						"editDeduction",
		"click .delete_deduction":						"confirmdeleteDeduction",
		"click #deduction_listing_all_done":			"doTimeouts"		
	},
    render:function () {		
		var self = this;
		if (typeof this.template != "function") {
			var view = "deduction_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
	   
		var deductions = this.collection.toJSON();
		var mymodel = this.model.toJSON();

		var page_title = this.model.get("page_title");
		var embedded = this.model.get("embedded");
				
		_.each( deductions, function(deduction) {
			if (deduction.deduction_date!="" && deduction.deduction_date!="0000-00-00") {
				deduction.deduction_date = moment(deduction.deduction_date).format("MM/DD/YY")
			} else {
				deduction.deduction_date = "";
			}
			if (deduction.case_name=="") {
				deduction.case_name = deduction.case_number;
			}
			if (deduction.case_name=="") {
				deduction.case_name = deduction.file_number;
			}		
		});
		
		if (typeof mymodel.embedded == "undefined") {
			this.model.set("embedded", false);
		}
		var case_id = mymodel.case_id;
		
		try {
			$(this.el).html(this.template({
				deductions: deductions,
				page_title: page_title, 
				embedded: embedded
			}));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		
		blnNewDeduction = false;
		
		return this;
    },
	doTimeouts: function(event) {
		if (this.collection.length==0) {
			var page_title = this.model.get("page_title");
			$(".deduction_listing_" + page_title).hide();
			$("#" + page_title.toLowerCase() + "_holder").css("padding-bottom", "15px");
			
		}
		$(".deduction_listing th").css("font-size", "1em");
		$(".deduction_listing").css("font-size", "1.1em");
		
		$(".tablesorter").css("background", "url(../img/glass_dark.png)");
	},
	confirmdeleteDeduction: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		//check for which box we're in
		var boxtype = "in";
		if (element.className.indexOf(" out") > -1) {
			boxtype = "out";
		}
		composeDelete(id, "deduction");
	},
	newDeduction:function (l) {
		//alert("here");
		if (blnNewDeduction) {
			return;
		}
		blnNewDeduction = true;
		var element_id = "new_deduction_-1";
		if (typeof payback_id != "undefined") {
			element_id = payback_id;
		}
		composeDeduction(element_id);
		
		var self = this;
		setTimeout(function() {
			blnNewDeduction = false;
		}, 1200);
    },
	newFlatCost:function (l) {
		//alert("here");
		if (blnNewDeduction) {
			return;
		}
		blnNewDeduction = true;
		var element_id = "new_deduction_-1";
		if (typeof payback_id != "undefined") {
			element_id = payback_id;
		}
		composeFlatCost(element_id);
		
		var self = this;
		setTimeout(function() {
			blnNewDeduction = false;
		}, 1200);
    },
	editDeduction:function (event) {
		if (blnNewDeduction) {
			return;
		}
		blnNewDeduction = true;
		
		var element = event.currentTarget;
		var element_id = element.id;
		
		//try promise here
        composeDeduction(element.id);
		
		//then statement
		setTimeout(function() {
			blnNewDeduction = false;
		}, 1200);
    },
	clearSearch: function() {
		$("#deductions_searchList").val("");
		$( "#deductions_searchList" ).trigger( "keyup" );
		$("#deductions_searchList").focus();
	},
});