window.costs_view = Backbone.View.extend({
	initialize:function () {
	},
	events:{
		"click .costs .save":				"saveCosts",
		"click .costs .save_field":			"saveCostsViewField",
		"click .costs .edit": 				"toggleCostsEdit",
		"click .costs .reset": 				"resetCostsForm",
		"click .costs#all_done":			"doTimeouts"
	},
	render: function () {
		var self = this;
		
		this.model.set("sizey", "1");
		this.model.set("marginTop", "-26");
		this.model.set("marginTopSpan", "-30");
		if (this.model.get("fee_type")=="depo_fees") {
			this.model.set("sizey", "3");
			this.model.set("marginTop", "0");
			this.model.set("marginTopSpan", "0");
		}
		var costs = this.collection.toJSON();
		try {
			$(this.el).html(this.template({costs: costs, fee_type: this.model.get("fee_type"), id: this.model.get("id"), injury_id: this.model.get("injury_id"), sizey: this.model.get("sizey"), marginTop: this.model.get("marginTop"), marginTopSpan: this.model.get("marginTopSpan")}));
		}
		catch(err) {
			var view = "costs_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
        return this;
    },
	doTimeouts: function() {
		var self = this;
		
		gridsterById('gridster_costs');
		
		var costs =  this.collection.toJSON();
		var form_class = ".costs_" + this.model.get("fee_type");
		
		_.each(costs, function (cost) {
            if (cost.fee_check_number!="") {
				$(form_class + " #cost" + cost.fee_check_number + "Input").val(cost.fee_paid);
				$(form_class + " #cost" + cost.fee_check_number + "Span").html(cost.fee_paid);
			}
			if (cost.fee_date != "" && cost.fee_date != "0000-00-00") {
				cost.fee_date = moment(cost.fee_date).format("MM/DD/YYYY");
				$(form_class + " #date" + cost.fee_check_number + "Input").val(cost.fee_date);
				$(form_class + " #date" + cost.fee_check_number + "Span").html(cost.fee_date);
			}
			if (cost.fee_recipient!="") {
				$(form_class + " #comment" + cost.fee_check_number + "Input").val(cost.fee_recipient);
				$(form_class + " #comment" + cost.fee_check_number + "Span").html(cost.fee_recipient);
			}
        }, this);
		
		$(".costs .date_field").datetimepicker({
				timepicker:false, 
				format:'m/d/Y',
				mask:false,
				onChangeDateTime:function(dp,$input){
					//alert($input.val());
			}
		});
		costTokenInput();
	},
	saveCosts:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		addForm(event, "costs_" + this.model.get("fee_type"), "costs");
		return;
    },
	toggleCostsEdit: function(event) {
		event.preventDefault();
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}
		//$("#address").show();
		//get all the editing fields, and toggle them back
		var form_class = ".costs_" + this.model.get("fee_type");
		
		$(form_class + " .editing").toggleClass("hidden");
		$(form_class + " .span_class").removeClass("editing");
		$(form_class + " .input_class").removeClass("editing");
		
		$(form_class + " .span_class").toggleClass("hidden");
		$(form_class + " .input_class").toggleClass("hidden");
		$(form_class + " .input_holder").toggleClass("hidden");
		
		var class_name = "costs";
		if (this.model.get("fee_type")=="depo_fees") {
			class_name = "fees";
		}
		$(".button_row." + class_name).toggleClass("hidden");
		$(".edit_row." + class_name).toggleClass("hidden");
	},
	
	resetCostsForm: function(event) {
		event.preventDefault();
		this.toggleCostsEdit(event);
	}
});
var costTokenInput = function() {
	//$(".token-input-dropdown-person").css("width", "150px");
	var theme = {
		theme: "person", 
		minChars:3, 
		noResultsText:"None Found", 
		propertyToSearch:"full_name",
		tokenLimit:1,
		onResult: function(results) {
			//console.log(results);
			return results;
		}
	}
	
	//$("#full_name_" + cost.fee_check_number + "Input").tokenInput("api/users/tokeninput", theme);
	
	
	//$("#token-input-full_nameInput").focus();
	$(".token").tokenInput("api/user", theme);
	$(".token-input-list-person").css("margin-left", "85px");
	$(".token-input-list-person").css("margin-top", "-12px");
	$(".token-input-list-person").css("width", "110px");
	//$(".token-input-list-person").css("display", "none");
}