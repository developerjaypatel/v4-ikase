window.adjustment_form = Backbone.View.extend({
	events:{
		"click #adjustment_view_done":					"doTimeouts"
    },
	render: function () {
		var self = this;
		
		if (typeof this.template != "function") {
			var view = "adjustment_form";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		var adjustment = this.model.toJSON();
		if (adjustment.adjustment_date!="" && adjustment.adjustment_date!="0000-00-00 00:00:00") {
			adjustment.adjustment_date = moment(adjustment.adjustment_date).format("YYYY-MM-DD");
		} else {
			adjustment.adjustment_date = "";
		}
		try {
			$(this.el).html(this.template(adjustment));
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
var blnNewAdjustment = false;
window.adjustment_listing_view = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click .adjustment_category":					"filterByCategory",
		"click #adjustments_clear_search":				"clearSearch",
		"click #new_adjustment":						"newAdjustment",
		"click .edit_adjustment":						"editAdjustment",
		"click .delete_adjustment":						"confirmdeleteAdjustment",
		"click #adjustment_listing_all_done":			"doTimeouts"		
	},
    render:function () {		
		var self = this;
		if (typeof this.template != "function") {
			var view = "adjustment_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
	   
		var adjustments = this.collection;
		var mymodel = this.model.toJSON();

		var page_title = this.model.get("page_title");
		var embedded = this.model.get("embedded");
				
		_.each( adjustments, function(adjustment) {
			if (adjustment.adjustment_date!="" && adjustment.adjustment_date!="0000-00-00") {
				adjustment.adjustment_date = moment(adjustment.adjustment_date).format("MM/DD/YY")
			} else {
				adjustment.adjustment_date = "";
			}		
		});
		
		if (typeof mymodel.embedded == "undefined") {
			this.model.set("embedded", false);
		}
		var case_id = mymodel.case_id;
		
		try {
			$(this.el).html(this.template({
				adjustments: adjustments,
				page_title: page_title, 
				embedded: embedded
			}));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		
		blnNewAdjustment = false;
		
		return this;
    },
	doTimeouts: function(event) {
		if (this.collection.length==0) {
			var page_title = this.model.get("page_title");
			$(".adjustment_listing_" + page_title).hide();
			$("#" + page_title.toLowerCase() + "_holder").css("padding-bottom", "15px");
			
		}
		$(".adjustment_listing th").css("font-size", "1em");
		$(".adjustment_listing").css("font-size", "1.1em");
		
		$(".tablesorter").css("background", "url(../img/glass_dark.png)");
	},
	confirmdeleteAdjustment: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		//check for which box we're in
		var boxtype = "in";
		if (element.className.indexOf(" out") > -1) {
			boxtype = "out";
		}
		composeDelete(id, "adjustment");
	},
	newAdjustment:function (l) {
		if (blnNewAdjustment) {
			return;
		}
		event.preventDefault();
		
		var account_id = this.model.get("account_id");
		
		blnNewAdjustment = true;
		var element_id = "new_adjustment_-1";

		composeAdjustment(element_id, account_id);
		
		var self = this;
		setTimeout(function() {
			blnNewAdjustment = false;
		}, 1200);
    },
	editAdjustment:function (event) {
		if (blnNewAdjustment) {
			return;
		}
		blnNewAdjustment = true;
		
		var element = event.currentTarget;
		var element_id = element.id;
		
		var account_id = this.model.get("account_id");
		
        composeAdjustment(element.id, account_id);
		
		//then statement
		setTimeout(function() {
			blnNewAdjustment = false;
		}, 1200);
    },
	clearSearch: function() {
		$("#adjustments_searchList").val("");
		$( "#adjustments_searchList" ).trigger( "keyup" );
		$("#adjustments_searchList").focus();
	},
});