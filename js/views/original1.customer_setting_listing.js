window.customer_setting_listing = Backbone.View.extend({

    initialize:function () {
        this.collection.on("change", this.render, this);
		this.collection.on("add", this.render, this);
    },
	events: {
		"click #new_setting": 						"newSettings",
		"click .compose_new_setting": 				"newSetting",
		"click .compose_new_category":				"newCategorySetting",
		"click .delete_icon":						"confirmdeleteSetting",
		"click .delete_yes":						"deleteSetting",
		"click .delete_no":							"canceldeleteSetting",
		"click .expand_category":					"expandCategory",
		"click .collapse_category":					"collapseCategory"
	},
    render:function () {		
		var self = this;
		
		this.collection.bind("reset", this.render, this);
		
		var settings = this.collection.toJSON();
		var arrID = [];
		_.each( settings, function(setting) {
			if (setting.category == "letterhead") {
				setting.setting_value = "<a href='D:/uploads/" + customer_id + "/" +setting.setting_value + "' target='_blank' class='white_text'>" + setting.setting_value + "</a>";
			}
		});
		
		try {
			$(this.el).html(this.template({customers_setting: settings, level: this.model.get("level")}));
		}
		catch(err) {
			var view = "customer_setting_listing";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		tableSortIt("customer_setting_listing");
		
		return this;
    },
	newSetting: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeNewSetting(element.id, this.model.get("level"));
	},
	newSettings: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeNewSetting(element.id, this.model.get("level"));
	},
	newCategorySetting: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeNewCategorySetting(element.id);
	},
	confirmdeleteSetting: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var id = element.id.split("_")[2];
		$("#confirm_delete_id").val(id);
		var arrPosition = showDeleteConfirm(element, 450);	
		$("#confirm_delete").css({display: "none", top: arrPosition[0] - 50, left: arrPosition[1] + 50, position:'absolute'});
		$("#confirm_delete").fadeIn();
	},
	canceldeleteSetting: function(event) {
		event.preventDefault();
		$("#confirm_delete").fadeOut();
	},
	deleteSetting: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "setting");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteSetting(event);
			$(".customer_setting_data_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				$(".customer_setting_data_row_" + id).fadeOut();
			}, 2500);
		}
	},
	expandCategory: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var id = element.id.replace("open", "");
		
		$(".setting_rows_" + id).fadeIn();
		$("#open" + id).fadeOut(function(){
			$("#close" + id).fadeIn();
		});
	},
	collapseCategory: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var id = element.id.replace("close", "");
		
		$(".setting_rows_" + id).fadeOut();
		$("#close" + id).fadeOut(function(){
			$("#open" + id).fadeIn();
		});
	}
});
