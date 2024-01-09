window.priors_view = Backbone.View.extend({
	render: function () {
		var self = this;
		mymodel = this.model.toJSON();
		
		try {
			$(this.el).html(this.template(mymodel));
		}
		catch(err) {
			var view = "priors_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
        return this;
	},
	events:{
		//"click .roll_over": 										"changeImage",
		"click .priors .edit": 										"toggleEditViewPriors",
		"click #priors_all_done":									"doTimeouts"
    },
	toggleEditViewPriors: function(event) {
		if (typeof event != "undefined") {
			event.preventDefault();
		}
		var accident_partie = this.model.get("accident_partie");
		if ($(" ." + accident_partie + " .priors #table_id").val()=="") {
			return;
		}
		
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		//$("#address").show();
		//get all the editing fields, and toggle them back
		
		$("." + accident_partie + " .priors .editing").toggleClass("hidden");
		//$(".defendant .property_damage").toggleClass("hidden");
		//$(".plaintiff .priors .span_class").toggleClass("hidden");
		//$(".plaintiff .priors .input_class").toggleClass("hidden");
		$("." + accident_partie + " .priors .span_class").removeClass("editing");
		$("." + accident_partie + " .priors .input_class").removeClass("editing");

		$("." + accident_partie + " .priors .span_class").toggleClass("hidden");
		$("." + accident_partie + " .priors .input_class").toggleClass("hidden");
		$("." + accident_partie + " .priors .input_holder").toggleClass("hidden");
		$(".button_row.priors").toggleClass("hidden");
		$(".edit_row.priors").toggleClass("hidden");
	},
	doTimeouts: function(){
		gridsterById('gridster_priors');
		this.model.set("editing", false);
		
		var accident_partie = this.model.get("accident_partie");
		
		$("." + accident_partie + " .priors .edit").trigger("click");
	}
	
});